<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;
use App\Traits\HasOrganizationScopes;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasOrganizationFilter, HasOrganizationScopes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'mobile',
        'password',
        'national_code',
        'address',
        'postal_code',
        'organization_id',
        'tenants_id',
        'tenant_id',
        'personalID',
        'leader_id',
        'isActive',
        'isAdmin',
        'isGod',
        'isGold',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->BelongsToMany(Role::class);
    }

    public function scopes()
    {
        return $this->hasMany(UserScope::class);
    }

    public function permissions()
    {
        return $this->BelongsToMany(Permission::class);
    }

    public function logs()
    {
        return $this->BelongsToMany(Log::class);
    }

    public function histories()
    {
        return $this->BelongsToMany(History::class);
    }

    public function organization()
    {
        return $this->BelongsTo(Organization::class);
    }

    public function setTenantsIdAttribute($value)
    {
        $this->attributes['tenants_id'] = $value;

        if (!array_key_exists('tenant_id', $this->attributes) || empty($this->attributes['tenant_id'])) {
            $this->attributes['tenant_id'] = $value;
        }
    }

    public function setTenantIdAttribute($value)
    {
        $this->attributes['tenant_id'] = $value;

        if (!array_key_exists('tenants_id', $this->attributes) || empty($this->attributes['tenants_id'])) {
            $this->attributes['tenants_id'] = $value;
        }
    }

    public function tenant()
    {
        return $this->belongsTo(Tenants::class, 'tenant_id');
    }

    public function legacyTenant()
    {
        return $this->belongsTo(Tenants::class, 'tenants_id');
    }

    public function loginBlockMessage(): ?string
    {
        if ((int) $this->isActive === 0) {
            return 'کاربری توسط مدیریت غیرفعال شده است.';
        }

        if ((int) $this->isGod === 1) {
            return null;
        }

        $tenant = $this->tenant ?: $this->legacyTenant;

        if (!$tenant) {
            return null;
        }

        if ((int) $tenant->status !== 1) {
            return 'پنل شما غیرفعال است. لطفا با پشتیبانی تماس بگیرید.';
        }

        if ($tenant->subscription_ends_at && Carbon::parse($tenant->subscription_ends_at)->endOfDay()->isPast()) {
            return 'اشتراک پنل شما به پایان رسیده است. لطفا برای تمدید با پشتیبانی تماس بگیرید.';
        }

        return null;
    }

    public function hasPermission($permission)
    {
        if ($this->isGod == 1) {
            return true;
        }

        if ($this->isAdmin == 1 && $permission->title !== 'tenants') {
            return true;
        }

        return $this->permissions->contains('id', $permission->id)
            || $this->permissions->contains('title', $permission->title)
            || ($permission->canonical_title && $this->permissions->contains('canonical_title', $permission->canonical_title))
            || $this->hasRole($permission->roles);
    }

    public function hasRole($roles)
    {
        return !! $roles->intersect($this->roles)->all();
    }

    public function getOrganizationIdsForSearchAttribute()
    {
        // مقدار اولیه
        $raw = $this->organization_id;

        // اگر هیچی نباشه، آرایه خالی برگردون
        if (empty($raw)) {
            return [];
        }

        // اگر رشته‌ی JSON بود
        $decoded = json_decode($raw, true);

        $ids = is_array($decoded)
            ? $decoded
            : [intval($raw)];

        $searchValues = [];
        foreach ($ids as $id) {
            $searchValues[] = intval($id);
            $searchValues[] = strval($id);
        }

        return $searchValues;
    }


    public function cargo()
    {
        return $this->hasOne(Cargo::class, 'driver_id', 'id');
    }

    /**
     * 🔹 اسکوپ: مشتریان جدید ثبت‌شده توسط کاربر یا زیرمجموعه‌ها
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $firstDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNewCustomers($query, $firstDate = null)
    {
        // اگر تاریخ ندادیم، یعنی از ابتدا همه مشتری‌ها رو حساب کن
        $firstDate = $firstDate ? Carbon::parse($firstDate) : null;
        $today = Carbon::now();

        return $query->withCount([
            // 🔹 مشتریان مستقیم
            'customersCreated as direct_new_customers' => function ($q) use ($firstDate, $today) {
                if ($firstDate) {
                    $q->whereBetween('created_at', [$firstDate, $today]);
                }
            },

            // 🔹 مشتریان زیرمجموعه‌ها
            'subordinates as team_new_customers' => function ($subQuery) use ($firstDate, $today) {
                $subQuery->whereHas('customersCreated', function ($customerQuery) use ($firstDate, $today) {
                    if ($firstDate) {
                        $customerQuery->whereBetween('created_at', [$firstDate, $today]);
                    }
                });
            }
        ]);
    }

    public function subtree($start = null, $end = null)
    {
        return app(\App\Services\UserHierarchyService::class)
            ->getSubtree($this->id, $start, $end);
    }


    /** روابط مرتبط */
    public function customersCreated()
    {
        return $this->hasMany(Customers::class, 'created_by', 'id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'leader_id', 'id');
    }
}
