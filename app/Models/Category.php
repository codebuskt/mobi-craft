<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'sort_order',
        'is_active',
        'is_service',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_service' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the tenant that owns the category.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get products in this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
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
     * Scope for active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for root categories (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for service categories.
     */
    public function scopeServices($query)
    {
        return $query->where('is_service', true);
    }

    /**
     * Scope for product categories.
     */
    public function scopeProducts($query)
    {
        return $query->where('is_service', false);
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