<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryStructure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'staff_profile_id',
        'basic_salary',
        'house_allowance',
        'medical_allowance',
        'transport_allowance',
        'other_allowances',
        'provident_fund',
        'tax_deduction',
        'advance_deduction',
        'other_deductions',
        'overtime_rate_per_hour',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'house_allowance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'provident_fund' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'overtime_rate_per_hour' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
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

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEffective($query)
    {
        return $query->where('effective_from', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now()->toDateString());
            });
    }

    // Helpers

    public function getGrossSalaryAttribute(): float
    {
        return $this->basic_salary
            + $this->house_allowance
            + $this->medical_allowance
            + $this->transport_allowance
            + $this->other_allowances;
    }

    public function getTotalDeductionsAttribute(): float
    {
        return $this->provident_fund
            + $this->tax_deduction
            + $this->advance_deduction
            + $this->other_deductions;
    }

    public function getNetSalaryAttribute(): float
    {
        return $this->gross_salary - $this->total_deductions;
    }
}
