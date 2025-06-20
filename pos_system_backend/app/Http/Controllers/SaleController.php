<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRateModel;
use App\Models\ProductModel;
use App\Models\SaleDetailModel;
use App\Models\SaleModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    //

   protected function list()
    {
        try {
            $sales = SaleModel::with(['user', 'details.orderType'])->get(); // eager load relationships

            $data = [];

            foreach ($sales as $sale) {
                foreach ($sale->details as $detail) {
                    $data[] = [
                        'sale_id' => $sale->id,
                        'sale_date' => $sale->sale_date,
                        'sale_by' => $sale->user ? $sale->user->name : null,
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
                'message' => 'Success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error get sale: ' . $e->getMessage());

            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    protected function add(Request $request)
    {
        $request->validate([
            'sale_date' => 'required|date',
            'sale_by' => 'nullable|exists:users,id',
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

        try {
            $sale = SaleModel::create([
                'sale_date' => $request->sale_date,
                'sale_by' => $request->sale_by
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
                $price = $item['price'] ?? $product->price;
                $qty = $item['qty'];
                $productDiscount = $item['discount'] ?? $product->discount ?? 0;

                // Calculate totals and discounts
                $totalBeforeDiscount = $price * $qty;
                $discountAmount = ($totalBeforeDiscount * $productDiscount) / 100;
                $totalAfterDiscount = $totalBeforeDiscount - $discountAmount;

                $totalSaleDiscount += $discountAmount;
                $grandTotalUsd += $totalAfterDiscount;

                $totalPriceUsd = round($totalAfterDiscount, 2);
                $totalPriceRiel = round($totalPriceUsd * $rate, 2);

                SaleDetailModel::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'qty' => $qty,
                    'price' => $price,
                    'total_price_usd' => $totalPriceUsd,
                    'total_price_riel' => $totalPriceRiel,
                    'discount' => $productDiscount,
                    'order_type_id' => $item['order_type_id'],
                    'status' => $item['status'] ?? 'on_the_way',
                    'amount_take_usd' => $item['amount_take_usd'] ?? 0,
                    'amount_take_riel' => $item['amount_take_riel'] ?? 0,
                    'amount_change_usd' => $item['amount_change_usd'] ?? 0,
                    'amount_change_riel' => $item['amount_change_riel'] ?? 0,
                ]);
            }

        
        // $sale->update([
        //     'total_discount_usd' => round($totalSaleDiscount, 2),
        //     'grand_total_usd' => round($grandTotalUsd, 2),
        // ]);

            DB::commit();

            $sale->load(['user', 'details.orderType']);

            $data = [];
            foreach ($sale->details as $detail) {
                $data[] = [
                    'sale_id' => $sale->id,
                    'sale_date' => $sale->sale_date,
                    'sale_by' => $sale->user ? $sale->user->name : null,
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
                'message' => 'Sale added successfully',
                'data' => $data,
                'total_discount_usd' => round($totalSaleDiscount, 2),
                'grand_total_usd' => round($grandTotalUsd, 2),
                'exchange_rate' => $rate
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Failed to add sale',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
