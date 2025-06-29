<?php

namespace App\Http\Controllers;

use App\Models\PositionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PositionController extends Controller
{
    //
    public function __construct()
        {
            $this->middleware('auth:sanctum');
        }


    public function list() {
        try {
            $positions = PositionModel::where('status', 'ACT')
                ->with(['createBy', 'updateBy'])
                ->get();

            if ($positions->isEmpty()) {
                return response()->json([
                    'message' => 'No data found'
                ], 404);
            }

            $positionData = [];

            foreach ($positions as $position) {
                $positionData[] = [
                    'id' => $position->id,
                    'name' => $position->name,
                    'note' => $position->note,
                    'created_by' => $position->createBy?->name,
                    'updated_by' => $position->updateBy?->name,
                ];
            }

            return response()->json([
                'positions' => $positionData
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getById($id){
        $position = PositionModel::where('status','ACT')->where('id',$id)->first();
        try{
            if(!empty($position)){
                return response()->json([
                 'position' => [
                    'id' => $position->id,
                    'name' => $position->name,
                    'note' => $position->note,
                    'created_by' => $position->createBy ? $position->createBy->name : null,
                    'updated_by' => $position->updateBy ? $position->updateBy->name : null,

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
                'created_by' => Auth::id(),
                'updated_by' => null,
            ]);
                     
                if(!empty($position)){
                    return response()->json([
                    'message' => 'Position added successfully',
                     'position' => [
                        'id' => $position->id,
                        'name' => $position->name,
                        'note' => $position->note,
                        'created_by' => $position->createBy ? $position->createBy->name : null,
                        'updated_by' => $position->updateBy ? $position->updateBy->name : null,

                ]
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
            $position->created_by = $request->created_by;
            $position->updated_by = Auth::id();

            $position->save();

            if(!empty($position)){
                return response()->json([
                    'message' => 'Position updated successfully',
                     'position' => [
                        'id' => $position->id,
                        'name' => $position->name,
                        'note' => $position->note,
                        'created_by' => $position->createBy ? $position->createBy->name : null,
                        'updated_by' => $position->updateBy ? $position->updateBy->name : null,

                ]
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
            ->update([
                'status' => 'DEL',
                'updated_by' => Auth::id()
            ]);

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
