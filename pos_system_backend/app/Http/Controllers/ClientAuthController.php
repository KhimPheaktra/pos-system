<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\RefreshClientTokenModel;
use App\Models\User;
use App\Models\UserClientLoginLogoutInfoModel;
use App\Models\UserClientModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;

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


        // $tokenResult = $user->createToken('api-token', ['*'], now()->addHours(48));
        // $accessToken = $tokenResult->plainTextToken;

         // Create access token 
            $tokenResult = $user->createToken('api-token', ['*']);
            $token = $tokenResult->accessToken; // model instance

            // Set token expiration 24 hours from now
            $token->expires_at = now()->addMinutes(15);
            $token->save();

            $accessToken = $tokenResult->plainTextToken;

        // Create refresh token (7 days)
            $refreshTokenString = hash('sha256', Str::random(64));
            $user->refreshTokens()->create([
                'token' => $refreshTokenString,
                'expires_at' => now()->addDays(7),
        ]);

        // Store refresh token in HttpOnly cookie
        $cookie = cookie('refresh_token', $refreshTokenString, 60 * 24 * 7, null, null, true, true, false, 'Strict');

        return response()->json([
            'user' => $user,
            'token' => $accessToken,
            'expires_at' => $token->expires_at->toDateTimeString(),
        ], 200)->cookie($cookie);

    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'Login failed. Please try again later.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function refreshToken(Request $request)
    {
        $refreshToken = $request->cookie('refresh_token');

        if (!$refreshToken) {
            return response()->json(['message' => 'Refresh token not found.'], 401);
        }

        $tokenRecord = RefreshClientTokenModel::where('token', $refreshToken)->first();

        if (!$tokenRecord || $tokenRecord->isExpired()) {
            return response()->json(['message' => 'Refresh token is invalid or expired.'], 401);
        }

        $user = $tokenRecord->user;

        // Revoke all previous tokens (optional)
            $user->tokens()->delete();

            $tokenResult = $user->createToken('api-token', ['*']);
            $token = $tokenResult->accessToken; // model instance

            // Set token expiration 24 hours from now
            $token->expires_at = now()->addHours(24);
            $token->save();

            $accessToken = $tokenResult->plainTextToken;

        // Rotate refresh token
        $newRefreshTokenString = hash('sha256', Str::random(64));
        $tokenRecord->update([
            'token' => $newRefreshTokenString,
            'expires_at' => now()->addDays(7),
        ]);

        $cookie = cookie('refresh_token', $newRefreshTokenString, 60 * 24 * 7, null, null, true, true, false, 'Strict');

        return response()->json([
            'token' => $accessToken,
            'expires_at' => $token->expires_at->toDateTimeString(),
        ])->cookie($cookie);
    }


    public function registerClient(RegisterRequest $request)
    {
        try {
            // Check if email exists in staff/admin users
            if (User::where('email', $request->email)->exists()) {
                return response()->json([
                    'message' => 'Email is already in use'
                ], 422);
            }

            // Handle image upload
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
                'image' => $imagePath ?? null,
            ]);

            // Mark user as active
            $userClient->is_active = 1;
            $userClient->save();

            // Log registration/login time
            UserClientLoginLogoutInfoModel::create([
                'user_id' => $userClient->id, 
                'name' => $userClient->name,
                'email' => $userClient->email,
                'login_at' => now(),
                'logout_at' => null,
            ]);

            // Create access token valid for 48 hours
            $tokenResult = $userClient->createToken('api-token', ['*']);
            $token = $tokenResult->accessToken; // model instance

            // Set token expiration 24 hours from now
            $token->expires_at = now()->addMinutes(15);
            $token->save();

            $accessToken = $tokenResult->plainTextToken;

            // Create refresh token valid for 7 days
            $refreshTokenString = hash('sha256', Str::random(64));
            $userClient->refreshTokens()->create([
                'token' => $refreshTokenString,
                'expires_at' => now()->addDays(7),
            ]);

            // Store refresh token in HttpOnly cookie
            $cookie = cookie('refresh_token', $refreshTokenString, 60 * 24 * 7, null, null, true, true, false, 'Strict');

            return response()->json([
                'message' => 'Registered successfully',
                'userClient' => $userClient,
                'token' => $accessToken,
                'expires_at' => $token->expires_at->toDateTimeString(),
            ], 201)->cookie($cookie);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Register failed. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }



   public function logout(Request $request)
    {
        try {
            $userClient = $request->user();

            if (!$userClient) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $userClient->is_active = 0;
            $userClient->save();

            UserClientLoginLogoutInfoModel::where('user_id', $userClient->id)
                ->whereNull('logout_at')
                ->latest()
                ->first()
                ?->update(['logout_at' => now()]);

            // Delete access token
            $token = $userClient->currentAccessToken();
            if ($token) {
                $token->delete();
            }

            // Delete all refresh tokens
            $userClient->refreshTokens()->delete();

            // Clear refresh token cookie
            $cookie = cookie()->forget('refresh_token');

            return response()->json([
                'message' => 'Logout succeed'
            ], 204)->cookie($cookie);
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
