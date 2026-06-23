<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>راس گیری چک - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
</head>

<body>
    @include('partials.panel-toasts')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections/sidebar')
            <div class="layout-page">
                @include('sections/header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> راس گیری چک
                            </h4>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-info" href="{{ route('Accounting.treasury.cheques') }}">گزارش
                                    چک</a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('Accounting.treasury') }}">بازگشت</a>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('Accounting.treasury.cheques.aging') }}"
                            class="card mb-4">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">تاریخ مبنا</label>
                                        <input type="date" name="base_date" class="form-control"
                                            value="{{ request('base_date', $baseDate) }}">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">وضعیت</label>
                                        <select name="status" class="form-select">
                                            <option value="">همه</option>
                                            @foreach ($statuses as $status => $label)
                                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                                    {{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">جهت</label>
                                        <select name="direction" class="form-select">
                                            <option value="">همه</option>
                                            <option value="incoming" @selected(request('direction') === 'incoming')>دریافتنی</option>
                                            <option value="outgoing" @selected(request('direction') === 'outgoing')>پرداختنی</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">سررسید از</label>
                                        <input type="date" name="due_from" class="form-control"
                                            value="{{ request('due_from') }}">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">سررسید تا</label>
                                        <input type="date" name="due_to" class="form-control"
                                            value="{{ request('due_to') }}">
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">محاسبه</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">جمع مبلغ چک ها</div>
                                        <h4 class="mb-0 text-end">{{ number_format((float) $totalAmount) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">راس روز نسبت به تاریخ مبنا</div>
                                        <h4 class="mb-0 text-end">{{ number_format((float) $weightedDays, 2) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">تاریخ راس</div>
                                        <h4 class="mb-0 text-end">{{ $weightedDueDate ?: '-' }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>گروه</th>
                                            <th class="text-center">تعداد</th>
                                            <th class="text-end">مبلغ</th>
                                            <th class="text-end">راس روز</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($groups as $group)
                                            <tr>
                                                <td>{{ $group['direction'] === 'incoming' ? 'دریافتنی' : 'پرداختنی' }}
                                                    -
                                                    {{ $statuses[$group['status']] ?? $group['status'] }}</td>
                                                <td class="text-center">{{ number_format((int) $group['count']) }}
                                                </td>
                                                <td class="text-end">{{ number_format((float) $group['amount']) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $group['weighted_days'], 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">چکی برای راس
                                                    گیری پیدا نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>شماره چک</th>
                                            <th>جهت</th>
                                            <th>وضعیت</th>
                                            <th>سررسید</th>
                                            <th class="text-end">مبلغ</th>
                                            <th>طرف حساب</th>
                                            <th>سند</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($instruments as $instrument)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $instrument->cheque_number ?: '-' }}</td>
                                                <td>{{ $instrument->direction === 'incoming' ? 'دریافتنی' : 'پرداختنی' }}
                                                </td>
                                                <td>{{ $statuses[$instrument->status] ?? $instrument->status }}</td>
                                                <td>{{ verta_date($instrument->due_date) }}</td>
                                                <td class="text-end">{{ number_format((float) $instrument->amount) }}
                                                </td>
                                                <td>{{ optional($instrument->counterAccount)->code }} -
                                                    {{ optional($instrument->counterAccount)->name }}</td>
                                                <td>{{ optional($instrument->voucher)->voucher_number ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">چکی برای نمایش
                                                    وجود ندارد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @include('sections/footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>

    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
</body>

</html>
