<!DOCTYPE html>
<html lang="fa" class="light-style layout-menu-fixed" dir="rtl" data-theme="theme-default"
    data-assets-path="../../assets/" data-template="vertical-menu-template-free">

<head>
    @include('sections.head')
    <title>سبک سازی و مقیاس ERP - دکان دارمینو</title>
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
                        @php($scoreClass = $readiness_score >= 90 ? 'success' : ($readiness_score >= 70 ? 'warning' : 'danger'))
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">ERP /</span> سبک سازی و مقیاس</h4>
                                <div class="text-muted">کنترل index، summary/cache، lookup از راه دور، داده سرد و فشار
                                    جدول های بزرگ.</div>
                            </div>
                            <form method="POST" action="{{ route('erp.scale-hardening.snapshot') }}">
                                @csrf
                                <button class="btn btn-primary" type="submit">ثبت snapshot مقیاس</button>
                            </form>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <span>امتیاز آمادگی</span>
                                        <h2 class="text-{{ $scoreClass }} mt-2 mb-0">{{ $readiness_score }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <span>سطح ریسک</span>
                                        <h2 class="text-{{ $scoreClass }} mt-2 mb-0">
                                            {{ $risk_levels[$risk_level] ?? $risk_level }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <span>جدول های audit شده</span>
                                        <h2 class="mt-2 mb-0">{{ number_format($summary['tables_audited']) }}</h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <span>Scope</span>
                                        <h5 class="mt-3 mb-0 ltr-chip">{{ $scope_label }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            @foreach ($summary as $key => $value)
                                <div class="col-md-3 col-sm-6">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <div class="text-muted small ltr-chip">{{ $key }}</div>
                                            <h5 class="mb-0 mt-2">
                                                {{ is_numeric($value) ? number_format($value) : $value }}</h5>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-7">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">کنترل های باکس سبک سازی</h5>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>شدت</th>
                                                    <th>کنترل</th>
                                                    <th>عدد</th>
                                                    <th>اقدام</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($checks as $check)
                                                    <tr>
                                                        <td><span
                                                                class="badge bg-label-{{ in_array($check['severity'], ['critical', 'high'], true) ? 'danger' : ($check['severity'] === 'medium' ? 'warning' : 'success') }}">{{ $check['severity'] }}</span>
                                                        </td>
                                                        <td>{{ $check['title'] }}<div
                                                                class="text-muted small ltr-chip">{{ $check['key'] }}
                                                            </div>
                                                        </td>
                                                        <td>{{ number_format($check['count']) }}</td>
                                                        <td class="text-wrap">{{ $check['recommendation'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">پروفایل جدول های پرتکرار</h5>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>جدول</th>
                                                    <th>ردیف</th>
                                                    <th>Index</th>
                                                    <th>Scope</th>
                                                    <th>Missing</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($table_profiles as $profile)
                                                    <tr>
                                                        <td>{{ $profile['label'] }}<div
                                                                class="text-muted small ltr-chip">
                                                                {{ $profile['table'] }}</div>
                                                        </td>
                                                        <td>{{ number_format($profile['row_count']) }}</td>
                                                        <td>{{ $profile['index_coverage'] }}%</td>
                                                        <td><span
                                                                class="badge bg-label-{{ $profile['has_scope_columns'] ? 'success' : 'warning' }}">{{ $profile['has_scope_columns'] ? 'scope دارد' : 'scope ناقص' }}</span>
                                                        </td>
                                                        <td class="text-wrap ltr-chip">
                                                            {{ implode(', ', $profile['missing_indexes']) ?: '-' }}
                                                        </td>
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
                                        <h5 class="mb-0">Remote lookup فعال</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            @foreach ($lookup_entities as $entity)
                                                <span
                                                    class="badge bg-label-primary ltr-chip">{{ $entity }}</span>
                                            @endforeach
                                        </div>
                                        <div class="text-muted">Endpoint مشترک: <span
                                                class="ltr-chip">{{ route('erp.scale-hardening.lookup') }}?entity=customers&amp;q=term</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">پیشنهادهای باز</h5>
                                    </div>
                                    <div class="card-body">
                                        @forelse ($recommendations as $recommendation)
                                            <div class="alert alert-warning mb-2">{{ $recommendation }}</div>
                                        @empty
                                            <div class="alert alert-success mb-0">پیشنهاد بحرانی یا متوسط باز نیست.
                                            </div>
                                        @endforelse
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
                                                    <th>Score</th>
                                                    <th>Risk</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($snapshots as $snapshot)
                                                    <tr>
                                                        <td>{{ verta_datetime($snapshot->generated_at) }}</td>
                                                        <td>{{ $snapshot->readiness_score }}</td>
                                                        <td>{{ $snapshot->riskText() }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-4">Snapshot
                                                            ثبت نشده است.</td>
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
        </div>
    </div>
    @include('sections.script')
</body>

</html>
