<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\SyncsTenantColumns;

class Accounts extends Model
{
    use HasFactory, SoftDeletes, SyncsTenantColumns;
    protected $fillable = ['code', 'name', 'level', 'type', 'treasury_type', 'account_category', 'detail_type', 'is_control', 'is_system', 'is_treasury', 'cost_center_required', 'floating_detail_required', 'nature', 'account_number', 'card_number', 'iban', 'branch', 'bank_name', 'currency_type', 'opening_balance', 'isActive', 'parent_id', 'organization_id', 'tenants_id', 'tenant_id', 'created_by'];

    protected $casts = [
        'is_control' => 'boolean',
        'is_system' => 'boolean',
        'is_treasury' => 'boolean',
        'cost_center_required' => 'boolean',
        'floating_detail_required' => 'boolean',
        'opening_balance' => 'decimal:2',
    ];

    public function vouchers()
    {
        return $this->hasMany(Voucher::class, 'account_id');
    }

    public function voucherItems()
    {
        return $this->hasMany(VoucherItems::class, 'account_id');
    }

    public function treasuryTypeText(): string
    {
        return match ($this->treasury_type) {
            'bank' => 'بانک',
            'cash' => 'صندوق / وجه نقد',
            'terminal' => 'کارتخوان / درگاه',
            default => 'حساب عملیاتی',
        };
    }
}
