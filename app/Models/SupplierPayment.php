<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'purchase_receipt_id',
        'amount',
        'payment_method',
        'payment_reference',
        'notes',
        'paid_by',
        'payment_date',
        'allocated_amount',
        'tds_amount',
        'vat_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::created(function (self $payment) {
            $payment->supplier?->decrement('outstanding_balance', $payment->allocated_amount);
            $payment->purchaseReceipt?->increment('paid_amount', $payment->allocated_amount);
        });

        static::deleted(function (self $payment) {
            $payment->supplier?->increment('outstanding_balance', $payment->allocated_amount);
            $payment->purchaseReceipt?->decrement('paid_amount', $payment->allocated_amount);
        });
    }

    // Relationships

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseReceipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // Scopes

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function scopeByMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }
}
