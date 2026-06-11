<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>هزینه ها و مراکز هزینه - دکان دارمینو</title>
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
    @include('sweetalert::alert')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections/sidebar')
            <div class="layout-page">
                @include('sections/header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> هزینه ها و
                                مراکز هزینه</h4>
                            <div class="d-flex gap-2">
                                <a class="btn btn-outline-secondary" href="{{ route('Accounting.vouchers') }}">
                                    <i class="ti ti-file-invoice me-1"></i> اسناد حسابداری
                                </a>
                                <a class="btn btn-outline-secondary" href="{{ route('Accounting.treasury') }}">
                                    <i class="ti ti-cash me-1"></i> خزانه
                                </a>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">تعداد هزینه</small>
                                        <h4 class="mb-0 text-end">{{ number_format($totals['count']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">مبلغ پایه</small>
                                        <h4 class="mb-0 text-end">{{ number_format($totals['amount']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">مالیات/عوارض</small>
                                        <h4 class="mb-0 text-end">{{ number_format($totals['tax']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">جمع هزینه</small>
                                        <h4 class="mb-0 text-end">{{ number_format($totals['total']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-4">
                                <form class="card h-100" method="POST"
                                    action="{{ route('Accounting.expenses.costCenters.store') }}">
                                    @csrf
                                    <div class="card-header">
                                        <h5 class="mb-0">تعریف مرکز هزینه</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-12 col-md-5">
                                            <label class="form-label">کد</label>
                                            <input type="text" name="code" class="form-control"
                                                value="{{ old('code') }}">
                                        </div>
                                        <div class="col-12 col-md-7">
                                            <label class="form-label">نام مرکز هزینه</label>
                                            <input type="text" name="name" class="form-control"
                                                value="{{ old('name') }}" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">نوع مرکز</label>
                                            <select name="center_type" class="form-select">
                                                <option value="branch">شعبه / واحد</option>
                                                <option value="warehouse">انبار</option>
                                                <option value="department">دپارتمان</option>
                                                <option value="production_line">خط تولید</option>
                                                <option value="route">مسیر پخش</option>
                                                <option value="project">پروژه</option>
                                                <option value="other">سایر</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">انبار/شعبه مرتبط</label>
                                            <select name="store_id" class="form-select select2-basic">
                                                <option value="">بدون اتصال</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}">{{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">مبنای تخصیص</label>
                                            <select name="allocation_basis" class="form-select">
                                                <option value="manual">دستی</option>
                                                <option value="amount">مبلغ</option>
                                                <option value="quantity">تعداد</option>
                                                <option value="weight">وزن</option>
                                                <option value="time">زمان کارکرد</option>
                                                <option value="equal">مساوی</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">شرح</label>
                                            <input type="text" name="description" class="form-control"
                                                value="{{ old('description') }}">
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button class="btn btn-primary" type="submit">ثبت مرکز هزینه</button>
                                    </div>
                                </form>
                            </div>

                            <div class="col-12 col-xl-4">
                                <form class="card h-100" method="POST"
                                    action="{{ route('Accounting.expenses.types.store') }}">
                                    @csrf
                                    <div class="card-header">
                                        <h5 class="mb-0">تعریف نوع هزینه</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-12 col-md-5">
                                            <label class="form-label">کد</label>
                                            <input type="text" name="code" class="form-control">
                                        </div>
                                        <div class="col-12 col-md-7">
                                            <label class="form-label">عنوان هزینه</label>
                                            <input type="text" name="name" class="form-control" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">گروه</label>
                                            <select name="expense_group" class="form-select">
                                                <option value="operational">عملیاتی</option>
                                                <option value="production">تولید</option>
                                                <option value="purchase">خرید و تامین</option>
                                                <option value="distribution">پخش و توزیع</option>
                                                <option value="administrative">اداری و عمومی</option>
                                                <option value="financial">مالی</option>
                                                <option value="import">واردات</option>
                                                <option value="asset">دارایی ثابت</option>
                                                <option value="other">سایر</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">رفتار هزینه</label>
                                            <select name="cost_behavior" class="form-select">
                                                <option value="direct">مستقیم</option>
                                                <option value="indirect">غیرمستقیم</option>
                                                <option value="overhead">سربار</option>
                                                <option value="initial">هزینه اولیه</option>
                                                <option value="variable">متغیر</option>
                                                <option value="fixed">ثابت</option>
                                                <option value="mixed">مختلط</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">workflow اختصاصی</label>
                                            <select name="workflow_type" class="form-select">
                                                <option value="standard">استاندارد</option>
                                                @foreach ($specializedKinds as $kind => $label)
                                                    <option value="{{ $kind }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">سیاست جذب هزینه</label>
                                            <select name="capitalization_policy" class="form-select">
                                                <option value="expense">ثبت مستقیم هزینه</option>
                                                <option value="landed_cost">جذب به بهای تمام شده خرید</option>
                                                <option value="production_overhead">سربار تولید</option>
                                                <option value="asset_cost">بهای دارایی</option>
                                                <option value="commission_payable">کارمزد پرداختنی</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" value="1"
                                                    name="requires_approval" id="requires_approval" checked>
                                                <label class="form-check-label" for="requires_approval">نیازمند تایید
                                                    قبل از سند</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">حساب هزینه پیش فرض</label>
                                            <select name="account_id" class="form-select account-select">
                                                <option value="">حساب سیستمی هزینه استفاده شود</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->code }} -
                                                        {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">شرح</label>
                                            <input type="text" name="description" class="form-control">
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button class="btn btn-primary" type="submit">ثبت نوع هزینه</button>
                                    </div>
                                </form>
                            </div>

                            <div class="col-12 col-xl-4">
                                <form class="card h-100" method="POST"
                                    action="{{ route('Accounting.expenses.store') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="card-header">
                                        <h5 class="mb-0">ثبت هزینه عملیاتی</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">تاریخ</label>
                                            <input type="date" name="expense_date_en" class="form-control"
                                                value="{{ old('expense_date_en', $today) }}">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">شماره مرجع</label>
                                            <input type="text" name="reference_number" class="form-control"
                                                value="{{ old('reference_number') }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">مرکز هزینه</label>
                                            <select name="cost_center_id" class="form-select select2-basic" required>
                                                <option value="">انتخاب مرکز هزینه</option>
                                                @foreach ($costCenters as $costCenter)
                                                    <option value="{{ $costCenter->id }}">{{ $costCenter->code }} -
                                                        {{ $costCenter->name }}
                                                        {{ $costCenter->store ? '(' . $costCenter->store->title . ')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">نوع هزینه</label>
                                            <select name="expense_type_id" class="form-select select2-basic" required>
                                                <option value="">انتخاب نوع هزینه</option>
                                                @foreach ($expenseTypes as $expenseType)
                                                    <option value="{{ $expenseType->id }}">{{ $expenseType->code }} -
                                                        {{ $expenseType->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">حساب هزینه</label>
                                            <select name="expense_account_id" class="form-select account-select">
                                                <option value="">حساب نوع هزینه یا حساب سیستمی</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->code }} -
                                                        {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">حساب تسویه / پرداختنی</label>
                                            <select name="settlement_account_id" class="form-select account-select"
                                                required>
                                                <option value="">انتخاب حساب صندوق، بانک یا پرداختنی</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->code }} -
                                                        {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">مبلغ پایه</label>
                                            <input type="number" min="0.01" step="0.01" name="amount"
                                                class="form-control text-end" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">مالیات/عوارض</label>
                                            <input type="number" min="0" step="0.01" name="tax_amount"
                                                class="form-control text-end" value="0">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">هدف تخصیص</label>
                                            <select name="allocation_target_type" class="form-select">
                                                <option value="manual">دستی</option>
                                                <option value="product">کالا</option>
                                                <option value="order">سفارش/خرید</option>
                                                <option value="project">پروژه</option>
                                                <option value="contract">قرارداد</option>
                                                <option value="cost_center">مرکز هزینه</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">مبنای تخصیص</label>
                                            <select name="allocation_basis" class="form-select">
                                                <option value="direct">مستقیم</option>
                                                <option value="manual">دستی</option>
                                                <option value="amount">مبلغ</option>
                                                <option value="quantity">تعداد</option>
                                                <option value="weight">وزن</option>
                                                <option value="time">زمان</option>
                                                <option value="equal">مساوی</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">کالا / محصول مرتبط</label>
                                            @include('partials.forms.erp-product-filter-select', [
                                                'name' => 'product_id',
                                                'placeholder' => 'بدون اتصال کالا',
                                                'class' => 'form-select select2-basic erp-remote-select',
                                            ])
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">کد پروژه</label>
                                            <input type="text" name="project_code" class="form-control"
                                                value="{{ old('project_code') }}">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">شناسه سفارش/هدف</label>
                                            <input type="number" min="1" step="1"
                                                name="allocation_target_id" class="form-control text-end"
                                                value="{{ old('allocation_target_id') }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">یادداشت تخصیص</label>
                                            <input type="text" name="allocation_note" class="form-control"
                                                placeholder="مثلا جذب هزینه حمل به کالای A یا سفارش خرید 102">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">شرح</label>
                                            <input type="text" name="description" class="form-control"
                                                placeholder="مثلا هزینه حمل سفارش تامین کننده یا هزینه آب شعبه غرب">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">فایل مدرک</label>
                                            <input type="file" name="attachment_file" class="form-control"
                                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">یادداشت مدرک</label>
                                            <input type="text" name="attachment_note" class="form-control"
                                                placeholder="مثلا رسید پرداخت، قبض، قرارداد یا بارنامه">
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button class="btn btn-primary" type="submit">ثبت هزینه و سند</button>
                                    </div>
                                </form>
                            </div>

                            <div class="col-12 col-xl-8">
                                <form class="card h-100" method="POST"
                                    action="{{ route('Accounting.expenses.specialized.store') }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="card-header d-flex justify-content-between align-items-center gap-2">
                                        <h5 class="mb-0">ثبت هزینه اختصاصی با workflow تایید</h5>
                                        <span class="badge bg-label-warning">بیمه، گمرک، ضایعات، کارمزد، حقوق تولید،
                                            استهلاک</span>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">تاریخ</label>
                                            <input type="date" name="expense_date_en" class="form-control"
                                                value="{{ old('expense_date_en', $today) }}">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">نوع workflow</label>
                                            <select name="specialized_kind" class="form-select" required>
                                                @foreach ($specializedKinds as $kind => $label)
                                                    <option value="{{ $kind }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">شماره مرجع</label>
                                            <input type="text" name="reference_number" class="form-control"
                                                value="{{ old('reference_number') }}">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">هدف تخصیص</label>
                                            <select name="allocation_target_type" class="form-select">
                                                <option value="manual">دستی</option>
                                                <option value="product">کالا</option>
                                                <option value="order">سفارش/خرید</option>
                                                <option value="project">پروژه</option>
                                                <option value="contract">قرارداد</option>
                                                <option value="cost_center">مرکز هزینه</option>
                                                <option value="asset">دارایی ثابت</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">مبنای تخصیص</label>
                                            <select name="allocation_basis" class="form-select">
                                                <option value="direct">مستقیم</option>
                                                <option value="manual">دستی</option>
                                                <option value="amount">مبلغ</option>
                                                <option value="quantity">تعداد</option>
                                                <option value="weight">وزن</option>
                                                <option value="time">زمان</option>
                                                <option value="equal">مساوی</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">مرکز هزینه</label>
                                            <select name="cost_center_id" class="form-select select2-basic" required>
                                                <option value="">انتخاب مرکز هزینه</option>
                                                @foreach ($costCenters as $costCenter)
                                                    <option value="{{ $costCenter->id }}">{{ $costCenter->code }} -
                                                        {{ $costCenter->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">نوع هزینه</label>
                                            <select name="expense_type_id" class="form-select select2-basic" required>
                                                <option value="">انتخاب نوع هزینه</option>
                                                @foreach ($expenseTypes as $expenseType)
                                                    <option value="{{ $expenseType->id }}">
                                                        {{ $expenseType->code }} - {{ $expenseType->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">کالا / محصول مرتبط</label>
                                            @include('partials.forms.erp-product-filter-select', [
                                                'name' => 'product_id',
                                                'placeholder' => 'بدون اتصال کالا',
                                                'class' => 'form-select select2-basic erp-remote-select',
                                            ])
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">حساب هزینه</label>
                                            <select name="expense_account_id" class="form-select account-select">
                                                <option value="">حساب نوع هزینه یا حساب سیستمی</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->code }} -
                                                        {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">حساب تسویه / پرداختنی</label>
                                            <select name="settlement_account_id" class="form-select account-select"
                                                required>
                                                <option value="">انتخاب حساب</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->code }} -
                                                        {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <label class="form-label">مبلغ پایه</label>
                                            <input type="number" min="0.01" step="0.01" name="amount"
                                                class="form-control text-end" required>
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <label class="form-label">مالیات/عوارض</label>
                                            <input type="number" min="0" step="0.01" name="tax_amount"
                                                class="form-control text-end" value="0">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">کد پروژه</label>
                                            <input type="text" name="project_code" class="form-control"
                                                value="{{ old('project_code') }}">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">کد قرارداد</label>
                                            <input type="text" name="contract_code" class="form-control"
                                                value="{{ old('contract_code') }}">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">شناسه هدف تخصیص</label>
                                            <input type="number" min="1" step="1"
                                                name="allocation_target_id" class="form-control text-end"
                                                value="{{ old('allocation_target_id') }}">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">شرح</label>
                                            <input type="text" name="description" class="form-control"
                                                placeholder="شرح هزینه اختصاصی">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">یادداشت workflow</label>
                                            <input type="text" name="workflow_note" class="form-control"
                                                placeholder="شماره بیمه نامه، اظهارنامه گمرکی، حواله ضایعات یا مبنای محاسبه">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">یادداشت تخصیص</label>
                                            <input type="text" name="allocation_note" class="form-control"
                                                placeholder="مبنای جذب هزینه به کالا، سفارش یا پروژه">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">فایل مدرک</label>
                                            <input type="file" name="attachment_file" class="form-control"
                                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">یادداشت مدرک</label>
                                            <input type="text" name="attachment_note" class="form-control"
                                                placeholder="تصویر قبض، قرارداد، اظهارنامه یا حواله">
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button class="btn btn-warning" type="submit">ثبت و ارسال برای تایید</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <form class="card mb-4" method="GET" action="{{ route('Accounting.expenses') }}">
                            <div class="card-body row g-3 align-items-end">
                                <div class="col-12 col-md-3">
                                    <label class="form-label">از تاریخ</label>
                                    <input type="date" name="date_from" class="form-control"
                                        value="{{ request('date_from') }}">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">تا تاریخ</label>
                                    <input type="date" name="date_to" class="form-control"
                                        value="{{ request('date_to') }}">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">مرکز هزینه</label>
                                    <select name="cost_center_id" class="form-select select2-basic">
                                        <option value="">همه</option>
                                        @foreach ($costCenters as $costCenter)
                                            <option value="{{ $costCenter->id }}" @selected((string) request('cost_center_id') === (string) $costCenter->id)>
                                                {{ $costCenter->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">نوع هزینه</label>
                                    <select name="expense_type_id" class="form-select select2-basic">
                                        <option value="">همه</option>
                                        @foreach ($expenseTypes as $expenseType)
                                            <option value="{{ $expenseType->id }}" @selected((string) request('expense_type_id') === (string) $expenseType->id)>
                                                {{ $expenseType->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">workflow اختصاصی</label>
                                    <select name="specialized_kind" class="form-select">
                                        <option value="">همه</option>
                                        @foreach ($specializedKinds as $kind => $label)
                                            <option value="{{ $kind }}" @selected(request('specialized_kind') === $kind)>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">وضعیت workflow</label>
                                    <select name="workflow_status" class="form-select">
                                        <option value="">همه</option>
                                        <option value="pending_approval" @selected(request('workflow_status') === 'pending_approval')>در انتظار تایید
                                        </option>
                                        <option value="approved" @selected(request('workflow_status') === 'approved')>تایید شده</option>
                                        <option value="rejected" @selected(request('workflow_status') === 'rejected')>رد شده</option>
                                    </select>
                                </div>
                                <div class="col-12 text-end">
                                    <button class="btn btn-outline-primary" type="submit">اعمال فیلتر</button>
                                </div>
                            </div>
                        </form>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">لیست هزینه ها</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>شماره</th>
                                                    <th>تاریخ</th>
                                                    <th>نوع</th>
                                                    <th>workflow</th>
                                                    <th>مرکز هزینه</th>
                                                    <th class="text-end">مبلغ</th>
                                                    <th>سند</th>
                                                    <th>مدارک</th>
                                                    <th>اقدام</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($expenses as $expense)
                                                    <tr>
                                                        <td>{{ $expense->expense_number }}</td>
                                                        <td>{{ $expense->expense_date_fa ?: optional($expense->expense_date_en)->format('Y-m-d') }}
                                                        </td>
                                                        <td>{{ optional($expense->expenseType)->name }}</td>
                                                        <td>
                                                            @if ($expense->specialized_kind)
                                                                <span
                                                                    class="badge bg-label-info">{{ $specializedKinds[$expense->specialized_kind] ?? $expense->specialized_kind }}</span>
                                                            @else
                                                                <span class="badge bg-label-secondary">استاندارد</span>
                                                            @endif
                                                            <div class="small text-muted mt-1">
                                                                @if ($expense->workflow_status === 'pending_approval')
                                                                    در انتظار تایید
                                                                @elseif ($expense->workflow_status === 'rejected')
                                                                    رد شده
                                                                @else
                                                                    تایید شده
                                                                @endif
                                                            </div>
                                                            @if ($expense->product || $expense->project_code || $expense->contract_code)
                                                                <div class="small text-muted">
                                                                    {{ optional($expense->product)->title ?? optional($expense->product)->name }}
                                                                    {{ $expense->project_code ? ' / پروژه: ' . $expense->project_code : '' }}
                                                                    {{ $expense->contract_code ? ' / قرارداد: ' . $expense->contract_code : '' }}
                                                                </div>
                                                            @endif
                                                            @if ($expense->allocations->isNotEmpty())
                                                                <div class="small text-muted">
                                                                    تخصیص:
                                                                    {{ $expense->allocations->map(fn($allocation) => ($allocation->allocation_target_type ?: $allocation->target_type ?: 'manual') . ' / ' . number_format((float) ($allocation->allocated_amount ?: $allocation->amount)))->implode('، ') }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td>{{ optional($expense->costCenter)->name }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $expense->total_amount) }}</td>
                                                        <td>{{ optional($expense->voucher)->voucher_number ?: '-' }}
                                                        </td>
                                                        <td style="min-width: 260px">
                                                            @forelse($expense->financialAttachments as $attachment)
                                                                <a class="badge bg-label-secondary mb-1"
                                                                    target="_blank" href="{{ $attachment->url }}">
                                                                    {{ $attachment->original_name ?: 'مشاهده مدرک' }}
                                                                </a>
                                                            @empty
                                                                <span class="text-muted d-block mb-1">بدون مدرک</span>
                                                            @endforelse
                                                            <form method="POST"
                                                                action="{{ route('Accounting.expenses.attachments.store', $expense) }}"
                                                                enctype="multipart/form-data"
                                                                class="d-flex flex-wrap gap-1 align-items-center mt-1">
                                                                @csrf
                                                                <input type="file" name="attachment_file"
                                                                    class="form-control form-control-sm" required
                                                                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt">
                                                                <input type="text" name="attachment_note"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="یادداشت">
                                                                <button class="btn btn-sm btn-outline-primary"
                                                                    type="submit">افزودن</button>
                                                            </form>
                                                        </td>
                                                        <td>
                                                            @if ($expense->specialized_kind && $expense->workflow_status === 'pending_approval')
                                                                <div class="d-flex flex-wrap gap-1">
                                                                    <form method="POST"
                                                                        action="{{ route('Accounting.expenses.approve', $expense) }}">
                                                                        @csrf
                                                                        <button class="btn btn-sm btn-success"
                                                                            type="submit">تایید و سند</button>
                                                                    </form>
                                                                    <form method="POST"
                                                                        action="{{ route('Accounting.expenses.reject', $expense) }}">
                                                                        @csrf
                                                                        <button class="btn btn-sm btn-outline-danger"
                                                                            type="submit">رد</button>
                                                                    </form>
                                                                </div>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center text-muted py-4">هزینه
                                                            ای ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">{{ $expenses->links() }}</div>
                                </div>
                            </div>
                            <div class="col-12 col-xl-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">خلاصه مراکز هزینه</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>مرکز</th>
                                                    <th>شعبه/انبار</th>
                                                    <th class="text-end">جمع</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($costCenterSummaries as $summary)
                                                    <tr>
                                                        <td>{{ $summary['cost_center'] }}</td>
                                                        <td>{{ $summary['store'] }}</td>
                                                        <td class="text-end">{{ number_format($summary['total']) }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-4">داده ای
                                                            برای خلاصه وجود ندارد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">خلاصه تخصیص هزینه به محصول، سفارش و پروژه</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>هدف تخصیص</th>
                                                    <th>مبنا</th>
                                                    <th class="text-end">تعداد</th>
                                                    <th class="text-end">جمع تخصیص</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($allocationSummaries as $summary)
                                                    <tr>
                                                        <td>{{ $summary['target'] }}</td>
                                                        <td>{{ $summary['basis'] }}</td>
                                                        <td class="text-end">{{ number_format($summary['count']) }}
                                                        </td>
                                                        <td class="text-end">{{ number_format($summary['total']) }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-4">تخصیصی
                                                            برای هزینه ها ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">کنترل workflow هزینه های اختصاصی</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>نوع workflow</th>
                                                    <th class="text-end">تعداد</th>
                                                    <th class="text-end">در انتظار تایید</th>
                                                    <th class="text-end">تایید شده</th>
                                                    <th class="text-end">جمع</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($specializedSummaries as $summary)
                                                    <tr>
                                                        <td>{{ $summary['label'] }}</td>
                                                        <td class="text-end">{{ number_format($summary['count']) }}
                                                        </td>
                                                        <td class="text-end">{{ number_format($summary['pending']) }}
                                                        </td>
                                                        <td class="text-end">
                                                            {{ number_format($summary['approved']) }}
                                                        </td>
                                                        <td class="text-end">{{ number_format($summary['total']) }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">هزینه
                                                            اختصاصی ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
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
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    @include('partials.erp-remote-select-assets')
    <script>
        $(function() {
            $('.select2-basic:not(.erp-remote-select), .account-select').select2({
                width: '100%'
            });
        });
    </script>
</body>

</html>
