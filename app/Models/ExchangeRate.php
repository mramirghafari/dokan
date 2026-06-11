<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExchangeRate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['tenant_id', 'currency_id', 'rate_date', 'rate', 'source', 'description', 'created_by'];

    protected $casts = [
        'rate_date' => 'date',
        'rate' => 'decimal:6',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
