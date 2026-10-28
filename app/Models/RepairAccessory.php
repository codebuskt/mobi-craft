<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairAccessory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'repair_ticket_id',
        'repair_device_id',
        'name',
        'description',
        'type',
        'is_received',
        'is_returned',
        'condition_notes',
        'photos',
    ];

    protected $casts = [
        'is_received' => 'boolean',
        'is_returned' => 'boolean',
        'photos' => 'array',
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

    // Scopes

    public function scopeReceived($query)
    {
        return $query->where('is_received', true);
    }

    public function scopeNotReturned($query)
    {
        return $query->where('is_returned', false);
    }

    public function scopePendingReturn($query)
    {
        return $query->where('is_received', true)->where('is_returned', false);
    }
}
