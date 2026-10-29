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
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_receipt_id')->nullable()->constrained()->nullOnDelete();

            // Payment Details
            $table->decimal('amount', 15, 2);
            $table->enum('payment_method', [
                'cash',
                'card',
                'mobile_banking',
                'bank_transfer',
                'cheque',
            ]);
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('payment_date');

            // Allocation
            $table->decimal('allocated_amount', 15, 2)->default(0);
            $table->decimal('tds_amount', 15, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'supplier_id']);
            $table->index(['tenant_id', 'purchase_receipt_id']);
            $table->index(['tenant_id', 'payment_date']);
            $table->index(['tenant_id', 'payment_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
