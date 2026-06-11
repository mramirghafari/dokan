<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>انبارگردانی و اصلاحیه - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('assets/') }}/js/config.js"></script>
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
                        <div class="d-flex justify-content-between align-items-center py-3 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">انبار و تامین /</span> انبارگردانی و
                                اصلاحیه</h4>
                            <a class="btn btn-primary" href="{{ route('stocks.inventoryAdjustments.create') }}">ثبت سند
                                جدید</a>
                        </div>

                        <div class="card">
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>شماره</th>
                                            <th>تاریخ</th>
                                            <th>انبار</th>
                                            <th>ثبت کننده</th>
                                            <th>ردیف</th>
                                            <th>اختلاف کل</th>
                                            <th>وضعیت</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($adjustments as $adjustment)
                                            @php($totalDifference = $adjustment->items->sum(fn($item) => (float) $item->difference_quantity))
                                            <tr>
                                                <td>{{ $adjustment->number }}</td>
                                                <td>{{ $adjustment->date_fa ?: optional($adjustment->date_en)->format('Y-m-d') }}
                                                </td>
                                                <td>{{ optional($adjustment->store)->title ?: '-' }}</td>
                                                <td>{{ optional($adjustment->user)->name ?: '-' }}</td>
                                                <td>{{ $adjustment->items->count() }}</td>
                                                <td>{{ number_format($totalDifference, 3) }}</td>
                                                <td>
                                                    @if ($adjustment->status === 'approved')
                                                        <span class="badge bg-label-success">تایید شده</span>
                                                    @elseif($adjustment->status === 'canceled')
                                                        <span class="badge bg-label-danger">باطل شده</span>
                                                    @else
                                                        <span class="badge bg-label-warning">پیش نویس</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($adjustment->status === 'draft')
                                                        <form class="d-inline" method="POST"
                                                            action="{{ route('stocks.inventoryAdjustments.approve', $adjustment->id) }}"
                                                            onsubmit="return confirm('اختلاف این سند در دفتر گردش کالا ثبت شود؟')">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-success"
                                                                type="submit">تایید</button>
                                                        </form>
                                                    @endif
                                                    @if ($adjustment->status !== 'canceled')
                                                        <form class="d-inline" method="POST"
                                                            action="{{ route('stocks.inventoryAdjustments.cancel', $adjustment->id) }}"
                                                            onsubmit="return confirm('آیا از ابطال سند و حذف اثر آن از موجودی مطمئن هستید؟')">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-danger"
                                                                type="submit">ابطال</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center" colspan="8">سند انبارگردانی ثبت نشده است.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $adjustments->links() }}</div>
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
    <script>
        $('.anbarotozi').addClass('open');
        $('.anbarotozi .inventory_adjustments').addClass('active');
    </script>
</body>

</html>
