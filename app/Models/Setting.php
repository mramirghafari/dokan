<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'store_id',
        'user_id',
        'scope',
        'category',
        'key',
        'title',
        'value',
        'type',
        'is_locked',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenants::class, 'tenant_id');
    }
}
