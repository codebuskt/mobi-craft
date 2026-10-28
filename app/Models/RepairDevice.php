<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'repair_ticket_id',
        'product_id',
        'brand',
        'model',
        'imei',
        'serial_number',
        'color',
        'condition',
        'reported_issue',
        'diagnosis_notes',
        'repair_notes',
        'quality_check_notes',
        'accessories_received',
        'intake_photos',
        'diagnosis_photos',
        'repair_photos',
        'delivery_photos',
        'device_status',
    ];

    protected $casts = [
        'accessories_received' => 'array',
        'intake_photos' => 'array',
        'diagnosis_photos' => 'array',
        'repair_photos' => 'array',
        'delivery_photos' => 'array',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function accessories(): HasMany
    {
        return $this->hasMany(RepairAccessory::class);
    }

    public function diagnoses(): HasMany
    {
        return $this->hasMany(RepairDiagnosis::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(RepairApproval::class);
    }

    public function repairParts(): HasMany
    {
        return $this->hasMany(RepairPart::class);
    }

    public function qualityChecks(): HasMany
    {
        return $this->hasMany(QualityCheck::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(RepairDelivery::class);
    }

    // Scopes

    public function scopeByStatus($query, string $status)
    {
        return $query->where('device_status', $status);
    }

    // Helpers

    public function getDisplayNameAttribute(): string
    {
        $parts = [];
        if ($this->brand) {
            $parts[] = $this->brand;
        }
        if ($this->model) {
            $parts[] = $this->model;
        }
        if ($this->color) {
            $parts[] = $this->color;
        }

        return implode(' ', $parts) ?: 'Unknown Device';
    }

    public function getIdentifierAttribute(): string
    {
        return $this->imei ?? $this->serial_number ?? 'N/A';
    }
}
