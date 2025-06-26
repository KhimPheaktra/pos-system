<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ControlUserController extends Controller
{
    //
    public function deleteUser($id){
        try{
            DB::table('users')
            ->where('id',$id)
            ->update(['status' => 'DEL']);
            return response()->json([
                'message' => 'User deleted successfully'
            ],200);
        }
        catch(\Throwable $e){
             return response()->json([
                'message' => 'Delete failed. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
     public function bannedUser($id){
        try{
            DB::table('users')
            ->where('id',$id)
            ->update(['status' => 'BAN']);
            return response()->json([
                'message' => 'User has been banned'
            ],200);
        }
        catch(\Throwable $e){
             return response()->json([
                'message' => 'Changing failed. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



     public function deleteUserClient($id){
        try{
            DB::table('user_clients')
            ->where('id',$id)
            ->update(['status' => 'DEL']);
            return response()->json([
                'message' => 'User client deleted successfully'
            ],200);
        }
        catch(\Throwable $e){
             return response()->json([
                'message' => 'Delete failed. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bannedUserClient($id){
        try{
            DB::table('user_clients')
            ->where('id',$id)
            ->update(['status' => 'BAN']);
            return response()->json([
                'message' => 'User has been banned'
            ],200);
            }
         catch(\Throwable $e){
            return response()->json([
                'message' => 'Changing failed. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
