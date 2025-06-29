<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRateModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ExchangeRateController extends Controller
{
    //
      public function __construct()
        {
            $this->middleware('auth:sanctum');
        }


    public function list(){
        try{
              $exchangeRates = ExchangeRateModel::all();
            if(!empty($exchangeRate)){
                if($exchangeRates->isEmpty()){
                       return response()->json([
                        'message' => 'No data found '
                    ], 404);
                }
                
                $exchangeRateData = [];
                foreach ($exchangeRates as $exchangeRate){
                    $exchangeRateData[] = [
                    'id' => $exchangeRate->id,
                    'base_currency' => $exchangeRate->base_currency,
                    'target_currency' => $exchangeRate->target_currency,
                    'rate' => $exchangeRate->rate,
                    'note' => $exchangeRate->note,
                    'created_by' => $exchangeRate->createBy ? $exchangeRate->createBy->name : null,
                    'updated_by' => $exchangeRate->updateBy ? $exchangeRate->updateBy->name : null,
                    ];
                }

                return response()->json([
                    'exchangeRate' => $exchangeRateData
                ],200);
            }
          

        }
        catch(\Throwable $e){
            Log::error('Error get exchange rate: ' . $e->getMessage());

        return response()->json([
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ], 500); // 500 Internal Server Error
        }
     
    }

    public function getById($id){
        $exchangeRate = ExchangeRateModel::findOrFail($id);
        try{
            if(!empty($exchangeRate)){
                return response()->json([
                    'exchangeRate' => [
                    'id' => $exchangeRate->id,
                    'base_currency' => $exchangeRate->base_currency,
                    'target_currency' => $exchangeRate->target_currency,
                    'rate' => $exchangeRate->rate,
                    'note' => $exchangeRate->note,
                    'created_by' => $exchangeRate->createBy ? $exchangeRate->createBy->name : null,
                    'updated_by' => $exchangeRate->updateBy ? $exchangeRate->updateBy->name : null,
                ]
                ],200);
            }   
            else{
                return response()->json([
                'message' => 'No data found '
            ], 404);
            }
          
        }
        catch(\Throwable $e){
            Log::error('Error get exchange rate by id: ' . $e->getMessage());

        return response()->json([
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ], 500); // 500 Internal Server Error
        }
        
    }

    public function add(Request $request){

        $request->validate([
            'base_currency' => 'required|string|max:255',
            'target_currency' => 'required|string|max:255',
            'rate' => 'required|numeric',
            'note' => 'nullable|string',
            
        ]);
        try{
            $exchangeRate = ExchangeRateModel::create([
                'base_currency' => $request->base_currency,
                'target_currency' => $request->target_currency,
                'rate' => $request->rate,
                'note' => $request->note,
                'created_by' => Auth::id(),
                'updated_by' => null,
            ]);

            if(!empty($exchangeRate)){
                return response()->json([
                'message' => 'Exchange rate added successfully',
                'exchangeRate' => [
                    'id' => $exchangeRate->id,
                    'base_currency' => $exchangeRate->base_currency,
                    'target_currency' => $exchangeRate->target_currency,
                    'rate' => $exchangeRate->rate,
                    'note' => $exchangeRate->note,
                    'created_by' => $exchangeRate->createBy ? $exchangeRate->createBy->name : null,
                    'updated_by' => $exchangeRate->updateBy ? $exchangeRate->updateBy->name : null,
                ]
            ],201);
            }
            else{
                 return response()->json([
                'message' => 'Add failed '
            ], 500);
            }
         
        }
        catch(\Throwable $e){
            Log::error('Error added exchange rate: ' . $e->getMessage());

            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    public function update(Request $request, $id){
         $request->validate([
            'base_currency' => 'required|string|max:255',
            'target_currency' => 'required|string|max:255',
            'rate' => 'required|numeric',
            'note' => 'nullable|string',
            
        ]);

        $exchangeRate = ExchangeRateModel::findOrFail($id);
        
        $validator = Validator::make($request->only('target_currency'), [
            'target_currency' => ['required', 'string', Rule::unique('exchange_rate', 'code')->ignore($exchangeRate->id)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try{
            

            $exchangeRate->base_currency = $request->base_currency;
            $exchangeRate->target_currency = $request->target_currency;
            $exchangeRate->rate = $request->rate;
            $exchangeRate->note = $request->note;
            $exchangeRate->created_by = $request->created_by;
            $exchangeRate->updated_by = Auth::id();
            $exchangeRate->save();

            if(!empty($exchangeRate)){
                return response()->json([
                'message' => 'Exchange rate updated successfully ',
                  'exchangeRate' => [
                    'id' => $exchangeRate->id,
                    'base_currency' => $exchangeRate->base_currency,
                    'target_currency' => $exchangeRate->target_currency,
                    'rate' => $exchangeRate->rate,
                    'note' => $exchangeRate->note,
                    'created_by' => $exchangeRate->createBy ? $exchangeRate->createBy->name : null,
                    'updated_by' => $exchangeRate->updateBy ? $exchangeRate->updateBy->name : null,
                ]
            ], 200);
            }
            else{
                 return response()->json([
                'message' => 'Update failed'
            ], 404);
            }
            
        }
        catch(\Throwable $e){
                return response()->json([
                'message' => 'Update failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id){
        try{
            $exchangeRate = ExchangeRateModel::findOrFail($id);
            $exchangeRate->delete();

            return response()->json([
                'message' => 'Exchange deleted successfully.'
            ],200);
        }
        catch(\Throwable $e){
              return response()->json([
            'message' => 'Delete failed.',
            'error' => $e->getMessage()
        ], 500);
        }
    }
   
}
