<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WaiterRegistrationRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class WaiterRegistrationController extends Controller
{
    /**
     * API Registration for waiters - No CSRF protection required
     */
    public function register(WaiterRegistrationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'email' => $validated['email'],
            'auth_provider' => 'email',
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'location' => $validated['location'] ?? null,
            'restaurant_id' => null,
            'waiter_code' => null,
            'global_waiter_number' => User::generateGlobalWaiterNumber(),
        ]);

        $user->assignRole('waiter');

        // Create Sanctum token for immediate API access
        $token = $user->createToken('api-registration')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Akaunti yako imefunguliwa. Nambari yako ya pekee: '.$user->global_waiter_number.'. Ongea na manager wa restaurant ili akuunge.',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'global_waiter_number' => $user->global_waiter_number,
                'is_linked' => false,
                'restaurant_id' => null,
                'waiter_code' => null,
                'roles' => $user->getRoleNames()->toArray(),
            ]
        ], 201);
    }
}
