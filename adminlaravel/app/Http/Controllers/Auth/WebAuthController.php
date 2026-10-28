<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WebAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === 'SUPER_ADMIN') {
                return redirect()->route('super_admin.dashboard');
            }
            if ($user->role === 'SUB_ADMIN') {
                return redirect()->route('sub_admin.dashboard');
            }
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if ($user->status !== 'ACTIVE') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Your account is inactive. Please contact system administrator.',
                ])->onlyInput('email');
            }

            if (! in_array($user->role, ['SUPER_ADMIN', 'SUB_ADMIN'], true)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Access denied. Only Super Admin and Sub Admin can sign in here.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            if ($user->role === 'SUPER_ADMIN') {
                return redirect()->intended(route('super_admin.dashboard'))->with('success', 'Welcome back, Super Admin!');
            }

            return redirect()->intended(route('sub_admin.dashboard'))->with('success', 'Welcome back, Sub Admin!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been signed out successfully.');
    }

    public function downloadApp()
    {
        $path = public_path('downloads/student-app.apk');
        if (!file_exists($path)) {
            abort(404, 'APK file not found.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Content-Disposition' => 'attachment; filename="rudraboyspg-student-v2.4.apk"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Please enter your current password.',
            'password.required' => 'Please enter a new password.',
            'password.min' => 'The new password must be at least 6 characters.',
            'password.confirmed' => 'New password confirmation does not match.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The current password you provided is incorrect.',
                    'errors' => [
                        'current_password' => ['The current password you provided is incorrect.']
                    ]
                ], 422);
            }

            return back()->withErrors([
                'current_password' => 'The current password you provided is incorrect.',
            ]);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully.',
            ]);
        }

        return back()->with('success', 'Password updated successfully.');
    }
}
