<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\partList;
use App\Models\assetCode;
use App\Models\PartStock;
use App\Models\prRequest;
use App\Models\PrLogHistory;
use App\Models\PartListLogHistories;
use Illuminate\Support\Facades\Log;


class PartListController extends Controller
{
    //

    public function index()
    {
        $parts = partList::with('PartStock')->get();

        // Pisahkan berdasarkan kondisi logis
        $stock = $parts->filter(function ($p) {
            return $p->requires_stock_reduction == '1';
        });

        $nonStock = $parts->filter(function ($p) {
            return $p->requires_stock_reduction == '0';
        });


        // dd($stock, $nonStock);

        return view('parts.create_part', compact('stock', 'nonStock'));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        try {
            $request->validate([
                'part_name' => 'required|string',
                'category' => 'required|string',
                'item_type' => 'required|string|in:stock,non-stock',
                'stocks' => 'nullable|integer|min:0',
                'UoM' => 'nullable|string',
                'type' => 'nullable|string',
            ]);

            // Gunakan pilihan user untuk menentukan jenis item
            $isStockItem = $request->item_type === 'stock';

            $newPart = partList::create([
                'asset_code_id' => 0,
                'part_name' => $request->part_name,
                'category' => $request->category,
                'UoM' => $request->UoM ?? 'pcs',
                'requires_stock_reduction' => $isStockItem ? 1 : 0,
                'type' => $request->type ?? '-',
                'current_stock' => $isStockItem ? ($request->stocks ?? 0) : 0,
            ]);

            // Jika tipe item adalah stock, catat transaksi stok awal (boleh 0)
            if ($isStockItem) {
                PartStock::create([
                    'part_list_id' => $newPart->id,
                    'quantity' => $request->stocks ?? 0,
                    'operations' => 'plus',
                    'source' => 'Initial Stock Entry',
                    'source_type' => 'System',
                    'source_ref' => auth()->id(),
                ]);
            }

            return response()->json(['message' => 'New Part successfully added']);
        } catch (\Exception $e) {
            \Log::error("Failed to create new part: {$e->getMessage()}");
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
            $part->current_stock += $request->quantity;
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
            'quantity' => 'nullable|numeric',
        ]);

        $part = partList::findOrFail($validatedData['part_id']);
        // dd($part->requires_stock_reduction);

        $part->part_name = $validatedData['part_name'];
        $part->category = $validatedData['category'];
        $part->type = $validatedData['description'] ?? $part->type;
        $part->save();

        $quantity = (float) ($validatedData['quantity'] ?? 0);

        // hanya untuk item yang butuh pengurangan stok
        if ((int) $part->requires_stock_reduction === 1) {

            // kalau user isi quantity, catat ke PartStock
            if ($quantity != 0) {
                $operation = $quantity >= 0 ? 'plus' : 'minus';

                PartStock::create([
                    'part_list_id' => $part->id,
                    'quantity' => abs($quantity),
                    'operations' => $operation,
                    'source' => 'Manual Adjustment',
                    'source_type' => 'Manual Update',
                    'source_ref' => auth()->id(),
                ]);
            }

            // selalu update current stock
            $this->getCurrentStock($part->id);
        }

        return response()->json(['message' => 'Part details updated successfully']);
    }

    protected function getCurrentStock($partId)
    {
        $part = partList::findOrFail($partId);

        // Skip untuk non-stock item
        if ((int)$part->requires_stock_reduction === 0) {
            return $part->current_stock;
        }

        $stock = $part->PartStock->where('operations', 'plus')->sum('quantity') -
                $part->PartStock->where('operations', 'minus')->sum('quantity');

        $stock = max($stock, 0);

        if ($part->current_stock != $stock) {
            $part->update([
                'current_stock' => $stock,
                'last_synced_at' => now(),
            ]);
        }

        return $stock;
    }



    public function validateStock(Request $request)
    {
        try {
            $prRequests = $request->input('pr_request', []);

            foreach ($prRequests as $prQ) {
                $part = partList::findOrFail($prQ['partlist_id']);
                $qty = (int) $prQ['qty'];

                // Jika part ini butuh pengurangan stok (stock item)
                if ((int)$part->requires_stock_reduction === 1) {
                    if ($part->current_stock <= 0) {
                        return response()->json([
                            'valid' => false,
                            'error' => "Part '{$part->part_name}' is out of stock."
                        ], 422);
                    }

                    if ($qty > $part->current_stock) {
                        return response()->json([
                            'valid' => false,
                            'error' => "Insufficient stock for '{$part->part_name}'. Requested: {$qty}, Available: {$part->current_stock}."
                        ], 422);
                    }
                }
                // else → non-stock item (requires_stock_reduction = 0)
                // tidak perlu dicek stoknya, langsung lanjut
            }

            return response()->json(['valid' => true]);
        } catch (\Exception $e) {
            \Log::error('Stock validation failed: ' . $e->getMessage());
            return response()->json(['valid' => false, 'error' => 'Failed to validate stock'], 500);
        }
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
