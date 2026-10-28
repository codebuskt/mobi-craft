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
        Schema::create('repair_accessories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repair_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('repair_device_id')->nullable()->constrained()->nullOnDelete();

            // Accessory Details
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('type', ['charger', 'cable', 'case', 'screen_protector', 'earphones', 'box', 'manual', 'other'])->default('other');
            $table->boolean('is_received')->default(false);
            $table->boolean('is_returned')->default(false);
            $table->text('condition_notes')->nullable();
            $table->json('photos')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'repair_ticket_id']);
            $table->index(['tenant_id', 'repair_device_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_accessories');
    }
};
