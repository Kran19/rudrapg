<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:reset-test-data', function () {
    $this->info('Resetting test data to clean slate (zero state)...');

    \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

    if (\Illuminate\Support\Facades\Schema::hasTable('payment_proofs')) \App\Models\PaymentProof::truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('payments')) \App\Models\Payment::truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('registration_requests')) \App\Models\RegistrationRequest::truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('complaints')) \App\Models\Complaint::truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('electricity_readings')) \App\Models\ElectricityReading::truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('announcements')) \App\Models\Announcement::truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('notifications')) \App\Models\Notification::truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('audit_logs')) \App\Models\AuditLog::truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('student_documents')) \App\Models\StudentDocument::truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('room_allocations')) \App\Models\RoomAllocation::truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('students')) \App\Models\Student::truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('beds')) \App\Models\Bed::truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('rooms')) \App\Models\Room::truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('sub_admin_branch')) \Illuminate\Support\Facades\DB::table('sub_admin_branch')->truncate();
    if (\Illuminate\Support\Facades\Schema::hasTable('branches')) \App\Models\Branch::truncate();

    // Remove demo non-super-admin users
    \App\Models\User::where('role', '!=', 'SUPER_ADMIN')->delete();

    \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

    $this->info('Success: All demo branches, rooms, beds, students, and payments have been wiped to 0.');
    $this->info('Super Admin account remains untouched.');
})->purpose('Wipe all seed/test data to make all dashboard counters zero');

