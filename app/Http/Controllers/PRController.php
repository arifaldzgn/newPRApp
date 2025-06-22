<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\partList;
use App\Models\prTicket;
use App\Models\PartStock;
use App\Models\prRequest;
use App\Models\User;

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

    public function create(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'pr_request.*.part_name' => 'required|min:1',
                'pr_request.*.partlist_id' => 'required|min:1',
                'pr_request.*.qty' => 'required|min:1',
                'pr_request.*.amount' => 'nullable',
                'pr_request.*.remark' => 'nullable',
                'pr_request.*.other_cost' => 'required|min:1',
                'pr_request.*.vendor' => 'required|min:1',
                'pr_request.*.category' => 'required|min:1',
                'pr_request.*.tag' => 'required|min:1'
            ]);

            $currentDay = date('d');
            $currentMonth = date('m');
            $currentYear = date('y');

            $ticketCount = prTicket::whereYear('created_at', '=', date('Y'))
                ->whereMonth('created_at', '=', date('m'))
                ->count() + 1;

            $ticketNumber = str_pad($ticketCount, 3, '0', STR_PAD_LEFT);

            $ticketCode = $currentYear . ' ' . $currentMonth . ' ' . $ticketNumber;

            $userId = auth()->user()->id;
            $hodId = User::find($userId)->deptList->user_hod_id;

            // Create the ticket
            $newTicket = prTicket::create([
                'ticketCode' => $ticketCode,
                'status' => 'Pending',
                'user_id' => $userId,
                'approved_user_id' => $hodId,
                'date_approval' => date('Y-m-d'),
                'advance_cash' => $request->advance_cash
            ]);

            foreach ($validatedData['pr_request'] as $prQ) {

                $part = partList::findOrFail($prQ['partlist_id']);


                if ($part->requires_stock_reduction !== 'false') {
                    $part->requires_stock_reduction -= $prQ['qty'];
                    $part->save();
                }

                $prQ['ticket_id'] = $newTicket->id;

                $req = prRequest::create($prQ);

                $getFalse = $part->requires_stock_reduction;

                if($getFalse !== "false"){
                    PartStock::create([
                        'part_list_id' => $prQ['partlist_id'],
                        'quantity' => $prQ['qty'],
                        'source' => $newTicket->ticketCode,
                        'operations' => 'minus'
                    ]);
                }

                // Send email
                $data = [
                    'ticket' => $newTicket->ticketCode, // Changed to use $newTicket
                    'status' =>  $newTicket->status // Changed to use $newTicket
                ];

                // $hodId = auth()->user()->deptList->user_hod_id;
                // $hodEmail = User::find($hodId)->email;

                // Mail::to($hodEmail)->send(new TestEmail($data));
            }

            // Log history
            $newTicket->logHistory('created', null, $newTicket->toArray(), null);

            return response()->json(['message' => 'New Request Successfully added']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function retrievePartDetails(Request $request)
    {

        $partName = $request->input('partName');
        // dd($partName);
        $part = partList::where('id', $partName)->first();

        $stock = $part->PartStock->where('operations', 'plus')->sum('quantity') - $part->PartStock->where('operations', 'minus')->sum('quantity');

        $newStock = 0;
        if($stock == 0){
            $newStock = "false";
        }else{
            $newStock = $stock;
        }

        if ($part) {
            return response()->json([
                'part' => $part,
                'stock' => $newStock
            ]);
        } else {
            return response()->json(['error' => 'Part not found'], 404);
        }
    }

    public function show($id)
    {
        $ticketRequests = prRequest::where('ticket_id', $id)->get();
        $ticket = prTicket::find($id);
        if($ticket->advance_cash){
            $aC = $ticket->advance_cash;
        }else{
            $aC = 0;
        }

        return response()->json([
            // 'stock' => $ticketRequests->partList->,
            'advance_cash' => $aC,
            'pr_requests' => $ticketRequests
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
        if(auth()->user()->role === 'pic' or auth()->user()->role === 'admin'){
            return view('pr.pending_pr', [
                'dataT' => prTicket::where(function($query) {
                    $query->where('status', 'Pending')
                          ->orWhere('status', 'Revised');
                })
                ->get()
            ]);
        }else{
            return view('pending', [
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
        return view('approved', [
            'dataT' => prTicket::where('status', 'Approved')->get()
        ]);
    }

    public function rejected()
    {
        if(auth()->user()->role === 'pic' or auth()->user()->role === 'admin'){
            return view('rejected', [
                'dataT' => prTicket::where(function($query) {
                    $query->where('status', 'Rejected');
                })->get()
            ]);
        }else{
            return view('rejected', [
                'dataT' => prTicket::where(function($query) {
                    $query->where('status', 'Rejected');
                })->where('user_id', auth()->user()->id)
                ->get()
            ]);
        }
    }

    public function destroy($id)
    {
        $ticket = prTicket::with('prRequest')->findOrFail($id);

        $totalQuantities = $ticket->prRequest->groupBy('partlist_id')->map(function ($requests) {
            return $requests->sum('qty');
        });

        // dd($totalQuantities);
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

        if($stock){
            $ticket->delete();
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
                 'operations' => 'plus',
                 'quantity' => $quantity,
                 'source' => 'PR No. ' . $ticketCode,
                 'part_list_id' => $partlist_id,
             ]);
         }

         // Delete the prRequest instance
         $part->delete();
    }

    public function approveTicket($requestId)
    {
        $request = prTicket::findOrFail($requestId);

        if($request->approved_user_id !== ''){
            $request->status = 'Approved';
            $request->date_approval = date('Y-m-d');
            $request->save();

            $data = [
                'ticket' => prTicket::find($requestId)->ticketCode,
                'status' => prTicket::find($requestId)->status
            ];

            $userHodId = prTicket::find($requestId)->user->deptList->user_hod_id;
            $userDeptHodEmail = User::find($userHodId)->email;

            Mail::to($userDeptHodEmail)->send(new TestEmail($data));

        }else{
            $request->status = 'Approved';
            $request->date_approval = date('Y-m-d');
            $request->approved_user_id = auth()->user()->id;
            $request->save();
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

        if($ticket->save()){

            $data = [
                'ticket' => prTicket::find($id)->ticketCode,
                'status' => prTicket::find($id)->status
            ];

            // Send mail To Related User Email
            $userHodId = prTicket::find($id)->user->deptList->user_hod_id;
            $userDeptHodEmail = User::find($userHodId)->email;

            Mail::to($userDeptHodEmail)->send(new TestEmail($data));
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

            return view('printTicket', [
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

                if($getFalse !== "false"){
                    $stocks = PartStock::create([
                        'part_list_id' => $newRequest->partlist_id,
                        'quantity' => $quantityDifference,
                        'operations' => $operations,
                        'source' => 'PR No. ' . $newRequest->prTicket->ticketCode
                    ]);
                }
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
                $stocks = PartStock::create([
                    'part_list_id' => $newRequest->partlist_id,
                    'quantity' => $quantityDifference,
                    'operations' => $operations,
                    'source' => $sa
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
