<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'payroll_period_id',
        'staff_profile_id',
        'salary_structure_id',
        'basic_salary',
        'house_allowance',
        'medical_allowance',
        'transport_allowance',
        'overtime_pay',
        'other_allowances',
        'gross_salary',
        'provident_fund',
        'tax_deduction',
        'advance_deduction',
        'other_deductions',
        'total_deductions',
        'net_salary',
        'working_days',
        'present_days',
        'absent_days',
        'leave_days',
        'overtime_hours',
        'status',
        'approved_at',
        'approved_by',
        'paid_at',
        'paid_by',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'house_allowance' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'provident_fund' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
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

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function staffProfile(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class);
    }

    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollItem::class);
    }

    // Scopes

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPeriod($query, int $periodId)
    {
        return $query->where('payroll_period_id', $periodId);
    }

    // Helpers

    public function calculateFromStructure(): void
    {
        $structure = $this->salaryStructure;
        if (! $structure) {
            return;
        }

        $this->basic_salary = $structure->basic_salary;
        $this->house_allowance = $structure->house_allowance;
        $this->medical_allowance = $structure->medical_allowance;
        $this->transport_allowance = $structure->transport_allowance;
        $this->other_allowances = $structure->other_allowances;
        $this->provident_fund = $structure->provident_fund;
        $this->tax_deduction = $structure->tax_deduction;
        $this->advance_deduction = $structure->advance_deduction;
        $this->other_deductions = $structure->other_deductions;

        $this->gross_salary = $this->basic_salary
            + $this->house_allowance
            + $this->medical_allowance
            + $this->transport_allowance
            + $this->overtime_pay
            + $this->other_allowances;

        $this->total_deductions = $this->provident_fund
            + $this->tax_deduction
            + $this->advance_deduction
            + $this->other_deductions;

        $this->net_salary = $this->gross_salary - $this->total_deductions;
    }

    public function generateItems(): void
    {
        $this->items()->delete();

        $items = [
            ['name' => 'Basic Salary', 'type' => 'earning', 'amount' => $this->basic_salary],
            ['name' => 'House Allowance', 'type' => 'earning', 'amount' => $this->house_allowance],
            ['name' => 'Medical Allowance', 'type' => 'earning', 'amount' => $this->medical_allowance],
            ['name' => 'Transport Allowance', 'type' => 'earning', 'amount' => $this->transport_allowance],
            ['name' => 'Overtime Pay', 'type' => 'earning', 'amount' => $this->overtime_pay, 'is_recurring' => false],
            ['name' => 'Other Allowances', 'type' => 'earning', 'amount' => $this->other_allowances],
            ['name' => 'Provident Fund', 'type' => 'deduction', 'amount' => $this->provident_fund, 'is_taxable' => false],
            ['name' => 'Tax Deduction', 'type' => 'deduction', 'amount' => $this->tax_deduction, 'is_taxable' => false],
            ['name' => 'Advance Deduction', 'type' => 'deduction', 'amount' => $this->advance_deduction, 'is_taxable' => false, 'is_recurring' => false],
            ['name' => 'Other Deductions', 'type' => 'deduction', 'amount' => $this->other_deductions],
        ];

        foreach ($items as $index => $item) {
            $this->items()->create(array_merge($item, [
                'tenant_id' => $this->tenant_id,
                'sort_order' => $index,
                'is_taxable' => $item['is_taxable'] ?? true,
                'is_recurring' => $item['is_recurring'] ?? true,
            ]));
        }
    }

    public function approve(int $approvedBy): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy,
        ]);
    }

    public function markPaid(int $paidBy, string $paymentMethod, ?string $reference = null): bool
    {
        return $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'paid_by' => $paidBy,
            'payment_method' => $paymentMethod,
            'payment_reference' => $reference,
        ]);
    }
}
