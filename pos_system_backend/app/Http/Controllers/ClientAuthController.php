<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\UserClientLoginLogoutInfoModel;
use App\Models\UserClientModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientAuthController extends Controller
{
    //

    public function login(LoginRequest $request)
    {
        try{
            $credentials = $request->validated();
            $user = UserClientModel::where('email', $credentials['email'])->first();

            if (! $user || ! Hash::check($credentials['password'], $user->password)) {
                return response()->json(['message' => 'Email or password are wrong.'], 401);
            }
            // for accounts got banned
            if ($user->status === 'BAN') {
                return response()->json([
                    'message' => 'Your account has been banned due to policy violations. Please contact support.'
                ], 403);
            }

            // for deleted accounts 
            if ($user->status === 'DEL') {
                return response()->json([
                    'message' => 'Your account has been deleted. Please contact support if this is a mistake.'
                ], 403);
            }


            // Mark user as active
            $user->is_active = 1;
            $user->save();
            UserClientLoginLogoutInfoModel::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'login_at' => now(),
                'logout_at' => null,
            ]);

            // Create Sanctum token (24 hours expiry)
            $tokenResult = $user->createToken('api-token', ['*'], now()->addHours(48));
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



    public function registerClient(RegisterRequest $request)
    {
        try{
             //     check if email exists in staff/admin users
            if (User::where('email', $request->email)->exists()) {
                return response()->json([
                    'message' => 'Email is already in use'
                ], 422);
            }
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('client_images', 'public');
            } else {
                $imagePath = null;
            }


            $userClient = UserClientModel::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => 3,  
                'image' => $imagePath ?? null ,
            ]);
            $tokenResult = $userClient->createToken('api-token', ['*'], now()->addHours(48));
            $token = $tokenResult->plainTextToken;
            // Mark user as active
            $userClient->is_active = 1;
            $userClient->save();
 
            UserClientLoginLogoutInfoModel::create([
                'user_id' => Auth::id(),
                'name' => $userClient->name,
                'email' => $userClient->email,
                'login_at' => now(),
                'logout_at' => null,
            ]);

            return response()->json([
                'message' => 'Registered successfully',
                'userClient' => $userClient,
                'token' => $token
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
        $userClient = $request->user();
        if (!$userClient) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        // Mark user as inactive
        $userClient->is_active = 0;
        $userClient->save();
        UserClientLoginLogoutInfoModel::where('user_id', Auth::id())
                ->whereNull('logout_at')
                ->latest() // get the most recent login
                ->first()
                ?->update(['logout_at' => now()]);

        $token = $userClient->currentAccessToken();
        if ($token) {
            $token->delete();
        }
        return response('', 204);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

   public function update(Request $request, $id)
    {
        $userClient = UserClientModel::findOrFail($id);

        // Validate only 'name' and 'image'
        $validator = Validator::make($request->only('name', 'image'), [
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:2048'], // max 2MB, optional
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('client_images', 'public');
                $userClient->image = $imagePath;
            }

            $userClient->name = $request->name;
            $userClient->save();

            return response()->json([
                'message' => 'Update successful',
                'userClient' => $userClient,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Update failed. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



}
