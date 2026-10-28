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
        Schema::create('shop_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Business Identity
            $table->string('business_name');
            $table->string('trade_license_number')->nullable()->unique();
            $table->date('trade_license_expiry')->nullable();
            $table->string('vat_registration_number')->nullable();
            $table->string('tax_identification_number')->nullable();

            // Address & Contact
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Bangladesh');
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->string('email');
            $table->string('website')->nullable();

            // Branding
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('invoice_prefix')->default('INV');
            $table->string('repair_ticket_prefix')->default('REP');
            $table->string('purchase_order_prefix')->default('PO');
            $table->string('quotation_prefix')->default('QUO');

            // Localization
            $table->string('currency')->default('BDT');
            $table->string('currency_symbol')->default('৳');
            $table->string('timezone')->default('Asia/Dhaka');
            $table->string('locale')->default('en');
            $table->string('date_format')->default('d/m/Y');
            $table->string('time_format')->default('H:i');

            // Financial Settings
            $table->decimal('default_tax_rate', 5, 2)->default(0);
            $table->string('tax_label')->default('VAT');
            $table->boolean('tax_inclusive')->default(false);
            $table->integer('payment_terms_days')->default(0);
            $table->decimal('rounding_precision', 3, 2)->default(0.50);

            // POS Settings
            $table->boolean('enable_barcode_scanner')->default(true);
            $table->boolean('enable_customer_display')->default(false);
            $table->boolean('auto_print_receipt')->default(false);
            $table->string('receipt_printer_type')->default('thermal'); // thermal, a4, dot-matrix
            $table->integer('receipt_width')->default(80); // mm for thermal

            // Repair Settings
            $table->boolean('enable_imei_tracking')->default(true);
            $table->integer('default_warranty_days')->default(30);
            $table->boolean('require_customer_approval')->default(true);
            $table->boolean('enable_quality_check')->default(true);

            // Inventory Settings
            $table->boolean('allow_negative_stock')->default(false);
            $table->boolean('track_serial_numbers')->default(true);
            $table->boolean('enable_low_stock_alerts')->default(true);
            $table->integer('low_stock_threshold')->default(5);

            // Notifications
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->json('notification_events')->nullable(); // Which events trigger notifications

            // Terms & Conditions
            $table->text('invoice_terms')->nullable();
            $table->text('repair_terms')->nullable();
            $table->text('warranty_terms')->nullable();

            // Meta
            $table->json('settings')->nullable(); // Flexible key-value for future settings
            $table->boolean('is_setup_complete')->default(false);
            $table->timestamp('setup_completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('tenant_id'); // One profile per tenant
            $table->index('trade_license_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_profiles');
    }
};