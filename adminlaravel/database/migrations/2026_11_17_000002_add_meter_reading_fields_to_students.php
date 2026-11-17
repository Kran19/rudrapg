<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'initial_meter_reading')) {
                $table->decimal('initial_meter_reading', 10, 2)->nullable()->after('deposit_balance_due');
            }
            if (!Schema::hasColumn('students', 'initial_meter_photo')) {
                $table->string('initial_meter_photo')->nullable()->after('initial_meter_reading');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'initial_meter_photo')) {
                $table->dropColumn('initial_meter_photo');
            }
            if (Schema::hasColumn('students', 'initial_meter_reading')) {
                $table->dropColumn('initial_meter_reading');
            }
        });
    }
};
