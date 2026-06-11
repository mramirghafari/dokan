<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxRate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['tenant_id', 'code', 'title', 'rate', 'valid_from', 'valid_to', 'is_default', 'isActive'];

    protected $casts = [
        'rate' => 'decimal:4',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_default' => 'boolean',
        'isActive' => 'boolean',
    ];
}
