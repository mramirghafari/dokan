<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyAssetEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_asset_id',
        'tenant_id',
        'organization_id',
        'event_type',
        'event_date_en',
        'event_date_fa',
        'from_store_id',
        'to_store_id',
        'from_employee_id',
        'to_employee_id',
        'status_before',
        'status_after',
        'amount',
        'title',
        'description',
        'created_by',
    ];

    protected $casts = [
        'event_date_en' => 'date',
        'amount' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(CompanyAsset::class, 'company_asset_id');
    }

    public function fromStore()
    {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    public function toStore()
    {
        return $this->belongsTo(Store::class, 'to_store_id');
    }

    public function fromEmployee()
    {
        return $this->belongsTo(Employee::class, 'from_employee_id');
    }

    public function toEmployee()
    {
        return $this->belongsTo(Employee::class, 'to_employee_id');
    }
}
