<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>دفتر گردش کالا - دکان دارمینو</title>
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
                        <h4 class="py-3 mb-4"><span class="text-muted fw-light">انبار و تامین /</span> دفتر گردش کالا
                        </h4>
                        <div class="card mb-4">
                            <div class="card-body">
                                <form class="row g-3" method="GET" action="{{ route('stocks.inventoryMovements') }}">
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
                                            href="{{ route('stocks.inventoryMovements') }}">پاک کردن</a>
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
                                            <th>تاریخ</th>
                                            <th>انبار</th>
                                            <th>مکان</th>
                                            <th>کالا</th>
                                            <th>نوع سند</th>
                                            <th>شماره</th>
                                            <th>ورود</th>
                                            <th>خروج</th>
                                            <th>واحد فرعی</th>
                                            <th>وزن</th>
                                            <th>بهای واحد</th>
                                            <th>مبلغ گردش</th>
                                            <th>توضیحات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($movements as $movement)
                                            <tr>
                                                <td>{{ $movements->firstItem() + $loop->index }}</td>
                                                <td>{{ optional($movement->occurred_at)->format('Y-m-d H:i') ?: '-' }}
                                                </td>
                                                <td>{{ optional($movement->store)->title ?: '-' }}</td>
                                                <td>{{ optional($movement->warehouseLocation)->path ?: 'بدون مکان' }}
                                                </td>
                                                <td>
                                                    @if ($movement->product)
                                                        <a
                                                            href="{{ route('stocks.PrCartex', $movement->product_id) }}">{{ $movement->product->title }}
                                                            {{ $movement->product->display_name }}</a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $movement->movement_type }}</td>
                                                <td>{{ $movement->reference_no ?: ($movement->receipt_id ? 'رسید #' . $movement->receipt_id : '-') }}
                                                </td>
                                                <td>{{ $movement->direction === 'in' ? number_format((float) $movement->quantity, 3) : '-' }}
                                                </td>
                                                <td>{{ $movement->direction === 'out' ? number_format((float) $movement->quantity, 3) : '-' }}
                                                </td>
                                                <td>{{ (float) $movement->quantity_sub_unit ? number_format((float) $movement->quantity_sub_unit, 3) : '-' }}
                                                </td>
                                                <td>{{ (float) $movement->weight ? number_format((float) $movement->weight, 3) : '-' }}
                                                </td>
                                                <td>{{ number_format((float) $movement->unit_cost) }}</td>
                                                <td>{{ number_format((float) $movement->total_cost) }}</td>
                                                <td><small>{{ $movement->description }}</small></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center" colspan="14">گردش انباری ثبت نشده است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $movements->links() }}</div>
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
