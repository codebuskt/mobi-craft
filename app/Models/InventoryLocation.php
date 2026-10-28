<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryLocation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'name',
        'code',
        'slug',
        'description',
        'type',
        'manager_id',
        'address',
        'is_active',
        'is_default',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
        'settings' => 'array',
    ];

    /**
     * Get the tenant that owns the location.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the parent location.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'parent_id');
    }

    /**
     * Get child locations.
     */
    public function children(): HasMany
    {
        return $this->hasMany(InventoryLocation::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get the manager user.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get stock movements for this location.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'location_id');
    }

    /**
     * Get stock movements from this location (transfers out).
     */
    public function outgoingMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'from_location_id');
    }

    /**
     * Get stock movements to this location (transfers in).
     */
    public function incomingMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'to_location_id');
    }

    /**
     * Get the full path (breadcrumbs).
     */
    public function getPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'warehouse' => 'Warehouse',
            'shop_floor' => 'Shop Floor',
            'repair_bay' => 'Repair Bay',
            'showroom' => 'Showroom',
            'storage' => 'Storage',
            'transit' => 'In Transit',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Get total stock value at this location.
     */
    public function getTotalStockValueAttribute(): float
    {
        return $this->stockMovements()
            ->where('type', 'not in', ['transfer_out', 'adjustment_out', 'damage', 'loss'])
            ->sum('total_cost');
    }

    /**
     * Scope for active locations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default location.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
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