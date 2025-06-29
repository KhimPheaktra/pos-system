<?php

namespace App\Http\Controllers;

use App\Models\EmployeeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class EmployeeController extends Controller
{
    //

      public function __construct()
        {
            $this->middleware('auth:sanctum');
        }

    public function list()
    {
        try {
            $employees = EmployeeModel::where('status', 'ACT')
                ->with(['gender', 'province', 'position', 'createBy', 'updateBy'])
                ->get();

            if ($employees->isEmpty()) {
                return response()->json([
                    'message' => 'No data found'
                ], 404);
            }

            $data = $employees->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'gender' => $employee->gender?->name,
                    'dob' => $employee->dob,
                    'pob' => $employee->province?->name,
                    'salary' => $employee->salary,
                    'position' => $employee->position?->name,
                    'image' => $employee->image,
                    'created_by' => $employee->createBy?->name,
                    'updated_by' => $employee->updateBy?->name,
                ];
            });

            return response()->json([
                'employees' => $data
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Error getting employee: ' . $e->getMessage());

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getById($id){
         $employee = EmployeeModel::where('status', 'ACT')
                         ->where('id', $id)
                         ->first();
        try{
            if(!empty($employee))
            return response()->json([
                 'employee' => [
                        'id' => $employee->id,
                        'first_name' => $employee->first_name,
                        'last_name' => $employee->last_name,
                        'gender' => $employee->gender ? $employee->gender->name : null,
                        'dob' => $employee->dob,
                        'pob' => $employee->province ? $employee->province->name : null,
                        'salary' => $employee->salary,
                        'position' => $employee->position ? $employee->position->name : null,
                        'image' => $employee->image,
                        'created_by' => $employee->createBy ? $employee->createBy->name : null,
                        'updated_by' => $employee->updateBy ? $employee->updateBy->name : null,
                        
                    ]
                
            ],200);
            else{
                return response()->json([
                    'message' => 'No data found'
                ],404);
            }
        }
        catch(\Throwable $e){
            Log::error('Error get employee: ' . $e->getMessage());

              return response()->json([
                'message' => 'Something when wrong',
                'error' => $e->getMessage()
            ],500);
        }
    }

    public function add(Request $request){
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender_id' => 'required|integer|exists:genders,id',
            'dob' => 'required|date',
            'pob' => 'required|integer|exists:provinces,id',
            'salary' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'position_id' => 'required|integer|exists:positions,id',
        ]);

        try{
            $imagePath = null;
            if($request->hasFile('image')){
                $imagePath = $request->file('image')->store('employee','public');
            }

            $employee = EmployeeModel::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'gender_id' => $request->gender_id,
                'dob' => $request->dob,
                'pob' => $request->pob,
                'salary' => $request->salary,
                'image' => $imagePath,
                'status' => $request->status ?? 'ACT',
                'position_id' => $request->position_id,
                'created_by' => Auth::id(),
                'updated_by' => null,
            ]);
              $employee = EmployeeModel::with('gender','pob','position')->find($employee->id);

              if(!empty($employee)){
                return response()->json([
                    'message' => 'Employee added successfully',
                    'employee' =>[
                        'id' => $employee->id,
                        'first_name' => $employee->first_name,
                        'last_name' => $employee->last_name,
                        'gender' => $employee->gender ? $employee->gender->name : null,
                        'dob' => $employee->dob,
                        'pob' => $employee->province ? $employee->province->name : null,
                        'salary' => $employee->salary,
                        'position' => $employee->position ? $employee->position->name : null,
                        'image' => $employee->image,
                        'created_by' => $employee->createBy ? $employee->createBy->name : null,
                        'updated_by' => $employee->updateBy ? $employee->updateBy->name : null,
                    ]
                ],200);
              }
              else{
                return response()->json([
                    'message' => 'Add failed',
                ],500);
              }      
        }
        catch(\Throwable $e){
            Log::error('Error add employee'. $e->getMessage());
             return response()->json([
                    'message' => 'Something when wrong',
                    'error' => $e->getMessage()
            ],500);
        }
    }

    public function update(Request $request,$id){
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender_id' => 'required|integer|exists:genders,id',
            'dob' => 'required|string',
            'pob' => 'required|integer|exists:provinces,id',
            'salary' => 'nullable|numeric',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'position_id' => 'required|integer|exists:positions,id',
        ]);

        $employee = EmployeeModel::findOrFail($id);
        try{
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('employee', 'public');
                $employee->image = $imagePath;
            }
            $employee->first_name = $request->first_name;
            $employee->last_name = $request->last_name;
            $employee->gender_id = $request->gender_id;
            $employee->dob = $request->dob;
            $employee->salary = $request->salary;
            $employee->created_by = $request->created_by;
            $employee->updated_by = Auth::id();
            $employee->save();

            if(!empty($employee)){
                return response()->json([
                    'message' => 'Employee updated successfully',
                    'employee' =>
                    [
                        'id' => $employee->id,
                        'first_name' => $employee->first_name,
                        'last_name' => $employee->last_name,
                        'gender' => $employee->gender ? $employee->gender->name : null,
                        'dob' => $employee->dob,
                        'pob' => $employee->province ? $employee->province->name : null,
                        'salary' => $employee->salary,
                        'position' => $employee->position ? $employee->position->name : null,
                        'image' => $employee->image,
                        'created_by' => $employee->createBy ? $employee->createBy->name : null,
                        'updated_by' => $employee->updateBy ? $employee->updateBy->name : null,
                    ]
        
                ]);
            }
            else{
                return response()->json([
                    'message' => 'Error updating employee',
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
            $employee = DB::table('employees')
            ->where('id',$id)
            ->update([
                'status' => 'DEL',
                'updated_by' => Auth::id(),
            ]);
    
            return response()->json([
                'message' => 'Employee deleted successfully'
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
