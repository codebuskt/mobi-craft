<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReceipt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'purchase_order_id',
        'supplier_id',
        'location_id',
        'received_by',
        'approved_by',
        'receipt_number',
        'receipt_date',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'payment_status',
        'paid_amount',
        'balance_amount',
        'notes',
        'quality_notes',
        'attachments',
        'approved_at',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'attachments' => 'array',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the purchase receipt.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the purchase order this receipt is linked to.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the supplier for this receipt.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the inventory location for this receipt.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class);
    }

    /**
     * Get the user who received this purchase receipt.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the user who approved this purchase receipt.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get purchase receipt items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class)->orderBy('sort_order');
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'pending_approval' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
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
            default => 'gray',
        };
    }

    /**
     * Check if receipt can be approved.
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'draft' || $this->status === 'pending_approval';
    }

    /**
     * Generate next receipt number for tenant.
     */
    public static function generateReceiptNumber(int $tenantId): string
    {
        $year = now()->year;
        $latest = self::where('tenant_id', $tenantId)
            ->where('receipt_number', 'like', "PR-{$year}-%")
            ->latest('id')
            ->first();

        $sequence = $latest ? (int) substr($latest->receipt_number, -6) + 1 : 1;

        return sprintf("PR-{$year}-%06d", $sequence);
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
     * Scope for pending approval.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    /**
     * Scope for approved.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (self $receipt) {
            if (! $receipt->receipt_number) {
                $tenantId = $receipt->tenant_id ?? Tenant::getCurrentTenant()?->id;
                if ($tenantId) {
                    $receipt->receipt_number = self::generateReceiptNumber($tenantId);
                }
            }

            if (! $receipt->status) {
                $receipt->status = 'draft';
            }

            if (! $receipt->payment_status) {
                $receipt->payment_status = 'unpaid';
            }

            if (! $receipt->receipt_date) {
                $receipt->receipt_date = now()->toDateString();
            }
        });

        static::updated(function (self $receipt) {
            // When receipt is approved, update purchase order and stock
            if ($receipt->wasChanged('status') && $receipt->status === 'approved') {
                $receipt->updatePurchaseOrderStatus();
                $receipt->updateStock();
            }
        });
    }

    /**
     * Update purchase order status based on receipt progress.
     */
    public function updatePurchaseOrderStatus(): void
    {
        if ($this->purchaseOrder) {
            $po = $this->purchaseOrder;

            // Check if all items are fully received
            $allReceived = $po->items->every(function ($item) {
                $totalReceived = $this->items()
                    ->where('purchase_order_item_id', $item->id)
                    ->sum('accepted_quantity');

                return $totalReceived >= $item->quantity;
            });

            if ($allReceived) {
                $po->status = 'received';
                $po->received_at = now();
            } else {
                $po->status = 'partial_received';
            }

            $po->save();
        }
    }

    /**
     * Update stock movements for received items.
     */
    public function updateStock(): void
    {
        foreach ($this->items as $item) {
            if ($item->accepted_quantity > 0) {
                // Create stock movement
                StockMovement::create([
                    'tenant_id' => $this->tenant_id,
                    'product_id' => $item->product_id,
                    'location_id' => $this->location_id,
                    'type' => 'purchase',
                    'quantity' => $item->accepted_quantity,
                    'reference_type' => 'purchase_receipt',
                    'reference_id' => $this->id,
                    'unit_cost' => $item->unit_price,
                    'notes' => "Purchase receipt: {$this->receipt_number}",
                ]);
            }
        }
    }
}
