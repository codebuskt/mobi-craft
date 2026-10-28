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
        Schema::create('repair_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repair_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repair_device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivered_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('received_by_customer_id')->nullable()->constrained('users')->nullOnDelete();

            // Delivery Details
            $table->date('delivered_date');
            $table->text('condition_on_delivery')->nullable();
            $table->json('accessories_returned')->nullable();
            $table->json('delivery_photos')->nullable();
            $table->string('customer_signature')->nullable(); // Base64 encoded signature
            $table->text('customer_notes')->nullable();

            // Warranty
            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            $table->text('warranty_terms')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'repair_ticket_id']);
            $table->index(['tenant_id', 'repair_device_id']);
            $table->index(['tenant_id', 'delivered_by']);
            $table->index(['tenant_id', 'delivered_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_deliveries');
    }
};
