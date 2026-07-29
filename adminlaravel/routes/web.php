<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\SubAdminController;

// Default Redirect to Super Admin Dashboard
Route::get('/', function () {
    return redirect()->route('super_admin.dashboard');
});

// Super Admin Routes
Route::prefix('super-admin')->name('super_admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/branches', [SuperAdminController::class, 'branches'])->name('branches');
    Route::get('/sub-admins', [SuperAdminController::class, 'subAdmins'])->name('sub_admins');
    Route::get('/rooms-master', [SuperAdminController::class, 'roomsMaster'])->name('rooms_master');
    Route::get('/students', [SuperAdminController::class, 'students'])->name('students');
    Route::get('/finance', [SuperAdminController::class, 'finance'])->name('finance');
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
});

// Sub Admin Routes
Route::prefix('sub-admin')->name('sub_admin.')->group(function () {
    Route::get('/dashboard', [SubAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/verifications', [SubAdminController::class, 'verifications'])->name('verifications');
    Route::get('/bed-map', [SubAdminController::class, 'bedMap'])->name('bed_map');
    Route::get('/rent-ledger', [SubAdminController::class, 'rentLedger'])->name('rent_ledger');
    Route::get('/electricity-audit', [SubAdminController::class, 'electricityAudit'])->name('electricity_audit');
    Route::get('/complaints', [SubAdminController::class, 'complaints'])->name('complaints');
});
