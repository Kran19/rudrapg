<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $credentials): array
    {
        $loginId = trim($credentials['phone'] ?? $credentials['email'] ?? '');
        $digits = preg_replace('/\D+/', '', $loginId);
        $phone10 = strlen($digits) >= 10 ? substr($digits, -10) : null;

        $candidates = User::where(function ($query) use ($loginId, $phone10) {
            $query->where('email', $loginId)
                  ->orWhere('phone', $loginId);

            if ($phone10) {
                $query->orWhere('phone', $phone10)
                      ->orWhere('phone', '+91 ' . substr($phone10, 0, 5) . ' ' . substr($phone10, 5))
                      ->orWhere('phone', '+91' . $phone10)
                      ->orWhere('phone', '91' . $phone10)
                      ->orWhereRaw("SUBSTR(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), -10) = ?", [$phone10]);
            }
        })
        ->orderByRaw("CASE WHEN id IN (SELECT user_id FROM students WHERE user_id IS NOT NULL) THEN 0 ELSE 1 END")
        ->orderBy('id', 'desc')
        ->get();

        $user = null;
        foreach ($candidates as $candidate) {
            if (Hash::check($credentials['password'], $candidate->password)) {
                $user = $candidate;
                break;
            }
        }

        if (! $user) {
            $user = $candidates->first();
        }

        $passwordMatches = $user && Hash::check($credentials['password'], $user->password);

        if (! $user || ! $passwordMatches) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials provided. Please check your phone number and password.'],
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
