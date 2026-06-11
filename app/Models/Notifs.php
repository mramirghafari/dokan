<?php

namespace App\Models;

use App\Casts\changeDate;
use Illuminate\Database\Eloquent\Model;

class Notifs extends Model
{

    protected $table = 'notifs';
    protected $fillable = [
        'id',
        'user_id',
        'tenant_id',
        'title',
        'content',
        'status',
        'source',
        'severity',
        'reference_type',
        'reference_id',
        'alert_key',
        'scheduled_for',
        'sent_at',
    ];

    protected $casts = [
        'status' => 'boolean',
        'scheduled_for' => 'date',
        'sent_at' => 'datetime',
    ];
}
