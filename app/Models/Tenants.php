<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Tenants extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'id',
        'code',
        'name',
        'display_name',
        'legal_name',
        'economic_number',
        'national_id',
        'phone',
        'mobile',
        'address',
        'subscription_type',
        'subscription_started_at',
        'subscription_ends_at',
        'wallet_balance',
        'sms_unit_price_toman',
        'customer_group_status',
        'unit_order',
        'sub_order',
        'currency_type',
        'fiscal_year_start',
        'fiscal_year_end',
        'tozihat',
        'settings',
        'created_by',
        'status',
        'panel_status',
        'panel_type',
        'default_currency_id',
        'default_fiscal_year_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $casts = [
        'settings' => 'array',
        'customer_group_status' => 'boolean',
        'status' => 'boolean',
        'subscription_started_at' => 'date',
        'subscription_ends_at' => 'date',
        'wallet_balance' => 'decimal:2',
        'sms_unit_price_toman' => 'decimal:2',
        'fiscal_year_start' => 'date',
        'fiscal_year_end' => 'date',
    ];

    public function defaultCurrency()
    {
        return $this->belongsTo(Currency::class, 'default_currency_id');
    }

    public function defaultFiscalYear()
    {
        return $this->belongsTo(FiscalYear::class, 'default_fiscal_year_id');
    }

    public function panelStatusText(): string
    {
        return match ($this->panel_status) {
            'inactive' => 'غیرفعال',
            'suspended' => 'تعلیق شده',
            default => 'فعال',
        };
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function organizations()
    {
        return $this->hasMany(Organization::class, 'tenant_id');
    }

    public function legacyOrganizations()
    {
        return $this->hasMany(Organization::class, 'tenants_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    public function legacyUsers()
    {
        return $this->hasMany(User::class, 'tenants_id');
    }

    public function stores()
    {
        return $this->hasMany(Store::class, 'tenant_id');
    }
}
