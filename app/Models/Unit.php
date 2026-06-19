<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;

class Unit extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;
    protected $fillable = ['code', 'title', 'symbol', 'unit_type', 'usage_scope', 'parent_id', 'conversion_to_parent', 'organization_id', 'tenant_id', 'description', 'isActive'];

    protected $casts = [
        'conversion_to_parent' => 'decimal:6',
    ];

    public const SCOPE_PRODUCT = 'product';
    public const SCOPE_SHIPPING = 'shipping';

    public const USAGE_SCOPE_LABELS = [
        self::SCOPE_PRODUCT => 'واحد محصول',
        self::SCOPE_SHIPPING => 'واحد باربری',
    ];

    public const UNIT_TYPE_LABELS = [
        'count' => 'تعدادی',
        'weight' => 'وزنی',
        'volume' => 'حجمی',
        'length' => 'طولی',
        'service' => 'خدمت',
    ];

    public function scopeForUsageScope($query, string $scope)
    {
        return $query->where('usage_scope', $scope);
    }

    public function parent()
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function children()
    {
        return $this->hasMany(Unit::class, 'parent_id');
    }
}
