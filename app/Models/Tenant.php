<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    protected static ?self $currentTenant = null;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'logo',
        'settings',
        'status',
        'trial_ends_at',
        'subscription_ends_at',
        'plan_id',
    ];

    protected $casts = [
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    protected $dates = ['trial_ends_at', 'subscription_ends_at'];

    /**
     * Get the users for the tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    /**
     * Get the subscription plan.
     */
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * Get the shop profile.
     */
    public function shopProfile()
    {
        return $this->hasOne(ShopProfile::class);
    }

    /**
     * Check if tenant is active.
     */
    public function getIsActiveAttribute()
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->trial_ends_at && now()->gt($this->trial_ends_at) && ! $this->subscription_ends_at) {
            return false;
        }

        if ($this->subscription_ends_at && now()->gt($this->subscription_ends_at)) {
            return false;
        }

        return true;
    }

    /**
     * Check if tenant is on trial.
     */
    public function getIsTrialAttribute()
    {
        return $this->status === 'trial';
    }

    /**
     * Check if tenant is suspended.
     */
    public function getIsSuspendedAttribute()
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if tenant is expired.
     */
    public function getIsExpiredAttribute()
    {
        return $this->status === 'expired';
    }

    /**
     * Get the domain for tenant identification.
     */
    public function getIdentificationDomain()
    {
        return $this->domain ?: $this->slug.'.test';
    }

    /**
     * Scope query to current tenant only.
     */
    public function scopeCurrentTenant($query)
    {
        $tenant = $this->getCurrentTenant();

        return $tenant ? $query->where('tenant_id', $tenant->id) : $query->whereNull('tenant_id');
    }

    /**
     * Get the current tenant (from middleware context).
     */
    public static function getCurrentTenant()
    {
        return static::$currentTenant;
    }

    /**
     * Set the current tenant (for middleware).
     */
    public static function setCurrentTenant(?self $tenant)
    {
        static::$currentTenant = $tenant;
    }

    /**
     * Determine if user can access a tenant's record.
     */
    public function canAccessRecord($user, $record)
    {
        // If user has no tenant, they cannot access tenant-specific records
        if (! $user->tenant_id) {
            return false;
        }

        // Users can always access their own tenant's records
        return $user->tenant_id === $this->id;
    }
}
