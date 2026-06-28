<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>لاگ‌ها و گزارشات - دکان دارمینو</title>
    <meta content="" name="description" />
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        .activity-log-description {
            max-width: 28rem;
            white-space: normal;
            word-break: break-word;
        }

        .activity-log-ip {
            direction: ltr;
            unicode-bidi: plaintext;
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
                        <h4 class="py-3 mb-4">
                            <span class="text-muted fw-light">اطلاعات پایه /</span>
                            لاگ‌ها و گزارشات
                        </h4>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" action="{{ route('activity-logs.index') }}"
                                    class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label" for="user_id">کاربر</label>
                                        <select class="form-select select2" id="user_id" name="user_id">
                                            <option value="">همه کاربران</option>
                                            @foreach ($users as $teamUser)
                                                <option value="{{ $teamUser->id }}"
                                                    @selected((string) ($filters['user_id'] ?? '') === (string) $teamUser->id)>
                                                    {{ $teamUser->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="action">عملیات</label>
                                        <select class="form-select" id="action" name="action">
                                            <option value="">همه عملیات</option>
                                            @foreach ($actionLabels as $key => $label)
                                                <option value="{{ $key }}"
                                                    @selected(($filters['action'] ?? '') === $key)>{{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label" for="section">بخش</label>
                                        <select class="form-select" id="section" name="section">
                                            <option value="">همه بخش‌ها</option>
                                            @foreach ($sectionLabels as $key => $label)
                                                <option value="{{ $key }}"
                                                    @selected(($filters['section'] ?? '') === $key)>{{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label" for="search">جستجو</label>
                                        <input class="form-control" id="search" name="search" type="text"
                                            value="{{ $filters['search'] ?? '' }}"
                                            placeholder="جزئیات، IP یا بخش..." />
                                    </div>
                                    <div class="col-md-2 d-flex gap-2">
                                        <button class="btn btn-primary flex-grow-1" type="submit">اعمال فیلتر</button>
                                        <a class="btn btn-outline-secondary" href="{{ route('activity-logs.index') }}">پاک
                                            کردن</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <h5 class="mb-0">گزارش فعالیت کاربران</h5>
                                <span class="text-muted small">{{ number_format($logs->total()) }} رکورد</span>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>تاریخ</th>
                                            <th>کاربر</th>
                                            <th>عملیات</th>
                                            <th>جزئیات</th>
                                            <th>IP</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($logs as $log)
                                            <tr>
                                                <td>{{ verta_datetime($log->getRawOriginal('created_at')) }}</td>
                                                <td>{{ optional($log->user)->name ?: 'کاربر ناشناس' }}</td>
                                                <td>
                                                    @php($actionKey = strtolower((string) $log->action))
                                                    <span class="badge {{ $actionBadges[$actionKey] ?? 'bg-label-secondary' }}">
                                                        {{ $actionLabels[$actionKey] ?? $log->action }}
                                                    </span>
                                                    @if ($log->section)
                                                        <div class="text-muted small">{{ $sectionLabels[$log->section] ?? $log->section }}</div>
                                                    @endif
                                                </td>
                                                <td class="activity-log-description">{{ $log->description }}</td>
                                                <td class="activity-log-ip">{{ $log->ip ?: '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    هنوز لاگ فعالیتی ثبت نشده است.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            @if ($logs->hasPages())
                                <div class="card-footer">{{ $logs->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script>
        $(function() {
            $('.select2').select2({
                width: '100%',
                dir: 'rtl'
            });
        });
    </script>
</body>

</html>
