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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('inventory_locations')->nullOnDelete();
            $table->foreignId('from_location_id')->nullable()->constrained('inventory_locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('inventory_locations')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Who performed the movement

            // Movement Details
            $table->enum('type', [
                'purchase',           // Stock received from supplier
                'purchase_return',    // Returned to supplier
                'sale',               // Stock sold to customer
                'sale_return',        // Returned by customer
                'adjustment_in',      // Positive adjustment
                'adjustment_out',     // Negative adjustment
                'transfer_in',        // Transfer from another location
                'transfer_out',       // Transfer to another location
                'repair_consumption', // Parts consumed in repair
                'repair_return',      // Parts returned from repair
                'damage',             // Damaged stock
                'loss',               // Lost/stolen stock
                'opening_stock',      // Initial stock entry
                'stock_take',         // Physical count adjustment
            ]);

            $table->decimal('quantity', 15, 4); // Positive for in, negative for out
            $table->decimal('unit_cost', 15, 4)->default(0); // Cost per unit at time of movement
            $table->decimal('total_cost', 15, 4)->default(0); // quantity * unit_cost
            $table->decimal('running_balance', 15, 4)->default(0); // Balance after this movement

            // Reference
            $table->string('reference_type')->nullable(); // Sale, PurchaseOrder, RepairTicket, etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of reference
            $table->string('reference_number')->nullable(); // Human-readable reference

            // Additional Info
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Flexible data: batch, expiry, serial numbers, etc.

            // Approval
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'product_id']);
            $table->index(['tenant_id', 'location_id']);
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'reference_type', 'reference_id']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
