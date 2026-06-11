<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;
use App\Traits\SyncsTenantColumns;

class PaymentTerminal extends Model
{

    use HasFactory, SoftDeletes, HasOrganizationFilter, SyncsTenantColumns;
    protected $fillable = [
        'account_id',
        'settlement_account_id',
        'terminal_type',      // pos, gateway, wallet ...
        'terminal_kind',
        'provider_name',      // Mellat, ZarinPal ...
        'terminal_number',    // شماره خاص پایانه یا merchantId
        'terminal_status',
        'settlement_cycle',
        'title',              // عنوان نمایشی
        'description',              // توضیحات
        'type',              // نوع
        'is_active',
        'tenants_id',
        'tenant_id',
        'organization_id',
        'created_by',
    ];

    public function account()
    {
        return $this->belongsTo(Accounts::class);
    }

    public function settlementAccount()
    {
        return $this->belongsTo(Accounts::class, 'settlement_account_id');
    }

    public function terminalKindText(): string
    {
        return match ($this->terminal_kind) {
            'gateway' => 'درگاه بانکی',
            'ussd' => 'درگاه USSD',
            default => 'کارتخوان',
        };
    }
}
