<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_id',
        'joining_date',
        'department',
        'designation',
        'employment_type',
        'status',
        'bank_name',
        'bank_account_number',
        'bank_branch',
        'bank_routing_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'date_of_birth',
        'national_id',
        'address',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'date_of_birth' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (self $staff) {
            if (empty($staff->employee_id)) {
                $staff->employee_id = static::generateEmployeeId($staff->tenant_id);
            }
        });
    }

    /**
     * Generate unique employee ID: EMP-{SEQUENCE}
     */
    public static function generateEmployeeId(int $tenantId): string
    {
        $lastStaff = static::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenantId)
            ->orderBy('employee_id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastStaff && preg_match('/EMP-(\d+)/', $lastStaff->employee_id, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('EMP-%05d', $sequence);
    }

    // Relationships

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salaryStructures(): HasMany
    {
        return $this->hasMany(SalaryStructure::class);
    }

    public function currentSalaryStructure(): HasMany
    {
        return $this->hasMany(SalaryStructure::class)
            ->where('is_active', true)
            ->where('effective_from', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now()->toDateString());
            })
            ->latest('effective_from');
    }

    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    public function scopeByDesignation($query, string $designation)
    {
        return $query->where('designation', $designation);
    }

    // Helpers

    public function getActiveSalaryStructure(): ?SalaryStructure
    {
        return $this->currentSalaryStructure()->first();
    }

    public function getFullNameAttribute(): string
    {
        return $this->user?->name ?? 'Unknown';
    }

    public function getGrossSalaryAttribute(): float
    {
        $structure = $this->getActiveSalaryStructure();
        if (! $structure) {
            return 0;
        }

        return $structure->basic_salary
            + $structure->house_allowance
            + $structure->medical_allowance
            + $structure->transport_allowance
            + $structure->other_allowances;
    }
}
