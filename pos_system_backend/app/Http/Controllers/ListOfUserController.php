<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserClientModel;
use Illuminate\Http\Request;

class ListOfUserController extends Controller
{
    //
    public function __construct()
        {
            $this->middleware('auth:sanctum');
        }

     public function listStaff()
        {
            try {
                
                $user = User::where('status', 'ACT')
                    ->with(['createBy', 'updateBy'])
                    ->get();

                if ($user->isEmpty()) {
                    return response()->json([
                        'message' => 'No data found'
                    ], 404);
                }

                $data = $user->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role ?->name,
                        'image' => $user->image,
                        'created_by' => $user->createBy?->name,
                        'updated_by' => $user->updateBy?->name,
                    ];
                });

                return response()->json([
                    'user' => $data
                ], 200);
            } catch (\Throwable $e) {

                return response()->json([
                    'message' => 'Something went wrong',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        
    public function listClient()
        {
            try {
                $userClient = UserClientModel::where('status', 'ACT')
                    ->with(['updateBy'])
                    ->get();

                if ($userClient->isEmpty()) {
                    return response()->json([
                        'message' => 'No data found'
                    ], 404);
                }

                $data = $userClient->map(function ($userClient) {
                    return [
                        'id' => $userClient->id,
                        'name' => $userClient->name,
                        'email' => $userClient->email,
                        'role' => $userClient->role ?->name,
                        'image' => $userClient->image,
                        'updated_by' => $userClient->updateBy?->name,
                    ];
                });

                return response()->json([
                    'userClient' => $data
                ], 200);
            } catch (\Throwable $e) {

                return response()->json([
                    'message' => 'Something went wrong',
                    'error' => $e->getMessage()
                ], 500);
            }
        }


        public function getUserStaffById($id)
        {
            try {
                
                $user = User::where('status', 'ACT')->where('id',$id)
                    ->with(['createBy', 'updateBy'])
                    ->first();

                if (!empty($user)) {
                    return response()->json([
                        'user' => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role ?->name,
                            'image' => $user->image,
                            'updated_by' => $user->updateBy?->name,
                        ]
                    ], 200);
                }
                else{
                    return response()->json([
                        'message' => 'No data found'
                    ],404);
                }
                
            } catch (\Throwable $e) {
                return response()->json([
                    'message' => 'Something went wrong',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        
    public function getUserClientById($id)
        {
            try {
                $userClient = UserClientModel::where('status', 'ACT')->where('id',$id)
                    ->with(['updateBy'])
                    ->first();

                if (!empty($userClient)) {
                    return response()->json([
                        'userClient' =>[
                            'id' => $userClient->id,
                            'name' => $userClient->name,
                            'email' => $userClient->email,
                            'role' => $userClient->role ?->name,
                            'image' => $userClient->image,
                            'updated_by' => $userClient->updateBy?->name,
                        ]
                    ], 200);
                }
                else{
                    return response()->json([
                        'message' => 'No data found'
                    ],404); 
                }
            } catch (\Throwable $e) {

                return response()->json([
                    'message' => 'Something went wrong',
                    'error' => $e->getMessage()
                ], 500);
            }
        }



}
