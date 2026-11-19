<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->date('exit_date')->nullable()->after('joining_date');
            $table->string('exit_reason')->nullable()->after('exit_date'); // RELOCATION, COURSE_COMPLETED, JOB_CHANGE, PERSONAL, DISCIPLINARY, OTHER
            $table->text('exit_remarks')->nullable()->after('exit_reason');
            $table->string('deposit_refund_status')->default('PENDING')->after('deposit_balance_due'); // REFUNDED, PENDING, ADJUSTED, FORFEITED, PARTIAL
            $table->decimal('deposit_refund_amount', 10, 2)->default(0.00)->after('deposit_refund_status');
            $table->date('deposit_refund_date')->nullable()->after('deposit_refund_amount');
            $table->string('deposit_refund_reference')->nullable()->after('deposit_refund_date');
            $table->string('last_room_number')->nullable()->after('room_id');
            $table->string('last_bed_code')->nullable()->after('bed_id');
            $table->foreignId('vacated_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['vacated_by']);
            $table->dropColumn([
                'exit_date',
                'exit_reason',
                'exit_remarks',
                'deposit_refund_status',
                'deposit_refund_amount',
                'deposit_refund_date',
                'deposit_refund_reference',
                'last_room_number',
                'last_bed_code',
                'vacated_by',
            ]);
        });
    }
};
