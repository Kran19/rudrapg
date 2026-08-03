<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->foreignId('bed_id')->nullable()->constrained('beds')->onDelete('set null');
            $table->string('app_reference')->unique(); // e.g. REG-2026-8812
            $table->string('full_name');
            $table->string('phone');
            $table->string('email');
            $table->string('aadhaar_number');
            $table->string('pan_number')->nullable();
            $table->string('parent_name');
            $table->string('parent_phone');
            $table->string('emergency_contact');
            $table->text('current_address');
            $table->date('joining_date')->nullable();
            $table->string('kyc_status')->default('PENDING'); // PENDING, VERIFIED, REJECTED
            $table->string('rent_status')->default('PAID'); // PAID, DUE, OVERDUE
            $table->string('deposit_status')->default('PENDING'); // PENDING, VERIFIED, REFUNDED
            $table->string('status')->default('PENDING_APPROVAL'); // PENDING_APPROVAL, APPROVED, REJECTED, EXITED
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'status']);
            $table->index('aadhaar_number');
        });

        Schema::create('student_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('doc_type'); // PROFILE_PHOTO, AADHAAR_FRONT, AADHAAR_BACK, PAN_CARD
            $table->string('file_path');
            $table->string('status')->default('PENDING'); // PENDING, VERIFIED, REJECTED
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('app_reference');
            $table->string('status')->default('PENDING'); // PENDING, APPROVED, REJECTED
            $table->text('rejection_reason')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_requests');
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('students');
    }
};
