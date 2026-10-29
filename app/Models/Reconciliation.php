<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reconciliation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'type',
        'reference_number',
        'reconciliation_date',
        'statement_date',
        'account_name',
        'account_number',
        'inventory_location_id',
        'opening_balance',
        'closing_balance',
        'system_balance',
        'difference',
        'status',
        'notes',
        'prepared_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'reconciliation_date' => 'date',
        'statement_date' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'system_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (self $reconciliation) {
            if (empty($reconciliation->reference_number)) {
                $reconciliation->reference_number = static::generateReferenceNumber($reconciliation->tenant_id, $reconciliation->type);
            }
        });
    }

    public static function generateReferenceNumber(int $tenantId, string $type): string
    {
        $prefix = match ($type) {
            'bank' => 'BANK-REC',
            'cash' => 'CASH-REC',
            'stock' => 'STOCK-REC',
            default => 'REC',
        };

        $year = now()->year;
        $lastRec = static::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenantId)
            ->where('reference_number', 'like', "{$prefix}-{$year}-%")
            ->orderBy('reference_number', 'desc')
            ->first();

        $sequence = 1;
        if ($lastRec) {
            $parts = explode('-', $lastRec->reference_number);
            $sequence = (int) ($parts[2] ?? 0) + 1;
        }

        return sprintf('%s-%d-%04d', $prefix, $year, $sequence);
    }

    // Relationships

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inventoryLocation(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class);
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReconciliationItem::class);
    }

    // Scopes

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('reconciliation_date', [$startDate, $endDate]);
    }

    // Helpers

    public function getIsBalancedAttribute(): bool
    {
        return $this->difference == 0;
    }

    public function getDiscrepancyCountAttribute(): int
    {
        return $this->items()->where('is_discrepancy', true)->count();
    }

    public function getMatchedCountAttribute(): int
    {
        return $this->items()->where('is_matched', true)->count();
    }

    public function getTotalItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    public function calculateSystemBalance(): void
    {
        $total = $this->items()->sum('amount');
        $this->update(['system_balance' => $total, 'difference' => $total - $this->closing_balance]);
    }

    public function start(): bool
    {
        return $this->update(['status' => 'in_progress']);
    }

    public function complete(int $approvedBy): bool
    {
        return $this->update([
            'status' => 'completed',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    public function markDiscrepancy(): bool
    {
        return $this->update(['status' => 'discrepancy']);
    }
}
