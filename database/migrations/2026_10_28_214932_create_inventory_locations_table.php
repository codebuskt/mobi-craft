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
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('inventory_locations')->nullOnDelete();

            $table->string('name');
            $table->string('code')->unique(); // e.g., WH001, SF001, RB001
            $table->string('slug')->unique(); // warehouse, shop-floor, repair-bay
            $table->text('description')->nullable();
            $table->enum('type', ['warehouse', 'shop_floor', 'repair_bay', 'showroom', 'storage', 'transit'])->default('storage');
            $table->string('manager_id')->nullable(); // User ID of location manager
            $table->text('address')->nullable(); // Internal address within premises
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // Default location for new stock
            $table->integer('sort_order')->default(0);
            $table->json('settings')->nullable(); // Temperature control, security level, etc.

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'parent_id']);
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_locations');
    }
};
