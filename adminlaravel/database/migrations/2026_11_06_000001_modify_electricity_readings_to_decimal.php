<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('electricity_readings', function (Blueprint $table) {
            $table->decimal('current_reading', 10, 2)->change();
            $table->decimal('previous_reading', 10, 2)->change();
            $table->decimal('units_consumed', 10, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('electricity_readings', function (Blueprint $table) {
            $table->integer('current_reading')->change();
            $table->integer('previous_reading')->change();
            $table->integer('units_consumed')->change();
        });
    }
};
