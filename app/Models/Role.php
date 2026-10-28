<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
        'is_tenant_scoped',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_tenant_scoped' => 'boolean',
    ];

    /**
     * Get the permissions for the role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')
            ->withTimestamps(false); // Disable timestamps on pivot
    }

    /**
     * Get the users with this role.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('slug', $permission)->exists();
    }

    /**
     * Give permission to role.
     */
    public function givePermissionTo(Permission|string $permission): void
    {
        if ($permission instanceof Permission) {
            $permission = $permission->slug;
        }

        $this->permissions()->syncWithoutDetaching([
            Permission::where('slug', $permission)->firstOrFail()->id,
        ]);
    }

    /**
     * Revoke permission from role.
     */
    public function revokePermissionTo(Permission|string $permission): void
    {
        if ($permission instanceof Permission) {
            $permission = $permission->slug;
        }

        $this->permissions()->detach(
            Permission::where('slug', $permission)->firstOrFail()->id
        );
    }

    /**
     * Sync permissions.
     */
    public function syncPermissions(array $permissions): void
    {
        $permissionIds = Permission::whereIn('slug', $permissions)->pluck('id');
        $this->permissions()->sync($permissionIds);
    }
}