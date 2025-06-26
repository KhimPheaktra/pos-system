<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoleModel;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    //
      public function __construct()
        {
            $this->middleware('auth:sanctum');
        }


    public function list(){
        $role = RoleModel::where('status','ACT')->get();

        try{
            if(!empty($role)){
                return response()->json([
                'role' => $role
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
        $role = RoleModel::where('status','ACT')->where('id',$id)->first();
        try{
            if(!empty($role)){
                return response()->json([
                'role' => $role
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
        ]);
        try{
            $role = RoleModel::create([
                'name' => $request->name,
                'status' => $request->status ?? 'ACT',
            ]);
                     
                if(!empty($role)){
                    return response()->json([
                    'message' => 'Role added successfully',
                    'role' => $role,
                ],200);
                }
                else{
                    return response()->json([
                        'message' => 'Error adding role'
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
        ]);

        $role = RoleModel::findOrFail($id);
        try{
            $role->name = $request->name;
            $role->status = $request->status ?? 'ACT';

            $role->save();

            if(!empty($role)){
                return response()->json([
                    'message' => 'role updated successfully',
                    'role' => $role
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'Error updating role',
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
            DB::table('roles')
            ->where('id',$id)
            ->update(['status' => 'DEL']);

            return response()->json([
                'message' => 'role deleted successfully'
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
