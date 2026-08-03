<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])
            ->orWhere('phone', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials provided.'],
            ]);
        }

        if ($user->status !== 'ACTIVE') {
            throw ValidationException::withMessages([
                'email' => ['Your account is currently suspended or inactive.'],
            ]);
        }

        // Generate Sanctum Access Token
        $token = $user->createToken('api_token_'.strtolower($user->role))->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
            'role' => $user->role,
        ];
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }
}
