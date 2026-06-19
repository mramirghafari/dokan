<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;


class factorMaker extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;

    protected $fillable = [
        'name', 'type', 'pr_type', 'business_profile', 'line_layout', 'currency_type',
        'seller_name', 'seller_economic_number', 'seller_registration_number', 'seller_id_number',
        'seller_address', 'seller_zip_code', 'seller_phone', 'seller_fax',
        'buyer_name', 'buyer_econimic_code', 'buyer_registration_number', 'buyer_address',
        'buyer_zip_code', 'buyer_phone', 'buyer_region_area', 'buyer_map_code',
        'visitor_display', 'visitor_mobile',
        'column_pr_code', 'column_moadian', 'column_sub_unit', 'column_discount', 'column_tax',
        'organization_id', 'tenant_id', 'store_id',
    ];

    protected $casts = [
        'line_layout' => 'array',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function businessProfileLabel(): string
    {
        return app(\App\Services\InvoiceLayoutService::class)
            ->profileLabel($this->business_profile);
    }

    public function productTypeLabel(): string
    {
        return app(\App\Services\InvoiceLayoutService::class)
            ->productTypeLabel($this->pr_type === null ? null : (string) $this->pr_type);
    }
}
