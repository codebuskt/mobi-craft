<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerDue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'sale_id',
        'amount',
        'due_date',
        'status',
        'notes',
        'paid_amount',
        'balance_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    /**
     * Get the tenant that owns the customer due.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the customer this due belongs to.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the sale this due is from.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'partial' => 'info',
            'paid' => 'success',
            'overdue' => 'danger',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Check if due is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date < now()->toDateString();
    }

    /**
     * Get aging bucket.
     */
    public function getAgingBucketAttribute(): string
    {
        if ($this->status === 'paid') {
            return 'Paid';
        }

        $daysOverdue = now()->diffInDays($this->due_date, false);

        if ($daysOverdue <= 0) {
            return 'Current';
        } elseif ($daysOverdue <= 30) {
            return '1-30 Days';
        } elseif ($daysOverdue <= 60) {
            return '31-60 Days';
        } elseif ($daysOverdue <= 90) {
            return '61-90 Days';
        } else {
            return '90+ Days';
        }
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
     * Scope for overdue dues.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('due_date', '<', now()->toDateString());
    }

    /**
     * Scope for pending dues.
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'partial']);
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (self $due) {
            if (! $due->status) {
                $due->status = 'pending';
            }

            if (! $due->paid_amount) {
                $due->paid_amount = 0;
            }

            if (! $due->balance_amount) {
                $due->balance_amount = $due->amount;
            }
        });

        static::updated(function (self $due) {
            // Auto-update status based on payment
            if ($due->wasChanged('paid_amount') || $due->wasChanged('amount')) {
                if ($due->paid_amount >= $due->amount) {
                    $due->status = 'paid';
                    $due->balance_amount = 0;
                } elseif ($due->paid_amount > 0) {
                    $due->status = 'partial';
                    $due->balance_amount = $due->amount - $due->paid_amount;
                } else {
                    $due->status = $due->isOverdue() ? 'overdue' : 'pending';
                    $due->balance_amount = $due->amount;
                }

                $due->saveQuietly();
            }
        });
    }
}
