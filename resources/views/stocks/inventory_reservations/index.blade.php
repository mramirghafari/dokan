<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>رزرو موجودی - دکان دارمینو</title>
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
                        <h4 class="py-3 mb-4"><span class="text-muted fw-light">انبار و تامین /</span> رزرو موجودی</h4>

                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">رزرو فعال</span>
                                        <h4>{{ number_format((float) $summary['reserved'], 3) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">مصرف شده در خروج</span>
                                        <h4>{{ number_format((float) $summary['consumed'], 3) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body"><span class="text-muted">آزاد شده</span>
                                        <h4>{{ number_format((float) $summary['released'], 3) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form class="row g-3" method="get"
                                    action="{{ route('stocks.inventoryReservations') }}">
                                    <div class="col-md-3">
                                        <label class="form-label">انبار</label>
                                        <select class="select2 form-select" name="store_id">
                                            <option value="">همه</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}" @selected(request('store_id') == $store->id)>
                                                    {{ $store->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">کالا</label>
                                        @include('partials.forms.erp-product-filter-select', ['placeholder' => 'همه'])
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">وضعیت</label>
                                        <select class="form-select" name="status">
                                            <option value="">همه</option>
                                            <option value="reserved" @selected(request('status') === 'reserved')>فعال</option>
                                            <option value="consumed" @selected(request('status') === 'consumed')>مصرف شده</option>
                                            <option value="released" @selected(request('status') === 'released')>آزاد شده</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2"><label class="form-label">Batch</label><input
                                            class="form-control" name="batch_no" value="{{ request('batch_no') }}">
                                    </div>
                                    <div class="col-md-2"><label class="form-label">Serial</label><input
                                            class="form-control" name="serial_no" value="{{ request('serial_no') }}">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100"
                                            type="submit">فیلتر</button></div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>فاکتور</th>
                                            <th>کالا</th>
                                            <th>انبار/مکان</th>
                                            <th>Trace</th>
                                            <th>مقدار</th>
                                            <th>موجودی آزاد هنگام رزرو</th>
                                            <th>وضعیت</th>
                                            <th>زمان</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reservations as $reservation)
                                            <tr>
                                                <td>{{ optional($reservation->pishfactor)->invoiceID ?: '#' . $reservation->pishfactor_id }}
                                                </td>
                                                <td>{{ optional($reservation->product)->title ?? '-' }}</td>
                                                <td>{{ optional($reservation->store)->title ?? '-' }}<br><small>{{ optional($reservation->warehouseLocation)->title ?? 'بدون مکان' }}</small>
                                                </td>
                                                <td>{{ $reservation->batch_no ?: '-' }} /
                                                    {{ $reservation->lot_no ?: '-' }} /
                                                    {{ $reservation->serial_no ?: '-' }}</td>
                                                <td>{{ number_format((float) $reservation->quantity, 3) }}</td>
                                                <td>{{ number_format((float) $reservation->available_quantity_snapshot, 3) }}
                                                </td>
                                                <td>
                                                    @if ($reservation->status === 'reserved')
                                                        <span class="badge bg-label-primary">فعال</span>
                                                    @elseif($reservation->status === 'consumed')
                                                        <span class="badge bg-label-success">مصرف شده</span>
                                                    @else
                                                        <span class="badge bg-label-secondary">آزاد شده</span>
                                                    @endif
                                                </td>
                                                <td>{{ optional($reservation->reserved_at)->format('Y-m-d H:i') ?: '-' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center text-muted" colspan="8">رزروی برای فیلتر فعلی
                                                    وجود ندارد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $reservations->links() }}</div>
                        </div>
                    </div>
                    @include('sections.footer')
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
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
