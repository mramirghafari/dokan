<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Permission;
use App\Models\RoleScope;
use App\Models\User;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'max_discount_percent',
        'max_discount_amount',
        'tenant_id',
        'scope_type',
        'isActive',
    ];

    protected $casts = [
        'max_discount_percent' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenants::class, 'tenant_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function scopes()
    {
        return $this->hasMany(RoleScope::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class);
    }

    public function regions()
    {
        return $this->belongsToMany(Region::class);
    }
}
