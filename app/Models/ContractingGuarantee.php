<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractingGuarantee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contracting_project_id',
        'voucher_id',
        'tenant_id',
        'organization_id',
        'guarantee_number',
        'guarantee_type',
        'issuer',
        'beneficiary',
        'amount',
        'issue_date_en',
        'issue_date_fa',
        'expiry_date_en',
        'expiry_date_fa',
        'status',
        'description',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'issue_date_en' => 'date',
        'expiry_date_en' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(ContractingProject::class, 'contracting_project_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
