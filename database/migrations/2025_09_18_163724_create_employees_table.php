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
        Schema::create('employees', function (Blueprint $table) {
            $table->id('employee_id');
                $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');
            $table->string('branch_code');
                $table->foreignId('position_id')->constrained('employee_positions', 'position_id');
            $table->string('employee_number')->unique();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone_number');
            $table->date('join_date');
            $table->enum('status', ['ACTIVE', 'LEAVE', 'RESIGNED'])->default('ACTIVE');
            $table->timestamps();
            
            $table->foreign('branch_code')->references('branch_code')->on('branches');
            $table->index(['employee_number', 'status']);
            $table->index(['branch_code', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
