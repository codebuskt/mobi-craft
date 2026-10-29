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
        Schema::create('reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Reconciliation Details
            $table->enum('type', ['bank', 'cash', 'stock'])->default('bank');
            $table->string('reference_number')->unique(); // e.g., BANK-REC-2026-001
            $table->date('reconciliation_date');
            $table->date('statement_date')->nullable(); // For bank reconciliation

            // Account/Location
            $table->string('account_name')->nullable(); // Bank account name
            $table->string('account_number')->nullable(); // Masked account number
            $table->foreignId('inventory_location_id')->nullable()->constrained()->nullOnDelete(); // For stock reconciliation

            // Balances
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->decimal('system_balance', 15, 2)->default(0);
            $table->decimal('difference', 15, 2)->default(0);

            // Status
            $table->enum('status', ['draft', 'in_progress', 'completed', 'discrepancy'])->default('draft');
            $table->text('notes')->nullable();

            // Approval
            $table->foreignId('prepared_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'reconciliation_date']);
            $table->index(['tenant_id', 'reference_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliations');
    }
};
