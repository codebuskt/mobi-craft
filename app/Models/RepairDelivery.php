<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairDelivery extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'repair_ticket_id',
        'repair_device_id',
        'delivered_by',
        'received_by_customer_id',
        'delivered_date',
        'condition_on_delivery',
        'accessories_returned',
        'delivery_photos',
        'customer_signature',
        'customer_notes',
        'warranty_start_date',
        'warranty_end_date',
        'warranty_terms',
    ];

    protected $casts = [
        'delivered_date' => 'date',
        'accessories_returned' => 'array',
        'delivery_photos' => 'array',
        'warranty_start_date' => 'date',
        'warranty_end_date' => 'date',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::created(function (self $delivery) {
            $ticket = $delivery->repairTicket;
            $device = $delivery->repairDevice;

            // Update ticket and device status
            $ticket?->transitionTo('delivered', 'Device delivered to customer', $delivery->delivered_by);
            $device?->update(['device_status' => 'delivered']);

            // Update warranty dates on ticket
            if ($delivery->warranty_start_date) {
                $ticket->update([
                    'warranty_start_date' => $delivery->warranty_start_date,
                    'warranty_end_date' => $delivery->warranty_end_date,
                    'warranty_terms' => $delivery->warranty_terms,
                ]);
            }

            // Mark accessories as returned
            if (is_array($delivery->accessories_returned)) {
                foreach ($delivery->accessories_returned as $accessoryId) {
                    $delivery->repairTicket->accessories()
                        ->where('id', $accessoryId)
                        ->update(['is_returned' => true]);
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

    public function deliverer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_customer_id');
    }

    // Scopes

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('delivered_date', [$startDate, $endDate]);
    }

    // Helpers

    public function isUnderWarranty(): bool
    {
        if (! $this->warranty_end_date) {
            return false;
        }

        return now()->lte($this->warranty_end_date);
    }
}
