<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>گزارش چک ها - دکان دارمینو</title>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> گزارش چک ها
                            </h4>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-primary"
                                    href="{{ route('Accounting.treasury.cheques.aging') }}">راس گیری چک</a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('Accounting.treasury') }}">بازگشت</a>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('Accounting.treasury.cheques') }}" class="card mb-4">
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">وضعیت</label>
                                        <select name="status" class="form-select">
                                            <option value="">همه وضعیت ها</option>
                                            @foreach ($statuses as $status => $label)
                                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                                    {{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
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
                                        <button type="submit" class="btn btn-primary w-100">فیلتر</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>شماره چک</th>
                                            <th>بانک</th>
                                            <th>جهت</th>
                                            <th>وضعیت</th>
                                            <th>سررسید</th>
                                            <th class="text-end">مبلغ</th>
                                            <th>طرف حساب</th>
                                            <th>محل فعلی</th>
                                            <th>آخرین گردش</th>
                                            <th>سند</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($instruments as $instrument)
                                            @php($lastHistory = $instrument->histories->first())
                                            <tr>
                                                <td>{{ $loop->iteration + ($instruments->currentPage() - 1) * $instruments->perPage() }}
                                                </td>
                                                <td>{{ $instrument->cheque_number ?: '-' }}</td>
                                                <td>{{ $instrument->issuing_bank ?: '-' }}</td>
                                                <td>{{ $instrument->direction === 'incoming' ? 'دریافتنی' : 'پرداختنی' }}
                                                </td>
                                                <td>{{ $statuses[$instrument->status] ?? $instrument->status }}</td>
                                                <td>{{ optional($instrument->due_date)->format('Y-m-d') ?: '-' }}</td>
                                                <td class="text-end">{{ number_format((float) $instrument->amount) }}
                                                </td>
                                                <td>{{ optional($instrument->counterAccount)->code }} -
                                                    {{ optional($instrument->counterAccount)->name }}</td>
                                                <td>
                                                    {{ optional($instrument->currentHolderAccount)->code }}
                                                    {{ optional($instrument->currentHolderAccount)->name ?: $instrument->current_holder_name ?: '-' }}
                                                </td>
                                                <td>
                                                    @if ($lastHistory)
                                                        {{ optional($lastHistory->action_date)->format('Y-m-d') ?: '-' }}<br>
                                                        <small>{{ $statuses[$lastHistory->new_status] ?? $lastHistory->new_status }}</small>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ optional($instrument->voucher)->voucher_number }}</td>
                                                <td style="min-width: 320px">
                                                    <form method="POST"
                                                        action="{{ route('Accounting.treasury.instruments.status', $instrument) }}"
                                                        class="row g-1">
                                                        @csrf
                                                        <div class="col-6">
                                                            <select name="status" class="form-select form-select-sm">
                                                                @foreach ($statuses as $status => $label)
                                                                    <option value="{{ $status }}"
                                                                        @selected($instrument->status === $status)>
                                                                        {{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <select name="settlement_account_id"
                                                                class="form-select form-select-sm">
                                                                <option value="">حساب تسویه/بانک</option>
                                                                @foreach ($treasuryAccounts as $account)
                                                                    <option value="{{ $account->id }}">
                                                                        {{ $account->code }} - {{ $account->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-6">
                                                            <input type="text" name="current_holder_name"
                                                                class="form-control form-control-sm"
                                                                placeholder="تحویل گیرنده">
                                                        </div>
                                                        <div class="col-6">
                                                            <input type="text" name="status_note"
                                                                class="form-control form-control-sm"
                                                                placeholder="شرح">
                                                        </div>
                                                        <div class="col-12">
                                                            <button class="btn btn-sm btn-outline-primary w-100"
                                                                type="submit">ثبت گردش</button>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="12" class="text-center text-muted py-4">چکی با فیلتر
                                                    انتخاب شده پیدا نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3">
                            {{ $instruments->links() }}
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
