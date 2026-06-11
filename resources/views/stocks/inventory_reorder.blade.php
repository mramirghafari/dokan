<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>کمبود و پیشنهاد سفارش - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('assets/') }}/js/config.js"></script>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4"><span class="text-muted fw-light">انبار و تامین /</span> کمبود و پیشنهاد
                            سفارش</h4>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form class="row g-3" method="GET" action="{{ route('stocks.inventoryReorder') }}">
                                    <div class="col-md-3">
                                        <label class="form-label">انبار</label>
                                        <select class="select2 form-select" name="store_id">
                                            <option value="">همه انبارها</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}" @selected(request('store_id') == $store->id)>
                                                    {{ $store->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">کالا</label>
                                        @include('partials.forms.erp-product-filter-select')
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">وضعیت</label>
                                        <select class="form-select" name="status">
                                            <option value="needs_order" @selected($report['status'] === 'needs_order')>نیازمند تامین
                                            </option>
                                            <option value="out_of_stock" @selected($report['status'] === 'out_of_stock')>ناموجود/منفی
                                            </option>
                                            <option value="negative" @selected($report['status'] === 'negative')>فقط منفی</option>
                                            <option value="all" @selected($report['status'] === 'all')>همه اقلام</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end gap-2">
                                        <button class="btn btn-primary" type="submit">فیلتر</button>
                                        <a class="btn btn-label-secondary"
                                            href="{{ route('stocks.inventoryReorder') }}">پاک کردن</a>
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
                                    <div class="card-body"><span class="text-muted">منفی</span>
                                        <h5>{{ number_format($report['summary']['negative']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">ناموجود</span>
                                        <h5>{{ number_format($report['summary']['out_of_stock']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">زیر نقطه سفارش</span>
                                        <h5>{{ number_format($report['summary']['needs_order']) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">مقدار پیشنهادی</span>
                                        <h5>{{ number_format($report['summary']['suggested_quantity'], 3) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">ارزش تامین</span>
                                        <h5>{{ number_format($report['summary']['suggested_cost']) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('purchase-requisitions.reorder.store') }}">
                            @csrf
                            <div class="card">
                                <div
                                    class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div>
                                        <h5 class="mb-1">اقلام قابل تبدیل به درخواست خرید</h5>
                                        <small class="text-muted">ردیف های انتخاب شده برای هر انبار در یک درخواست خرید
                                            جداگانه ثبت می شوند.</small>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <select class="form-select" name="priority" style="width: 140px">
                                            <option value="normal">اولویت عادی</option>
                                            <option value="high">اولویت بالا</option>
                                            <option value="urgent">فوری</option>
                                            <option value="low">کم</option>
                                        </select>
                                        <button class="btn btn-success" type="submit">ساخت درخواست خرید</button>
                                    </div>
                                </div>
                                <div class="table-responsive text-nowrap">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>انتخاب</th>
                                                <th>انبار/مکان</th>
                                                <th>کالا</th>
                                                <th>موجودی</th>
                                                <th>رزرو</th>
                                                <th>موجودی آزاد</th>
                                                <th>نقطه سفارش</th>
                                                <th>حداکثر/هدف</th>
                                                <th>کمبود</th>
                                                <th>پیشنهاد تامین</th>
                                                <th>ارزش پیشنهادی</th>
                                                <th>آخرین گردش</th>
                                                <th>وضعیت</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($report['rows'] as $row)
                                                <tr>
                                                    <td>
                                                        @if ($row['suggested_quantity'] > 0)
                                                            <input class="form-check-input" type="checkbox"
                                                                name="reorder_key[]"
                                                                value="{{ $row['reorder_key'] }}" checked>
                                                        @else
                                                            <input class="form-check-input" type="checkbox" disabled>
                                                        @endif
                                                    </td>
                                                    <td>{{ $row['store_title'] }}<br><small>{{ $row['location_title'] }}</small>
                                                    </td>
                                                    <td><a
                                                            href="{{ route('stocks.PrCartex', $row['product_id']) }}">{{ $row['product_title'] }}</a>
                                                    </td>
                                                    <td>{{ number_format($row['quantity'], 3) }}</td>
                                                    <td>{{ number_format($row['reserved_quantity'], 3) }}</td>
                                                    <td>{{ number_format($row['available_quantity'], 3) }}</td>
                                                    <td>{{ $row['minimum_quantity'] > 0 ? number_format($row['minimum_quantity'], 3) : '-' }}
                                                    </td>
                                                    <td>{{ $row['maximum_quantity'] > 0 ? number_format($row['maximum_quantity'], 3) : '-' }}
                                                    </td>
                                                    <td>{{ number_format($row['shortage_quantity'], 3) }}</td>
                                                    <td>
                                                        @if ($row['suggested_quantity'] > 0)
                                                            <input class="form-control form-control-sm text-end"
                                                                min="0.001"
                                                                name="quantity[{{ $row['reorder_key'] }}]"
                                                                step="0.001" type="number"
                                                                value="{{ $row['suggested_quantity'] }}"
                                                                style="width: 120px">
                                                        @else
                                                            {{ number_format($row['suggested_quantity'], 3) }}
                                                        @endif
                                                    </td>
                                                    <td>{{ number_format($row['suggested_cost']) }}</td>
                                                    <td>{{ optional($row['last_movement_at'])->format('Y-m-d H:i') ?: '-' }}
                                                    </td>
                                                    <td>
                                                        @if ($row['status'] === 'negative')
                                                            <span class="badge bg-label-danger">منفی</span>
                                                        @elseif ($row['status'] === 'out_of_stock')
                                                            <span class="badge bg-label-danger">ناموجود</span>
                                                        @elseif ($row['status'] === 'needs_order')
                                                            <span class="badge bg-label-warning">نیازمند تامین</span>
                                                        @else
                                                            <span class="badge bg-label-success">عادی</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-center text-muted py-4" colspan="13">موردی برای
                                                        این
                                                        فیلتر پیدا نشد.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
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
