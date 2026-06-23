<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    @include('sections.head')
    <title>سلامت و Audit CRM - دکان دارمینو</title>
</head>

<body>
    @include('sweetalert::alert')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @php($scoreClass = $health_score >= 90 ? 'success' : ($health_score >= 70 ? 'warning' : 'danger'))
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">CRM /</span> سلامت داده و Audit
                                </h4>
                                <div class="text-muted">کنترل scope، orphanها، SLA، اقدام بعدی و integrationهای CRM.
                                </div>
                            </div>
                            <form method="POST" action="{{ route('crm.health.snapshot') }}">
                                @csrf
                                <button class="btn btn-primary" type="submit">ثبت snapshot سلامت</button>
                            </form>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><span>امتیاز سلامت</span>
                                        <h2 class="text-{{ $scoreClass }} mt-2 mb-0">{{ $health_score }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><span>سطح ریسک</span>
                                        <h2 class="text-{{ $scoreClass }} mt-2 mb-0">
                                            {{ $risk_levels[$risk_level] ?? $risk_level }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><span>Issue فعال</span>
                                        <h2 class="text-warning mt-2 mb-0">
                                            {{ number_format(count($issues)) }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><span>Scope</span>
                                        <h5 class="mt-3 mb-0 ltr-chip">{{ $scope_label }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            @foreach ($summary as $key => $value)
                                <div class="col-md-2 col-sm-4">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="text-muted small">{{ $key }}</div>
                                            <h4 class="mb-0 mt-1">{{ number_format($value) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Issueهای سلامت CRM</h5>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>شدت</th>
                                            <th>موضوع</th>
                                            <th>تعداد</th>
                                            <th>اقدام پیشنهادی</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($issues as $issue)
                                            <tr>
                                                <td><span
                                                        class="badge bg-label-{{ in_array($issue['severity'], ['critical', 'high'], true) ? 'danger' : ($issue['severity'] === 'medium' ? 'warning' : 'info') }}">{{ $issue['severity'] }}</span>
                                                </td>
                                                <td>{{ $issue['title'] }}<div class="text-muted small ltr-chip">
                                                        {{ $issue['key'] }}</div>
                                                </td>
                                                <td>{{ number_format($issue['count']) }}</td>
                                                <td class="text-wrap">{{ $issue['recommendation'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">Issue فعالی در
                                                    این scope دیده نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Snapshotهای اخیر</h5>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>زمان</th>
                                            <th>Scope</th>
                                            <th>Score</th>
                                            <th>Risk</th>
                                            <th>Issue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($snapshots as $snapshot)
                                            <tr>
                                                <td>{{ verta_datetime($snapshot->generated_at) }}</td>
                                                <td>{{ $snapshot->scope_label }}</td>
                                                <td>{{ $snapshot->health_score }}</td>
                                                <td>{{ $snapshot->riskText() }}</td>
                                                <td>{{ count($snapshot->issues ?: []) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">Snapshot ثبت نشده
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
    @include('sections.script')
</body>

</html>
