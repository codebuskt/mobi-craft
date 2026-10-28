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
        Schema::create('repair_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            // Ticket Details
            $table->string('ticket_number')->unique(); // REP-{YEAR}-{SEQUENCE}
            $table->enum('status', [
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
            ])->default('received');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('reported_issue')->nullable();
            $table->text('intake_notes')->nullable();

            // Pricing
            $table->decimal('diagnostic_fee', 15, 2)->default(0);
            $table->decimal('labor_charge', 15, 2)->default(0);
            $table->decimal('parts_cost', 15, 2)->default(0);
            $table->decimal('total_estimated', 15, 2)->default(0);
            $table->decimal('total_charged', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);

            // Dates
            $table->date('received_date');
            $table->date('estimated_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->date('delivered_date')->nullable();

            // Warranty
            $table->integer('warranty_months')->default(0);
            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            $table->text('warranty_terms')->nullable();

            // SLA
            $table->timestamp('sla_due_at')->nullable();
            $table->boolean('sla_breached')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'assigned_technician_id']);
            $table->index(['tenant_id', 'received_date']);
            $table->index(['tenant_id', 'ticket_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_tickets');
    }
};
