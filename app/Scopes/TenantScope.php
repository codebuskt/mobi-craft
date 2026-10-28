<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Models\Tenant;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Skip if this is the Tenant model itself or if tenant_id column doesn't exist
        if ($model instanceof Tenant || ! $model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'tenant_id')) {
            return;
        }

        $tenant = Tenant::getCurrentTenant();

        if ($tenant) {
            $builder->where($model->getTable() . '.tenant_id', $tenant->id);
        } elseif ($model->getTable() !== 'tenants') {
            // If no tenant is set and model has tenant_id, exclude tenant-scoped records
            // This prevents super admin from accidentally seeing tenant data without context
            $builder->whereNull($model->getTable() . '.tenant_id');
        }
    }

    /**
     * Extend the model with the scope.
     */
    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenantScope', function (Builder $builder) {
            return $builder->withoutGlobalScope(static::class);
        });
    }
}