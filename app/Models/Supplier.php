<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'contact_person',
        'phone',
        'mobile',
        'email',
        'website',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'trade_license_number',
        'vat_registration_number',
        'tax_identification_number',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'bank_branch',
        'bank_routing_number',
        'credit_limit',
        'outstanding_balance',
        'payment_terms_days',
        'status',
        'type',
        'lead_time_days',
        'discount_percentage',
        'preferences',
        'notes',
        'total_orders',
        'total_purchase_value',
        'on_time_delivery_rate',
        'last_order_at',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'total_purchase_value' => 'decimal:2',
        'on_time_delivery_rate' => 'decimal:2',
        'preferences' => 'array',
        'last_order_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the supplier.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get purchase orders for this supplier.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get purchase receipts for this supplier.
     */
    public function purchaseReceipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class);
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
     * Get display name with code.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }

    /**
     * Check if supplier has available credit.
     */
    public function hasAvailableCredit(float $amount = 0): bool
    {
        $available = $this->credit_limit - $this->outstanding_balance;

        return $available >= $amount;
    }

    /**
     * Get available credit amount.
     */
    public function getAvailableCreditAttribute(): float
    {
        return max(0, $this->credit_limit - $this->outstanding_balance);
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'inactive' => 'gray',
            'blocked' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'distributor' => 'Distributor',
            'manufacturer' => 'Manufacturer',
            'wholesaler' => 'Wholesaler',
            'importer' => 'Importer',
            'local_vendor' => 'Local Vendor',
            'service_provider' => 'Service Provider',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Generate next supplier code for tenant.
     */
    public static function generateCode(int $tenantId): string
    {
        $latest = self::where('tenant_id', $tenantId)
            ->latest('id')
            ->first();

        $sequence = $latest ? (int) substr($latest->code, -6) + 1 : 1;

        return sprintf('SUP-%06d', $sequence);
    }

    /**
     * Scope for active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (self $supplier) {
            if (! $supplier->code) {
                $tenantId = $supplier->tenant_id ?? Tenant::getCurrentTenant()?->id;
                if ($tenantId) {
                    $supplier->code = self::generateCode($tenantId);
                }
            }

            if (! $supplier->status) {
                $supplier->status = 'active';
            }
        });
    }
}
