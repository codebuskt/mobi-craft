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
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Employee Details
            $table->string('employee_id')->unique(); // EMP-{SEQUENCE}
            $table->date('joining_date');
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern'])->default('full_time');
            $table->enum('status', ['active', 'on_leave', 'terminated', 'resigned'])->default('active');

            // Bank Details
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('bank_routing_number')->nullable();

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relation')->nullable();

            // Personal
            $table->date('date_of_birth')->nullable();
            $table->string('national_id')->nullable();
            $table->text('address')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'employee_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'department']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};
