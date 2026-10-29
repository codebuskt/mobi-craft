<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollPeriod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'period_start',
        'period_end',
        'cutoff_date',
        'pay_date',
        'status',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'cutoff_date' => 'date',
        'pay_date' => 'date',
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

    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }

    // Scopes

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCurrent($query)
    {
        return $query->where('period_start', '<=', now()->toDateString())
            ->where('period_end', '>=', now()->toDateString());
    }

    // Helpers

    public function canCalculate(): bool
    {
        return $this->status === 'draft';
    }

    public function canApprove(): bool
    {
        return $this->status === 'processing';
    }

    public function canDisburse(): bool
    {
        return $this->status === 'approved';
    }

    public function markProcessing(): void
    {
        $this->update(['status' => 'processing']);
    }

    public function markApproved(): void
    {
        $this->update(['status' => 'approved']);
    }

    public function markDisbursed(): void
    {
        $this->update(['status' => 'disbursed']);
    }

    public function markClosed(): void
    {
        $this->update(['status' => 'closed']);
    }
}
