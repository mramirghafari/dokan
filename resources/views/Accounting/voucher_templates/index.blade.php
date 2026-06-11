<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>الگوهای سند حسابداری - دکان دارمینو</title>
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
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> الگوهای سند
                                حسابداری</h4>
                            <a class="btn btn-outline-secondary" href="{{ route('Accounting.vouchers') }}">بازگشت به
                                اسناد</a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>نام الگو</th>
                                            <th>تناوب</th>
                                            <th>سند مبدا</th>
                                            <th class="text-end">جمع بدهکار</th>
                                            <th class="text-end">جمع بستانکار</th>
                                            <th>اقلام</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($templates as $template)
                                            <tr>
                                                <td>{{ $template->name }}<br><small
                                                        class="text-muted">{{ $template->description }}</small></td>
                                                <td>
                                                    @switch($template->frequency)
                                                        @case('monthly')
                                                            ماهانه
                                                        @break

                                                        @case('seasonal')
                                                            فصلی
                                                        @break

                                                        @case('annual')
                                                            سالانه
                                                        @break

                                                        @default
                                                            موردی
                                                    @endswitch
                                                </td>
                                                <td>{{ optional($template->sourceVoucher)->voucher_number ?: '-' }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $template->items->sum('debit_amount')) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $template->items->sum('credit_amount')) }}
                                                </td>
                                                <td>{{ number_format($template->items->count()) }}</td>
                                                <td>
                                                    <form method="POST"
                                                        action="{{ route('Accounting.voucherTemplates.draft', $template) }}"
                                                        class="d-flex flex-wrap gap-1 align-items-center">
                                                        @csrf
                                                        <input type="date" name="voucher_date_en"
                                                            class="form-control form-control-sm"
                                                            value="{{ now()->toDateString() }}" style="width: 145px">
                                                        <input type="text" name="description"
                                                            class="form-control form-control-sm"
                                                            value="ثبت از الگوی {{ $template->name }}"
                                                            style="width: 210px">
                                                        <button class="btn btn-sm btn-primary" type="submit">ساخت
                                                            سند</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @if ($template->items->isNotEmpty())
                                                <tr>
                                                    <td></td>
                                                    <td colspan="6">
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
                                                                    @foreach ($template->items as $item)
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
                                                    <td colspan="7" class="text-center text-muted py-4">هنوز الگوی سند
                                                        ثبت نشده است.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mt-3">
                                {{ $templates->links() }}
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
        <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
        <script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
        <script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
        <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
        <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
        <script src="{{ asset('assets/') }}/js/main.js"></script>
    </body>

    </html>
