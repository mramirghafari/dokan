<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataExchangeRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'organization_id',
        'store_id',
        'user_id',
        'direction',
        'entity_type',
        'file_name',
        'status',
        'total_rows',
        'success_rows',
        'failed_rows',
        'options_json',
        'summary_json',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'options_json' => 'array',
        'summary_json' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
