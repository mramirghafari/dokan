@php($result = $builder['result'])

@if (!empty($result['chart']) && in_array($result['chart_type'], ['bar', 'line'], true))
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">نمودار {{ $result['chart_type'] === 'line' ? 'خطی' : 'میله‌ای' }}</h5>
            <span class="badge bg-label-primary">{{ $result['chart']['category_label'] }}</span>
        </div>
        <div class="card-body">
            <div id="biReportChart" style="min-height:280px;"></div>
        </div>
    </div>
@endif

@if (($result['view_mode'] ?? 'table') === 'pivot' && !empty($result['pivot']))
    @php($pivot = $result['pivot'])
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Pivot — {{ $pivot['row_label'] ?? '' }} × {{ $pivot['col_label'] ?? '' }}</h5>
        </div>
        @if (empty($pivot['ready']))
            <div class="card-body"><div class="alert alert-warning mb-0">{{ $pivot['message'] ?? 'Pivot آماده نیست.' }}</div></div>
        @else
            <div class="table-responsive text-nowrap">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>{{ $pivot['row_label'] }}</th>
                            @foreach ($pivot['columns'] as $col)
                                <th>{{ $col }}</th>
                            @endforeach
                            <th>جمع</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pivot['rows'] as $pivotRow)
                            <tr>
                                <td><strong>{{ $pivotRow['label'] }}</strong></td>
                                @foreach ($pivot['columns'] as $col)
                                    <td>{{ number_format((float) ($pivotRow['cells'][$col] ?? 0), 2) }}</td>
                                @endforeach
                                <td><strong>{{ number_format((float) $pivotRow['total'], 2) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <th>جمع ستون</th>
                            @foreach ($pivot['columns'] as $col)
                                <th>{{ number_format((float) ($pivot['column_totals'][$col] ?? 0), 2) }}</th>
                            @endforeach
                            <th>{{ number_format((float) ($pivot['grand_total'] ?? 0), 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
@endif

<div class="card mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">خروجی گزارش</h5>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge bg-label-primary">{{ number_format($result['rows']->count()) }} ردیف</span>
            @if (!empty($result['security']['export_allowed']))
                @foreach (['csv' => 'CSV', 'xlsx' => 'Excel', 'pdf' => 'PDF'] as $fmt => $label)
                    <form class="d-inline" method="POST" action="{{ route('bi.report-builder.export') }}">
                        @csrf
                        <input type="hidden" name="format" value="{{ $fmt }}">
                        @foreach ($input as $key => $val)
                            @if (is_array($val))
                                @foreach ($val as $item)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                @endforeach
                            @elseif($val !== null && $val !== '')
                                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                            @endif
                        @endforeach
                        <input type="hidden" name="run" value="1">
                        <button class="btn btn-sm btn-outline-primary" type="submit">{{ $label }}</button>
                    </form>
                @endforeach
            @endif
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    @foreach ($result['columns'] as $column)
                        <th>{{ $column['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($result['rows'] as $row)
                    <tr>
                        @foreach ($result['columns'] as $column)
                            @php $value = $row->{$column['key']}; @endphp
                            <td>
                                @if ($column['type'] === 'measure' && is_numeric($value))
                                    {{ number_format((float) $value, 2) }}
                                @else
                                    {{ $value ?? '-' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ max(1, count($result['columns'])) }}" class="text-center text-muted py-4">داده‌ای برای فیلترهای انتخابی پیدا نشد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if (!empty($result['chart']))
<script src="{{ asset('assets/') }}/vendor/libs/apex-charts/apexcharts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const payload = @json($result['chart']);
    const el = document.querySelector('#biReportChart');
    if (!el || typeof ApexCharts === 'undefined' || !payload) return;
    new ApexCharts(el, {
        series: payload.series.map(s => ({ name: s.name, data: s.data })),
        chart: { type: payload.type, height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
        stroke: { curve: 'smooth', width: payload.type === 'line' ? 3 : 0 },
        colors: ['#696cff', '#28c76f', '#ff9f43'],
        xaxis: { categories: payload.categories, labels: { rotate: -35 } },
        yaxis: { labels: { formatter: v => new Intl.NumberFormat('fa-IR').format(Math.round(v)) } },
        dataLabels: { enabled: false },
        grid: { borderColor: '#f1f1f2' },
        legend: { position: 'top' }
    }).render();
});
</script>
@endif
