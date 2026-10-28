<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairPart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'repair_ticket_id',
        'repair_device_id',
        'product_id',
        'stock_movement_id',
        'quantity',
        'unit_price',
        'total_price',
        'source',
        'notes',
        'is_installed',
        'installed_at',
        'installed_by',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'installed_at' => 'datetime',
        'is_installed' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (self $part) {
            $part->total_price = $part->quantity * $part->unit_price;
        });

        static::updating(function (self $part) {
            if ($part->isDirty(['quantity', 'unit_price'])) {
                $part->total_price = $part->quantity * $part->unit_price;
            }
        });

        static::updated(function (self $part) {
            $part->repairTicket?->recalculateTotals();
        });

        static::deleted(function (self $part) {
            $part->repairTicket?->recalculateTotals();
        });
    }

    // Relationships

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function repairTicket(): BelongsTo
    {
        return $this->belongsTo(RepairTicket::class);
    }

    public function repairDevice(): BelongsTo
    {
        return $this->belongsTo(RepairDevice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function installer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'installed_by');
    }

    // Scopes

    public function scopeFromInventory($query)
    {
        return $query->where('source', 'inventory');
    }

    public function scopeSpecialOrder($query)
    {
        return $query->where('source', 'special_order');
    }

    public function scopeInstalled($query)
    {
        return $query->where('is_installed', true);
    }

    public function scopeNotInstalled($query)
    {
        return $query->where('is_installed', false);
    }

    // Helpers

    public function markAsInstalled(int $installedBy): bool
    {
        return $this->update([
            'is_installed' => true,
            'installed_at' => now(),
            'installed_by' => $installedBy,
        ]);
    }
}
