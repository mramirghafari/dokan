<!DOCTYPE html>
<html lang="fa" class="light-style layout-menu-fixed" dir="rtl" data-theme="theme-default"
    data-assets-path="../../assets/" data-template="vertical-menu-template-free">

<head>
    @include('sections.head')
    <title>Ù‡Ø´Ø¯Ø§Ø± Ùˆ Ù¾ÛŒØ´ Ø¨ÛŒÙ†ÛŒ BI - Ø¯Ú©Ø§Ù† Ø¯Ø§Ø±Ù…ÛŒÙ†Ùˆ</title>
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
                                <h4 class="mb-1"><span class="text-muted fw-light">BI /</span> Ù‡Ø´Ø¯Ø§Ø±ØŒ anomaly Ùˆ Ù¾ÛŒØ´
                                    Ø¨ÛŒÙ†ÛŒ</h4>
                                <div class="text-muted">RuleÙ‡Ø§ÛŒ Ø¹Ù…Ù„ÛŒØ§ØªÛŒ Ø±ÙˆÛŒ data martØŒ ØªØ´Ø®ÛŒØµ Ø§ÙØª/Ø±Ø´Ø¯ ØºÛŒØ±Ø¹Ø§Ø¯ÛŒ Ùˆ forecast
                                    Ú©ÙˆØªØ§Ù‡ Ù…Ø¯Øª.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-secondary" href="{{ route('bi.report-builder.index') }}">Ú¯Ø²Ø§Ø±Ø´
                                    Ø³Ø§Ø²</a>
                                <form method="POST" action="{{ route('bi.insights.run') }}">
                                    @csrf
                                    <button class="btn btn-primary" type="submit">Ø§Ø¬Ø±Ø§ÛŒ ØªØ­Ù„ÛŒÙ„</button>
                                </form>
                            </div>
                        </div>

                        <div class="insight-kpis mb-4">
                            <div class="card">
                                <div class="card-body"><span>Ù‡Ø´Ø¯Ø§Ø± Ø¨Ø§Ø²</span>
                                    <h3 class="mt-2 mb-0 text-warning">{{ number_format($stats['open_alerts']) }}</h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>Ø¨Ø­Ø±Ø§Ù†ÛŒ/Ù…Ù‡Ù…</span>
                                    <h3 class="mt-2 mb-0 text-danger">{{ number_format($stats['critical_alerts']) }}
                                    </h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>Rule ÙØ¹Ø§Ù„</span>
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
                                <h5 class="mb-0">Ù‚Ø§Ù†ÙˆÙ† Ù‡Ø´Ø¯Ø§Ø± Ø¬Ø¯ÛŒØ¯</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('bi.insights.rules.store') }}">
                                    @csrf
                                    <div class="rule-grid">
                                        <div><label class="form-label">Ø¹Ù†ÙˆØ§Ù†</label><input class="form-control"
                                                name="title" required></div>
                                        <div><label class="form-label">Ø¯Ø§Ù…Ù†Ù‡</label><input class="form-control"
                                                name="domain" list="bi-domains" required></div>
                                        <div><label class="form-label">Ø´Ø§Ø®Øµ</label><input class="form-control"
                                                name="metric_key" list="bi-metrics" required></div>
                                        <div>
                                            <label class="form-label">Ù†ÙˆØ¹</label>
                                            <select class="form-select" name="rule_type">
                                                @foreach ($ruleTypes as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label">Ø¹Ù…Ù„Ú¯Ø± Ø¢Ø³ØªØ§Ù†Ù‡</label>
                                            <select class="form-select" name="operator">
                                                <option value="gte">Ø¨Ø²Ø±Ú¯ØªØ±/Ø¨Ø±Ø§Ø¨Ø±</option>
                                                <option value="lte">Ú©ÙˆÚ†Ú©ØªØ±/Ø¨Ø±Ø§Ø¨Ø±</option>
                                            </select>
                                        </div>
                                        <div><label class="form-label">Ø¢Ø³ØªØ§Ù†Ù‡/Ø¯Ø±ØµØ¯</label><input class="form-control"
                                                type="number" step="0.01" name="threshold_value" required></div>
                                        <div>
                                            <label class="form-label">Ø´Ø¯Øª</label>
                                            <select class="form-select" name="severity">
                                                @foreach ($severities as $key => $label)
                                                    <option value="{{ $key }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div><label class="form-label">Ø±ÙˆØ²Ù‡Ø§ÛŒ Ù…Ø¨Ù†Ø§</label><input class="form-control"
                                                type="number" name="lookback_days" value="7" min="1"></div>
                                        <div><label class="form-label">Ø§ÙÙ‚ Ù¾ÛŒØ´ Ø¨ÛŒÙ†ÛŒ</label><input class="form-control"
                                                type="number" name="comparison_days" value="7" min="1">
                                        </div>
                                    </div>
                                    <div class="mt-3"><label class="form-label">Ø§Ù‚Ø¯Ø§Ù… Ù¾ÛŒØ´Ù†Ù‡Ø§Ø¯ÛŒ</label>
                                        <textarea class="form-control" name="suggestion" rows="2"></textarea>
                                    </div>
                                    <button class="btn btn-outline-primary mt-3" type="submit">Ø«Ø¨Øª Rule</button>
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
                                        <h5 class="mb-0">Ù‡Ø´Ø¯Ø§Ø±Ù‡Ø§ÛŒ Ø¹Ù…Ù„ÛŒØ§ØªÛŒ</h5>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Ø¹Ù†ÙˆØ§Ù†</th>
                                                    <th>Ø´Ø§Ø®Øµ</th>
                                                    <th>Ø´Ø¯Øª</th>
                                                    <th>Ù…Ù‚Ø¯Ø§Ø±</th>
                                                    <th>ÙˆØ¶Ø¹ÛŒØª</th>
                                                    <th>Ø§Ù‚Ø¯Ø§Ù…</th>
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
                                                                    type="submit">Ø«Ø¨Øª</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted">Ù‡Ù†ÙˆØ² Ù‡Ø´Ø¯Ø§Ø±ÛŒ
                                                            Ø«Ø¨Øª Ù†Ø´Ø¯Ù‡ Ø§Ø³Øª.</td>
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
                                        <h5 class="mb-0">ForecastÙ‡Ø§ÛŒ ØªØ§Ø²Ù‡</h5>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Ø´Ø§Ø®Øµ</th>
                                                    <th>ØªØ§Ø±ÛŒØ®</th>
                                                    <th>Ù¾ÛŒØ´ Ø¨ÛŒÙ†ÛŒ</th>
                                                    <th>Ø±ÙˆÙ†Ø¯</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($forecasts as $forecast)
                                                    <tr>
                                                        <td>{{ $forecast->domain }}<div class="text-muted small">
                                                                {{ $forecast->metric_key }}</div>
                                                        </td>
                                                        <td>{{ verta_date($forecast->forecast_date) }}</td>
                                                        <td>{{ number_format((float) $forecast->forecast_value, 2) }}
                                                            <div class="text-muted small">
                                                                {{ number_format((float) $forecast->lower_bound, 2) }}
                                                                ØªØ§
                                                                {{ number_format((float) $forecast->upper_bound, 2) }}
                                                            </div>
                                                        </td>
                                                        <td><span
                                                                class="badge bg-label-{{ $forecast->trend_direction === 'down' ? 'danger' : ($forecast->trend_direction === 'up' ? 'success' : 'secondary') }}">{{ $forecast->trend_direction }}</span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted">Ø¨Ø¹Ø¯ Ø§Ø² Ø§Ø¬Ø±Ø§ÛŒ
                                                            ØªØ­Ù„ÛŒÙ„ØŒ forecastÙ‡Ø§ Ø§ÛŒÙ†Ø¬Ø§ Ù†Ù…Ø§ÛŒØ´ Ø¯Ø§Ø¯Ù‡ Ù…ÛŒ Ø´ÙˆÙ†Ø¯.</td>
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
                                <h5 class="mb-0">RuleÙ‡Ø§ÛŒ ÙØ¹Ø§Ù„</h5>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Ø¹Ù†ÙˆØ§Ù†</th>
                                            <th>Ø¯Ø§Ù…Ù†Ù‡/Ø´Ø§Ø®Øµ</th>
                                            <th>Ù†ÙˆØ¹</th>
                                            <th>Ø¢Ø³ØªØ§Ù†Ù‡</th>
                                            <th>Ø§Ù‚Ø¯Ø§Ù… Ù¾ÛŒØ´Ù†Ù‡Ø§Ø¯ÛŒ</th>
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
                                                <td colspan="5" class="text-center text-muted">Rule ÙØ¹Ø§Ù„ÛŒ ÙˆØ¬ÙˆØ¯
                                                    Ù†Ø¯Ø§Ø±Ø¯.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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
</body>

</html>
