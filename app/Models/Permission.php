<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PermissionAlias;
use App\Models\Role;
use App\Models\User;

class Permission extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title',
        'canonical_title',
        'module_key',
        'resource_key',
        'action_key',
        'naming_status',
        'description',
        'tenant_id',
        'isActive',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenants::class, 'tenant_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function aliases()
    {
        return $this->hasMany(PermissionAlias::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
