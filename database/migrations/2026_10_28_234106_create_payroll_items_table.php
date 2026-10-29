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
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_record_id')->constrained()->cascadeOnDelete();

            // Item Details
            $table->string('name'); // e.g., "Basic Salary", "Overtime", "PF Deduction"
            $table->enum('type', ['earning', 'deduction']);
            $table->decimal('amount', 15, 2);
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_recurring')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'payroll_record_id']);
            $table->index(['tenant_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
