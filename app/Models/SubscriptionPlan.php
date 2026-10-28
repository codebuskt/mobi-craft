<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'yearly_price',
        'trial_days',
        'features',
        'limits',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'features' => 'array',
        'limits' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenants on this plan.
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'plan_id');
    }

    /**
     * Check if plan has a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Get limit for a specific resource.
     */
    public function getLimit(string $resource): int
    {
        return $this->limits[$resource] ?? 0;
    }

    /**
     * Get active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get plans ordered by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}