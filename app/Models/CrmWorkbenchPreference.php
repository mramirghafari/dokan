<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmWorkbenchPreference extends Model
{
    use HasFactory, BelongsToTenant, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'user_id',
        'focus_scope',
        'enabled_widgets',
        'filters',
    ];

    protected $casts = [
        'enabled_widgets' => 'array',
        'filters' => 'array',
    ];

    public const FOCUS_SCOPES = [
        'mine' => 'فقط کارهای من',
        'team' => 'تیم من',
        'all' => 'همه موارد مجاز',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function focusScopeText(): string
    {
        return self::FOCUS_SCOPES[$this->focus_scope] ?? $this->focus_scope;
    }
}
