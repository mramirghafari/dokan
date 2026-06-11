<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserScope extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'scope_type',
        'scope_id',
        'created_by',
        'updated_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
