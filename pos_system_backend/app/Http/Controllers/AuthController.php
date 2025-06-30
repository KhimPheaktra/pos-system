<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\RefreshTokenModel;
use App\Models\User;
use App\Models\UserLoginLogoutInfoModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
  

    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->validated();

            if (!Auth::guard('web')->attempt($credentials)) {
                return response()->json(['message' => 'Email or password are wrong.'], 401);
            }

            /** @var \App\Models\User $user */
            $user = Auth::guard('web')->user();

            if ($user->status === 'BAN') {
                return response()->json(['message' => 'Your account has been banned.'], 403);
            }

            if ($user->status === 'DEL') {
                return response()->json(['message' => 'Your account has been deleted.'], 403);
            }

            $user->is_active = 1;
            $user->save();

            UserLoginLogoutInfoModel::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'login_at' => now(),
                'logout_at' => null,
            ]);

            // Create short-lived access token (15 min)
            $tokenResult = $user->createToken('api-token', ['*']);
            $token = $tokenResult->accessToken;
            $token->expires_at = now()->addMinutes(15);
            $token->save();

            $accessToken = $tokenResult->plainTextToken;

            // Create secure refresh token
            $refreshTokenString = hash('sha256', Str::random(64));

            $user->refreshTokens()->create([
                'token' => $refreshTokenString,
                'expires_at' => now()->addDays(7),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // $cookie = cookie(
            //     'refresh_token',
            //     $refreshTokenString,
            //     60 * 24 * 7, // 7 days
            //     '/',
            //     null,
            //     true,  // Secure
            //     true,  // HttpOnly
            //     false, // raw
            //     'Strict' // SameSite
            // );

            $cookie = cookie(
                'refresh_token',
                $refreshTokenString,
                60 * 24 * 7,
                '/',        // Use root path so cookie is sent on all API routes
                null,
                false,      // Secure = false for localhost (HTTP, not HTTPS)
                false,       // HttpOnly = true
                false,
                'Lax'       // SameSite = 'Lax' or 'None' with Secure = true in prod
            );

            return response()->json([
                'user' => $user,
                'token' => $accessToken,
                'expires_at' => $token->expires_at->toDateTimeString(),
            ])->cookie($cookie);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Login failed. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function refreshToken(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return response()->json(['message' => 'Refresh token not found.'], 401);
        }

        $tokenRecord = RefreshTokenModel::where('token', $refreshToken)->first();

        if (!$tokenRecord || $tokenRecord->isExpired()) {
            return response()->json(['message' => 'Refresh token is invalid or expired.'], 401);
        }

        $user = $tokenRecord->user;
        $user->tokens()->delete();

        // Create new access token
        $tokenResult = $user->createToken('api-token', ['*']);
        $token = $tokenResult->accessToken;
        $token->expires_at = now()->addMinutes(15);
        $token->save();

        $accessToken = $tokenResult->plainTextToken;

        // Delete the used refresh token
        $tokenRecord->delete();

        // Generate and store new refresh token
        $newRefreshTokenString = hash('sha256', Str::random(64));

        $user->refreshTokens()->create([
            'token' => $newRefreshTokenString,
            'expires_at' => now()->addDays(7),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Return new refresh token cookie
        $cookie = cookie(
            'refresh_token',
            $newRefreshTokenString,
            60 * 24 * 7,
            '/',        // Use root path so cookie is sent on all API routes
            null,
            false,      // Secure = false for localhost (HTTP, not HTTPS)
            false,       // HttpOnly = true
            false,
            'Lax'       // SameSite = 'Lax' or 'None' with Secure = true in prod
        );

        return response()->json([
            'token' => $accessToken,
        ])->cookie($cookie);
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

            $user->load('role');
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


   public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            // Mark user as inactive
            $user->is_active = 0;
            $user->save();

            UserLoginLogoutInfoModel::where('user_id', $user->id)
                ->whereNull('logout_at')
                ->latest() // get the most recent login
                ->first()
                ?->update(['logout_at' => now()]);

            // Delete current access token
            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
            }

            // Delete all refresh tokens for user
            $user->refreshTokens()->delete();

            // Clear the refresh_token cookie by sending an expired cookie
            $cookie = cookie()->forget('refresh_token');

            return response()->json([
                'message' => 'Logout succeed'
            ], 204)->cookie($cookie);
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

            $user->load('role'); 

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
