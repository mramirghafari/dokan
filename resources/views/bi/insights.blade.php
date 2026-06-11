<!DOCTYPE html>
<html lang="fa" class="light-style layout-menu-fixed" dir="rtl" data-theme="theme-default"
    data-assets-path="../../assets/" data-template="vertical-menu-template-free">

<head>
    @include('sections.head')
    <title>هشدار و پیش بینی BI - دکان دارمینو</title>
    <style>
        .insight-kpis {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .rule-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        @media (max-width: 992px) {

            .insight-kpis,
            .rule-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {

            .insight-kpis,
            .rule-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    @include('sweetalert::alert')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.navbar')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">BI /</span> هشدار، anomaly و پیش
                                    بینی</h4>
                                <div class="text-muted">Ruleهای عملیاتی روی data mart، تشخیص افت/رشد غیرعادی و forecast
                                    کوتاه مدت.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-secondary" href="{{ route('bi.report-builder.index') }}">گزارش
                                    ساز</a>
                                <form method="POST" action="{{ route('bi.insights.run') }}">
                                    @csrf
                                    <button class="btn btn-primary" type="submit">اجرای تحلیل</button>
                                </form>
                            </div>
                        </div>

                        <div class="insight-kpis mb-4">
                            <div class="card">
                                <div class="card-body"><span>هشدار باز</span>
                                    <h3 class="mt-2 mb-0 text-warning">{{ number_format($stats['open_alerts']) }}</h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>بحرانی/مهم</span>
                                    <h3 class="mt-2 mb-0 text-danger">{{ number_format($stats['critical_alerts']) }}
                                    </h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>Rule فعال</span>
                                    <h3 class="mt-2 mb-0 text-primary">{{ number_format($stats['rules']) }}</h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>Forecast</span>
                                    <h3 class="mt-2 mb-0 text-success">{{ number_format($stats['forecasts']) }}</h3>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">قانون هشدار جدید</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('bi.insights.rules.store') }}">
                                    @csrf
                                    <div class="rule-grid">
                                        <div><label class="form-label">عنوان</label><input class="form-control"
                                                name="title" required></div>
                                        <div><label class="form-label">دامنه</label><input class="form-control"
                                                name="domain" list="bi-domains" required></div>
                                        <div><label class="form-label">شاخص</label><input class="form-control"
                                                name="metric_key" list="bi-metrics" required></div>
                                        <div>
                                            <label class="form-label">نوع</label>
                                            <select class="form-select" name="rule_type">
                                                @foreach ($ruleTypes as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label">عملگر آستانه</label>
                                            <select class="form-select" name="operator">
                                                <option value="gte">بزرگتر/برابر</option>
                                                <option value="lte">کوچکتر/برابر</option>
                                            </select>
                                        </div>
                                        <div><label class="form-label">آستانه/درصد</label><input class="form-control"
                                                type="number" step="0.01" name="threshold_value" required></div>
                                        <div>
                                            <label class="form-label">شدت</label>
                                            <select class="form-select" name="severity">
                                                @foreach ($severities as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div><label class="form-label">روزهای مبنا</label><input class="form-control"
                                                type="number" name="lookback_days" value="7" min="1"></div>
                                        <div><label class="form-label">افق پیش بینی</label><input class="form-control"
                                                type="number" name="comparison_days" value="7" min="1">
                                        </div>
                                    </div>
                                    <div class="mt-3"><label class="form-label">اقدام پیشنهادی</label>
                                        <textarea class="form-control" name="suggestion" rows="2"></textarea>
                                    </div>
                                    <button class="btn btn-outline-primary mt-3" type="submit">ثبت Rule</button>
                                </form>
                                <datalist id="bi-domains">
                                    @foreach ($metrics->pluck('domain')->unique() as $domain)
                                        <option value="{{ $domain }}">
                                    @endforeach
                                </datalist>
                                <datalist id="bi-metrics">
                                    @foreach ($metrics as $metric)
                                        <option value="{{ $metric->metric_key }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-xl-7">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">هشدارهای عملیاتی</h5>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>عنوان</th>
                                                    <th>شاخص</th>
                                                    <th>شدت</th>
                                                    <th>مقدار</th>
                                                    <th>وضعیت</th>
                                                    <th>اقدام</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($alerts as $alert)
                                                    <tr>
                                                        <td>{{ $alert->title }}<div class="text-muted small">
                                                                {{ $alert->message }}</div>
                                                        </td>
                                                        <td>{{ $alert->domain }} / {{ $alert->metric_key }}</td>
                                                        <td><span
                                                                class="badge bg-label-{{ in_array($alert->severity, ['high', 'critical'], true) ? 'danger' : 'warning' }}">{{ $severities[$alert->severity] ?? $alert->severity }}</span>
                                                        </td>
                                                        <td>{{ number_format((float) $alert->current_value, 2) }}</td>
                                                        <td>{{ $alertStatuses[$alert->status] ?? $alert->status }}</td>
                                                        <td>
                                                            <form method="POST"
                                                                action="{{ route('bi.insights.alerts.update', $alert) }}"
                                                                class="d-flex gap-2">
                                                                @csrf
                                                                @method('PATCH')
                                                                <select class="form-select form-select-sm"
                                                                    name="status">
                                                                    @foreach ($alertStatuses as $key => $label)
                                                                        <option value="{{ $key }}"
                                                                            @selected($alert->status === $key)>
                                                                            {{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button class="btn btn-sm btn-primary"
                                                                    type="submit">ثبت</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">هنوز هشداری
                                                            ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-5">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">Forecastهای تازه</h5>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>شاخص</th>
                                                    <th>تاریخ</th>
                                                    <th>پیش بینی</th>
                                                    <th>روند</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($forecasts as $forecast)
                                                    <tr>
                                                        <td>{{ $forecast->domain }}<div class="text-muted small">
                                                                {{ $forecast->metric_key }}</div>
                                                        </td>
                                                        <td>{{ optional($forecast->forecast_date)->format('Y-m-d') }}
                                                        </td>
                                                        <td>{{ number_format((float) $forecast->forecast_value, 2) }}
                                                            <div class="text-muted small">
                                                                {{ number_format((float) $forecast->lower_bound, 2) }}
                                                                تا
                                                                {{ number_format((float) $forecast->upper_bound, 2) }}
                                                            </div>
                                                        </td>
                                                        <td><span
                                                                class="badge bg-label-{{ $forecast->trend_direction === 'down' ? 'danger' : ($forecast->trend_direction === 'up' ? 'success' : 'secondary') }}">{{ $forecast->trend_direction }}</span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">بعد از اجرای
                                                            تحلیل، forecastها اینجا نمایش داده می شوند.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Ruleهای فعال</h5>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>عنوان</th>
                                            <th>دامنه/شاخص</th>
                                            <th>نوع</th>
                                            <th>آستانه</th>
                                            <th>اقدام پیشنهادی</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($rules as $rule)
                                            <tr>
                                                <td>{{ $rule->title }}</td>
                                                <td>{{ $rule->domain }} / {{ $rule->metric_key }}</td>
                                                <td>{{ $rule->typeText() }}</td>
                                                <td>{{ number_format((float) $rule->threshold_value, 2) }}</td>
                                                <td>{{ $rule->suggestion }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Rule فعالی وجود
                                                    ندارد.</td>
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
</body>

</html>
<!DOCTYPE html>
<html lang="fa" class="light-style layout-menu-fixed" dir="rtl" data-theme="theme-default"
    data-assets-path="../../assets/" data-template="vertical-menu-template-free">

<head>
    @include('sections.head')
    <title>هشدار و پیش بینی BI - دکان دارمینو</title>
</head>

<body>
    @include('sweetalert::alert')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.navbar')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">BI /</span> هشدار، anomaly و پیش
                                    بینی</h4>
                                <div class="text-muted">Rule محور روی data mart روزانه: افت فروش، رشد بدهی، کمبود
                                    موجودی، تاخیر SLA و پیش بینی عملیاتی.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-secondary" href="{{ route('bi.dashboard.index') }}">BI
                                    جامع</a>
                                <form method="POST" action="{{ route('bi.insights.run') }}">@csrf<button
                                        class="btn btn-primary" type="submit">اجرای تحلیل</button></form>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><span>هشدار باز</span>
                                        <h3 class="mt-2 mb-0 text-warning">{{ number_format($stats['open_alerts']) }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><span>بحرانی/مهم</span>
                                        <h3 class="mt-2 mb-0 text-danger">
                                            {{ number_format($stats['critical_alerts']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><span>Rule فعال</span>
                                        <h3 class="mt-2 mb-0 text-primary">{{ number_format($stats['rules']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><span>پیش بینی</span>
                                        <h3 class="mt-2 mb-0 text-success">{{ number_format($stats['forecasts']) }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-xl-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">Rule جدید</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('bi.insights.rules.store') }}">
                                            @csrf
                                            <div class="mb-3"><label class="form-label">عنوان</label><input
                                                    class="form-control" name="title" required></div>
                                            <div class="row g-2">
                                                <div class="col-md-6"><label class="form-label">Domain</label><input
                                                        class="form-control" name="domain" list="bi-domains"
                                                        required></div>
                                                <div class="col-md-6"><label class="form-label">Metric</label><input
                                                        class="form-control" name="metric_key" list="bi-metrics"
                                                        required></div>
                                            </div>
                                            <datalist id="bi-domains">
                                                @foreach ($metrics->pluck('domain')->unique() as $domain)
                                                    <option value="{{ $domain }}"></option>
                                                @endforeach
                                            </datalist>
                                            <datalist id="bi-metrics">
                                                @foreach ($metrics as $metric)
                                                    <option value="{{ $metric->metric_key }}"></option>
                                                @endforeach
                                            </datalist>
                                            <div class="row g-2 mt-1">
                                                <div class="col-md-6"><label class="form-label">نوع</label><select
                                                        class="form-select" name="rule_type">
                                                        @foreach ($ruleTypes as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6"><label
                                                        class="form-label">Operator</label><select class="form-select"
                                                        name="operator">
                                                        <option value="gte">بزرگتر/مساوی</option>
                                                        <option value="lte">کوچکتر/مساوی</option>
                                                    </select></div>
                                                <div class="col-md-6"><label class="form-label">آستانه</label><input
                                                        class="form-control" name="threshold_value" type="number"
                                                        step="0.01" min="0" required></div>
                                                <div class="col-md-6"><label class="form-label">شدت</label><select
                                                        class="form-select" name="severity">
                                                        @foreach ($severities as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6"><label class="form-label">Lookback</label><input
                                                        class="form-control" name="lookback_days" type="number"
                                                        min="1" max="90" value="7"></div>
                                                <div class="col-md-6"><label class="form-label">Horizon</label><input
                                                        class="form-control" name="comparison_days" type="number"
                                                        min="1" max="90" value="7"></div>
                                            </div>
                                            <div class="mt-3"><label class="form-label">اقدام پیشنهادی</label>
                                                <textarea class="form-control" name="suggestion" rows="3"></textarea>
                                            </div>
                                            <button class="btn btn-primary w-100 mt-3" type="submit">ثبت
                                                Rule</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-8">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">هشدارهای اخیر</h5>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>عنوان</th>
                                                    <th>Metric</th>
                                                    <th>شدت</th>
                                                    <th>مقدار</th>
                                                    <th>انحراف</th>
                                                    <th>وضعیت</th>
                                                    <th>اقدام</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($alerts as $alert)
                                                    <tr>
                                                        <td><strong>{{ $alert->title }}</strong>
                                                            <div class="text-muted small">{{ $alert->message }}</div>
                                                        </td>
                                                        <td>{{ $alert->domain }} / {{ $alert->metric_key }}</td>
                                                        <td><span
                                                                class="badge bg-label-{{ in_array($alert->severity, ['high', 'critical'], true) ? 'danger' : 'warning' }}">{{ $severities[$alert->severity] ?? $alert->severity }}</span>
                                                        </td>
                                                        <td>{{ number_format((float) $alert->current_value, 2) }}</td>
                                                        <td>{{ is_null($alert->deviation_percent) ? '-' : number_format((float) $alert->deviation_percent, 2) . '%' }}
                                                        </td>
                                                        <td>{{ $alertStatuses[$alert->status] ?? $alert->status }}</td>
                                                        <td>
                                                            <form class="d-flex gap-2" method="POST"
                                                                action="{{ route('bi.insights.alerts.update', $alert) }}">
                                                                @csrf @method('PATCH')
                                                                <select class="form-select form-select-sm"
                                                                    name="status">
                                                                    @foreach ($alertStatuses as $key => $label)
                                                                        <option value="{{ $key }}"
                                                                            @selected($alert->status === $key)>
                                                                            {{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button class="btn btn-sm btn-outline-primary"
                                                                    type="submit">ثبت</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-center text-muted" colspan="7">هشداری ثبت
                                                            نشده است. تحلیل را اجرا کنید یا Rule جدید بسازید.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">پیش بینی های عملیاتی</h5><span
                                    class="badge bg-label-secondary">Moving average weighted</span>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>تاریخ</th>
                                            <th>Metric</th>
                                            <th>واقعی</th>
                                            <th>پیش بینی</th>
                                            <th>بازه</th>
                                            <th>اطمینان</th>
                                            <th>روند</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($forecasts as $forecast)
                                            <tr>
                                                <td>{{ optional($forecast->forecast_date)->format('Y-m-d') }}</td>
                                                <td>{{ $forecast->domain }} / {{ $forecast->metric_key }}</td>
                                                <td>{{ number_format((float) $forecast->actual_value, 2) }}</td>
                                                <td>{{ number_format((float) $forecast->forecast_value, 2) }}</td>
                                                <td>{{ number_format((float) $forecast->lower_bound, 2) }} تا
                                                    {{ number_format((float) $forecast->upper_bound, 2) }}</td>
                                                <td>{{ number_format((float) $forecast->confidence_score, 1) }}%</td>
                                                <td><span
                                                        class="badge bg-label-{{ $forecast->trend_direction === 'down' ? 'danger' : ($forecast->trend_direction === 'up' ? 'success' : 'secondary') }}">{{ $forecast->trend_direction }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center text-muted" colspan="7">هنوز پیش بینی تولید
                                                    نشده است.</td>
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
</body>

</html>
