<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairApproval extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'repair_ticket_id',
        'repair_device_id',
        'repair_diagnosis_id',
        'customer_id',
        'quoted_amount',
        'status',
        'customer_notes',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'expires_at',
    ];

    protected $casts = [
        'quoted_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::updated(function (self $approval) {
            if ($approval->isDirty('status')) {
                $ticket = $approval->repairTicket;
                $device = $approval->repairDevice;

                if ($approval->status === 'approved') {
                    $ticket?->transitionTo('waiting_parts', 'Customer approved repair quote', $approval->approved_by);
                    $device?->update(['device_status' => 'waiting_parts']);
                } elseif ($approval->status === 'rejected') {
                    $ticket?->transitionTo('rejected', 'Customer rejected repair quote: '.$approval->rejection_reason, $approval->approved_by);
                    $device?->update(['device_status' => 'rejected']);
                }
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

    public function repairDiagnosis(): BelongsTo
    {
        return $this->belongsTo(RepairDiagnosis::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    // Helpers

    public function isExpired(): bool
    {
        return $this->expires_at && now()->gt($this->expires_at) && $this->status === 'pending';
    }

    public function approve(int $approvedBy, ?string $notes = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $approvedBy,
            'customer_notes' => $notes,
        ]);
    }

    public function reject(int $approvedBy, string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'approved_at' => now(),
            'approved_by' => $approvedBy,
            'rejection_reason' => $reason,
        ]);
    }
}
