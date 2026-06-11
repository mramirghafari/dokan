<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;
use App\Models\{Area, Region, Pishfactor, User};

class Customers extends Model
{
    use HasFactory, BelongsToTenant, SoftDeletes, HasOrganizationFilter;

    protected $fillable = [
        'name',
        'national_id',
        'economic_number',
        'phone',
        'mobile',
        'tablo',
        'senf',
        'customer_group_id',
        'channel',
        'sales_channel_id',
        'customer_code',
        'status',
        'customer_status_id',
        'area',
        'region_id',
        'mapcode',
        'address',
        'store_address',
        'shop_lat',
        'shop_lng',
        'store_lat',
        'store_lng',
        'leader_id',
        'created_by',
        'organization_id',
        'tenant_id',
        'updated_at'
    ];

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id', 'id');
    }

    public function customerGroup()
    {
        return $this->belongsTo(CustomerSegment::class, 'customer_group_id');
    }

    public function salesChannel()
    {
        return $this->belongsTo(CustomerSegment::class, 'sales_channel_id');
    }

    public function customerStatus()
    {
        return $this->belongsTo(CustomerSegment::class, 'customer_status_id');
    }

    public function customerGroupText(): string
    {
        return optional($this->customerGroup)->title ?: (string) $this->senf;
    }

    public function salesChannelText(): string
    {
        return optional($this->salesChannel)->title ?: (string) $this->channel;
    }

    public function customerStatusText(): string
    {
        return optional($this->customerStatus)->title ?: ((int) $this->status === 1 ? 'فعال' : 'غیرفعال');
    }

    /** 🔹 منطقه (Area) که مشتری به آن تعلق دارد */
    public function Area()
    {
        return $this->belongsTo(Area::class, 'area', 'id');
    }

    /** 🔹 ناحیه اصلی (Region) مشتری */
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    /** 🔹 سفارش‌ها */
    public function pishfactors()
    {
        return $this->hasMany(Pishfactor::class, 'customer_id', 'id');
    }

    /** 🔹 سفارش‌های فعال */
    public function activeOrders()
    {
        return $this->hasMany(Pishfactor::class, 'customer_id', 'id')
            ->whereIn('status', [1, 4]);
    }

    public function crmFollowups()
    {
        return $this->hasMany(CrmFollowup::class, 'customer_id', 'id');
    }

    public function crmOpportunities()
    {
        return $this->hasMany(CrmOpportunity::class, 'customer_id', 'id');
    }

    public function activeOrdersSum()
    {
        return $this->activeOrders()->sum('fullPrice');
    }

    /** 🔹 تعیین رهبر بر اساس Area → یا Region */
    public function getLeaderAttribute()
    {
        $area = $this->Area;
        if ($area && $area->leader_id) {
            return User::find($area->leader_id);
        }

        if ($area && $area->region && $area->region->leader_id) {
            return User::find($area->region->leader_id);
        }
        return null;
    }
}
