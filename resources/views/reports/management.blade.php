<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>گزارش مدیریتی - دکان دارمینو</title>
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
                        <h4 class="py-3 mb-4"><span class="text-muted fw-light">گزارش ها /</span> گزارش مدیریتی</h4>
                        <div class="card mb-4">
                            <div class="card-body">
                                <form class="row g-3 align-items-end" method="get"
                                    action="{{ route('reports.management') }}">
                                    <div class="col-md-3">
                                        <label class="form-label">از تاریخ میلادی</label>
                                        <input class="form-control" name="start_date" type="date"
                                            value="{{ $startDate->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">تا تاریخ میلادی</label>
                                        <input class="form-control" name="end_date" type="date"
                                            value="{{ $endDate->format('Y-m-d') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">قالب گزارش</label>
                                        <select class="form-select" name="template_id">
                                            <option value="">داشبورد اجرایی پیش فرض</option>
                                            @foreach ($templates as $template)
                                                <option value="{{ $template->id }}" @selected(optional($selectedTemplate)->id === $template->id)>
                                                    {{ $template->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3"><button class="btn btn-primary" type="submit">بروزرسانی
                                            گزارش</button></div>
                                </form>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <form method="post" action="{{ route('reports.management.snapshot') }}">
                                        @csrf
                                        <input name="start_date" type="hidden"
                                            value="{{ $startDate->format('Y-m-d') }}">
                                        <input name="end_date" type="hidden" value="{{ $endDate->format('Y-m-d') }}">
                                        <input name="template_id" type="hidden"
                                            value="{{ optional($selectedTemplate)->id }}">
                                        <button class="btn btn-outline-secondary" type="submit">ذخیره snapshot</button>
                                    </form>
                                    <a class="btn btn-outline-success"
                                        href="{{ route('reports.management.export', ['format' => 'excel', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}">خروجی
                                        Excel</a>
                                    <a class="btn btn-outline-dark" target="_blank"
                                        href="{{ route('reports.management.export', ['format' => 'pdf', 'start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}">چاپ
                                        / PDF</a>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><span class="text-muted">فروش کل</span>
                                        <h3>{{ number_format($report['sales']['sales_amount']) }}</h3>
                                        <small>{{ number_format($report['sales']['orders_count']) }} سفارش</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><span class="text-muted">فروش خالص</span>
                                        <h3>{{ number_format($report['sales']['net_amount']) }}</h3><small>میانگین
                                            سفارش: {{ number_format($report['sales']['average_order']) }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><span class="text-muted">تحقق تارگت</span>
                                        <h3>{{ $report['targets']['achievement_percent'] }}%</h3><small>هدف:
                                            {{ number_format($report['targets']['target_amount']) }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><span class="text-muted">پورسانت قابل پرداخت</span>
                                        <h3 class="text-success">
                                            {{ number_format($report['targets']['commission_payable']) }}</h3>
                                        <small>{{ number_format($report['settlements']['calculated_count']) }}
                                            settlement</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <div class="card h-100 border-start border-primary border-3">
                                    <div class="card-body"><span class="text-muted">مالی و اسناد</span>
                                        <h3>{{ number_format($report['financial']['vouchers_count']) }}</h3>
                                        <small>اختلاف تراز:
                                            {{ number_format($report['financial']['balance_gap']) }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100 border-start border-info border-3">
                                    <div class="card-body"><span class="text-muted">ارزش موجودی</span>
                                        <h3>{{ number_format($report['warehouse']['inventory_value']) }}</h3>
                                        <small>رزرو:
                                            {{ number_format($report['warehouse']['reserved_quantity'], 3) }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100 border-start border-warning border-3">
                                    <div class="card-body"><span class="text-muted">تولید</span>
                                        <h3>{{ number_format($report['production']['orders_count']) }}</h3>
                                        <small>هزینه مواد:
                                            {{ number_format($report['production']['material_cost']) }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100 border-start border-success border-3">
                                    <div class="card-body"><span class="text-muted">فروشگاه اینترنتی</span>
                                        <h3>{{ number_format($report['ecommerce']['orders_count']) }}</h3>
                                        <small>خطا/Conflict:
                                            {{ number_format($report['ecommerce']['failed_count']) }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row g-4">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">مقایسه با دوره قبل</h5>
                                        <small
                                            class="text-muted">{{ $report['period']['previous_start']->format('Y-m-d') }}
                                            تا {{ $report['period']['previous_end']->format('Y-m-d') }}</small>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>شاخص</th>
                                                    <th>فعلی</th>
                                                    <th>دوره قبل</th>
                                                    <th>اختلاف</th>
                                                    <th>درصد تغییر</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($report['comparison'] as $key => $row)
                                                    <tr>
                                                        <td>{{ [
                                                            'sales_amount' => 'فروش کل',
                                                            'net_amount' => 'فروش خالص',
                                                            'orders_count' => 'تعداد سفارش',
                                                            'gross_profit' => 'سود ناخالص',
                                                            'inventory_value' => 'ارزش موجودی',
                                                            'production_cost' => 'هزینه تولید',
                                                            'ecommerce_orders' => 'سفارش اینترنتی',
                                                        ][$key] ?? $key }}
                                                        </td>
                                                        <td>{{ number_format($row['current']) }}</td>
                                                        <td>{{ number_format($row['previous']) }}</td>
                                                        <td
                                                            class="{{ $row['delta'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ number_format($row['delta']) }}</td>
                                                        <td>{{ is_null($row['percent']) ? '-' : $row['percent'] . '%' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">ویزیتورهای برتر</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>ویزیتور</th>
                                                    <th>سفارش</th>
                                                    <th>فروش</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($report['top_visitors'] as $visitor)
                                                    <tr>
                                                        <td>{{ $visitor['name'] }}</td>
                                                        <td>{{ number_format($visitor['orders_count']) }}</td>
                                                        <td>{{ number_format($visitor['sales_amount']) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-center text-muted" colspan="3">داده ای وجود
                                                            ندارد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">کالاهای پرفروش</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>کالا</th>
                                                    <th>تعداد</th>
                                                    <th>مبلغ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($report['top_products'] as $product)
                                                    <tr>
                                                        <td>{{ $product['title'] }}</td>
                                                        <td>{{ number_format($product['quantity']) }}</td>
                                                        <td>{{ number_format($product['amount']) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-center text-muted" colspan="3">داده ای وجود
                                                            ندارد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">پخش و تحویل</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-md-4">
                                            <div class="border rounded p-3"><span class="text-muted">کل سفرها</span>
                                                <h4>{{ number_format($report['distribution']['shipments_count']) }}
                                                </h4>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3"><span class="text-muted">سفرهای
                                                    فعال</span>
                                                <h4>{{ number_format($report['distribution']['active_shipments_count']) }}
                                                </h4>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3"><span class="text-muted">سفرهای تکمیل
                                                    شده</span>
                                                <h4>{{ number_format($report['distribution']['completed_shipments_count']) }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">ذخیره قالب گزارش</h5>
                                    </div>
                                    <div class="card-body">
                                        <form class="row g-3" method="post"
                                            action="{{ route('reports.management.templates.store') }}">
                                            @csrf
                                            <input name="start_date" type="hidden"
                                                value="{{ $startDate->format('Y-m-d') }}">
                                            <input name="end_date" type="hidden"
                                                value="{{ $endDate->format('Y-m-d') }}">
                                            <div class="col-md-6">
                                                <label class="form-label">عنوان قالب</label>
                                                <input class="form-control" name="title" required
                                                    value="داشبورد ماهانه مدیریت">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">فرمت پیش فرض</label>
                                                <select class="form-select" name="default_export_format">
                                                    <option value="excel">Excel</option>
                                                    <option value="pdf">PDF/چاپ</option>
                                                    <option value="html">HTML</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">بخش ها</label>
                                                <div class="d-flex flex-wrap gap-3">
                                                    @foreach (['financial' => 'مالی', 'sales' => 'فروش', 'warehouse' => 'انبار', 'production' => 'تولید', 'distribution' => 'پخش', 'ecommerce' => 'فروشگاه'] as $sectionKey => $sectionTitle)
                                                        <label class="form-check">
                                                            <input class="form-check-input" checked name="sections[]"
                                                                type="checkbox" value="{{ $sectionKey }}">
                                                            <span class="form-check-label">{{ $sectionTitle }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button class="btn btn-primary" type="submit">ذخیره قالب</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">زمان بندی گزارش</h5>
                                    </div>
                                    <div class="card-body">
                                        <form class="row g-3" method="post"
                                            action="{{ route('reports.management.schedules.store') }}">
                                            @csrf
                                            <input name="start_date" type="hidden"
                                                value="{{ $startDate->format('Y-m-d') }}">
                                            <input name="end_date" type="hidden"
                                                value="{{ $endDate->format('Y-m-d') }}">
                                            <div class="col-md-6">
                                                <label class="form-label">عنوان زمان بندی</label>
                                                <input class="form-control" name="title" required
                                                    value="ارسال ماهانه داشبورد مدیریت">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">قالب</label>
                                                <select class="form-select" name="management_report_template_id">
                                                    <option value="">پیش فرض</option>
                                                    @foreach ($templates as $template)
                                                        <option value="{{ $template->id }}">{{ $template->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">تناوب</label>
                                                <select class="form-select" name="frequency">
                                                    <option value="daily">روزانه</option>
                                                    <option value="weekly">هفتگی</option>
                                                    <option selected value="monthly">ماهانه</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">خروجی</label>
                                                <select class="form-select" name="delivery_format">
                                                    <option value="excel">Excel</option>
                                                    <option value="pdf">PDF/چاپ</option>
                                                    <option value="html">HTML</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">اجرای بعدی</label>
                                                <input class="form-control" name="next_run_at" type="datetime-local">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">گیرنده ها</label>
                                                <input class="form-control" name="recipients"
                                                    placeholder="email1@example.com, email2@example.com">
                                            </div>
                                            <div class="col-12">
                                                <button class="btn btn-primary" type="submit">ذخیره زمان
                                                    بندی</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">زمان بندی های فعال</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>عنوان</th>
                                                    <th>قالب</th>
                                                    <th>تناوب</th>
                                                    <th>خروجی</th>
                                                    <th>اجرای بعدی</th>
                                                    <th>وضعیت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($schedules as $schedule)
                                                    <tr>
                                                        <td>{{ $schedule->title }}</td>
                                                        <td>{{ optional($schedule->template)->title ?: 'پیش فرض' }}
                                                        </td>
                                                        <td>{{ $schedule->frequency }}</td>
                                                        <td>{{ $schedule->delivery_format }}</td>
                                                        <td>{{ optional($schedule->next_run_at)->format('Y-m-d H:i') ?: '-' }}
                                                        </td>
                                                        <td>{{ $schedule->is_active ? 'فعال' : 'غیرفعال' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-center text-muted" colspan="6">زمان بندی
                                                            فعالی ثبت نشده است.</td>
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
</body>

</html>
