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
        Schema::create('repair_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repair_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete(); // For known products

            // Device Details
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('imei')->nullable()->index(); // Encrypted in model
            $table->string('serial_number')->nullable()->index(); // Encrypted in model
            $table->string('color')->nullable();
            $table->enum('condition', ['new', 'excellent', 'good', 'fair', 'poor', 'damaged'])->default('good');
            $table->text('reported_issue')->nullable();
            $table->text('diagnosis_notes')->nullable();
            $table->text('repair_notes')->nullable();
            $table->text('quality_check_notes')->nullable();

            // Accessories received
            $table->json('accessories_received')->nullable();

            // Photos (stored in tenant-isolated paths)
            $table->json('intake_photos')->nullable();
            $table->json('diagnosis_photos')->nullable();
            $table->json('repair_photos')->nullable();
            $table->json('delivery_photos')->nullable();

            // Status tracking per device
            $table->enum('device_status', [
                'received',
                'diagnosing',
                'waiting_approval',
                'waiting_parts',
                'in_repair',
                'quality_check',
                'ready_delivery',
                'delivered',
            ])->default('received');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'repair_ticket_id']);
            $table->index(['tenant_id', 'product_id']);
            $table->index(['tenant_id', 'device_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_devices');
    }
};
