<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseBudget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'expense_category_id',
        'period_type',
        'period_start',
        'period_end',
        'budgeted_amount',
        'actual_amount',
        'variance_amount',
        'variance_percentage',
        'status',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'budgeted_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'variance_amount' => 'decimal:2',
        'variance_percentage' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::saving(function (self $budget) {
            $budget->variance_amount = $budget->actual_amount - $budget->budgeted_amount;
            if ($budget->budgeted_amount > 0) {
                $budget->variance_percentage = (($budget->actual_amount - $budget->budgeted_amount) / $budget->budgeted_amount) * 100;
            }
        });
    }

    // Relationships

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByPeriod($query, string $periodType, $startDate, $endDate)
    {
        return $query->where('period_type', $periodType)
            ->where('period_start', '>=', $startDate)
            ->where('period_end', '<=', $endDate);
    }

    // Helpers

    public function getVarianceStatusAttribute(): string
    {
        if ($this->variance_percentage > 10) {
            return 'over_budget';
        } elseif ($this->variance_percentage < -10) {
            return 'under_budget';
        }

        return 'on_track';
    }

    public function recalculateActual(): void
    {
        $this->actual_amount = Expense::query()
            ->where('tenant_id', $this->tenant_id)
            ->where('category_id', $this->expense_category_id)
            ->where('status', 'approved')
            ->whereBetween('date', [$this->period_start, $this->period_end])
            ->sum('amount');

        $this->save();
    }
}
