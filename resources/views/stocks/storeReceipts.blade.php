<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>لیست رسیدهای انبار - دکان دارمینو</title>
    <meta content="" name="description" />
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"
        rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css"
        rel="stylesheet" />

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
</head>

<body>
    @include('sweetalert::alert')
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <!-- Layout container -->
            <div class="layout-page">
                @include('sections.header')
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4">
                            <span class="text-muted fw-light">تولید و انبار /</span>
                            لیست رسیدهای انبار
                        </h4>
                        <div class="card mb-4">
                            <div class="card-body">
                                <form class="row g-3" method="GET"
                                    action="{{ route('stocks.storeReceipts', $store->id) }}">
                                    <div class="col-md-3">
                                        <label class="form-label">جستجو</label>
                                        <input type="text" name="q" value="{{ request('q') }}"
                                            class="form-control" placeholder="شماره، صادرکننده، قبض یا پلاک">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">نوع رسید</label>
                                        <select name="type" class="form-select">
                                            <option value="">همه</option>
                                            <option value="1" @selected(request('type') === '1')>خرید داخلی</option>
                                            <option value="2" @selected(request('type') === '2')>خرید وارداتی</option>
                                            <option value="3" @selected(request('type') === '3')>تولید</option>
                                            <option value="5" @selected(request('type') === '5')>موجودی اول دوره</option>
                                            <option value="6" @selected(request('type') === '6')>انتقال بین انبار
                                            </option>
                                            <option value="7" @selected(request('type') === '7')>فروش</option>
                                            <option value="8" @selected(request('type') === '8')>مصرف</option>
                                            <option value="10" @selected(request('type') === '10')>برگشت رسید</option>
                                            <option value="11" @selected(request('type') === '11')>برگشت حواله</option>
                                            <option value="12" @selected(request('type') === '12')>برگشت ترکیبی</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">وضعیت سند</label>
                                        <select name="document_status" class="form-select">
                                            <option value="">همه</option>
                                            <option value="draft" @selected(request('document_status') === 'draft')>پیش نویس</option>
                                            <option value="approved" @selected(request('document_status') === 'approved')>تایید شده</option>
                                            <option value="canceled" @selected(request('document_status') === 'canceled')>باطل شده</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">از تاریخ</label>
                                        <input type="date" name="from_date" value="{{ request('from_date') }}"
                                            class="form-control">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">تا تاریخ</label>
                                        <input type="date" name="to_date" value="{{ request('to_date') }}"
                                            class="form-control">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end gap-2">
                                        <button class="btn btn-primary" type="submit">فیلتر</button>
                                    </div>
                                    <div class="col-12">
                                        <a class="btn btn-label-secondary btn-sm"
                                            href="{{ route('stocks.storeReceipts', $store->id) }}">پاک کردن فیلتر</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- Sticky Actions -->
                        <div class="row mt-5">
                            <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">

                                <div class="card">
                                    <div class="card-datatable table-responsive py-0">
                                        <table class="datatables-direct-basic table">
                                            <thead>
                                                <tr>
                                                    <th width="25">#</th>
                                                    <th class="text-center">شماره</th>
                                                    <th class="text-center">نوع رسید</th>
                                                    <th class="text-center">تاریخ</th>
                                                    <th class="text-center">انبار</th>
                                                    <th>صادر کننده</th>
                                                    <th class="text-center">تحویل دهنده</th>
                                                    <th class="text-center">باسکول</th>
                                                    <th class="text-center">وضعیت سند</th>
                                                    <th class="text-center">عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($Receipts as $receipt)
                                                    @php($documentStatus = $receipt->document_status ?: 'approved')
                                                    <tr>
                                                        <td>{{ $Receipts->firstItem() + $loop->index }}</td>
                                                        <td class="text-center"><a
                                                                href="{{ route('stocks.storeReceiptShow', [$receipt->store_id, $receipt->id]) }}"><small>{{ $receipt->number ? $receipt->number : 100 + $receipt->id }}</small></a>
                                                        </td>
                                                        <td class="text-center">
                                                            <a
                                                                href="{{ route('stocks.storeReceiptShow', [$receipt->store_id, $receipt->id]) }}">
                                                                @if ($receipt->type == 1)
                                                                    خرید (داخلی)
                                                                @elseif($receipt->type == 2)
                                                                    خرید (وارداتی)
                                                                @elseif($receipt->type == 3)
                                                                    تولید
                                                                @elseif($receipt->type == 4)
                                                                    سایر
                                                                @elseif($receipt->type == 5)
                                                                    موجودی اول دوره
                                                                @elseif($receipt->type == 6)
                                                                    انتقال بین انبار
                                                                @elseif($receipt->type == 7)
                                                                    فروش
                                                                @elseif($receipt->type == 8)
                                                                    مصرف
                                                                @elseif($receipt->type == 9)
                                                                    سایر
                                                                @elseif($receipt->type == 10)
                                                                    برگشت رسید
                                                                @elseif($receipt->type == 11)
                                                                    برگشت حواله
                                                                @elseif($receipt->type == 12)
                                                                    برگشت ترکیبی
                                                                @endif
                                                                @if ($receipt->return_source_receipt_id)
                                                                    <br><small class="text-muted">از سند
                                                                        #{{ $receipt->return_source_receipt_id }}</small>
                                                                @endif
                                                            </a>
                                                        </td>
                                                        <td class="text-center">
                                                            <a
                                                                href="{{ route('stocks.storeReceiptShow', [$receipt->store_id, $receipt->id]) }}">
                                                                <small>{{ $receipt->date_fa ? $receipt->date_fa : verta($receipt->created_at)->format('Y/m/d') }}</small>
                                                            </a>
                                                        </td>
                                                        <td class="text-center">
                                                            <small>{{ $receipt->store ? $receipt->store->title : '' }}</small>
                                                        </td>
                                                        <td class="text-center">
                                                            <small>{{ $receipt->sender ? $receipt->sender : '' }}</small>
                                                        </td>
                                                        <td><a
                                                                href="{{ route('stocks.storeReceiptShow', [$receipt->store_id, $receipt->id]) }}">{{ optional($receipt->user)->name ?: '-' }}</a>
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($receipt->net_weight)
                                                                <small>{{ number_format((float) $receipt->net_weight, 3) }}</small>
                                                                <br><small
                                                                    class="text-muted">{{ $receipt->scale_ticket_number ?: $receipt->vehicle_plate }}</small>
                                                            @else
                                                                <small class="text-muted">-</small>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($documentStatus === 'approved')
                                                                <span class="badge bg-label-success">تایید شده</span>
                                                            @elseif($documentStatus === 'canceled')
                                                                <span class="badge bg-label-danger">باطل شده</span>
                                                            @else
                                                                <span class="badge bg-label-warning">پیش نویس</span>
                                                            @endif
                                                        </td>
                                                        <th class="text-center">
                                                            @if ($documentStatus !== 'approved' && $documentStatus !== 'canceled')
                                                                <form
                                                                    action="{{ route('stocks.receipts.approve', $receipt->id) }}"
                                                                    method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit"
                                                                        class="btn btn-sm btn-outline-success">تایید</button>
                                                                </form>
                                                            @endif
                                                            @if ($documentStatus !== 'canceled')
                                                                <form
                                                                    action="{{ route('stocks.receipts.cancel', $receipt->id) }}"
                                                                    method="POST" class="d-inline"
                                                                    onsubmit="return confirm('آیا از ابطال این سند انبار مطمئن هستید؟ اثر موجودی و سند مالی موقت حذف می شود.')">
                                                                    @csrf
                                                                    <input type="hidden" name="cancellation_reason"
                                                                        value="ابطال از لیست رسیدهای انبار">
                                                                    <button type="submit"
                                                                        class="btn btn-sm btn-outline-danger">ابطال</button>
                                                                </form>
                                                            @endif
                                                            <a href="{{ route('stocks.deleteReceipt', $receipt->id) }}"
                                                                class="ms-1"
                                                                onclick="return confirm('آیا از حذف این رسید مطمئن هستید؟')">
                                                                <svg width="20" height="24"
                                                                    viewBox="0 0 12 14" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M7.82667 5.00023L7.596 11.0001M4.404 11.0001L4.17333 5.00023M10.8187 2.86028C11.0467 2.89495 11.2733 2.93162 11.5 2.97095M10.8187 2.86028L10.1067 12.1154C10.0776 12.4922 9.90738 12.8441 9.63001 13.1008C9.35265 13.3576 8.9886 13.5001 8.61067 13.5H3.38933C3.0114 13.5001 2.64735 13.3576 2.36999 13.1008C2.09262 12.8441 1.92239 12.4922 1.89333 12.1154L1.18133 2.86028M10.8187 2.86028C10.0492 2.74397 9.27584 2.65569 8.5 2.59562M1.18133 2.86028C0.953333 2.89428 0.726667 2.93095 0.5 2.97028M1.18133 2.86028C1.95076 2.74397 2.72416 2.65569 3.5 2.59562M8.5 2.59562V1.98497C8.5 1.19833 7.89333 0.542346 7.10667 0.51768C6.36908 0.494107 5.63092 0.494107 4.89333 0.51768C4.10667 0.542346 3.5 1.199 3.5 1.98497V2.59562M8.5 2.59562C6.83581 2.46701 5.16419 2.46701 3.5 2.59562"
                                                                        stroke="#C1292E" stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                </svg>

                                                            </a>
                                                        </th>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">
                                        {{ $Receipts->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Sticky Actions -->
                    </div>
                    <!-- / Content -->
                    @include('sections.footer')
                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
        <!-- Drag Target Area To SlideIn Menu On Small Screens -->
        <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->
    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <!-- endbuild -->
    <script src="{{ asset('assets/') }}/vendor/libs/jquery-sticky/jquery-sticky.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave-phone.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
    <script>
        $('.anbarotozi').addClass('open')
        $('.anbarotozi .receipts').addClass('active')

        $(document).ready(function() {
            $('#pr_id').on('change', function() {
                var dataUnit = $(this).find('option:selected').attr('data-unit');
                $('.unitplace').html(dataUnit);
                var dataSubUnit = $(this).find('option:selected').attr('data-subunit');
                $('.subunitplace').html(dataSubUnit);
            });
        });
    </script>
</body>

</html>
