<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>پیشخوان نماینده</title>
    <meta content="" name="description" />
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
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
                        <div class="row mb-4">
                            <div class="col-12 col-md-6 mb-3">
                                <a class="btn btn-primary waves-effect waves-light w-100"
                                    href="{{ route('products.neworder') }}">
                                    <x-ui.icon name="plus" class="me-2" />
                                    ثبت سفارش جدید
                                </a>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <a class="btn btn-outline-primary waves-effect w-100"
                                    href="{{ route('invoices.myInvoices') }}">
                                    <x-ui.icon name="file-text" class="me-2" />
                                    مرور فاکتورهای من
                                </a>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12 col-md-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <small class="text-muted d-block mb-2">کل سفارش ها</small>
                                        <h3 class="mb-0">{{ number_format($AllFactorCount) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <small class="text-muted d-block mb-2">سفارش های تایید شده</small>
                                        <h3 class="mb-0">{{ number_format($MyAcceptedFactorsCount) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <small class="text-muted d-block mb-2">فروش امروز</small>
                                        <h3 class="mb-0">{{ number_format($todaySum) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <small class="text-muted d-block mb-2">فروش این ماه</small>
                                        <h3 class="mb-0">{{ number_format($monthSum) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-lg-5 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">خلاصه حساب نماینده</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="text-muted">نام کاربر</span>
                                            <strong>{{ $User->name }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="text-muted">موبایل</span>
                                            <strong>{{ $User->mobile ?? 'وارد نشده' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-3">
                                            <span class="text-muted">جمع کل سفارش ها</span>
                                            <strong>{{ number_format($AllFactorPrices) }} ریال</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-0">
                                            <span class="text-muted">فروش این هفته</span>
                                            <strong>{{ number_format($weekSum) }} ریال</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-7 mb-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">سفارش های امروز</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>شماره</th>
                                                    <th>مبلغ</th>
                                                    <th>وضعیت</th>
                                                    <th>عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($DayFactors as $factor)
                                                    <tr>
                                                        <td>{{ $factor->invoiceID }}</td>
                                                        <td>{{ number_format((int) str_replace(',', '', $factor->fullPrice)) }}
                                                            ریال</td>
                                                        <td>
                                                            @if ($factor->status == 0)
                                                                <span class="badge bg-label-warning">در انتظار
                                                                    تایید</span>
                                                            @elseif(in_array($factor->status, [1, 4]))
                                                                <span class="badge bg-label-success">تایید شده</span>
                                                            @elseif($factor->status == 3)
                                                                <span class="badge bg-label-danger">رد شده</span>
                                                            @else
                                                                <span class="badge bg-label-secondary">ثبت شده</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('pishFactorInfo', $factor->id) }}"
                                                                class="btn btn-sm btn-outline-primary">مشاهده</a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">امروز
                                                            سفارشی ثبت نشده است.</td>
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
        <div class="drag-target"></div>
    </div>
    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
</body>

</html>
