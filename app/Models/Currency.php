<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['tenant_id', 'code', 'title', 'symbol', 'decimal_places', 'is_default', 'isActive'];

    protected $casts = [
        'is_default' => 'boolean',
        'isActive' => 'boolean',
    ];

    public function exchangeRates()
    {
        return $this->hasMany(ExchangeRate::class);
    }
}
