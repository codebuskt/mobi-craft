<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

class UsedDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'brand_id',
        'purchase_order_id',
        'product_id',
        'device_number',
        'imei',
        'serial_number',
        'brand',
        'model',
        'color',
        'storage',
        'carrier',
        'purchase_source',
        'purchase_source_reference',
        'purchase_date',
        'acquisition_cost',
        'additional_costs',
        'condition_grade',
        'functional_status',
        'inspection_checklist',
        'inspection_notes',
        'defects_found',
        'intake_photos',
        'refurbishment_cost',
        'refurbishment_parts',
        'refurbishment_notes',
        'refurbishment_photos',
        'refurbishment_completed_at',
        'ownership_verified',
        'ownership_documents',
        'ownership_notes',
        'listing_status',
        'listing_price',
        'minimum_price',
        'listed_at',
        'listing_platform',
        'listing_description',
        'listing_photos',
        'sale_id',
        'sale_price',
        'sold_at',
        'gross_margin',
        'margin_percentage',
        'warranty_months',
        'warranty_type',
        'attributes',
        'settings',
        'notes',
    ];

    protected $casts = [
        'acquisition_cost' => 'decimal:4',
        'additional_costs' => 'decimal:4',
        'refurbishment_cost' => 'decimal:4',
        'listing_price' => 'decimal:4',
        'minimum_price' => 'decimal:4',
        'sale_price' => 'decimal:4',
        'gross_margin' => 'decimal:4',
        'margin_percentage' => 'decimal:2',
        'purchase_date' => 'date',
        'refurbishment_completed_at' => 'date',
        'listed_at' => 'date',
        'sold_at' => 'date',
        'inspection_checklist' => 'array',
        'defects_found' => 'array',
        'intake_photos' => 'array',
        'refurbishment_parts' => 'array',
        'refurbishment_photos' => 'array',
        'ownership_documents' => 'array',
        'listing_photos' => 'array',
        'attributes' => 'array',
        'settings' => 'array',
        'ownership_verified' => 'boolean',
        'warranty_months' => 'integer',
    ];

    /**
     * The attributes that should be encrypted.
     */
    protected $encryptedAttributes = ['imei', 'serial_number'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (self $device) {
            if (empty($device->device_number)) {
                $device->device_number = static::generateDeviceNumber();
            }

            // Encrypt sensitive identifiers
            if ($device->imei) {
                $device->imei = Crypt::encryptString($device->imei);
            }
            if ($device->serial_number) {
                $device->serial_number = Crypt::encryptString($device->serial_number);
            }
        });

        static::updating(function (self $device) {
            // Recalculate margin if sale price is set
            if ($device->isDirty('sale_price') && $device->sale_price) {
                $device->recalculateMargin();
            }

            // Encrypt sensitive identifiers if changed
            if ($device->isDirty('imei') && $device->imei) {
                $device->imei = Crypt::encryptString($device->imei);
            }
            if ($device->isDirty('serial_number') && $device->serial_number) {
                $device->serial_number = Crypt::encryptString($device->serial_number);
            }
        });
    }

    /**
     * Generate unique device number: UD-{YEAR}-{SEQUENCE}
     */
    public static function generateDeviceNumber(): string
    {
        $year = now()->year;
        $tenant = Tenant::getCurrentTenant();
        $tenantId = $tenant?->id;

        $lastDevice = static::withoutGlobalScope(TenantScope::class)
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('device_number', 'like', "UD-{$year}-%")
            ->orderBy('device_number', 'desc')
            ->first();

        $sequence = 1;
        if ($lastDevice) {
            $parts = explode('-', $lastDevice->device_number);
            $sequence = (int) ($parts[2] ?? 0) + 1;
        }

        return sprintf('UD-%d-%05d', $year, $sequence);
    }

    /**
     * Get decrypted IMEI.
     */
    public function getImeiAttribute($value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Get decrypted Serial Number.
     */
    public function getSerialNumberAttribute($value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Set encrypted IMEI.
     */
    public function setImeiAttribute(?string $value): void
    {
        $this->attributes['imei'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Set encrypted Serial Number.
     */
    public function setSerialNumberAttribute(?string $value): void
    {
        $this->attributes['serial_number'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Recalculate gross margin and margin percentage.
     */
    public function recalculateMargin(): void
    {
        $totalCost = $this->acquisition_cost + $this->additional_costs + $this->refurbishment_cost;
        $this->gross_margin = $this->sale_price - $totalCost;

        if ($totalCost > 0) {
            $this->margin_percentage = round(($this->gross_margin / $totalCost) * 100, 2);
        } else {
            $this->margin_percentage = 0;
        }
    }

    /**
     * Get total cost (acquisition + additional + refurbishment).
     */
    public function getTotalCostAttribute(): float
    {
        return round((float) $this->acquisition_cost + (float) $this->additional_costs + (float) $this->refurbishment_cost, 4);
    }

    /**
     * Get gross profit (sale price - total cost).
     */
    public function getGrossProfitAttribute(): ?float
    {
        if (! $this->sale_price) {
            return null;
        }

        return round((float) $this->sale_price - $this->total_cost, 4);
    }

    /**
     * Get margin percentage.
     */
    public function getMarginPercentageAttribute(): ?float
    {
        if (! $this->sale_price || $this->total_cost <= 0) {
            return null;
        }

        return round((($this->sale_price - $this->total_cost) / $this->total_cost) * 100, 2);
    }

    /**
     * Check if device is available for sale.
     */
    public function isAvailableForSale(): bool
    {
        return in_array($this->listing_status, ['ready_for_sale', 'listed']) && ! $this->sold_at;
    }

    /**
     * Check if device is sold.
     */
    public function isSold(): bool
    {
        return $this->listing_status === 'sold' || $this->sold_at !== null;
    }

    /**
     * Mark device as sold.
     */
    public function markAsSold(float $salePrice, ?string $saleId = null): bool
    {
        $this->listing_status = 'sold';
        $this->sale_price = $salePrice;
        $this->sold_at = now()->toDateString();
        $this->sale_id = $saleId;
        $this->recalculateMargin();

        return $this->save();
    }

    /**
     * Get display name.
     */
    public function getDisplayNameAttribute(): string
    {
        $parts = [];
        if ($this->brand) {
            $parts[] = $this->brand;
        }
        if ($this->model) {
            $parts[] = $this->model;
        }
        if ($this->storage) {
            $parts[] = $this->storage;
        }
        if ($this->color) {
            $parts[] = $this->color;
        }

        return implode(' ', $parts) ?: 'Unknown Device';
    }

    /**
     * Get identifier (IMEI or Serial).
     */
    public function getIdentifierAttribute(): string
    {
        return $this->imei ?? $this->serial_number ?? 'N/A';
    }

    /**
     * Get condition grade label.
     */
    public function getConditionGradeLabelAttribute(): string
    {
        return match ($this->condition_grade) {
            'a_plus' => 'A+',
            'a' => 'A',
            'b_plus' => 'B+',
            'b' => 'B',
            'c_plus' => 'C+',
            'c' => 'C',
            'd' => 'D',
            default => ucfirst($this->condition_grade ?? ''),
        };
    }

    /**
     * Get condition grade color.
     */
    public function getConditionGradeColorAttribute(): string
    {
        return match ($this->condition_grade) {
            'a_plus', 'a' => 'success',
            'b_plus', 'b' => 'info',
            'c_plus', 'c' => 'warning',
            'd' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Get listing status label.
     */
    public function getListingStatusLabelAttribute(): string
    {
        return match ($this->listing_status) {
            'acquired' => 'Acquired',
            'inspecting' => 'Inspecting',
            'refurbishing' => 'Refurbishing',
            'ready_for_sale' => 'Ready for Sale',
            'listed' => 'Listed',
            'reserved' => 'Reserved',
            'sold' => 'Sold',
            'returned' => 'Returned',
            'written_off' => 'Written Off',
            default => ucfirst(str_replace('_', ' ', $this->listing_status)),
        };
    }

    /**
     * Get listing status color.
     */
    public function getListingStatusColorAttribute(): string
    {
        return match ($this->listing_status) {
            'acquired' => 'gray',
            'inspecting' => 'info',
            'refurbishing' => 'warning',
            'ready_for_sale' => 'success',
            'listed' => 'primary',
            'reserved' => 'orange',
            'sold' => 'success',
            'returned' => 'danger',
            'written_off' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get purchase source label.
     */
    public function getPurchaseSourceLabelAttribute(): string
    {
        return match ($this->purchase_source) {
            'customer' => 'Customer',
            'supplier' => 'Supplier',
            'trade_in' => 'Trade-in',
            'auction' => 'Auction',
            'other' => 'Other',
            default => ucfirst($this->purchase_source),
        };
    }

    /**
     * Get functional status label.
     */
    public function getFunctionalStatusLabelAttribute(): string
    {
        return match ($this->functional_status) {
            'fully_functional' => 'Fully Functional',
            'minor_issues' => 'Minor Issues',
            'major_issues' => 'Major Issues',
            'parts_only' => 'Parts Only',
            default => ucfirst(str_replace('_', ' ', $this->functional_status)),
        };
    }

    // Relationships

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    // Scopes

    public function scopeByStatus($query, string $status): Builder
    {
        return $query->where('listing_status', $status);
    }

    public function scopeByCondition($query, string $condition): Builder
    {
        return $query->where('condition_grade', $condition);
    }

    public function scopeAvailable($query): Builder
    {
        return $query->whereIn('listing_status', ['ready_for_sale', 'listed'])
            ->whereNull('sold_at');
    }

    public function scopeSold($query): Builder
    {
        return $query->where('listing_status', 'sold')
            ->orWhereNotNull('sold_at');
    }

    public function scopeByPurchaseSource($query, string $source): Builder
    {
        return $query->where('purchase_source', $source);
    }

    public function scopeCurrentTenant($query): Builder
    {
        $tenant = Tenant::getCurrentTenant();

        if ($tenant) {
            return $query->where('tenant_id', $tenant->id);
        }

        return $query->whereNull('tenant_id');
    }

    public function scopeSearch($query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('device_number', 'like', "%{$search}%")
                ->orWhere('brand', 'like', "%{$search}%")
                ->orWhere('model', 'like', "%{$search}%")
                ->orWhere('imei', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%")
                ->orWhere('purchase_source_reference', 'like', "%{$search}%");
        });
    }

    public function scopeProfitable($query): Builder
    {
        return $query->where('gross_margin', '>', 0);
    }

    public function scopeLossMaking($query): Builder
    {
        return $query->where('gross_margin', '<', 0);
    }
}