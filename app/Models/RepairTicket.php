<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class RepairTicket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'assigned_technician_id',
        'created_by',
        'ticket_number',
        'status',
        'priority',
        'reported_issue',
        'intake_notes',
        'diagnostic_fee',
        'labor_charge',
        'parts_cost',
        'total_estimated',
        'total_charged',
        'paid_amount',
        'balance_amount',
        'received_date',
        'estimated_completion_date',
        'actual_completion_date',
        'delivered_date',
        'warranty_months',
        'warranty_start_date',
        'warranty_end_date',
        'warranty_terms',
        'sla_due_at',
        'sla_breached',
    ];

    protected $casts = [
        'diagnostic_fee' => 'decimal:2',
        'labor_charge' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'total_estimated' => 'decimal:2',
        'total_charged' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'received_date' => 'date',
        'estimated_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'delivered_date' => 'date',
        'warranty_start_date' => 'date',
        'warranty_end_date' => 'date',
        'sla_due_at' => 'datetime',
        'sla_breached' => 'boolean',
    ];

    // Valid status transitions (state machine)
    protected static $validTransitions = [
        'received' => ['diagnosing', 'cancelled'],
        'diagnosing' => ['waiting_approval', 'waiting_parts', 'in_repair', 'cancelled', 'rejected'],
        'waiting_approval' => ['approved', 'rejected', 'cancelled'],
        'waiting_parts' => ['in_repair', 'cancelled'],
        'in_repair' => ['quality_check', 'waiting_parts', 'cancelled'],
        'quality_check' => ['ready_delivery', 'in_repair', 'cancelled'],
        'ready_delivery' => ['delivered', 'cancelled'],
        'delivered' => [],
        'cancelled' => [],
        'rejected' => [],
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (self $ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = static::generateTicketNumber();
            }
            $ticket->received_date ??= now()->toDateString();
            $ticket->balance_amount = $ticket->total_charged - $ticket->paid_amount;
        });

        static::updating(function (self $ticket) {
            if ($ticket->isDirty('total_charged') || $ticket->isDirty('paid_amount')) {
                $ticket->balance_amount = $ticket->total_charged - $ticket->paid_amount;
            }
        });

        static::updated(function (self $ticket) {
            if ($ticket->isDirty('status')) {
                $ticket->recordStatusChange(
                    $ticket->getOriginal('status'),
                    $ticket->status,
                    auth()->id() ?? $ticket->created_by
                );
            }
        });

        static::deleted(function (self $ticket) {
            // Restore customer balance if ticket had charges
            if ($ticket->customer && $ticket->paid_amount > 0) {
                $ticket->customer->increment('outstanding_balance', $ticket->paid_amount);
            }
        });
    }

    /**
     * Generate unique ticket number: REP-{YEAR}-{SEQUENCE}
     */
    public static function generateTicketNumber(): string
    {
        $year = now()->year;
        $tenant = Tenant::getCurrentTenant();
        $tenantId = $tenant?->id;

        $lastTicket = static::withoutGlobalScope(TenantScope::class)
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->where('ticket_number', 'like', "REP-{$year}-%")
            ->orderBy('ticket_number', 'desc')
            ->first();

        $sequence = 1;
        if ($lastTicket) {
            $parts = explode('-', $lastTicket->ticket_number);
            $sequence = (int) ($parts[2] ?? 0) + 1;
        }

        return sprintf('REP-%d-%05d', $year, $sequence);
    }

    /**
     * Check if status transition is valid
     */
    public function canTransitionTo(string $newStatus): bool
    {
        if ($this->status === $newStatus) {
            return true;
        }

        return in_array($newStatus, self::$validTransitions[$this->status] ?? []);
    }

    /**
     * Transition to new status with validation
     */
    public function transitionTo(string $newStatus, ?string $notes = null, ?int $changedBy = null): bool
    {
        if (! $this->canTransitionTo($newStatus)) {
            return false;
        }

        $oldStatus = $this->status;
        $this->status = $newStatus;

        // Set dates based on status
        match ($newStatus) {
            'delivered' => $this->delivered_date = now()->toDateString(),
            'actual_completion_date' => $this->actual_completion_date = now()->toDateString(),
            default => null,
        };

        $saved = $this->save();

        if ($saved) {
            $this->recordStatusChange($oldStatus, $newStatus, $changedBy ?? auth()->id(), $notes);
        }

        return $saved;
    }

    /**
     * Record status change in history
     */
    public function recordStatusChange(string $fromStatus, string $toStatus, ?int $changedBy, ?string $notes = null): void
    {
        $this->statusHistories()->create([
            'tenant_id' => $this->tenant_id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => $changedBy,
            'notes' => $notes,
        ]);
    }

    /**
     * Recalculate totals from parts and diagnoses
     */
    public function recalculateTotals(): void
    {
        $this->parts_cost = $this->repairParts()->sum('total_price');
        $this->labor_charge = $this->diagnoses()->sum('labor_charge');
        $this->total_estimated = $this->diagnoses()->sum('estimated_total');
        $this->total_charged = $this->diagnostic_fee + $this->labor_charge + $this->parts_cost;
        $this->balance_amount = $this->total_charged - $this->paid_amount;
        $this->save();
    }

    /**
     * Check if ticket is overdue
     */
    public function isOverdue(): bool
    {
        if (! $this->estimated_completion_date) {
            return false;
        }

        return now()->gt(Carbon::parse($this->estimated_completion_date)) &&
            ! in_array($this->status, ['delivered', 'cancelled', 'rejected']);
    }

    /**
     * Get aging in days
     */
    public function getAgingDaysAttribute(): int
    {
        return $this->received_date ? Carbon::parse($this->received_date)->diffInDays(now()) : 0;
    }

    // Relationships

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(RepairDevice::class);
    }

    public function accessories(): HasMany
    {
        return $this->hasMany(RepairAccessory::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(RepairStatusHistory::class);
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

    public function latestDelivery(): HasOne
    {
        return $this->hasOne(RepairDelivery::class)->latestOfMany();
    }

    public function latestDiagnosis(): HasOne
    {
        return $this->hasOne(RepairDiagnosis::class)->latestOfMany();
    }

    public function pendingApproval(): HasOne
    {
        return $this->hasOne(RepairApproval::class)->where('status', 'pending')->latestOfMany();
    }

    // Scopes

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByTechnician($query, int $technicianId)
    {
        return $query->where('assigned_technician_id', $technicianId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('estimated_completion_date', '<', now()->toDateString())
            ->whereNotIn('status', ['delivered', 'cancelled', 'rejected']);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['received', 'diagnosing', 'waiting_approval', 'waiting_parts']);
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('status', ['in_repair', 'quality_check', 'ready_delivery']);
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['delivered']);
    }
}
