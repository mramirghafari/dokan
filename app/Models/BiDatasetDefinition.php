<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiDatasetDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'dataset_key',
        'title',
        'domain',
        'source_type',
        'description',
        'dimensions',
        'measures',
        'filters',
        'fixed_filters',
        'default_dimensions',
        'default_measures',
        'default_sort',
        'permission_key',
        'is_active',
    ];

    protected $casts = [
        'dimensions' => 'array',
        'measures' => 'array',
        'filters' => 'array',
        'fixed_filters' => 'array',
        'default_dimensions' => 'array',
        'default_measures' => 'array',
        'default_sort' => 'array',
        'is_active' => 'boolean',
    ];
}
