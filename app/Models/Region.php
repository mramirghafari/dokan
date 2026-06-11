<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;



class Region extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;
    protected $fillable = ['name', 'northEast_lat', 'northEast_lang', 'southWest_lat', 'southWest_lang', 'city_id', 'leader_id', 'organization_id', 'tenant_id', 'store_id', 'leader_id'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }
    public function areas()
    {
        return $this->hasMany(Area::class, 'region_id', 'id');
    }

    public function customers()
    {
        // روش مستقیم
        return $this->hasMany(Customers::class, 'region_id', 'id');
    }

    public function activeCustomers()
    {
        return $this->areas()
            ->with('customers')
            ->get()
            ->flatMap(function ($area) {
                return $area->customers;
            })
            ->filter(function ($customer) {
                return $customer->pishfactors()->whereIn('status', [1, 4])->exists();
            })
            ->unique('id')
            ->values(); // به جای get()، برای مرتب کردن index ها
    }

    // تعداد مشتریان فعال (یکبار برای هر مشتری شمارش می‌شود)
    public function activeCustomersCount()
    {
        return $this->areas() // همه Area های این Region
            ->with('customers') // لود کردن مشتری‌ها
            ->get()
            ->flatMap(function ($area) {
                return $area->customers; // لیست مشتری‌های هر Area
            })
            ->filter(function ($customer) {
                return $customer->pishfactors()->whereIn('status', [1, 4])->exists();
            })
            ->unique('id')
            ->count();
    }


    public function activeOrders()
    {
        return \App\Models\Pishfactor::query()
            ->whereIn('customer_id', function ($query) {
                $query->select('id')
                    ->from('customers')
                    ->whereIn('area', function ($q) {
                        $q->select('id')
                            ->from('areas')
                            ->where('region_id', $this->id);
                    });
            })
            ->whereIn('status', [1, 4])
            ->get();
    }

    // مجموع مبلغ سفارشات فعال
    public function activeOrdersSum()
    {
        return \App\Models\Pishfactor::query()
            ->whereIn('customer_id', function ($query) {
                $query->select('id')
                    ->from('customers')
                    ->whereIn('area', function ($q) {
                        $q->select('id')
                            ->from('areas')
                            ->where('region_id', $this->id);
                    });
            })
            ->whereIn('status', [1, 4])
            ->sum('fullPrice');
    }

    public function customersThroughAreas()
    {
        // روش غیر مستقیم (از طریق Areas)
        return $this->hasManyThrough(
            Customers::class, // مدل مقصد
            Area::class,      // مدل واسط
            'region_id',      // ستون در Area که Region را وصل می‌کند
            'area',           // ستون در Customers که Area را وصل می‌کند
            'id',             // ستون در Region
            'id'              // ستون در Area
        );
    }
}
