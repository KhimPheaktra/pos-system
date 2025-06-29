<?php

namespace App\Http\Controllers;

use App\Models\InvoiceModel;
use App\Models\SaleModel;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    //

    public function __construct()
    {
            $this->middleware('auth:sanctum');
    }

    public function generateInvoices()
    {
        try {
            $sales = SaleModel::all();
            $created = 0;
            $skipped = 0;

            foreach ($sales as $sale) {
                // Avoid duplicate invoices
                $existingInvoice = InvoiceModel::where('sale_id', $sale->id)->first();

                if (!$existingInvoice) {
                    InvoiceModel::create([
                        'sale_id' => $sale->id
                    ]);
                    $created++;
                } else {
                    $skipped++;
                }
            }

            return response()->json([
                'message' => 'Invoice generation complete.',
                'created' => $created,
                'skipped' => $skipped
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to generate invoices.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function list()
    {
        try {
            $invoices = InvoiceModel::with([
                'sale.user',
                'sale.userClient',
                'sale.details.orderType',
                'sale.details.product'
            ])->get();

            if ($invoices->isEmpty()) {
                return response()->json([
                    'message' => 'No invoices found'
                ], 404);
            }

            $data = [];

            foreach ($invoices as $invoice) {
                $sale = $invoice->sale;

                $invoiceData = [
                    'invoice_id' => $invoice->id,
                    'sale_id' => $sale->id,
                    'sale_date' => $sale->sale_date,
                    'sale_by' => $sale->user?->name,
                    'order_by' => $sale->userClient?->name,
                    'items' => []
                ];

                foreach ($sale->details as $detail) {
                    $invoiceData['items'][] = [
                        'product_id' => $detail->product_id,
                        'product_name' => $detail->product?->name,
                        'qty' => $detail->qty,
                        'price' => $detail->price,
                        'total_price_usd' => $detail->total_price_usd,
                        'total_price_riel' => $detail->total_price_riel,
                        'discount' => $detail->discount,
                        'order_type' => $detail->orderType?->order_type,
                        'status' => $detail->status,
                        'amount_take_usd' => $detail->amount_take_usd,
                        'amount_take_riel' => $detail->amount_take_riel,
                        'amount_change_usd' => $detail->amount_change_usd,
                        'amount_change_riel' => $detail->amount_change_riel,
                    ];
                }

                $data[] = $invoiceData;
            }

            return response()->json([
                'message' => 'Success',
                'data' => $data
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function getById($id)
    {
        try {
            $invoice = InvoiceModel::with([
                'sale.user',
                'sale.userClient',
                'sale.details.orderType',
                'sale.details.product'
            ])->find($id);

            if (!$invoice) {
                return response()->json([
                    'message' => 'Invoice not found'
                ], 404);
            }

            $sale = $invoice->sale;

            $data = [
                'invoice_id' => $invoice->id,
                'sale_id' => $sale->id,
                'sale_date' => $sale->sale_date,
                'sale_by' => $sale->user?->name,
                'order_by' => $sale->userClient?->name,
                'items' => []
            ];

            foreach ($sale->details as $detail) {
                $data['items'][] = [
                    'product_id' => $detail->product_id,
                    'product_name' => $detail->product?->name,
                    'qty' => $detail->qty,
                    'price' => $detail->price,
                    'total_price_usd' => $detail->total_price_usd,
                    'total_price_riel' => $detail->total_price_riel,
                    'discount' => $detail->discount,
                    'order_type' => $detail->orderType?->order_type,
                    'status' => $detail->status,
                    'amount_take_usd' => $detail->amount_take_usd,
                    'amount_take_riel' => $detail->amount_take_riel,
                    'amount_change_usd' => $detail->amount_change_usd,
                    'amount_change_riel' => $detail->amount_change_riel,
                ];
            }

            return response()->json([
                'message' => 'Success',
                'data' => $data
            ], 200);

        } catch (\Throwable $e) {
            

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
