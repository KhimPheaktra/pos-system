<?php

namespace App\Http\Controllers;

use App\Models\PositionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PositionController extends Controller
{
    //
    public function __construct()
        {
            $this->middleware('auth:sanctum');
        }


    public function list(){
        $position = PositionModel::where('status','ACT')->get();

        try{
            if(!empty($position)){
                return response()->json([
                'position' => $position
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
                'message' => 'Something when wrong ',
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function getById($id){
        $position = PositionModel::where('status','ACT')->where('id',$id)->first();
        try{
            if(!empty($position)){
                return response()->json([
                'position' => $position
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
                'message' => 'Something when wrong ',
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function add(Request $request){
        $request->validate([
            'name' => 'nullable|string',
            'note' => 'nullable|string',
        ]);
        try{
            $position = PositionModel::create([
                'name' => $request->name,
                'note' => $request->note,
                'status' => $request->status ?? 'ACT',
            ]);
                     
                if(!empty($position)){
                    return response()->json([
                    'message' => 'Position added successfully',
                    'position' => $position,
                ],200);
                }
                else{
                    return response()->json([
                        'message' => 'Error adding position'
                    ],500);
                }
           
        }
        catch(\Throwable $e){
            return response()->json([
                'message' => 'Something when wrong',
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function update(Request $request,$id){
          $request->validate([
            'name' => 'nullable|string',
            'note' => 'nullable|string',
        ]);

        $position = PositionModel::findOrFail($id);
        try{
            $position->name = $request->name;
            $position->note = $request->note;
            $position->status = $request->status ?? 'ACT';

            $position->save();

            if(!empty($position)){
                return response()->json([
                    'message' => 'Position updated successfully',
                    'position' => $position
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'Error updating position',
                ],500);
            }
        }   
        catch(\Throwable $e){
                return response()->json([
                    'message' => 'Something when wrong',
                    'error' => $e->getMessage()
                ],500);
        }
    }

    public function delete($id){
        try{
            DB::table('positions')
            ->where('id',$id)
            ->update(['status' => 'DEL']);

            return response()->json([
                'message' => 'Position deleted successfully'
            ],200);

        }
        catch(\Throwable $e){
            return response()->json([
                'message' => 'Something when wrong',
                'error' => $e->getMessage()  
            ],500);

        }
    }
}
