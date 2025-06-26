<?php

namespace App\Http\Controllers;

use App\Models\ProvinceModel;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }


    public function list(){
        $province = ProvinceModel::all();
        try{
            if(!empty($province)){
                return response()->json([
                    'province' => $province
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'No data found'
                ]);
            }
        }
        catch(\Throwable $e){
            return response()->json([
                'message' => 'Something when wrong',
                'error' => $e->getMessage()
            ],500);
        }
    }
}
