<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerContact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'name',
        'designation',
        'department',
        'phone',
        'mobile',
        'email',
        'is_primary',
        'receive_invoices',
        'receive_repair_updates',
        'receive_marketing',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'receive_invoices' => 'boolean',
        'receive_repair_updates' => 'boolean',
        'receive_marketing' => 'boolean',
    ];

    /**
     * Get the tenant that owns the contact.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the customer that owns the contact.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Ensure only one primary contact per customer
        static::saving(function (self $contact) {
            if ($contact->is_primary) {
                self::where('customer_id', $contact->customer_id)
                    ->where('id', '!=', $contact->id)
                    ->update(['is_primary' => false]);
            }
        });
    }
}
