<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualityCheck extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'repair_ticket_id',
        'repair_device_id',
        'checked_by',
        'checklist',
        'overall_result',
        'notes',
        'checked_date',
        'photos',
        'is_recheck',
        'original_check_id',
    ];

    protected $casts = [
        'checklist' => 'array',
        'checked_date' => 'date',
        'photos' => 'array',
        'is_recheck' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::created(function (self $check) {
            $ticket = $check->repairTicket;
            $device = $check->repairDevice;

            if ($check->overall_result === 'pass') {
                $ticket?->transitionTo('ready_delivery', 'Quality check passed');
                $device?->update(['device_status' => 'ready_delivery']);
            } else {
                $ticket?->transitionTo('in_repair', 'Quality check failed: '.$check->notes);
                $device?->update(['device_status' => 'in_repair']);
            }
        });
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

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function originalCheck(): BelongsTo
    {
        return $this->belongsTo(QualityCheck::class, 'original_check_id');
    }

    public function rechecks(): BelongsTo
    {
        return $this->hasMany(QualityCheck::class, 'original_check_id');
    }

    // Scopes

    public function scopePassed($query)
    {
        return $query->where('overall_result', 'pass');
    }

    public function scopeFailed($query)
    {
        return $query->where('overall_result', 'fail');
    }

    public function scopeInitialChecks($query)
    {
        return $query->where('is_recheck', false);
    }

    // Helpers

    public function getPassedItemsCountAttribute(): int
    {
        if (! is_array($this->checklist)) {
            return 0;
        }

        return collect($this->checklist)->where('result', 'pass')->count();
    }

    public function getFailedItemsCountAttribute(): int
    {
        if (! is_array($this->checklist)) {
            return 0;
        }

        return collect($this->checklist)->where('result', 'fail')->count();
    }

    public function getTotalItemsCountAttribute(): int
    {
        return is_array($this->checklist) ? count($this->checklist) : 0;
    }
}
