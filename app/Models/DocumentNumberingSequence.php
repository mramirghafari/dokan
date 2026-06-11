<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentNumberingSequence extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['tenant_id', 'organization_id', 'document_type', 'prefix', 'year', 'next_number', 'padding', 'separator', 'reset_yearly', 'isActive'];

    protected $casts = [
        'reset_yearly' => 'boolean',
        'isActive' => 'boolean',
    ];
}
