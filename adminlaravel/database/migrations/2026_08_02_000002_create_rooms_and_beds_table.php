<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->integer('floor_number');
            $table->string('room_number');
            $table->string('sharing_type'); // e.g., 2 Sharing AC, 3 Sharing Non-AC
            $table->integer('max_beds')->default(2);
            $table->boolean('is_ac')->default(true);
            $table->text('description')->nullable();
            $table->json('facilities')->nullable();
            $table->json('images')->nullable();
            $table->string('status')->default('AVAILABLE'); // AVAILABLE, FULL, MAINTENANCE
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['branch_id', 'room_number']);
            $table->index(['branch_id', 'status']);
        });

        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->string('bed_code'); // e.g., Bed 1A, Bed 1B
            $table->decimal('monthly_rent', 10, 2);
            $table->decimal('security_deposit', 10, 2);
            $table->string('status')->default('AVAILABLE'); // AVAILABLE, OCCUPIED, RESERVED, MAINTENANCE
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['room_id', 'bed_code']);
            $table->index(['room_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beds');
        Schema::dropIfExists('rooms');
    }
};
