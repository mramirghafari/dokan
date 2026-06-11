<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxpayerSubmissionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'taxpayer_invoice_id',
        'tenant_id',
        'organization_id',
        'action',
        'status_before',
        'status_after',
        'reference_number',
        'request_payload',
        'response_payload',
        'message',
        'created_by',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(TaxpayerInvoice::class, 'taxpayer_invoice_id');
    }
}
