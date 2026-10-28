<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockMovement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'location_id',
        'from_location_id',
        'to_location_id',
        'user_id',
        'type',
        'quantity',
        'unit_cost',
        'total_cost',
        'running_balance',
        'reference_type',
        'reference_id',
        'reference_number',
        'notes',
        'metadata',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'running_balance' => 'decimal:4',
        'reference_id' => 'integer',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the tenant that owns the movement.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the location.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    /**
     * Get the source location (for transfers).
     */
    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'from_location_id');
    }

    /**
     * Get the destination location (for transfers).
     */
    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'to_location_id');
    }

    /**
     * Get the user who performed the movement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the approver.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the polymorphic reference.
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if movement increases stock.
     */
    public function isInbound(): bool
    {
        return in_array($this->type, [
            'purchase',
            'purchase_return', // Actually outbound, but from supplier perspective
            'adjustment_in',
            'transfer_in',
            'repair_return',
            'opening_stock',
            'stock_take', // Can be positive or negative
        ]);
    }

    /**
     * Check if movement decreases stock.
     */
    public function isOutbound(): bool
    {
        return in_array($this->type, [
            'sale',
            'sale_return', // Actually inbound, but from customer perspective
            'adjustment_out',
            'transfer_out',
            'repair_consumption',
            'damage',
            'loss',
        ]);
    }

    /**
     * Get movement direction.
     */
    public function getDirectionAttribute(): string
    {
        return $this->quantity > 0 ? 'in' : 'out';
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'purchase' => 'Purchase Receipt',
            'purchase_return' => 'Purchase Return',
            'sale' => 'Sale',
            'sale_return' => 'Sale Return',
            'adjustment_in' => 'Adjustment (+)',
            'adjustment_out' => 'Adjustment (-)',
            'transfer_in' => 'Transfer In',
            'transfer_out' => 'Transfer Out',
            'repair_consumption' => 'Repair Consumption',
            'repair_return' => 'Repair Return',
            'damage' => 'Damage/Loss',
            'loss' => 'Loss/Theft',
            'opening_stock' => 'Opening Stock',
            'stock_take' => 'Stock Take',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Get formatted quantity with sign.
     */
    public function getFormattedQuantityAttribute(): string
    {
        $sign = $this->quantity > 0 ? '+' : '';

        return $sign.number_format($this->quantity, 2);
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
     * Scope for approved movements.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for a specific product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope for a specific location.
     */
    public function scopeAtLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Scope for movement type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for date range.
     */
    public function scopeDateRange($query, $startDate, $endDate = null)
    {
        $query->whereDate('created_at', '>=', $startDate);

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (self $movement) {
            // Set tenant from current context if not provided
            if (! $movement->tenant_id) {
                $tenant = Tenant::getCurrentTenant();
                if ($tenant) {
                    $movement->tenant_id = $tenant->id;
                }
            }

            // Set user from auth if not provided
            if (! $movement->user_id && auth()->check()) {
                $movement->user_id = auth()->id();
            }

            // Calculate total cost
            $movement->total_cost = $movement->quantity * $movement->unit_cost;

            // Calculate running balance
            if ($movement->product_id && $movement->location_id) {
                $lastMovement = self::where('tenant_id', $movement->tenant_id)
                    ->where('product_id', $movement->product_id)
                    ->where('location_id', $movement->location_id)
                    ->where('status', 'approved')
                    ->latest('created_at')
                    ->first();

                $previousBalance = $lastMovement ? $lastMovement->running_balance : 0;
                $movement->running_balance = $previousBalance + $movement->quantity;
            }
        });

        // Update product stock on approved movements
        static::saved(function (self $movement) {
            if ($movement->status === 'approved') {
                $movement->updateProductStock();
            }
        });

        // Handle status changes
        static::updated(function (self $movement) {
            if ($movement->wasChanged('status')) {
                if ($movement->status === 'approved') {
                    $movement->updateProductStock();
                } elseif ($movement->getOriginal('status') === 'approved') {
                    // Was approved, now rejected/pending - reverse the stock
                    $movement->reverseProductStock();
                }
            }
        });

        // Handle deletion
        static::deleted(function (self $movement) {
            if ($movement->status === 'approved') {
                $movement->reverseProductStock();
            }
        });
    }

    /**
     * Update product stock based on this movement.
     */
    public function updateProductStock(): void
    {
        $product = $this->product;

        if (! $product) {
            return;
        }

        // Only update stock for inbound/outbound movements
        if (in_array($this->type, ['purchase', 'sale', 'sale_return', 'adjustment_in', 'adjustment_out', 'damage', 'loss', 'opening_stock', 'stock_take'])) {
            $product->stock_quantity += $this->quantity;
            $product->available_quantity = $product->stock_quantity - $product->reserved_quantity;
            $product->save();
        }
    }

    /**
     * Reverse product stock (when movement is rejected/deleted).
     */
    public function reverseProductStock(): void
    {
        $product = $this->product;

        if (! $product) {
            return;
        }

        if (in_array($this->type, ['purchase', 'sale', 'sale_return', 'adjustment_in', 'adjustment_out', 'damage', 'loss', 'opening_stock', 'stock_take'])) {
            $product->stock_quantity -= $this->quantity;
            $product->available_quantity = $product->stock_quantity - $product->reserved_quantity;
            $product->save();
        }
    }

    /**
     * Create a stock movement for a sale.
     */
    public static function createForSale(
        int $tenantId,
        int $productId,
        int $locationId,
        float $quantity,
        float $unitCost,
        int $referenceId,
        string $referenceNumber,
        ?int $userId = null
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'product_id' => $productId,
            'location_id' => $locationId,
            'user_id' => $userId,
            'type' => 'sale',
            'quantity' => -abs($quantity), // Negative for sales
            'unit_cost' => $unitCost,
            'reference_type' => Sale::class,
            'reference_id' => $referenceId,
            'reference_number' => $referenceNumber,
            'status' => 'approved',
        ]);
    }

    /**
     * Create a stock movement for a purchase receipt.
     */
    public static function createForPurchase(
        int $tenantId,
        int $productId,
        int $locationId,
        float $quantity,
        float $unitCost,
        int $referenceId,
        string $referenceNumber,
        ?int $userId = null
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'product_id' => $productId,
            'location_id' => $locationId,
            'user_id' => $userId,
            'type' => 'purchase',
            'quantity' => abs($quantity), // Positive for purchases
            'unit_cost' => $unitCost,
            'reference_type' => PurchaseReceipt::class,
            'reference_id' => $referenceId,
            'reference_number' => $referenceNumber,
            'status' => 'approved',
        ]);
    }

    /**
     * Create a stock movement for repair consumption.
     */
    public static function createForRepairConsumption(
        int $tenantId,
        int $productId,
        int $locationId,
        float $quantity,
        float $unitCost,
        int $referenceId,
        string $referenceNumber,
        ?int $userId = null
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'product_id' => $productId,
            'location_id' => $locationId,
            'user_id' => $userId,
            'type' => 'repair_consumption',
            'quantity' => -abs($quantity),
            'unit_cost' => $unitCost,
            'reference_type' => RepairTicket::class,
            'reference_id' => $referenceId,
            'reference_number' => $referenceNumber,
            'status' => 'approved',
        ]);
    }

    /**
     * Create a stock movement for transfer between locations.
     */
    public static function createForTransfer(
        int $tenantId,
        int $productId,
        int $fromLocationId,
        int $toLocationId,
        float $quantity,
        float $unitCost,
        int $referenceId,
        string $referenceNumber,
        ?int $userId = null
    ): array {
        // Outbound from source location
        $outbound = self::create([
            'tenant_id' => $tenantId,
            'product_id' => $productId,
            'location_id' => $fromLocationId,
            'from_location_id' => $fromLocationId,
            'to_location_id' => $toLocationId,
            'user_id' => $userId,
            'type' => 'transfer_out',
            'quantity' => -abs($quantity),
            'unit_cost' => $unitCost,
            'reference_type' => StockTransfer::class,
            'reference_id' => $referenceId,
            'reference_number' => $referenceNumber,
            'status' => 'approved',
        ]);

        // Inbound to destination location
        $inbound = self::create([
            'tenant_id' => $tenantId,
            'product_id' => $productId,
            'location_id' => $toLocationId,
            'from_location_id' => $fromLocationId,
            'to_location_id' => $toLocationId,
            'user_id' => $userId,
            'type' => 'transfer_in',
            'quantity' => abs($quantity),
            'unit_cost' => $unitCost,
            'reference_type' => StockTransfer::class,
            'reference_id' => $referenceId,
            'reference_number' => $referenceNumber,
            'status' => 'approved',
        ]);

        return [$outbound, $inbound];
    }

    /**
     * Create an adjustment movement.
     */
    public static function createAdjustment(
        int $tenantId,
        int $productId,
        int $locationId,
        float $quantity, // Positive or negative
        float $unitCost,
        string $notes,
        ?int $userId = null
    ): self {
        $type = $quantity > 0 ? 'adjustment_in' : 'adjustment_out';

        return self::create([
            'tenant_id' => $tenantId,
            'product_id' => $productId,
            'location_id' => $locationId,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'notes' => $notes,
            'status' => 'approved',
        ]);
    }

    /**
     * Create opening stock movement.
     */
    public static function createOpeningStock(
        int $tenantId,
        int $productId,
        int $locationId,
        float $quantity,
        float $unitCost,
        ?int $userId = null
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'product_id' => $productId,
            'location_id' => $locationId,
            'user_id' => $userId,
            'type' => 'opening_stock',
            'quantity' => abs($quantity),
            'unit_cost' => $unitCost,
            'notes' => 'Opening stock entry',
            'status' => 'approved',
        ]);
    }
}
