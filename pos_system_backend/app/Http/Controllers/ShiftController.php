<?php

namespace App\Http\Controllers;

use App\Models\EmployeeShiftModel;
use App\Models\SaleModel;
use Illuminate\Http\Request;
use Carbon\Carbon;


class ShiftController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function list(){
        $shift = EmployeeShiftModel::all();
        try{
            if(!empty($shift)){
                return response()->json([
                    'shift' => $shift->map(function ($shift){
                        return[
                            'id' => $shift->id,
                            'Saler' => $shift->startUser ? $shift->startUser->name : null,
                            'start_at' => $shift->start_at,
                            'end_at' => $shift->end_at,
                            'end_by' => $shift->endUser ? $shift->endUser->name : null,
                            'amount_input' => $shift->amount_input,
                            'total_item_sale' => $shift->total_item_sale,
                            'total_amount' => $shift->total_amount,
                            'status' => $shift->status,
                        ];
                    })
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'No data found'
                ],404);
            }
        }
        catch(\Throwable $e){
              return response()->json([
                    'message' => 'Something when wrong',
                    'error' => $e->getMessage()
            ],500);
        }
    }

    public function getById($id){
         $shift = EmployeeShiftModel::findOrFail($id);
        try{
            if(!empty($shift)){
                return response()->json([
                    'shift' =>[
                            'id' => $shift->id,
                            'start_by' => $shift->startUser ? $shift->startUser->name : null,
                            'start_at' => $shift->start_at,
                            'end_at' => $shift->end_at,
                            'end_by' => $shift->endUser ? $shift->endUser->name : null,
                            'amount_input' => $shift->amount_input,
                            'total_item_sale' => $shift->total_item_sale,
                            'total_amount' => $shift->total_amount,
                            'status' => $shift->status,
                        ]
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'No data found'
                ],404);
            }
        }
        catch(\Throwable $e){
              return response()->json([
                    'message' => 'Something when wrong',
                    'error' => $e->getMessage()
            ],500);
        }
    }

    public function startShift(Request $request)
    {
        $request->validate([
            'start_by' => 'required|exists:users,id',
            'start_at' => 'nullable|date',
            'amount_input' => 'required|numeric',
        ]);

        // Prevent staff from starting new shift when the shift already start 
        $activeShift = EmployeeShiftModel::whereNull('end_at')->first();
        if ($activeShift) {
            return response()->json([
                'message' => 'A shift is still active. Please end the current shift first.',
                'active_shift_id' => $activeShift->id
            ], 409);
        }


        try {
            $shift = EmployeeShiftModel::create([
                'start_by' => $request->start_by,
                'start_at' => $request->start_at ?? now(),
                'amount_input' => $request->amount_input,
                'status' => 'Processing', // default status
            ]);

                $shift->load('startUser');

            return response()->json([
                'message' => 'Shift started successfully',
                'shift' => [
                    'id' => $shift->id,
                    'start_by' => $shift->startUser ? $shift->startUser->name : null,
                    'start_at' => $request->start_at ?? now(),
                    'amount_input' => $shift->amount_input,
                    'total_item_sale' => 0,
                    'total_amount' => 0,
                    'status' => $shift->status,
                ]
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

   public function endShift(Request $request, $id)
{
    $request->validate([
        'end_at' => 'nullable|date',
        'end_by' => 'required|exists:users,id',
        'status' => 'nullable|string',
    ]);

    try {
        $shift = EmployeeShiftModel::find($id);
        if (!$shift) {
            return response()->json(['message' => 'Shift not found'], 404);
        }

        // Check if shift is already ended
        if ($shift->end_at) {
            return response()->json(['message' => 'Shift has already been ended'], 400);
        }

        $endAt = $request->end_at ?? now();
        
        // Parse dates properly
        $startTime = Carbon::parse($shift->start_at);
        $endTime = Carbon::parse($endAt);

        // Get all sales during the shift period
        $sales = SaleModel::with('details')
            ->where(function($query) use ($startTime, $endTime) {
                // For datetime format
                $query->whereBetween('sale_date', [$startTime, $endTime])
                      // For date-only format, check if sale date is with shift day
                      ->orWhereDate('sale_date', '>=', $startTime->toDateString())
                      ->whereDate('sale_date', '<=', $endTime->toDateString());
            })
            ->get();

        $totalItems = 0;
        $totalAmount = 0;

        foreach ($sales as $sale) {
            foreach ($sale->details as $detail) {
                $totalItems += $detail->qty;
                $totalAmount += $detail->total_price_usd;
            }
        }

        // Update shift with end details
        $shift->update([
            'end_at' => $endAt,
            'end_by' => $request->end_by,
            'total_item_sale' => $totalItems,
            'total_amount' => round($totalAmount, 2),
            'status' => $request->status ?? 'Completed',
        ]);

        $shift->load(['startUser', 'endUser']);

        return response()->json([
            'message' => 'Shift ended successfully',
            'shift' => [
                'id' => $shift->id,
                'start_by' => $shift->startUser?->name,
                'start_at' => $shift->start_at,
                'end_at' => $shift->end_at,
                'end_by' => $shift->endUser?->name,
                'amount_input' => $shift->amount_input,
                'total_item_sale' => $shift->total_item_sale,
                'total_amount' => $shift->total_amount,
                'status' => $shift->status,
            ]
        ], 200);
    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Something went wrong',
            'error' => $e->getMessage(),
        ], 500);
    }
}



}
