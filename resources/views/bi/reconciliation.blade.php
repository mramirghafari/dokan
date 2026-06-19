<!DOCTYPE html>
<html lang="fa" class="light-style layout-menu-fixed" dir="rtl" data-theme="theme-default" data-assets-path="../../assets/" data-template="vertical-menu-template-free">
<head>
    @include('sections.head')
    <title>مغایرت‌گیری BI - دکان دارمینو</title>
    <style>
        .bi-recon-score {
            width: 120px; height: 120px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; flex-direction: column;
            font-weight: 700; border: 6px solid;
        }
        .bi-recon-score.healthy { border-color: #28c76f; color: #28c76f; background: rgba(40,199,111,.08); }
        .bi-recon-score.warning { border-color: #ff9f43; color: #ff9f43; background: rgba(255,159,67,.08); }
        .bi-recon-score.critical { border-color: #ea5455; color: #ea5455; background: rgba(234,84,85,.08); }
        .bi-recon-check { border: 0; border-radius: .75rem; height: 100%; transition: transform .12s ease; }
        .bi-recon-check:hover { transform: translateY(-2px); box-shadow: 0 .25rem 1rem rgba(67,89,113,.12); }
        .bi-recon-check .status-bar { height: 4px; border-radius: 4px 4px 0 0; }
        .preset-chip { cursor: pointer; }
    </style>
</head>
<body>
@include('sweetalert::alert')
@php
    $recon = $page['reconciliation'];
    $coverage = $page['coverage'];
    $status = $recon['health_status'] ?? 'warning';
@endphp
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('sections.sidebar')
        <div class="layout-page">
            @include('sections.navbar')
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <h4 class="mb-1"><span class="text-muted fw-light">BI /</span> مغایرت‌گیری و Backfill</h4>
                            <p class="text-muted mb-0">
                                تطبیق <code>bi_daily_summaries</code> با منابع عملیاتی —
                                تاریخ مرجع: {{ $recon['summary_date'] ?: '—' }}
                            </p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('bi.dashboard.refresh-data-mart') }}">@csrf
                                <button class="btn btn-label-secondary btn-sm" type="submit">Refresh امروز</button>
                            </form>
                            <form method="POST" action="{{ route('bi.reconciliation.run') }}">@csrf
                                <button class="btn btn-primary btn-sm" type="submit"><x-ui.icon name="refresh" class="me-1" />اجرای مغایرت‌گیری</button>
                            </form>
                            <a href="{{ route('bi.executive.index') }}" class="btn btn-label-primary btn-sm">Executive</a>
                        </div>
                    </div>

                    @if($recon['message'] && empty($recon['checks']))
                        <div class="alert alert-warning">{{ $recon['message'] }}</div>
                    @endif

                    <div class="row g-4 mb-4">
                        <div class="col-lg-4">
                            <div class="card h-100">
                                <div class="card-body d-flex align-items-center gap-4">
                                    <div class="bi-recon-score {{ $status }}">
                                        <span style="font-size:1.75rem;line-height:1">{{ number_format($recon['health_score'], 0) }}%</span>
                                        <small class="fw-normal" style="font-size:.7rem">سلامت داده</small>
                                    </div>
                                    <div>
                                        <div class="mb-2">
                                            <span class="badge bg-label-success me-1">{{ $recon['aligned_count'] }} هم‌خوان</span>
                                            <span class="badge bg-label-warning me-1">{{ $recon['warning_count'] }} هشدار</span>
                                            <span class="badge bg-label-danger">{{ $recon['critical_count'] }} بحرانی</span>
                                        </div>
                                        <div class="small text-muted">
                                            پوشش: {{ number_format($coverage['distinct_days']) }} روز
                                            @if($coverage['first_date']) ({{ $coverage['first_date'] }} → {{ $coverage['last_date'] }}) @endif
                                        </div>
                                        <div class="small text-muted mt-1">{{ number_format($coverage['metric_rows']) }} ردیف summary</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card h-100">
                                <div class="card-header"><h5 class="mb-0">Backfill تاریخی data mart</h5></div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">برای داشبورد Executive و روند ۱۲ ماهه، summary روزانه را برای بازه گذشته بسازید. پردازش در صف heavy انجام می‌شود.</p>
                                    <form method="POST" action="{{ route('bi.reconciliation.backfill') }}" id="backfillForm">
                                        @csrf
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            @foreach ($page['backfill_presets'] as $preset)
                                                <button type="button" class="btn btn-sm btn-outline-primary preset-chip" data-months="{{ $preset['months'] }}">{{ $preset['label'] }}</button>
                                            @endforeach
                                        </div>
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-3">
                                                <label class="form-label">از تاریخ</label>
                                                <input class="form-control" type="date" name="from" id="backfillFrom">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">تا تاریخ</label>
                                                <input class="form-control" type="date" name="to" id="backfillTo" value="{{ now()->toDateString() }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">ماه (جایگزین)</label>
                                                <input class="form-control" type="number" name="months" id="backfillMonths" min="1" max="24" value="{{ config('erp_scale.bi_reconciliation.default_backfill_months', 12) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <button class="btn btn-primary w-100" type="submit" onclick="return confirm('Backfill در صف قرار بگیرد؟');">
                                                    <x-ui.icon name="history" class="me-1" />شروع Backfill
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="form-text mt-2">CLI: <code>php artisan bi:backfill-data-mart --months=12 --sync</code></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        @foreach ($recon['checks'] as $check)
                            @php
                                $barColor = match($check['status']) {
                                    'aligned' => '#28c76f',
                                    'warning' => '#ff9f43',
                                    'critical' => '#ea5455',
                                    default => '#a8aaae',
                                };
                            @endphp
                            <div class="col-md-6 col-xl-4">
                                <div class="card bi-recon-check">
                                    <div class="status-bar" style="background:{{ $barColor }}"></div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <small class="text-muted d-block">{{ $check['domain'] }}</small>
                                                <h6 class="mb-0">{{ $check['title'] }}</h6>
                                            </div>
                                            <span class="badge bg-label-{{ $check['status'] === 'aligned' ? 'success' : ($check['status'] === 'critical' ? 'danger' : 'warning') }}">
                                                <x-ui.icon :name="$check['icon']" />
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">Summary</span>
                                            <strong>{{ $check['summary_value'] !== null ? number_format($check['summary_value'], $check['unit'] === 'count' ? 0 : 2) : '—' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span class="text-muted">منبع ({{ $check['source'] }})</span>
                                            <strong>{{ $check['source_value'] !== null ? number_format($check['source_value'], $check['unit'] === 'count' ? 0 : 2) : '—' }}</strong>
                                        </div>
                                        @if(!is_null($check['delta_percent']))
                                            <div class="small mt-2 {{ abs($check['delta_percent']) <= 2 ? 'text-success' : (abs($check['delta_percent']) <= 10 ? 'text-warning' : 'text-danger') }}">
                                                اختلاف: {{ $check['delta_percent'] >= 0 ? '+' : '' }}{{ $check['delta_percent'] }}%
                                                — {{ $check['status_label'] }}
                                            </div>
                                        @endif
                                        @if(!empty($check['snapshot']))
                                            <div class="form-text mt-1">snapshot لحظه‌ای — برای تاریخ‌های گذشته ثابت است</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="card">
                        <div class="card-header"><h5 class="mb-0">تاریخچه Refresh / Backfill / مغایرت‌گیری</h5></div>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>نوع</th>
                                        <th>وضعیت</th>
                                        <th>ردیف</th>
                                        <th>پیام</th>
                                        <th>زمان</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($page['recent_logs'] as $log)
                                        <tr>
                                            <td><span class="badge bg-label-primary">{{ $log->dataset_key }}</span></td>
                                            <td><span class="badge bg-label-{{ $log->status === 'success' ? 'success' : ($log->status === 'failed' ? 'danger' : 'warning') }}">{{ $log->status }}</span></td>
                                            <td>{{ number_format($log->rows_count ?? 0) }}</td>
                                            <td class="small text-muted">{{ \Illuminate\Support\Str::limit($log->message, 80) }}</td>
                                            <td class="small">{{ optional($log->finished_at ?? $log->started_at)->format('Y-m-d H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-4">لاگی ثبت نشده</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @include('sections.footer')
            </div>
        </div>
    </div>
</div>
@include('sections.script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toInput = document.querySelector('#backfillTo');
    const fromInput = document.querySelector('#backfillFrom');
    const monthsInput = document.querySelector('#backfillMonths');

    function setFromMonths(months) {
        if (!toInput || !fromInput) return;
        const to = new Date(toInput.value || new Date().toISOString().slice(0, 10));
        const from = new Date(to);
        from.setMonth(from.getMonth() - months);
        fromInput.value = from.toISOString().slice(0, 10);
        if (monthsInput) monthsInput.value = months;
    }

    document.querySelectorAll('.preset-chip').forEach(btn => {
        btn.addEventListener('click', () => setFromMonths(parseInt(btn.dataset.months, 10)));
    });

    if (fromInput && !fromInput.value && monthsInput) {
        setFromMonths(parseInt(monthsInput.value, 10) || 12);
    }
});
</script>
</body>
</html>
