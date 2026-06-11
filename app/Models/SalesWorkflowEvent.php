<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesWorkflowEvent extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'organization_id', 'pishfactor_id', 'event_type', 'from_status', 'to_status', 'order_amount', 'credit_status', 'description', 'created_by'];

    protected $casts = [
        'order_amount' => 'decimal:2',
    ];

    public function pishfactor()
    {
        return $this->belongsTo(Pishfactor::class, 'pishfactor_id');
    }
}
