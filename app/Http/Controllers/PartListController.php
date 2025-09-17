<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\partList;
use App\Models\assetCode;
use App\Models\PartStock;
use App\Models\prRequest;
use App\Models\PrLogHistory;

class PartListController extends Controller
{
    //

    public function index()
    {
        //
        return view('parts.create_part', [
            'nonStock' => partList::where('requires_stock_reduction', '=', 'false')->get(),
            'stock' => partList::where('requires_stock_reduction', '!=', 'false')->get()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        try{

            $request->validate([
                'part_name' => 'required',
                'category' => 'required',
            ]);

            if($request->stocks){
                $newPart = partList::create([
                    'asset_code_id' => 0,
                    'part_name' => $request->part_name,
                    'category' => $request->category,
                    'UoM' => 'pcs',
                    'requires_stock_reduction' => true,
                    'type' => $request->type
                ]);

                $partListId = $newPart->id;

                PartStock::create([
                    'part_list_id' => $partListId,
                    'quantity' => $request->stocks,
                    'source' => 'Part Created',
                    'operations' => 'plus'
                ]);

            }else{
                partList::create([
                    'asset_code_id' => 0,
                    'part_name' => $request->part_name,
                    'category' => $request->category,
                    'UoM' => $request->UoM,
                    'requires_stock_reduction' => 'false',
                    'type' => $request->type
                ]);
            }


            return response()->json(['message' => 'New Part Successfully added']);

        }catch (\Exception $e){

            return response()->json(['error' => 'Failed to Add New Part'], 500);

        }
    }

    public function test(Request $request)
    {
        $partP = partList::find(1)->PartStock->where('operations', 'plus')->sum('quantity');
        $partM = partList::find(1)->PartStock->where('operations', 'minus')->sum('quantity');
        $total = $partP - $partM;

        $index[] = [
            'Plus' => $partP,
            'Minus' => $partM,
            'Total Stock' => $total
        ];

        dd($index);
    }

    public function delete_part($id)
    {
        try {
            // Find the PartList to be deleted
            $part = PartList::findOrFail($id);

            // Log the deletion in part_list_log_histories table
            PartListLogHistories::create([
                'action' => 'delete',
                'part_list_id' => $id,
                'asset_code_id' => 0,
                'part_name' => $part->part_name,
                'category' => $part->category,
                'UoM' => $part->UoM,
                'type' => $part->type,
                'user_id' => auth()->user()->id,
            ]);

            // Delete the PartList
            $part->delete();

            return response()->json(['message' => 'Part successfully deleted']);
        } catch (\Exception $e) {
            // Log the error message and stack trace
            Log::error('Failed to delete the part: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            // Return a generic error response
            return response()->json(['error' => 'Failed to delete the part'], 500);
        }
    }

    public function refundStock(Request $request)
    {
        try {
            $prRequest = prRequest::findOrFail($request->pr_id);
            $part = partList::findOrFail($prRequest->partlist_id);

            // Increase the stock
            $part->requires_stock_reduction += $request->quantity;
            $part->save();

            return response()->json(['message' => 'Stock refunded successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getPartDetails($id)
    {
        $data = partList::find($id);
        $stock = $data->PartStock->where('operations', 'plus')->sum('quantity') - $data->PartStock->where('operations', 'minus')->sum('quantity');
        return response()->json([
            'data' => $data,
            'stock' => $stock
        ]);
    }

    public function updatePartList(Request $request)
    {
       $validatedData = $request->validate([
        'part_id' => 'required|exists:part_lists,id',
        'part_name' => 'required|string',
        'category' => 'required|string',
        'description' => 'nullable|string',
        'quantity' => 'required|numeric',
        ]);

        $part = partList::findOrFail($validatedData['part_id']);

        $part->part_name = $validatedData['part_name'];
        $part->category = $validatedData['category'];
        $part->type = $validatedData['description'];
        $part->save();

        $operation = $validatedData['quantity'] >= 0 ? 'plus' : 'minus';
        if($operation == 'plus'){
            $source = 'Master Stock';
        }else{
            $source = 'Master Stock';
        }

        $part->PartStock()->create([
            'quantity' => abs($validatedData['quantity']),
            'operations' => $operation,
            'source' => $source
        ]);

        return response()->json(['message' => 'Part details updated successfully']);
    }

    public function log()
    {
        // return PartStock::orderBy('created_at', 'desc')->get();
        return view('parts.pr_log', [
            // 'logs' => PrLogHistory::orderBy('created_at', 'asc')->get(),
            'partStockLogs' => PartStock::orderBy('created_at', 'desc')->get()
        ]);
    }
}
