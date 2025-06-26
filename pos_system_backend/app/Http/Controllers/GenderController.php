<?php

namespace App\Http\Controllers;

use App\Models\GenderModel;
use Illuminate\Http\Request;

class GenderController extends Controller
{
      public function __construct()
        {
            $this->middleware('auth:sanctum');
        }

    //
    public function list(){
        $gender = GenderModel::all();
        try{
            if(!empty($gender)){
                return response()->json([
                    'gender' => $gender,
            ],200);
            }
            else{
                return response()->json([
                    'message' => 'No data found',
                ]);
            }
           
        }
        catch(\Throwable $e){
            return response()->json([
                    'message' => 'Something when wrong',
            ],500);
        }
        
    }
}
