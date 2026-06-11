<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>موجودی لحظه ای انبار - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4"><span class="text-muted fw-light">انبار و تامین /</span> موجودی لحظه ای
                        </h4>
                        <div class="card mb-4">
                            <div class="card-body">
                                <form class="row g-3" method="GET" action="{{ route('stocks.inventoryBalances') }}">
                                    <div class="col-md-4">
                                        <label class="form-label">انبار</label>
                                        <select class="select2 form-select" name="store_id">
                                            <option value="">همه انبارها</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}"
                                                    @if ((string) request('store_id') === (string) $store->id) selected @endif>
                                                    {{ $store->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label">کالا</label>
                                        @include('partials.forms.erp-product-filter-select')
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end gap-2">
                                        <button class="btn btn-primary" type="submit">فیلتر</button>
                                        <a class="btn btn-label-secondary"
                                            href="{{ route('stocks.inventoryBalances') }}">پاک کردن</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ردیف</th>
                                            <th>انبار</th>
                                            <th>مکان</th>
                                            <th>کالا</th>
                                            <th>موجودی</th>
                                            <th>واحد فرعی</th>
                                            <th>قابل فروش</th>
                                            <th>بهای واحد</th>
                                            <th>ارزش موجودی</th>
                                            <th>رزرو</th>
                                            <th>حداقل</th>
                                            <th>آخرین گردش</th>
                                            <th>وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($balances as $balance)
                                            @php
                                                $quantity = (float) $balance->quantity;
                                                $reserved = (float) $balance->reserved_quantity;
                                                $available = $quantity - $reserved;
                                                $orderLimit = (float) optional($balance->product)->orderLimit;
                                                $isLow =
                                                    $available <= 0 || ($orderLimit > 0 && $available <= $orderLimit);
                                            @endphp
                                            <tr>
                                                <td>{{ $balances->firstItem() + $loop->index }}</td>
                                                <td>{{ optional($balance->store)->title ?: '-' }}</td>
                                                <td>{{ optional($balance->warehouseLocation)->path ?: 'بدون مکان' }}
                                                </td>
                                                <td>
                                                    @if ($balance->product)
                                                        <a href="{{ route('stocks.PrCartex', $balance->product_id) }}">{{ $balance->product->title }}
                                                            {{ $balance->product->display_name }}</a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ number_format($quantity, 3) }}</td>
                                                <td>{{ (float) $balance->quantity_sub_unit ? number_format((float) $balance->quantity_sub_unit, 3) : '-' }}
                                                </td>
                                                <td>{{ number_format($available, 3) }}</td>
                                                <td>{{ number_format((float) $balance->unit_cost) }}</td>
                                                <td>{{ number_format((float) $balance->total_cost) }}</td>
                                                <td>{{ number_format($reserved, 3) }}</td>
                                                <td>{{ $orderLimit > 0 ? number_format($orderLimit, 3) : '-' }}</td>
                                                <td>{{ optional($balance->last_movement_at)->format('Y-m-d H:i') ?: '-' }}
                                                </td>
                                                <td>
                                                    @if ($quantity < 0)
                                                        <span class="badge bg-label-danger">منفی</span>
                                                    @elseif ($isLow)
                                                        <span class="badge bg-label-warning">کمبود</span>
                                                    @else
                                                        <span class="badge bg-label-success">عادی</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center" colspan="13">موجودی ثبت شده ای پیدا نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $balances->links() }}</div>
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
