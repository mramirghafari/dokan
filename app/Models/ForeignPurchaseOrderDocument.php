<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForeignPurchaseOrderDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'foreign_purchase_order_id',
        'tenant_id',
        'organization_id',
        'document_type',
        'document_number',
        'document_date_en',
        'document_date_fa',
        'reference_number',
        'file_path',
        'description',
    ];

    protected $casts = [
        'document_date_en' => 'date',
    ];

    public function foreignPurchaseOrder()
    {
        return $this->belongsTo(ForeignPurchaseOrder::class);
    }
}
