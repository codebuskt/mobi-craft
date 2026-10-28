<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'business_name',
        'trade_license_number',
        'trade_license_expiry',
        'vat_registration_number',
        'tax_identification_number',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'alternate_phone',
        'email',
        'website',
        'logo',
        'favicon',
        'invoice_prefix',
        'repair_ticket_prefix',
        'purchase_order_prefix',
        'quotation_prefix',
        'currency',
        'currency_symbol',
        'timezone',
        'locale',
        'date_format',
        'time_format',
        'default_tax_rate',
        'tax_label',
        'tax_inclusive',
        'payment_terms_days',
        'rounding_precision',
        'enable_barcode_scanner',
        'enable_customer_display',
        'auto_print_receipt',
        'receipt_printer_type',
        'receipt_width',
        'enable_imei_tracking',
        'default_warranty_days',
        'require_customer_approval',
        'enable_quality_check',
        'allow_negative_stock',
        'track_serial_numbers',
        'enable_low_stock_alerts',
        'low_stock_threshold',
        'email_notifications',
        'sms_notifications',
        'notification_events',
        'invoice_terms',
        'repair_terms',
        'warranty_terms',
        'settings',
        'is_setup_complete',
        'setup_completed_at',
    ];

    protected $casts = [
        'trade_license_expiry' => 'date',
        'default_tax_rate' => 'decimal:2',
        'rounding_precision' => 'decimal:2',
        'enable_barcode_scanner' => 'boolean',
        'enable_customer_display' => 'boolean',
        'auto_print_receipt' => 'boolean',
        'enable_imei_tracking' => 'boolean',
        'require_customer_approval' => 'boolean',
        'enable_quality_check' => 'boolean',
        'allow_negative_stock' => 'boolean',
        'track_serial_numbers' => 'boolean',
        'enable_low_stock_alerts' => 'boolean',
        'email_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'notification_events' => 'array',
        'settings' => 'array',
        'is_setup_complete' => 'boolean',
        'setup_completed_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the shop profile.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the full formatted address.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the default currency for formatting.
     */
    public function getDefaultCurrencyAttribute(): string
    {
        return $this->currency ?? 'BDT';
    }

    /**
     * Get the currency symbol for display.
     */
    public function getCurrencySymbolAttribute(): string
    {
        return $this->currency_symbol ?? '৳';
    }

    /**
     * Format a monetary amount with currency.
     */
    public function formatCurrency(float|int|string $amount): string
    {
        $amount = (float) $amount;
        $symbol = $this->currency_symbol;
        $precision = 2;

        return $symbol . number_format($amount, $precision, '.', ',');
    }

    /**
     * Generate the next invoice number.
     */
    public function getNextInvoiceNumber(): string
    {
        $prefix = $this->invoice_prefix ?? 'INV';
        $year = now()->format('Y');

        // Get the latest sale for this tenant this year
        $latestSale = \App\Models\Sale::where('tenant_id', $this->tenant_id)
            ->whereYear('created_at', now()->year)
            ->latest('id')
            ->first();

        $sequence = $latestSale
            ? (int) substr($latestSale->invoice_number, -6) + 1
            : 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }

    /**
     * Generate the next repair ticket number.
     */
    public function getNextRepairTicketNumber(): string
    {
        $prefix = $this->repair_ticket_prefix ?? 'REP';
        $year = now()->format('Y');

        $latestTicket = \App\Models\RepairTicket::where('tenant_id', $this->tenant_id)
            ->whereYear('created_at', now()->year)
            ->latest('id')
            ->first();

        $sequence = $latestTicket
            ? (int) substr($latestTicket->ticket_number, -6) + 1
            : 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }

    /**
     * Generate the next purchase order number.
     */
    public function getNextPurchaseOrderNumber(): string
    {
        $prefix = $this->purchase_order_prefix ?? 'PO';
        $year = now()->format('Y');

        $latestPO = \App\Models\PurchaseOrder::where('tenant_id', $this->tenant_id)
            ->whereYear('created_at', now()->year)
            ->latest('id')
            ->first();

        $sequence = $latestPO
            ? (int) substr($latestPO->po_number, -6) + 1
            : 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }

    /**
     * Generate the next quotation number.
     */
    public function getNextQuotationNumber(): string
    {
        $prefix = $this->quotation_prefix ?? 'QUO';
        $year = now()->format('Y');

        $latestQuotation = \App\Models\Quotation::where('tenant_id', $this->tenant_id)
            ->whereYear('created_at', now()->year)
            ->latest('id')
            ->first();

        $sequence = $latestQuotation
            ? (int) substr($latestQuotation->quotation_number, -6) + 1
            : 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $sequence);
    }

    /**
     * Check if setup is complete.
     */
    public function isSetupComplete(): bool
    {
        return $this->is_setup_complete
            && filled($this->business_name)
            && filled($this->address)
            && filled($this->phone)
            && filled($this->email);
    }

    /**
     * Get a setting value with fallback.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Set a setting value.
     */
    public function setSetting(string $key, mixed $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Get the logo URL.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        return asset('storage/' . $this->logo);
    }

    /**
     * Scope for current tenant.
     */
    public function scopeCurrentTenant($query)
    {
        $tenant = Tenant::getCurrentTenant();

        if ($tenant) {
            return $query->where('tenant_id', $tenant->id);
        }

        return $query->whereNull('tenant_id');
    }
}