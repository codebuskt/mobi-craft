<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairDiagnosis extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'repair_ticket_id',
        'repair_device_id',
        'technician_id',
        'findings',
        'recommended_repair',
        'recommended_parts',
        'labor_charge',
        'parts_estimated_cost',
        'estimated_total',
        'diagnosis_date',
        'status',
    ];

    protected $casts = [
        'recommended_parts' => 'array',
        'labor_charge' => 'decimal:2',
        'parts_estimated_cost' => 'decimal:2',
        'estimated_total' => 'decimal:2',
        'diagnosis_date' => 'date',
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

    public function repairTicket(): BelongsTo
    {
        return $this->belongsTo(RepairTicket::class);
    }

    public function repairDevice(): BelongsTo
    {
        return $this->belongsTo(RepairDevice::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function approval(): BelongsTo
    {
        return $this->hasOne(RepairApproval::class, 'repair_diagnosis_id');
    }

    // Scopes

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRequiresApproval($query)
    {
        return $query->where('status', 'requires_approval');
    }
}
