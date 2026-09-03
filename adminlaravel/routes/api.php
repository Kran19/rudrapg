<?php

use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Controllers\Api\v1\StudentApiController;
use App\Http\Controllers\Api\v1\SubAdminApiController;
use App\Http\Controllers\Api\v1\SuperAdminApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Rudra Group PG REST API (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public Auth & Student QR Registration
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/student/register', [StudentApiController::class, 'register']);
    Route::post('/branch/verify-qr', [StudentApiController::class, 'verifyBranchQr']);
    Route::get('/branch/verify-qr', [StudentApiController::class, 'verifyBranchQr']);
    Route::get('/branches/active-list', [StudentApiController::class, 'activeBranchesList']);

    // Public App Version Check
    Route::get('/app-version', function () {
        return response()->json([
            'version' => config('version.android_version', '1.0.0'),
            'download_url' => config('version.download_url', ''),
            'force_update' => config('version.force_update', false),
        ]);
    });

    // Secure Deploy Route for Hostinger Code Sync
    Route::get('/sys-deploy', function (\Illuminate\Http\Request $request) {
        $secret = 'rudra_secure_deploy_2026';
        if ($request->query('key') !== $secret) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }
        
        $output = [];
        
        // Try git pull
        if (function_exists('exec')) {
            exec('git pull origin main 2>&1', $output);
        } else {
            $output[] = 'exec() is disabled. Cannot run git pull.';
        }
        
        // Clear caches and run migrations using Laravel API
        try {
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            $output[] = 'Artisan optimize:clear: ' . trim(\Illuminate\Support\Facades\Artisan::output());
            
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $output[] = 'Artisan migrate --force: ' . trim(\Illuminate\Support\Facades\Artisan::output());
        } catch (\Exception $e) {
            $output[] = 'Artisan error: ' . $e->getMessage();
        }
        
        return response()->json([
            'status' => 'success',
            'output' => $output
        ]);
    });

    // Protected Routes (Sanctum Auth)
    Route::middleware('auth:sanctum')->group(function () {

        // Session & Identity
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Student Mobile App Routes
        Route::prefix('student')->middleware('role:STUDENT')->group(function () {
            Route::get('/profile', [StudentApiController::class, 'profile']);
            Route::post('/payment-proof', [StudentApiController::class, 'uploadPaymentProof']);
            Route::get('/payments', [StudentApiController::class, 'payments']);
            Route::post('/electricity-reading', [StudentApiController::class, 'submitElectricityReading']);
            Route::get('/electricity-readings', [StudentApiController::class, 'electricityReadings']);
            Route::post('/complaint', [StudentApiController::class, 'createComplaint']);
            Route::get('/complaints', [StudentApiController::class, 'complaints']);
            Route::get('/notices', [StudentApiController::class, 'notices']);
        });

        // Sub Admin Management Routes
        Route::prefix('sub-admin')->middleware('role:SUB_ADMIN')->group(function () {
            Route::get('/pending-verifications', [SubAdminApiController::class, 'pendingVerifications']);
            Route::post('/registrations/{id}/approve', [SubAdminApiController::class, 'approveRegistration']);
            Route::post('/registrations/{id}/reject', [SubAdminApiController::class, 'rejectRegistration']);
            Route::get('/room-matrix', [SubAdminApiController::class, 'roomMatrix']);
            Route::post('/payments/{id}/verify', [SubAdminApiController::class, 'verifyPayment']);
            Route::post('/electricity-readings/{id}/audit', [SubAdminApiController::class, 'auditElectricity']);
            Route::post('/complaints/{id}/resolve', [SubAdminApiController::class, 'resolveComplaint']);
        });

        // Super Admin Global Control Routes
        Route::prefix('super-admin')->middleware('role:SUPER_ADMIN')->group(function () {
            Route::get('/dashboard', [SuperAdminApiController::class, 'dashboard']);
            Route::post('/branches', [SuperAdminApiController::class, 'createBranch']);

            // Sub Admin Management
            Route::post('/sub-admins', [SuperAdminApiController::class, 'createSubAdmin']);
            Route::put('/sub-admins/{id}', [SuperAdminApiController::class, 'updateSubAdmin']);
            Route::post('/sub-admins/{id}/toggle-status', [SuperAdminApiController::class, 'toggleSubAdminStatus']);
            Route::post('/sub-admins/{id}/reset-password', [SuperAdminApiController::class, 'resetSubAdminPassword']);
            Route::post('/sub-admins/assign', [SuperAdminApiController::class, 'assignSubAdmin']);

            Route::get('/audit-logs', [SuperAdminApiController::class, 'auditLogs']);
        });
    });
});








