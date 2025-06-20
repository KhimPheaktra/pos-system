<?php

namespace App\Http\Controllers;

use App\Models\ExchangeRateModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExchangeRateController extends Controller
{
    //

    protected function list(){
        return response()->json(ExchangeRateModel::all());
    }

    protected function add(Request $request){

        $request->validate([
            'base_currency' => 'required|string|max:255',
            'target_currency' => 'required|string|max:255',
            'rate' => 'required|numeric',
            'note' => 'required|string',
            
        ]);
        try{
            $exchangeRate = ExchangeRateModel::create([
                'base_currency' => $request->base_currency,
                'target_currency' => $request->target_currency,
                'rate' => $request->rate,
                'note' => $request->note,
            ]);

            return response()->json([
                'message' => 'Exchange rate added successfully',
                'exchangeRate' => $exchangeRate
            ],201);
        }
        catch(\Exception $e){
            Log::error('Error added exchange rate: ' . $e->getMessage());

            return response()->json([
                'message' => 'Something went wrong.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    protected function update(Request $request, $id){
         $request->validate([
            'base_currency' => 'required|string|max:255',
            'target_currency' => 'required|string|max:255',
            'rate' => 'required|numeric',
            'note' => 'required|string',
            
        ]);

        try{
            $exchangeRate = ExchangeRateModel::findOrFail($id);

            $exchangeRate->base_currency = $request->base_currency;
            $exchangeRate->target_currency = $request->target_currency;
            $exchangeRate->rate = $request->rate;
            $exchangeRate->note = $request->note;
            $exchangeRate->save();

            return response()->json([
                'message' => 'Exchange rate updated successfully ',
                'exchangeRate' => $exchangeRate
            ], 200);
        }
        catch(\Exception $e){
                return response()->json([
                'message' => 'Update failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    protected function delete($id){
        try{
            $exchangeRate = ExchangeRateModel::findOrFail($id);
            $exchangeRate->delete();

            return response()->json([
                'message' => 'Exchange deleted successfully.'
            ],200);
        }
        catch(\Exception $e){
              return response()->json([
            'message' => 'Delete failed.',
            'error' => $e->getMessage()
        ], 500);
        }
    }
   
}
