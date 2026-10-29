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
        Schema::create('expense_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_category_id')->constrained()->cascadeOnDelete();

            // Budget Period
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->date('period_start');
            $table->date('period_end');

            // Budget Amounts
            $table->decimal('budgeted_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->decimal('variance_amount', 15, 2)->default(0);
            $table->decimal('variance_percentage', 5, 2)->default(0);

            // Status
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'expense_category_id']);
            $table->index(['tenant_id', 'period_type', 'period_start']);
            $table->index(['tenant_id', 'status']);
            $table->unique(['tenant_id', 'expense_category_id', 'period_type', 'period_start'], 'unique_budget_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_budgets');
    }
};
