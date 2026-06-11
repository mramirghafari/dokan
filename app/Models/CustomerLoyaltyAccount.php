<?php

namespace App\Models;

use App\Traits\HasOrganizationFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerLoyaltyAccount extends Model
{
    use HasFactory, HasOrganizationFilter, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'customer_id',
        'tier',
        'retention_status',
        'points_balance',
        'lifetime_points',
        'lifetime_value',
        'last_purchase_at',
        'last_activity_at',
        'benefits_note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'points_balance' => 'integer',
        'lifetime_points' => 'integer',
        'lifetime_value' => 'decimal:2',
        'last_purchase_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public const TIERS = [
        'bronze' => 'برنزی',
        'silver' => 'نقره ای',
        'gold' => 'طلایی',
        'platinum' => 'پلاتینیوم',
    ];

    public const RETENTION_STATUSES = [
        'new' => 'جدید',
        'loyal' => 'وفادار',
        'at_risk' => 'در خطر',
        'lost' => 'از دست رفته',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id');
    }

    public function transactions()
    {
        return $this->hasMany(CustomerLoyaltyTransaction::class);
    }

    public function tierText(): string
    {
        return self::TIERS[$this->tier] ?? $this->tier;
    }

    public function retentionStatusText(): string
    {
        return self::RETENTION_STATUSES[$this->retention_status] ?? $this->retention_status;
    }
}
