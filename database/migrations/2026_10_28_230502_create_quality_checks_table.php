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
        Schema::create('quality_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repair_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repair_device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checked_by')->constrained('users')->cascadeOnDelete();

            // Quality Check Details
            $table->json('checklist'); // [{item, description, result: pass/fail/na, notes}]
            $table->enum('overall_result', ['pass', 'fail'])->default('pass');
            $table->text('notes')->nullable();
            $table->date('checked_date');
            $table->json('photos')->nullable();

            // Re-check tracking
            $table->boolean('is_recheck')->default(false);
            $table->foreignId('original_check_id')->nullable()->constrained('quality_checks')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'repair_ticket_id']);
            $table->index(['tenant_id', 'repair_device_id']);
            $table->index(['tenant_id', 'checked_by']);
            $table->index(['tenant_id', 'overall_result']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_checks');
    }
};
