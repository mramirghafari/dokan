<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Area extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'region_id', 'organization_id', 'tenant_id', 'leader_id', 'visit_days', 'visit_frequency'];

    protected $casts = [
        'visit_days' => 'array',
    ];

    public const VISIT_DAY_LABELS = [
        'saturday' => 'شنبه',
        'sunday' => 'یکشنبه',
        'monday' => 'دوشنبه',
        'tuesday' => 'سه شنبه',
        'wednesday' => 'چهارشنبه',
        'thursday' => 'پنجشنبه',
        'friday' => 'جمعه',
    ];

    public const VISIT_FREQUENCY_LABELS = [
        'weekly' => 'هفتگی',
        'biweekly' => 'دو هفته یک بار',
        'monthly' => 'ماهانه',
    ];

    public function visitDaysText(): string
    {
        $days = collect($this->visit_days ?: [])
            ->map(fn($day) => self::VISIT_DAY_LABELS[$day] ?? $day)
            ->filter()
            ->implode('، ');

        return $days ?: 'ثبت نشده';
    }

    public function visitFrequencyText(): string
    {
        return self::VISIT_FREQUENCY_LABELS[$this->visit_frequency] ?? 'هفتگی';
    }

    public function customers()
    {
        return $this->hasMany(Customers::class, 'area', 'id');
    }


    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function customersCount()
    {
        return $this->customers()->count();
    }

    public function activeCustomers()
    {
        return $this->customers() // همه مشتری‌های این ناحیه
            ->get()
            ->filter(function ($customer) {
                return $customer->pishfactors()
                    ->whereIn('status', [1, 4]) // فاکتور فعال
                    ->exists(); // حداقل یک فاکتور فعال داشته باشد
            })
            ->values(); // مرتب کردن index‌ها در collection نهایی
    }

    public function activeCustomersCount()
    {
        return $this->customers() // مشتری‌های این مسیر
            ->whereHas('pishfactors') // فقط مشتریانی که فاکتور دارند
            ->distinct('customers.id')
            ->count('customers.id');
    }

    public function activeOrders()
    {
        return \App\Models\Pishfactor::query()
            ->whereIn('customer_id', function ($query) {
                $query->select('id')
                    ->from('customers')
                    ->where('area', $this->id);
            })
            ->whereIn('status', [1, 4]) // فقط فاکتورهای فعال
            ->get();
    }

    public function activeOrdersSum()
    {
        return $this->customers()
            ->join('pishfactors', 'customers.id', '=', 'pishfactors.customer_id')
            ->whereIn('pishfactors.status', [1, 4]) // فقط فاکتورهای فعال
            ->sum('pishfactors.fullPrice'); // جمع مبلغ فاکتورهای فعال
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
