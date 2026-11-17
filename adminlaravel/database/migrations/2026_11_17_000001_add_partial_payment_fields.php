<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add partial payment fields to payments table
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'total_due')) {
                $table->decimal('total_due', 10, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('payments', 'remaining_amount')) {
                $table->decimal('remaining_amount', 10, 2)->default(0)->after('total_due');
            }
        });

        // 2. Add balance tracking fields to students table
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'rent_balance_due')) {
                $table->decimal('rent_balance_due', 10, 2)->default(0)->after('rent_status');
            }
            if (!Schema::hasColumn('students', 'deposit_balance_due')) {
                $table->decimal('deposit_balance_due', 10, 2)->default(0)->after('deposit_status');
            }
        });

        // 3. Backfill existing payments: default total_due to amount where null
        DB::table('payments')
            ->whereNull('total_due')
            ->update([
                'total_due' => DB::raw('amount'),
                'remaining_amount' => 0,
            ]);

        // 4. Backfill existing students with bed rent if rent_status is DUE
        $dueStudents = DB::table('students')
            ->join('beds', 'students.bed_id', '=', 'beds.id')
            ->where('students.rent_status', 'DUE')
            ->whereNotNull('students.bed_id')
            ->select('students.id as student_id', 'beds.monthly_rent')
            ->get();

        foreach ($dueStudents as $st) {
            DB::table('students')
                ->where('id', $st->student_id)
                ->update(['rent_balance_due' => $st->monthly_rent ?? 0]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'remaining_amount')) {
                $table->dropColumn('remaining_amount');
            }
            if (Schema::hasColumn('payments', 'total_due')) {
                $table->dropColumn('total_due');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'deposit_balance_due')) {
                $table->dropColumn('deposit_balance_due');
            }
            if (Schema::hasColumn('students', 'rent_balance_due')) {
                $table->dropColumn('rent_balance_due');
            }
        });
    }
};
