<?php

namespace App\Services;

use App\Models\Log;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ActivityLogService
{
    public const ACTION_LABELS = [
        'create' => 'ایجاد',
        'update' => 'ویرایش',
        'delete' => 'حذف',
        'restore' => 'بازیابی',
        'forceDelete' => 'حذف دائم',
        'login' => 'ورود به سیستم',
        'logout' => 'خروج از سیستم',
        'failed_login' => 'ورود ناموفق',
    ];

    public const ACTION_BADGES = [
        'create' => 'bg-label-success',
        'update' => 'bg-label-info',
        'delete' => 'bg-label-danger',
        'restore' => 'bg-label-warning',
        'forceDelete' => 'bg-label-danger',
        'login' => 'bg-label-primary',
        'logout' => 'bg-label-secondary',
        'failed_login' => 'bg-label-warning',
    ];

    public const SECTION_LABELS = [
        'auth' => 'احراز هویت',
        'crm' => 'CRM',
        'accounting' => 'مالی و حسابداری',
        'warehouse' => 'انبار',
        'sales' => 'فروش و بازاریابی',
        'invoice' => 'فاکتور',
        'product' => 'محصول',
        'procurement' => 'تدارکات',
        'report' => 'گزارش',
        'settings' => 'تنظیمات',
        'users' => 'کاربران',
        'roles' => 'نقش و دسترسی',
        'organization' => 'سازمان',
        'delivery' => 'تحویل',
        'contracting' => 'پیمانکاری',
        'bi' => 'هوش تجاری',
        'system' => 'سیستم',
    ];

    public static function log(
        string $action,
        ?string $description = null,
        ?int $userId = null,
        array $context = []
    ): ?Log {
        if (!Schema::hasTable('logs')) {
            return null;
        }

        $user = $userId ? User::find($userId) : auth()->user();
        $tenantContext = app(TenantContextService::class);

        $attributes = [
            'ip' => Request::ip() ?: ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'),
            'user_id' => $userId ?? $user?->id,
            'action' => $action,
            'description' => $description ?? (self::ACTION_LABELS[$action] ?? $action),
            'tenant_id' => $context['tenant_id'] ?? $tenantContext->tenantId($user),
            'organization_id' => $context['organization_id'] ?? $tenantContext->organizationId($user),
            'section' => $context['section'] ?? null,
            'section_id' => $context['section_id'] ?? 0,
            'event_key' => $context['event_key'] ?? null,
            'source_type' => $context['source_type'] ?? null,
            'source_id' => $context['source_id'] ?? null,
            'payload_json' => $context['payload'] ?? $context['payload_json'] ?? null,
        ];

        foreach (array_keys($attributes) as $column) {
            if (!Schema::hasColumn('logs', $column)) {
                unset($attributes[$column]);
            }
        }

        return Log::create($attributes);
    }

    public static function safeLog(
        string $action,
        ?string $description = null,
        ?int $userId = null,
        array $context = []
    ): ?Log {
        try {
            return self::log($action, $description, $userId, $context);
        } catch (Throwable $exception) {
            Logger::warning('Activity log failed', [
                'action' => $action,
                'description' => $description,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public static function safeLogModel(string $action, string $description, ?Model $source = null, array $context = []): ?Log
    {
        try {
            return self::logModel($action, $description, $source, $context);
        } catch (Throwable $exception) {
            Logger::warning('Activity log failed', [
                'action' => $action,
                'description' => $description,
                'source_type' => $source ? get_class($source) : null,
                'source_id' => $source?->getKey(),
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    public static function logModel(string $action, string $description, ?Model $source = null, array $context = []): ?Log
    {
        if ($source) {
            $context['section'] = $context['section'] ?? class_basename($source);
            $context['section_id'] = $context['section_id'] ?? $source->getKey();
            $context['source_type'] = get_class($source);
            $context['source_id'] = $source->getKey();

            if (empty($context['tenant_id'])) {
                $context['tenant_id'] = $source->tenant_id ?? $source->tenants_id ?? null;
            }

            if (empty($context['organization_id']) && !empty($source->organization_id)) {
                $context['organization_id'] = $source->organization_id;
            }
        }

        return self::log($action, $description, null, $context);
    }

    public static function logLogin(User $user): ?Log
    {
        return self::safeLog('login', 'ورود به سیستم - ' . $user->name, $user->id, [
            'section' => 'auth',
            'event_key' => 'auth.login',
            'payload' => ['user_name' => $user->name],
        ]);
    }

    public static function logLogout(User $user): ?Log
    {
        return self::safeLog('logout', 'خروج از سیستم - ' . $user->name, $user->id, [
            'section' => 'auth',
            'event_key' => 'auth.logout',
            'payload' => ['user_name' => $user->name],
        ]);
    }

    public static function logFailedLogin(?string $attemptedLogin = null, ?User $user = null, ?string $reason = null): ?Log
    {
        $description = 'ورود ناموفق';

        if ($attemptedLogin) {
            $description .= ' - ' . $attemptedLogin;
        }

        if ($reason) {
            $description .= ' (' . $reason . ')';
        }

        $context = [
            'section' => 'auth',
            'event_key' => 'auth.failed_login',
            'payload' => [
                'attempted_login' => $attemptedLogin,
                'reason' => $reason,
            ],
        ];

        if ($user) {
            $tenantContext = app(TenantContextService::class);
            $context['tenant_id'] = $tenantContext->tenantId($user);
            $context['organization_id'] = $tenantContext->organizationId($user);
        }

        return self::safeLog('failed_login', $description, $user?->id, $context);
    }
}
