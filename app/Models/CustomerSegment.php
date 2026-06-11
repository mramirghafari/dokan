<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerSegment extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'type',
        'title',
        'code',
        'sort_order',
        'is_default',
        'isActive',
        'description',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'isActive' => 'boolean',
    ];

    public const TYPE_LABELS = [
        'customer_group' => 'گروه مشتری',
        'sales_channel' => 'کانال فروش',
        'customer_status' => 'وضعیت مشتری',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function typeText(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }
}
