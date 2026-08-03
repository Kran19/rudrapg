<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('tx_reference')->unique(); // e.g. PAY-2026-9912
            $table->string('payment_type'); // RENT, DEPOSIT, ELECTRICITY, OTHER
            $table->decimal('amount', 10, 2);
            $table->string('payment_mode')->default('UPI'); // UPI, CASH, BANK_TRANSFER
            $table->date('payment_date');
            $table->date('due_date')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, PAID, VERIFIED, REJECTED
            $table->text('verification_remarks')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->string('utr_number')->nullable();
            $table->string('screenshot_path');
            $table->string('status')->default('PENDING'); // PENDING, VERIFIED, REJECTED
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
        Schema::dropIfExists('payments');
    }
};
