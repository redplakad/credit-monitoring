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
        Schema::create('employee_positions', function (Blueprint $table) {
            $table->id('position_id');
            $table->string('position_name')->unique();
            $table->text('description')->nullable();
            $table->enum('access_level', ['ADMIN', 'MANAGER', 'STAFF', 'USER'])->default('USER');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_positions');
    }
};
