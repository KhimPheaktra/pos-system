<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\UserLoginLogoutInfoModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    //
  public function login(LoginRequest $request)
    {
        try{
            $credentials = $request->validated();

            // Attempt to login with the 'web' guard
            if (!Auth::guard('web')->attempt($credentials)) {
                return response()->json([
                    'message' => 'Email or password are wrong.'
                ], 401);
            }

              /** @var \App\Models\User $user **/
            // Retrieve the authenticated user
            $user = Auth::guard('web')->user();

            // for accounts got banned
            if ($user->status === 'BAN') {
                return response()->json([
                    'message' => 'Your account has been banned due to policy violations. Please contact admin.'
                ], 403);
            }

            // for deleted accounts 
            if ($user->status === 'DEL') {
                return response()->json([
                    'message' => 'Your account has been deleted. Please contact admin if this is a mistake.'
                ], 403);
            }
            // Mark user as active
            $user->is_active = 1;
            $user->save();

            UserLoginLogoutInfoModel::create([
                'user_id' => Auth::id(),
                'name' => $user->name,
                'email' => $user->email,
                'login_at' => now(),
                'logout_at' => null,
            ]);

            // Create Sanctum token valid for 24 hours
            $tokenResult = $user->createToken('api-token', ['*'], now()->addHours(24));
            $token = $tokenResult->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 200);
        }
        catch(\Throwable $e){
              return response()->json([
                'message' => 'Login failed. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
        
    }



    public function registerStaff(RegisterRequest $request)
    {
        try{
            $allowedRoles = ['Admin', 'SuperAdmin'];
            $user = auth()->user();
            /** @var \App\Models\User $user */
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user->load('role'); // eager load the role relation

            $userRole = $user->role?->name;

            if (!in_array($userRole, $allowedRoles)) {
                return response()->json(['message' => 'Forbidden'], 403);
            }


            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('staff_images', 'public');
            } else {
                $imagePath = null;
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'created_by' => Auth::id(),
                'updated_by' => null ,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id, 
                'image' => $imagePath ?? null ,
            ]);

            return response()->json([
                'message' => 'Staff registered successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role_id' => $user->role ? $user->role->name : null,
                    'created_by' => $user->createBy ? $user->createBy->name : null,
                    'updated_by' => $user->updateBy ? $user->updateBy->name : null,
                    'image' => $user->image,
                ],
            ], 201);
            }
            catch(\Throwable $e){
                return response()->json([
                    'message' => 'Register failed. Please try again later.',
                    'error' => $e->getMessage()
                ], 500);
            }
        
        }


   public function logout(Request $request) {
    try {
        $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            // Mark user as inactive
            $user->is_active = 0;
            $user->save();
            UserLoginLogoutInfoModel::where('user_id', Auth::id())
                ->whereNull('logout_at')
                ->latest() // get the most recent login
                ->first()
                ?->update(['logout_at' => now()]);


            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
            }
            return response('', 204);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // Update 
    public function update(RegisterRequest $request,$id){
        $user = User::findOrFail($id);
         $validator = Validator::make($request->only('emial'), [
            'emial' => ['required', 'string', Rule::unique('users', 'emial')->ignore($user->id)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        try{
            $allowedRoles = ['Admin', 'SuperAdmin'];
            $user = auth()->user();
            /** @var \App\Models\User $user */
            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user->load('role'); // eager load the role relation

            $userRole = $user->role?->name;

            if (!in_array($userRole, $allowedRoles)) {
                return response()->json(['message' => 'Forbidden'], 403);
            }


            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('staff_images', 'public');
            } else {
                $imagePath = null;
            }

                $user->name = $request->name;
                $user->email = $request->email;
                $user->password = $request->password;
                $user->role_id = $request->role_id;
                $user->created_by = $request->created_by;
                $user->updated_by = Auth::id();
                $user->image = $imagePath ?? null;
                $user->save();

            return response()->json([
                'message' => 'Staff updated successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role_id' => $user->role ? $user->role->name : null,
                    'created_by' => $user->createBy ? $user->createBy->name : null,
                    'updated_by' => $user->updateBy ? $user->updateBy->name : null,
                    'image' => $user->image,
                ],
            ], 201);
        }
        catch(\Throwable $e){
            return response()->json([
                'message' => 'Update failed. Please try again later.',
                'error' => $e->getMessage()
                ], 500);
        }
        
    }

    

}
