<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionAlias extends Model
{
    use HasFactory;

    protected $fillable = [
        'permission_id',
        'tenant_id',
        'alias_title',
        'alias_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
