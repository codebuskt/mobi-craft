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
        Schema::create('reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reconciliation_id')->constrained()->cascadeOnDelete();

            // Source Transaction (polymorphic)
            $table->string('source_type')->nullable(); // Sale, PurchaseReceipt, Expense, StockMovement, etc.
            $table->unsignedBigInteger('source_id')->nullable();
            $table->index(['source_type', 'source_id']);

            // Item Details
            $table->date('transaction_date');
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->enum('direction', ['debit', 'credit']);

            // Matching
            $table->boolean('is_matched')->default(false);
            $table->foreignId('matched_item_id')->nullable()->constrained('reconciliation_items')->nullOnDelete();
            $table->text('match_notes')->nullable();

            // Discrepancy
            $table->boolean('is_discrepancy')->default(false);
            $table->text('discrepancy_notes')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'reconciliation_id']);
            $table->index(['tenant_id', 'is_matched']);
            $table->index(['tenant_id', 'is_discrepancy']);
            $table->index(['tenant_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliation_items');
    }
};
