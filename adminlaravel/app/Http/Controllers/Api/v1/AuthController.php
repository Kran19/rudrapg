<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'token' => $result['token'],
                'user' => new UserResource($result['user']),
                'role' => $result['role'],
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => new UserResource($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        if ($request->has('new_password') && ! $request->has('password')) {
            $request->merge([
                'password' => $request->input('new_password'),
                'password_confirmation' => $request->input('confirm_password') 
                    ?? $request->input('new_password_confirmation') 
                    ?? $request->input('password_confirmation'),
            ]);
        } elseif ($request->has('confirm_password') && ! $request->has('password_confirmation')) {
            $request->merge([
                'password_confirmation' => $request->input('confirm_password'),
            ]);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'The current password you provided is incorrect.',
                'errors' => [
                    'current_password' => ['The current password you provided is incorrect.']
                ]
            ], 422);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully.',
        ]);
    }

    public function requestPasswordReset(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $loginId = trim($request->input('phone'));
        $digits = preg_replace('/\D+/', '', $loginId);
        $phone10 = strlen($digits) >= 10 ? substr($digits, -10) : $digits;

        $user = \App\Models\User::where(function ($query) use ($loginId, $phone10) {
            $query->where('phone', $loginId)
                  ->orWhere('email', $loginId);
            if ($phone10) {
                $query->orWhere('phone', $phone10)
                      ->orWhere('phone', '+91 ' . substr($phone10, 0, 5) . ' ' . substr($phone10, 5))
                      ->orWhere('phone', '+91' . $phone10)
                      ->orWhere('phone', '91' . $phone10)
                      ->orWhereRaw("SUBSTR(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), -10) = ?", [$phone10]);
            }
        })->first();

        if (! $user) {
            $student = \App\Models\Student::where(function ($query) use ($loginId, $phone10) {
                $query->where('phone', $loginId)
                      ->orWhere('email', $loginId);
                if ($phone10) {
                    $query->orWhere('phone', $phone10)
                          ->orWhereRaw("SUBSTR(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), -10) = ?", [$phone10]);
                }
            })->first();

            if ($student && $student->user) {
                $user = $student->user;
            } elseif ($student) {
                $user = \App\Models\User::create([
                    'name' => $student->full_name,
                    'email' => $student->email ?: ($student->phone . '@rudrapg.com'),
                    'phone' => $student->phone,
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(12)),
                    'role' => 'STUDENT',
                    'status' => 'ACTIVE',
                ]);
                $student->user_id = $user->id;
                $student->save();
            }
        }

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No account found with this mobile number. Please verify your number or register at your branch reception.',
            ], 404);
        }

        $student = \App\Models\Student::where('user_id', $user->id)->first();
        if (!$student && $user->phone) {
            $last10 = substr(preg_replace('/[^0-9]/', '', $user->phone), -10);
            $student = \App\Models\Student::whereRaw("SUBSTR(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+', ''), -10) = ?", [$last10])->first();
            if ($student && !$student->user_id) {
                $student->user_id = $user->id;
                $student->save();
            }
        }

        $branchId = $student?->branch_id;
        if (!$branchId) {
            $reg = \App\Models\RegistrationRequest::where('phone', $user->phone)->orWhere('student_id', $student?->id)->first();
            $branchId = $reg?->branch_id;
        }
        if (!$branchId) {
            $branchId = \App\Models\Branch::first()?->id;
        }

        // Check if an existing pending request is already logged
        $existing = \App\Models\PasswordResetRequest::where('user_id', $user->id)
            ->where('status', 'PENDING')
            ->first();

        if ($existing) {
            $existing->update([
                'branch_id' => $existing->branch_id ?: $branchId,
                'notes' => $request->input('notes') ?? $existing->notes,
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Your password reset request is already active. Please contact your PG Manager or Sub-Admin to receive your new password.',
                'data' => [
                    'request_id' => $existing->id,
                    'status' => 'PENDING',
                ],
            ]);
        }

        $resetReq = \App\Models\PasswordResetRequest::create([
            'user_id' => $user->id,
            'student_id' => $student?->id,
            'branch_id' => $branchId,
            'phone' => $user->phone,
            'status' => 'PENDING',
            'notes' => $request->input('notes') ?? 'Resident submitted password reset request via mobile/web login',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset request submitted successfully. Your PG Manager & Admin will create your new password.',
            'data' => [
                'request_id' => $resetReq->id,
                'status' => 'PENDING',
            ],
        ], 201);
    }
}
