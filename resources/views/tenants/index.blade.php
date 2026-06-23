<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>پنل های سامانه - دکان دارمینو</title>
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
    <style>
        .tenants-table .tenant-row-number {
            width: 56px;
            min-width: 56px;
            text-align: center;
        }

        .tenants-table .tenant-name-column {
            width: 240px;
            min-width: 240px;
            white-space: nowrap;
        }

        .tenants-table .tenant-actions-column {
            width: 130px;
            min-width: 130px;
            white-space: nowrap;
        }

        .tenants-table .tenant-actions-column .btn {
            padding: .35rem .55rem;
            font-size: .78rem;
        }

        .tenants-table .tenant-actions-column .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
        }
    </style>
</head>

<body>
    @include('sweetalert::alert')
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections/sidebar')
            <!-- Layout container -->
            <div class="layout-page">
                @include('sections/header')
                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4">
                            <span class="text-muted fw-light">اطلاعات پایه /</span>
                            پنل ها
                        </h4>
                        <!-- Sticky Actions -->
                        <div class="row mt-5">
                            <div class="col-12 col-sm-12 order-1 order-lg-2 mb-4 mb-lg-0">

                                <div class="card">
                                    <div
                                        class="card-header sticky-element bg-label-secondary d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                                        <div>
                                            <h5 class="card-title mb-sm-0 me-2">لیست پنل ها</h5>
                                            <small class="text-muted">مدیریت پنل های اصلی، مدیر کل هر پنل و وضعیت
                                                عملیاتی هر مجموعه</small>
                                            <div class="mt-2">
                                                <span class="badge bg-label-success me-1">فعال:
                                                    {{ number_format($activeTenantsCount) }}</span>
                                                <span class="badge bg-label-danger">غیرفعال:
                                                    {{ number_format($inactiveTenantsCount) }}</span>
                                            </div>
                                        </div>
                                        <div class="action-btns">
                                            <button class="btn btn-primary" data-bs-target="#modalTop"
                                                data-bs-toggle="modal" type="button">
                                                <x-ui.icon name="plus" class="me-1" />
                                                افزودن پنل
                                            </button>
                                        </div>
                                        <div class="modal modal-top fade" id="modalTop" tabindex="-1">
                                            <div class="modal-dialog modal-xl">
                                                <form action="{{ route('tenants.store') }}" class="modal-content"
                                                    method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalTopTitle">ایجاد پنل جدید و مدیر
                                                            کل پنل</h5>
                                                        <button aria-label="بستن" class="btn-close"
                                                            data-bs-dismiss="modal" type="button"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @if ($errors->any())
                                                            <div class="alert alert-danger mb-3">
                                                                <strong>پنل ذخیره نشد.</strong>
                                                                <ul class="mb-0 mt-2 pe-3">
                                                                    @foreach ($errors->all() as $error)
                                                                        <li>{{ $error }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif
                                                        <div class="row g-2">
                                                            <div class="col-12 col-md-4 mb-3">
                                                                <label class="form-label" for="tenant_name">نام
                                                                    پنل</label>
                                                                <input class="form-control" id="tenant_name"
                                                                    name="name" placeholder="نام پنل را وارد کنید"
                                                                    required type="text"
                                                                    value="{{ old('name') }}" />
                                                            </div>
                                                            <div class="col-12 col-md-4 mb-3">
                                                                <label class="form-label" for="tenant_legal_name">نام
                                                                    شرکت</label>
                                                                <input class="form-control" id="tenant_legal_name"
                                                                    name="legal_name"
                                                                    placeholder="نام ثبتی یا تجاری شرکت" required
                                                                    type="text" value="{{ old('legal_name') }}" />
                                                            </div>
                                                            <div class="col-12 col-md-4 mb-3">
                                                                <label class="form-label" for="tenant_phone">شماره
                                                                    تماس</label>
                                                                <input class="form-control" id="tenant_phone"
                                                                    inputmode="numeric" name="phone"
                                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                                    pattern="[0-9]*" placeholder="021..."
                                                                    type="tel" value="{{ old('phone') }}" />
                                                            </div>
                                                            <div class="col-12 col-md-4 mb-3">
                                                                <label class="form-label" for="subscription_type">نوع
                                                                    اشتراک</label>
                                                                <select class="form-select" id="subscription_type"
                                                                    name="subscription_type" required>
                                                                    <option value="">انتخاب کنید</option>
                                                                    @foreach ($subscriptionOptions as $value => $option)
                                                                        <option value="{{ $value }}"
                                                                            @if (old('subscription_type') === $value) selected @endif>
                                                                            {{ $option['label'] }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <h6 class="mb-3">اطلاعات مدیر کل پنل</h6>
                                                        <div class="row g-2">
                                                            <div class="col-12 col-md-4 mb-3">
                                                                <label class="form-label" for="admin_name">نام مدیر
                                                                    کل</label>
                                                                <input class="form-control" id="admin_name"
                                                                    name="admin_name" placeholder="نام و نام خانوادگی"
                                                                    required type="text"
                                                                    value="{{ old('admin_name') }}" />
                                                            </div>
                                                            <div class="col-12 col-md-6 mb-3">
                                                                <label class="form-label" for="admin_mobile">شماره
                                                                    همراه مدیر کل</label>
                                                                <input class="form-control" id="admin_mobile"
                                                                    inputmode="numeric" name="admin_mobile"
                                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                                    pattern="[0-9]*" placeholder="09121234567"
                                                                    type="tel"
                                                                    value="{{ old('admin_mobile') }}" />
                                                            </div>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col-12 col-md-6 mb-3">
                                                                <label class="form-label" for="admin_username">نام
                                                                    کاربری</label>
                                                                <input class="form-control" id="admin_username"
                                                                    name="admin_username"
                                                                    placeholder="مثلا manager-almas" required
                                                                    type="text"
                                                                    value="{{ old('admin_username') }}" />
                                                            </div>
                                                            <div class="col-12 col-md-6 mb-3">
                                                                <label class="form-label" for="admin_email">ایمیل مدیر
                                                                    کل</label>
                                                                <input class="form-control" id="admin_email"
                                                                    name="admin_email"
                                                                    placeholder="manager@example.com" type="email"
                                                                    value="{{ old('admin_email') }}" />
                                                            </div>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col-12 col-md-4 mb-3">
                                                                <label class="form-label" for="admin_national_code">کد
                                                                    ملی</label>
                                                                <input class="form-control" id="admin_national_code"
                                                                    name="admin_national_code" inputmode="numeric"
                                                                    pattern="[0-9]*"
                                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                                    placeholder="0012345678" type="tel"
                                                                    value="{{ old('admin_national_code') }}" />
                                                            </div>
                                                            <div class="col-12 col-md-4 mb-3">
                                                                <label class="form-label" for="admin_postal_code">کد
                                                                    پستی</label>
                                                                <input class="form-control" id="admin_postal_code"
                                                                    name="admin_postal_code" placeholder="1234567890"
                                                                    inputmode="numeric"
                                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                                    pattern="[0-9]*" type="tel"
                                                                    value="{{ old('admin_postal_code') }}" />
                                                            </div>
                                                            <div class="col-12 col-md-6 mb-3">
                                                                <label class="form-label" for="admin_password">رمز
                                                                    عبور مدیر کل</label>
                                                                <input class="form-control" id="admin_password"
                                                                    name="admin_password"
                                                                    placeholder="حداقل 6 کاراکتر" required
                                                                    type="password" />
                                                            </div>
                                                            <div class="col-12 mb-3">
                                                                <label class="form-label"
                                                                    for="admin_address">آدرس</label>
                                                                <textarea class="form-control" id="admin_address" name="admin_address" placeholder="آدرس محل سکونت یا دفتر مدیر کل"
                                                                    rows="2">{{ old('admin_address') }}</textarea>
                                                            </div>
                                                        </div>
                                                        <small class="text-muted">برای پنل آزمایشی، نوع اشتراک «دموی 3
                                                            روزه» را انتخاب کنید؛ تاریخ پایان اشتراک از امروز تا سه روز
                                                            بعد محاسبه می شود. مدیر کل می تواند با نام کاربری، ایمیل
                                                            یا
                                                            شماره همراه وارد شود. بعد از پایان اشتراک پنل، ورود کاربران
                                                            همان پنل بسته می شود.</small>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-label-secondary"
                                                            data-bs-dismiss="modal" type="button"> بستن</button>
                                                        <button class="btn btn-primary" type="submit">ساخت پنل و مدیر
                                                            کل</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-datatable table-responsive py-0">
                                        <table class="datatables-direct-basic table tenants-table">
                                            <thead>
                                                <tr>
                                                    <th class="tenant-row-number">#</th>
                                                    <th class="tenant-name-column">نام پنل</th>
                                                    <th>تعداد واحد / شعبه</th>
                                                    <th>تعداد کارمندان</th>
                                                    <th>تعداد مشتریان</th>
                                                    <th>تعداد فاکتور ها</th>
                                                    <th>مجموع فروش</th>
                                                    <th>شهر/مناطق/مسیرها</th>
                                                    <th>اشتراک</th>
                                                    <th>وضعیت پنل</th>
                                                    <th class="tenant-actions-column">عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($Tenants as $tenant)
                                                    @php($stats = $tenant->dashboard_stats)
                                                    <tr>
                                                        <td class="tenant-row-number">{{ $loop->iteration }}</td>
                                                        <td class="tenant-name-column">
                                                            <a href="{{ route('tenants.edit', $tenant->id) }}"
                                                                class="fw-semibold">
                                                                {{ $tenant->name }}
                                                            </a>
                                                            <div class="text-muted small">
                                                                شرکت: {{ $tenant->legal_name ?: 'ثبت نشده' }}
                                                            </div>
                                                            <div class="text-muted small">
                                                                تاریخ ساخت:
                                                                {{ $tenant->created_at ? verta_date($tenant->created_at) : 'ثبت نشده' }}
                                                            </div>
                                                        </td>
                                                        <td>{{ number_format($stats['organizations_count']) }}</td>
                                                        <td>{{ number_format($stats['users_count']) }}</td>
                                                        <td>{{ number_format($stats['customers_count']) }}</td>
                                                        <td>{{ number_format($stats['pishfactors_count']) }}</td>
                                                        <td>{{ number_format($stats['total_sales']) }}</td>
                                                        <td>{{ $stats['geo_summary'] }}</td>
                                                        <td>
                                                            <span class="badge bg-label-info">
                                                                {{ $subscriptionOptions[$tenant->subscription_type]['label'] ?? 'ثبت نشده' }}
                                                            </span>
                                                            <div class="text-muted small">شروع:
                                                                {{ $tenant->subscription_started_at ? verta_date($tenant->subscription_started_at) : 'ثبت نشده' }}
                                                            </div>
                                                            @if ($tenant->subscription_ends_at)
                                                                <div class="text-muted small">پایان:
                                                                    {{ verta_date($tenant->subscription_ends_at) }}
                                                                </div>
                                                            @elseif ($tenant->subscription_type === 'permanent')
                                                                <div class="text-muted small">پایان: بدون انقضا</div>
                                                            @else
                                                                <div class="text-muted small">پایان: ثبت نشده</div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($tenant->status == 1)
                                                                <span class="badge  bg-label-success">فعال</span>
                                                            @else
                                                                <span class="badge  bg-label-danger">غیرفعال</span>
                                                            @endif
                                                        </td>
                                                        <td class="tenant-actions-column">
                                                            <a class="btn btn-sm btn-label-primary"
                                                                href="{{ route('tenants.edit', $tenant->id) }}">
                                                                <x-ui.icon name="edit" class="me-1" />ویرایش
                                                            </a>

                                                            @if (auth()->user()->isGod == 1)
                                                                <form
                                                                    action="{{ route('tenants.destroy', $tenant->id) }}"
                                                                    method="POST" class="d-inline-block"
                                                                    onsubmit="return confirm('آیا از حذف پنل مورد نظر اطمینان دارید؟');">
                                                                    @method('delete')
                                                                    @csrf
                                                                    <button
                                                                        class="btn btn-sm btn-icon btn-label-danger"
                                                                        type="submit">
                                                                        <x-ui.icon name="trash" />
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Sticky Actions -->
                    </div>
                    <!-- / Content -->
                    @include('sections/footer')
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
            @if ($errors->any())
                $('#modalTop').modal('show');
            @endif

            var
                dt_without_ajax_table = $('.datatables-direct-basic');

            // DataTable Direct
            // --------------------------------------------------------------------
            if (dt_without_ajax_table.length) {
                dt_without_ajax = dt_without_ajax_table.DataTable({
                    searching: true,
                    lengthChange: true,
                    ordering: true,
                    pageLength: 25,
                    columnDefs: [{
                        targets: [0, 10],
                        orderable: false,
                        searchable: false
                    }],
                    language: {
                        search: 'جستجو: ',
                        searchPlaceholder: 'نام پنل، شماره یا وضعیت...',
                        info: 'نمایش _START_ تا _END_ از _TOTAL_ پنل',
                        infoEmpty: 'پنلی ثبت نشده است.',
                        lengthMenu: 'نمایش _MENU_ پنل',
                        zeroRecords: 'پنلی پیدا نشد',
                        paginate: {
                            previous: 'قبلی',
                            next: 'بعدی',
                        }
                    }
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
