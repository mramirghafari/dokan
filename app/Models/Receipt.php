<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasOrganizationFilter;


class Receipt extends Model
{
    use HasFactory, SoftDeletes, HasOrganizationFilter;

    protected $fillable = ['user_id', 'tenant_id', 'organization_id', 'type', 'store_id', 'to_store_id', 'number', 'date_fa', 'date_en', 'sender', 'moeen', 'driver', 'scale_ticket_number', 'vehicle_plate', 'waybill_number', 'gross_weight', 'tare_weight', 'net_weight', 'weighing_notes', 'tozihat', 'document_status', 'approved_at', 'approved_by', 'canceled_at', 'canceled_by', 'cancellation_reason', 'return_source_receipt_id', 'return_reason', 'updated_at'];

    protected $casts = [
        'gross_weight' => 'decimal:3',
        'tare_weight' => 'decimal:3',
        'net_weight' => 'decimal:3',
        'approved_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
    public function depots()
    {
        return $this->hasMany(Depot::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function returnSourceReceipt()
    {
        return $this->belongsTo(self::class, 'return_source_receipt_id');
    }

    public function returnReceipts()
    {
        return $this->hasMany(self::class, 'return_source_receipt_id');
    }
}
