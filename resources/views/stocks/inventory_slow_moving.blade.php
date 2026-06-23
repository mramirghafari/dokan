<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>کالاهای کم فروش و راکد - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4"><span class="text-muted fw-light">انبار و تامین /</span> کالاهای کم فروش و
                            راکد</h4>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form class="row g-3" method="GET"
                                    action="{{ route('stocks.inventorySlowMoving') }}">
                                    <div class="col-md-2">
                                        <label class="form-label">از تاریخ</label>
                                        <input class="form-control" type="date" name="from_date"
                                            value="{{ request('from_date', $report['from_date']) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">تا تاریخ</label>
                                        <input class="form-control" type="date" name="to_date"
                                            value="{{ request('to_date', $report['to_date']) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">انبار</label>
                                        <select class="select2 form-select" name="store_id">
                                            <option value="">همه انبارها</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}" @selected(request('store_id') == $store->id)>
                                                    {{ $store->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">کالا</label>
                                        @include('partials.forms.erp-product-filter-select')
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">وضعیت</label>
                                        <select class="form-select" name="status">
                                            <option value="at_risk" @selected($report['status'] === 'at_risk')>کم فروش و راکد</option>
                                            <option value="stagnant" @selected($report['status'] === 'stagnant')>فقط راکد</option>
                                            <option value="slow" @selected($report['status'] === 'slow')>فقط کم فروش</option>
                                            <option value="moving" @selected($report['status'] === 'moving')>دارای گردش مناسب
                                            </option>
                                            <option value="all" @selected($report['status'] === 'all')>همه اقلام دارای موجودی
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">آستانه</label>
                                        <input class="form-control" min="0" name="slow_threshold" step="0.001"
                                            type="number"
                                            value="{{ request('slow_threshold', $report['slow_threshold']) }}">
                                    </div>
                                    <div class="col-md-12 d-flex justify-content-end gap-2">
                                        <button class="btn btn-primary" type="submit">فیلتر</button>
                                        <a class="btn btn-label-secondary"
                                            href="{{ route('stocks.inventorySlowMoving') }}">پاک کردن</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">اقلام</span>
                                        <h5>{{ number_format($report['summary']['rows']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">راکد</span>
                                        <h5>{{ number_format($report['summary']['stagnant']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">کم فروش</span>
                                        <h5>{{ number_format($report['summary']['slow']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">خروج موثر</span>
                                        <h5>{{ number_format($report['summary']['effective_out_quantity'], 3) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">موجودی</span>
                                        <h5>{{ number_format($report['summary']['current_quantity'], 3) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">ارزش خوابیده</span>
                                        <h5>{{ number_format($report['summary']['stock_value']) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                                    <h5 class="mb-1">اقلام دارای ریسک خواب سرمایه</h5>
                                    <small class="text-muted">آستانه کم فروش یعنی خروج موثر کمتر یا مساوی این مقدار در
                                        بازه انتخابی.</small>
                                </div>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>انبار/مکان</th>
                                            <th>کالا</th>
                                            <th>موجودی</th>
                                            <th>رزرو</th>
                                            <th>آزاد</th>
                                            <th>خروج موثر</th>
                                            <th>فروش</th>
                                            <th>نسبت گردش</th>
                                            <th>ارزش موجودی</th>
                                            <th>آخرین گردش</th>
                                            <th>وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($report['rows'] as $row)
                                            <tr>
                                                <td>{{ $row['store_title'] }}<br><small>{{ $row['location_title'] }}</small>
                                                </td>
                                                <td><a
                                                        href="{{ route('stocks.PrCartex', $row['product_id']) }}">{{ $row['product_title'] }}</a>
                                                </td>
                                                <td>{{ number_format($row['current_quantity'], 3) }}</td>
                                                <td>{{ number_format($row['reserved_quantity'], 3) }}</td>
                                                <td>{{ number_format($row['available_quantity'], 3) }}</td>
                                                <td>{{ number_format($row['effective_out_quantity'], 3) }}</td>
                                                <td>{{ number_format($row['sales_quantity'], 3) }}</td>
                                                <td>{{ number_format($row['turnover_ratio'] * 100, 2) }}%</td>
                                                <td>{{ number_format($row['stock_value']) }}</td>
                                                <td>{{ verta_datetime($row['last_movement_at']) }}</td>
                                                <td>
                                                    @if ($row['status'] === 'stagnant')
                                                        <span class="badge bg-label-danger">راکد</span>
                                                    @elseif ($row['status'] === 'slow')
                                                        <span class="badge bg-label-warning">کم فروش</span>
                                                    @elseif ($row['status'] === 'moving')
                                                        <span class="badge bg-label-success">گردش مناسب</span>
                                                    @else
                                                        <span class="badge bg-label-secondary">بدون موجودی</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center text-muted py-4" colspan="11">موردی برای
                                                    فیلتر فعلی پیدا نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    @include('partials.erp-remote-select-assets')
    <script>
        $('.select2:not(.erp-remote-select)').select2({
            dir: 'rtl',
            width: '100%'
        });
    </script>
</body>

</html>
