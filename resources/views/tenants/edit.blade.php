<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ویرایش پنل - دکان دارمینو</title>
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
                            <span class="text-muted fw-light">اطلاعات پایه /</span>
                            جزئیات و ویرایش پنل
                        </h4>
                        <!-- Sticky Actions -->
                        <div class="row mb-4">
                            <div class="col-12 col-md-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <small class="text-muted">واحد / شعبه</small>
                                        <h4 class="mb-0">{{ number_format($stats['organizations_count']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <small class="text-muted">کارمندان</small>
                                        <h4 class="mb-0">{{ number_format($stats['users_count']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <small class="text-muted">فاکتورها</small>
                                        <h4 class="mb-0">{{ number_format($stats['pishfactors_count']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <small class="text-muted">مجموع فروش</small>
                                        <h4 class="mb-0">{{ number_format($stats['total_sales']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-5">
                            <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">تنظیمات اصلی پنل</h5>
                                            <small class="text-muted">مدیر کل این پنل از داخل پنل خودش نقش ها و کاربران
                                                را مدیریت می کند.</small>
                                        </div>
                                        <a class="btn btn-label-secondary" href="{{ route('tenants.index') }}">بازگشت
                                            به لیست پنل ها</a>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('tenants.update', $Tenant->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="mb-3">
                                                <label class="form-label" for="name">نام پنل</label>
                                                <input class="form-control" name="name" id="name"
                                                    placeholder="نام پنل" value="{{ $Tenant->name }}" type="text" />
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-12 col-md-6 mb-3">
                                                    <label class="form-label" for="legal_name">نام شرکت</label>
                                                    <input class="form-control" name="legal_name" id="legal_name"
                                                        placeholder="نام شرکت" value="{{ $Tenant->legal_name }}"
                                                        type="text" />
                                                </div>
                                                <div class="col-12 col-md-6 mb-3">
                                                    <label class="form-label" for="phone">شماره تماس</label>
                                                    <input class="form-control" name="phone" id="phone"
                                                        inputmode="numeric" pattern="[0-9]*"
                                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                        placeholder="شماره تماس پنل" value="{{ $Tenant->phone }}"
                                                        type="tel" />
                                                </div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-12 col-md-6 mb-3">
                                                    <label class="form-label" for="subscription_type">نوع
                                                        اشتراک</label>
                                                    <select class="form-select" id="subscription_type"
                                                        name="subscription_type">
                                                        <option value="">انتخاب کنید</option>
                                                        @foreach ($subscriptionOptions as $value => $option)
                                                            <option value="{{ $value }}"
                                                                @if ($Tenant->subscription_type === $value) selected @endif>
                                                                {{ $option['label'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small>برای پنل آزمایشی، «دموی 3 روزه» را انتخاب کنید. اگر نوع
                                                        اشتراک را تغییر بدهید، تاریخ پایان از امروز دوباره محاسبه می
                                                        شود.</small>
                                                </div>
                                                <div class="col-12 col-md-6 mb-3">
                                                    <label class="form-label">پایان اشتراک فعلی</label>
                                                    <input class="form-control" disabled
                                                        value="{{ $Tenant->subscription_ends_at ? $Tenant->subscription_ends_at->format('Y/m/d') : ($Tenant->subscription_type === 'permanent' ? 'دائمی' : 'ثبت نشده') }}"
                                                        type="text" />
                                                </div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-12 col-md-6 mb-3">
                                                    <label class="form-label" for="wallet_balance">کیف پول پیامک
                                                        پنل</label>
                                                    <input class="form-control" id="wallet_balance"
                                                        name="wallet_balance" min="0" step="1000"
                                                        value="{{ $Tenant->wallet_balance ?? 0 }}" type="number" />
                                                    <small>تا زمان اتصال درگاه، مدیر سیستم می تواند موجودی را از همینجا
                                                        شارژ کند.</small>
                                                </div>
                                                <div class="col-12 col-md-6 mb-3">
                                                    <label class="form-label" for="sms_unit_price_toman">هزینه هر
                                                        پیامک
                                                        تومان</label>
                                                    <input class="form-control" id="sms_unit_price_toman"
                                                        name="sms_unit_price_toman" min="0" step="1"
                                                        value="{{ $Tenant->sms_unit_price_toman ?? 0 }}"
                                                        type="number" />
                                                    <small>در ارسال پیامک، این مبلغ از کیف پول مدیر پنل کم می
                                                        شود.</small>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="unit_order">واحد اصلی سفارش گیری
                                                    محصولات</label>
                                                <select class="select2 form-select" data-allow-clear="true"
                                                    id="unit_order" name="unit_order">
                                                    <option value="">انتخاب کنید</option>
                                                    <option value="مثقال"
                                                        @if ($Tenant->unit_order == 'مثقال') selected @endif>مثقال</option>
                                                    <option value="گرم"
                                                        @if ($Tenant->unit_order == 'گرم') selected @endif>گرم</option>
                                                    <option value="کیلوگرم"
                                                        @if ($Tenant->unit_order == 'کیلوگرم') selected @endif>کیلوگرم
                                                    </option>
                                                    <option value="تن"
                                                        @if ($Tenant->unit_order == 'تن') selected @endif>تن</option>
                                                    <option value="سی سی"
                                                        @if ($Tenant->unit_order == 'سی سی') selected @endif>سی سی
                                                    </option>
                                                    <option value="میلی لیتر"
                                                        @if ($Tenant->unit_order == 'میلی لیتر') selected @endif>میلی لیتر
                                                    </option>
                                                    <option value="لیتر"
                                                        @if ($Tenant->unit_order == 'ملیترثقال') selected @endif>لیتر</option>
                                                    <option value="عدد"
                                                        @if ($Tenant->unit_order == 'عدد') selected @endif>عدد</option>
                                                </select>
                                                <small>این واحد در تیتر جدول ها نمایش داده میشود.</small>
                                            </div>
                                            <div class="mb-3">
                                                <label for="sub_unit">انتخاب واحد فرعی سفارش گیری:</label>
                                                <select class="form-select" id="sub_order" name="sub_order">
                                                    <option>-- انتخاب کنید --</option>
                                                    <option value="نایلون"
                                                        @if ($Tenant->sub_order == 'نایلون') selected @endif>نایلون
                                                    </option>
                                                    <option value="کارتن"
                                                        @if ($Tenant->sub_order == 'کارتن') selected @endif>کارتن
                                                    </option>
                                                    <option value="فله"
                                                        @if ($Tenant->sub_order == 'فله') selected @endif>فله</option>
                                                </select>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col mb-3">
                                                    <label for="currency_type">واحد پولی پنل:</label>
                                                    <select class="form-select" id="currency_type"
                                                        name="currency_type">
                                                        <option value="0">-- انتخاب کنید --</option>
                                                        <option value="1"
                                                            @if ($Tenant->currency_type == 1) selected @endif>تومان
                                                        </option>
                                                        <option value="2"
                                                            @if ($Tenant->currency_type == 2) selected @endif>ریال
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col mb-3">
                                                    <label for="status">وضعیت پنل:</label>
                                                    <select class="form-select" id="status" name="status">
                                                        <option value="1"
                                                            @if ($Tenant->status == 1) selected @endif>فعال
                                                        </option>
                                                        <option value="0"
                                                            @if ($Tenant->status == 0) selected @endif>غیرفعال
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="tozihat">توضیح:</label>
                                                <input type="text" class="form-control" name="tozihat"
                                                    id="tozihat" value="{{ $Tenant->tozihat }}">
                                            </div>
                                            <button class="btn btn-primary" type="submit">ذخیره تنظیمات پنل</button>
                                        </form>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">مدیران کل پنل</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>نام</th>
                                                    <th>نام کاربری</th>
                                                    <th>ایمیل</th>
                                                    <th>شماره همراه</th>
                                                    <th>کد ملی</th>
                                                    <th>وضعیت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($admins as $admin)
                                                    <tr>
                                                        <td>{{ $admin->name }}</td>
                                                        <td>{{ $admin->username }}</td>
                                                        <td>{{ $admin->email }}</td>
                                                        <td>{{ $admin->mobile }}</td>
                                                        <td>{{ $admin->national_code ?: $admin->personalID }}</td>
                                                        <td>
                                                            @if ($admin->isActive == 1)
                                                                <span class="badge bg-label-success">فعال</span>
                                                            @else
                                                                <span class="badge bg-label-danger">غیرفعال</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-center text-muted" colspan="6">مدیر کلی
                                                            برای این پنل ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
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
        // datatable (jquery)
        $('.basicdata').addClass('open')
        $('.basicdata .panels').addClass('active open')
        $(function() {
            var
                dt_without_ajax_table = $('.datatables-direct-basic');

            // DataTable Direct
            // --------------------------------------------------------------------
            if (dt_without_ajax_table.length) {
                dt_without_ajax = dt_without_ajax_table.DataTable({
                    searching: false,
                    lengthChange: false,
                    ordering: false,
                    pageLength: 5,
                });

                $('.datatables-direct-basic tbody').on('click', '.dropdown-item.delete-record', function() {
                    dt_without_ajax
                        .row($(this).parents('tr'))
                        .remove()
                        .draw();
                });
            }


        });
    </script>
</body>

</html>
