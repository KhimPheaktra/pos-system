<?php

namespace App\Http\Controllers;

use App\Models\EmployeeShiftModel;
use App\Models\ExchangeRateModel;
use App\Models\ProductModel;
use App\Models\SaleDetailModel;
use App\Models\SaleModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Empty_;

class SaleController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function listForClient(Request $request)
    {
        try {
            $client = $request->user('client');

            if (!$client) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $sales = SaleModel::where('order_by', $client->id)
                ->with(['details.orderType', 'user', 'userClient'])
                ->get();

            if ($sales->isEmpty()) {
                return response()->json(['message' => 'No sales found.'], 404);
            }

            $data = [];

            foreach ($sales as $sale) {
                foreach ($sale->details as $detail) {
                    $data[] = [
                        'sale_id' => $sale->id,
                        'sale_date' => $sale->sale_date,
                        'sale_by' => $sale->user ? $sale->user->name : null,
                        'order_by' => $sale->userClient ? $sale->userClient->name : null,
                        'product_id' => $detail->product_id,
                        'qty' => $detail->qty,
                        'price' => $detail->price,
                        'total_price_usd' => $detail->total_price_usd,
                        'total_price_riel' => $detail->total_price_riel,
                        'discount' => $detail->discount,
                        'order_type' => $detail->orderType ? $detail->orderType->order_type : null,
                        'status' => $detail->status,
                        'amount_take_usd' => $detail->amount_take_usd,
                        'amount_take_riel' => $detail->amount_take_riel,
                        'amount_change_usd' => $detail->amount_change_usd,
                        'amount_change_riel' => $detail->amount_change_riel,
                    ];
                }
            }

            return response()->json([
                'message' => 'Sales retrieved successfully.',
                'data' => $data,
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'message' => 'Something went wrong while fetching sales.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



   public function list()
    {
        try {
            $sales = SaleModel::with(['user','userClient', 'details.orderType'])->get(); // eager load relationships

            $data = [];

            foreach ($sales as $sale) {
                foreach ($sale->details as $detail) {
                    $data[] = [
                        'sale_id' => $sale->id,
                        'sale_date' => $sale->sale_date,
                        'sale_by' => $sale->user ? $sale->user->name : null,
                        'order_by' => $sale->userClient ? $sale->userClient->name : null,
                        'product_id' => $detail->product_id,
                        'qty' => $detail->qty,
                        'price' => $detail->price,
                        'total_price_usd' => $detail->total_price_usd,
                        'total_price_riel' => $detail->total_price_riel,
                        'discount' => $detail->discount,
                        'order_type' => $detail->orderType ? $detail->orderType->order_type : null,
                        'status' => $detail->status,
                        'amount_take_usd' => $detail->amount_take_usd,
                        'amount_take_riel' => $detail->amount_take_riel,
                        'amount_change_usd' => $detail->amount_change_usd,
                        'amount_change_riel' => $detail->amount_change_riel,
                    ];
                }
            }

          if (!empty($data)) {
                return response()->json([
                    'message' => 'Success',
                    'data' => $data
                ]);
            } else {
                return response()->json([
                    'message' => 'No data found'
                ], 404);
            }

          
        } catch (\Throwable $e) {
            Log::error('Error get sale: ' . $e->getMessage());

            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getById($id){
          try {
            $sale = SaleModel::with(['user','userClient', 'details.orderType'])->findOrFail($id);

            $data = [];
                foreach ($sale->details as $detail) {
                    $data[] = [
                        'sale_id' => $sale->id,
                        'sale_date' => $sale->sale_date,
                        'sale_by' => $sale->user ? $sale->user->name : null,
                        'order_by' => $sale->userClient ? $sale->userClient->name : null,
                        'product_id' => $detail->product_id,
                        'qty' => $detail->qty,
                        'price' => $detail->price,
                        'total_price_usd' => $detail->total_price_usd,
                        'total_price_riel' => $detail->total_price_riel,
                        'discount' => $detail->discount,
                        'order_type' => $detail->orderType ? $detail->orderType->order_type : null,
                        'status' => $detail->status,
                        'amount_take_usd' => $detail->amount_take_usd,
                        'amount_take_riel' => $detail->amount_take_riel,
                        'amount_change_usd' => $detail->amount_change_usd,
                        'amount_change_riel' => $detail->amount_change_riel,
                    ];
                }
                    if (!empty($data)) {
                        return response()->json([
                            'message' => 'Success',
                            'data' => $data
                        ]);
                    } else {
                        return response()->json([
                            'message' => 'No data found '
                        ], 404);
                    }

        } catch (\Throwable $e) {
            Log::error('Error get sale by id: ' . $e->getMessage());

            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function add(Request $request)
    {

        $request->validate([
            'sale_date' => 'required|date',
            'sale_by' => 'nullable|exists:users,id',
            'order_by' => 'nullable|exists:user_client,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'nullable|numeric|min:0', // Add this if not present
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.order_type_id' => 'required|exists:order_type,id',
            'items.*.status' => 'nullable|string',
            'items.*.amount_take_usd' => 'nullable|numeric',
            'items.*.amount_take_riel' => 'nullable|numeric',
            'items.*.amount_change_usd' => 'nullable|numeric',
            'items.*.amount_change_riel' => 'nullable|numeric',
        ]);

        // Fetch latest USD -> KHR rate
        $rate = ExchangeRateModel::where('base_currency', 'USD')
            ->where('target_currency', 'KHR')
            ->latest()
            ->value('rate') ?? 0;

        DB::beginTransaction();
            //  Check if the user's shift is currently active
        $activeShift = EmployeeShiftModel::where('start_by', $request->sale_by)
            ->whereNull('end_at')
            ->first();

        if (!$activeShift) {
            return response()->json([
                'message' => 'Your shift is not started. Please start your shift first.'
            ], 403);
        }

        try {
            $sale = SaleModel::create([
                'sale_date' => now(),
                'sale_by' => $request->sale_by,
                'order_by' => $request->order_by
            ]);

            $totalSaleDiscount = 0;
            $grandTotalUsd = 0;

            foreach ($request->items as $item) {
                $product = ProductModel::find($item['product_id']);

                if (!$product) {
                    return response()->json([
                        'message' => "Product with ID {$item['product_id']} not found."
                    ], 404);
                }

                if ($product->current_qty <= 0) {
                    return response()->json([
                        'message' => "Product '{$product->name}' is out of stock."
                    ], 400);
                }

                if ($item['qty'] > $product->current_qty) {
                    return response()->json([
                        'message' => "Not enough stock for '{$product->name}'. Available: {$product->current_qty}, Requested: {$item['qty']}"
                    ], 400);
                }

                // Deduct stock
                $product->current_qty -= $item['qty'];
                $product->save();

                // Determine price and discount
                $qty = $item['qty'];
               // Prefer item price if provided, otherwise use from product
                $originalPrice = $item['price'] ?? $product->price;

                // Use product discount or item discount
                $productDiscount = $item['discount'] ?? $product->discount ?? 0;

                // Use product's price_after_discount only if discount > 0
                if ($productDiscount > 0 && $product->price_after_discount > 0) {
                    $priceToUse = $product->price_after_discount;
                } else {
                    $priceToUse = $originalPrice;
                }


                // Calculate totals and discounts
                    $totalBeforeDiscount = $priceToUse * $qty;
                    $discountAmount = ($totalBeforeDiscount * $productDiscount) / 100;
                    $totalAfterDiscount = $totalBeforeDiscount - $discountAmount;

                    $totalPriceUsd = round($totalAfterDiscount, 2);
                    $totalPriceRiel = round($totalPriceUsd * $rate, 2);

                    // Cast payments to float
                    $amountTakeUsd = (float) ($item['amount_take_usd'] ?? 0);
                    $amountTakeRiel = (float) ($item['amount_take_riel'] ?? 0);

                    // Calculate both USD and Riel change
                    $amountChangeUsd = max(0, $amountTakeUsd - $totalPriceUsd);
                    $amountChangeRiel = max(0, $amountTakeRiel - $totalPriceRiel);

                    // Accumulate for total summary
                    $totalSaleDiscount += $discountAmount;
                    $grandTotalUsd += $totalAfterDiscount;


                SaleDetailModel::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'qty' => $qty,
                    'price' => $priceToUse,
                    'total_price_usd' => $totalPriceUsd,
                    'total_price_riel' => $totalPriceRiel,
                    'discount' => $productDiscount,
                    'order_type_id' => $item['order_type_id'],
                    'status' => $item['status'] ?? 'on_the_way',
                    'amount_take_usd' => $amountTakeUsd,
                    'amount_take_riel' => $amountTakeRiel,
                    'amount_change_usd' => $amountChangeUsd,
                    'amount_change_riel' => $amountChangeRiel,

                ]);
            }

        
            // $sale->update([
            //     'total_discount_usd' => round($totalSaleDiscount, 2),
            //     'grand_total_usd' => round($grandTotalUsd, 2),
            // ]);

            DB::commit();

            $sale->load(['user','userClient' ,'details.orderType']);

            $data = [];
            foreach ($sale->details as $detail) {
                $data[] = [
                    'sale_id' => $sale->id,
                    'sale_date' => $sale->sale_date,
                    'sale_by' => $sale->user ? $sale->user->name : null,
                    'order_by' => $sale->userClient ? $sale->userClient->name : null,
                    'product_id' => $detail->product_id,
                    'qty' => $detail->qty,
                    'price' => $detail->price,
                    'total_price_usd' => $detail->total_price_usd,
                    'total_price_riel' => $detail->total_price_riel,
                    'discount' => $detail->discount,
                    'order_type' => $detail->orderType ? $detail->orderType->order_type : null,
                    'status' => $detail->status,
                    'amount_take_usd' => $detail->amount_take_usd,
                    'amount_take_riel' => $detail->amount_take_riel,
                    'amount_change_usd' => $detail->amount_change_usd,
                    'amount_change_riel' => $detail->amount_change_riel,
                ];
            }

            if(!empty($data)){
                return response()->json([
                'message' => 'Sale added successfully',
                'data' => $data,
                'total_discount_usd' => round($totalSaleDiscount, 2),
                'grand_total_usd' => round($grandTotalUsd, 2),
                'exchange_rate' => $rate
            ], 201);
            }
            else{
                return response()->json([
                    'message' => 'sale added failed '
                ], 404);
            }
          
        } catch (\Throwable $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Failed to add sale',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $sale = SaleModel::findOrFail($id);

        $request->validate([
            'sale_date' => 'required|date',
            'sale_by' => 'nullable|exists:users,id',
            'order_by' => 'nullable|exists:user_client,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.order_type_id' => 'required|exists:order_type,id',
            'items.*.status' => 'nullable|string',
            'items.*.amount_take_usd' => 'nullable|numeric',
            'items.*.amount_take_riel' => 'nullable|numeric',
            'items.*.amount_change_usd' => 'nullable|numeric',
            'items.*.amount_change_riel' => 'nullable|numeric',
        ]);

        $rate = ExchangeRateModel::where('base_currency', 'USD')
            ->where('target_currency', 'KHR')
            ->latest()
            ->value('rate') ?? 0;

        DB::beginTransaction();

        try {
            // Update sale 
            $sale->update([
                'sale_date' => $request->sale_date,
                'sale_by' => $request->sale_by,
                'order_by' => $request->order_by,
            ]);

            // Optional: restore old stock
            foreach ($sale->details as $oldDetail) {
                $product = ProductModel::find($oldDetail->product_id);
                if ($product) {
                    $product->current_qty += $oldDetail->qty;
                    $product->save();
                }
            }

            // Delete old sale details
            $sale->details()->delete();

            $totalSaleDiscount = 0;
            $grandTotalUsd = 0;

            foreach ($request->items as $item) {
                $product = ProductModel::find($item['product_id']);

                if (!$product) {
                    return response()->json([
                        'message' => "Product with ID {$item['product_id']} not found."
                    ], 404);
                }

                if ($item['qty'] > $product->current_qty) {
                    return response()->json([
                        'message' => "Not enough stock for '{$product->name}'. Available: {$product->current_qty}, Requested: {$item['qty']}"
                    ], 400);
                }

                // Deduct new qty
                $product->current_qty -= $item['qty'];
                $product->save();

                $qty = $item['qty'];
                $originalPrice = $item['price'] ?? $product->price;
                $productDiscount = $item['discount'] ?? $product->discount ?? 0;

                $priceToUse = ($productDiscount > 0 && $product->price_after_discount > 0)
                    ? $product->price_after_discount
                    : $originalPrice;

                $totalBeforeDiscount = $priceToUse * $qty;
                $discountAmount = ($totalBeforeDiscount * $productDiscount) / 100;
                $totalAfterDiscount = $totalBeforeDiscount - $discountAmount;

                $totalPriceUsd = round($totalAfterDiscount, 2);
                $totalPriceRiel = round($totalPriceUsd * $rate, 2);

                $amountTakeUsd = (float) ($item['amount_take_usd'] ?? 0);
                $amountTakeRiel = (float) ($item['amount_take_riel'] ?? 0);
                $amountChangeUsd = max(0, $amountTakeUsd - $totalPriceUsd);
                $amountChangeRiel = max(0, $amountTakeRiel - $totalPriceRiel);

                $totalSaleDiscount += $discountAmount;
                $grandTotalUsd += $totalAfterDiscount;

                SaleDetailModel::create([
                    'sale_id' => $sale->id, // Same sale_id
                    'product_id' => $item['product_id'],
                    'qty' => $qty,
                    'price' => $priceToUse,
                    'total_price_usd' => $totalPriceUsd,
                    'total_price_riel' => $totalPriceRiel,
                    'discount' => $productDiscount,
                    'order_type_id' => $item['order_type_id'],
                    'status' => $item['status'] ?? null,
                    'amount_take_usd' => $amountTakeUsd,
                    'amount_take_riel' => $amountTakeRiel,
                    'amount_change_usd' => $amountChangeUsd,
                    'amount_change_riel' => $amountChangeRiel,
                ]);
            }

            DB::commit();

            $sale->load(['user', 'details.orderType']);

            $data = [];
            foreach ($sale->details as $detail) {
                $data[] = [
                    'sale_id' => $sale->id,
                    'sale_date' => $sale->sale_date,
                    'sale_by' => $sale->user ? $sale->user->name : null,
                    'order_by' => 'nullable|exists:user_client,id',
                    'product_id' => $detail->product_id,
                    'qty' => $detail->qty,
                    'price' => $detail->price,
                    'total_price_usd' => $detail->total_price_usd,
                    'total_price_riel' => $detail->total_price_riel,
                    'discount' => $detail->discount,
                    'order_type' => $detail->orderType ? $detail->orderType->order_type : null,
                    'status' => $detail->status,
                    'amount_take_usd' => $detail->amount_take_usd,
                    'amount_take_riel' => $detail->amount_take_riel,
                    'amount_change_usd' => $detail->amount_change_usd,
                    'amount_change_riel' => $detail->amount_change_riel,
                ];
            }

            return response()->json([
                'message' => 'Sale updated successfully',
                'data' => $data,
                'total_discount_usd' => round($totalSaleDiscount, 2),
                'grand_total_usd' => round($grandTotalUsd, 2),
                'exchange_rate' => $rate
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update sale',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function delete($id){
        try{
            $sale = SaleModel::findOrFail($id);
            $sale->delete();
            return response()->json([
                'message' => 'Sale deleted successfully',
            ],200);
        }
        catch(\Throwable $e){
            return response()->json([
                'message' => 'Faild to deleted sale'
            ],500);
        }
    }


}
