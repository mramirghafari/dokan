<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmSalesBoard extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'owner_user_id',
        'title',
        'type',
        'visibility',
        'description',
        'cover_image_path',
        'is_default',
        'position',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'position' => 'integer',
    ];

    public function lists()
    {
        return $this->hasMany(CrmSalesBoardList::class, 'board_id')->orderBy('position')->orderBy('id');
    }

    public function cards()
    {
        return $this->hasMany(CrmSalesBoardCard::class, 'board_id');
    }

    public function automationRules()
    {
        return $this->hasMany(CrmAutomationRule::class, 'board_id')->latest();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }
}
