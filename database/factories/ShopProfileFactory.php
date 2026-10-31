<?php

namespace Database\Factories;

use App\Models\ShopProfile;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShopProfileFactory extends Factory
{
    protected $model = ShopProfile::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'business_name' => fake()->company(),
            'trade_license_number' => fake()->unique()->regexify('[A-Z]{2}[0-9]{6}'),
            'trade_license_expiry' => fake()->dateTimeBetween('now', '+2 years'),
            'vat_registration_number' => fake()->unique()->regexify('[0-9]{15}'),
            'tax_identification_number' => fake()->unique()->regexify('[0-9]{12}'),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => 'Bangladesh',
            'phone' => fake()->unique()->phoneNumber(),
            'alternate_phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'website' => fake()->url(),
            'logo' => null,
            'favicon' => null,
            'invoice_prefix' => 'INV',
            'repair_ticket_prefix' => 'REP',
            'purchase_order_prefix' => 'PO',
            'quotation_prefix' => 'QUO',
            'currency' => 'BDT',
            'currency_symbol' => '৳',
            'timezone' => 'Asia/Dhaka',
            'locale' => 'en',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'default_tax_rate' => 15.00,
            'tax_label' => 'VAT',
            'tax_inclusive' => false,
            'payment_terms_days' => 30,
            'rounding_precision' => 0.50,
            'enable_barcode_scanner' => true,
            'enable_customer_display' => false,
            'auto_print_receipt' => false,
            'receipt_printer_type' => 'thermal',
            'receipt_width' => 80,
            'enable_imei_tracking' => true,
            'default_warranty_days' => 30,
            'require_customer_approval' => true,
            'enable_quality_check' => true,
            'allow_negative_stock' => false,
            'track_serial_numbers' => true,
            'enable_low_stock_alerts' => true,
            'low_stock_threshold' => 5,
            'email_notifications' => true,
            'sms_notifications' => false,
            'notification_events' => [
                'new_order' => true,
                'order_status_change' => true,
                'repair_status_change' => true,
                'low_stock' => true,
                'payment_received' => true,
            ],
            'invoice_terms' => 'Payment due within stated terms. Late payments may incur interest.',
            'repair_terms' => 'Repairs are performed based on customer approval. Warranty covers parts and labor for the specified period.',
            'warranty_terms' => 'Warranty does not cover physical damage, liquid damage, or unauthorized repairs.',
            'settings' => [
                'theme' => 'light',
                'sidebar_collapsed' => false,
                'notifications' => true,
            ],
            'is_setup_complete' => true,
            'setup_completed_at' => now(),
        ];
    }

    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_setup_complete' => true,
            'setup_completed_at' => now(),
            'business_name' => $attributes['business_name'] ?? fake()->company(),
            'address' => $attributes['address'] ?? fake()->streetAddress(),
            'phone' => $attributes['phone'] ?? fake()->phoneNumber(),
            'email' => $attributes['email'] ?? fake()->safeEmail(),
        ]);
    }

    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_setup_complete' => false,
            'setup_completed_at' => null,
        ]);
    }
}