<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'repair_ticket_id',
        'changed_by',
        'from_status',
        'to_status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
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

    public function changer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // Scopes

    public function scopeForTicket($query, int $ticketId)
    {
        return $query->where('repair_ticket_id', $ticketId)->orderBy('created_at');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('to_status', $status);
    }
}
