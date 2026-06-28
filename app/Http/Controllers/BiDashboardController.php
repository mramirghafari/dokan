<?php

namespace App\Http\Controllers;

use App\Models\DataExchangeRun;
use App\Services\BiDashboardService;
use App\Services\BiExecutiveDashboardService;
use App\Services\BiReconciliationService;
use App\Services\BiReportDeliveryService;
use App\Services\BiSelfServiceReportService;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RealRashid\SweetAlert\Facades\Alert;

class BiDashboardController extends Controller
{
    public function index(BiDashboardService $dashboardService)
    {
        return view('bi.dashboard', [
            'bi' => $dashboardService->dashboardForUser(Auth::user()),
        ]);
    }

    public function executive(BiExecutiveDashboardService $executiveService)
    {
        return view('bi.executive', [
            'dashboard' => $executiveService->executiveForUser(Auth::user()),
        ]);
    }

    public function cfo(BiExecutiveDashboardService $executiveService)
    {
        return view('bi.cfo', [
            'dashboard' => $executiveService->cfoForUser(Auth::user()),
        ]);
    }

    public function reconciliation(BiReconciliationService $reconciliationService)
    {
        return view('bi.reconciliation', [
            'page' => $reconciliationService->dashboardForUser(Auth::user()),
        ]);
    }

    public function runReconciliation(BiReconciliationService $reconciliationService, Request $request)
    {
        $data = $request->validate([
            'summary_date' => ['nullable', 'date'],
        ]);

        $result = $reconciliationService->runReconciliation(
            Auth::user(),
            $data['summary_date'] ?? null,
            true
        );

        $score = $result['health_score'];
        Alert::success(
            'مغایرت‌گیری انجام شد',
            'امتیاز سلامت: ' . $score . '% — ' . $result['aligned_count'] . ' از ' . count($result['checks']) . ' شاخص هم‌خوان.'
        );

        return redirect()->route('bi.reconciliation.index');
    }

