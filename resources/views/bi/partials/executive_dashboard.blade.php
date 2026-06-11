@php($dash = $dashboard)
<style>
    .bi-exec-kpi { border: 0; border-radius: .75rem; transition: transform .12s ease; height: 100%; }
    .bi-exec-kpi:hover { transform: translateY(-2px); box-shadow: 0 .2rem .9rem rgba(67,89,113,.1); }
    .bi-exec-kpi .accent { width: 4px; }
    .delta-up { color: #28c76f; }
    .delta-down { color: #ea5455; }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h4 class="mb-1"><span class="text-muted fw-light">BI /</span> {{ $title }}</h4>
        <p class="text-muted mb-0">فقط از <code>bi_daily_summaries</code> — تاریخ: {{ $dash['latest_date'] ?: '—' }} @if($dash['previous_date']) · مقایسه با {{ $dash['previous_date'] }} @endif</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <form method="POST" action="{{ route('bi.dashboard.refresh-data-mart') }}">@csrf<button class="btn btn-primary btn-sm" type="submit">Refresh data mart</button></form>
        <a href="{{ route('bi.dashboard.index') }}" class="btn btn-label-secondary btn-sm">BI پایه</a>
        <a href="{{ route('bi.report-builder.index') }}" class="btn btn-label-primary btn-sm">گزارش‌ساز</a>
    </div>
</div>

@if(!$dash['latest_date'])
    <div class="alert alert-warning">هنوز summary روزانه ندارید. یک‌بار «Refresh data mart» را بزنید.</div>
@endif

<div class="row g-3 mb-4">
    @foreach ($dash['cards'] as $card)
        <div class="col-sm-6 col-xl-4 col-xxl-3">
            <div class="card bi-exec-kpi">
                <div class="card-body d-flex gap-3 p-3">
                    <div class="accent bg-primary"></div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <small class="text-muted">{{ $card['title'] }}</small>
                                <h4 class="mb-0 mt-1">
                                    @if($card['unit'] === 'rial')
                                        {{ number_format($card['value']) }}
                                    @else
                                        {{ number_format($card['value']) }}
                                    @endif
                                </h4>
                            </div>
                            <span class="badge bg-label-{{ $card['status'] === 'fresh' ? 'success' : 'warning' }}"><i class="ti {{ $card['icon'] }}"></i></span>
                        </div>
                        @if(!is_null($card['delta_percent']))
                            <small class="{{ $card['delta_percent'] >= 0 ? 'delta-up' : 'delta-down' }}">
                                {{ $card['delta_percent'] >= 0 ? '▲' : '▼' }} {{ abs($card['delta_percent']) }}% نسبت دوره قبل
                            </small>
                        @endif
                        @if($card['budget_percent'])
                            <div class="small text-muted mt-1">بودجه/هدف: {{ $card['budget_percent'] }}%</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">روند فروش (۱۴ روز)</h5></div>
            <div class="card-body"><div id="biExecTrend" style="min-height:240px;"></div></div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">توزیع دامنه‌ها</h5></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>دامنه</th><th>تعداد شاخص</th><th>جمع مقدار</th></tr></thead>
                    <tbody>
                        @forelse($dash['domains'] as $row)
                            <tr>
                                <td><span class="badge bg-label-primary">{{ $row['domain'] }}</span></td>
                                <td>{{ number_format($row['metrics_count']) }}</td>
                                <td>{{ number_format($row['total_value']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">داده‌ای نیست</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">بودجه فروش (Targets)</h5></div>
            <div class="card-body">
                <div class="d-flex justify-content-between border-bottom py-2"><span>هدف فعال</span><strong>{{ number_format($dash['budget_summary']['sales_target']) }}</strong></div>
                <div class="d-flex justify-content-between py-2"><span>تعداد target</span><strong>{{ number_format($dash['budget_summary']['active_targets']) }}</strong></div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h5 class="mb-0">منبع داده</h5></div>
            <div class="card-body small text-muted">
                تمام اعداد از جدول <code>bi_daily_summaries</code> خوانده می‌شود؛ هیچ query عملیاتی مستقیم روی فاکتور/انبار در این صفحه نیست.
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/') }}/vendor/libs/apex-charts/apexcharts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trend = @json($dash['trend']);
    const el = document.querySelector('#biExecTrend');
    if (!el || !trend.length || typeof ApexCharts === 'undefined') return;
    new ApexCharts(el, {
        series: [{ name: 'فروش', data: trend.map(r => r.value) }],
        chart: { type: 'area', height: 240, toolbar: { show: false }, fontFamily: 'inherit' },
        stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
        colors: ['#696cff'],
        xaxis: { categories: trend.map(r => r.label) },
        yaxis: { labels: { formatter: v => new Intl.NumberFormat('fa-IR').format(Math.round(v)) } },
        dataLabels: { enabled: false },
        grid: { borderColor: '#f1f1f2' }
    }).render();
});
</script>
