<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmSalesBoardCardComment extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'card_id',
        'tenant_id',
        'organization_id',
        'user_id',
        'comment',
        'mentions',
    ];

    protected $casts = [
        'mentions' => 'array',
    ];

    public function card()
    {
        return $this->belongsTo(CrmSalesBoardCard::class, 'card_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
