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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Customer Identification
            $table->string('code')->unique(); // Auto-generated: CUST-000001
            $table->string('name');
            $table->string('phone')->index();
            $table->string('email')->nullable()->index();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Bangladesh');

            // Customer Classification
            $table->enum('type', ['walk_in', 'regular', 'corporate', 'wholesale'])->default('regular');
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');

            // Financial
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('outstanding_balance', 15, 2)->default(0);
            $table->integer('payment_terms_days')->nullable(); // Override tenant default

            // Additional Info
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('national_id')->nullable(); // NID for KYC
            $table->json('tags')->nullable(); // Custom tags for segmentation
            $table->text('notes')->nullable();
            $table->json('preferences')->nullable(); // Communication preferences, etc.

            // Meta
            $table->string('source')->nullable(); // walk_in, referral, online, etc.
            $table->string('referred_by')->nullable();
            $table->timestamp('last_purchase_at')->nullable();
            $table->timestamp('last_repair_at')->nullable();
            $table->integer('total_purchases')->default(0);
            $table->integer('total_repairs')->default(0);
            $table->decimal('lifetime_value', 15, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Composite indexes for tenant-scoped queries
            $table->unique(['tenant_id', 'code']);
            $table->unique(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};