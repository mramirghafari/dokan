<?php

namespace App\Traits;

use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    protected function logActivity(string $action, string $description, ?Model $source = null, array $context = []): void
    {
        if ($source) {
            ActivityLogService::safeLogModel($action, $description, $source, $context);
        } else {
            ActivityLogService::safeLog($action, $description, null, $context);
        }
    }
}
