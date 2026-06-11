<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmSalesBoardList extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'board_id',
        'tenant_id',
        'organization_id',
        'title',
        'stage_key',
        'color',
        'probability_percent',
        'wip_limit',
        'position',
        'is_final',
        'final_status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'probability_percent' => 'integer',
        'wip_limit' => 'integer',
        'position' => 'integer',
        'is_final' => 'boolean',
    ];

    public function board()
    {
        return $this->belongsTo(CrmSalesBoard::class, 'board_id');
    }

    public function cards()
    {
        return $this->hasMany(CrmSalesBoardCard::class, 'list_id')->orderBy('position')->orderByDesc('id');
    }
}
