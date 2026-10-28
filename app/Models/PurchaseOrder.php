<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'location_id',
        'created_by',
        'approved_by',
        'po_number',
        'order_date',
        'expected_delivery_date',
        'status',
        'subtotal',
        'discount_amount',
        'discount_percentage',
        'tax_amount',
        'shipping_cost',
        'other_charges',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'payment_status',
        'payment_method',
        'payment_reference',
        'notes',
        'supplier_notes',
        'terms_conditions',
        'attachments',
        'approved_at',
        'ordered_at',
        'received_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'attachments' => 'array',
        'approved_at' => 'datetime',
        'ordered_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the purchase order.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the supplier for this purchase order.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the inventory location for this purchase order.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class);
    }

    /**
     * Get the user who created this purchase order.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this purchase order.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get purchase order items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class)->orderBy('sort_order');
    }

    /**
     * Get purchase receipts for this purchase order.
     */
    public function receipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class);
    }

    /**
     * Get total received quantity across all items.
     */
    public function getTotalReceivedQuantityAttribute(): float
    {
        return $this->items->sum('received_quantity');
    }

    /**
     * Get total pending quantity across all items.
     */
    public function getTotalPendingQuantityAttribute(): float
    {
        return $this->items->sum('pending_quantity');
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'pending_approval' => 'warning',
            'approved' => 'info',
            'ordered' => 'primary',
            'partial_received' => 'warning',
            'received' => 'success',
            'cancelled' => 'danger',
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
     * Check if purchase order can be approved.
     */
    public function canBeApproved(): bool
    {
        return in_array($this->status, ['draft', 'pending_approval']);
    }

    /**
     * Check if purchase order can be ordered.
     */
    public function canBeOrdered(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if purchase order can be received.
     */
    public function canBeReceived(): bool
    {
        return in_array($this->status, ['ordered', 'partial_received']);
    }

    /**
     * Check if purchase order is fully received.
     */
    public function isFullyReceived(): bool
    {
        return $this->items->every(fn ($item) => $item->pending_quantity <= 0);
    }

    /**
     * Check if purchase order is partially received.
     */
    public function isPartiallyReceived(): bool
    {
        $received = $this->items->sum('received_quantity');
        $total = $this->items->sum('quantity');

        return $received > 0 && $received < $total;
    }

    /**
     * Generate next PO number for tenant.
     */
    public static function generatePONumber(int $tenantId): string
    {
        $year = now()->year;
        $latest = self::where('tenant_id', $tenantId)
            ->where('po_number', 'like', "PO-{$year}-%")
            ->latest('id')
            ->first();

        $sequence = $latest ? (int) substr($latest->po_number, -6) + 1 : 1;

        return sprintf("PO-{$year}-%06d", $sequence);
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
     * Scope for ordered.
     */
    public function scopeOrdered($query)
    {
        return $query->where('status', 'ordered');
    }

    /**
     * Scope for received.
     */
    public function scopeReceived($query)
    {
        return $query->whereIn('status', ['partial_received', 'received']);
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (self $po) {
            if (! $po->po_number) {
                $tenantId = $po->tenant_id ?? Tenant::getCurrentTenant()?->id;
                if ($tenantId) {
                    $po->po_number = self::generatePONumber($tenantId);
                }
            }

            if (! $po->status) {
                $po->status = 'draft';
            }

            if (! $po->payment_status) {
                $po->payment_status = 'unpaid';
            }

            if (! $po->order_date) {
                $po->order_date = now()->toDateString();
            }
        });

        static::updating(function (self $po) {
            // Auto-update status based on receipt progress
            if ($po->isDirty('status')) {
                return; // Don't override manual status changes
            }

            if ($po->status === 'ordered' || $po->status === 'partial_received') {
                if ($po->isFullyReceived()) {
                    $po->status = 'received';
                    $po->received_at = now();
                } elseif ($po->isPartiallyReceived()) {
                    $po->status = 'partial_received';
                }
            }
        });
    }
}
