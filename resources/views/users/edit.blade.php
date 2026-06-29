<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ویرایش کاربر - دکان دارمینو</title>
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
                            ویرایش کاربران
                        </h4>
                        <!-- Sticky Actions -->
                        <div class="row mt-5">
                            <div class="col-12 col-md-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <form action="{{ route('users.update', $userEdit->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="mb-3">
                                                <label for="name">نام و نام خانوادگی:</label>
                                                <input type="text" class="form-control" name="name"
                                                    value="{{ $userEdit->name }}" required id="name">
                                            </div>
                                            <div class="mb-3">
                                                <label for="UserName">نام نمایشی (کد پرسنلی):</label>
                                                <input type="text" class="form-control" name="username"
                                                    value="{{ $userEdit->username }}" id="UserName">
                                            </div>
                                            <div class="mb-3">
                                                <label for="userMail">ایمیل سازمانی:</label>
                                                <input type="text" required class="form-control"
                                                    value="{{ $userEdit->email }}" name="email" id="userMail">
                                            </div>
                                            <div class="mb-3">
                                                <label for="mobile">شماره موبایل:</label>
                                                <input type="text" class="form-control" name="mobile" id="mobile"
                                                    value="{{ $userEdit->mobile }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="password">رمز کاربر:</label>
                                                <input type="text" class="form-control" name="password"
                                                    id="password">
                                            </div>
                                            <div class="mb-3" id="leaderFieldWrapper">
                                                <label for="userLeader">انتخاب سرپرست:</label>
                                                <select class="js-example-basic-single form-control" name="leader_id"
                                                    id="userLeader" style="width: 100%;">
                                                    <option value="">--هیچکدام--</option>
                                                    @foreach ($Leaders as $leader)
                                                        <option value="{{ $leader->id }}"
                                                            {{ $userEdit->leader_id == $leader->id ? 'selected' : '' }}>
                                                            {{ $leader->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @if (auth()->user()->isGod == 1)
                                                <div class="mb-3">
                                                    <label for="tenants_id">انتخاب پنل:</label>
                                                    <select class="js-example-basic-single form-control"
                                                        id="tenants_id" name="tenants_id" style="width: 100%;">
                                                        <option value="">--هیچکدام--</option>
                                                        @foreach ($Tenants as $tenant)
                                                            <option value="{{ $tenant->id }}"
                                                                {{ $tenant->id == $userEdit->tenants_id ? 'selected' : '' }}>
                                                                {{ $tenant->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                            <div class="mb-3">
                                                <label for="exampleInputEmail12">انتخاب واحد پخش:</label>
                                                <select class="select2 form-select" required name="organization_id[]"
                                                    multiple style="width: 100%;">
                                                    <option value="">--هیچکدام--</option>
                                                    @if (\Auth::user()->isAdmin == 1)
                                                        @foreach ($organizations as $organization)
                                                            <option value="{{ $organization->id }}"
                                                                @if (is_array(json_decode($userEdit->organization_id)) &&
                                                                        in_array($organization->id, json_decode($userEdit->organization_id))) selected @elseif($organization->id == $userEdit->organization_id) selected @endif>
                                                                {{ $organization->description }}</option>
                                                        @endforeach
                                                    @else
                                                        <option value="{{ \Auth::user()->organization_id }}" selected>
                                                            {{ optional(\Auth::user()->organization)->title ?? '—' }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="exampleInputEmail12">انتخاب نقش کاربری:</label>
                                                <select class="js-example-basic-single form-control" id="role_id"
                                                    name="role_id" style="width: 100%;">
                                                    <option value="">--هیچکدام--</option>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->id }}"
                                                            data-title="{{ $role->title }}"
                                                            {{ in_array($role->id, $userEdit->roles->pluck('id')->toArray()) ? 'selected' : '' }}>
                                                            {{ $role->description }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <div class="border rounded p-3">
                                                    <h6 class="mb-3">محدوده اختصاصی کاربر</h6>
                                                    <div class="row g-3">
                                                        @foreach ($scopeLabels as $scopeType => $scopeLabel)
                                                            <div class="col-12 col-md-6">
                                                                <label class="form-label"
                                                                    for="user_scope_{{ $scopeType }}">{{ $scopeLabel }}</label>
                                                                <select class="select2 form-select"
                                                                    id="user_scope_{{ $scopeType }}"
                                                                    name="scopes[{{ $scopeType }}][]" multiple
                                                                    style="width: 100%;">
                                                                    @foreach ($scopeOptions[$scopeType] ?? collect() as $scopeOption)
                                                                        <option value="{{ $scopeOption->id }}"
                                                                            @if (in_array((int) $scopeOption->id, $selectedUserScopes[$scopeType] ?? [], true)) selected @endif>
                                                                            {{ $scopeOption->title }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <small class="text-muted d-block mt-2">اگر خالی بماند، محدودیت از
                                                        نقش کاربر و واحد پخش انتخاب شده اعمال می شود.</small>
                                                </div>
                                            </div>
                                            <?php
                                            $Driverer = DB::table('roles')->where('title', 'driver')->first();
                                            $IsDriver = DB::table('role_user')->where('role_id', $Driverer->id)->where('user_id', $userEdit->id)->count();
                                            ?>
                                            <div class="mb-3 driver_box {{ $IsDriver > 0 ? '' : 'd-none' }}">
                                                <label for="pr_weight">طرفیت گنجایش کارتن در ناوگان:</label>
                                                <div class="input-group">
                                                    <input aria-label="طرفیت ناوگان" class="form-control"
                                                        id="box_count_driver" name="box_count_driver" type="number"
                                                        value="{{ $userEdit->cargo ? $userEdit->cargo->cartons : '' }}" />
                                                    <button aria-expanded="false" class="btn btn-outline-primary"
                                                        type="button">
                                                        کارتن
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="mb-3 driver_box {{ $IsDriver > 0 ? '' : 'd-none' }}">
                                                <label for="pr_weight">طرفیت وزن ناوگان:</label>
                                                <div class="input-group">
                                                    <input aria-label="طرفیت وزن" class="form-control"
                                                        id="weight_count_driver" name="weight_count_driver"
                                                        type="number"
                                                        value="{{ $userEdit->cargo ? $userEdit->cargo->weight : '' }}" />
                                                    <button aria-expanded="false" class="btn btn-outline-primary"
                                                        type="button">
                                                        کیلوگرم
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="checkbox checkbox-primary mb-5">
                                                <input type="checkbox" name="isActive" id="checkbox-p-1"
                                                    {{ $userEdit->isActive ? 'checked' : '' }}>
                                                <label for="checkbox-p-1" class="cr">فعال</label>
                                            </div>
                                            <button class="btn btn-primary" type="submit">به روزرسانی کاربر</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @if (is_array($UserFactors['factors']))
                                <div class="col-12 col-md-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                    <div class="card mb-4">
                                        <div class="card-body">
                                            <div class="card-datatable table-responsive py-0">
                                                <table class="datatables-direct-basic table">
                                                    <thead>
                                                        <tr>
                                                            <th width="30">ردیف</th>
                                                            <th>تاریخ ثبت</th>
                                                            <th>شماره فاکتور</th>
                                                            <th>واحد پخش</th>
                                                            <th>نام خریدار</th>
                                                            <th>مجموع مقدار</th>
                                                            <th>تاریخ تحویل</th>
                                                            <th>مبلغ کل</th>
                                                            <th>بازاریاب</th>
                                                            <th>سرپرست</th>
                                                            <th>وضعیت</th>
                                                            <th>عملیات</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $organ = App\Models\Organization::find(auth()->user()->organization_id); ?>
                                                        @php($x = 1)
                                                        @foreach ($UserFactors['factors'] as $invoice)
                                                            <tr>
                                                                <th class="text-center">
                                                                    <small>{{ $x }}</small></th>
                                                                <td><small data-bs-toggle="tooltip"
                                                                        data-bs-placement="top"
                                                                        data-bs-custom-class="custom-tooltip"
                                                                        data-bs-title="ساعت {{ Verta($invoice->created_at)->format('H:i') }}">{{ Verta($invoice->created_at)->format('Y-m-d') }}</small>
                                                                </td>
                                                                <td class="text-center">{{ $invoice->invoiceID }}
                                                                </td>
                                                                <td>{{ $invoice->organization ? $invoice->organization->title : '---' }}
                                                                </td>
                                                                <td><a
                                                                        href="{{ route('pishFactorInfo', $invoice->id) }}">{{ isset($invoice->customer->name) ? $invoice->customer->name : 'وارد نشده' }}</a>
                                                                </td>
                                                                <td class="text-center">
                                                                    <?php $details = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->count(); ?>
                                                                    <small data-bs-toggle="tooltip"
                                                                        data-bs-placement="top"
                                                                        data-bs-custom-class="custom-tooltip"
                                                                        data-bs-title="{{ $details }} قلم">
                                                                        <?php
                                                                        $Packs = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->sum('pack');
                                                                        $tedad = App\Models\PishFactorItems::where('pishfactor_id', $invoice->id)->sum('tedad');
                                                                        ?>
                                                                        @if ($Packs > 0)
                                                                            {{ $Packs }} {{ $organ->sub_unit }}
                                                                        @endif
                                                                        @if ($tedad > 0)
                                                                            {{ $tedad }}
                                                                            {{ $organ->unit_order }}
                                                                        @endif

                                                                    </small>
                                                                </td>
                                                                <td><small>{{ $invoice->recive_date ? $invoice->recive_date : 'وارد نشده' }}</small>
                                                                </td>
                                                                <td>

                                                                    <small>
                                                                        {{ number_format(intval(str_replace(',', '', $invoice->fullPrice))) }}
                                                                        {{ org_currency_label($organ) }}
                                                                    </small>
                                                                </td>
                                                                @if (auth()->user()->isAdmin == 1 || auth()->user()->isGod == 1)
                                                                    <?php $Visitor = App\Models\User::find($invoice->visitor_id); ?>
                                                                    <td><small>{{ $Visitor->name }}</small></td>
                                                                @endif
                                                                <?php $leader = App\Models\User::find($invoice->sarparast_id); ?>
                                                                <td><small>{{ $leader->name }}</small></td>
                                                                <td>
                                                                    @if ($invoice->status == 0)
                                                                        <span class="badge bg-label-warning me-1">منتظر
                                                                            تایید</span> <br />
                                                                    @elseif($invoice->status == 1)
                                                                        <span class="badge bg-label-success me-1">تایید
                                                                            شده</span> <br />
                                                                    @elseif($invoice->status == 4)
                                                                        <span class="badge bg-label-info me-1">تحویل به
                                                                            مشتری</span> <br />
                                                                    @elseif($invoice->status == 3)
                                                                        <span class="badge bg-label-danger me-1">رد
                                                                            شده</span> <br />
                                                                    @elseif($invoice->status == 5)
                                                                        <span
                                                                            class="badge bg-label-warning me-1">مرجوعی</span>
                                                                        <br />
                                                                    @endif


                                                                </td>
                                                                <td class="text-center">
                                                                    <a class="d-inline-block me-3"
                                                                        href="{{ route('pishFactorInfo', $invoice->id) }}"><svg
                                                                            width="24" height="21"
                                                                            viewBox="0 0 14 11" fill="none"
                                                                            xmlns="http://www.w3.org/2000/svg">
                                                                            <path
                                                                                d="M1.02324 5.6932C0.981836 5.56875 0.981836 5.43425 1.02324 5.3098C1.85544 2.806 4.21764 1 7.00164 1C9.78444 1 12.1454 2.8042 12.9794 5.3068C13.0214 5.431 13.0214 5.5654 12.9794 5.6902C12.1478 8.194 9.78564 10 7.00164 10C4.21884 10 1.85724 8.1958 1.02324 5.6932Z"
                                                                                stroke="#248230"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round" />
                                                                            <path
                                                                                d="M8.80312 5.49995C8.80312 5.97734 8.61348 6.43518 8.27592 6.77274C7.93835 7.11031 7.48051 7.29995 7.00312 7.29995C6.52574 7.29995 6.0679 7.11031 5.73033 6.77274C5.39277 6.43518 5.20312 5.97734 5.20312 5.49995C5.20312 5.02256 5.39277 4.56472 5.73033 4.22716C6.0679 3.88959 6.52574 3.69995 7.00312 3.69995C7.48051 3.69995 7.93835 3.88959 8.27592 4.22716C8.61348 4.56472 8.80312 5.02256 8.80312 5.49995Z"
                                                                                stroke="#248230"
                                                                                stroke-linecap="round"
                                                                                stroke-linejoin="round" />
                                                                        </svg>
                                                                    </a>
                                                                    @if (auth()->user()->isAdmin == 1 || auth()->user()->isGod == 1)
                                                                        <form class="d-inline"
                                                                            action="{{ route('pishfactor.destroy', $invoice->id) }}"
                                                                            method="POST"
                                                                            onsubmit="return confirm('آیا از حذف فاکتور مورد نظر اطمینان دارید؟');">
                                                                            @csrf
                                                                            <button type="submit" class="d-inline"
                                                                                style="border: 0 none; background: transparent">
                                                                                <svg width="13" height="14"
                                                                                    viewBox="0 0 13 14" fill="none"
                                                                                    xmlns="http://www.w3.org/2000/svg">
                                                                                    <path
                                                                                        d="M8.31739 5.00023L8.08672 11.0001M4.89472 11.0001L4.66406 5.00023M11.3094 2.86028C11.5374 2.89495 11.7641 2.93162 11.9907 2.97095M11.3094 2.86028L10.5974 12.1154C10.5683 12.4922 10.3981 12.8441 10.1207 13.1008C9.84337 13.3576 9.47933 13.5001 9.10139 13.5H3.88006C3.50212 13.5001 3.13807 13.3576 2.86071 13.1008C2.58335 12.8441 2.41312 12.4922 2.38406 12.1154L1.67206 2.86028M11.3094 2.86028C10.54 2.74397 9.76657 2.65569 8.99072 2.59562M1.67206 2.86028C1.44406 2.89428 1.21739 2.93095 0.990723 2.97028M1.67206 2.86028C2.44148 2.74397 3.21488 2.65569 3.99072 2.59562M8.99072 2.59562V1.98497C8.99072 1.19833 8.38406 0.542346 7.59739 0.51768C6.8598 0.494107 6.12165 0.494107 5.38406 0.51768C4.59739 0.542346 3.99072 1.199 3.99072 1.98497V2.59562M8.99072 2.59562C7.32654 2.46701 5.65491 2.46701 3.99072 2.59562"
                                                                                        stroke="#C1292E"
                                                                                        stroke-linecap="round"
                                                                                        stroke-linejoin="round" />
                                                                                </svg>

                                                                            </button>
                                                                        </form>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            @php($x++)
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
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
        $('.users').addClass('open')
        $('.users .userslist').addClass('active open')
        $(function() {
            var
                dt_without_ajax_table = $('.datatables-direct-basic');

            // DataTable Direct
            // --------------------------------------------------------------------
            if (dt_without_ajax_table.length) {
                dt_without_ajax = dt_without_ajax_table.DataTable({
                    searching: true,
                    lengthChange: false,
                    ordering: true,
                    order: [
                        [0, 'asc']
                    ],
                    pageLength: 25,
                    language: {
                        search: 'جستجو: ',
                        searchPlaceholder: 'جستجو کنید...',
                        info: 'نمایش صفحه _PAGE_ از _PAGES_',
                        infoEmpty: 'موردی وجود ندارد.',
                        infoFiltered: '(فیلتر شده _MAX_ از records)',
                        lengthMenu: 'نمایش _MENU_ مورد در صفحه',
                        zeroRecords: 'متاسفانه موردی پیدا نشد',
                        paginate: {
                            previous: 'قبلی',
                            next: 'بعدی',
                        }
                    }
                });

            }


        });

        $(document).ready(function() {
            // Roles that ARE supervisors — subordinate roles do not need this set
            var supervisorRoles = ['leader', 'expert'];

            function toggleLeaderField() {
                var roleTitle = $('#role_id').find('option:selected').attr('data-title') || '';
                var needsSupervisor = roleTitle !== '' && supervisorRoles.indexOf(roleTitle) === -1;
                $('#leaderFieldWrapper').toggle(needsSupervisor);
            }

            $('#role_id').on('change', function() {
                var role = $(this).find('option:selected').attr('data-title');
                if (role == 'driver') {
                    $('.driver_box').removeClass('d-none');
                } else {
                    $('.driver_box').addClass('d-none');
                }
                toggleLeaderField();
            });

            // Run on page load to reflect current role
            toggleLeaderField();
        });
    </script>
</body>

</html>
