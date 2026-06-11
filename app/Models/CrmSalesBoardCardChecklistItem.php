<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmSalesBoardCardChecklistItem extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'card_id',
        'tenant_id',
        'organization_id',
        'title',
        'is_done',
        'done_at',
        'done_by',
        'position',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'done_at' => 'datetime',
        'position' => 'integer',
    ];

    public function card()
    {
        return $this->belongsTo(CrmSalesBoardCard::class, 'card_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function doneBy()
    {
        return $this->belongsTo(User::class, 'done_by');
    }
}
