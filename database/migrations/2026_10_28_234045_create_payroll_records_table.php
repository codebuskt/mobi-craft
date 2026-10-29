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
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('salary_structure_id')->constrained()->cascadeOnDelete();

            // Earnings
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('house_allowance', 15, 2)->default(0);
            $table->decimal('medical_allowance', 15, 2)->default(0);
            $table->decimal('transport_allowance', 15, 2)->default(0);
            $table->decimal('overtime_pay', 15, 2)->default(0);
            $table->decimal('other_allowances', 15, 2)->default(0);
            $table->decimal('gross_salary', 15, 2);

            // Deductions
            $table->decimal('provident_fund', 15, 2)->default(0);
            $table->decimal('tax_deduction', 15, 2)->default(0);
            $table->decimal('advance_deduction', 15, 2)->default(0);
            $table->decimal('other_deductions', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2);

            // Net
            $table->decimal('net_salary', 15, 2);

            // Attendance
            $table->integer('working_days')->default(0);
            $table->integer('present_days')->default(0);
            $table->integer('absent_days')->default(0);
            $table->integer('leave_days')->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);

            // Status
            $table->enum('status', ['draft', 'calculated', 'approved', 'paid', 'cancelled'])->default('draft');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'payroll_period_id']);
            $table->index(['tenant_id', 'staff_profile_id']);
            $table->index(['tenant_id', 'status']);
            $table->unique(['tenant_id', 'payroll_period_id', 'staff_profile_id'], 'unique_payroll_record');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
