<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'location_id',
        'created_by',
        'sale_number',
        'sale_date',
        'status',
        'subtotal',
        'discount_amount',
        'discount_percentage',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'payment_status',
        'payment_method',
        'payment_reference',
        'notes',
        'terms_conditions',
        'completed_at',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the sale.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the customer for this sale.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the inventory location for this sale.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class);
    }

    /**
     * Get the user who created this sale.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get sale items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class)->orderBy('sort_order');
    }

    /**
     * Get sale payments.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'pending' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            'refunded' => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get payment status badge color.
     */
    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'danger',
            'partial' => 'warning',
            'paid' => 'success',
            'refunded' => 'purple',
            default => 'gray',
        };
    }

    /**
     * Get customer name or 'Walk-in Customer'.
     */
    public function getCustomerNameAttribute(): string
    {
        return $this->customer?->name ?? 'Walk-in Customer';
    }

    /**
     * Check if sale is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if sale can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return $this->status === 'completed' && $this->payment_status !== 'refunded';
    }

    /**
     * Generate next sale number for tenant.
     */
    public static function generateSaleNumber(int $tenantId): string
    {
        $year = now()->year;
        $latest = self::where('tenant_id', $tenantId)
            ->where('sale_number', 'like', "SAL-{$year}-%")
            ->latest('id')
            ->first();

        $sequence = $latest ? (int) substr($latest->sale_number, -6) + 1 : 1;

        return sprintf("SAL-{$year}-%06d", $sequence);
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
     * Scope for completed sales.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for today's sales.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('sale_date', now()->toDateString());
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (self $sale) {
            if (! $sale->sale_number) {
                $tenantId = $sale->tenant_id ?? Tenant::getCurrentTenant()?->id;
                if ($tenantId) {
                    $sale->sale_number = self::generateSaleNumber($tenantId);
                }
            }

            if (! $sale->status) {
                $sale->status = 'draft';
            }

            if (! $sale->payment_status) {
                $sale->payment_status = 'unpaid';
            }

            if (! $sale->sale_date) {
                $sale->sale_date = now()->toDateString();
            }
        });

        static::updated(function (self $sale) {
            // Update payment status based on paid amount
            if ($sale->wasChanged('paid_amount') || $sale->wasChanged('total_amount')) {
                if ($sale->paid_amount >= $sale->total_amount) {
                    $sale->payment_status = 'paid';
                } elseif ($sale->paid_amount > 0) {
                    $sale->payment_status = 'partial';
                } else {
                    $sale->payment_status = 'unpaid';
                }

                $sale->balance_amount = $sale->total_amount - $sale->paid_amount;
                $sale->saveQuietly();
            }
        });
    }
}
