<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleScope extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'tenant_id',
        'scope_type',
        'scope_id',
        'created_by',
        'updated_by',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
