<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>استعلام بها - دکان دارمینو</title>
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
            @include('sections/sidebar')
            <div class="layout-page">
                @include('sections/header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">انبار و تامین /</span> استعلام بها</h4>
                            <div class="d-flex gap-2">
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.index') }}">سفارش
                                    خرید</a>
                                <a class="btn btn-primary" href="{{ route('purchase-requisitions.create') }}">
                                    <i class="ti ti-plus me-1"></i> درخواست خرید
                                </a>
                            </div>
                        </div>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>شماره</th>
                                            <th>تاریخ</th>
                                            <th>انبار</th>
                                            <th>اولویت</th>
                                            <th class="text-end">اقلام</th>
                                            <th class="text-end">پیشنهادها</th>
                                            <th>تامین کننده منتخب</th>
                                            <th>سفارش خرید</th>
                                            <th>وضعیت</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($purchaseRequisitions as $requisition)
                                            <tr>
                                                <td>{{ $loop->iteration + ($purchaseRequisitions->currentPage() - 1) * $purchaseRequisitions->perPage() }}
                                                </td>
                                                <td>{{ $requisition->request_number }}</td>
                                                <td>{{ $requisition->request_date_fa ?: optional($requisition->request_date_en)->format('Y-m-d') }}
                                                </td>
                                                <td>{{ optional($requisition->store)->title }}</td>
                                                <td>{{ ['low' => 'کم', 'normal' => 'عادی', 'high' => 'بالا', 'urgent' => 'فوری'][$requisition->priority] ?? $requisition->priority }}
                                                </td>
                                                <td class="text-end">{{ number_format($requisition->items->count()) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format($requisition->quotations->count()) }}</td>
                                                <td>{{ optional($requisition->selectedSupplier)->title ?: optional($requisition->selectedSupplier)->name }}
                                                </td>
                                                <td>
                                                    @if ($requisition->purchaseOrder)
                                                        {{ $requisition->purchaseOrder->order_number }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($requisition->status === 'converted')
                                                        <span class="badge bg-label-success">تبدیل شده</span>
                                                    @elseif ($requisition->status === 'quoted')
                                                        <span class="badge bg-label-info">دارای پیشنهاد</span>
                                                    @elseif ($requisition->status === 'open')
                                                        <span class="badge bg-label-warning">باز</span>
                                                    @else
                                                        <span
                                                            class="badge bg-label-secondary">{{ $requisition->status }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a class="btn btn-sm btn-outline-primary"
                                                        href="{{ route('purchase-requisitions.show', $requisition) }}">جزئیات</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center text-muted py-4">درخواست خریدی ثبت
                                                    نشده است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $purchaseRequisitions->links() }}</div>
                        </div>
                    </div>
                    @include('sections/footer')
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