    public function queueBackfill(BiReconciliationService $reconciliationService, Request $request)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'months' => ['nullable', 'integer', 'min:1', 'max:24'],
        ]);

        $to = $data['to'] ?? now()->toDateString();
        $months = (int) ($data['months'] ?? config('erp_scale.bi_reconciliation.default_backfill_months', 12));
        $from = $data['from'] ?? Carbon::parse($to)->subMonths($months)->toDateString();

        $queued = $reconciliationService->queueBackfill(Auth::user(), $from, $to);

        Alert::success(
            'Backfill در صف قرار گرفت',
            'بازه ' . $from . ' تا ' . $to . ' — شناسه log: ' . $queued['log_id']
        );

        return redirect()->route('bi.reconciliation.index');
    }

    public function refreshCrm(BiDashboardService $dashboardService)
    {
        $dashboardService->refreshCrmSummary(Auth::user());

        Alert::success('بروزرسانی شد', 'summary روزانه CRM برای BI محاسبه شد.');

        return redirect()->route('bi.dashboard.index');
    }

    public function refreshDataMart(BiSelfServiceReportService $reportService)
    {
        $log = $reportService->refreshEnterpriseDataMart(Auth::user());

        Alert::success('Data mart بروزرسانی شد', number_format($log->rows_count) . ' شاخص فروش، مالی، انبار و CRM محاسبه شد.');

        return redirect()->route('bi.dashboard.index');
    }

    public function reportBuilder(Request $request, BiSelfServiceReportService $reportService)
    {
        return view('bi.report_builder', [
            'builder' => $reportService->builderState(Auth::user(), $request->all(), $request->boolean('run')),
            'input' => $request->all(),
        ]);
    }

    public function storeTemplate(Request $request, BiSelfServiceReportService $reportService)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'dataset_key' => ['required', 'string', 'max:100'],
            'dimensions' => ['nullable', 'array'],
            'measures' => ['nullable', 'array'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'domain' => ['nullable', 'string', 'max:60'],
            'metric_key' => ['nullable'],
            'dimension_type' => ['nullable', 'string', 'max:80'],
            'chart_type' => ['nullable', 'string', 'max:40'],
            'visibility' => ['nullable', 'string', 'max:40'],
            'shared_role_id' => ['nullable', 'integer'],
            'view_mode' => ['nullable', 'string', 'max:40'],
            'pivot_row' => ['nullable', 'string', 'max:80'],
            'pivot_col' => ['nullable', 'string', 'max:80'],
            'analysis_mode' => ['nullable', 'string', 'max:80'],
        ]);

        $reportService->storeTemplate(Auth::user(), $data);

        Alert::success('قالب ذخیره شد', 'قالب گزارش self-service برای استفاده بعدی ذخیره شد.');

        return redirect()->route('bi.report-builder.index', ['dataset_key' => $data['dataset_key']]);
    }

    public function storeSchedule(Request $request, BiReportDeliveryService $deliveryService)
    {
        $data = $request->validate([
            'bi_report_template_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'frequency' => ['required', 'in:daily,weekly,monthly'],
            'delivery_format' => ['required', 'in:csv,html,pdf'],
            'recipients' => ['nullable', 'string', 'max:1000'],
            'channels' => ['nullable', 'array'],
            'channels.*' => ['in:panel,email,sms'],
            'next_run_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $deliveryService->createSchedule(Auth::user(), $data);

        Alert::success('زمان بندی ثبت شد', 'گزارش BI طبق قالب انتخابی زمان بندی شد.');

        return redirect()->route('bi.report-builder.index');
    }

    public function queueExport(Request $request, BiSelfServiceReportService $reportService)
    {
        $data = $request->validate([
            'dataset_key' => ['required', 'string', 'max:100'],
            'format' => ['required', 'in:csv,xlsx,pdf'],
            'dimensions' => ['nullable', 'array'],
            'measures' => ['nullable', 'array'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'domain' => ['nullable', 'string', 'max:60'],
            'metric_key' => ['nullable'],
            'dimension_type' => ['nullable', 'string', 'max:80'],
            'sort_by' => ['nullable', 'string', 'max:80'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
            'analysis_mode' => ['nullable', 'string', 'max:80'],
            'view_mode' => ['nullable', 'string', 'max:40'],
            'chart_type' => ['nullable', 'string', 'max:40'],
            'pivot_row' => ['nullable', 'string', 'max:80'],
            'pivot_col' => ['nullable', 'string', 'max:80'],
            'limit' => ['nullable', 'integer', 'min:10', 'max:500'],
        ]);

        $user = Auth::user();
        $preview = $reportService->runForUser($user, $data + ['run' => 1], false);

        if (empty($preview['security']['export_allowed'])) {
            throw ValidationException::withMessages([
                'format' => 'خروجی این گزارش برای نقش فعلی مجاز نیست.',
            ]);
        }

        $format = $data['format'];
        $run = $reportService->queueExportForUser($user, $data, $format);

        Alert::success('خروجی در صف قرار گرفت', 'فایل ' . strtoupper($format) . ' پس از آماده‌شدن قابل دانلود است (شناسه ' . $run->id . ').');

        return redirect()->route('bi.report-builder.index', array_merge(
            $request->except(['_token', 'format']),
            ['run' => 1]
        ));
    }

    public function downloadExport(DataExchangeRun $exchangeRun)
    {
        $user = Auth::user();

        if ((int) $user->isGod !== 1 && (int) $exchangeRun->user_id !== (int) $user->id) {
            abort(403);
        }

        if ($exchangeRun->entity_type !== 'bi_report' || $exchangeRun->direction !== 'export') {
            abort(404);
        }

        if ($exchangeRun->status !== 'completed' && $exchangeRun->status !== 'completed_with_errors') {
            Alert::warning('هنوز آماده نیست', 'خروجی در حال پردازش است. چند لحظه بعد دوباره تلاش کنید.');

            return redirect()->route('bi.report-builder.index');
        }

        $path = $exchangeRun->summary_json['storage_path'] ?? null;

        if (!$path || !Storage::disk('local')->exists($path)) {
            abort(404, 'فایل خروجی پیدا نشد.');
        }

        $format = $exchangeRun->summary_json['format'] ?? 'csv';
        $downloadName = $exchangeRun->summary_json['download_name'] ?? ('bi-report-' . $exchangeRun->id . '.' . $format);
        $mime = match ($format) {
            'pdf' => 'text/html',
            'xlsx' => 'application/vnd.ms-excel',
            default => 'text/csv',
        };

        return Storage::disk('local')->download($path, $downloadName, ['Content-Type' => $mime . '; charset=UTF-8']);
    }

    public function sharedReport(string $token, BiReportDeliveryService $deliveryService)
    {
        $delivery = $deliveryService->deliveryByToken($token);
        $deliveryService->auditDeliveryView($delivery, Auth::user());

        return response($deliveryService->render($delivery), 200, [
            'Content-Type' => $deliveryService->contentType($delivery),
        ]);
    }
}
