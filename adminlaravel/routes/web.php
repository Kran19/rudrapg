<?php

use App\Http\Controllers\Auth\WebAuthController;
use App\Http\Controllers\SubAdminController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Default Root Redirect
Route::get('/', [WebAuthController::class, 'showLoginForm'])->name('root');

// Authentication Web Routes
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// Super Admin Protected Routes
Route::middleware(['auth', 'role:SUPER_ADMIN'])->prefix('super-admin')->name('super_admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    
    Route::get('/branches', [SuperAdminController::class, 'branches'])->name('branches');
    Route::post('/branches', [SuperAdminController::class, 'storeBranch'])->name('branches.store');
    
    Route::get('/sub-admins', [SuperAdminController::class, 'subAdmins'])->name('sub_admins');
    Route::post('/sub-admins', [SuperAdminController::class, 'storeSubAdmin'])->name('sub_admins.store');
    
    Route::get('/rooms-master', [SuperAdminController::class, 'roomsMaster'])->name('rooms_master');
    Route::post('/rooms-master', [SuperAdminController::class, 'storeRoom'])->name('rooms_master.store');
    
    Route::get('/students', [SuperAdminController::class, 'students'])->name('students');
    Route::get('/finance', [SuperAdminController::class, 'finance'])->name('finance');
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
});

// Sub Admin Protected Routes (Accessible by Sub Admin and Super Admin)
Route::middleware(['auth', 'role:SUPER_ADMIN,SUB_ADMIN'])->prefix('sub-admin')->name('sub_admin.')->group(function () {
    Route::get('/dashboard', [SubAdminController::class, 'dashboard'])->name('dashboard');
    
    Route::get('/verifications', [SubAdminController::class, 'verifications'])->name('verifications');
    Route::post('/verifications/{id}/approve', [SubAdminController::class, 'approveVerification'])->name('verifications.approve');
    Route::post('/verifications/{id}/reject', [SubAdminController::class, 'rejectVerification'])->name('verifications.reject');
    
    Route::get('/bed-map', [SubAdminController::class, 'bedMap'])->name('bed_map');
    
    Route::get('/rent-ledger', [SubAdminController::class, 'rentLedger'])->name('rent_ledger');
    Route::post('/rent-ledger/cash-payment', [SubAdminController::class, 'recordCashPayment'])->name('rent_ledger.cash_payment');
    Route::post('/rent-ledger/{id}/verify', [SubAdminController::class, 'verifyPayment'])->name('rent_ledger.verify');
    
    Route::get('/electricity-audit', [SubAdminController::class, 'electricityAudit'])->name('electricity_audit');
    Route::post('/electricity-audit/{id}/approve', [SubAdminController::class, 'approveElectricityReading'])->name('electricity_audit.approve');
    Route::post('/electricity-audit/{id}/reject', [SubAdminController::class, 'rejectElectricityReading'])->name('electricity_audit.reject');
    
    Route::get('/complaints', [SubAdminController::class, 'complaints'])->name('complaints');
    Route::post('/complaints/broadcast-notice', [SubAdminController::class, 'broadcastNotice'])->name('complaints.broadcast_notice');
});
