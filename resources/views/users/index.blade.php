<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>مدیریت کاربران سامانه - دکان دارمینو</title>
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
        .users-table-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 0 0 16px;
            background: #fff;
        }

        .users-toolbar-search,
        .users-toolbar-filters {
            display: flex;
            align-items: center;
        }

        .users-toolbar-search {
            align-self: flex-end;
        }

        .users-toolbar-search .dataTables_filter {
            background: #d9ead7;
            padding: 14px 18px 10px;
            border-radius: 14px 14px 0 0;
            min-width: 340px;
        }

        .users-toolbar-filters {
            padding-bottom: 10px;
        }

        .users-filters {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .users-filters .form-select {
            min-width: 180px;
        }

        .dataTables_filter {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
            margin: 0;
        }

        .dataTables_filter label {
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #5f656f;
            white-space: nowrap;
        }

        .dataTables_filter input {
            min-width: 250px;
            margin: 0 !important;
        }

        .datatables-direct-basic thead th:first-child {
            border-top-right-radius: 0 !important;
        }

        .table thead tr th:first-child {
            border-top-right-radius: 0px !important;
        }

        @media (max-width: 768px) {
            .users-table-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .users-toolbar-search,
            .users-toolbar-filters {
                width: 100%;
            }

            .users-toolbar-search {
                align-self: stretch;
            }

            .users-toolbar-search .dataTables_filter {
                min-width: 0;
                width: 100%;
                padding: 12px;
            }

            .users-filters {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }

            .users-filters .form-select {
                width: 100%;
                min-width: 0;
            }

            .dataTables_filter {
                width: 100%;
            }

            .dataTables_filter label {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
            }

            .dataTables_filter input {
                min-width: 0;
                width: 100% !important;
            }
        }
    </style>
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
                            <span class="text-muted fw-light">کاربران /</span>
                            مدیریت کاربران سامانه
                        </h4>
                        <div class="row g-4 mb-4">
                            <div class="col-sm-6 col-xl-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left"> <span>مجموع پرسنل</span>
                                                <div class="d-flex align-items-center my-2">
                                                    <h3 class="mb-0 me-2">{{ number_format($usersTotal) }}</h3>
                                                </div>
                                            </div>
                                            <div class="avatar"> <span class="avatar-initial rounded bg-label-primary">
                                                    <x-ui.icon name="user" class="ti-sm" /> </span> </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left"> <span>کاربران فعال</span>
                                                <div class="d-flex align-items-center my-2">
                                                    <h3 class="mb-0 me-2">{{ number_format($activeUsersCount) }}</h3>
                                                </div>
                                            </div>
                                            <div class="avatar"> <span class="avatar-initial rounded bg-label-success">
                                                    <x-ui.icon name="user-plus" class="ti-sm" /> </span> </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="content-left"> <span>کاربران غیرفعال</span>
                                                <div class="d-flex align-items-center my-2">
                                                    <h3 class="mb-0 me-2">{{ number_format($deactiveUsersCount) }}</h3>
                                                </div>
                                            </div>
                                            <div class="avatar"> <span class="avatar-initial rounded bg-label-danger">
                                                    <x-ui.icon name="user-check" class="ti-sm" /> </span> </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Sticky Actions -->
                        <div class="row mt-5">
                            <div class="col-12 col-sm-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                <style>
                                    tbody td {
                                        padding-top: 5px;
                                        padding-bottom: 5px;
                                    }
                                </style>
                                <div class="card">
                                    <div
                                        class="card-header sticky-element bg-label-secondary d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                                        <h5 class="card-title mb-sm-0 me-2">مدیریت کاربران سامانه</h5>
                                        <div class="action-btns">
                                            <button class="btn btn-primary" data-bs-target="#modalTop"
                                                data-bs-toggle="modal" type="button">ثبت کاربر جدید</button>
                                        </div>
                                        <div class="modal modal-top fade" id="modalTop" tabindex="-1">
                                            <div class="modal-dialog">
                                                <form class="modal-content" id="addUser"
                                                    action="{{ route('users.store') }}" method="POST" novalidate>
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="modalTopTitle">ایجاد کاربر جدید
                                                            برای سامانه</h5>
                                                        <button aria-label="بستن" class="btn-close"
                                                            data-bs-dismiss="modal" type="button"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @if ($errors->any())
                                                            <div class="alert alert-danger">
                                                                <ul class="mb-0 pe-3">
                                                                    @foreach ($errors->all() as $error)
                                                                        <li>{{ $error }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif
                                                        <div class="row g-2">
                                                            <div class="col mb-3">
                                                                <label class="form-label" for="name">نام و نام
                                                                    خانوادگی</label>
                                                                <input class="form-control" id="name"
                                                                    name="name" required
                                                                    value="{{ old('name') }}"
                                                                    placeholder="نام کامل کاربر" type="text" />
                                                            </div>
                                                            <div class="col mb-3">
                                                                <label class="form-label" for="usercode">کد نمایشی
                                                                    کاربر</label>
                                                                <input class="form-control" id="usercode"
                                                                    name="usercode" value="{{ old('usercode') }}"
                                                                    placeholder="کدنمایشی کاربر" type="text" />
                                                            </div>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col mb-3">
                                                                <label class="form-label" for="email">ایمیل
                                                                    کاربر</label>
                                                                <input class="form-control" id="email"
                                                                    name="email" required
                                                                    value="{{ old('email') }}"
                                                                    placeholder="xxxx@xxx.xx" type="email" />
                                                            </div>
                                                            <div class="col mb-3">
                                                                <label class="form-label" for="mobile">شماره همراه
                                                                    کاربر</label>
                                                                <input class="form-control nospin" id="mobile"
                                                                    name="mobile" required
                                                                    value="{{ old('mobile') }}"
                                                                    placeholder="09121234567" type="number"
                                                                    maxlength="11" />
                                                            </div>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col mb-3">
                                                                <label class="form-label" for="password">رمز عبور
                                                                    کاربر</label>
                                                                <input class="form-control" id="password"
                                                                    placeholder="رمزعبور" type="password"
                                                                    name="password" required />
                                                            </div>
                                                        </div>

                                                        @if (auth()->user()->isGod == 1)
                                                            @php($tenants = DB::table('tenants')->get())
                                                            <div class="form-group mb-3">
                                                                <label for="exampleInputEmail12">انتخاب پنل:</label>
                                                                <select class="js-example-basic-single form-control"
                                                                    name="tenants_id" style="width: 100%;">
                                                                    <option value="">--هیچکدام--</option>
                                                                    @foreach ($tenants as $tenant)
                                                                        <option value="{{ $tenant->id }}"
                                                                            @if (old('tenants_id') == $tenant->id) selected @endif>
                                                                            {{ $tenant->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @endif

                                                        <div class="row g-2">
                                                            <div class="col mb-3">
                                                                <label for="exampleInputEmail12">انتخاب شعبه:</label>
                                                                <select class="js-example-basic-single form-control"
                                                                    required name="organization_id"
                                                                    style="width: 100%;">
                                                                    <option value="0">--هیچکدام--</option>
                                                                    @if (\Auth::user()->isAdmin == 1)
                                                                        @foreach ($organizations as $organization)
                                                                            <option value="{{ $organization->id }}"
                                                                                @if (old('organization_id') == $organization->id) selected @endif>
                                                                                {{ $organization->description }}
                                                                            </option>
                                                                        @endforeach
                                                                    @else
                                                                        <option
                                                                            value="{{ \Auth::user()->organization_id }}"
                                                                            selected>
                                                                            {{ optional(\Auth::user()->organization)->title ?? '—' }}
                                                                        </option>
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col mb-3">
                                                                <label for="exampleInputEmail12">انتخاب نقش
                                                                    کاربری:</label>
                                                                <select class="js-example-basic-single form-control"
                                                                    name="role_id" style="width: 100%;">
                                                                    <option value="">--هیچکدام--</option>
                                                                    @foreach ($roles as $role)
                                                                        <option value="{{ $role->id }}"
                                                                            @if (old('role_id') == $role->id) selected @endif>
                                                                            {{ $role->description }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col mb-3">
                                                                <div class="border rounded p-3">
                                                                    <h6 class="mb-3">محدوده اختصاصی کاربر</h6>
                                                                    <div class="row g-3">
                                                                        @foreach ($scopeLabels as $scopeType => $scopeLabel)
                                                                            <div class="col-12 col-md-6">
                                                                                <label class="form-label"
                                                                                    for="user_scope_{{ $scopeType }}">{{ $scopeLabel }}</label>
                                                                                <select class="select2 form-select"
                                                                                    id="user_scope_{{ $scopeType }}"
                                                                                    name="scopes[{{ $scopeType }}][]"
                                                                                    multiple style="width: 100%;">
                                                                                    @foreach ($scopeOptions[$scopeType] ?? collect() as $scopeOption)
                                                                                        <option
                                                                                            value="{{ $scopeOption->id }}"
                                                                                            @if (in_array((int) $scopeOption->id, old("scopes.$scopeType", $selectedUserScopes[$scopeType] ?? []), true)) selected @endif>
                                                                                            {{ $scopeOption->title }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                    <small class="text-muted d-block mt-2">اگر خالی
                                                                        بماند، محدوده از نقش کاربر و شعبه انتخاب شده
                                                                        خوانده می شود.</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col mb-3">
                                                                <label class="form-label" for="leader_id">انتخاب
                                                                    سرپرست: </label>
                                                                <select class="select2 form-select"
                                                                    data-allow-clear="true" id="leader_id"
                                                                    name="leader_id">
                                                                    <option value="">انتخاب کنید</option>
                                                                    @foreach ($Leaders as $leader)
                                                                        <option value="{{ $leader->id }}"
                                                                            @if (old('leader_id') == $leader->id) selected @endif>
                                                                            {{ $leader->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-primary" type="submit">ایجاد
                                                            کاربر</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    
                                    <div class="card-body border-bottom">
                                        <form class="row g-3" method="GET" action="{{ route('users.index') }}">
                                            <div class="col-md-4">
                                                <label class="form-label">جستجو</label>
                                                <input type="text" name="q" value="{{ request('q') }}"
                                                    class="form-control" placeholder="نام، کد، موبایل یا ایمیل">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">وضعیت</label>
                                                <select name="status" class="form-select">
                                                    <option value="">همه وضعیت ها</option>
                                                    <option value="1" @selected(request('status') === '1')>فعال</option>
                                                    <option value="0" @selected(request('status') === '0')>غیرفعال
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">نقش</label>
                                                <select name="role_id" class="form-select">
                                                    <option value="">همه نقش ها</option>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->id }}"
                                                            @selected((string) request('role_id') === (string) $role->id)>
                                                            {{ trim($role->description) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end gap-2">
                                                <button class="btn btn-primary" type="submit">فیلتر</button>
                                                <a class="btn btn-label-secondary"
                                                    href="{{ route('users.index') }}">پاک کردن</a>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="card-datatable table-responsive py-0">
                                        <table class="datatables-direct-basic table">
                                            <thead>
                                                <tr>
                                                    <th>ردیف</th>
                                                    <th>کد کاربر</th>
                                                    <th>نام کاربر</th>
                                                    <th>موبایل کاربر</th>
                                                    <th>ایمیل کاربر</th>
                                                    <th>نقش کاربر</th>
                                                    <th>محدوده</th>
                                                    <th>وضعیت کاربر</th>
                                                    <th>عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($users as $user)
                                                    <tr>
                                                        <td>{{ $users->firstItem() + $loop->index }}</td>
                                                        <td><a
                                                                href="{{ route('users.edit', $user->id) }}">{{ $user->username }}</a>
                                                        </td>
                                                        <td><a
                                                                href="{{ route('users.edit', $user->id) }}">{{ $user->name }}</a>
                                                        </td>
                                                        <td>{{ $user->mobile }}</td>
                                                        <td>{{ $user->email }}</td>
                                                        <td>
                                                            @foreach ($user->roles as $role)
                                                                {{ $role->description }} -
                                                            @endforeach
                                                        </td>
                                                        <td>{{ $userScopeSummaries[$user->id] ?? 'از نقش' }}</td>

                                                        <td>
                                                            @if ($user->isActive == 1)
                                                                <span class="badge bg-label-success me-1">فعال</span>
                                                            @else
                                                                <span class="badge bg-label-danger me-1">غیرفعال</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('users.edit', $user->id) }}"
                                                                style="font-size:20px;float: right;margin-left:5px"><x-ui.icon name="fa-edit" /></a>
                                                            <form action="{{ route('users.destroy', $user->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('آیا از حذف کاربر مورد نظر اطمینان دارید؟');">
                                                                @method('delete')
                                                                @csrf
                                                                <button type="submit"
                                                                    style="font-size:20px;border: none;background-color: transparent;float: right;">
                                                                    <x-ui.icon name="fa-trash" />
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">
                                        {{ $users->links() }}
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
    <script></script>
    <script>
        $(document).ready(function() {
            @if ($errors->any())
                var userModal = new bootstrap.Modal(document.getElementById('modalTop'));
                userModal.show();
            @endif

            $('#addUser').on('submit', function(e) {
                let isValid = true;

                // پاک کردن پیام‌های قبلی
                $('.error-message').remove();

                // چک کردن تمام فیلدهای این فرم که الزامی هستند
                $(this).find('input[required], select[required]').each(function() {
                    let $field = $(this);
                    let value = $.trim($field.val());

                    // شرط: اگر select باشد و مقدارش 0 باشد، یا اینپوت خالی باشد
                    if (($field.is('select') && value === '0') ||
                        ($field.is('input') && value === '')) {

                        isValid = false;

                        // ساخت پیام خطا
                        let errorMsg = $(
                            '<div class="error-message" style="color:red;font-size:12px;margin-top:4px;">این فیلد الزامی است</div>'
                        );

                        // درج پیام بعد از فیلد
                        if ($field.next('.select2').length) {
                            // اگر select2 هست، پیام رو بعد از container select2 بذاریم
                            $field.next('.select2').after(errorMsg);
                        } else {
                            $field.after(errorMsg);
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault(); // جلوگیری از ارسال فرم
                }
            });
        });
    </script>
</body>

</html>
