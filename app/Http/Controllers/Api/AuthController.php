<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * API Login - Returns Sanctum token for mobile/app clients
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.',
            ], 401);
        }

        $user = Auth::user()->load('restaurant');

        $user->tokens()->delete();

        $token = $user->createToken('api-login', ['app'])->plainTextToken;

        $isLinked = $user->restaurant_id !== null;
        $userPayload = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_linked' => $isLinked,
            'restaurant_id' => $user->restaurant_id,
            'restaurant_name' => $user->restaurant?->name,
            'restaurant_location' => $user->restaurant?->location,
            'global_waiter_number' => $user->global_waiter_number,
            'roles' => $user->getRoleNames()->toArray(),
        ];
        if ($isLinked) {
            $userPayload['waiter_code'] = $user->waiter_code;
            $userPayload['waiter_qr_url'] = $user->waiter_qr_url;
        } else {
            $userPayload['waiter_code'] = null;
            $userPayload['waiter_qr_url'] = null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $userPayload,
        ]);
    }

    /**
     * API Logout - Revoke current token
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
