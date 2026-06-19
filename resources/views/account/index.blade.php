<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>مدیریت حساب ها - دکان دارمینو</title>
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
    <link href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" rel="stylesheet" />
    {{-- Page-scoped fix for the accounts DataTable top control bar (green box) only --}}
    <style>
        /* Make the green control bar wrap the WHOLE top row (length + search) comfortably */
        #accounts-datatable-card .dataTables_wrapper .row:first-child {
            background-color: #D0E4D3;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            padding: 14px 18px;
            margin: 0;
            align-items: center;
            direction: ltr;
        }

        #accounts-datatable-card .dataTables_wrapper .row:first-child > div {
            display: flex;
            align-items: center;
            padding: 0 !important;
        }

        /* Length selector fully on the LEFT, vertically centered, no separate box */
        #accounts-datatable-card .dataTables_length {
            justify-content: flex-start;
            margin: 0 !important;
            padding: 0 !important;
            background-color: transparent !important;
        }

        #accounts-datatable-card .dataTables_length label {
            margin: 0;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        /* Search box fully on the RIGHT, blended into the row's green background */
        #accounts-datatable-card .dataTables_filter {
            width: auto !important;
            float: none !important;
            margin: 0 0 0 auto !important;
            padding: 0 !important;
            background-color: transparent !important;
            border-radius: 0 !important;
            display: flex;
            justify-content: flex-end !important;
            align-items: center;
        }

        #accounts-datatable-card .dataTables_filter label {
            margin: 0;
            display: flex;
            align-items: center;
            gap: .5rem;
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
                            <span class="text-muted fw-light">اطلاعات پایه /</span>
                            مدیریت حساب ها
                        </h4>
                        <!-- Sticky Actions -->
                        <div class="row mt-5">
                            <div class="col-12 col-md-4 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <form id="addStore" action="{{ route('Account.store') }}" method="POST"
                                            novalidate>
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label" for="name">نام حساب<small
                                                        style="color: red">*</small></label>
                                                <input class="form-control" id="name" placeholder="عنوان حساب"
                                                    type="text" name="name" required />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="code">کد حساب<small
                                                        style="color: red">*</small></label>
                                                <input class="form-control" id="code" placeholder="کد حساب"
                                                    type="text" name="code" required />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="description">توضیحات حساب</label>
                                                <textarea class="form-control" id="description" placeholder="توضیح" name="description"></textarea>
                                            </div>
                                            <div class="mb-3" id="field-level">
                                                <label class="form-label" for="level">سطح حساب</label>
                                                <select class="select2 form-select" id="level" name="level">
                                                    <option value="0">انتخاب کنید</option>
                                                    <option value="1">حساب کل</option>
                                                    <option value="2">حساب معین</option>
                                                    <option value="3">حساب تفصیلی</option>
                                                </select>
                                            </div>
                                            <div class="mb-3 parent_box d-none">
                                                <label class="form-label" for="parent_id">حساب والد:</label>
                                                <select class="select2 form-select" id="parent_id" name="parent_id">
                                                    <option value="0">انتخاب کنید</option>
                                                    @foreach ($Accounts as $acc)
                                                        <option value="{{ $acc->id }}"
                                                            data-level="{{ $acc->level }}">
                                                            @if ($acc->level == 1)
                                                                حساب کل:
                                                            @elseif($acc->level == 2)
                                                                حساب معین:
                                                            @elseif($acc->level == 3)
                                                                حساب تفصیلی:
                                                            @endif
                                                            {{ $acc->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3" id="field-category">
                                                <label class="form-label" for="account_category">طبقه حساب</label>
                                                <select class="select2 form-select" id="account_category"
                                                    name="account_category">
                                                    <option value="">انتخاب کنید</option>
                                                    <option value="asset">دارایی</option>
                                                    <option value="liability">بدهی</option>
                                                    <option value="equity">حقوق مالکانه</option>
                                                    <option value="income">درآمد</option>
                                                    <option value="expense">هزینه</option>
                                                    <option value="cost_of_goods">بهای تمام شده</option>
                                                    <option value="memo">انتظامی / آماری</option>
                                                </select>
                                            </div>
                                            <div class="mb-3 d-none" id="field-asset-class">
                                                <label class="form-label" for="asset_class">طبقه فرعی (جاری /
                                                    غیرجاری)</label>
                                                <select class="select2 form-select" id="asset_class" name="asset_class">
                                                    <option value="">انتخاب کنید</option>
                                                </select>
                                                <small class="text-muted">جاری = کمتر از یک سال؛ غیرجاری = بلندمدت (بیش از
                                                    یک سال).</small>
                                            </div>
                                            <div class="mb-3 d-none" id="field-standard-account">
                                                <label class="form-label" for="asset_type">حساب کل استاندارد
                                                    (راهنما)</label>
                                                <select class="select2 form-select" id="asset_type" name="asset_type">
                                                    <option value="">انتخاب کنید</option>
                                                </select>
                                                <small class="text-muted d-block mt-1" id="standard-account-desc">با
                                                    انتخاب یک حساب کل استاندارد، نام آن به‌صورت خودکار پر می‌شود.</small>
                                            </div>
                                            <div class="mb-3" id="field-type">
                                                <label class="form-label" for="type">نوع حساب</label>
                                                <select class="select2 form-select" id="type" name="type">
                                                    <option value="0">انتخاب کنید</option>
                                                    <option value="1">بانک</option>
                                                    <option value="2">صندوق / وجه نقد</option>
                                                    <option value="3">حساب‌های دریافتنی (مطالبات)</option>
                                                    <option value="4">حساب‌های پرداختنی (بدهی‌ها)</option>
                                                    <option value="5">درآمد / فروش</option>
                                                    <option value="6">هزینه</option>
                                                    <option value="7">حقوق صاحبان سهام / سرمایه</option>
                                                    <option value="8">دارایی</option>
                                                    <option value="9">بدهی / تعهدات</option>
                                                </select>
                                                <small class="text-muted d-block mt-1">این فیلد فقط رفتار حساب را مشخص می‌کند (مثلاً «بانک» فیلدهای بانکی را فعال می‌کند) و جایگزین «طبقه حساب» نیست.</small>
                                            </div>
                                            <div class="mb-3" id="field-detail">
                                                <label class="form-label" for="detail_type">نوع تفصیل</label>
                                                <select class="select2 form-select" id="detail_type"
                                                    name="detail_type">
                                                    <option value="">بدون تفصیل مشخص</option>
                                                    <option value="customer">مشتری</option>
                                                    <option value="supplier">تامین کننده</option>
                                                    <option value="employee">پرسنل</option>
                                                    <option value="cost_center">مرکز هزینه</option>
                                                    <option value="store">انبار / شعبه</option>
                                                    <option value="project">پروژه</option>
                                                    <option value="asset">دارایی ثابت</option>
                                                </select>
                                            </div>
                                            <div class="mb-3" id="account-flags">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="is_control"
                                                        value="1" id="is_control">
                                                    <label class="form-check-label" for="is_control">حساب کنترلی
                                                        است</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="cost_center_required" value="1"
                                                        id="cost_center_required">
                                                    <label class="form-check-label" for="cost_center_required">ثبت
                                                        مرکز هزینه برای این حساب الزامی است</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="floating_detail_required" value="1"
                                                        id="floating_detail_required">
                                                    <label class="form-check-label"
                                                        for="floating_detail_required">تفصیل شناور الزامی است</label>
                                                </div>
                                            </div>
                                            <div class="mb-3" id="field-nature">
                                                <label class="form-label" for="nature">ماهیت حساب</label>
                                                <select class="select2 form-select" id="nature" name="nature">
                                                    <option value="0">خنثی</option>
                                                    <option value="1">بدهکار</option>
                                                    <option value="2">بستانکار</option>
                                                </select>
                                            </div>
                                            <div class="mb-3" id="field-opening-balance">
                                                <label class="form-label" for="opening_balance">مانده افتتاحیه</label>
                                                <input class="form-control" id="opening_balance"
                                                    placeholder="مبلغ مانده اول دوره این حساب — برای سند افتتاحیه استفاده می‌شود"
                                                    type="number" step="any" name="opening_balance" />
                                                <small class="text-muted d-block mt-1">مبلغ مانده اول دوره این حساب — برای سند افتتاحیه استفاده می‌شود.</small>
                                            </div>
                                            <div class="mb-3 con_bank d-none">
                                                <label class="form-label" for="account_number">شماره حساب</label>
                                                <input class="form-control" id="account_number" placeholder="کد حساب"
                                                    type="text" name="account_number" />
                                            </div>
                                            <div class="mb-3 con_bank d-none">
                                                <label class="form-label" for="card_number">شماره کارت</label>
                                                <input class="form-control" id="card_number" placeholder="شماره کارت"
                                                    type="text" name="card_number" />
                                            </div>
                                            <div class="mb-3 con_bank d-none">
                                                <label class="form-label" for="iban">شماره شبا</label>
                                                <input class="form-control" id="iban" placeholder="شماره شبا"
                                                    type="text" name="iban" />

                                            </div>
                                            <div class="mb-3 con_bank d-none">
                                                <label class="form-label" for="branch">نام شعبه</label>
                                                <input class="form-control" id="branch" placeholder="نام شعبه"
                                                    type="text" name="branch" />

                                            </div>
                                            <button class="btn btn-primary" type="submit">ایجاد حساب</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-8 order-1 order-lg-2 mb-4 mb-lg-0">

                                <div class="card" id="accounts-datatable-card">
                                    <div class="card-datatable table-responsive py-0">
                                        <table class="datatables-direct-basic table">
                                            <thead>
                                                <tr class="text-center">
                                                    <th>ردیف</th>
                                                    <th>کد حساب</th>
                                                    <th>نام حساب</th>
                                                    <th>سطح</th>
                                                    <th>نوع</th>
                                                    <th>وضعیت</th>
                                                    <th>عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php($x = 1)
                                                @foreach ($MainAccounts as $account)
                                                    <tr>
                                                        <th>{{ $x }}</th>
                                                        <td><a
                                                                href="{{ route('Account.edit', $account->id) }}">{{ $account->code }}</a>
                                                        </td>
                                                        <td><a
                                                                href="{{ route('Account.edit', $account->id) }}">{{ $account->name }}</a>
                                                        </td>
                                                        <td>
                                                            @if ($account->level == 1)
                                                                حساب کل
                                                            @elseif($account->level == 2)
                                                                حساب معین
                                                            @elseif($account->level == 3)
                                                                حساب تفصیلی
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($account->type == 1)
                                                                بانک
                                                            @elseif($account->type == 2)
                                                                صندوق / وجه نقد
                                                            @elseif($account->type == 3)
                                                                حساب‌های دریافتنی (مطالبات)
                                                            @elseif($account->type == 4)
                                                                حساب‌های پرداختنی (بدهی‌ها)
                                                            @elseif($account->type == 5)
                                                                درآمد / فروش
                                                            @elseif($account->type == 6)
                                                                هزینه
                                                            @elseif($account->type == 7)
                                                                حقوق صاحبان سهام / سرمایه
                                                            @elseif($account->type == 8)
                                                                دارایی
                                                            @elseif($account->type == 9)
                                                                بدهی / تعهدات
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($account->isActive == 1)
                                                                <span class="badge bg-label-success me-1">فعال</span>
                                                            @else
                                                                <span class="badge bg-label-danger me-1">غیرفعال</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $account->isActive }}</td>
                                                    </tr>
                                                    @php($x++)

                                                    <?php $ChildAccounts = DB::table('accounts')->where('parent_id', $account->id)->get(); ?>
                                                    @if (count($ChildAccounts) > 0)
                                                        @foreach ($ChildAccounts as $account)
                                                            <tr>
                                                                <th>{{ $x }}</th>
                                                                <td><a
                                                                        href="{{ route('Account.edit', $account->id) }}">{{ $account->code }}</a>
                                                                </td>
                                                                <td><a
                                                                        href="{{ route('Account.edit', $account->id) }}">
                                                                        --{{ $account->name }}</a></td>
                                                                <td>
                                                                    @if ($account->level == 1)
                                                                        حساب کل
                                                                    @elseif($account->level == 2)
                                                                        حساب معین
                                                                    @elseif($account->level == 3)
                                                                        حساب تفصیلی
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($account->type == 1)
                                                                        بانک
                                                                    @elseif($account->type == 2)
                                                                        صندوق / وجه نقد
                                                                    @elseif($account->type == 3)
                                                                        حساب‌های دریافتنی (مطالبات)
                                                                    @elseif($account->type == 4)
                                                                        حساب‌های پرداختنی (بدهی‌ها)
                                                                    @elseif($account->type == 5)
                                                                        درآمد / فروش
                                                                    @elseif($account->type == 6)
                                                                        هزینه
                                                                    @elseif($account->type == 7)
                                                                        حقوق صاحبان سهام / سرمایه
                                                                    @elseif($account->type == 8)
                                                                        دارایی
                                                                    @elseif($account->type == 9)
                                                                        بدهی / تعهدات
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($account->isActive == 1)
                                                                        <span
                                                                            class="badge bg-label-success me-1">فعال</span>
                                                                    @else
                                                                        <span
                                                                            class="badge bg-label-danger me-1">غیرفعال</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $account->isActive }}</td>
                                                            </tr>
                                                            @php($x++)

                                                            <?php $TafsiliAccounts = DB::table('accounts')->where('parent_id', $account->id)->get(); ?>
                                                            @if (count($TafsiliAccounts) > 0)
                                                                @foreach ($TafsiliAccounts as $account)
                                                                    <tr>
                                                                        <th>{{ $x }}</th>
                                                                        <td><a
                                                                                href="{{ route('Account.edit', $account->id) }}">{{ $account->code }}</a>
                                                                        </td>
                                                                        <td><a
                                                                                href="{{ route('Account.edit', $account->id) }}">
                                                                                ---> {{ $account->name }}</a></td>
                                                                        <td>
                                                                            @if ($account->level == 1)
                                                                                حساب کل
                                                                            @elseif($account->level == 2)
                                                                                حساب معین
                                                                            @elseif($account->level == 3)
                                                                                حساب تفصیلی
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            @if ($account->type == 1)
                                                                                بانک
                                                                            @elseif($account->type == 2)
                                                                                صندوق / وجه نقد
                                                                            @elseif($account->type == 3)
                                                                                حساب‌های دریافتنی (مطالبات)
                                                                            @elseif($account->type == 4)
                                                                                حساب‌های پرداختنی (بدهی‌ها)
                                                                            @elseif($account->type == 5)
                                                                                درآمد / فروش
                                                                            @elseif($account->type == 6)
                                                                                هزینه
                                                                            @elseif($account->type == 7)
                                                                                حقوق صاحبان سهام / سرمایه
                                                                            @elseif($account->type == 8)
                                                                                دارایی
                                                                            @elseif($account->type == 9)
                                                                                بدهی / تعهدات
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            @if ($account->isActive == 1)
                                                                                <span
                                                                                    class="badge bg-label-success me-1">فعال</span>
                                                                            @else
                                                                                <span
                                                                                    class="badge bg-label-danger me-1">غیرفعال</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ $account->isActive }}</td>
                                                                    </tr>
                                                                    @php($x++)
                                                                @endforeach
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Sticky Actions -->

                        <!-- ============ الگوی آمادهٔ سرفصل‌ها (مدل سپیدار) ============ -->
                        <div class="row mt-2" id="standard-chart-template">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-label-success d-flex flex-wrap align-items-center gap-2">
                                        <h5 class="mb-0 fw-bold d-flex align-items-center gap-1">
                                            <x-ui.icon name="book" />
                                            الگوی آمادهٔ سرفصل‌ها (نمودار درختی استاندارد حسابداری)
                                        </h5>
                                        <span class="badge bg-success ms-auto">ساخت با یک کلیک</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info d-flex flex-column flex-md-row gap-2 align-items-md-center mt-3">
                                            <div class="flex-grow-1 small mb-0">
                                                به‌جای ساخت دستی تک‌تک حساب‌ها، می‌توانید کل ساختار استاندارد حسابداری (گروه ← حساب کل ← حساب معین) را با عناوین رایج، با یک کلیک بسازید. حساب‌های تکراری (با همان کد) دوباره ساخته نمی‌شوند.
                                            </div>
                                            <form id="importStandardForm" action="{{ route('Account.importStandard') }}" method="POST">
                                                @csrf
                                                <button type="submit" id="importStandardBtn" class="btn btn-success">
                                                    <x-ui.icon name="plus" class="me-1" />
                                                    ایجاد خودکار همهٔ سرفصل‌ها
                                                </button>
                                            </form>
                                        </div>

                                        @php($sepidarGroups = config('sepidar_chart_of_accounts.groups', []))
                                        <div class="accordion" id="sepidarTreeAccordion">
                                            @foreach ($sepidarGroups as $gi => $group)
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header">
                                                        <button class="accordion-button collapsed" type="button"
                                                            data-bs-toggle="collapse"
                                                            data-bs-target="#sepidar-group-{{ $gi }}">
                                                            <span class="badge bg-label-primary me-2">{{ $group['code'] }}</span>
                                                            {{ $group['title'] }}
                                                            <span class="badge bg-label-secondary ms-2">{{ count($group['totals'] ?? []) }} حساب کل</span>
                                                        </button>
                                                    </h2>
                                                    <div id="sepidar-group-{{ $gi }}"
                                                        class="accordion-collapse collapse"
                                                        data-bs-parent="#sepidarTreeAccordion">
                                                        <div class="accordion-body">
                                                            @foreach ($group['totals'] as $total)
                                                                <div class="mb-3">
                                                                    <div class="fw-bold">
                                                                        <span class="badge bg-label-info">{{ $total['code'] }}</span>
                                                                        {{ $total['title'] }}
                                                                        <span class="badge bg-label-dark">حساب کل</span>
                                                                    </div>
                                                                    <ul class="list-unstyled mt-2 mb-0" style="padding-inline-start:1.75rem">
                                                                        @foreach ($total['subsidiaries'] ?? [] as $sub)
                                                                            <li class="mb-1 text-muted small">
                                                                                <span class="badge bg-label-secondary">{{ $sub['code'] }}</span>
                                                                                {{ $sub['title'] }}
                                                                                <span class="text-muted">— معین</span>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============ پایان الگوی آماده ============ -->

                        <!-- ============ سکشن آموزشی جامع حساب‌ها ============ -->
                        <div class="row mt-2" id="account-guide">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-label-primary d-flex flex-wrap align-items-center gap-2">
                                        <h5 class="mb-0 fw-bold d-flex align-items-center gap-1">
                                            <x-ui.icon name="book" />
                                            راهنمای جامع ساخت حساب — همه‌چیز را اینجا یاد بگیرید
                                        </h5>
                                        <span class="badge bg-primary ms-auto">آموزش گام‌به‌گام</span>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-4">
                                            اگر با مفاهیم حسابداری آشنا نیستید نگران نباشید؛ در این بخش تک‌به‌تک گزینه‌های فرم ساخت حساب را
                                            با زبان ساده و مثال توضیح داده‌ایم. ابتدا «سطح حساب»، سپس «نوع»، «طبقه» و «تفصیل» را بخوانید.
                                        </p>

                                        {{-- مفهوم درختی کل / معین / تفصیلی --}}
                                        <div class="alert alert-info d-flex flex-column flex-md-row gap-3 align-items-md-center mb-4">
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-2">ساختار درختی حساب‌ها چگونه است؟</h6>
                                                <p class="mb-0 small">
                                                    حساب‌ها مثل یک درخت سه‌طبقه‌اند: <strong>حساب کل</strong> بالاترین سطح و کلی‌ترین است،
                                                    زیر آن <strong>حساب معین</strong> قرار می‌گیرد و ریزترین سطح <strong>حساب تفصیلی</strong> است.
                                                    یعنی هر معین زیرمجموعهٔ یک کل، و هر تفصیلی زیرمجموعهٔ یک معین است.
                                                </p>
                                            </div>
                                            <div class="bg-white rounded p-3 border" style="min-width:240px">
                                                <div class="fw-bold text-primary">📁 دارایی‌های جاری <span class="badge bg-label-primary">کل</span></div>
                                                <div class="ms-3 mt-1">↳ 📂 موجودی نقد و بانک <span class="badge bg-label-info">معین</span></div>
                                                <div class="ms-5 mt-1">↳ 📄 بانک ملت - شعبه مرکزی <span class="badge bg-label-secondary">تفصیلی</span></div>
                                                <div class="ms-5">↳ 📄 صندوق فروشگاه <span class="badge bg-label-secondary">تفصیلی</span></div>
                                            </div>
                                        </div>

                                        <div class="accordion" id="accountGuideAccordion">

                                            {{-- ۱) سطح حساب --}}
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                                        data-bs-target="#guide-level" aria-expanded="true">
                                                        ۱) سطح حساب — کل، معین، تفصیلی یعنی چه؟
                                                    </button>
                                                </h2>
                                                <div id="guide-level" class="accordion-collapse collapse show"
                                                    data-bs-parent="#accountGuideAccordion">
                                                    <div class="accordion-body">
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered align-middle mb-3">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th style="width:140px">گزینه</th>
                                                                        <th>توضیح</th>
                                                                        <th style="width:260px">مثال</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td><span class="badge bg-label-primary">حساب کل</span></td>
                                                                        <td>سرفصل اصلی و کلی. خودش مستقیماً در سند استفاده نمی‌شود، بلکه چتر بالای معین‌هاست. در گزارش‌های کلان دیده می‌شود.</td>
                                                                        <td>دارایی‌های جاری، بدهی‌های جاری، درآمد فروش، هزینه‌های اداری</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><span class="badge bg-label-info">حساب معین</span></td>
                                                                        <td>زیرمجموعهٔ یک حساب کل و دقیق‌تر از آن. بیشتر ثبت‌ها روی همین سطح انجام می‌شود.</td>
                                                                        <td>موجودی بانک‌ها، حساب‌های دریافتنی تجاری، هزینه حقوق</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><span class="badge bg-label-secondary">حساب تفصیلی</span></td>
                                                                        <td>ریزترین سطح؛ یک شخص یا مورد مشخص را نشان می‌دهد. زیرمجموعهٔ یک معین است.</td>
                                                                        <td>«بانک ملت شعبه مرکزی»، «آقای رضایی (مشتری)»، «تأمین‌کننده الف»</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                        <div class="alert alert-warning mb-0 small">
                                            <strong>نکته:</strong>
                                            وقتی سطح را «معین» یا «تفصیلی» انتخاب کنید، فیلد <strong>حساب والد</strong> ظاهر می‌شود تا مشخص کنید این حساب زیرمجموعهٔ کدام حساب بالاتر است (معین زیرِ کل، تفصیلی زیرِ معین).
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ۲) نوع حساب --}}
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#guide-type">
                                                        ۲) نوع حساب — هر کدام برای چیست؟
                                                    </button>
                                                </h2>
                                                <div id="guide-type" class="accordion-collapse collapse"
                                                    data-bs-parent="#accountGuideAccordion">
                                                    <div class="accordion-body">
                                                        <p class="small text-muted">«نوع حساب» رفتار حساب را مشخص می‌کند؛ مثلاً اگر «بانک» را انتخاب کنید فیلدهای شماره حساب، کارت و شبا ظاهر می‌شوند.</p>
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered align-middle mb-0">
                                                                <thead class="table-light">
                                                                    <tr><th style="width:220px">نوع</th><th>کاربرد</th></tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr><td><strong>بانک</strong></td><td>حساب‌های بانکی شرکت. با انتخاب آن، شماره حساب/کارت/شبا/شعبه فعال می‌شود.</td></tr>
                                                                    <tr><td><strong>صندوق / وجه نقد</strong></td><td>پول نقد در صندوق فروشگاه یا تنخواه.</td></tr>
                                                                    <tr><td><strong>حساب‌های دریافتنی (مطالبات)</strong></td><td>طلب شما از مشتریان؛ کسانی که به شما بدهکارند.</td></tr>
                                                                    <tr><td><strong>حساب‌های پرداختنی (بدهی‌ها)</strong></td><td>بدهی شما به تأمین‌کنندگان؛ کسانی که به آن‌ها بدهکارید.</td></tr>
                                                                    <tr><td><strong>درآمد / فروش</strong></td><td>درآمد حاصل از فروش کالا و خدمات.</td></tr>
                                                                    <tr><td><strong>هزینه</strong></td><td>هزینه‌های جاری مثل اجاره، حقوق، حمل‌ونقل، آب و برق.</td></tr>
                                                                    <tr><td><strong>حقوق صاحبان سهام / سرمایه</strong></td><td>سرمایهٔ آورده‌شده توسط مالکان و سود انباشته.</td></tr>
                                                                    <tr><td><strong>دارایی</strong></td><td>اموال و دارایی‌ها مثل ملک، خودرو، تجهیزات، موجودی کالا.</td></tr>
                                                                    <tr><td><strong>بدهی / تعهدات</strong></td><td>وام‌ها و تعهدات بلندمدت یا کوتاه‌مدت شرکت.</td></tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ۳) طبقه حساب --}}
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#guide-category">
                                                        ۳) طبقه حساب — جایگاه در صورت‌های مالی
                                                    </button>
                                                </h2>
                                                <div id="guide-category" class="accordion-collapse collapse"
                                                    data-bs-parent="#accountGuideAccordion">
                                                    <div class="accordion-body">
                                                        <p class="small text-muted">«طبقه» تعیین می‌کند حساب در ترازنامه یا صورت سود و زیان کجا قرار بگیرد. این مورد برای گزارش‌گیری درست بسیار مهم است.</p>
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered align-middle mb-0">
                                                                <thead class="table-light">
                                                                    <tr><th style="width:200px">طبقه</th><th>توضیح</th><th style="width:120px">ماهیت معمول</th></tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr><td><strong>دارایی</strong></td><td>آنچه دارید: نقد، بانک، کالا، تجهیزات، مطالبات.</td><td>بدهکار</td></tr>
                                                                    <tr><td><strong>بدهی</strong></td><td>آنچه بدهکارید: وام، حساب‌های پرداختنی.</td><td>بستانکار</td></tr>
                                                                    <tr><td><strong>حقوق مالکانه</strong></td><td>سرمایه و سود انباشتهٔ مالکان.</td><td>بستانکار</td></tr>
                                                                    <tr><td><strong>درآمد</strong></td><td>درآمد فروش و سایر درآمدها (صورت سود و زیان).</td><td>بستانکار</td></tr>
                                                                    <tr><td><strong>هزینه</strong></td><td>هزینه‌های عملیاتی و اداری (صورت سود و زیان).</td><td>بدهکار</td></tr>
                                                                    <tr><td><strong>بهای تمام شده</strong></td><td>بهای تمام‌شدهٔ کالای فروش‌رفته.</td><td>بدهکار</td></tr>
                                                                    <tr><td><strong>انتظامی / آماری</strong></td><td>حساب‌های یادداشتی و آماری خارج از ترازنامه (مثل ضمانت‌نامه‌ها).</td><td>خنثی</td></tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ۴) کدینگ استاندارد کامل --}}
                                            <?php
                                                $sca = config('standard_chart_of_accounts');
                                                $natureBadge = [
                                                    'debit' => '<span class="badge bg-label-primary">بدهکار</span>',
                                                    'credit' => '<span class="badge bg-label-info">بستانکار</span>',
                                                ];
                                            ?>
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#guide-standard">
                                                        ۴) کدینگ استاندارد — همهٔ حساب‌های کل به تفکیک طبقه
                                                    </button>
                                                </h2>
                                                <div id="guide-standard" class="accordion-collapse collapse"
                                                    data-bs-parent="#accountGuideAccordion">
                                                    <div class="accordion-body">
                                                        <div class="alert alert-info small mb-3">
                                                            هنگام ساخت حساب، ابتدا <strong>طبقه حساب</strong> را انتخاب کنید؛ اگر «دارایی» یا «بدهی» باشد،
                                                            فیلد <strong>جاری/غیرجاری</strong> و سپس فهرست <strong>حساب کل استاندارد</strong> ظاهر می‌شود.
                                                            با انتخاب هر مورد، نام حساب خودکار پر می‌شود. در ادامه فهرست کامل آمده است:
                                                        </div>

                                                        @foreach (['asset' => 'دارایی', 'liability' => 'بدهی'] as $cat => $catLabel)
                                                            <h6 class="fw-bold text-primary mt-3">طبقه {{ $catLabel }}</h6>
                                                            @foreach ($sca['sub_classes'][$cat] as $scKey => $scLabel)
                                                                <div class="mb-2">
                                                                    <span class="badge bg-label-dark mb-2">{{ $scLabel }}</span>
                                                                    <div class="table-responsive">
                                                                        <table class="table table-sm table-bordered align-middle mb-0">
                                                                            <thead class="table-light">
                                                                                <tr><th style="width:240px">حساب کل</th><th>توضیح</th><th style="width:110px">ماهیت</th></tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                @foreach ($sca['standard_accounts'][$cat][$scKey] as $acc)
                                                                                    <tr>
                                                                                        <td class="fw-medium">{{ $acc['name'] }}</td>
                                                                                        <td class="small text-muted">{{ $acc['desc'] }}</td>
                                                                                        <td>{!! $natureBadge[$acc['nature']] ?? '' !!}</td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @endforeach

                                                        @foreach (['equity' => 'حقوق مالکانه', 'income' => 'درآمد', 'expense' => 'هزینه', 'cost_of_goods' => 'بهای تمام شده'] as $cat => $catLabel)
                                                            <h6 class="fw-bold text-primary mt-3">طبقه {{ $catLabel }}</h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered align-middle mb-0">
                                                                    <thead class="table-light">
                                                                        <tr><th style="width:240px">حساب کل</th><th>توضیح</th><th style="width:110px">ماهیت</th></tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($sca['standard_accounts'][$cat] as $acc)
                                                                            <tr>
                                                                                <td class="fw-medium">{{ $acc['name'] }}</td>
                                                                                <td class="small text-muted">{{ $acc['desc'] }}</td>
                                                                                <td>{!! $natureBadge[$acc['nature']] ?? '' !!}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ۵) نمونهٔ کامل ساخت چهار سطحی --}}
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#guide-howto">
                                                        ۵) چطور یک حساب را از طبقه تا تفصیلی بسازم؟ (نمونهٔ کامل)
                                                    </button>
                                                </h2>
                                                <div id="guide-howto" class="accordion-collapse collapse"
                                                    data-bs-parent="#accountGuideAccordion">
                                                    <div class="accordion-body">
                                                        <p class="small text-muted">ساختار همیشه چهار لایه دارد: طبقه ← حساب کل ← حساب معین ← حساب تفصیلی. مثال مالیات ارزش افزوده:</p>
                                                        <div class="d-flex flex-column gap-2 mb-3">
                                                            <div class="border rounded p-2"><span class="badge bg-label-dark">طبقه</span> بدهی جاری <span class="text-muted small">(طبقه حساب = بدهی، جاری)</span></div>
                                                            <div class="border rounded p-2 ms-3"><span class="badge bg-label-primary">حساب کل</span> مالیات پرداختنی <span class="text-muted small">(سطح = حساب کل)</span></div>
                                                            <div class="border rounded p-2 ms-5"><span class="badge bg-label-info">حساب معین</span> مالیات بر ارزش افزوده پرداختنی <span class="text-muted small">(سطح = معین، والد = مالیات پرداختنی)</span></div>
                                                            <div class="border rounded p-2" style="margin-right:5.5rem"><span class="badge bg-label-secondary">حساب تفصیلی</span> ادارهٔ امور مالیاتی <span class="text-muted small">(سطح = تفصیلی، والد = مالیات بر ارزش افزوده پرداختنی)</span></div>
                                                        </div>
                                                        <h6 class="fw-bold">گام‌به‌گام ثبت:</h6>
                                                        <ol class="small mb-0">
                                                            <li class="mb-1">ابتدا <strong>حساب کل</strong> را بسازید: نام «مالیات پرداختنی»، سطح = حساب کل، طبقه = بدهی، جاری، سپس حساب کل استاندارد «مالیات پرداختنی».</li>
                                                            <li class="mb-1">سپس <strong>حساب معین</strong>: نام «مالیات بر ارزش افزوده پرداختنی»، سطح = معین، در فیلد حساب والد «مالیات پرداختنی» را انتخاب کنید.</li>
                                                            <li class="mb-1">در پایان <strong>حساب تفصیلی</strong>: نام «ادارهٔ امور مالیاتی»، سطح = تفصیلی، حساب والد = «مالیات بر ارزش افزوده پرداختنی». می‌توانید نوع تفصیل را هم تعیین کنید.</li>
                                                        </ol>
                                                        <div class="alert alert-success mt-3 mb-0 small">
                                                            همین روش برای همهٔ حساب‌ها صادق است؛ مثلاً سرمایه (حقوق مالکانه)، درآمد فروش کالا (درآمد) یا هزینهٔ اجاره (هزینه).
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ۶) نوع تفصیل --}}
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#guide-detail">
                                                        ۶) نوع تفصیل — این حساب به چه چیزی وصل می‌شود؟
                                                    </button>
                                                </h2>
                                                <div id="guide-detail" class="accordion-collapse collapse"
                                                    data-bs-parent="#accountGuideAccordion">
                                                    <div class="accordion-body">
                                                        <p class="small text-muted">اگر این حساب باید به یک موجودیت مشخص (مثل مشتری یا انبار) متصل شود، نوع تفصیل را انتخاب کنید. در غیر این صورت «بدون تفصیل مشخص» را بگذارید.</p>
                                                        <div class="table-responsive">
                                                            <table class="table table-bordered align-middle mb-0">
                                                                <thead class="table-light">
                                                                    <tr><th style="width:180px">نوع تفصیل</th><th>کاربرد</th></tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr><td><strong>بدون تفصیل مشخص</strong></td><td>حساب به موجودیت خاصی وصل نیست (پیش‌فرض).</td></tr>
                                                                    <tr><td><strong>مشتری</strong></td><td>حساب به پروندهٔ مشتریان متصل می‌شود؛ مناسب مطالبات.</td></tr>
                                                                    <tr><td><strong>تأمین کننده</strong></td><td>حساب به تأمین‌کنندگان متصل می‌شود؛ مناسب حساب‌های پرداختنی.</td></tr>
                                                                    <tr><td><strong>پرسنل</strong></td><td>برای حقوق و مطالبات کارکنان.</td></tr>
                                                                    <tr><td><strong>مرکز هزینه</strong></td><td>تفکیک هزینه بر اساس دپارتمان/واحد.</td></tr>
                                                                    <tr><td><strong>انبار / شعبه</strong></td><td>اتصال حساب به یک انبار یا شعبهٔ مشخص.</td></tr>
                                                                    <tr><td><strong>پروژه</strong></td><td>پیگیری درآمد و هزینهٔ یک پروژه.</td></tr>
                                                                    <tr><td><strong>دارایی ثابت</strong></td><td>اتصال به اموال و دارایی‌های ثابت.</td></tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ۵) چک‌باکس‌ها و ماهیت --}}
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#guide-flags">
                                                        ۷) چک‌باکس‌ها و ماهیت حساب
                                                    </button>
                                                </h2>
                                                <div id="guide-flags" class="accordion-collapse collapse"
                                                    data-bs-parent="#accountGuideAccordion">
                                                    <div class="accordion-body">
                                                        <ul class="mb-3">
                                                            <li class="mb-2"><strong>حساب کنترلی است:</strong> یعنی مانده این حساب فقط از طریق تفصیلی‌های زیرمجموعه به‌دست می‌آید و نمی‌توان مستقیماً روی خودش سند زد (مثل مطالبات که از جمع مشتریان حاصل می‌شود).</li>
                                                            <li class="mb-2"><strong>ثبت مرکز هزینه الزامی است:</strong> هنگام استفاده از این حساب در سند، کاربر مجبور است یک مرکز هزینه انتخاب کند (برای هزینه‌ها مفید است).</li>
                                                            <li class="mb-2"><strong>تفصیل شناور الزامی است:</strong> هنگام ثبت سند باید حتماً یک تفصیل (مثل مشتری/تأمین‌کننده) انتخاب شود تا مانده بدون طرف‌حساب نماند.</li>
                                                        </ul>
                                                        <h6 class="fw-bold">ماهیت حساب</h6>
                                                        <p class="small mb-0">
                                                            ماهیت یعنی مانده طبیعی حساب: <span class="badge bg-label-primary">بدهکار</span> برای دارایی‌ها و هزینه‌ها،
                                                            <span class="badge bg-label-info">بستانکار</span> برای بدهی‌ها، سرمایه و درآمد، و
                                                            <span class="badge bg-label-secondary">خنثی</span> وقتی حساب می‌تواند هر دو حالت را بگیرد.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- ۶) سناریوهای عملی --}}
                                            <div class="accordion-item">
                                                <h2 class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#guide-examples">
                                                        ۸) می‌خواهم بسازم… کدام گزینه‌ها را انتخاب کنم؟
                                                    </button>
                                                </h2>
                                                <div id="guide-examples" class="accordion-collapse collapse"
                                                    data-bs-parent="#accountGuideAccordion">
                                                    <div class="accordion-body">
                                                        <div class="row g-3">
                                                            <div class="col-12 col-lg-6">
                                                                <div class="border rounded p-3 h-100">
                                                                    <h6 class="fw-bold text-primary">🏦 ساخت حساب بانکی (مثلاً بانک ملت)</h6>
                                                                    <ul class="small mb-0">
                                                                        <li>سطح: <strong>تفصیلی</strong> (زیر معین «موجودی بانک‌ها»)</li>
                                                                        <li>نوع: <strong>بانک</strong> → فیلد شماره حساب/شبا باز می‌شود</li>
                                                                        <li>طبقه: <strong>دارایی</strong></li>
                                                                        <li>ماهیت: <strong>بدهکار</strong></li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-lg-6">
                                                                <div class="border rounded p-3 h-100">
                                                                    <h6 class="fw-bold text-primary">👤 حساب مشتریان (مطالبات)</h6>
                                                                    <ul class="small mb-0">
                                                                        <li>سطح: <strong>معین</strong> (زیر کل «دارایی‌های جاری»)</li>
                                                                        <li>نوع: <strong>حساب‌های دریافتنی</strong></li>
                                                                        <li>طبقه: <strong>دارایی</strong></li>
                                                                        <li>نوع تفصیل: <strong>مشتری</strong> + تیک «تفصیل شناور الزامی»</li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-lg-6">
                                                                <div class="border rounded p-3 h-100">
                                                                    <h6 class="fw-bold text-primary">🛒 حساب فروش (درآمد)</h6>
                                                                    <ul class="small mb-0">
                                                                        <li>سطح: <strong>معین</strong> (زیر کل «درآمدها»)</li>
                                                                        <li>نوع: <strong>درآمد / فروش</strong></li>
                                                                        <li>طبقه: <strong>درآمد</strong></li>
                                                                        <li>ماهیت: <strong>بستانکار</strong></li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-lg-6">
                                                                <div class="border rounded p-3 h-100">
                                                                    <h6 class="fw-bold text-primary">💡 حساب هزینه (مثلاً اجاره)</h6>
                                                                    <ul class="small mb-0">
                                                                        <li>سطح: <strong>معین</strong> (زیر کل «هزینه‌ها»)</li>
                                                                        <li>نوع: <strong>هزینه</strong></li>
                                                                        <li>طبقه: <strong>هزینه</strong></li>
                                                                        <li>ماهیت: <strong>بدهکار</strong> + در صورت نیاز تیک «مرکز هزینه الزامی»</li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ============ پایان سکشن آموزشی ============ -->
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
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
    <script>
        // datatable (jquery)
        $('.basicdata').addClass('open')
        $('.basicdata .accounts').addClass('active open')
        $(function() {
            var
                dt_without_ajax_table = $('.datatables-direct-basic');

            // DataTable Direct
            // --------------------------------------------------------------------
            if (dt_without_ajax_table.length) {
                dt_without_ajax = dt_without_ajax_table.DataTable({
                    searching: true,
                    lengthChange: true,
                    ordering: false,
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, 'همه']
                    ],
                    language: {
                        search: 'جستجو: ',
                        searchPlaceholder: 'جستجو کنید...',
                        lengthMenu: 'نمایش _MENU_ ردیف',
                        info: 'نمایش _START_ تا _END_ از _TOTAL_ مورد',
                        infoEmpty: 'موردی برای نمایش نیست',
                        infoFiltered: '(فیلتر شده از _MAX_ مورد)',
                        zeroRecords: 'حسابی پیدا نشد',
                        paginate: {
                            previous: 'قبلی',
                            next: 'بعدی',
                        }
                    },
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
    <script>
        $(document).ready(function() {
            $('#addStore').on('submit', function(e) {
                e.preventDefault(); // جلوگیری از ارسال ابتدا، تا ولیدیشن اجرا شود
                let isValid = true;
                $('.error-message').remove();

                $(this).find('input[required], select[required]').each(function() {
                    let $field = $(this);
                    let value = $field.val();

                    // تبدیل آرایه به رشته اگر multiple باشد
                    if (Array.isArray(value)) {
                        value = value.length ? value.join(',') : '';
                    }

                    if ($.trim(value) === '') {
                        isValid = false;
                        let errorMsg = $(
                            '<div class="error-message" style="color:red;font-size:12px;margin-top:4px;">این فیلد الزامی است</div>'
                            );

                        if ($field.next('.select2').length) {
                            $field.next('.select2').after(errorMsg);
                        } else {
                            $field.after(errorMsg);
                        }
                    }
                });

                if (isValid) {
                    this.submit(); // اگر معتبر بود، فرم رو ارسال کن
                }
            });

            $('#type').on('change', function() {
                var type = $(this).find('option:selected').val();
                if (type == 1) {
                    $('.con_bank').removeClass('d-none');
                } else {
                    $('.con_bank').addClass('d-none');
                }
            });

            $('#level').on('change', function() {
                var type = $(this).find('option:selected').val();
                if (type > 1) {
                    $('.parent_box').removeClass('d-none');
                } else {
                    $('.parent_box').addClass('d-none');
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {

            // ذخیره کامل همه آپشن‌ها
            let allOptions = [];
            $('#parent_id option').each(function() {
                allOptions.push({
                    id: $(this).val(),
                    text: $(this).text(),
                    level: $(this).data('level') ?? null
                });
            });

            $('#level').select2();
            $('#parent_id').select2();

            $('#level').on('change', function() {

                let selectedLevel = parseInt($(this).val());
                let allowed = null;

                if (selectedLevel === 2) allowed = 1; // معین ← والد کل
                if (selectedLevel === 3) allowed = 2; // تفصیلی ← والد معین
                if (selectedLevel === 1 || selectedLevel === 0) allowed = -1;

                // Destroy قبل از ساخت لیست جدید
                $('#parent_id').select2('destroy');

                // ساخت HTML جدید بر اساس فیلتر درست
                let newHtml = '<option value="0">انتخاب کنید</option>';

                if (allowed !== -1) {
                    allOptions.forEach(opt => {
                        if (opt.id !== "0" && opt.level === allowed) {
                            newHtml +=
                                `<option value="${opt.id}" data-level="${opt.level}">${opt.text}</option>`;
                        }
                    });
                }

                $('#parent_id').html(newHtml);

                // Init جدید؛ حالا Select2 دقیقاً فقط همین گزینه‌ها رو می‌بینه
                $('#parent_id').select2();

            });

        });
    </script>

    <script>
        // ===== کدینگ استاندارد: طبقه → جاری/غیرجاری → حساب کل استاندارد =====
        $(document).ready(function() {
            const subClasses = @json(config('standard_chart_of_accounts.sub_classes', []));
            const standardAccounts = @json(config('standard_chart_of_accounts.standard_accounts', []));

            function rebuildSelect($el, html) {
                if ($el.data('select2')) {
                    $el.select2('destroy');
                }
                $el.html(html).select2();
            }

            function getStandardList(category, subClass) {
                const node = standardAccounts[category];
                if (!node) return [];
                if (Array.isArray(node)) return node; // طبقه‌های بدون زیرطبقه
                return node[subClass] || []; // دارایی/بدهی → بر اساس زیرطبقه
            }

            function refreshSubClass(selectedClass) {
                const category = $('#account_category').val();
                const classes = subClasses[category];

                if (!classes) {
                    $('#field-asset-class').addClass('d-none');
                    rebuildSelect($('#asset_class'), '<option value=""></option>');
                    return;
                }

                let html = '<option value="">انتخاب کنید</option>';
                Object.keys(classes).forEach(function(key) {
                    const sel = (selectedClass && selectedClass === key) ? ' selected' : '';
                    html += `<option value="${key}"${sel}>${classes[key]}</option>`;
                });
                rebuildSelect($('#asset_class'), html);
                $('#field-asset-class').removeClass('d-none');
            }

            function refreshStandardAccounts(selectedName) {
                const category = $('#account_category').val();
                const needsSub = !!subClasses[category];
                const subClass = $('#asset_class').val();

                // اگر طبقه نیاز به زیرطبقه دارد ولی هنوز انتخاب نشده، فیلد را پنهان کن
                if (!category || (needsSub && !subClass)) {
                    $('#field-standard-account').addClass('d-none');
                    rebuildSelect($('#asset_type'), '<option value=""></option>');
                    return;
                }

                const list = getStandardList(category, subClass);
                if (!list.length) {
                    $('#field-standard-account').addClass('d-none');
                    rebuildSelect($('#asset_type'), '<option value=""></option>');
                    return;
                }

                let html = '<option value="">انتخاب کنید</option>';
                list.forEach(function(item) {
                    const sel = (selectedName && selectedName === item.name) ? ' selected' : '';
                    html += `<option value="${item.name}" data-desc="${item.desc || ''}"${sel}>${item.name}</option>`;
                });
                rebuildSelect($('#asset_type'), html);
                $('#field-standard-account').removeClass('d-none');
                updateStandardDesc();
            }

            function updateStandardDesc() {
                const desc = $('#asset_type').find('option:selected').data('desc') || '';
                $('#standard-account-desc').text(desc ||
                    'با انتخاب یک حساب کل استاندارد، نام آن به‌صورت خودکار پر می‌شود.');
            }

            $('#account_category').on('change', function() {
                refreshSubClass();
                refreshStandardAccounts();
            });
            $('#asset_class').on('change', function() {
                refreshStandardAccounts();
            });
            $('#asset_type').on('change', function() {
                updateStandardDesc();
                const name = $(this).val();
                // اگر نام حساب خالی است، با نام استاندارد انتخاب‌شده پر شود
                if (name && !$('#name').val().trim()) {
                    $('#name').val(name);
                }
            });

            // مقداردهی اولیه (حالت ویرایش)
            const initialClass = $('#asset_class').data('selected') || '';
            const initialType = $('#asset_type').data('selected') || '';
            refreshSubClass(initialClass);
            refreshStandardAccounts(initialType);
        });
    </script>

    <script>
        $(document).ready(function() {
            var importBtn = document.getElementById('importStandardBtn');
            var importForm = document.getElementById('importStandardForm');

            if (importBtn && importForm) {
                importBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'question',
                        title: 'ایجاد سرفصل‌های استاندارد',
                        text: 'کل سرفصل‌های استاندارد برای این پنل ساخته می‌شوند. حساب‌های موجود با همان کد دست‌نخورده می‌مانند.',
                        showCancelButton: true,
                        confirmButtonText: 'بله، ساخته شود',
                        cancelButtonText: 'انصراف',
                        confirmButtonColor: '#28a745',
                        reverseButtons: true,
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            importForm.submit();
                        }
                    });
                });
            }
        });
    </script>

</body>

</html>
