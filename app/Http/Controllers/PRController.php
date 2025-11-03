<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\partList;
use App\Models\prTicket;
use App\Models\PartStock;
use App\Models\prRequest;
use App\Models\User;
use App\Models\Notification;
use App\Mail\TestEmail;
use App\Models\deptList;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;
use Dompdf\Options;
use Skype; // From SkypePHP library
use App\Models\PrDocument;
use App\Models\PrLogHistory;
use Illuminate\Support\Facades\File;

class PRController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->role === 'admin' || $user->role === 'purchasing' || $user->role === 'pic') {
            // Admin and Purchasing can see all tickets
            $dataT = prTicket::all();
        } elseif ($user->role === 'hod') {
            // HOD can see all tickets related to their department
            $dept = deptList::where('user_hod_id', $user->id)->first();
            if ($dept) {
                // Get all user IDs in the HOD's department
                $deptUserIds = User::where('dept_id', $dept->id)->pluck('id');
                // Get tickets where user_id is in the department
                $dataT = prTicket::whereIn('user_id', $deptUserIds)->get();
            } else {
                $dataT = collect(); 
            }
        } else {
            // own ticket
            $dataT = prTicket::where('user_id', $user->id)->get();
        }

        return view('pr.create_pr', [
            'dataR' => partList::all(),
            'dataT' => $dataT
        ]);
    }

    public function generateUniqueTicketCode()
    {
        $currentYear = date('y'); 
        $currentMonth = date('m'); 
        $prefix = $currentYear . $currentMonth; // 00 00 00 format

        return DB::transaction(function () use ($prefix, $currentYear, $currentMonth) {
            // last code this month
            $lastTicket = prTicket::whereRaw("REPLACE(ticketCode, ' ', '') LIKE ?", [$prefix . '%'])
                ->lockForUpdate()
                ->orderBy('ticketCode', 'desc')
                ->first();

            // last number 
            $lastNumber = 0;
            if ($lastTicket) {
                $clean = preg_replace('/\s+/', '', $lastTicket->ticketCode); // hapus spasi
                $lastNumber = (int) substr($clean, -3); // 3 digit number
            }

            $nextNumber = $lastNumber + 1;
            $ticketNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $ticketCode = "{$currentYear} {$currentMonth} {$ticketNumber}";

            if (prTicket::where('ticketCode', $ticketCode)->exists()) {
                $ticketCode = "{$currentYear} {$currentMonth} " . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
            }

            \Log::info("Generated Ticket Code: {$ticketCode}");
            return $ticketCode;
        });
    }

    public function create(Request $request)
    {
        try {
            if (!in_array(auth()->user()->role, ['admin', 'hod', 'clerk', 'regular', 'purchasing', 'pic'])) {
                return response()->json(['error' => 'Unauthorized to create ticket'], 403);
            }

            Log::info('PR Request Input', $request->all());
            Log::info('PR Files', $request->allFiles());

            $validatedData = $request->validate([
                'pr_request.*.part_name'       => 'required|string|min:1',
                'pr_request.*.partlist_id'     => 'required|integer|exists:part_lists,id',
                'pr_request.*.qty'             => 'required|integer|min:1',
                'pr_request.*.amount'          => 'nullable|numeric|min:0',
                'pr_request.*.remark'          => 'nullable|string',
                'pr_request.*.other_cost'      => 'required|numeric|min:0',
                'pr_request.*.vendor'          => 'required|string|min:1',
                'pr_request.*.category'        => 'required|string|min:1',
                'pr_request.*.tag'             => 'required|string|min:1',
                'pr_request.*.document_type'   => 'nullable|string|in:Receipt,Quotation,Invoice,Others',
                'advance_cash'                 => 'nullable|numeric|min:0',
            ]);

            $user   = auth()->user();
            $userId = $user->id;
            $hodId  = $user->deptList->user_hod_id ?? $userId;
            if (!User::find($hodId)) $hodId = $userId;
            $picUser = User::where('role', 'pic')->first();
            $picId  = $picUser?->id ?? $hodId;

            $ticketCode = $this->generateUniqueTicketCode();

            $newTicket = DB::transaction(function () use (
                $ticketCode, $userId, $hodId, $picId, $validatedData, $request
            ) {
                $ticket = prTicket::create([
                    'ticketCode'                 => $ticketCode,
                    'status'                     => 'Pending',
                    'user_id'                    => $userId,
                    'approved_user_id'           => $hodId,
                    'purchasing_approved_user_id'=> $picId,
                    'date_approval'              => now()->format('Y-m-d'),
                    'advance_cash'               => $request->advance_cash ?? 0,
                ]);

                foreach ($validatedData['pr_request'] as $index => $prQ) {
                    $part = partList::findOrFail($prQ['partlist_id']);
                    $qty  = (int) $prQ['qty'];

                    // stock reduction 
                    $currentStock = $this->getCurrentStock($part->id);
                    if ($currentStock !== 'false' && is_numeric($currentStock)) {
                        $newStock = (int) $currentStock - $qty;
                        if ($newStock < 0) {
                            throw new \Exception("Insufficient stock for part: {$part->part_name}");
                        }

                        $part->current_stock = $newStock;
                        $part->save();

                        PartStock::create([
                            'part_list_id' => $prQ['partlist_id'],
                            'quantity'     => $qty,
                            'operations'   => 'minus',
                            'source'       => 'Issued for PR Request',
                            'source_type'  => 'pr_request',
                            'source_ref'   => $ticket->ticketCode,
                        ]);
                    }

                    $prQ['ticket_id'] = $ticket->id;
                    $prRequest = prRequest::create($prQ);

                    $fileKey = "pr_request.{$index}.documents";
                    if ($request->hasFile($fileKey)) {
                        Log::info("Files found for index {$index}", [$fileKey => $request->file($fileKey)]);

                        foreach ($request->file($fileKey) as $file) {
                            if (!$file->isValid()) {
                                Log::warning("Invalid file skipped", ['name' => $file->getClientOriginalName()]);
                                continue;
                            }

                            $ext = strtolower($file->getClientOriginalExtension());
                            $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
                            if (!in_array($ext, $allowed)) {
                                Log::warning("File type not allowed", ['ext' => $ext]);
                                continue;
                            }

                            if ($file->getSize() > 10 * 1024 * 1024) {
                                Log::warning("File too large", ['size' => $file->getSize()]);
                                continue;
                            }

                            $originalName = $file->getClientOriginalName();
                            $mimeType     = $file->getMimeType() ?? 'application/octet-stream';
                            $fileSize     = $file->getSize();

                            $fileName = time() . '_' . uniqid() . '.' . $ext;
                            $file->move(public_path('assets/pr-documents'), $fileName);

                            PrDocument::create([
                                'pr_request_id' => $prRequest->id,
                                'document_type' => $request->input("pr_request.{$index}.document_type", 'Others'),
                                'file_name'     => $fileName,
                                'file_path'     => 'assets/pr-documents/' . $fileName,
                                'original_name' => $originalName,
                                'mime_type'     => $mimeType,
                                'file_size'     => $fileSize,
                            ]);

                            Log::info("Document saved", [
                                'pr_request_id' => $prRequest->id,
                                'file_name' => $fileName
                            ]);
                        }
                    } else {
                        Log::info("No files for index {$index}");
                    }
                }

                return $ticket;
            });

            try {
                $hodEmail = User::find($hodId)->email ?? 'ariffalkzn@gmail.com';
                Mail::to($hodEmail)->queue(new TestEmail([
                    'ticket' => $newTicket->ticketCode,
                    'status' => $newTicket->status
                ]));

                Notification::create([
                    'user_id'       => $userId,
                    'pr_ticket_id'  => $newTicket->id,
                    'status'        => $newTicket->status,
                    'message'       => "Your PR request {$newTicket->ticketCode} has been submitted and is {$newTicket->status}.",
                ]);

                Notification::create([
                    'user_id'       => $hodId,
                    'pr_ticket_id'  => $newTicket->id,
                    'status'        => $newTicket->status,
                    'message'       => "A new PR request {$newTicket->ticketCode} from {$user->name} is {$newTicket->status} for your approval.",
                ]);

                $newTicket->logHistory('created', null, $newTicket->toArray(), null);
            } catch (\Exception $notifyEx) {
                Log::warning('PR notification failed (non-critical)', ['error' => $notifyEx->getMessage()]);
            }

            return response()->json(['message' => 'New Request Successfully added']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', $e->errors());
            return response()->json(['error' => 'Validation failed: ' . implode(' ', $e->errors()->all())], 422);

        } catch (\Exception $e) {
            Log::error('PR creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function retrievePartDetails(Request $request)
    {
        $partName = $request->input('partName');
        $part = partList::find($partName);

        if (!$part) {
            Log::error('Part not found', ['partName' => $partName]);
            return response()->json(['error' => 'Part not found'], 404);
        }

        $stock = $this->getCurrentStock($part->id);
        Log::info('Retrieved part details', ['part_id' => $part->id, 'stock' => $stock]);

        return response()->json([
            'part' => [
                'id' => $part->id,
                'UoM' => $part->UoM,
                'category' => $part->category,
                'type' => $part->type,
                'name' => $part->part_name,
            ],
            'stock' => $stock,
        ]);
    }

    protected function getCurrentStock($partId)
    {
        $part = partList::findOrFail($partId);

        // Jika part tidak memerlukan pengurangan stok, return 'false'
        if ($part->requires_stock_reduction === 'false' || $part->requires_stock_reduction === 0) {
            return 'false';
        }

        $plus = $part->PartStock->where('operations', 'plus')->sum('quantity');
        $minus = $part->PartStock->where('operations', 'minus')->sum('quantity');
        $stock = max($plus - $minus, 0); // 

        if ($part->current_stock != $stock) {
            $part->current_stock = $stock;
            $part->save();
            \Log::info("Stock synced for part ID {$partId}: {$stock}");
        }

        return $stock;
    }


    public function show($id)
    {
        $user = auth()->user();
        $ticket = prTicket::findOrFail($id);

        if ($user->role !== 'admin' && $user->role !== 'purchasing' && $user->role !== 'hod') {
            if ($user->role === 'hod') {
                $dept = deptList::where('user_hod_id', $user->id)->first();
                if (!$dept || !User::where('id', $ticket->user_id)->where('dept_id', $dept->id)->exists()) {
                    return response()->json(['error' => 'Unauthorized to view this ticket'], 403);
                }
            } else {
                if ($ticket->user_id !== $user->id) {
                    return response()->json(['error' => 'Unauthorized to view this ticket'], 403);
                }
            }
        }

        $ticketRequests = prRequest::with('documents')->where('ticket_id', $id)->get();

        $advanceCash = $ticket->advance_cash ?? 0;

        $requester = User::find($ticket->user_id);
        $approver = $ticket->approved_user_id ? User::find($ticket->approved_user_id) : null;
        $purchasingApprover = $ticket->purchasing_approved_user_id ? User::find($ticket->purchasing_approved_user_id) : null;

        $ticketRequests = $ticketRequests->map(function ($pr) {
            return [
                'id' => $pr->id,
                'ticket_id' => $pr->ticket_id,
                'partlist_id' => $pr->partlist_id,
                'part_name' => $pr->part_name,
                'qty' => $pr->qty,
                'amount' => $pr->amount,
                'other_cost' => $pr->other_cost,
                'vendor' => $pr->vendor,
                'category' => $pr->category,
                'tag' => $pr->tag,
                'remark' => $pr->remark,
                'documents' => $pr->documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'original_name' => $doc->original_name,
                        'file_path' => $doc->file_path,
                    ];
                })->toArray(),
            ];
        });

        return response()->json([
            'advance_cash' => $advanceCash,
            'pr_requests' => $ticketRequests,
            'requester_name' => $requester ? $requester->name : 'Unknown',
            'approver_name' => $approver ? $approver->name : 'None',
            'purchasing_approver_name' => $purchasingApprover ? $purchasingApprover->name : 'None',
            'ticket' => [
                'ticketCode' => $ticket->ticketCode,
                'status' => $ticket->status,
                'reason_reject' => $ticket->reason_reject,
                'date_approval' => $ticket->date_approval,
                'date_purchasing_approval' => $ticket->date_purchasing_approval,
                'date_checked' => $ticket->date_checked,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
            ]
        ]);
    }

    public function retrievePartName($id) {
        $partList = partList::find($id);
        if($partList->requires_stock_reduction == 'false'){
            $newStock = "false";
        }else{
            $stock = $partList->PartStock->where('operations', 'plus')->sum('quantity') - $partList->PartStock->where('operations', 'minus')->sum('quantity');
            $newStock = $stock;
        }

        if ($partList) {
            return response()->json([
                'part_name' => $partList->part_name,
                'stock' => $newStock
            ]);
        } else {
            return response()->json(['error' => 'Part not found'], 404);
        }
    }

    public function pending()
    {
        $user = auth()->user();

        if ($user->role === 'admin' || $user->role === 'purchasing' || $user->role === 'pic') {
            // Admin and Purchasing can see all pending tickets
            $dataT = prTicket::whereIn('status', ['Pending', 'Revised', 'HOD_Approved'])->get();
        } elseif ($user->role === 'hod') {
            // HOD can see pending tickets from their department
            $dept = deptList::where('user_hod_id', $user->id)->first();
            if ($dept) {
                $deptUserIds = User::where('dept_id', $dept->id)->pluck('id');
                $dataT = prTicket::whereIn('user_id', $deptUserIds)
                                 ->whereIn('status', ['Pending', 'Revised', 'HOD_Approved'])
                                 ->get();
            } else {
                $dataT = collect(); 
            }
        } else {
            // Clerk can see their own pending tickets, including HOD_Approved
            $dataT = prTicket::where('user_id', $user->id)
                             ->whereIn('status', ['Pending', 'Revised', 'HOD_Approved'])
                             ->get();
        }

        // return $dataT; 

        return view('pr.pending_pr', [
            'dataT' => $dataT
        ]);
    }

    public function approved()
    {
        $user = auth()->user();

        if ($user->role === 'admin' || $user->role === 'purchasing' || $user->role === 'pic') {
            // Admin and Purchasing can see all approved tickets
            $dataT = prTicket::where('status', 'Approved')->get();
        } elseif ($user->role === 'hod') {
            // HOD can see approved tickets from their department
            $dept = deptList::where('user_hod_id', $user->id)->first();
            if ($dept) {
                $deptUserIds = User::where('dept_id', $dept->id)->pluck('id');
                $dataT = prTicket::whereIn('user_id', $deptUserIds)
                                 ->where('status', 'Approved')
                                 ->get();
            } else {
                $dataT = collect();
            }
        } else {
            // Clerk can only see their own approved tickets
            $dataT = prTicket::where('user_id', $user->id)
                             ->where('status', 'Approved')
                             ->get();
        }

        return view('pr.approved_pr', [
            'dataT' => $dataT
        ]);
    }

    public function rejected()
    {
        $user = auth()->user();

        if ($user->role === 'admin' || $user->role === 'purchasing' || $user->role === 'pic') {
            // Admin and Purchasing can see all rejected + canceled tickets
            $dataT = prTicket::whereIn('status', ['Rejected', 'Canceled'])->get();
        } elseif ($user->role === 'hod') {
            // HOD can see rejected + canceled from their department
            $dept = deptList::where('user_hod_id', $user->id)->first();
            if ($dept) {
                $deptUserIds = User::where('dept_id', $dept->id)->pluck('id');
                $dataT = prTicket::whereIn('user_id', $deptUserIds)
                                ->whereIn('status', ['Rejected', 'Canceled'])
                                ->get();
            } else {
                $dataT = collect();
            }
        } else {
            // Clerk can only see their own rejected + canceled tickets
            $dataT = prTicket::where('user_id', $user->id)
                            ->whereIn('status', ['Rejected', 'Canceled'])
                            ->get();
        }

        return view('pr.rejected_pr', [
            'dataT' => $dataT
        ]);
    }

    public function destroy($id)
    {
        try {
            // Checking roles for deletion - admin, owner, or HOD only
            $ticket = prTicket::findOrFail($id);
            $user = auth()->user();
            $isOwner = $ticket->user_id === $user->id;
            $dept = deptList::where('user_hod_id', $user->id)->first();
            $isHod = ($user->role === 'hod') && $dept && User::where('id', $ticket->user_id)->where('dept_id', $dept->id)->exists();
            if ($user->role !== 'admin' && !$isOwner && !$isHod) {
                return response()->json(['error' => 'Unauthorized to delete this ticket'], 403);
            }

            DB::beginTransaction();

            $ticket = prTicket::with(['user.deptList', 'prRequest'])->findOrFail($id);

            $userNotification = Notification::create([
                'user_id' => $ticket->user_id,
                'pr_ticket_id' => $ticket->id,
                'status' => 'Deleted',
                'message' => "Your PR request {$ticket->ticketCode} has been deleted."
            ]);
            $this->sendSkypeNotification($ticket->user_id, $userNotification->message);

            $hodId = $ticket->user->deptList->user_hod_id ?? null;
            if ($hodId) {
                $hodNotification = Notification::create([
                    'user_id' => $hodId,
                    'pr_ticket_id' => $ticket->id,
                    'status' => 'Deleted',
                    'message' => "PR request {$ticket->ticketCode} from {$ticket->user->name} has been deleted."
                ]);
                $this->sendSkypeNotification($hodId, $hodNotification->message);
            }

            if ($ticket->purchasing_approved_user_id) {
                $picNotification = Notification::create([
                    'user_id' => $ticket->purchasing_approved_user_id,
                    'pr_ticket_id' => $ticket->id,
                    'status' => 'Deleted',
                    'message' => "PR request {$ticket->ticketCode} has been deleted."
                ]);
                $this->sendSkypeNotification($ticket->purchasing_approved_user_id, $picNotification->message);
            }

            $totalQuantities = $ticket->prRequest->groupBy('partlist_id')->map(function ($requests) {
                return $requests->sum('qty');
            });

            $sa = 'PR No. ' . $ticket->ticketCode;
            $stockCreated = false;

            foreach ($totalQuantities as $partlist_id => $quantity) {
                $part = PartList::find($partlist_id);
                if (!$part) {
                    continue;
                }

                if ($part->requires_stock_reduction !== "false" && (int)$part->requires_stock_reduction > 0) {
                    $part->requires_stock_reduction = (int)$part->requires_stock_reduction + $quantity;
                    $part->save();

                    PartStock::create([
                        'part_list_id' => $partlist_id,
                        'quantity'     => $quantity,
                        'operations'   => 'plus',
                        'source'       => 'PR Request Canceled',
                        'source_type'  => 'pr_request_cancel',
                        'source_ref'   => $ticket->ticketCode
                    ]);
                }
            }

            $ticket->delete();
            DB::commit();

            $allParts = $ticket->prRequest()->pluck('partlist_id')->unique();
            foreach ($allParts as $partId) {
                $this->getCurrentStock($partId);
            }

            return response()->json(['message' => 'Ticket successfully deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to delete ticket',
                'debug' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
            \Log::error('Error deleting ticket: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete ticket'], 500);
        }
    }

    public function destroyPart($id)
    {
        // ADD: Checking roles for part deletion - admin, owner, or HOD only
        $part = prRequest::findOrFail($id);
        $ticket = prTicket::findOrFail($part->ticket_id);
        $user = auth()->user();
        $isOwner = $ticket->user_id === $user->id;
        $dept = deptList::where('user_hod_id', $user->id)->first();
        $isHod = ($user->role === 'hod') && $dept && User::where('id', $ticket->user_id)->where('dept_id', $dept->id)->exists();
        if ($user->role !== 'admin' && !$isOwner && !$isHod) {
            return response()->json(['error' => 'Unauthorized to delete this part'], 403);
        }

        $part = prRequest::findOrFail($id);

        $quantity = $part->qty;
        $partlist_id = $part->partlist_id;
        $ticketId = $part->ticket_id; 
        $ticketCode = prTicket::find($ticketId)->ticketCode;

        $getPart = PartList::find($partlist_id);
        $getFalse = $getPart->requires_stock_reduction;

        if($getFalse !== "false"){
            PartStock::create([
                'part_list_id' => $partlist_id,
                'quantity'     => $quantity,
                'operations'   => 'plus',
                'source'       => 'PR Item Canceled',
                'source_type'  => 'pr_request_cancel',
                'source_ref'   => $ticketCode
            ]);
        }

        $part->delete();
    }

    public function approveTicket(Request $request, $id)
    {
        try {
            $ticket = prTicket::findOrFail($id);
            $currentUser = auth()->user();

            // Authorization: hanya HOD, admin, PIC atau purchasing yang boleh approve
            if (!in_array($currentUser->role, ['hod', 'admin', 'pic', 'purchasing'])) {
                return response()->json(['error' => 'Unauthorized to approve this ticket'], 403);
            }

            // pending revised only
            if (!in_array($ticket->status, ['Pending', 'Revised'])) {
                return response()->json(['error' => 'Ticket must be Pending or Revised to approve'], 400);
            }

            // Update ticket status
            $ticket->status = 'HOD_Approved';
            $ticket->date_approval = date('Y-m-d');
            $ticket->approved_user_id = $currentUser->id;
            $ticket->save();
            $ticket->refresh(); 

            $allParts = $ticket->prRequest()->pluck('partlist_id')->unique();
            foreach ($allParts as $partId) {
                $this->getCurrentStock($partId);
            }

            $picId = $ticket->purchasing_approved_user_id;
            if ($picId) {
                $picEmail = User::find($picId)->email ?? null;
                if ($picEmail) {
                    Mail::to($picEmail)->send(new TestEmail([
                        'ticket' => $ticket->ticketCode,
                        'status' => $ticket->status
                    ]));
                }
            }

            Notification::create([
                'user_id' => $ticket->user_id,
                'pr_ticket_id' => $ticket->id,
                'status' => $ticket->status,
                'message' => "Your PR request {$ticket->ticketCode} has been approved by HOD and is now pending purchasing approval."
            ]);

            if ($picId) {
                Notification::create([
                    'user_id' => $picId,
                    'pr_ticket_id' => $ticket->id,
                    'status' => $ticket->status,
                    'message' => "PR request {$ticket->ticketCode} from {$ticket->user->name} has been approved by HOD and is pending your approval."
                ]);
            }

            $userHodId = $ticket->user->deptList->user_hod_id ?? null;
            if ($userHodId) {
                $userDeptHodEmail = User::find($userHodId)->email ?? null;
                if ($userDeptHodEmail) {
                    Mail::to($userDeptHodEmail)->send(new TestEmail([
                        'ticket' => $ticket->ticketCode,
                        'status' => $ticket->status
                    ]));
                }
            }

            return response()->json(['message' => 'Ticket successfully approved by HOD']);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function purchasingApprove($requestId)
    {
        try {
            $ticket = prTicket::with('user')->findOrFail($requestId);
            $ticket = $ticket->fresh();

            $currentUser = auth()->user();

            if (!in_array($currentUser->role, ['purchasing', 'pic', 'admin'])) {
                return response()->json(['error' => 'Unauthorized to approve this ticket'], 403);
            }

            if ($ticket->status !== 'HOD_Approved') {
                return response()->json(['error' => 'Ticket must be HOD_Approved to be approved by purchasing'], 400);
            }

            $ticket->status = 'Approved';
            $ticket->date_purchasing_approval = date('Y-m-d');
            $ticket->purchasing_approved_user_id = $currentUser->id;
            $ticket->save();

            $ticketId = $ticket->id;
            $userId = $ticket->user_id;
            $ticketCode = $ticket->ticketCode;

            $userEmail = optional($ticket->user)->email ?? 'ariffalkzn@gmail.com';
            Mail::to($userEmail)->send(new TestEmail([
                'ticket' => $ticketCode,
                'status' => $ticket->status
            ]));

            if ($ticketId && $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'pr_ticket_id' => $ticketId,
                    'status' => $ticket->status,
                    'message' => "Your PR request {$ticketCode} has been fully approved by purchasing."
                ]);
            }

            if ($ticketId && $currentUser->id) {
                Notification::create([
                    'user_id' => $currentUser->id,
                    'pr_ticket_id' => $ticketId,
                    'status' => $ticket->status,
                    'message' => "You have approved PR request {$ticketCode} from " . optional($ticket->user)->name
                ]);
            }

            $allParts = $ticket->prRequest()->pluck('partlist_id')->unique();
            foreach ($allParts as $partId) {
                $this->getCurrentStock($partId);
            }

            return response()->json(['message' => 'The ticket has been successfully approved by purchasing']);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    public function test()
    {
        $ticket = prTicket::with('prRequest')->findOrFail(27);

        $totalQuantities = $ticket->prRequest->groupBy('partlist_id')->map(function ($requests) {
            return $requests->sum('qty');
        });

        $sa = 'PR No. ' . $ticket->ticketCode;

        foreach ($totalQuantities as $partlist_id => $quantity) {
            $getPart = PartList::find($partlist_id);
            $getFalse = $getPart->requires_stock_reduction;

            if($getFalse !== "false"){
                $stock = PartStock::create([
                    'operations' => 'plus',
                    'quantity' => $quantity,
                    'source' => $sa,
                    'part_list_id' => $partlist_id,
                ]);
            }
        }
    }

    public function rejectTicket(Request $request, $id)
    {
        try {
            $ticket = prTicket::findOrFail($id);
            $currentUser = auth()->user();

            // Authorization check based on hierarchy
            if (in_array($ticket->status, ['Pending', 'Revised'])) {
                if (
                    $currentUser->id !== $ticket->approved_user_id &&
                    !in_array($currentUser->role, ['admin', 'purchasing'])
                ) {
                    return response()->json(['error' => 'Unauthorized to reject this ticket'], 403);
                }
            } elseif ($ticket->status === 'HOD_Approved') {
                if (!in_array($currentUser->role, ['purchasing', 'pic', 'admin'])) {
                    return response()->json(['error' => 'Unauthorized to reject this ticket'], 403);
                }
            } else {
                return response()->json(['error' => 'Ticket not in a rejectable state'], 400);
            }

            $rejectorName = $currentUser->name . ' (' . ($currentUser->deptList->dept_code ?? 'N/A') . ')';

            $reasonInput = $request->input('reason');
            $ticket->status = 'Rejected';
            $ticket->reason_reject = "{$reasonInput} — Rejected by {$rejectorName}";
            $ticket->save();
            $ticket->refresh(); 

            $allParts = $ticket->prRequest()->pluck('partlist_id')->unique();
            foreach ($allParts as $partId) {
                $this->getCurrentStock($partId);
            }

            if ($ticket->wasChanged('status')) {
                $data = [
                    'ticket' => $ticket->ticketCode,
                    'status' => $ticket->status,
                ];

                $userHodId = $ticket->user->deptList->user_hod_id ?? null;
                if ($userHodId) {
                    $userDeptHodEmail = User::find($userHodId)->email;
                    Mail::to($userDeptHodEmail)->send(new TestEmail($data));
                }

                Notification::create([
                    'user_id' => $ticket->user_id,
                    'pr_ticket_id' => $ticket->id,
                    'status' => $ticket->status,
                    'message' => "Your PR request {$ticket->ticketCode} has been rejected by {$rejectorName}. Reason: {$reasonInput}"
                ]);

                if ($userHodId) {
                    Notification::create([
                        'user_id' => $userHodId,
                        'pr_ticket_id' => $ticket->id,
                        'status' => $ticket->status,
                        'message' => "PR request {$ticket->ticketCode} from {$ticket->user->name} has been rejected by {$rejectorName}."
                    ]);
                }
            }

            return response()->json(['message' => 'Ticket rejected successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cancelTicket(Request $request, $id)
    {
        try {
            $currentUser = auth()->user();

            $ticket = prTicket::with(['prRequest.documents', 'user.deptList'])->findOrFail($id);

            $isOwner = $ticket->user_id === $currentUser->id;
            $isHod = ($currentUser->role === 'hod') && 
                    $ticket->user->deptList && 
                    $ticket->user->deptList->user_hod_id === $currentUser->id;
            $isPrivileged = in_array($currentUser->role, ['admin', 'purchasing', 'hod']);

            if (!$isOwner && !$isPrivileged) {
                return response()->json(['error' => 'Unauthorized to cancel this ticket'], 403);
            }

            if (!in_array($ticket->status, ['Pending', 'Revised', 'HOD_Approved'])) {
                return response()->json(['error' => 'Ticket cannot be canceled in current state'], 400);
            }

            $reason = $request->input('reason', 'No reason provided');
            $canceler = $currentUser->name . ' (' . ($currentUser->deptList->dept_code ?? 'N/A') . ')';

            $ticket->status = 'Canceled';
            $ticket->reason_reject = "{$reason} — Canceled by {$canceler}";
            $ticket->save();

            $ticket->load('prRequest.documents');

            foreach ($ticket->prRequest as $pr) {
                $part = PartList::find($pr->partlist_id);
                if ($part && $part->requires_stock_reduction !== "false") {
                    PartStock::create([
                        'part_list_id' => $part->id,
                        'quantity'     => $pr->qty,
                        'operations'   => 'plus',
                        'source'       => 'PR Request Canceled',
                        'source_type'  => 'pr_request_cancel',
                        'source_ref'   => $ticket->ticketCode
                    ]);
                }

                foreach ($pr->documents as $doc) {
                    $filePath = public_path($doc->file_path);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                $pr->documents()->delete();
                $pr->delete(); 
            }

            $partIds = $ticket->prRequest()
                ->withTrashed()
                ->pluck('partlist_id')
                ->unique();

            foreach ($partIds as $partId) {
                $this->getCurrentStock($partId);
            }

            $userHodId = $ticket->user->deptList->user_hod_id ?? null;

            Notification::create([
                'user_id' => $ticket->user_id,
                'pr_ticket_id' => $ticket->id,
                'status' => 'Canceled',
                'message' => "Your PR {$ticket->ticketCode} has been canceled by {$canceler}. Reason: {$reason}"
            ]);

            if ($userHodId) {
                Notification::create([
                    'user_id' => $userHodId,
                    'pr_ticket_id' => $ticket->id,
                    'status' => 'Canceled',
                    'message' => "PR {$ticket->ticketCode} from {$ticket->user->name} was canceled by {$canceler}."
                ]);
            }

            return response()->json(['message' => 'Ticket canceled successfully']);
        } catch (\Exception $e) {
            \Log::error('Cancel Error: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    public function print($ticketCode)
    {
        $user = auth()->user();
        $data = prTicket::where('ticketCode', $ticketCode)->firstOrFail();

        // Authorization check based on hierarchy
        if ($user->role !== 'admin' && $user->role !== 'purchasing' && $user->role !== 'pic') {
            if ($user->role === 'hod') {
                $dept = deptList::where('user_hod_id', $user->id)->first();
                if (!$dept || !User::where('id', $data->user_id)->where('dept_id', $dept->id)->exists()) {
                    return redirect()->route('dashboard')->with('error', 'Unauthorized to view this ticket');
                }
            } else {
                if ($data->user_id !== $user->id) {
                    return redirect()->route('dashboard')->with('error', 'Unauthorized to view this ticket');
                }
            }
        }

        if (!in_array($data->status, ['HOD_Approved', 'Approved'])) {
            return redirect()->route('dashboard')->with('error', 'Ticket must be HOD_Approved or Approved to print');
        }

        $price = prTicket::find($data->id)->prRequest()->sum('amount');
        $qty = prTicket::find($data->id)->prRequest()->sum('qty');
        $otherCost = prTicket::find($data->id)->prRequest()->sum('other_cost');
        $priceXqty = $price * $qty;
        $priceTotal = $priceXqty + $otherCost;

        return view('print.print', [
            'dataT' => prTicket::find($data->id),
            'dataN' => $priceTotal,
            'dataU' => User::find($data->approved_user_id)
        ]);
    }

    public function printPdf($ticketCode)
    {
        $user = auth()->user();
        $data = prTicket::where('ticketCode', $ticketCode)->firstOrFail();

        // Authorization check based on hierarchy
        if ($user->role !== 'admin' && $user->role !== 'purchasing' && $user->role !== 'pic') {
            if ($user->role === 'hod') {
                $dept = deptList::where('user_hod_id', $user->id)->first();
                if (!$dept || !User::where('id', $data->user_id)->where('dept_id', $dept->id)->exists()) {
                    return redirect()->back()->with('error', 'Unauthorized to view this ticket');
                }
            } else {
                if ($data->user_id !== $user->id) {
                    return redirect()->back()->with('error', 'Unauthorized to view this ticket');
                }
            }
        }

        if (!in_array($data->status, ['HOD_Approved', 'Approved'])) {
            return redirect()->back()->with('error', 'Ticket must be HOD_Approved or Approved to print');
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $html = view('printTicket', [
            'dataT' => prTicket::find($data->id),
            'dataN' => prTicket::find($data->id)->prRequest()->sum('amount'),
            'dataU' => User::find($data->approved_user_id)
        ])->render();
        $html = "<style>" . file_get_contents(public_path('css/style.css')) . "</style>" . $html;
        $dompdf->loadHtml($html);
        $dompdf->render();

        return $dompdf->stream('invoice.pdf');
    }

    public function update(Request $request)
    {
        try {
            $ticketId = null;
            $submittedIds = [];
            $processedIds = [];

            // Collect submitted IDs and get ticket ID
            foreach ($request->pr_request ?? [] as $pRQ) {
                if (isset($pRQ['id']) && !in_array($pRQ['id'], $submittedIds)) {
                    $submittedIds[] = $pRQ['id'];
                }
                if (!$ticketId && isset($pRQ['ticket_id'])) {
                    $ticketId = $pRQ['ticket_id'];
                }
            }

            if (!$ticketId) {
                return response()->json(['error' => 'Ticket ID not found'], 400);
            }

            $ticket = prTicket::findOrFail($ticketId);
            $user = auth()->user();
            $isOwner = $ticket->user_id === $user->id;
            $dept = deptList::where('user_hod_id', $user->id)->first();
            $isHod = ($user->role === 'hod') && $dept && User::where('id', $ticket->user_id)->where('dept_id', $dept->id)->exists();
            if ($user->role !== 'admin' && !$isOwner && !$isHod) {
                return response()->json(['error' => 'Unauthorized to update this ticket'], 403);
            }

            // Existing PR requests
            $existingPrRequests = prRequest::where('ticket_id', $ticketId)->get();
            $existingIds = $existingPrRequests->pluck('id')->toArray();
            $idsToDelete = array_diff($existingIds, $submittedIds);

            // Delete PR requests if needed
            foreach ($idsToDelete as $deleteId) {
                $prToDelete = prRequest::find($deleteId);
                if ($prToDelete) {
                    // disk removal
                    foreach ($prToDelete->documents as $doc) {
                        $filePath = public_path($doc->file_path);
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }

                    $part = PartList::find($prToDelete->partlist_id);
                    if ($part && $part->requires_stock_reduction !== "false") {
                        PartStock::create([
                            'part_list_id' => $part->id,
                            'quantity'     => $prToDelete->qty,
                            'operations'   => 'plus',
                            'source'       => 'PR Request Canceled',
                            'source_type'  => 'pr_request_cancel',
                            'source_ref'   => $ticket->ticketCode
                        ]);
                    }

                    $prToDelete->documents()->delete(); 
                    $prToDelete->delete();              
                }
            }

            foreach ($request->pr_request ?? [] as $pRQ) {
                if (!isset($pRQ['id']) || in_array($pRQ['id'], $processedIds)) continue;

                $newRequest = prRequest::find($pRQ['id']);
                if (!$newRequest) continue;

                if ($pRQ['qty'] != $newRequest->qty) {
                    $quantityDifference = abs($pRQ['qty'] - $newRequest->qty);
                    $part = PartList::find($newRequest->partlist_id);
                    if ($part && $part->requires_stock_reduction !== "false") {
                        PartStock::create([
                            'part_list_id' => $part->id,
                            'quantity'     => $quantityDifference,
                            'operations'   => ($pRQ['qty'] < $newRequest->qty) ? 'plus' : 'minus',
                            'source'       => 'PR Request Updated',
                            'source_type'  => 'pr_request_update',
                            'source_ref'   => $ticket->ticketCode
                        ]);
                    }
                }

                $newRequest->update([
                    'qty' => $pRQ['qty'],
                    'amount' => $pRQ['amount'],
                    'other_cost' => $pRQ['other_cost'],
                    'vendor' => $pRQ['vendor'],
                    'remark' => $pRQ['remark'],
                    'category' => $pRQ['category'],
                    'tag' => $pRQ['tag']
                ]);

                $processedIds[] = $pRQ['id'];
            }

            $ticket->status = 'Revised';
            $ticket->advance_cash = $request->input('advance_cash');
            $ticket->save();

            $userHodId = $ticket->user->deptList->user_hod_id ?? null;
            $userDeptHodEmail = $userHodId ? User::find($userHodId)->email : null;
            if ($userDeptHodEmail) {
                Mail::to($userDeptHodEmail)->send(new TestEmail([
                    'ticket' => $ticket->ticketCode,
                    'status' => $ticket->status
                ]));
            }

            Notification::create([
                'user_id' => $ticket->user_id,
                'pr_ticket_id' => $ticket->id,
                'status' => $ticket->status,
                'message' => "Your PR request {$ticket->ticketCode} has been Revised. Please review the changes."
            ]);
            if ($userHodId) {
                Notification::create([
                    'user_id' => $userHodId,
                    'pr_ticket_id' => $ticket->id,
                    'status' => $ticket->status,
                    'message' => "PR request {$ticket->ticketCode} from {$ticket->user->name} has been Revised and needs your review."
                ]);
            }

            $allParts = $ticket->prRequest()->pluck('partlist_id')->unique();
            foreach ($allParts as $partId) {
                $this->getCurrentStock($partId);
            }

            return response()->json(['message' => 'Request successfully saved']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function updateR(Request $request)
    {
        try {
            $ticketId = null;
            $submittedIds = [];
            $processedIds = [];

            foreach ($request->pr_request ?? [] as $pRQ) {
                if (isset($pRQ['id']) && !in_array($pRQ['id'], $submittedIds)) {
                    $submittedIds[] = $pRQ['id'];
                }
                if (!$ticketId && isset($pRQ['ticket_id'])) {
                    $ticketId = $pRQ['ticket_id'];
                }
            }

            if (!$ticketId) {
                return response()->json(['error' => 'Ticket ID not found'], 400);
            }

            $ticket = prTicket::with('user.deptList')->findOrFail($ticketId);
            $user = auth()->user();
            $isOwner = $ticket->user_id === $user->id;
            $dept = deptList::where('user_hod_id', $user->id)->first();
            $isHod = ($user->role === 'hod') && $dept && User::where('id', $ticket->user_id)->where('dept_id', $dept->id)->exists();

            if ($user->role !== 'admin' && !$isOwner && !$isHod) {
                return response()->json(['error' => 'Unauthorized to update this ticket'], 403);
            }

            $existingPrRequests = prRequest::where('ticket_id', $ticketId)->get();
            $idsToDelete = array_diff($existingPrRequests->pluck('id')->toArray(), $submittedIds);

            foreach ($idsToDelete as $deleteId) {
                $prToDelete = prRequest::find($deleteId);
                if ($prToDelete) {
                    foreach ($prToDelete->documents as $doc) {
                        $filePath = public_path($doc->file_path);
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                    }

                    $part = PartList::find($prToDelete->partlist_id);
                    if ($part && $part->requires_stock_reduction !== "false") {
                        PartStock::create([
                            'part_list_id' => $part->id,
                            'quantity'     => $prToDelete->qty,
                            'operations'   => 'plus',
                            'source'       => 'PR Request Canceled',
                            'source_type'  => 'pr_request_cancel',
                            'source_ref'   => $ticket->ticketCode
                        ]);
                    }

                    $prToDelete->documents()->delete(); 
                    $prToDelete->delete();              
                }
            }

            foreach ($request->pr_request ?? [] as $pRQ) {
                if (in_array($pRQ['id'], $processedIds)) continue;

                $newRequest = prRequest::find($pRQ['id']);
                if (!$newRequest) continue;

                if ($pRQ['qty'] != $newRequest->qty) {
                    $quantityDifference = abs($pRQ['qty'] - $newRequest->qty);
                    $part = PartList::find($newRequest->partlist_id);
                    if ($part && $part->requires_stock_reduction !== "false") {
                        PartStock::create([
                            'part_list_id' => $part->id,
                            'quantity' => $quantityDifference,
                            'operations' => ($pRQ['qty'] < $newRequest->qty) ? 'plus' : 'minus',
                            'source' => 'PR Request Updated (R)',
                            'source_type' => 'pr_request_update',
                            'source_ref' => $ticket->ticketCode
                        ]);
                    }
                }

                $newRequest->update([
                    'qty' => $pRQ['qty'],
                    'amount' => $pRQ['amount'],
                    'other_cost' => $pRQ['other_cost'],
                    'vendor' => $pRQ['vendor'],
                    'remark' => $pRQ['remark'],
                    'category' => $pRQ['category'],
                    'tag' => $pRQ['tag']
                ]);

                $processedIds[] = $pRQ['id'];
            }

            $ticket->status = 'Revised';
            $ticket->advance_cash = $request->input('advance_cash');
            $ticket->save();

            $ticket->load('user.deptList');
            $userHodId = $ticket->user->deptList->user_hod_id ?? null;

            if ($userHodId) {
                $userDeptHodEmail = User::find($userHodId)->email;
                Mail::to($userDeptHodEmail)->send(new TestEmail([
                    'ticket' => $ticket->ticketCode,
                    'status' => $ticket->status
                ]));
            }

            Notification::create([
                'user_id' => $ticket->user_id,
                'pr_ticket_id' => $ticket->id,
                'status' => $ticket->status,
                'message' => "Your PR request {$ticket->ticketCode} has been Revised. Please review the changes."
            ]);

            if ($userHodId) {
                Notification::create([
                    'user_id' => $userHodId,
                    'pr_ticket_id' => $ticket->id,
                    'status' => $ticket->status,
                    'message' => "PR request {$ticket->ticketCode} from {$ticket->user->name} has been Revised and needs your review."
                ]);
            }

            $allParts = $ticket->prRequest()->pluck('partlist_id')->unique();
            foreach ($allParts as $partId) {
                $this->getCurrentStock($partId);
            }

            return response()->json(['message' => 'Request successfully saved']);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    protected function sendSkypeNotification($userId, $message)
    {
        $user = User::find($userId);
        if (!$user) {
            Log::error("[SkypeSim] User not found for Skype notification", ['userId' => $userId]);
            return;
        }

        $isSimulation = env('SKYPE_SIMULATION', true); // dev purpose default true

        $skypeId = $user->skype_id ?? 'fake_skype_id_' . $userId;

        if ($isSimulation) {
            $testEndpoint = "https://api.skype.com/v1/messages/send";
            $testPayload = [
                'recipient' => $skypeId,
                'message' => $message,
                'timestamp' => now()->toDateTimeString(),
            ];
            Log::info("[SkypeSim] (SIMULATION) Sending Skype Bot message...", [
                'endpoint' => $testEndpoint,
                'payload' => $testPayload,
            ]);
            Log::info("[SkypeSim] (SIMULATION) Skype Bot message recorded", [
                'to' => $user->name,
                'skype_id' => $skypeId,
                'message' => $message
            ]);
            return;
        }

        // --- PRODUCTION () ---
        
    }

}