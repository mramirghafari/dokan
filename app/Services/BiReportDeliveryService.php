<?php

namespace App\Services;

use App\Models\BiReportDelivery;
use App\Models\BiReportSchedule;
use App\Models\BiReportTemplate;
use App\Models\Notifs;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BiReportDeliveryService
{
    public function __construct(private BiSelfServiceReportService $reportService) {}

    public function createSchedule($user, array $data): BiReportSchedule
    {
        $template = $this->templateForUser($user, (int) $data['bi_report_template_id']);
        $nextRun = !empty($data['next_run_at']) ? Carbon::parse($data['next_run_at']) : now();
        $deliveryFormat = $this->format($data['delivery_format'] ?? 'csv');

        if (in_array($deliveryFormat, ['csv', 'pdf'], true) && $this->templateNeedsSensitiveExportPermission($template) && !$this->reportService->canExportSensitiveBi($user)) {
            throw ValidationException::withMessages(['delivery_format' => 'خروجی گرفتن از گزارش BI حساس برای این نقش مجاز نیست.']);
        }

        return BiReportSchedule::create([
            'tenant_id' => $this->tenantId($user),
            'organization_id' => $this->organizationId($user),
            'bi_report_template_id' => $template->id,
            'title' => trim($data['title'] ?? '') ?: $template->title,
            'frequency' => $this->frequency($data['frequency'] ?? 'daily'),
            'delivery_format' => $deliveryFormat,
            'recipients' => $this->recipients($data['recipients'] ?? ''),
            'channels' => $this->channels($data['channels'] ?? ['panel']),
            'next_run_at' => $nextRun,
            'is_active' => true,
            'created_by' => $user->id,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function runDue(?int $onlyUserId = null, int $limit = 100, bool $dryRun = false): array
    {
        $query = BiReportSchedule::query()
            ->with('template')
            ->where('is_active', true)
            ->where(function ($scope) {
                $scope->whereNull('next_run_at')->orWhere('next_run_at', '<=', now());
            })
            ->oldest('next_run_at')
            ->limit(max(1, min(500, $limit)));

        if ($onlyUserId) {
            $query->where('created_by', $onlyUserId);
        }

        $schedules = $query->get();
        $delivered = 0;
        $failed = 0;

        foreach ($schedules as $schedule) {
            if (!$dryRun) {
                $delivery = $this->deliver($schedule);
                $delivery->status === 'success' ? $delivered++ : $failed++;
            }
        }

        return ['due' => $schedules->count(), 'delivered' => $delivered, 'failed' => $failed];
    }

    public function deliver(BiReportSchedule $schedule): BiReportDelivery
    {
        $template = $schedule->template;
        $user = User::find($schedule->created_by) ?: $this->systemContext($schedule);

        if (!$template) {
            $delivery = $this->failedDelivery($schedule, 'قالب گزارش BI پیدا نشد.');
            $this->advanceSchedule($schedule, 'failed');

            return $delivery;
        }

        try {
            $input = array_merge($template->filters ?: [], [
                'dataset_key' => $template->dataset_key,
                'dimensions' => $template->dimensions ?: ['summary_date', 'metric_key'],
                'measures' => $template->measures ?: ['value_sum'],
                'limit' => 500,
                'run' => 1,
            ]);
            $result = $this->reportService->runForUser($user, $input, true);
            $delivery = BiReportDelivery::create([
                'bi_report_schedule_id' => $schedule->id,
                'bi_report_template_id' => $template->id,
                'tenant_id' => $schedule->tenant_id,
                'organization_id' => $schedule->organization_id,
                'delivery_token' => Str::random(48),
                'title' => $schedule->title,
                'dataset_key' => $template->dataset_key,
                'delivery_format' => $schedule->delivery_format,
                'recipient_count' => count($schedule->recipients ?: []),
                'channels' => $schedule->channels ?: ['panel'],
                'filters' => $template->filters ?: [],
                'output_snapshot' => $this->snapshot($result),
                'row_count' => $result['rows']->count(),
                'status' => 'success',
                'message' => 'Scheduled BI report generated from stored template.',
                'generated_by' => $schedule->created_by,
                'generated_at' => now(),
                'expires_at' => now()->addDays(30),
            ]);

            $this->notifyRecipients($schedule, $delivery);
            $this->advanceSchedule($schedule, 'success');

            return $delivery;
        } catch (\Throwable $exception) {
            $delivery = $this->failedDelivery($schedule, $exception->getMessage(), $template);
            $this->advanceSchedule($schedule, 'failed');

            return $delivery;
        }
    }

    public function deliveryByToken(string $token): BiReportDelivery
    {
        return BiReportDelivery::query()
            ->where('delivery_token', $token)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->firstOrFail();
    }

    public function auditDeliveryView(BiReportDelivery $delivery, $user = null): void
    {
        \App\Models\BiReportRun::create([
            'template_id' => $delivery->bi_report_template_id,
            'tenant_id' => $delivery->tenant_id,
            'organization_id' => $delivery->organization_id,
            'dataset_key' => $delivery->dataset_key,
            'requested_by' => $user?->id,
            'status' => 'viewed',
            'row_count' => $delivery->row_count,
            'filters' => $delivery->filters ?: [],
            'output_summary' => [
                'delivery_id' => $delivery->id,
                'delivery_format' => $delivery->delivery_format,
                'shared_link' => true,
                'security' => $delivery->output_snapshot['security'] ?? [],
            ],
            'started_at' => now(),
            'finished_at' => now(),
            'message' => 'Secure shared BI report delivery viewed.',
        ]);
    }

    public function render(BiReportDelivery $delivery): string
    {
        return $delivery->delivery_format === 'csv' ? $this->csv($delivery) : $this->html($delivery);
    }

    public function contentType(BiReportDelivery $delivery): string
    {
        return $delivery->delivery_format === 'csv' ? 'text/csv; charset=UTF-8' : 'text/html; charset=UTF-8';
    }

    private function templateForUser($user, int $templateId): BiReportTemplate
    {
        $query = BiReportTemplate::query()->where('is_active', true)->whereKey($templateId);

        if ((int) $user->isGod !== 1) {
            $tenantId = $this->tenantId($user);
            $organizationId = $this->organizationId($user);
            $roleIds = method_exists($user, 'roles')
                ? $user->roles()->pluck('roles.id')->map(fn ($id) => (int) $id)->all()
                : [];

            $query->where(function ($scope) use ($user, $tenantId, $organizationId, $roleIds) {
                $scope->where('created_by', $user->id)->orWhere(function ($tenantScope) use ($tenantId) {
                    $tenantScope->where('visibility', 'tenant')->where('tenant_id', $tenantId);
                });

                if ($organizationId) {
                    $scope->orWhere(function ($organizationScope) use ($organizationId) {
                        $organizationScope->where('visibility', 'organization')->where('organization_id', $organizationId);
                    });
                }

                if ($roleIds !== []) {
                    $scope->orWhere(function ($roleScope) use ($roleIds) {
                        $roleScope->where('visibility', 'role')->whereIn('shared_role_id', $roleIds);
                    });
                }
            });
        }

        $template = $query->first();
        if (!$template) {
            throw ValidationException::withMessages(['bi_report_template_id' => 'قالب BI معتبر نیست.']);
        }

        return $template;
    }

    private function snapshot(array $result): array
    {
        $columns = collect($result['columns'])->map(fn($column) => [
            'key' => $column['key'],
            'label' => $column['label'],
            'type' => $column['type'],
        ])->values()->all();

        return [
            'columns' => $columns,
            'totals' => $result['totals'],
            'analysis_mode' => $result['analysis_mode'] ?? 'none',
            'analysis_insights' => $result['analysis_insights'] ?? [],
            'security' => $result['security'] ?? [],
            'rows' => $result['rows']->take(500)->map(function ($row) use ($columns) {
                $payload = [];
                foreach ($columns as $column) {
                    $payload[$column['key']] = $row->{$column['key']} ?? null;
                }

                return $payload;
            })->values()->all(),
        ];
    }

    private function notifyRecipients(BiReportSchedule $schedule, BiReportDelivery $delivery): void
    {
        if (!in_array('panel', $schedule->channels ?: ['panel'], true)) {
            return;
        }

        foreach ($this->panelRecipients($schedule) as $user) {
            Notifs::firstOrCreate([
                'user_id' => $user->id,
                'alert_key' => 'bi:scheduled-report:' . $delivery->id . ':' . $user->id,
            ], [
                'tenant_id' => $schedule->tenant_id,
                'title' => 'گزارش BI آماده شد: ' . $delivery->title,
                'content' => 'Snapshot زمان بندی شده گزارش BI با لینک امن 30 روزه ساخته شد.',
                'status' => false,
                'source' => 'bi_report_schedule',
                'severity' => 'info',
                'reference_type' => 'bi_report_delivery',
                'reference_id' => $delivery->id,
                'scheduled_for' => now()->toDateString(),
                'sent_at' => now(),
            ]);
        }
    }

    private function panelRecipients(BiReportSchedule $schedule)
    {
        return User::query()
            ->where('isActive', 1)
            ->where(function ($query) use ($schedule) {
                $query->where('id', $schedule->created_by)->orWhere('isGod', 1);

                if ($schedule->tenant_id) {
                    $query->orWhere(function ($tenantQuery) use ($schedule) {
                        $tenantQuery->where('isAdmin', 1)
                            ->where(function ($scope) use ($schedule) {
                                $scope->where('tenant_id', $schedule->tenant_id)->orWhere('tenants_id', $schedule->tenant_id);
                            });

                        if ($schedule->organization_id) {
                            $tenantQuery->where(function ($scope) use ($schedule) {
                                $scope->where('organization_id', $schedule->organization_id)
                                    ->orWhere('organization_id', 'like', '%"' . $schedule->organization_id . '"%')
                                    ->orWhere('organization_id', 'like', '%[' . $schedule->organization_id . ']%');
                            });
                        }
                    });
                }
            })
            ->limit(50)
            ->get();
    }

    private function failedDelivery(BiReportSchedule $schedule, string $message, ?BiReportTemplate $template = null): BiReportDelivery
    {
        return BiReportDelivery::create([
            'bi_report_schedule_id' => $schedule->id,
            'bi_report_template_id' => $template?->id ?: $schedule->bi_report_template_id,
            'tenant_id' => $schedule->tenant_id,
            'organization_id' => $schedule->organization_id,
            'delivery_token' => Str::random(48),
            'title' => $schedule->title,
            'dataset_key' => $template?->dataset_key ?: 'unknown',
            'delivery_format' => $schedule->delivery_format,
            'recipient_count' => count($schedule->recipients ?: []),
            'channels' => $schedule->channels ?: ['panel'],
            'filters' => $template?->filters ?: [],
            'output_snapshot' => [],
            'row_count' => 0,
            'status' => 'failed',
            'message' => Str::limit($message, 1000),
            'generated_by' => $schedule->created_by,
            'generated_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);
    }

    private function templateNeedsSensitiveExportPermission(BiReportTemplate $template): bool
    {
        $filters = $template->filters ?: [];
        $domain = $filters['domain'] ?? null;
        $metric = $filters['metric_key'] ?? null;

        if (in_array((string) $domain, ['finance', 'treasury', 'payroll'], true)) {
            return true;
        }

        foreach ($this->recipients($metric) as $value) {
            foreach (['amount', 'debit', 'credit', 'pay', 'salary', 'cash', 'voucher', 'balance'] as $needle) {
                if (str_contains(strtolower((string) $value), $needle)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function advanceSchedule(BiReportSchedule $schedule, string $status): void
    {
        $base = $schedule->next_run_at && $schedule->next_run_at->isFuture() ? $schedule->next_run_at->copy() : now();
        $nextRun = match ($schedule->frequency) {
            'weekly' => $base->addWeek(),
            'monthly' => $base->addMonthNoOverflow(),
            default => $base->addDay(),
        };

        $schedule->update(['last_run_at' => now(), 'last_status' => $status, 'next_run_at' => $nextRun]);
    }

    private function csv(BiReportDelivery $delivery): string
    {
        $snapshot = $delivery->output_snapshot ?: [];
        $columns = $snapshot['columns'] ?? [];
        $rows = $snapshot['rows'] ?? [];
        $csvRows = [collect($columns)->pluck('label')->all()];

        foreach ($rows as $row) {
            $csvRows[] = collect($columns)->map(fn($column) => $row[$column['key']] ?? '')->all();
        }

        return "\xEF\xBB\xBF" . collect($csvRows)->map(fn($row) => collect($row)->map(fn($cell) => '"' . str_replace('"', '""', (string) $cell) . '"')->implode(','))->implode("\n");
    }

    private function html(BiReportDelivery $delivery): string
    {
        $snapshot = $delivery->output_snapshot ?: [];
        $columns = $snapshot['columns'] ?? [];
        $rows = $snapshot['rows'] ?? [];
        $header = collect($columns)->map(fn($column) => '<th>' . e($column['label']) . '</th>')->implode('');
        $body = collect($rows)->map(function ($row) use ($columns) {
            $cells = collect($columns)->map(fn($column) => '<td>' . e($row[$column['key']] ?? '-') . '</td>')->implode('');

            return '<tr>' . $cells . '</tr>';
        })->implode('');

        return '<!doctype html><html dir="rtl" lang="fa"><head><meta charset="utf-8"><title>' . e($delivery->title) . '</title><style>body{font-family:tahoma,sans-serif;margin:32px}table{width:100%;border-collapse:collapse}td,th{border:1px solid #ddd;padding:8px;text-align:right}th{background:#f5f5f5}</style></head><body><h1>' . e($delivery->title) . '</h1><p>تاریخ تولید: ' . e(optional($delivery->generated_at)->format('Y-m-d H:i')) . '</p><table><thead><tr>' . $header . '</tr></thead><tbody>' . $body . '</tbody></table></body></html>';
    }

    private function recipients($value): array
    {
        return collect(is_array($value) ? $value : explode(',', (string) $value))->map(fn($item) => trim((string) $item))->filter()->unique()->values()->all();
    }

    private function channels($value): array
    {
        $channels = collect(is_array($value) ? $value : explode(',', (string) $value))
            ->filter(fn($item) => in_array($item, ['panel', 'email', 'sms'], true))
            ->unique()
            ->values();

        return $channels->isNotEmpty() ? $channels->all() : ['panel'];
    }

    private function frequency(string $frequency): string
    {
        return in_array($frequency, ['daily', 'weekly', 'monthly'], true) ? $frequency : 'daily';
    }

    private function format(string $format): string
    {
        return in_array($format, ['csv', 'html', 'pdf'], true) ? $format : 'csv';
    }

    private function systemContext(BiReportSchedule $schedule): object
    {
        return (object) ['id' => 0, 'isGod' => 1, 'tenant_id' => $schedule->tenant_id, 'tenants_id' => $schedule->tenant_id, 'organization_id' => $schedule->organization_id];
    }

    private function tenantId($user): ?int
    {
        return $user->tenant_id ?: $user->tenants_id ?: null;
    }

    private function organizationId($user): ?int
    {
        return is_numeric($user->organization_id) ? (int) $user->organization_id : null;
    }
}
