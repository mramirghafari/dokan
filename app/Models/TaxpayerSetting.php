<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxpayerSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'title',
        'send_mode',
        'environment',
        'memory_id',
        'branch_tax_code',
        'economic_number',
        'seller_national_id',
        'seller_postal_code',
        'endpoint_url',
        'trusted_company_name',
        'certificate_alias',
        'auto_send',
        'is_active',
        'extra_config',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'auto_send' => 'boolean',
        'is_active' => 'boolean',
        'extra_config' => 'array',
    ];
}
