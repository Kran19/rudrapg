<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. PG-NRD-01
            $table->string('name');
            $table->text('address');
            $table->string('city');
            $table->string('state')->default('Gujarat');
            $table->string('pincode')->default('382330');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('google_map_link')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('manager_name');
            $table->string('manager_phone');
            $table->decimal('electricity_unit_rate', 8, 2)->default(10.00);
            $table->string('qr_code_hash')->unique();
            $table->json('amenities')->nullable();
            $table->text('branch_rules')->nullable();
            $table->json('images')->nullable();
            $table->string('status')->default('ACTIVE'); // ACTIVE, INACTIVE
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'city']);
        });

        Schema::create('sub_admin_branch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_admin_branch');
        Schema::dropIfExists('branches');
    }
};
