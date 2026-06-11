<!DOCTYPE html>
<html lang="fa" class="light-style layout-menu-fixed" dir="rtl" data-theme="theme-default"
    data-assets-path="../../assets/" data-template="vertical-menu-template-free">

<head>
    @include('sections.head')
    <title>گزارش ساز BI - دکان دارمینو</title>
</head>

<body>
    @php
        $datasets = $builder['datasets'];
        $dataset = $builder['selected_dataset'];
        $result = $builder['result'];
        $dimensions = collect(optional($dataset)->dimensions ?? []);
        $measures = collect(optional($dataset)->measures ?? []);
        $filters = $result['filters'];
        $selectedDimensions = $result['selected_dimensions'];
        $selectedMeasures = $result['selected_measures'];
        $metricValue = $input['metric_key'] ?? ($filters['metric_key'] ?? []);
        $metricValue = is_array($metricValue) ? implode(',', $metricValue) : $metricValue;
    @endphp
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.navbar')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">BI /</span> گزارش ساز self-service
                                </h4>
                                <div class="text-muted">Dataset، dimension، measure و فیلترها فقط از قرارداد whitelist
                                    شده BI اجرا می شوند.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-secondary" href="{{ route('bi.dashboard.index') }}">داشبورد
                                    BI</a>
                                <form method="POST" action="{{ route('bi.dashboard.refresh-data-mart') }}">
                                    @csrf
                                    <button class="btn btn-primary" type="submit">Refresh data mart</button>
                                </form>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-xl-4">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">ساخت گزارش</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="GET" action="{{ route('bi.report-builder.index') }}">
                                            <input type="hidden" name="run" value="1">
                                            <div class="mb-3">
                                                <label class="form-label">Dataset</label>
                                                <select class="form-select" name="dataset_key"
                                                    onchange="this.form.submit()">
                                                    @foreach ($datasets as $item)
                                                        <option value="{{ $item->dataset_key }}"
                                                            @selected(optional($dataset)->dataset_key === $item->dataset_key)>
                                                            {{ $item->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">ابعاد</label>
                                                <select class="form-select" name="dimensions[]" multiple size="5">
                                                    @foreach ($dimensions as $dimension)
                                                        <option value="{{ $dimension['key'] }}"
                                                            @selected(in_array($dimension['key'], $selectedDimensions, true))>
                                                            {{ $dimension['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Measureها</label>
                                                <select class="form-select" name="measures[]" multiple size="4">
                                                    @foreach ($measures as $measure)
                                                        <option value="{{ $measure['key'] }}"
                                                            @selected(in_array($measure['key'], $selectedMeasures, true))>
                                                            {{ $measure['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">از تاریخ</label>
                                                    <input class="form-control" type="date" name="date_from"
                                                        value="{{ $input['date_from'] ?? '' }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">تا تاریخ</label>
                                                    <input class="form-control" type="date" name="date_to"
                                                        value="{{ $input['date_to'] ?? '' }}">
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <label class="form-label">شاخص ها</label>
                                                <input class="form-control" name="metric_key"
                                                    value="{{ $metricValue }}"
                                                    placeholder="مثلا sales_order_count,crm_calls_today">
                                            </div>

                                            <div class="row g-2 mt-1">
                                                <div class="col-md-6">
                                                    <label class="form-label">Sort</label>
                                                    <select class="form-select" name="sort_by">
                                                        @foreach (array_merge($selectedDimensions, $selectedMeasures) as $column)
                                                            <option value="{{ $column }}"
                                                                @selected(($input['sort_by'] ?? '') === $column)>{{ $column }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">جهت</label>
                                                    <select class="form-select" name="sort_direction">
                                                        <option value="desc" @selected(($input['sort_direction'] ?? 'desc') === 'desc')>نزولی
                                                        </option>
                                                        <option value="asc" @selected(($input['sort_direction'] ?? '') === 'asc')>صعودی
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <label class="form-label">حالت تحلیل</label>
                                                <select class="form-select" name="analysis_mode">
                                                    @foreach ($result['analysis_modes'] as $modeKey => $modeLabel)
                                                        <option value="{{ $modeKey }}"
                                                            @selected($result['analysis_mode'] === $modeKey)>{{ $modeLabel }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="row g-2 mt-1">
                                                <div class="col-md-6">
                                                    <label class="form-label">نمایش</label>
                                                    <select class="form-select" name="view_mode">
                                                        @foreach ($builder['view_modes'] as $modeKey => $modeLabel)
                                                            <option value="{{ $modeKey }}" @selected(($input['view_mode'] ?? $result['view_mode'] ?? 'table') === $modeKey)>{{ $modeLabel }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">نمودار</label>
                                                    <select class="form-select" name="chart_type">
                                                        @foreach ($builder['chart_types'] as $typeKey => $typeLabel)
                                                            <option value="{{ $typeKey }}" @selected(($input['chart_type'] ?? $result['chart_type'] ?? 'table') === $typeKey)>{{ $typeLabel }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row g-2 mt-1">
                                                <div class="col-md-6">
                                                    <label class="form-label">Pivot سطر</label>
                                                    <select class="form-select" name="pivot_row">
                                                        <option value="">—</option>
                                                        @foreach ($dimensions as $dimension)
                                                            <option value="{{ $dimension['key'] }}" @selected(($input['pivot_row'] ?? '') === $dimension['key'])>{{ $dimension['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Pivot ستون</label>
                                                    <select class="form-select" name="pivot_col">
                                                        <option value="">—</option>
                                                        @foreach ($dimensions as $dimension)
                                                            <option value="{{ $dimension['key'] }}" @selected(($input['pivot_col'] ?? '') === $dimension['key'])>{{ $dimension['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <label class="form-label">Limit</label>
                                                <input class="form-control" type="number" min="10" max="500"
                                                    name="limit" value="{{ $result['limit'] }}">
                                            </div>

                                            <button class="btn btn-primary w-100 mt-4" type="submit">اجرای
                                                گزارش</button>
                                        </form>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">ذخیره قالب</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST"
                                            action="{{ route('bi.report-builder.templates.store') }}">
                                            @csrf
                                            <input type="hidden" name="dataset_key"
                                                value="{{ optional($dataset)->dataset_key }}">
                                            @foreach ($selectedDimensions as $dimension)
                                                <input type="hidden" name="dimensions[]"
                                                    value="{{ $dimension }}">
                                            @endforeach
                                            @foreach ($selectedMeasures as $measure)
                                                <input type="hidden" name="measures[]" value="{{ $measure }}">
                                            @endforeach
                                            @foreach (['date_from', 'date_to', 'metric_key', 'dimension_type', 'view_mode', 'pivot_row', 'pivot_col', 'analysis_mode'] as $filterKey)
                                                @if (!empty($input[$filterKey]))
                                                    <input type="hidden" name="{{ $filterKey }}"
                                                        value="{{ is_array($input[$filterKey]) ? implode(',', $input[$filterKey]) : $input[$filterKey] }}">
                                                @endif
                                            @endforeach
                                            <input type="hidden" name="chart_type" value="{{ $input['chart_type'] ?? $result['chart_type'] ?? 'table' }}">
                                            <div class="mb-3">
                                                <label class="form-label">عنوان قالب</label>
                                                <input class="form-control" name="title" required maxlength="180"
                                                    value="{{ optional($dataset)->title }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">اشتراک</label>
                                                <select class="form-select" name="visibility" id="templateVisibility">
                                                    <option value="private">فقط من</option>
                                                    <option value="organization">سازمان من</option>
                                                    <option value="tenant">کل پنل (tenant)</option>
                                                    <option value="role">نقش مشخص</option>
                                                </select>
                                            </div>
                                            <div class="mb-3" id="sharedRoleWrap" style="display:none">
                                                <label class="form-label">نقش</label>
                                                <select class="form-select" name="shared_role_id">
                                                    <option value="">انتخاب نقش</option>
                                                    @foreach ($builder['roles'] as $role)
                                                        <option value="{{ $role->id }}">{{ $role->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button class="btn btn-outline-primary w-100" type="submit">ذخیره قالب
                                                گزارش</button>
                                        </form>
                                    </div>
                                </div>

                                <div class="card mt-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">زمان بندی ارسال گزارش</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST"
                                            action="{{ route('bi.report-builder.schedules.store') }}">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">قالب گزارش</label>
                                                <select class="form-select" name="bi_report_template_id" required>
                                                    @foreach ($builder['templates'] as $template)
                                                        <option value="{{ $template->id }}">{{ $template->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">عنوان زمان بندی</label>
                                                <input class="form-control" name="title" maxlength="180" required
                                                    value="گزارش زمان بندی شده BI">
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">تناوب</label>
                                                    <select class="form-select" name="frequency">
                                                        <option value="daily">روزانه</option>
                                                        <option value="weekly">هفتگی</option>
                                                        <option value="monthly">ماهانه</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">فرمت</label>
                                                    <select class="form-select" name="delivery_format">
                                                        <option value="csv">CSV / Excel</option>
                                                        <option value="html">HTML</option>
                                                        <option value="pdf">چاپ / PDF</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label">گیرنده ها</label>
                                                <input class="form-control" name="recipients"
                                                    placeholder="ایمیل، موبایل یا شناسه کاربر با جداکننده کاما">
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label d-block">کانال ارسال</label>
                                                <label class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="channels[]"
                                                        value="panel" checked>
                                                    <span class="form-check-label">اعلان پنل</span>
                                                </label>
                                                <label class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="channels[]"
                                                        value="email">
                                                    <span class="form-check-label">ایمیل</span>
                                                </label>
                                                <label class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" name="channels[]"
                                                        value="sms">
                                                    <span class="form-check-label">پیامک</span>
                                                </label>
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label">اولین اجرا</label>
                                                <input class="form-control" type="datetime-local" name="next_run_at">
                                            </div>
                                            <button class="btn btn-outline-primary w-100 mt-4" type="submit"
                                                @disabled($builder['templates']->isEmpty())>ثبت زمان بندی</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-8">
                                <div class="row g-4 mb-4">
                                    @foreach ($result['totals'] as $key => $value)
                                        <div class="col-md-4">
                                            <div class="card h-100">
                                                <div class="card-body">
                                                    <div class="text-muted mb-1">{{ $key }}</div>
                                                    <h4 class="mb-0">{{ number_format((float) $value, 2) }}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if (!empty($result['analysis_insights']))
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0">خلاصه تحلیل</h5>
                                        </div>
                                        <div class="card-body">
                                            @foreach ($result['analysis_insights'] as $insight)
                                                <div class="alert alert-info mb-2">{{ $insight }}</div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($result['security']['masking_applied']) || empty($result['security']['export_allowed']))
                                    <div class="alert alert-warning mb-4">
                                        سیاست امنیتی BI روی این خروجی فعال است؛
                                        {{ number_format((int) ($result['security']['masked_rows'] ?? 0)) }} ردیف حساس
                                        ماسک شده و خروجی فایل برای نقش فعلی
                                        {{ !empty($result['security']['export_allowed']) ? 'مجاز' : 'غیرمجاز' }} است.
                                    </div>
                                @endif

                                @include('bi.partials.report_builder_output', ['builder' => $builder, 'input' => $input])

                                <div class="row g-4">
                                    <div class="col-lg-12">
                                        <div class="card mb-4">
                                            <div class="card-header"><h5 class="mb-0">خروجی‌های صف‌شده</h5></div>
                                            <div class="table-responsive text-nowrap">
                                                <table class="table mb-0">
                                                    <thead><tr><th>شناسه</th><th>فرمت</th><th>وضعیت</th><th>ردیف</th><th>دانلود</th></tr></thead>
                                                    <tbody>
                                                        @forelse ($builder['recent_exports'] as $export)
                                                            <tr>
                                                                <td>#{{ $export->id }}</td>
                                                                <td>{{ strtoupper($export->summary_json['format'] ?? $export->options_json['format'] ?? '—') }}</td>
                                                                <td><span class="badge bg-label-{{ $export->status === 'completed' ? 'success' : ($export->status === 'failed' ? 'danger' : 'warning') }}">{{ $export->status }}</span></td>
                                                                <td>{{ number_format($export->success_rows ?? 0) }}</td>
                                                                <td>
                                                                    @if (in_array($export->status, ['completed', 'completed_with_errors'], true))
                                                                        <a href="{{ route('bi.report-builder.exports.download', $export) }}" class="btn btn-sm btn-label-primary">دانلود</a>
                                                                    @else
                                                                        <span class="text-muted small">در حال پردازش</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="5" class="text-center text-muted py-3">خروجی صف‌شده‌ای نیست</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="card h-100">
                                            <div class="card-header">
                                                <h5 class="mb-0">زمان بندی های فعال</h5>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>عنوان</th>
                                                            <th>تناوب</th>
                                                            <th>اجرای بعدی</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($builder['schedules'] as $schedule)
                                                            <tr>
                                                                <td>{{ $schedule->title }}</td>
                                                                <td>{{ $schedule->frequency }}</td>
                                                                <td>{{ optional($schedule->next_run_at)->format('Y-m-d H:i') }}
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3"
                                                                    class="text-center text-muted py-4">زمان بندی فعالی
                                                                    ثبت نشده است.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="card h-100">
                                            <div class="card-header">
                                                <h5 class="mb-0">Snapshotهای ارسال شده</h5>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>عنوان</th>
                                                            <th>ردیف</th>
                                                            <th>لینک امن</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($builder['recent_deliveries'] as $delivery)
                                                            <tr>
                                                                <td>{{ $delivery->title }}</td>
                                                                <td>{{ number_format($delivery->row_count) }}</td>
                                                                <td><a href="{{ route('bi.report-builder.shared', $delivery->delivery_token) }}"
                                                                        target="_blank">مشاهده</a></td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3"
                                                                    class="text-center text-muted py-4">Snapshot ارسالی
                                                                    ثبت نشده است.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="card h-100">
                                            <div class="card-header">
                                                <h5 class="mb-0">قالب های ذخیره شده</h5>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>عنوان</th>
                                                            <th>Dataset</th>
                                                            <th>نمایش</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($builder['templates'] as $template)
                                                            @php
                                                                $loadParams = array_merge($template->filters ?? [], [
                                                                    'dataset_key' => $template->dataset_key,
                                                                    'dimensions' => $template->dimensions,
                                                                    'measures' => $template->measures,
                                                                    'chart_type' => $template->chart_type,
                                                                    'run' => 1,
                                                                ]);
                                                            @endphp
                                                            <tr>
                                                                <td><a href="{{ route('bi.report-builder.index', $loadParams) }}">{{ $template->title }}</a></td>
                                                                <td>{{ $template->dataset_key }}</td>
                                                                <td>{{ $template->visibility }}{{ $template->shared_role_id ? ' #' . $template->shared_role_id : '' }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3"
                                                                    class="text-center text-muted py-4">قالبی ثبت نشده
                                                                    است.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="card h-100">
                                            <div class="card-header">
                                                <h5 class="mb-0">Audit اجرای گزارش</h5>
                                            </div>
                                            <div class="table-responsive text-nowrap">
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>Dataset</th>
                                                            <th>ردیف</th>
                                                            <th>زمان</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($builder['recent_runs'] as $run)
                                                            <tr>
                                                                <td>{{ $run->dataset_key }}</td>
                                                                <td>{{ number_format($run->row_count) }}</td>
                                                                <td>{{ optional($run->finished_at)->format('Y-m-d H:i') }}
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3"
                                                                    class="text-center text-muted py-4">اجرایی ثبت نشده
                                                                    است.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('sections.footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    @include('sections.script')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const visibility = document.querySelector('#templateVisibility');
        const roleWrap = document.querySelector('#sharedRoleWrap');
        if (!visibility || !roleWrap) return;
        const toggle = () => { roleWrap.style.display = visibility.value === 'role' ? '' : 'none'; };
        visibility.addEventListener('change', toggle);
        toggle();
    });
    </script>
</body>

</html>
