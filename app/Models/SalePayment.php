<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalePayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'sale_id',
        'customer_id',
        'amount',
        'payment_method',
        'payment_reference',
        'notes',
        'received_by',
        'payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the tenant that owns the sale payment.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the sale this payment belongs to.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the customer this payment is from.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who received this payment.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get payment method label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'Cash',
            'card' => 'Card',
            'mobile_banking' => 'Mobile Banking',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'credit' => 'Credit / Due',
            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
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
     * Scope for today's payments.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', now()->toDateString());
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (self $payment) {
            if (! $payment->payment_date) {
                $payment->payment_date = now()->toDateString();
            }

            if (! $payment->received_by) {
                $payment->received_by = auth()->id();
            }
        });

        static::created(function (self $payment) {
            // Update sale paid amount
            if ($payment->sale) {
                $payment->sale->increment('paid_amount', $payment->amount);
            }

            // Update customer due balance
            if ($payment->customer && $payment->payment_method === 'credit') {
                $payment->customer->increment('outstanding_balance', $payment->amount);
            } elseif ($payment->customer) {
                $payment->customer->decrement('outstanding_balance', $payment->amount);
            }
        });

        static::deleted(function (self $payment) {
            // Update sale paid amount
            if ($payment->sale) {
                $payment->sale->decrement('paid_amount', $payment->amount);
            }

            // Update customer due balance
            if ($payment->customer && $payment->payment_method === 'credit') {
                $payment->customer->decrement('outstanding_balance', $payment->amount);
            } elseif ($payment->customer) {
                $payment->customer->increment('outstanding_balance', $payment->amount);
            }
        });
    }
}
