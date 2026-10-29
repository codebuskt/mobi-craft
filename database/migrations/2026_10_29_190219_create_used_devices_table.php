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
        Schema::create('used_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete(); // Source purchase
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete(); // Link to catalog product

            // Device Identification
            $table->string('device_number')->unique(); // Internal tracking number: UD-{YEAR}-{SEQ}
            $table->string('imei')->nullable()->index(); // Encrypted in model
            $table->string('serial_number')->nullable()->index(); // Encrypted in model
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->string('storage')->nullable(); // e.g., 128GB, 256GB
            $table->string('carrier')->nullable(); // Locked/unlocked carrier

            // Purchase/Acquisition
            $table->enum('purchase_source', ['customer', 'supplier', 'trade_in', 'auction', 'other'])->default('customer');
            $table->string('purchase_source_reference')->nullable(); // Customer name, supplier, etc.
            $table->date('purchase_date');
            $table->decimal('acquisition_cost', 15, 4)->default(0); // What we paid
            $table->decimal('additional_costs', 15, 4)->default(0); // Shipping, fees, etc.

            // Condition & Inspection
            $table->enum('condition_grade', ['a_plus', 'a', 'b_plus', 'b', 'c_plus', 'c', 'd'])->default('b'); // A+ to D
            $table->enum('functional_status', ['fully_functional', 'minor_issues', 'major_issues', 'parts_only'])->default('fully_functional');
            $table->json('inspection_checklist')->nullable(); // Structured inspection results
            $table->text('inspection_notes')->nullable();
            $table->json('defects_found')->nullable(); // Array of defects
            $table->json('intake_photos')->nullable(); // Photos at acquisition

            // Refurbishment
            $table->decimal('refurbishment_cost', 15, 4)->default(0); // Parts + labor
            $table->json('refurbishment_parts')->nullable(); // Parts used from inventory
            $table->json('refurbishment_notes')->nullable();
            $table->json('refurbishment_photos')->nullable(); // Before/after photos
            $table->date('refurbishment_completed_at')->nullable();

            // Ownership & Documentation
            $table->boolean('ownership_verified')->default(false);
            $table->json('ownership_documents')->nullable(); // Receipts, ID copies, etc.
            $table->text('ownership_notes')->nullable();

            // Listing & Sales
            $table->enum('listing_status', ['acquired', 'inspecting', 'refurbishing', 'ready_for_sale', 'listed', 'reserved', 'sold', 'returned', 'written_off'])->default('acquired');
            $table->decimal('listing_price', 15, 4)->nullable(); // Asking price
            $table->decimal('minimum_price', 15, 4)->nullable(); // Floor price
            $table->date('listed_at')->nullable();
            $table->string('listing_platform')->nullable(); // In-store, online, marketplace
            $table->text('listing_description')->nullable();
            $table->json('listing_photos')->nullable();

            // Sale Information
            $table->foreignId('sale_id')->nullable()->constrained()->nullOnDelete(); // When sold
            $table->decimal('sale_price', 15, 4)->nullable(); // Actual sale price
            $table->date('sold_at')->nullable();
            $table->decimal('gross_margin', 15, 4)->nullable(); // Sale price - (acquisition + refurb + additional)
            $table->decimal('margin_percentage', 5, 2)->nullable(); // Gross margin %

            // Warranty
            $table->integer('warranty_months')->default(0);
            $table->string('warranty_type')->nullable(); // seller, manufacturer, extended

            // Meta
            $table->json('attributes')->nullable(); // Flexible: color, storage, size, etc.
            $table->json('settings')->nullable(); // Additional settings
            $table->text('notes')->nullable(); // Internal notes

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['tenant_id', 'device_number']);
            $table->unique(['tenant_id', 'imei'])->whereNotNull('imei');
            $table->unique(['tenant_id', 'serial_number'])->whereNotNull('serial_number');
            $table->index(['tenant_id', 'category_id']);
            $table->index(['tenant_id', 'brand_id']);
            $table->index(['tenant_id', 'listing_status']);
            $table->index(['tenant_id', 'condition_grade']);
            $table->index(['tenant_id', 'purchase_date']);
            $table->index(['tenant_id', 'sold_at']);
            $table->index(['tenant_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('used_devices');
    }
};