<!DOCTYPE html>
<html lang="fa" class="light-style layout-menu-fixed" dir="rtl" data-theme="theme-default"
    data-assets-path="../../assets/" data-template="vertical-menu-template-free">

<head>
    @include('sections.head')
    <title>BI جامع - دکان دارمینو</title>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.navbar')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">گزارش ها /</span> BI جامع</h4>
                                <div class="text-muted">فاز پایه BI: کاتالوگ KPI، summary روزانه، refresh log و کنترل
                                    سلامت داده.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-secondary" href="{{ route('bi.executive.index') }}">Executive</a>
                                <a class="btn btn-outline-secondary" href="{{ route('bi.cfo.index') }}">CFO</a>
                                <a class="btn btn-outline-secondary" href="{{ route('bi.reconciliation.index') }}">مغایرت‌گیری</a>
                                <a class="btn btn-outline-primary" href="{{ route('bi.report-builder.index') }}">گزارش
                                    ساز BI</a>
                                <form method="POST" action="{{ route('bi.dashboard.refresh-data-mart') }}">
                                    @csrf
                                    <button class="btn btn-primary" type="submit">Refresh data mart</button>
                                </form>
                                <form method="POST" action="{{ route('bi.dashboard.refresh-crm') }}">
                                    @csrf
                                    <button class="btn btn-outline-secondary" type="submit">Refresh خلاصه CRM</button>
                                </form>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            @foreach ($bi['health'] as $row)
                                <div class="col-md-3">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="text-muted mb-1">{{ $row['metric_key'] }}</div>
                                            <h3 class="mb-1">
                                                {{ is_null($row['value']) ? '-' : number_format($row['value'], 2) }}
                                            </h3>
                                            <span
                                                class="badge bg-label-{{ $row['status'] === 'fresh' ? 'success' : 'warning' }}">{{ $row['status'] === 'fresh' ? 'به روز' : 'نیازمند refresh' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-7">
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">کاتالوگ KPI</h5>
                                        <span
                                            class="badge bg-label-secondary">{{ number_format($bi['metrics']->count()) }}
                                            شاخص</span>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>کلید</th>
                                                    <th>عنوان</th>
                                                    <th>دامنه</th>
                                                    <th>فرمول</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($bi['metrics'] as $metric)
                                                    <tr>
                                                        <td>{{ $metric->metric_key }}</td>
                                                        <td>{{ $metric->title }}</td>
                                                        <td><span
                                                                class="badge bg-label-primary">{{ $metric->domain }}</span>
                                                        </td>
                                                        <td class="text-wrap">{{ $metric->formula }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">آخرین summaryها</h5>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>شاخص</th>
                                                    <th>مقدار</th>
                                                    <th>تاریخ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($bi['summaries'] as $summary)
                                                    <tr>
                                                        <td>{{ $summary->metric_key }}</td>
                                                        <td>{{ number_format((float) $summary->value, 2) }}</td>
                                                        <td>{{ verta_date($summary->summary_date) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-4">هنوز
                                                            summary محاسبه نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Refresh log</h5>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Dataset</th>
                                                    <th>وضعیت</th>
                                                    <th>زمان</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($bi['refresh_logs'] as $log)
                                                    <tr>
                                                        <td>{{ $log->dataset_key }}</td>
                                                        <td><span
                                                                class="badge bg-label-{{ $log->status === 'success' ? 'success' : 'danger' }}">{{ $log->status }}</span>
                                                        </td>
                                                        <td>{{ verta_datetime($log->finished_at) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-4">هنوز
                                                            refresh ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
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
</body>

</html>
