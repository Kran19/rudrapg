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

// Uncached Direct Mobile APK Download Route
Route::get('/download-app', [WebAuthController::class, 'downloadApp'])->name('download_app');

// Super Admin Protected Routes
Route::middleware(['auth', 'role:SUPER_ADMIN'])->prefix('super-admin')->name('super_admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    
    Route::get('/branches', [SuperAdminController::class, 'branches'])->name('branches');
    Route::post('/branches', [SuperAdminController::class, 'storeBranch'])->name('branches.store');
    Route::post('/branches/{id}/update', [SuperAdminController::class, 'updateBranch'])->name('branches.update');
    Route::post('/branches/{id}/toggle-status', [SuperAdminController::class, 'toggleBranchStatus'])->name('branches.toggle_status');
    Route::delete('/branches/{id}', [SuperAdminController::class, 'destroyBranch'])->name('branches.destroy');
    
    Route::get('/sub-admins', [SuperAdminController::class, 'subAdmins'])->name('sub_admins');
    Route::post('/sub-admins', [SuperAdminController::class, 'storeSubAdmin'])->name('sub_admins.store');
    Route::post('/sub-admins/{id}/update', [SuperAdminController::class, 'updateSubAdmin'])->name('sub_admins.update');
    Route::post('/sub-admins/{id}/toggle-status', [SuperAdminController::class, 'toggleSubAdminStatus'])->name('sub_admins.toggle_status');
    Route::delete('/sub-admins/{id}', [SuperAdminController::class, 'destroySubAdmin'])->name('sub_admins.destroy');
    
    Route::get('/rooms-master', [SuperAdminController::class, 'roomsMaster'])->name('rooms_master');
    Route::post('/rooms-master', [SuperAdminController::class, 'storeRoom'])->name('rooms_master.store');
    Route::post('/rooms-master/{id}/update', [SuperAdminController::class, 'updateRoom'])->name('rooms_master.update');
    Route::delete('/rooms-master/{id}', [SuperAdminController::class, 'destroyRoom'])->name('rooms_master.destroy');
    
    Route::get('/students', [SuperAdminController::class, 'students'])->name('students');
    Route::post('/students/{id}/update', [SuperAdminController::class, 'updateStudent'])->name('students.update');
    Route::delete('/students/{id}', [SuperAdminController::class, 'destroyStudent'])->name('students.destroy');
    
    Route::get('/finance', [SuperAdminController::class, 'finance'])->name('finance');
    Route::post('/finance/{id}/update', [SuperAdminController::class, 'updateTransaction'])->name('finance.update');
    Route::delete('/finance/{id}', [SuperAdminController::class, 'destroyTransaction'])->name('finance.destroy');
    
    Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
});

// Sub Admin Protected Routes (Accessible by Sub Admin and Super Admin)
Route::middleware(['auth', 'role:SUPER_ADMIN,SUB_ADMIN'])->prefix('sub-admin')->name('sub_admin.')->group(function () {
    Route::get('/dashboard', [SubAdminController::class, 'dashboard'])->name('dashboard');
    
    Route::get('/verifications', [SubAdminController::class, 'verifications'])->name('verifications');
    Route::post('/verifications/{id}/approve-kyc', [SubAdminController::class, 'approveKycOnly'])->name('verifications.approve_kyc');
    Route::post('/verifications/{id}/assign-bed', [SubAdminController::class, 'assignBedOnly'])->name('verifications.assign_bed');
    Route::post('/verifications/{id}/approve', [SubAdminController::class, 'approveVerification'])->name('verifications.approve');
    Route::post('/verifications/{id}/reject', [SubAdminController::class, 'rejectVerification'])->name('verifications.reject');
    
    Route::get('/bed-map', [SubAdminController::class, 'bedMap'])->name('bed_map');
    
    Route::get('/rent-ledger', [SubAdminController::class, 'rentLedger'])->name('rent_ledger');
    Route::post('/rent-ledger/cash-payment', [SubAdminController::class, 'recordCashPayment'])->name('rent_ledger.cash_payment');
    Route::post('/rent-ledger/{id}/verify', [SubAdminController::class, 'verifyPayment'])->name('rent_ledger.verify');
    Route::post('/rent-ledger/{id}/update', [SubAdminController::class, 'updatePayment'])->name('rent_ledger.update');
    Route::delete('/rent-ledger/{id}', [SubAdminController::class, 'destroyPayment'])->name('rent_ledger.destroy');
    
    Route::get('/electricity-audit', [SubAdminController::class, 'electricityAudit'])->name('electricity_audit');
    Route::post('/electricity-audit/{id}/approve', [SubAdminController::class, 'approveElectricityReading'])->name('electricity_audit.approve');
    Route::post('/electricity-audit/{id}/reject', [SubAdminController::class, 'rejectElectricityReading'])->name('electricity_audit.reject');
    Route::post('/electricity-audit/{id}/update', [SubAdminController::class, 'updateElectricityReading'])->name('electricity_audit.update');
    
    Route::get('/complaints', [SubAdminController::class, 'complaints'])->name('complaints');
    Route::post('/complaints/broadcast-notice', [SubAdminController::class, 'broadcastNotice'])->name('complaints.broadcast_notice');
    Route::post('/complaints/{id}/update', [SubAdminController::class, 'updateComplaint'])->name('complaints.update');
    Route::delete('/complaints/{id}', [SubAdminController::class, 'destroyComplaint'])->name('complaints.destroy');
});
