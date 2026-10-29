<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'changed_attributes',
        'user_id',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'description',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_attributes' => 'array',
        'metadata' => 'array',
    ];

    public $timestamps = true;

    // No soft deletes - audit logs are immutable

    // Relationships

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForModel($query, string $modelClass, int $modelId)
    {
        return $query->where('auditable_type', $modelClass)
            ->where('auditable_id', $modelId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Static helper to log changes

    public static function log(string $action, Model $model, array $oldValues = [], array $newValues = [], ?string $description = null): self
    {
        $changedAttributes = [];
        if (! empty($oldValues) && ! empty($newValues)) {
            foreach ($newValues as $key => $newValue) {
                $oldValue = $oldValues[$key] ?? null;
                if ($oldValue != $newValue) {
                    $changedAttributes[] = $key;
                }
            }
        }

        return self::create([
            'tenant_id' => $model->tenant_id ?? auth()->user()?->tenant_id,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'action' => $action,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'changed_attributes' => $changedAttributes ?: null,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'description' => $description,
        ]);
    }
}
