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
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_profile_id')->constrained()->cascadeOnDelete();

            // Salary Components (all in BDT)
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('house_allowance', 15, 2)->default(0);
            $table->decimal('medical_allowance', 15, 2)->default(0);
            $table->decimal('transport_allowance', 15, 2)->default(0);
            $table->decimal('other_allowances', 15, 2)->default(0);

            // Deductions
            $table->decimal('provident_fund', 15, 2)->default(0);
            $table->decimal('tax_deduction', 15, 2)->default(0);
            $table->decimal('advance_deduction', 15, 2)->default(0);
            $table->decimal('other_deductions', 15, 2)->default(0);

            // Overtime
            $table->decimal('overtime_rate_per_hour', 10, 2)->default(0);

            // Validity
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'staff_profile_id']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'effective_from']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_structures');
    }
};
