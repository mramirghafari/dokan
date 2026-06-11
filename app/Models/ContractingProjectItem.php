<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractingProjectItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contracting_project_id',
        'tenant_id',
        'organization_id',
        'item_code',
        'title',
        'unit',
        'quantity',
        'unit_price',
        'total_amount',
        'executed_quantity',
        'executed_amount',
        'sort_order',
        'description',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'executed_quantity' => 'decimal:4',
        'executed_amount' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(ContractingProject::class, 'contracting_project_id');
    }
}
