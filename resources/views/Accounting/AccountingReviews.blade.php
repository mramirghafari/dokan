<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>بسته بازبینی حسابدار - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
    <style>
        .review-metric {
            border: 1px solid rgba(75, 70, 92, .12);
            border-radius: .75rem;
            background: #fff;
            height: 100%;
        }

        .review-metric strong {
            display: block;
            font-size: 1.35rem;
            line-height: 1.7;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .25rem .65rem;
            font-size: .78rem;
            font-weight: 600;
        }

        .status-ok {
            background: rgba(40, 199, 111, .12);
            color: #16834f;
        }

        .status-warn {
            background: rgba(255, 159, 67, .14);
            color: #a75f08;
        }

        .status-danger {
            background: rgba(234, 84, 85, .12);
            color: #b42525;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        @media print {

            .layout-menu,
            .navbar,
            .no-print,
            .footer {
                display: none !important;
            }

            .layout-page,
            .content-wrapper,
            .container-xxl {
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>

<body>
    @include('partials.panel-toasts')
    @php
        $summary = $review['summary'];
        $money = fn($value) => number_format((float) $value);
        $count = fn($value) => number_format((int) $value);
        $statusClass = fn($value) => (int) $value === 0 ? 'status-ok' : 'status-danger';
        $statusText = fn($value) => (int) $value === 0 ? 'بدون مغایرت' : 'نیازمند بررسی';
        $receiptType = function ($type) {
            return match ((int) $type) {
                1 => 'رسید انبار',
                2 => 'حواله انبار',
                3 => 'انتقال',
                10 => 'برگشت رسید',
                11 => 'برگشت حواله',
                12 => 'برگشت ترکیبی',
                default => 'نوع ' . $type,
            };
        };
    @endphp
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections/sidebar')
            <div class="layout-page">
                @include('sections/header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div id="tour-accounting-reviews-page" class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">مالی و حسابداری /</span> بسته
                                    بازبینی حسابدار</h4>
                                <div class="text-muted small">کنترل تراز اسناد، اتصال رسیدهای انبار به سند مالی، موجودی
                                    منفی و مغایرت دفتر گردش کالا با مانده لحظه ای.</div>
                            </div>
                            <div class="d-flex gap-2 no-print">
                                <button class="btn btn-label-secondary" type="button" onclick="window.print()">چاپ
                                    گزارش</button>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('Accounting.legalLedgers') }}">دفاتر و تراز</a>
                                <a class="btn btn-primary" href="{{ route('Accounting.vouchers') }}">اسناد حسابداری</a>
                            </div>
                        </div>

                        <form id="tour-accounting-reviews-filters" method="GET" action="{{ route('Accounting.AccountingReviews') }}"
                            class="card mb-4 no-print">
                            <div class="card-body row g-3 align-items-end">
                                <div class="col-12 col-md-3">
                                    <label class="form-label">از تاریخ سند</label>
                                    <input type="date" name="from_date" class="form-control"
                                        value="{{ $filters['from_date'] }}">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">تا تاریخ سند</label>
                                    <input type="date" name="to_date" class="form-control"
                                        value="{{ $filters['to_date'] }}">
                                </div>
                                <div class="col-12 col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">به روزرسانی بازبینی</button>
                                </div>
                                <div class="col-12 col-md-3">
                                    <a class="btn btn-label-secondary w-100"
                                        href="{{ route('Accounting.AccountingReviews') }}">حذف فیلتر</a>
                                </div>
                            </div>
                        </form>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-3">
                                <div class="review-metric p-3">
                                    <small class="text-muted">جمع بدهکار</small>
                                    <strong class="text-end">{{ $money($summary['debit']) }}</strong>
                                    <span
                                        class="status-pill {{ round((float) $summary['difference'], 2) === 0.0 ? 'status-ok' : 'status-danger' }}">اختلاف
                                        کل: {{ $money(abs($summary['difference'])) }}</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="review-metric p-3">
                                    <small class="text-muted">جمع بستانکار</small>
                                    <strong class="text-end">{{ $money($summary['credit']) }}</strong>
                                    <span
                                        class="status-pill {{ round((float) $summary['difference'], 2) === 0.0 ? 'status-ok' : 'status-danger' }}">تراز
                                        کلی اسناد</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="review-metric p-3">
                                    <small class="text-muted">اسناد حسابداری</small>
                                    <strong class="text-end">{{ $count($summary['vouchers']) }}</strong>
                                    <span class="text-muted small">موقت: {{ $count($summary['draft_vouchers']) }} |
                                        دائم: {{ $count($summary['permanent_vouchers']) }}</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="review-metric p-3">
                                    <small class="text-muted">سند برگشت انبار</small>
                                    <strong class="text-end">{{ $count($summary['return_receipts']) }}</strong>
                                    <span class="status-pill status-ok">قابل ردیابی تا سند اصلی</span>
                                </div>
                            </div>
                        </div>

                        <div id="tour-accounting-reviews-table" class="row g-3 mb-4">
                            @foreach ([['title' => 'اسناد نامتوازن', 'value' => $summary['unbalanced_vouchers']], ['title' => 'اسناد بدون ردیف', 'value' => $summary['vouchers_without_items']], ['title' => 'ردیف بدون حساب معتبر', 'value' => $summary['missing_account_items']], ['title' => 'رسید تایید شده بدون سند مالی', 'value' => $summary['approved_receipts_without_voucher']], ['title' => 'موجودی منفی یا رزرو نامعتبر', 'value' => $summary['negative_balances']], ['title' => 'مغایرت ledger و مانده', 'value' => $summary['ledger_balance_mismatches']]] as $item)
                                <div class="col-12 col-md-4 col-xl-2">
                                    <div class="review-metric p-3">
                                        <small class="text-muted">{{ $item['title'] }}</small>
                                        <strong class="text-end">{{ $count($item['value']) }}</strong>
                                        <span
                                            class="status-pill {{ $statusClass($item['value']) }}">{{ $statusText($item['value']) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">چک لیست تحویل به حسابدار</h5>
                                <span class="badge bg-label-primary">مرحله نهایی حسابداری/انبارداری</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12 col-lg-6">
                                        <div class="alert alert-light border mb-0">
                                            <strong>کنترل مالی:</strong>
                                            تراز کل باید صفر باشد، سند نامتوازن یا بدون ردیف نباید باقی بماند، ردیف سند
                                            باید حساب معتبر داشته باشد و سندهای موقت مهم قبل از بستن دوره تعیین تکلیف
                                            شوند.
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <div class="alert alert-light border mb-0">
                                            <strong>کنترل انبار:</strong>
                                            هر رسید تایید شده باید سند مالی موجودی داشته باشد، موجودی لحظه ای با دفتر
                                            گردش کالا بخواند، برگشت ها سند مستقل داشته باشند و برگشت بیشتر از مانده اصلی
                                            در سیستم رد شود.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">اسناد نامتوازن</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>سند</th>
                                            <th>تاریخ</th>
                                            <th>نوع</th>
                                            <th>وضعیت</th>
                                            <th class="text-end">بدهکار</th>
                                            <th class="text-end">بستانکار</th>
                                            <th class="text-end">اختلاف</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($review['unbalanced_vouchers'] as $row)
                                            <tr>
                                                <td>{{ $row->voucher_number ?: $row->id }}</td>
                                                <td>{{ optional($row->voucher_date_en ? \Carbon\Carbon::parse($row->voucher_date_en) : null)->format('Y-m-d') ?: '-' }}
                                                </td>
                                                <td>{{ $row->document_type ?: '-' }}</td>
                                                <td>{{ $row->status ?: '-' }}</td>
                                                <td class="text-end">{{ $money($row->debit) }}</td>
                                                <td class="text-end">{{ $money($row->credit) }}</td>
                                                <td class="text-end text-danger">
                                                    {{ $money(abs((float) $row->debit - (float) $row->credit)) }}</td>
                                                <td><a class="btn btn-sm btn-label-primary"
                                                        href="{{ route('Accounting.vouchers.edit', $row->id) }}">بررسی</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">سند نامتوازن
                                                    پیدا نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">اسناد بدون ردیف</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>سند</th>
                                                    <th>تاریخ</th>
                                                    <th>نوع</th>
                                                    <th>وضعیت</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($review['vouchers_without_items'] as $row)
                                                    <tr>
                                                        <td>{{ $row->voucher_number ?: $row->id }}</td>
                                                        <td>{{ optional($row->voucher_date_en ? \Carbon\Carbon::parse($row->voucher_date_en) : null)->format('Y-m-d') ?: '-' }}
                                                        </td>
                                                        <td>{{ $row->document_type ?: '-' }}</td>
                                                        <td>{{ $row->status ?: '-' }}</td>
                                                        <td><a class="btn btn-sm btn-label-primary"
                                                                href="{{ route('Accounting.vouchers.edit', $row->id) }}">تکمیل</a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">موردی
                                                            وجود ندارد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">ردیف سند بدون حساب معتبر</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ردیف</th>
                                                    <th>سند</th>
                                                    <th>حساب ثبت شده</th>
                                                    <th>نوع سند</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($review['missing_account_items'] as $row)
                                                    <tr>
                                                        <td>{{ $row->id }}</td>
                                                        <td>{{ $row->voucher_number ?: $row->voucher_id }}</td>
                                                        <td>{{ $row->account_id ?: '-' }}</td>
                                                        <td>{{ $row->document_type ?: '-' }}</td>
                                                        <td><a class="btn btn-sm btn-label-primary"
                                                                href="{{ route('Accounting.vouchers.edit', $row->voucher_id) }}">اصلاح</a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">همه
                                                            ردیف ها حساب معتبر دارند.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">رسید تایید شده بدون سند مالی موجودی</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>رسید</th>
                                                    <th>تاریخ</th>
                                                    <th>نوع</th>
                                                    <th>منبع برگشت</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($review['receipt_without_voucher'] as $row)
                                                    <tr>
                                                        <td>{{ $row->number ?: $row->id }}</td>
                                                        <td>{{ $row->date_en ?: '-' }}</td>
                                                        <td>{{ $receiptType($row->type) }}</td>
                                                        <td>{{ $row->return_source_receipt_id ?: '-' }}</td>
                                                        <td>
                                                            @if ($row->store_id)
                                                                <a class="btn btn-sm btn-label-primary"
                                                                    href="{{ route('stocks.storeReceiptShow', [$row->store_id, $row->id]) }}">بررسی
                                                                    رسید</a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">همه
                                                            رسیدهای تایید شده سند مالی دارند.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xl-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">موجودی منفی یا رزرو نامعتبر</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>کالا</th>
                                                    <th>انبار</th>
                                                    <th class="text-end">موجودی</th>
                                                    <th class="text-end">واحد فرعی</th>
                                                    <th class="text-end">رزرو</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($review['negative_balances'] as $row)
                                                    <tr>
                                                        <td>{{ $row->product_name ?: $row->product_id }}</td>
                                                        <td>{{ $row->store_name ?: $row->store_id }}</td>
                                                        <td
                                                            class="text-end {{ (float) $row->quantity < 0 ? 'text-danger' : '' }}">
                                                            {{ number_format((float) $row->quantity, 3) }}</td>
                                                        <td
                                                            class="text-end {{ (float) $row->quantity_sub_unit < 0 ? 'text-danger' : '' }}">
                                                            {{ number_format((float) $row->quantity_sub_unit, 3) }}
                                                        </td>
                                                        <td
                                                            class="text-end {{ (float) $row->reserved_quantity > (float) $row->quantity ? 'text-danger' : '' }}">
                                                            {{ number_format((float) $row->reserved_quantity, 3) }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">موجودی
                                                            منفی یا رزرو بیش از موجودی دیده نشد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">مغایرت دفتر گردش کالا با موجودی لحظه ای</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>کالا</th>
                                            <th>انبار</th>
                                            <th>مکان</th>
                                            <th class="text-end">مانده سیستم</th>
                                            <th class="text-end">مانده ledger</th>
                                            <th class="text-end">واحد فرعی سیستم</th>
                                            <th class="text-end">واحد فرعی ledger</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($review['ledger_mismatches'] as $row)
                                            <tr>
                                                <td>{{ $row->product_name ?: $row->product_id }}</td>
                                                <td>{{ $row->store_name ?: $row->store_id }}</td>
                                                <td>{{ $row->warehouse_location_id ?: '-' }}</td>
                                                <td class="text-end">{{ number_format((float) $row->quantity, 3) }}
                                                </td>
                                                <td class="text-end text-danger">
                                                    {{ number_format((float) $row->ledger_quantity, 3) }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $row->quantity_sub_unit, 3) }}</td>
                                                <td class="text-end text-danger">
                                                    {{ number_format((float) $row->ledger_sub_unit, 3) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">مغایرتی بین
                                                    دفتر گردش و موجودی لحظه ای پیدا نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">آخرین اسناد برگشت انبار</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>سند برگشت</th>
                                            <th>تاریخ</th>
                                            <th>نوع</th>
                                            <th>سند اصلی</th>
                                            <th>وضعیت</th>
                                            <th>علت</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($review['return_receipts'] as $row)
                                            <tr>
                                                <td>{{ $row->number ?: $row->id }}</td>
                                                <td>{{ $row->date_en ?: '-' }}</td>
                                                <td>{{ $receiptType($row->type) }}</td>
                                                <td>{{ $row->return_source_receipt_id }}</td>
                                                <td>{{ $row->document_status ?: '-' }}</td>
                                                <td>{{ \Illuminate\Support\Str::limit($row->return_reason ?: '-', 80) }}
                                                </td>
                                                <td>
                                                    @if ($row->store_id)
                                                        <a class="btn btn-sm btn-label-primary"
                                                            href="{{ route('stocks.storeReceiptShow', [$row->store_id, $row->id]) }}">مشاهده</a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">هنوز سند برگشت
                                                    انبار ثبت نشده است.</td>
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
