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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();

            // Product Identification
            $table->string('sku')->unique(); // Stock Keeping Unit
            $table->string('barcode')->nullable()->unique(); // EAN/UPC/ISBN
            $table->string('name');
            $table->string('slug'); // URL-friendly name

            // Product Type & Classification
            $table->enum('type', ['phone', 'accessory', 'spare_part', 'service', 'used_device'])->default('accessory');
            $table->enum('status', ['active', 'inactive', 'discontinued', 'draft'])->default('draft');

            // Pricing
            $table->decimal('cost_price', 15, 4)->default(0); // Purchase cost
            $table->decimal('sale_price', 15, 4)->default(0); // Regular selling price
            $table->decimal('wholesale_price', 15, 4)->nullable(); // Bulk price
            $table->decimal('special_price', 15, 4)->nullable(); // Promotional price
            $table->timestamp('special_price_start')->nullable();
            $table->timestamp('special_price_end')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0); // Override tenant default
            $table->boolean('tax_inclusive')->default(false); // Override tenant default

            // Inventory
            $table->decimal('stock_quantity', 15, 4)->default(0); // Current stock
            $table->decimal('reserved_quantity', 15, 4)->default(0); // Reserved for orders
            $table->decimal('available_quantity', 15, 4)->default(0); // Computed: stock - reserved
            $table->decimal('reorder_level', 15, 4)->default(0); // Min stock level
            $table->decimal('reorder_quantity', 15, 4)->default(0); // Qty to reorder
            $table->decimal('max_stock_level', 15, 4)->nullable(); // Max stock level
            $table->string('unit')->default('pcs'); // pcs, box, pack, meter, etc.
            $table->decimal('weight', 10, 3)->nullable(); // In kg
            $table->json('dimensions')->nullable(); // {length, width, height} in cm

            // Serial/IMEI Tracking
            $table->boolean('is_serialized')->default(false); // Track individual units
            $table->boolean('track_imei')->default(false); // For phones

            // Warranty
            $table->integer('warranty_months')->default(0);
            $table->string('warranty_type')->nullable(); // manufacturer, seller, extended

            // Media & Description
            $table->json('images')->nullable(); // Array of image paths
            $table->string('thumbnail')->nullable(); // Main image
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->longText('specifications')->nullable(); // Technical specs

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();

            // POS & Sales
            $table->boolean('is_pos_visible')->default(true); // Show in POS
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            // Purchase Info
            $table->string('preferred_supplier_id')->nullable(); // Supplier code/reference
            $table->integer('lead_time_days')->default(0); // Days to restock

            // Meta
            $table->json('attributes')->nullable(); // Flexible attributes: color, storage, size, etc.
            $table->json('settings')->nullable(); // Additional settings

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['tenant_id', 'sku']);
            $table->unique(['tenant_id', 'barcode']);
            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'category_id']);
            $table->index(['tenant_id', 'brand_id']);
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'is_pos_visible']);
            $table->index(['tenant_id', 'is_featured']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};