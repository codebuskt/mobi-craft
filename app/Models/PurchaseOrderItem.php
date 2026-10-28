<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'purchase_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'line_total',
        'received_quantity',
        'pending_quantity',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'received_quantity' => 'decimal:4',
        'pending_quantity' => 'decimal:4',
    ];

    /**
     * Get the tenant that owns the purchase order item.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the purchase order this item belongs to.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the product for this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if item is fully received.
     */
    public function isFullyReceived(): bool
    {
        return $this->pending_quantity <= 0;
    }

    /**
     * Check if item is partially received.
     */
    public function isPartiallyReceived(): bool
    {
        return $this->received_quantity > 0 && $this->pending_quantity > 0;
    }

    /**
     * Calculate line total based on quantity, unit price, discount, and tax.
     */
    public function calculateLineTotal(): float
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discount = $this->discount_amount ?: ($subtotal * $this->discount_percentage / 100);
        $taxable = $subtotal - $discount;
        $tax = $this->tax_amount ?: ($taxable * $this->tax_rate / 100);

        return round($taxable + $tax, 2);
    }

    /**
     * Update calculated fields.
     */
    public function updateCalculations(): void
    {
        $this->line_total = $this->calculateLineTotal();
        $this->pending_quantity = $this->quantity - $this->received_quantity;
        $this->save();
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
        static::creating(function (self $item) {
            $item->pending_quantity = $item->quantity;
            $item->line_total = $item->calculateLineTotal();
        });

        static::updating(function (self $item) {
            if ($item->isDirty(['quantity', 'unit_price', 'discount_percentage', 'discount_amount', 'tax_rate', 'tax_amount'])) {
                $item->line_total = $item->calculateLineTotal();
            }

            if ($item->isDirty('received_quantity')) {
                $item->pending_quantity = $item->quantity - $item->received_quantity;
            }
        });
    }
}
