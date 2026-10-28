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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Supplier Identification
            $table->string('code')->unique(); // Auto-generated: SUP-000001
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Bangladesh');

            // Business Details
            $table->string('trade_license_number')->nullable();
            $table->string('vat_registration_number')->nullable();
            $table->string('tax_identification_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('bank_routing_number')->nullable();

            // Financial
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('outstanding_balance', 15, 2)->default(0);
            $table->integer('payment_terms_days')->default(0); // Net 30, Net 60, etc.

            // Settings
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->enum('type', ['distributor', 'manufacturer', 'wholesaler', 'importer', 'local_vendor', 'service_provider'])->default('distributor');
            $table->integer('lead_time_days')->default(0); // Average delivery time
            $table->decimal('discount_percentage', 5, 2)->default(0); // Default discount

            // Communication
            $table->json('preferences')->nullable(); // Communication preferences
            $table->text('notes')->nullable();

            // Performance Metrics
            $table->integer('total_orders')->default(0);
            $table->decimal('total_purchase_value', 15, 2)->default(0);
            $table->decimal('on_time_delivery_rate', 5, 2)->nullable(); // Percentage
            $table->timestamp('last_order_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
            $table->unique(['tenant_id', 'phone']);
            $table->unique(['tenant_id', 'email']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
