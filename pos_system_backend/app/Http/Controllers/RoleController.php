<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoleModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    //
      public function __construct()
        {
            $this->middleware('auth:sanctum');
        }


   public function list(){
        try {
            $roles = RoleModel::where('status','ACT')->with(['createBy', 'updateBy'])->get();

            if ($roles->isEmpty()) {
                return response()->json([
                    'message' => 'No data found'
                ], 404);
            }

            $roleData = [];

            foreach ($roles as $role) {
                $roleData[] = [
                    'id' => $role->id,
                    'name' => $role->name,
                    'status' => $role->status,
                    'created_by' => $role->createBy?->name,
                    'updated_by' => $role->updateBy?->name,
                ];
            }

            return response()->json([
                'roles' => $roleData
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getById($id){
        $role = RoleModel::where('status','ACT')->where('id',$id)->first();
        try{
            if(!empty($role)){
                return response()->json([
                    'role' => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'status' => $role->status,
                        'created_by' => $role->createBy ? $role->createBy->name : null,
                        'updated_by' => $role->updateBy ? $role->updateBy->name : null,
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
        ]);
        try{
            $role = RoleModel::create([
                'name' => $request->name,
                'status' => $request->status ?? 'ACT',
                'created_by' => Auth::id(),
                'updated_by' => null,

            ]);
                     
                if(!empty($role)){
                    return response()->json([
                    'message' => 'Role added successfully',
                        'role' => [
                            'id' => $role->id,
                            'name' => $role->name,
                            'status' => $role->status,
                            'created_by' => $role->createBy ? $role->createBy->name : null,
                            'updated_by' => $role->updateBy ? $role->updateBy->name : null,

                        ]
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
            $role->created_by = $request->created_by;
            $role->updated_by = Auth::id();
            
            $role->save();

            if(!empty($role)){
                return response()->json([
                    'message' => 'role updated successfully',
                        'role' => [
                            'id' => $role->id,
                            'name' => $role->name,
                            'status' => $role->status,
                            'created_by' => $role->createBy ? $role->createBy->name : null,
                            'updated_by' => $role->updateBy ? $role->updateBy->name : null,
                    ]
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
            ->update([
                'status' => 'DEL',
                'updated_by' => Auth::id()
            ]);

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
