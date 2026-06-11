<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>دریافت و پرداخت - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('assets/') }}/js/config.js"></script>
</head>

<body>
    @include('sweetalert::alert')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections/sidebar')
            <div class="layout-page">
                @include('sections/header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> دریافت و
                                پرداخت</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-dark" href="{{ route('Accounting.treasury.liquidity') }}">
                                    <i class="ti ti-report-analytics me-1"></i> مانده نقدینگی
                                </a>
                                <a class="btn btn-outline-success"
                                    href="{{ route('Accounting.treasury.cashForecast') }}">
                                    <i class="ti ti-chart-arrows-vertical me-1"></i> پیش بینی نقدینگی
                                </a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('Accounting.treasury.pettyCash') }}">
                                    <i class="ti ti-wallet me-1"></i> تنخواه
                                </a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('Accounting.treasury.chequeBooks') }}">
                                    <i class="ti ti-notebook me-1"></i> دسته چک و هشدارها
                                </a>
                                <a class="btn btn-outline-warning"
                                    href="{{ route('Accounting.treasury.bankReconciliation') }}">
                                    <i class="ti ti-checkup-list me-1"></i> مغایرت بانکی
                                </a>
                                <a class="btn btn-outline-info" href="{{ route('Accounting.treasury.cheques') }}">
                                    <i class="ti ti-report-money me-1"></i> گزارش چک
                                </a>
                                <a class="btn btn-outline-primary"
                                    href="{{ route('Accounting.treasury.transfer.create') }}">
                                    <i class="ti ti-arrows-transfer-up me-1"></i> انتقال بین حساب ها
                                </a>
                                <a class="btn btn-primary" href="{{ route('Accounting.treasury.create') }}">
                                    <i class="ti ti-plus me-1"></i> ثبت دریافت/پرداخت
                                </a>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="card">
                            <div class="card-datatable table-responsive py-0">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr class="text-center">
                                            <th>#</th>
                                            <th>نوع</th>
                                            <th>شماره سند</th>
                                            <th>تاریخ</th>
                                            <th>شرح</th>
                                            <th>مبلغ</th>
                                            <th>چک / وضعیت</th>
                                            <th>وضعیت</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($vouchers as $voucher)
                                            <tr>
                                                <td class="text-center">
                                                    {{ $loop->iteration + ($vouchers->currentPage() - 1) * $vouchers->perPage() }}
                                                </td>
                                                <td class="text-center">
                                                    @if ($voucher->document_type === 'treasury_receipt')
                                                        <span class="badge bg-label-success">دریافت</span>
                                                    @elseif ($voucher->document_type === 'treasury_transfer')
                                                        <span class="badge bg-label-info">انتقال</span>
                                                    @elseif (str_starts_with($voucher->document_type, 'treasury_cheque_'))
                                                        <span class="badge bg-label-primary">وضعیت چک</span>
                                                    @else
                                                        <span class="badge bg-label-danger">پرداخت</span>
                                                    @endif
                                                </td>
                                                <td>{{ $voucher->voucher_number }}</td>
                                                <td>{{ $voucher->voucher_date_fa ?: optional($voucher->voucher_date_en)->format('Y-m-d') }}
                                                </td>
                                                <td>{{ $voucher->description }}</td>
                                                <td class="text-end">{{ number_format((float) $voucher->total_debit) }}
                                                </td>
                                                <td>
                                                    @forelse($voucher->treasuryInstruments as $instrument)
                                                        <div class="small mb-2">
                                                            <div>{{ $instrument->cheque_number ?: 'بدون شماره' }} -
                                                                {{ $instrument->issuing_bank ?: 'بانک نامشخص' }}</div>
                                                            <div class="text-muted">سررسید:
                                                                {{ optional($instrument->due_date)->format('Y-m-d') ?: '-' }}
                                                            </div>
                                                            <div class="text-muted">محل فعلی:
                                                                {{ optional($instrument->currentHolderAccount)->name ?: ($instrument->current_holder_name ?: '-') }}
                                                            </div>
                                                        </div>
                                                        <form method="POST"
                                                            action="{{ route('Accounting.treasury.instruments.status', $instrument) }}"
                                                            class="d-flex flex-wrap gap-1 align-items-center">
                                                            @csrf
                                                            <select name="status" class="form-select form-select-sm"
                                                                style="min-width: 130px">
                                                                @foreach ($statuses as $status => $label)
                                                                    <option value="{{ $status }}"
                                                                        @selected($instrument->status === $status)>
                                                                        {{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                            <select name="settlement_account_id"
                                                                class="form-select form-select-sm"
                                                                style="min-width: 150px">
                                                                <option value="">حساب تسویه</option>
                                                                @foreach ($treasuryAccounts as $account)
                                                                    <option value="{{ $account->id }}">
                                                                        {{ $account->code }} - {{ $account->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <input type="text" name="current_holder_name"
                                                                class="form-control form-control-sm"
                                                                style="min-width: 140px"
                                                                placeholder="تحویل گیرنده/محل فعلی">
                                                            <input type="text" name="status_note"
                                                                class="form-control form-control-sm"
                                                                style="min-width: 140px" placeholder="شرح گردش">
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-primary">ثبت</button>
                                                        </form>
                                                        @if ($instrument->histories->isNotEmpty())
                                                            <div class="small text-muted mt-1">
                                                                آخرین گردش:
                                                                {{ optional($instrument->histories->first()->action_date)->format('Y-m-d') ?: '-' }}
                                                                -
                                                                {{ $statuses[$instrument->histories->first()->new_status] ?? $instrument->histories->first()->new_status }}
                                                            </div>
                                                        @endif
                                                    @empty
                                                        <span class="text-muted">-</span>
                                                    @endforelse
                                                </td>
                                                <td class="text-center">
                                                    @if ($voucher->is_permanent)
                                                        <span class="badge bg-label-success">دائمی</span>
                                                    @else
                                                        <span class="badge bg-label-warning">موقت</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if (!$voucher->is_permanent)
                                                        <form
                                                            action="{{ route('Accounting.vouchers.permanent', $voucher) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-success">دائمی
                                                                کن</button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted">ثبت نهایی</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if ($voucher->items->isNotEmpty())
                                                <tr>
                                                    <td></td>
                                                    <td colspan="8">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>حساب</th>
                                                                        <th>شرح ردیف</th>
                                                                        <th class="text-end">بدهکار</th>
                                                                        <th class="text-end">بستانکار</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($voucher->items as $item)
                                                                        <tr>
                                                                            <td>{{ optional($item->account)->code }} -
                                                                                {{ optional($item->account)->name }}
                                                                            </td>
                                                                            <td>{{ $item->description }}</td>
                                                                            <td class="text-end">
                                                                                {{ number_format((float) $item->debit_amount) }}
                                                                            </td>
                                                                            <td class="text-end">
                                                                                {{ number_format((float) $item->credit_amount) }}
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-4">هنوز دریافت یا
                                                    پرداختی ثبت نشده است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3">
                            {{ $vouchers->links() }}
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
</body>

</html>
