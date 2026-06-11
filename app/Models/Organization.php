<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;

class Organization extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;

    protected $fillable = ['branch_code', 'title', 'legal_name', 'economic_number', 'national_id', 'phone', 'address', 'description', 'type', 'branch_type', 'branch_status', 'is_headquarters', 'unit_order', 'sub_unit', 'pr_type', 'currency_type', 'unit_display', 'isActive', 'tenants_id', 'tenant_id', 'customer_group_status'];

    protected $casts = [
        'is_headquarters' => 'boolean',
    ];

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

    public function branchTypeText(): string
    {
        return match ($this->branch_type) {
            'headquarters' => 'دفتر مرکزی',
            'warehouse_branch' => 'شعبه انبار',
            'production_branch' => 'شعبه تولید',
            default => 'شعبه فروش/پخش',
        };
    }

    public function branchStatusText(): string
    {
        return $this->branch_status === 'inactive' ? 'غیرفعال' : 'فعال';
    }

    public function legacyTenant()
    {
        return $this->belongsTo(Tenants::class, 'tenants_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class);
    }

    public function stores()
    {
        return $this->belongsToMany(Store::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
