<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'avatar',
        'status',
        'tenant_id',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    /**
     * Get the tenant that owns the user.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the role assigned to the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if user is a super admin.
     */
    public function getIsSuperAdminAttribute(): bool
    {
        return $this->role?->slug === 'super_admin';
    }

    /**
     * Check if user is a tenant user.
     */
    public function getIsTenantUserAttribute(): bool
    {
        return $this->tenant_id !== null && ! $this->is_super_admin;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        if (! $this->role) {
            return false;
        }

        return $this->role->permissions()->where('slug', $permission)->exists();
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        if (! $this->role) {
            return false;
        }

        return $this->role->permissions()->whereIn('slug', $permissions)->exists();
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        if (! $this->role) {
            return false;
        }

        return $this->role->permissions()->whereIn('slug', $permissions)->count() === count($permissions);
    }

    /**
     * Get all permissions for the user.
     */
    public function getAllPermissions(): array
    {
        if ($this->is_super_admin) {
            return Permission::pluck('slug')->toArray();
        }

        if (! $this->role) {
            return [];
        }

        return $this->role->permissions->pluck('slug')->toArray();
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include users for the current tenant.
     */
    public function scopeCurrentTenant($query)
    {
        $tenant = Tenant::getCurrentTenant();

        if ($tenant) {
            return $query->where('tenant_id', $tenant->id);
        }

        return $query;
    }
}