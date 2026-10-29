<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'payroll_record_id',
        'name',
        'type',
        'amount',
        'is_taxable',
        'is_recurring',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_recurring' => 'boolean',
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

    public function payrollRecord(): BelongsTo
    {
        return $this->belongsTo(PayrollRecord::class);
    }

    // Scopes

    public function scopeEarnings($query)
    {
        return $query->where('type', 'earning');
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', 'deduction');
    }

    public function scopeTaxable($query)
    {
        return $query->where('is_taxable', true);
    }
}
