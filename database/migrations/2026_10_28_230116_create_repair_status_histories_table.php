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
        Schema::create('repair_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repair_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();

            // Status Transition
            $table->enum('from_status', [
                'received',
                'diagnosing',
                'waiting_approval',
                'waiting_parts',
                'in_repair',
                'quality_check',
                'ready_delivery',
                'delivered',
                'cancelled',
                'rejected',
            ]);
            $table->enum('to_status', [
                'received',
                'diagnosing',
                'waiting_approval',
                'waiting_parts',
                'in_repair',
                'quality_check',
                'ready_delivery',
                'delivered',
                'cancelled',
                'rejected',
            ]);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional context (e.g., parts ordered, approval details)

            $table->timestamps();

            $table->index(['tenant_id', 'repair_ticket_id']);
            $table->index(['tenant_id', 'changed_by']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_status_histories');
    }
};
