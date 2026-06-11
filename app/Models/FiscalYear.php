<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FiscalYear extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['tenant_id', 'title', 'starts_at', 'ends_at', 'status', 'is_default'];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'is_default' => 'boolean',
    ];

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
