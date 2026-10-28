<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReceiptItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'purchase_receipt_id',
        'purchase_order_item_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'line_total',
        'accepted_quantity',
        'rejected_quantity',
        'rejection_reason',
        'notes',
        'metadata',
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
        'accepted_quantity' => 'decimal:4',
        'rejected_quantity' => 'decimal:4',
        'metadata' => 'array',
    ];

    /**
     * Get the tenant that owns the purchase receipt item.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the purchase receipt this item belongs to.
     */
    public function purchaseReceipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class);
    }

    /**
     * Get the purchase order item this receipt item is linked to.
     */
    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    /**
     * Get the product for this item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate line total.
     */
    public function calculateLineTotal(): float
    {
        $subtotal = $this->accepted_quantity * $this->unit_price;
        $discount = $this->discount_amount ?: ($subtotal * $this->discount_percentage / 100);
        $taxable = $subtotal - $discount;
        $tax = $this->tax_amount ?: ($taxable * $this->tax_rate / 100);

        return round($taxable + $tax, 2);
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
            $item->line_total = $item->calculateLineTotal();
        });

        static::updating(function (self $item) {
            if ($item->isDirty(['accepted_quantity', 'unit_price', 'discount_percentage', 'discount_amount', 'tax_rate', 'tax_amount'])) {
                $item->line_total = $item->calculateLineTotal();
            }
        });
    }
}
