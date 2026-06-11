<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractingProgressStatementItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'contracting_progress_statement_id',
        'contracting_project_item_id',
        'tenant_id',
        'organization_id',
        'item_code',
        'title',
        'unit',
        'quantity',
        'previous_quantity',
        'cumulative_quantity',
        'unit_price',
        'gross_amount',
        'description',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'previous_quantity' => 'decimal:4',
        'cumulative_quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'gross_amount' => 'decimal:2',
    ];

    public function statement()
    {
        return $this->belongsTo(ContractingProgressStatement::class, 'contracting_progress_statement_id');
    }

    public function contractItem()
    {
        return $this->belongsTo(ContractingProjectItem::class, 'contracting_project_item_id');
    }
}
