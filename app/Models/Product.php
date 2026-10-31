<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'brand_id',
        'sku',
        'barcode',
        'name',
        'slug',
        'type',
        'status',
        'cost_price',
        'sale_price',
        'wholesale_price',
        'special_price',
        'tax_rate',
        'tax_inclusive',
        'stock_quantity',
        'reserved_quantity',
        'available_quantity',
        'reorder_level',
        'reorder_quantity',
        'max_stock_level',
        'unit',
        'weight',
        'dimensions',
        'is_serialized',
        'track_imei',
        'warranty_months',
        'warranty_type',
        'images',
        'thumbnail',
        'short_description',
        'description',
        'specifications',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_pos_visible',
        'is_published',
        'is_featured',
        'sort_order',
        'preferred_supplier_id',
        'lead_time_days',
        'attributes',
        'settings',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'cost_price' => 'decimal:4',
        'sale_price' => 'decimal:4',
        'wholesale_price' => 'decimal:4',
        'special_price' => 'decimal:4',
        'stock_quantity' => 'decimal:4',
        'reserved_quantity' => 'decimal:4',
        'available_quantity' => 'decimal:4',
        'reorder_level' => 'decimal:4',
        'reorder_quantity' => 'decimal:4',
        'max_stock_level' => 'decimal:4',
        'weight' => 'decimal:3',
        'is_serialized' => 'boolean',
        'track_imei' => 'boolean',
        'tax_inclusive' => 'boolean',
        'is_pos_visible' => 'boolean',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the tenant that owns the product.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the brand.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the images array properly.
     */
    public function getImagesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Get the images array setter.
     */
    public function setImagesAttribute($value)
    {
        $this->attributes['images'] = json_encode($value ?? []);
    }

    /**
     * Get the specifications array properly.
     */
    public function getSpecificationsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Set the specifications array.
     */
    public function setSpecificationsAttribute($value)
    {
        $this->attributes['specifications'] = json_encode($value ?? []);
    }

    /**
     * Get the attributes array properly.
     */
    public function getAttributesAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Set the attributes array.
     */
    public function setAttributesAttribute($value)
    {
        $this->attributes['attributes'] = json_encode($value ?? []);
    }

    /**
     * Get the settings array properly.
     */
    public function getSettingsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Set the settings array.
     */
    public function setSettingsAttribute($value)
    {
        $this->attributes['settings'] = json_encode($value ?? []);
    }

    /**
     * Get cost price formatted.
     */
    public function getCostPriceFormattedAttribute(): string
    {
        return '৳'.number_format($this->cost_price, 2);
    }

    /**
     * Get sale price formatted.
     */
    public function getSalePriceFormattedAttribute(): string
    {
        return '৳'.number_format($this->sale_price, 2);
    }

    /**
     * Get the effective sale price (considering special price if active).
     */
    public function getEffectiveSalePriceAttribute(): float
    {
        $now = now();

        if ($this->special_price && $this->special_price_start && $this->special_price_end) {
            if ($now >= $this->special_price_start && $now <= $this->special_price_end) {
                return (float) $this->special_price;
            }
        }

        return (float) $this->sale_price;
    }

    /**
     * Get the effective sale price formatted.
     */
    public function getEffectiveSalePriceFormattedAttribute(): string
    {
        return '৳'.number_format($this->getEffectiveSalePriceAttribute(), 2);
    }

    /**
     * Get available stock after reserved.
     */
    public function getAvailableQuantityAttribute(): float
    {
        return max(0, (float) $this->stock_quantity - (float) $this->reserved_quantity);
    }

    /**
     * Check if product is low on stock.
     */
    public function isLowStock(): bool
    {
        $available = $this->available_quantity;

        return $available > 0 && $available <= (float) $this->reorder_level;
    }

    /**
     * Check if product is out of stock.
     */
    public function isOutOfStock(): bool
    {
        return $this->available_quantity <= 0;
    }

    /**
     * Check if product has stock.
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->available_quantity >= $quantity;
    }

    /**
     * Check if product can be sold (active and has stock).
     */
    public function canSell(): bool
    {
        return $this->status === 'active' && $this->hasStock();
    }

    /**
     * Check if product is on special offer.
     */
    public function isOnSpecial(): bool
    {
        $now = now();

        return ! empty($this->special_price)
            && $this->special_price_start
            && $this->special_price_end
            && $now >= $this->special_price_start
            && $now <= $this->special_price_end;
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'success',
            'inactive' => 'gray',
            'discontinued' => 'danger',
            'draft' => 'warning',
            default => 'gray',
        };
    }

    /**
     * Get product type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'phone' => 'Mobile Phone',
            'accessory' => 'Accessory',
            'spare_part' => 'Spare Part',
            'service' => 'Service',
            'used_device' => 'Used Device',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get unit label.
     */
    public function getUnitLabelAttribute(): string
    {
        return match ($this->unit) {
            'pcs' => 'Piece',
            'box' => 'Box',
            'pack' => 'Pack',
            'kg' => 'Kilogram',
            'g' => 'Gram',
            'pc' => 'Piece',
            default => $this->unit ?? 'Piece',
        };
    }

    /**
     * Get the formatted price including tax.
     */
    public function getPriceWithTaxFormattedAttribute(?float $price = null): string
    {
        $price = $price ?? $this->getEffectiveSalePriceAttribute();
        $taxRate = $this->tax_inclusive ? 0 : ($this->tax_rate ?? 0);
        $taxAmount = $price * ($taxRate / 100);
        $total = $price + $taxAmount;

        return '৳'.number_format($total, 2);
    }

    /**
     * Get the formatted price without tax.
     */
    public function getPriceWithoutTaxFormattedAttribute(?float $price = null): string
    {
        $price = $price ?? $this->getEffectiveSalePriceAttribute();

        return '৳'.number_format($price, 2);
    }

    /**
     * Calculate price including tax from inclusive price.
     */
    public function getPriceExcludingTax(float $priceInclusive): float
    {
        $taxRate = $this->tax_rate ?? 0;

        if ($taxRate == 0) {
            return $priceInclusive;
        }

        return $priceInclusive / (1 + ($taxRate / 100));
    }

    /**
     * Scope for active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for POS visible products.
     */
    public function scopePosVisible($query)
    {
        return $query->where('is_pos_visible', true);
    }

    /**
     * Scope for featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for published products (storefront visible).
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
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
     * Scope for product type.
     */
    public function scopeType($query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for search.
     */
    public function scopeSearch($query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('meta_title', 'like', "%{$search}%");
        });
    }

    /**
     * Scope for active and available.
     */
    public function scopeSellable($query): Builder
    {
        return $query->where('status', 'active')
            ->where('is_pos_visible', true);
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Generate SKU on creation if not provided
        static::creating(function (self $product) {
            if (! $product->sku) {
                $tenantId = $product->tenant_id ?? Tenant::getCurrentTenant()?->id;
                if ($tenantId) {
                    // Generate: PROD-{tenant_id}-{sequence}
                    $latest = self::where('tenant_id', $tenantId)
                        ->latest('id')
                        ->first();

                    $sequence = $latest ? (int) substr($latest->sku, -6) + 1 : 1;
                    $product->sku = sprintf('PROD-%06d', $sequence);
                }
            }

            // Generate barcode if not provided but SKU exists
            if ($product->sku && ! $product->barcode) {
                $product->barcode = $product->sku;
            }

            // Set default type if not provided
            if (! $product->type) {
                $product->type = 'accessory';
            }

            // Set status if not provided
            if (! $product->status) {
                $product->status = 'draft';
            }
        });

        // Recalculate available quantity on update
        static::updated(function (self $product) {
            $product->available_quantity = $product->stock_quantity - $product->reserved_quantity;
            $product->save();
        });
    }
}
