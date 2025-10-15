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
                // If HOD is not assigned to a department, show no tickets
                $dataT = collect(); // Empty collection
            }
        } else {
            // Clerk or other roles can only see their own tickets
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
        $prefix = $currentYear . ' ' . $currentMonth . ' ';

        return DB::transaction(function () use ($prefix) {
            $lastTicket = prTicket::where('ticketCode', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderBy('ticketCode', 'desc')
                ->first();

            $ticketCount = $lastTicket ? (int) substr($lastTicket->ticketCode, -3) : 0;
            $ticketCount++;
            Log::info('Generating ticket code', ['prefix' => $prefix, 'ticketCount' => $ticketCount]);

            do {
                $ticketNumber = str_pad($ticketCount, 3, '0', STR_PAD_LEFT);
                $ticketCode = $prefix . $ticketNumber;
                Log::info('Trying ticketCode', ['ticketCode' => $ticketCode]);
                $ticketCount++;
            } while (prTicket::where('ticketCode', $ticketCode)->exists());

            return $ticketCode;
        });
    }

    public function create(Request $request)
    {
        try {
            //Checking roles for creation - only admins, HODs, or clerks can create
            if (!in_array(auth()->user()->role, ['admin', 'hod', 'clerk','regular', 'purchasing', 'pic'])) {
                return response()->json(['error' => 'Unauthorized to create ticket'], 403);
            }

            Log::info('Starting PR creation', ['request' => $request->all()]);

            $validatedData = $request->validate([
                'pr_request.*.part_name' => 'required|string|min:1',
                'pr_request.*.partlist_id' => 'required|integer|exists:part_lists,id',
                'pr_request.*.qty' => 'required|integer|min:1',
                'pr_request.*.amount' => 'nullable|numeric|min:0',
                'pr_request.*.remark' => 'nullable|string',
                'pr_request.*.other_cost' => 'required|numeric|min:0',
                'pr_request.*.vendor' => 'required|string|min:1',
                'pr_request.*.category' => 'required|string|min:1',
                'pr_request.*.tag' => 'required|string|min:1',
                'advance_cash' => 'nullable|numeric|min:0',
            ]);

            $userId = auth()->user()->id;
            $hodId = User::find($userId)->deptList->user_hod_id ?? $userId;
            $picUser = User::where('role', 'pic')->first();
            $picId = $picUser ? $picUser->id : $hodId; // Fallback to HOD if no PIC found
            Log::info('User info', ['userId' => $userId, 'hodId' => $hodId, 'picId' => $picId]);

            $ticketCode = $this->generateUniqueTicketCode();

            $newTicket = DB::transaction(function () use ($ticketCode, $userId, $hodId, $picId, $validatedData, $request) {
                $newTicket = prTicket::create([
                    'ticketCode' => $ticketCode,
                    'status' => 'Pending',
                    'user_id' => $userId,
                    'approved_user_id' => $hodId,
                    'purchasing_approved_user_id' => $picId,
                    'date_approval' => date('Y-m-d'),
                    'advance_cash' => $request->advance_cash ?? 0,
                ]);
                Log::info('Created ticket', ['ticketCode' => $ticketCode, 'ticketId' => $newTicket->id]);

                foreach ($validatedData['pr_request'] as $index => $prQ) {
                    $part = partList::findOrFail($prQ['partlist_id']);
                    $qty = (int) $prQ['qty'];

                    // Validate stock
                    $currentStock = $this->getCurrentStock($part->id);
                    Log::info('Checking stock', ['part_id' => $part->id, 'currentStock' => $currentStock, 'requestedQty' => $qty]);

                    if ($currentStock !== 'false' && is_numeric($currentStock)) {
                        $newStock = (int) $currentStock - $qty;
                        if ($newStock < 0) {
                            throw new Exception("Insufficient stock for part: {$part->part_name} (ID: {$part->id}). Requested: {$qty}, Available: {$currentStock}");
                        }
                        $part->requires_stock_reduction = $newStock;
                        $part->save();
                        Log::info('Updated stock', ['part_id' => $part->id, 'newStock' => $newStock]);
                    }

                    $prQ['ticket_id'] = $newTicket->id;
                    prRequest::create($prQ);
                    Log::info('Created PR request', ['partlist_id' => $prQ['partlist_id'], 'ticket_id' => $newTicket->id]);

                    if ($currentStock !== 'false' && is_numeric($currentStock)) {
                        PartStock::create([
                            'part_list_id' => $prQ['partlist_id'],
                            'quantity' => $qty,
                            'operations' => 'minus',
                            'source' => 'Issued for PR Request',
                            'source_type' => 'pr_request',
                            'source_ref' => $newTicket->ticketCode,
                        ]);
                        Log::info('Created PartStock entry', ['part_list_id' => $prQ['partlist_id'], 'quantity' => $qty]);
                    }
                }

                $data = ['ticket' => $newTicket->ticketCode, 'status' => $newTicket->status];
                $hodEmail = User::find($hodId)->email ?? 'ariffalkzn@gmail.com';
                Mail::to($hodEmail)->queue(new TestEmail($data));
                Log::info('Queued email', ['hodEmail' => $hodEmail]);

                Notification::create([
                    'user_id' => $userId,
                    'pr_ticket_id' => $newTicket->id,
                    'status' => $newTicket->status,
                    'message' => "Your PR request {$newTicket->ticketCode} has been submitted and is {$newTicket->status}.",
                ]);
                Notification::create([
                    'user_id' => $hodId,
                    'pr_ticket_id' => $newTicket->id,
                    'status' => $newTicket->status,
                    'message' => "A new PR request {$newTicket->ticketCode} from " . auth()->user()->name . " is {$newTicket->status} for your approval.",
                ]);
                Log::info('Created notifications', ['ticket_id' => $newTicket->id]);

                $newTicket->logHistory('created', null, $newTicket->toArray(), null);
                Log::info('Logged ticket history', ['ticket_id' => $newTicket->id]);

                return $newTicket;
            });

            return response()->json(['message' => 'New Request Successfully added']);

        } catch (QueryException $e) {
            Log::error('Database error in PR creation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            if ($e->getCode() === '23000') {
                return response()->json(['error' => 'Failed to generate a unique ticket code. Please try again.'], 409);
            }
            return response()->json(['error' => 'Database error occurred. Please try again.'], 500);
        } catch (Exception $e) {
            Log::error('Error in PR creation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return response()->json(['error' => $e->getMessage()], 422);
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
        if ($part->requires_stock_reduction === 'false') {
            return 'false';
        }

        $stock = $part->PartStock->where('operations', 'plus')->sum('quantity') - 
                 $part->PartStock->where('operations', 'minus')->sum('quantity');
        $stock = $stock >= 0 ? $stock : 0; // Prevent negative stock

        // Sync requires_stock_reduction
        if ($part->requires_stock_reduction !== $stock) {
            $part->requires_stock_reduction = $stock;
            $part->save();
            Log::info('Synced requires_stock_reduction', ['part_id' => $partId, 'new_stock' => $stock]);
        }

        return $stock;
    }

    public function show($id)
    {
        $user = auth()->user();
        $ticket = prTicket::findOrFail($id);

        // Authorization check based on hierarchy
        if ($user->role !== 'admin' && $user->role !== 'purchasing' && $user->role !== 'pic') {
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

        $ticketRequests = prRequest::where('ticket_id', $id)->get();
        $advanceCash = $ticket->advance_cash ?? 0;

        // Fetch user names
        $requester = User::find($ticket->user_id);
        $approver = $ticket->approved_user_id ? User::find($ticket->approved_user_id) : null;
        $purchasingApprover = $ticket->purchasing_approved_user_id ? User::find($ticket->purchasing_approved_user_id) : null;

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
                $dataT = collect(); // Empty collection if no department
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
                $dataT = collect(); // Empty collection if no department
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
            // Admin and Purchasing can see all rejected tickets
            $dataT = prTicket::where('status', 'Rejected')->get();
        } elseif ($user->role === 'hod') {
            // HOD can see rejected tickets from their department
            $dept = deptList::where('user_hod_id', $user->id)->first();
            if ($dept) {
                $deptUserIds = User::where('dept_id', $dept->id)->pluck('id');
                $dataT = prTicket::whereIn('user_id', $deptUserIds)
                                 ->where('status', 'Rejected')
                                 ->get();
            } else {
                $dataT = collect(); // Empty collection if no department
            }
        } else {
            // Clerk can only see their own rejected tickets
            $dataT = prTicket::where('user_id', $user->id)
                             ->where('status', 'Rejected')
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

            // MOVED: Load ticket with relations once (reuse for stock and notifications)
            $ticket = prTicket::with('prRequest')->findOrFail($id);

            // MOVED: Create notifications BEFORE delete (avoids FK constraint on commit)
            Notification::create([
                'user_id' => $ticket->user_id,
                'pr_ticket_id' => $ticket->id,
                'status' => 'Deleted',
                'message' => "Your PR request {$ticket->ticketCode} has been deleted."
            ]);

            $hodId = $ticket->user->deptList->user_hod_id ?? null;
            if ($hodId) {
                Notification::create([
                    'user_id' => $hodId,
                    'pr_ticket_id' => $ticket->id,
                    'status' => 'Deleted',
                    'message' => "PR request {$ticket->ticketCode} from {$ticket->user->name} has been deleted."
                ]);
            }

            if ($ticket->purchasing_approved_user_id) {
                Notification::create([
                    'user_id' => $ticket->purchasing_approved_user_id,
                    'pr_ticket_id' => $ticket->id,
                    'status' => 'Deleted',
                    'message' => "PR request {$ticket->ticketCode} has been deleted."
                ]);
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

                if ($part->requires_stock_reduction !== "false") {
                    PartStock::create([
                        'part_list_id' => $partlist_id,
                        'quantity'     => $quantity,
                        'operations'   => 'plus',
                        'source'       => 'PR Request Canceled',
                        'source_type'  => 'pr_request_cancel',
                        'source_ref'   => $ticket->ticketCode
                    ]);
                    $stockCreated = true;
                }
            }

            $ticket->delete();
            DB::commit();

            return response()->json(['message' => 'Ticket successfully deleted']);
        } catch (\Exception $e) {
            DB::rollBack();
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

        // Find the prRequest instance
        $part = prRequest::findOrFail($id);

        // Retrieve the required data
        $quantity = $part->qty;
        $partlist_id = $part->partlist_id;
        $ticketId = $part->ticket_id; 
        $ticketCode = prTicket::find($ticketId)->ticketCode;

        // Store the data in PartStock
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

    public function approveTicket($requestId)
    {
        $request = prTicket::findOrFail($requestId);

        // Allow hod, admin, or pic to approve if status is Pending or Revised
        if (!in_array(auth()->user()->role, ['hod', 'admin', 'pic', 'purchasing'])) {
            return response()->json(['error' => 'Unauthorized to approve this ticket'], 403);
        }

        // Ensure ticket is in Pending or Revised status
        if (!in_array($request->status, ['Pending', 'Revised'])) {
            return response()->json(['error' => 'Ticket must be in Pending or Revised status to be approved by HOD'], 400);
        }

        $request->status = 'HOD_Approved';
        $request->date_approval = date('Y-m-d');
        $request->approved_user_id = auth()->user()->id;
        $request->save();

        $data = ['ticket' => $request->ticketCode, 'status' => $request->status];

        $picId = $request->purchasing_approved_user_id;
        $picEmail = User::find($picId)->email ?? 'ariffalkzn@gmail.com';
        Mail::to($picEmail)->send(new TestEmail($data));

        // Notify user
        Notification::create([
            'user_id' => $request->user_id,
            'pr_ticket_id' => $request->id,
            'status' => $request->status,
            'message' => "Your PR request {$request->ticketCode} has been approved by HOD and is now pending purchasing approval."
        ]);

        // Notify purchasing
        if ($picId) {
            Notification::create([
                'user_id' => $picId,
                'pr_ticket_id' => $request->id,
                'status' => $request->status,
                'message' => "PR request {$request->ticketCode} from " . $request->user->name . " has been approved by HOD and is pending your approval."
            ]);
        }

        return response()->json(['message' => 'The ticket has been successfully approved by HOD']);
    }

    public function purchasingApprove($requestId)
    {
        $request = prTicket::findOrFail($requestId);

        // Allow purchasing, pic, or admin to approve any ticket in HOD_Approved 
        // dd(auth()->user()->role);
        if (!in_array(auth()->user()->role, ['purchasing', 'pic', 'admin'])) {
            return response()->json(['error' => 'Unauthorized to approve this ticket'], 403);
        }

        // Ensure ticket is in HOD_Approved status
        if ($request->status !== 'HOD_Approved') {
            return response()->json(['error' => 'Ticket must be HOD_Approved to be approved by purchasing'], 400);
        }

        $request->status = 'Approved';
        $request->date_purchasing_approval = date('Y-m-d');
        $request->purchasing_approved_user_id = auth()->user()->id;
        $request->save();

        $data = ['ticket' => $request->ticketCode, 'status' => $request->status];

        $userEmail = $request->user->email ?? 'ariffalkzn@gmail.com';
        Mail::to($userEmail)->send(new TestEmail($data));

        // Notify user
        Notification::create([
            'user_id' => $request->user_id,
            'pr_ticket_id' => $request->id,
            'status' => $request->status,
            'message' => "Your PR request {$request->ticketCode} has been fully approved by purchasing."
        ]);

        return response()->json(['message' => 'The ticket has been successfully approved by purchasing']);
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
        $ticket = prTicket::findOrFail($id);
        $currentUser = auth()->user();

        // Authorization check based on hierarchy
        if ($ticket->status === 'Pending' || $ticket->status === 'Revised') {
            if ($currentUser->id !== $ticket->approved_user_id && $currentUser->role !== 'admin') {
                return response()->json(['error' => 'Unauthorized to reject this ticket'], 403);
            }
        } elseif ($ticket->status === 'HOD_Approved') {
            if ($currentUser->role !== 'purchasing' && $currentUser->role !== 'pic' && $currentUser->role !== 'admin') {
                return response()->json(['error' => 'Unauthorized to reject this ticket'], 403);
            }
        } else {
            return response()->json(['error' => 'Ticket not in a rejectable state'], 400);
        }

        $ticket->status = 'Rejected';
        $ticket->reason_reject = $request->input('reason');
        $ticket->save();

        if ($ticket->save()) {
            $data = ['ticket' => $ticket->ticketCode, 'status' => $ticket->status];
            $userHodId = $ticket->user->deptList->user_hod_id;
            $userDeptHodEmail = User::find($userHodId)->email;

            Mail::to($userDeptHodEmail)->send(new TestEmail($data));

            // Notify user
            Notification::create([
                'user_id' => $ticket->user_id,
                'pr_ticket_id' => $ticket->id,
                'status' => $ticket->status,
                'message' => "Your PR request {$ticket->ticketCode} has been Rejected. Reason: {$request->input('reason')}"
            ]);
        }
        return response()->json(['message' => 'Ticket rejected successfully']);
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

            // Collect submitted IDs and determine ticket ID
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

            // ADD: Checking roles for update - admin, owner, or HOD only
            $ticket = prTicket::findOrFail($ticketId);
            $user = auth()->user();
            $isOwner = $ticket->user_id === $user->id;
            $dept = deptList::where('user_hod_id', $user->id)->first();
            $isHod = ($user->role === 'hod') && $dept && User::where('id', $ticket->user_id)->where('dept_id', $dept->id)->exists();
            if ($user->role !== 'admin' && !$isOwner && !$isHod) {
                return response()->json(['error' => 'Unauthorized to update this ticket'], 403);
            }

            // Get all existing PR requests for this ticket
            $existingPrRequests = prRequest::where('ticket_id', $ticketId)->get();
            $existingIds = $existingPrRequests->pluck('id')->toArray();

            // Identify IDs to delete (existing but not submitted)
            $idsToDelete = array_diff($existingIds, $submittedIds);

            // Delete removed PR requests and handle stock if necessary
            foreach ($idsToDelete as $deleteId) {
                $prToDelete = prRequest::find($deleteId);
                if ($prToDelete) {
                    // Optional: Handle stock addition back if previously deducted
                    // For example, if PR deducts stock on creation, add back
                    $getPart = PartList::find($prToDelete->partlist_id);
                    if ($getPart && $getPart->requires_stock_reduction !== "false") {
                        PartStock::create([
                            'part_list_id' => $prToDelete->partlist_id,
                            'quantity'     => $prToDelete->qty,
                            'operations'   => 'plus', // Add back to stock
                            'source'       => 'PR Request Deleted',
                            'source_type'  => 'pr_request_delete',
                            'source_ref'   => $prToDelete->prTicket->ticketCode
                        ]);
                    }
                    $prToDelete->delete();
                }
            }

            // Update submitted PR requests
            foreach ($request->pr_request ?? [] as $pRQ) {
                if (!isset($pRQ['id']) || in_array($pRQ['id'], $processedIds)) {
                    continue;
                }

                $newRequest = prRequest::find($pRQ['id']);
                if (!$newRequest) {
                    continue; // Skip if not found
                }

                $operations = ($pRQ['qty'] < $newRequest->qty) ? 'plus' : 'minus';

                if ($pRQ['qty'] != $newRequest->qty) {
                    $quantityDifference = abs($pRQ['qty'] - $newRequest->qty);

                    $getPart = PartList::find($newRequest->partlist_id);
                    $getFalse = $getPart->requires_stock_reduction;

                    if ($getFalse !== "false") {
                        PartStock::create([
                            'part_list_id' => $newRequest->partlist_id,
                            'quantity'     => $quantityDifference,
                            'operations'   => $operations,
                            'source'       => 'PR Request Updated',
                            'source_type'  => 'pr_request_update',
                            'source_ref'   => $newRequest->prTicket->ticketCode
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

            // Update ticket status and advance cash
            $revised = prTicket::findOrFail($ticketId);
            $revised->status = 'Revised';
            $revised->advance_cash = $request->input('advance_cash');
            $revised->save();

            // Send email and notification
            $data = ['ticket' => $revised->ticketCode, 'status' => $revised->status];
            $userHodId = $revised->user->deptList->user_hod_id;
            $userDeptHodEmail = User::find($userHodId)->email;

            Mail::to($userDeptHodEmail)->send(new TestEmail($data));

            // Notify user
            Notification::create([
                'user_id' => $revised->user_id,
                'pr_ticket_id' => $revised->id,
                'status' => $revised->status,
                'message' => "Your PR request {$revised->ticketCode} has been Revised. Please review the changes."
            ]);

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

            // Collect submitted IDs and determine ticket ID
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

            // ADD: Checking roles for update - admin, owner, or HOD only
            $ticket = prTicket::findOrFail($ticketId);
            $user = auth()->user();
            $isOwner = $ticket->user_id === $user->id;
            $dept = deptList::where('user_hod_id', $user->id)->first();
            $isHod = ($user->role === 'hod') && $dept && User::where('id', $ticket->user_id)->where('dept_id', $dept->id)->exists();
            if ($user->role !== 'admin' && !$isOwner && !$isHod) {
                return response()->json(['error' => 'Unauthorized to update this ticket'], 403);
            }

            // Get all existing PR requests for this ticket
            $existingPrRequests = prRequest::where('ticket_id', $ticketId)->get();
            $existingIds = $existingPrRequests->pluck('id')->toArray();

            // Identify IDs to delete (existing but not submitted)
            $idsToDelete = array_diff($existingIds, $submittedIds);

            // Delete removed PR requests and handle stock if necessary
            foreach ($idsToDelete as $deleteId) {
                $prToDelete = prRequest::find($deleteId);
                if ($prToDelete) {
                    // Optional: Handle stock addition back if previously deducted
                    // For example, if PR deducts stock on creation, add back
                    $getPart = PartList::find($prToDelete->partlist_id);
                    if ($getPart && $getPart->requires_stock_reduction !== "false") {
                        PartStock::create([
                            'part_list_id' => $prToDelete->partlist_id,
                            'quantity'     => $prToDelete->qty,
                            'operations'   => 'plus', // Add back to stock
                            'source'       => 'PR Request Deleted',
                            'source_type'  => 'pr_request_delete',
                            'source_ref'   => $prToDelete->prTicket->ticketCode
                        ]);
                    }
                    $prToDelete->delete();
                }
            }

            foreach ($request->pr_request as $pRQ) {
                if (in_array($pRQ['id'], $processedIds)) {
                    continue;
                }

                $newRequest = prRequest::find($pRQ['id']);
                $operations = ($pRQ['qty'] < $newRequest->qty) ? 'plus' : 'minus';

                $sa = 'PR No. ' . $newRequest->prTicket->ticketCode;

                if ($pRQ['qty'] != $newRequest->qty) {
                    $quantityDifference = abs($pRQ['qty'] - $newRequest->qty);
                    PartStock::create([
                        'part_list_id' => $newRequest->partlist_id,
                        'quantity'     => $quantityDifference,
                        'operations'   => $operations,
                        'source'       => 'PR Request Updated (R)',
                        'source_type'  => 'pr_request_update',
                        'source_ref'   => $newRequest->prTicket->ticketCode
                    ]);
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

                $revised = prTicket::findOrFail($pRQ['ticket_id']);
                $revised->status = 'Revised';
                $revised->advance_cash = $request->input('advance_cash');
                $revised->save();

                if($revised->save()){
                    $data = [
                        'ticket' => prTicket::find($pRQ['ticket_id'])->ticketCode,
                        'status' => prTicket::find($pRQ['ticket_id'])->status
                    ];

                    // Send mail To Related User Email
                    $userHodId = prTicket::find($pRQ['ticket_id'])->user->deptList->user_hod_id;
                    $userDeptHodEmail = User::find($userHodId)->email;

                    Mail::to($userDeptHodEmail)->send(new TestEmail($data));

                    // ADD: Notify user for Revised status
                    Notification::create([
                        'user_id' => $revised->user_id,
                        'pr_ticket_id' => $revised->id,
                        'status' => $revised->status,
                        'message' => "Your PR request {$revised->ticketCode} has been Revised. Please review the changes."
                    ]);

                    // ADD: Optionally notify HOD for Revised status
                    $hodId = $revised->user->deptList->user_hod_id ?? null;
                    if ($hodId) {
                        Notification::create([
                            'user_id' => $hodId,
                            'pr_ticket_id' => $revised->id,
                            'status' => $revised->status,
                            'message' => "PR request {$revised->ticketCode} from {$revised->user->name} has been Revised and needs your review."
                        ]);
                    }
                }
            }
            return response()->json(['message' => 'Request successfully saved']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}