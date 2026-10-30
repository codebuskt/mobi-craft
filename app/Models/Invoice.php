<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'subscription_plan_id',
        'invoice_number',
        'status',
        'subtotal',
        'tax_amount',
        'total_amount',
        'amount_due',
        'period_start',
        'period_end',
        'due_date',
        'paid_at',
        'payment_amount',
        'payment_method',
        'payment_reference',
        'notes',
        'terms_conditions',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'paid_at' => 'date',
        'status' => 'string',
    ];

    /**
     * The attributes that should be casts to dates.
     *
     * @var array
     */
    protected $dates = [
        'period_start',
        'period_end',
        'due_date',
        'paid_at',
    ];

    /**
     * Get the tenant that owns the invoice.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the subscription plan for the invoice.
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * Get the user who created the invoice.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope query to only include invoices for a tenant.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope query to only include invoices with a specific status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope query to only include unpaid invoices.
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['draft', 'pending', 'overdue']);
    }

    /**
     * Scope query to only include paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope query to only include overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->where('due_date', '<', now()->toDateString());
    }

    /**
     * Get whether the invoice is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'overdue' && $this->due_date && now()->gt($this->due_date);
    }

    /**
     * Get whether the invoice is paid.
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Generate a unique invoice number.
     */
    public static function generateInvoiceNumber($tenantId): string
    {
        $year = now()->year;
        $latest = static::where('tenant_id', $tenantId)
            ->where('invoice_number', 'like', "INV-{$year}-%")
            ->latest('id')
            ->first();

        $sequence = $latest ? (int) substr($latest->invoice_number, -6) + 1 : 1;

        return sprintf("INV-{$year}-%06d", $sequence);
    }

    /**
     * Recalculate amount due and totals.
     */
    public function recalculateTotals(): void
    {
        $this->subtotal = 0; // Will be set by caller (e.g., plan price)
        $this->tax_amount = round($this->subtotal * 0.15, 2); // 15% VAT by default
        $this->total_amount = $this->subtotal + $this->tax_amount;
        $this->amount_due = $this->total_amount;
        $this->saveQuietly();
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid($paymentAmount, $paymentMethod = null, $paymentReference = null): void
    {
        $this->status = 'paid';
        $this->paid_at = now()->toDateString();
        $this->payment_amount = $paymentAmount;
        $this->payment_method = $paymentMethod;
        $this->payment_reference = $paymentReference;

        // If payment exceeds amount due, handle overpayment
        if ($paymentAmount > $this->total_amount) {
            // Store overpayment info or apply to next cycle
        }

        $this->saveQuietly();
    }

    /**
     * Mark invoice as void.
     */
    public function markAsVoid(): void
    {
        $this->status = 'void';
        $this->saveQuietly();
    }
}