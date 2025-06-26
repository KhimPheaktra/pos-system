<?php

namespace App\Http\Controllers;

use App\Models\EmployeeModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    //

      public function __construct()
        {
            $this->middleware('auth:sanctum');
        }

    public function list(){
        $employee = EmployeeModel::where('status','ACT')->get();

        try{
            if(!empty($employee))
            return response()->json([
                'employee' => $employee->map(function ($employee){
                    return [
                        'id' => $employee->id,
                        'first_name' => $employee->first_name,
                        'last_name' => $employee->last_name,
                        'gender' => $employee->gender ? $employee->gender->name : null,
                        'dob' => $employee->dob,
                        'pob' => $employee->province ? $employee->province->name : null,
                        'salary' => $employee->salary,
                        'position' => $employee->position ? $employee->position->name : null,
                        'image' => $employee->image,
                    ];
                })
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
            DB::table('employees')
            ->where('id',$id)
            ->update(['status' => 'DEL']);

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
