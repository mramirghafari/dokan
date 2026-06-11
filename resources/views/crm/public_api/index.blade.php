<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>API عمومی CRM - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        .api-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: 1.1fr .9fr;
        }

        .token-box {
            direction: ltr;
            unicode-bidi: plaintext;
            word-break: break-all;
        }

        @media (max-width: 992px) {
            .api-grid {
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
                @include('sections.header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">CRM /</span> API عمومی</h4>
                                <div class="text-muted">کلاینت های توکنی scopeدار برای فرم سایت، ابزارهای بیرونی و ورود
                                    داده حجیم CRM.</div>
                            </div>
                            <a class="btn btn-outline-primary" href="{{ route('crm.dashboard.index') }}">داشبورد CRM</a>
                        </div>

                        @if ($newToken)
                            <div class="alert alert-warning">
                                <strong>توکن جدید:</strong>
                                <div class="token-box mt-2">
                                    client={{ $newToken['code'] }}<br>token={{ $newToken['token'] }}</div>
                            </div>
                        @endif

                        <div class="api-grid mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">ساخت کلاینت API</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('crm.public-api.store') }}">
                                        @csrf
                                        <div class="mb-3"><label class="form-label">عنوان</label><input
                                                class="form-control" name="title" required></div>
                                        <div class="mb-3"><label class="form-label">IPهای مجاز</label><input
                                                class="form-control" name="allowed_ips"
                                                placeholder="5.10.20.30,5.10.20.31"></div>
                                        <div class="row g-2">
                                            @foreach ($scopes as $scope => $label)
                                                <div class="col-md-4">
                                                    <label class="form-check border rounded p-2 d-block">
                                                        <input class="form-check-input" type="checkbox" name="scopes[]"
                                                            value="{{ $scope }}" @checked($loop->first)>
                                                        <span class="form-check-label">{{ $label }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button class="btn btn-primary mt-3" type="submit">ساخت کلاینت و توکن</button>
                                    </form>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Endpointها</h5>
                                </div>
                                <div class="card-body">
                                    <div class="token-box small">
                                        GET /api/crm/{client}/meta<br>
                                        POST /api/crm/{client}/leads<br>
                                        POST /api/crm/{client}/tickets<br>
                                        POST /api/crm/{client}/opportunities
                                    </div>
                                    <div class="text-muted mt-3">Authorization: Bearer TOKEN یا header با نام
                                        X-CRM-Token پذیرفته می شود.</div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">کلاینت های فعال و غیرفعال</h5>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>عنوان</th>
                                            <th>کد</th>
                                            <th>Scope</th>
                                            <th>درخواست</th>
                                            <th>آخرین استفاده</th>
                                            <th>وضعیت</th>
                                            <th>اقدام</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($clients as $client)
                                            <tr>
                                                <td>{{ $client->title }}</td>
                                                <td class="token-box">{{ $client->code }}</td>
                                                <td>{{ collect((array) $client->scopes)->map(fn($scope) => $scopes[$scope] ?? $scope)->implode('، ') }}
                                                </td>
                                                <td>{{ number_format($client->request_count) }}</td>
                                                <td>{{ optional($client->last_used_at)->format('Y-m-d H:i') ?: '-' }}
                                                </td>
                                                <td><span
                                                        class="badge bg-label-{{ $client->is_active ? 'success' : 'secondary' }}">{{ $client->is_active ? 'فعال' : 'غیرفعال' }}</span>
                                                </td>
                                                <td>
                                                    <form method="POST"
                                                        action="{{ route('crm.public-api.toggle', $client) }}">@csrf
                                                        @method('PATCH')<button class="btn btn-sm btn-outline-warning"
                                                            type="submit">تغییر وضعیت</button></form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">کلاینتی ثبت نشده است.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $clients->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
