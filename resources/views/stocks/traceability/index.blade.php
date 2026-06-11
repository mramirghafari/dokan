<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ردیابی batch و سریال - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
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
                        <h4 class="py-3 mb-4"><span class="text-muted fw-light">انبار و تامین /</span> ردیابی batch و
                            سریال</h4>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form class="row g-3" method="get"
                                    action="{{ route('stocks.inventoryTraceability') }}">
                                    <div class="col-md-3">
                                        <label class="form-label">انبار</label>
                                        <select class="form-select" name="store_id">
                                            <option value="">همه</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}" @selected(request('store_id') == $store->id)>
                                                    {{ $store->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">کالا</label>
                                        @include('partials.forms.erp-product-filter-select', ['placeholder' => 'همه', 'class' => 'form-select erp-remote-select'])
                                    </div>
                                    <div class="col-md-2"><label class="form-label">Batch</label><input
                                            class="form-control" name="batch_no" value="{{ request('batch_no') }}">
                                    </div>
                                    <div class="col-md-2"><label class="form-label">Lot</label><input
                                            class="form-control" name="lot_no" value="{{ request('lot_no') }}"></div>
                                    <div class="col-md-2"><label class="form-label">Serial</label><input
                                            class="form-control" name="serial_no" value="{{ request('serial_no') }}">
                                    </div>
                                    <div class="col-md-2"><label class="form-label">انقضا تا</label><input
                                            class="form-control" type="date" name="expiry_to"
                                            value="{{ request('expiry_to') }}"></div>
                                    <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100"
                                            type="submit">فیلتر</button></div>
                                </form>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">ردیف های trace</span>
                                        <h4>{{ number_format($balances->total()) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">موجودی trace</span>
                                        <h4>{{ number_format($balances->sum('quantity'), 3) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">ارزش trace</span>
                                        <h4>{{ number_format($balances->sum('total_cost')) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">گردش نمایش داده شده</span>
                                        <h4>{{ number_format($movements->count()) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">مانده ردیابی</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>کالا</th>
                                            <th>انبار/قفسه</th>
                                            <th>Batch/Lot/Serial</th>
                                            <th>ویژگی ها</th>
                                            <th>انقضا</th>
                                            <th>موجودی</th>
                                            <th>ارزش</th>
                                            <th>آخرین گردش</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($balances as $balance)
                                            <tr>
                                                <td>{{ optional($balance->product)->title ?? '-' }}</td>
                                                <td>{{ optional($balance->store)->title ?? '-' }}<br><small>{{ optional($balance->warehouseLocation)->title ?? 'بدون قفسه' }}</small>
                                                </td>
                                                <td><strong>{{ $balance->batch_no ?: '-' }}</strong><br><small>{{ $balance->lot_no ?: '-' }}
                                                        / {{ $balance->serial_no ?: '-' }}</small></td>
                                                <td>{{ $balance->color ?: '-' }}
                                                    {{ $balance->size ? ' / ' . $balance->size : '' }}
                                                    {{ $balance->quality_grade ? ' / ' . $balance->quality_grade : '' }}
                                                </td>
                                                <td>{{ optional($balance->expiry_date)->format('Y-m-d') ?: '-' }}</td>
                                                <td>{{ number_format((float) $balance->quantity, 3) }}</td>
                                                <td>{{ number_format((float) $balance->total_cost) }}</td>
                                                <td>{{ optional($balance->last_movement_at)->format('Y-m-d H:i') ?: '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center text-muted" colspan="8">مانده ردیابی برای
                                                    فیلتر فعلی وجود ندارد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $balances->links() }}</div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">آخرین گردش های دارای trace</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>تاریخ</th>
                                            <th>نوع</th>
                                            <th>کالا</th>
                                            <th>Batch/Lot/Serial</th>
                                            <th>ورود/خروج</th>
                                            <th>تعداد</th>
                                            <th>مرجع</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($movements as $movement)
                                            <tr>
                                                <td>{{ optional($movement->occurred_at)->format('Y-m-d H:i') ?: '-' }}
                                                </td>
                                                <td>{{ $movement->movement_type }}</td>
                                                <td>{{ optional($movement->product)->title ?? '-' }}</td>
                                                <td>{{ $movement->batch_no ?: '-' }} / {{ $movement->lot_no ?: '-' }}
                                                    / {{ $movement->serial_no ?: '-' }}</td>
                                                <td><span
                                                        class="badge bg-label-{{ $movement->direction === 'in' ? 'success' : 'danger' }}">{{ $movement->direction === 'in' ? 'ورود' : 'خروج' }}</span>
                                                </td>
                                                <td>{{ number_format((float) $movement->quantity, 3) }}</td>
                                                <td>{{ $movement->reference_no ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center text-muted" colspan="7">گردش trace وجود
                                                    ندارد.</td>
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
