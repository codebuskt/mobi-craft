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
     * Check if tenant is on trial.
     */
    public function getIsTrialAttribute()
    {
        return $this->status === 'trial';
    }

    /**
     * Check if tenant is on trial period.
     */
    public function onTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && now()->lt($this->trial_ends_at);
    }

    /**
     * Get days remaining in trial period.
     */
    public function trialDaysRemaining(): ?int
    {
        if (! $this->onTrial()) {
            return null;
        }

        return $this->trial_ends_at->diffInDays(now());
    }

    /**
     * Check if tenant's subscription is active and paid up.
     */
    public function isPaidUp(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        // If no subscription end date, tenant is on trial only
        if (! $this->subscription_ends_at) {
            return $this->onTrial();
        }

        return now()->lt($this->subscription_ends_at);
    }

    /**
     * Get days remaining in subscription.
     */
    public function subscriptionDaysRemaining(): ?int
    {
        if (! $this->subscription_ends_at) {
            return $this->trialDaysRemaining();
        }

        return $this->subscription_ends_at->diffInDays(now());
    }

    /**
     * Get the current subscription plan.
     */
    public function currentPlan(): ?SubscriptionPlan
    {
        return $this->plan;
    }

    /**
     * Get whether the tenant can create more customers.
     */
    public function canCreateCustomers(): bool
    {
        $plan = $this->currentPlan();

        if (! $plan) {
            return true; // No plan limits
        }

        $limit = $plan->getLimit('customers');
        $currentCount = $this->users()->count();

        return $currentCount < $limit;
    }

    /**
     * Get whether the tenant can create more products.
     */
    public function canCreateProducts(): bool
    {
        $plan = $this->currentPlan();

        if (! $plan) {
            return true;
        }

        $limit = $plan->getLimit('products');
        $currentCount = $this->id ? // Need to count products - check Product model
            resolve('App\Models\Product')->where('tenant_id', $this->id)->count() : 0;

        return $currentCount < $limit;
    }

    /**
     * Get whether the tenant can create more repair tickets.
     */
    public function canCreateRepairs(): bool
    {
        $plan = $this->currentPlan();

        if (! $plan) {
            return true;
        }

        $limit = $plan->getLimit('repairs_per_month');

        // Check monthly repair count
        if ($limit >= 0) {
            $currentCount = resolve('App\Models\RepairTicket')
                ->where('tenant_id', $this->id)
                ->whereMonth('created_at', now()->month)
                ->count();

            return $currentCount < $limit;
        }

        return true; // Unlimited
    }

    /**
     * Auto-transition tenant from trial to active plan.
     */
    public function autoTransitionFromTrial(): bool
    {
        if (! $this->onTrial()) {
            return false;
        }

        if ($this->trialDaysRemaining() <= 0) {
            // Trial ended - transition to active
            $this->status = 'active';
            $this->trial_ends_at = null;

            // If a plan is assigned, set subscription end date
            if ($this->plan_id) {
                $this->subscription_ends_at = $this->calculateSubscriptionEndDate();
            }

            $this->saveQuietly();

            // Generate first invoice for the plan
            if ($this->plan_id) {
                $this->generateFirstInvoice();
            }

            return true;
        }

        return false;
    }

    /**
     * Calculate subscription end date based on plan.
     */
    protected function calculateSubscriptionEndDate(): ?Carbon
    {
        $plan = $this->plan;

        if (! $plan) {
            return null;
        }

        if ($plan->trial_days > 0) {
            return $plan->trial_days > 0
                ? now()->addDays($plan->trial_days)
                : null;
        }

        // Use yearly or monthly price to determine cycle
        if ($plan->yearly_price > 0) {
            return now()->addYear();
        }

        if ($plan->monthly_price > 0) {
            return now()->addMonth();
        }

        return null;
    }

    /**
     * Generate first invoice for the tenant's plan.
     */
    protected function generateFirstInvoice(): ?\App\Models\Invoice
    {
        $plan = $this->plan;

        if (! $plan) {
            return null;
        }

        $invoiceNumber = \App\Models\Invoice::generateInvoiceNumber($this->id);

        $subtotal = $plan->yearly_price > 0 ? $plan->yearly_price : $plan->monthly_price;
        $taxRate = 0.15; // 15% VAT
        $taxAmount = round($subtotal * $taxRate, 2);
        $totalAmount = $subtotal + $taxAmount;

        $dueDate = now()->addMonth(); // Due next month

        $invoice = \App\Models\Invoice::create([
            'tenant_id' => $this->id,
            'subscription_plan_id' => $plan->id,
            'invoice_number' => $invoiceNumber,
            'status' => 'pending',
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'amount_due' => $totalAmount,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'due_date' => $dueDate,
            'created_by' => null, // Will be set by admin
        ]);

        return $invoice;
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
