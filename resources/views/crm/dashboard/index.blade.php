<!DOCTYPE html>
<html lang="fa" class="light-style layout-menu-fixed" dir="rtl" data-theme="theme-default"
    data-assets-path="../../assets/" data-template="vertical-menu-template-free">

<head>
    @include('sections.head')
    <title>داشبورد CRM - دکان دارمینو</title>
    <style>
        .crm-kpi {
            border: 0;
            border-radius: .75rem;
            transition: transform .15s ease, box-shadow .15s ease;
            overflow: hidden;
        }
        .crm-kpi:hover {
            transform: translateY(-2px);
            box-shadow: 0 .25rem 1rem rgba(67, 89, 113, .12);
        }
        .crm-kpi .accent {
            width: 4px;
            flex-shrink: 0;
        }
        .crm-kpi .kpi-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: .5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
        .crm-drill-hint {
            font-size: .75rem;
            opacity: .75;
        }
        .aging-bar {
            height: .5rem;
            border-radius: 999px;
            overflow: hidden;
            display: flex;
            background: #f1f1f2;
        }
        .aging-bar span { display: block; height: 100%; }
        .reconcile-ok { color: var(--bs-success); }
        .reconcile-warn { color: var(--bs-warning); }
        .stage-row-link { cursor: pointer; }
        .stage-row-link:hover { background: rgba(105, 108, 255, .04); }
    </style>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.navbar')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @include('crm.partials.hub_bar', ['hubActive' => 'dashboard'])
                        @include('partials.erp-remote-select-assets')
                        @php
                            $summary = $dashboard['summary'];
                            $forecast = $dashboard['forecast'];
                            $reconcile = $forecast['reconcile'];
                            $drill = $dashboard['drilldowns'];
                            $ltv = $dashboard['ltv'];
                        @endphp

                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4" id="crm-tour-page-header">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">CRM /</span> داشبورد فروش و پیش‌بینی</h4>
                                <p class="text-muted mb-0">Forecast ماهانه، aging مرحله، منبع سرنخ و LTV — با drill-down به لیست‌های فیلترشده.</p>
                            </div>
                            <div class="d-flex flex-wrap gap-2" id="crm-tour-header-actions">
                                <a class="btn btn-primary" href="{{ route('crm.opportunities.index', ['status' => 'open', 'close_month' => 'current']) }}">
                                    <x-ui.icon name="target" class="me-1" />فرصت‌های ماه جاری
                                </a>
                                <a class="btn btn-label-primary" href="{{ route('crm.sales-boards.index') }}">کاریز فروش</a>
                                <a class="btn btn-label-secondary" href="{{ route('crm.followups.index') }}">پیگیری‌ها</a>
                            </div>
                        </div>

                        {{-- Forecast hero --}}
                        <div class="card mb-4 border-0 shadow-sm" id="crm-tour-forecast-card">
                            <div class="card-body">
                                <div class="row align-items-center g-4">
                                    <div class="col-lg-4">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <span class="badge bg-label-primary">Forecast {{ $forecast['month_label'] }}</span>
                                            @if($reconcile['pipeline_aligned'])
                                                <span class="badge bg-label-success"><x-ui.icon name="check" class="me-1" />Reconcile</span>
                                            @else
                                                <span class="badge bg-label-warning">نیاز به بررسی</span>
                                            @endif
                                        </div>
                                        <h2 class="mb-1">{{ number_format($forecast['forecast_total']) }} <small class="fs-6 text-muted">ریال</small></h2>
                                        <p class="text-muted small mb-3">بردشده + pipeline وزنی با تاریخ بستن در این ماه</p>
                                        <div class="d-flex flex-wrap gap-3 small">
                                            <div><span class="text-muted">قطعی (برد):</span> <strong>{{ number_format($forecast['committed_won']) }}</strong></div>
                                            <div><span class="text-muted">وزنی ماه:</span> <strong>{{ number_format($forecast['weighted_closing_month']) }}</strong></div>
                                            <div><span class="text-muted">فرصت:</span> <strong>{{ number_format($forecast['closing_count']) }}</strong></div>
                                        </div>
                                        <a href="{{ $drill['forecast_month'] }}" class="btn btn-sm btn-outline-primary mt-3">مشاهده فرصت‌های این ماه</a>
                                    </div>
                                    <div class="col-lg-8">
                                        <div id="crmForecastTrend" style="min-height: 220px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- KPI cards --}}
                        <div class="row g-3 mb-4" id="crm-tour-kpi-cards">
                            @foreach ([
                                ['key' => 'weighted_pipeline', 'label' => 'Pipeline وزنی', 'value' => number_format($summary['weighted_pipeline']), 'sub' => 'ریال | همه فرصت‌های باز', 'icon' => 'ti-chart-arcs', 'color' => 'primary', 'url' => $drill['weighted_pipeline']],
                                ['key' => 'open_opportunities', 'label' => 'فرصت‌های باز', 'value' => number_format($summary['open_opportunities']), 'sub' => 'نرخ برد ماه: ' . $summary['win_rate'] . '%', 'icon' => 'ti-briefcase', 'color' => 'info', 'url' => $drill['open_opportunities']],
                                ['key' => 'today_followups', 'label' => 'پیگیری امروز', 'value' => number_format($summary['today_followups']), 'sub' => number_format($summary['overdue_followups']) . ' معوق', 'icon' => 'ti-calendar-event', 'color' => 'warning', 'url' => $drill['today_followups']],
                                ['key' => 'open_cards', 'label' => 'کارت‌های باز', 'value' => number_format($summary['open_cards']), 'sub' => number_format($summary['overdue_cards']) . ' عقب‌افتاده', 'icon' => 'ti-layout-kanban', 'color' => 'success', 'url' => $drill['open_cards']],
                            ] as $kpi)
                                <div class="col-sm-6 col-xl-3">
                                    <a href="{{ $kpi['url'] }}" class="text-body text-decoration-none d-block h-100">
                                        <div class="card crm-kpi h-100" id="crm-tour-kpi-{{ $kpi['key'] }}">
                                            <div class="card-body d-flex gap-3 p-3">
                                                <div class="accent bg-{{ $kpi['color'] }}"></div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <div class="text-muted small mb-1">{{ $kpi['label'] }}</div>
                                                            <h4 class="mb-0">{{ $kpi['value'] }}</h4>
                                                            <div class="text-muted small mt-1">{{ $kpi['sub'] }}</div>
                                                        </div>
                                                        <div class="kpi-icon bg-label-{{ $kpi['color'] }} text-{{ $kpi['color'] }}">
                                                            <x-ui.icon :name="$kpi['icon']" />
                                                        </div>
                                                    </div>
                                                    <div class="crm-drill-hint text-primary mt-2"><x-ui.icon name="arrow-left" class="me-1" />مشاهده لیست فیلترشده</div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <div class="row g-4">
                            <div class="col-xl-8">
                                {{-- Stage funnel --}}
                                <div class="card mb-4" id="crm-tour-stage-funnel">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">قیف فرصت‌های باز</h5>
                                        <span class="badge bg-label-secondary">{{ number_format($summary['open_opportunities']) }} فرصت</span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>مرحله</th>
                                                    <th>تعداد</th>
                                                    <th>مبلغ</th>
                                                    <th>وزنی</th>
                                                    <th>میانگین روز در مرحله</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($dashboard['stage_rows'] as $row)
                                                    <tr class="stage-row-link" onclick="window.location='{{ $row['url'] }}'">
                                                        <td>
                                                            <span class="fw-medium">{{ $row['title'] }}</span>
                                                            <x-ui.icon name="chevron-left" class="text-muted small ms-1" />
                                                        </td>
                                                        <td>{{ number_format($row['count']) }}</td>
                                                        <td>{{ number_format($row['amount']) }}</td>
                                                        <td>{{ number_format($row['weighted_amount']) }}</td>
                                                        <td>
                                                            @if($row['avg_days_in_stage'] > 30)
                                                                <span class="badge bg-label-danger">{{ $row['avg_days_in_stage'] }} روز</span>
                                                            @elseif($row['avg_days_in_stage'] > 14)
                                                                <span class="badge bg-label-warning">{{ $row['avg_days_in_stage'] }} روز</span>
                                                            @else
                                                                <span class="text-muted">{{ $row['avg_days_in_stage'] }} روز</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="5" class="text-center text-muted py-5">فرصت باز برای نمایش وجود ندارد.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                {{-- Stage aging --}}
                                <div class="card mb-4" id="crm-tour-stage-aging">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">Aging مرحله</h5>
                                            <small class="text-muted">توزیع زمان ماندن در هر مرحله</small>
                                        </div>
                                        <a href="{{ $drill['stale_opportunities'] }}" class="btn btn-sm btn-label-warning">فرصت‌های کهنه (+۳۰ روز)</a>
                                    </div>
                                    <div class="card-body">
                                        @forelse ($dashboard['stage_aging']['stages'] as $aging)
                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <a href="{{ $aging['url'] }}" class="fw-medium text-body">{{ $aging['title'] }}</a>
                                                    <small class="text-muted">{{ $aging['total'] }} فرصت · میانگین {{ $aging['avg_days'] }} روز</small>
                                                </div>
                                                <div class="aging-bar mb-1">
                                                    @php $total = max(1, $aging['total']); @endphp
                                                    <span style="width: {{ ($aging['buckets']['0_7'] / $total) * 100 }}%; background: #71dd37;"></span>
                                                    <span style="width: {{ ($aging['buckets']['8_14'] / $total) * 100 }}%; background: #03c3ec;"></span>
                                                    <span style="width: {{ ($aging['buckets']['15_30'] / $total) * 100 }}%; background: #ffab00;"></span>
                                                    <span style="width: {{ ($aging['buckets']['30_plus'] / $total) * 100 }}%; background: #ff3e1d;"></span>
                                                </div>
                                                <div class="d-flex flex-wrap gap-3 small text-muted">
                                                    <span>تا ۷: {{ $aging['buckets']['0_7'] }}</span>
                                                    <span>۸–۱۴: {{ $aging['buckets']['8_14'] }}</span>
                                                    <span>۱۵–۳۰: {{ $aging['buckets']['15_30'] }}</span>
                                                    <span>+۳۰: {{ $aging['buckets']['30_plus'] }}</span>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-muted mb-0">داده aging برای فرصت باز موجود نیست.</p>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- User performance --}}
                                <div class="card" id="crm-tour-user-performance">
                                    <div class="card-header"><h5 class="mb-0">عملکرد کارشناس</h5></div>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>کارشناس</th>
                                                    <th>کارت‌ها</th>
                                                    <th>انجام/برد</th>
                                                    <th>ارزش وزنی</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($dashboard['user_rows'] as $row)
                                                    <tr class="stage-row-link" onclick="window.location='{{ $row['url'] }}'">
                                                        <td>{{ $row['name'] }}</td>
                                                        <td>{{ number_format($row['cards_count']) }}</td>
                                                        <td>{{ number_format($row['done_count']) }}</td>
                                                        <td>{{ number_format($row['weighted_amount']) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="4" class="text-center text-muted py-4">کارت دارای مسئول برای رتبه‌بندی وجود ندارد.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                {{-- Reconcile --}}
                                <div class="card mb-4" id="crm-tour-reconcile">
                                    <div class="card-header"><h5 class="mb-0">Reconcile Forecast</h5></div>
                                    <div class="card-body">
                                        <p class="small text-muted">{{ $reconcile['message'] }}</p>
                                        <div class="d-flex justify-content-between border-bottom py-2">
                                            <span>Pipeline وزنی کل</span>
                                            <strong>{{ number_format($reconcile['open_weighted_total']) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between border-bottom py-2">
                                            <span>جمع وزنی مراحل</span>
                                            <strong>{{ number_format($reconcile['stage_weighted_sum']) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between border-bottom py-2">
                                            <span>اختلاف</span>
                                            <strong class="{{ $reconcile['pipeline_aligned'] ? 'reconcile-ok' : 'reconcile-warn' }}">{{ number_format($reconcile['pipeline_delta']) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between py-2">
                                            <span>پوشش probability ماه</span>
                                            <strong>{{ $reconcile['month_coverage_percent'] }}%</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- Lead sources --}}
                                <div class="card mb-4" id="crm-tour-lead-sources">
                                    <div class="card-header"><h5 class="mb-0">منبع سرنخ</h5></div>
                                    <div class="card-body">
                                        @if(count($dashboard['lead_sources']) > 0)
                                            <div id="crmLeadSources" class="mb-3"></div>
                                            <div class="list-group list-group-flush">
                                                @foreach ($dashboard['lead_sources'] as $source)
                                                    <a href="{{ $source['url'] }}" class="list-group-item list-group-item-action px-0 d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <div class="fw-medium">{{ $source['title'] }}</div>
                                                            <small class="text-muted">{{ number_format($source['converted']) }}/{{ number_format($source['total']) }} تبدیل · {{ $source['conversion_rate'] }}%</small>
                                                        </div>
                                                        <span class="badge bg-label-primary">{{ number_format($source['won_amount']) }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">سرنخی ثبت نشده است.</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- LTV --}}
                                <div class="card mb-4" id="crm-tour-ltv">
                                    <div class="card-header"><h5 class="mb-0">LTV ساده</h5></div>
                                    <div class="card-body">
                                        <p class="small text-muted mb-3">{{ $ltv['message'] }}</p>
                                        <div class="row g-3 text-center">
                                            <div class="col-6">
                                                <div class="border rounded p-3">
                                                    <div class="text-muted small">میانگین LTV</div>
                                                    <h5 class="mb-0 mt-1">{{ number_format($ltv['avg_ltv']) }}</h5>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="border rounded p-3">
                                                    <div class="text-muted small">میانه LTV</div>
                                                    <h5 class="mb-0 mt-1">{{ number_format($ltv['median_ltv']) }}</h5>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex justify-content-between small">
                                                    <span>مشتری با فرصت برده</span>
                                                    <strong>{{ number_format($ltv['customer_count']) }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between small mt-2">
                                                    <span>درآمد کل فاکتور</span>
                                                    <strong>{{ number_format($ltv['total_revenue']) }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Alerts --}}
                                <div class="card mb-4" id="crm-tour-alerts">
                                    <div class="card-header"><h5 class="mb-0">هشدارهای CRM</h5></div>
                                    <div class="card-body pt-2">
                                        @foreach ($dashboard['alerts'] as $alert)
                                            <div class="alert alert-{{ $alert['type'] }} mb-2 py-2">
                                                <strong class="d-block">{{ $alert['title'] }}</strong>
                                                <span class="small">{{ $alert['body'] }}</span>
                                                @if(!empty($alert['url']))
                                                    <a href="{{ $alert['url'] }}" class="d-block small mt-1">رفتن به لیست ←</a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="card" id="crm-tour-month-summary">
                                    <div class="card-header"><h5 class="mb-0">خلاصه ماه جاری</h5></div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                                            <span>فروش برده‌شده</span>
                                            <a href="{{ $drill['won_month'] }}"><strong>{{ number_format($summary['won_amount']) }}</strong></a>
                                        </div>
                                        <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                                            <span>پیگیری‌های باز</span>
                                            <a href="{{ $drill['open_followups'] }}"><strong>{{ number_format($summary['open_followups']) }}</strong></a>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>پیگیری‌های انجام‌شده</span>
                                            <strong>{{ number_format($summary['done_followups']) }}</strong>
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
    <script src="{{ asset('assets/') }}/vendor/libs/apex-charts/apexcharts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const trend = @json($forecast['trend']);
            const leadSources = @json($dashboard['lead_sources']);

            const labelColor = typeof config !== 'undefined' ? config.colors.textMuted : '#a1acb8';
            const headingColor = typeof config !== 'undefined' ? config.colors.headingColor : '#566a7f';

            if (document.querySelector('#crmForecastTrend') && typeof ApexCharts !== 'undefined') {
                new ApexCharts(document.querySelector('#crmForecastTrend'), {
                    series: [
                        { name: 'بردشده', type: 'column', data: trend.map(r => r.won) },
                        { name: 'وزنی ماه', type: 'column', data: trend.map(r => r.weighted_pipeline) },
                        { name: 'Forecast', type: 'line', data: trend.map(r => r.forecast) }
                    ],
                    chart: { height: 240, type: 'line', toolbar: { show: false }, fontFamily: 'inherit' },
                    stroke: { width: [0, 0, 3], curve: 'smooth' },
                    plotOptions: { bar: { columnWidth: '40%', borderRadius: 4 } },
                    dataLabels: { enabled: false },
                    colors: ['#71dd37', '#696cff', '#ffab00'],
                    xaxis: { categories: trend.map(r => r.label), labels: { style: { colors: labelColor } } },
                    yaxis: { labels: { style: { colors: labelColor }, formatter: v => new Intl.NumberFormat('fa-IR').format(Math.round(v)) } },
                    legend: { position: 'top', labels: { colors: headingColor } },
                    grid: { borderColor: '#f1f1f2' },
                    tooltip: { y: { formatter: v => new Intl.NumberFormat('fa-IR').format(Math.round(v)) + ' ریال' } }
                }).render();
            }

            if (document.querySelector('#crmLeadSources') && leadSources.length && typeof ApexCharts !== 'undefined') {
                new ApexCharts(document.querySelector('#crmLeadSources'), {
                    series: leadSources.map(r => r.total),
                    labels: leadSources.map(r => r.title),
                    chart: { type: 'donut', height: 220, fontFamily: 'inherit' },
                    colors: ['#696cff', '#03c3ec', '#71dd37', '#ffab00', '#ff3e1d', '#8592a3'],
                    legend: { show: false },
                    dataLabels: { enabled: true, formatter: (v) => Math.round(v) + '%' },
                    plotOptions: { pie: { donut: { size: '65%' } } }
                }).render();
            }
        });
    </script>
</body>

</html>
