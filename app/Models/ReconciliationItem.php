<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReconciliationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'reconciliation_id',
        'source_type',
        'source_id',
        'transaction_date',
        'description',
        'amount',
        'direction',
        'is_matched',
        'matched_item_id',
        'match_notes',
        'is_discrepancy',
        'discrepancy_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'is_matched' => 'boolean',
        'is_discrepancy' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    // Relationships

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(Reconciliation::class);
    }

    public function matchedItem(): BelongsTo
    {
        return $this->belongsTo(ReconciliationItem::class, 'matched_item_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes

    public function scopeMatched($query)
    {
        return $query->where('is_matched', true);
    }

    public function scopeUnmatched($query)
    {
        return $query->where('is_matched', false);
    }

    public function scopeDiscrepancies($query)
    {
        return $query->where('is_discrepancy', true);
    }

    public function scopeByDirection($query, string $direction)
    {
        return $query->where('direction', $direction);
    }

    // Helpers

    public function matchWith(ReconciliationItem $otherItem, ?string $notes = null): bool
    {
        return $this->update([
            'is_matched' => true,
            'matched_item_id' => $otherItem->id,
            'match_notes' => $notes,
        ]);
    }

    public function markDiscrepancy(string $notes): bool
    {
        return $this->update([
            'is_discrepancy' => true,
            'discrepancy_notes' => $notes,
        ]);
    }

    public function resolve(int $resolvedBy, ?string $notes = null): bool
    {
        return $this->update([
            'is_discrepancy' => false,
            'discrepancy_notes' => $notes,
            'resolved_by' => $resolvedBy,
            'resolved_at' => now(),
        ]);
    }
}
