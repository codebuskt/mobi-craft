<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'type',
        'status',
        'credit_limit',
        'outstanding_balance',
        'payment_terms_days',
        'date_of_birth',
        'gender',
        'national_id',
        'tags',
        'notes',
        'preferences',
        'source',
        'referred_by',
        'last_purchase_at',
        'last_repair_at',
        'total_purchases',
        'total_repairs',
        'lifetime_value',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'lifetime_value' => 'decimal:2',
        'date_of_birth' => 'date',
        'tags' => 'array',
        'preferences' => 'array',
        'last_purchase_at' => 'datetime',
        'last_repair_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the customer.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the contacts for the customer.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    /**
     * Get the primary contact.
     */
    public function primaryContact(): HasMany
    {
        return $this->hasOne(CustomerContact::class)->where('is_primary', true);
    }

    /**
     * Get sales for this customer.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get repair tickets for this customer.
     */
    public function repairTickets(): HasMany
    {
        return $this->hasMany(RepairTicket::class);
    }

    /**
     * Get payments for this customer.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(CustomerPayment::class);
    }

    /**
     * Get dues for this customer.
     */
    public function dues(): HasMany
    {
        return $this->hasMany(CustomerDue::class);
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
     * Get the display name with code.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }

    /**
     * Check if customer has available credit.
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
     * Check if customer is over credit limit.
     */
    public function isOverCreditLimit(): bool
    {
        return $this->outstanding_balance > $this->credit_limit && $this->credit_limit > 0;
    }

    /**
     * Get customer type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'walk_in' => 'Walk-in',
            'regular' => 'Regular',
            'corporate' => 'Corporate',
            'wholesale' => 'Wholesale',
            default => $this->type,
        };
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
     * Generate next customer code for tenant.
     */
    public static function generateCode(int $tenantId): string
    {
        $latest = self::where('tenant_id', $tenantId)
            ->latest('id')
            ->first();

        $sequence = $latest ? (int) substr($latest->code, -6) + 1 : 1;

        return sprintf('CUST-%06d', $sequence);
    }

    /**
     * Normalize phone number for consistent storage and search.
     */
    public static function normalizePhone(string $phone): string
    {
        // Remove all non-digits
        $digits = preg_replace('/\D/', '', $phone);

        // Handle Bangladesh numbers
        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 3);
        }
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        // Format as +8801XXXXXXXXX
        if (strlen($digits) === 10 || strlen($digits) === 11) {
            return '+880'.$digits;
        }

        return '+880'.$digits;
    }

    /**
     * Scope for active customers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for customers with outstanding balance.
     */
    public function scopeWithDues($query)
    {
        return $query->where('outstanding_balance', '>', 0);
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
        static::creating(function (self $customer) {
            if (! $customer->code) {
                $tenantId = $customer->tenant_id ?? Tenant::getCurrentTenant()?->id;
                if ($tenantId) {
                    $customer->code = self::generateCode($tenantId);
                }
            }

            if ($customer->phone) {
                $customer->phone = self::normalizePhone($customer->phone);
            }
        });
    }
}
