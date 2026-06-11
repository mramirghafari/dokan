<?php

namespace App\Http\Controllers;

use App\Models\Abortion;
use App\Models\Employee;
use App\Models\ManagementReportSchedule;
use App\Models\ManagementReportTemplate;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Repair;
use App\Models\Stock;
use App\Models\Store;
use App\Models\Unit;
use App\Services\ManagementReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class ReportController extends Controller
{
    public function management(Request $request, ManagementReportService $service)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : now()->endOfMonth();
        $templates = $this->scopedManagementTemplates($request)->latest()->get();
        $schedules = $this->scopedManagementSchedules($request)->with('template')->latest()->limit(10)->get();
        $selectedTemplate = $templates->firstWhere('id', (int) $request->input('template_id'));
        $report = $service->build($request->user(), $startDate, $endDate, [
            'template_key' => optional($selectedTemplate)->template_key,
            'template_title' => optional($selectedTemplate)->title,
        ]);

        return view('reports.management', compact('report', 'startDate', 'endDate', 'templates', 'schedules', 'selectedTemplate'));
    }

    public function managementSnapshot(Request $request, ManagementReportService $service)
    {
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : now()->endOfMonth();
        $report = $service->build($request->user(), $startDate, $endDate);
        $service->saveSnapshot($request->user(), $report, $request->only(['start_date', 'end_date', 'template_id']));

        Alert::success('ثبت شد', 'snapshot گزارش مدیریتی ذخیره شد.');

        return redirect()->route('reports.management', $request->only(['start_date', 'end_date']));
    }

    public function managementTemplateStore(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'template_key' => ['nullable', 'string', 'max:80'],
            'sections' => ['nullable', 'array'],
            'default_export_format' => ['nullable', 'in:excel,pdf,html'],
            'is_shared' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        ManagementReportTemplate::create(array_merge($this->managementScopePayload($request), [
            'template_key' => $data['template_key'] ?: 'custom_' . now()->format('YmdHis'),
            'title' => $data['title'],
            'sections_json' => $data['sections'] ?? ['financial', 'sales', 'warehouse', 'production', 'distribution'],
            'filters_json' => $request->only(['start_date', 'end_date']),
            'chart_settings_json' => ['comparison' => true, 'compact_cards' => true],
            'default_export_format' => $data['default_export_format'] ?? 'excel',
            'is_shared' => (bool) ($data['is_shared'] ?? false),
            'is_active' => true,
            'notes' => $data['notes'] ?? null,
        ]));

        Alert::success('ثبت شد', 'قالب گزارش مدیریتی ذخیره شد.');

        return redirect()->route('reports.management', $request->only(['start_date', 'end_date']));
    }

    public function managementScheduleStore(Request $request)
    {
        $data = $request->validate([
            'management_report_template_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:190'],
            'frequency' => ['required', 'in:daily,weekly,monthly'],
            'delivery_format' => ['required', 'in:excel,pdf,html'],
            'recipients' => ['nullable', 'string'],
            'next_run_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        ManagementReportSchedule::create(array_merge($this->managementScopePayload($request), [
            'management_report_template_id' => $data['management_report_template_id'] ?? null,
            'title' => $data['title'],
            'frequency' => $data['frequency'],
            'delivery_format' => $data['delivery_format'],
            'recipients_json' => collect(explode(',', (string) ($data['recipients'] ?? '')))->map(fn($item) => trim($item))->filter()->values()->all(),
            'filters_json' => $request->only(['start_date', 'end_date']),
            'next_run_at' => $data['next_run_at'] ?? null,
            'is_active' => true,
            'notes' => $data['notes'] ?? null,
        ]));

        Alert::success('ثبت شد', 'زمان بندی گزارش مدیریتی ذخیره شد.');

        return redirect()->route('reports.management', $request->only(['start_date', 'end_date']));
    }

    public function managementExport(Request $request, ManagementReportService $service, string $format)
    {
        abort_unless(in_array($format, ['excel', 'pdf', 'html'], true), 404);

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->startOfMonth();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : now()->endOfMonth();
        $report = $service->build($request->user(), $startDate, $endDate);

        if ($format === 'excel') {
            $csv = $this->managementCsv($report);

            return response("\xEF\xBB\xBF" . $csv, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="management-dashboard-' . now()->format('Ymd-His') . '.csv"',
            ]);
        }

        return response($this->managementPrintableHtml($report), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    private function scopedManagementTemplates(Request $request)
    {
        $query = ManagementReportTemplate::query()->where('is_active', true);
        $user = $request->user();

        if ((int) $user->isGod !== 1) {
            $tenantId = $user->tenant_id ?: $user->tenants_id;
            $query->where(function ($scope) use ($tenantId, $user) {
                $scope->where('tenant_id', $tenantId)
                    ->orWhere('organization_id', is_numeric($user->organization_id) ? $user->organization_id : null)
                    ->orWhere('is_shared', true);
            });
        }

        return $query;
    }

    private function scopedManagementSchedules(Request $request)
    {
        $query = ManagementReportSchedule::query();
        $user = $request->user();

        if ((int) $user->isGod !== 1) {
            $tenantId = $user->tenant_id ?: $user->tenants_id;
            $query->where(function ($scope) use ($tenantId, $user) {
                $scope->where('tenant_id', $tenantId)
                    ->orWhere('organization_id', is_numeric($user->organization_id) ? $user->organization_id : null);
            });
        }

        return $query;
    }

    private function managementScopePayload(Request $request): array
    {
        $user = $request->user();

        return [
            'tenant_id' => $user->tenant_id ?: $user->tenants_id,
            'organization_id' => is_numeric($user->organization_id) ? $user->organization_id : null,
            'created_by' => $user->id,
        ];
    }

    private function managementCsv(array $report): string
    {
        $rows = [
            ['شاخص', 'مقدار فعلی', 'دوره قبل', 'اختلاف', 'درصد تغییر'],
        ];

        foreach ($report['comparison'] as $key => $row) {
            $rows[] = [$key, $row['current'], $row['previous'], $row['delta'], $row['percent'] ?? '-'];
        }

        return collect($rows)->map(fn($row) => collect($row)->map(fn($cell) => '"' . str_replace('"', '""', (string) $cell) . '"')->implode(','))->implode("\n");
    }

    private function managementPrintableHtml(array $report): string
    {
        $rows = collect($report['comparison'])->map(function ($row, $key) {
            return '<tr><td>' . e($key) . '</td><td>' . number_format((float) $row['current']) . '</td><td>' . number_format((float) $row['previous']) . '</td><td>' . number_format((float) $row['delta']) . '</td><td>' . e($row['percent'] ?? '-') . '</td></tr>';
        })->implode('');

        return '<!doctype html><html dir="rtl" lang="fa"><head><meta charset="utf-8"><title>داشبورد مدیریتی</title><style>body{font-family:tahoma,sans-serif;margin:32px}table{width:100%;border-collapse:collapse}td,th{border:1px solid #ddd;padding:10px;text-align:right}th{background:#f5f5f5}</style></head><body><h1>داشبورد مدیریتی یکپارچه</h1><p>از ' . e($report['period']['start']->format('Y-m-d')) . ' تا ' . e($report['period']['end']->format('Y-m-d')) . '</p><table><thead><tr><th>شاخص</th><th>فعلی</th><th>دوره قبل</th><th>اختلاف</th><th>درصد</th></tr></thead><tbody>' . $rows . '</tbody></table><script>window.print()</script></body></html>';
    }

    public function warehouse()
    {
        $stores = Store::where('isActive', 1)->get();
        $organizations = Organization::where('isActive', 1)->get();
        return view('reports.warehouses', compact('stores', 'organizations'));
    }

    public function warehouseFilter(Request $request)
    {
        $stores = Store::where('isActive', 1)->get();
        $organizations = Organization::where('isActive', 1)->get();

        $organizationField = $request['organization'];
        $storeField = $request['store'];
        $statusField = $request['status'];
        $typeField = $request['type'];


        //products --------------------------------
        if ($request['type'] == "products") {
            $products = Product::where('isActive', 1);

            if ($request->has('organization')) {
                if ($request->organization != "all") {
                    $products->where('organization_id', $request->organization);
                }
            }

            if ($request->has('store')) {
                if ($request->store != "all") {
                    $products->where('store_id', $request->store);
                }
            }

            if ($request->has('status')) {
                if ($request->status != "all") {
                    if ($request->status != "off") {
                        $products->where('entity', 0);
                    } else {
                        $products->where('entity', '>', 0);
                    }
                }
            }
            $products->get();

            $products = collect($products->get());
            return view('reports.warehouses-filter', compact('products', 'stores', 'organizations', 'organizationField', 'typeField', 'statusField', 'storeField'));
        }
        //End Products ---------------------------

        //stocks --------------------------------
        if ($request['type'] == "stocks") {
            $stocks = Stock::where('isActive', 1);

            if ($request->has('organization')) {
                if ($request->organization != "all") {
                    $stocks->where('organization_id', $request->organization);
                }
            }

            if ($request->has('store')) {
                if ($request->store != "all") {
                    $stocks->where('store_id', $request->store);
                }
            }

            if ($request->has('status')) {
                if ($request->status != "all") {
                    if ($request->status != "off") {
                        $stocks->where('entity', 0);
                    } else {
                        $stocks->where('entity', '>', 0);
                    }
                }
            }
            $stocks->get();

            $stocks = collect($stocks->get());
            return view('reports.warehouses-filter', compact('stocks', 'stores', 'organizations', 'organizationField', 'typeField', 'statusField', 'storeField'));
        }
        //End stocks ---------------------------


        //abortions --------------------------------
        if ($request['type'] == "abortions") {
            $abortions = Abortion::where('isActive', 1);

            if ($request->has('organization')) {
                if ($request->organization != "all") {
                    $abortions->where('organization_id', $request->organization);
                }
            }

            if ($request->has('store')) {
                if ($request->store != "all") {
                    $abortions->where('store_id', $request->store);
                }
            }

            if ($request->has('status')) {
                if ($request->status != "all") {
                    if ($request->status != "off") {
                        $abortions->where('entity', 0);
                    } else {
                        $abortions->where('entity', '>', 0);
                    }
                }
            }
            $abortions->get();

            $abortions = collect($abortions->get());
            return view('reports.warehouses-filter', compact('abortions', 'stores', 'organizations', 'organizationField', 'typeField', 'statusField', 'storeField'));
        }
        //End abortions ---------------------------


        //repairs --------------------------------
        if ($request['type'] == "repairs") {
            $repairs = Repair::where('isActive', 1);

            if ($request->has('organization')) {
                if ($request->organization != "all") {
                    $repairs->where('organization_id', $request->organization);
                }
            }

            if ($request->has('store')) {
                if ($request->store != "all") {
                    $repairs->where('store_id', $request->store);
                }
            }

            if ($request->has('status')) {
                if ($request->status != "all") {
                    if ($request->status != "off") {
                        $repairs->where('entity', 0);
                    } else {
                        $repairs->where('entity', '>', 0);
                    }
                }
            }
            $repairs->get();

            $repairs = collect($repairs->get());
            return view('reports.warehouses-filter', compact('repairs', 'stores', 'organizations', 'organizationField', 'typeField', 'statusField', 'storeField'));
        }
        //End repairs ---------------------------


        //all --------------------------------
        if ($request['type'] == "all") {
            $repairs = Repair::where('isActive', 1);
            $abortions = Abortion::where('isActive', 1);
            $stocks = Stock::where('isActive', 1);
            $products = Product::where('isActive', 1);

            if ($request->has('organization')) {
                if ($request->organization != "all") {
                    $repairs->where('organization_id', $request->organization);
                    $abortions->where('organization_id', $request->organization);
                    $stocks->where('organization_id', $request->organization);
                    $products->where('organization_id', $request->organization);
                }
            }

            if ($request->has('store')) {
                if ($request->store != "all") {
                    $repairs->where('store_id', $request->store);
                    $abortions->where('store_id', $request->store);
                    $stocks->where('store_id', $request->store);
                    $products->where('store_id', $request->store);
                }
            }

            if ($request->has('status')) {
                if ($request->status != "all") {
                    if ($request->status != "off") {
                        $repairs->where('entity', 0);
                        $abortions->where('entity', 0);
                        $stocks->where('entity', 0);
                        $products->where('entity', 0);
                    } else {
                        $repairs->where('entity', '>', 0);
                        $abortions->where('entity', '>', 0);
                        $stocks->where('entity', '>', 0);
                        $products->where('entity', '>', 0);
                    }
                }
            }
            $repairs->get();
            $abortions->get();
            $stocks->get();
            $products->get();

            $repairs = collect($repairs->get());
            $abortions = collect($abortions->get());
            $stocks = collect($stocks->get());
            $products = collect($products->get());

            $all = 1;

            return view('reports.warehouses-filter', compact('repairs', 'abortions', 'stocks', 'products', 'all', 'stores', 'organizations', 'organizationField', 'typeField', 'statusField', 'storeField'));
        }
        //End all ---------------------------

    }
}
