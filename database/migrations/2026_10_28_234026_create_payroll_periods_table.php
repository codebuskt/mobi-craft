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
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Period Details
            $table->string('name'); // e.g., "January 2026"
            $table->date('period_start');
            $table->date('period_end');
            $table->date('cutoff_date'); // Last date for attendance/overtime inclusion
            $table->date('pay_date'); // Actual pay date

            // Status
            $table->enum('status', ['draft', 'processing', 'approved', 'disbursed', 'closed'])->default('draft');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'period_start']);
            $table->unique(['tenant_id', 'period_start', 'period_end'], 'unique_payroll_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
