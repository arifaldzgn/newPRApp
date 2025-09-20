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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Dompdf\Dompdf;
use Exception;

class PRController extends Controller
{
    //

    public function index()
    {
        if(auth()->user()->role === 'admin' or auth()->user()->role === 'pic')
        {
            $dataT = prTicket::all();
        }else{
            $dataT = prTicket::where('user_id', auth()->user()->id)->get();
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
            Log::info('User info', ['userId' => $userId, 'hodId' => $hodId]);

            $ticketCode = $this->generateUniqueTicketCode();

            $newTicket = DB::transaction(function () use ($ticketCode, $userId, $hodId, $validatedData, $request) {
                $newTicket = prTicket::create([
                    'ticketCode' => $ticketCode,
                    'status' => 'Pending',
                    'user_id' => $userId,
                    'approved_user_id' => $hodId,
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
        $ticket = prTicket::findOrFail($id); // Use findOrFail for better error handling
        $ticketRequests = prRequest::where('ticket_id', $id)->get();
        $advanceCash = $ticket->advance_cash ?? 0;

        // Fetch user names
        $requester = User::find($ticket->user_id);
        $approver = $ticket->approved_user_id ? User::find($ticket->approved_user_id) : null;

        return response()->json([
            'advance_cash' => $advanceCash,
            'pr_requests' => $ticketRequests,
            'requester_name' => $requester ? $requester->name : 'Unknown',
            'approver_name' => $approver ? $approver->name : 'None',
            'ticket' => [
                'ticketCode' => $ticket->ticketCode,
                'status' => $ticket->status,
                'reason_reject' => $ticket->reason_reject,
                'date_approval' => $ticket->date_approval,
                'date_checked' => $ticket->date_checked,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->updated_at,
            ]
        ]);
    }

    public function retrievePartName($id) {
        $partList = partList::find($id);
        // dd($partList);
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
        if(auth()->user()->role === 'hod' or auth()->user()->role === 'admin'){
            return view('pr.pending_pr', [
                'dataT' => prTicket::where(function($query) {
                    $query->where('status', 'Pending')
                          ->orWhere('status', 'Revised');
                })
                ->get()
            ]);
        }else{
            return view('pr.pending_pr', [
                'dataT' => prTicket::where(function($query) {
                    $query->where('status', 'Pending')
                          ->orWhere('status', 'Revised');
                })->where('user_id', auth()->user()->id)
                ->get()
            ]);
        }
    }

    public function approved()
    {
        return view('pr.approved_pr', [
            'dataT' => prTicket::where('status', 'Approved')->get()
        ]);
    }

    public function rejected()
    {
        if(auth()->user()->role === 'pic' or auth()->user()->role === 'admin'){
            return view('pr.rejected_pr', [
                'dataT' => prTicket::where(function($query) {
                    $query->where('status', 'Rejected');
                })->get()
            ]);
        }else{
            return view('pr.rejected_pr', [
                'dataT' => prTicket::where(function($query) {
                    $query->where('status', 'Rejected');
                })->where('user_id', auth()->user()->id)
                ->get()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $ticket = prTicket::with('prRequest')->findOrFail($id);

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
         // Find the prRequest instance
         $part = prRequest::findOrFail($id);

         // Retrieve the required data
         $quantity = $part->qty;
         $partlist_id = $part->partlist_id;
         $ticketId = $part->ticket_id; // Assuming prRequest has a relationship to prTicket
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

         // Delete the prRequest instance
         $part->delete();
    }

    public function approveTicket($requestId)
    {
        $request = prTicket::findOrFail($requestId);

        if ($request->approved_user_id !== '') {
            $request->status = 'Approved';
            $request->date_approval = date('Y-m-d');
            $request->save();

            $data = ['ticket' => $request->ticketCode, 'status' => $request->status];
            $userHodId = $request->user->deptList->user_hod_id;
            $userDeptHodEmail = User::find($userHodId)->email;

            Mail::to($userDeptHodEmail)->send(new TestEmail($data));

            // Notify user
            Notification::create([
                'user_id' => $request->user_id,
                'pr_ticket_id' => $request->id,
                'status' => $request->status,
                'message' => "Your PR request {$request->ticketCode} has been Approved."
            ]);
        } else {
            $request->status = 'Approved';
            $request->date_approval = date('Y-m-d');
            $request->approved_user_id = auth()->user()->id;
            $request->save();

            // Notify user
            Notification::create([
                'user_id' => $request->user_id,
                'pr_ticket_id' => $request->id,
                'status' => $request->status,
                'message' => "Your PR request {$request->ticketCode} has been Approved by you."
            ]);
        }

        return response()->json(['message' => 'The ticket has been successfully approved']);
    }

    // Test purpose
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
        $data = prTicket::where('ticketCode', $ticketCode)->first();
        if($data->status == 'Rejected'){
            return redirect()->route('dashboard');
        }else{

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
    }

    public function printPdf($ticketCode)
    {
        $data = prTicket::where('ticketCode', $ticketCode)->first();
        if($data->status !== 'Approved'){
            return redirect()->back();
        }else{

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
    }

     // Update Material
    public function update(Request $request)
    {
        try {
            $processedIds = [];

            foreach ($request->pr_request as $pRQ) {
                if (in_array($pRQ['id'], $processedIds)) {
                    continue;
                }

                $newRequest = prRequest::find($pRQ['id']);
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

                $revised = prTicket::findOrFail($pRQ['ticket_id']);
                $revised->status = 'Revised';
                $revised->advance_cash = $request->input('advance_cash');
                $revised->save();

                if ($revised->save()) {
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
                }
            }

            return response()->json(['message' => 'Request successfully saved']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateR(Request $request)
    {
        try {

        $processedIds = [];

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
                'qty' => $pRQ['qty'], // New input
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
            }
        }
            return response()->json(['message' => 'Request successfully saved']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
