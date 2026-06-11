<?php

namespace App\Models;

use App\Services\PanelNotificationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log as Logger;
use Throwable;

class Log extends Model
{
    use HasFactory;
    protected $fillable = ['ip', 'user_id', 'tenant_id', 'organization_id', 'section', 'section_id', 'action', 'event_key', 'source_type', 'source_id', 'description', 'payload_json'];

    protected $casts = [
        'payload_json' => 'array',
    ];

    protected static function booted(): void
    {
        static::created(function (self $operationLog) {
            try {
                app(PanelNotificationService::class)->dispatchOperationLog($operationLog);
            } catch (Throwable $exception) {
                Logger::warning('Operation notification dispatch failed', [
                    'log_id' => $operationLog->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        });
    }

    public function user()
    {
        return $this->BelongsTo(User::class);
    }


    public function setDescriptionAttribute($description)
    {
        $this->attributes['description'] = mb_strtolower($description);
    }

    public function getCreatedAtAttribute($created_at)
    {
        $v1 = new \Hekmatinasser\Verta\Verta($created_at);
        $v1 = $v1->format('H:i:s - Y/m/d');
        return $v1;
    }
}
