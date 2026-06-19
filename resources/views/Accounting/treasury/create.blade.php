<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ثبت دریافت و پرداخت - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
</head>

<body>
    @include('partials.panel-toasts')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections/sidebar')
            <div class="layout-page">
                @include('sections/header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> ثبت دریافت و
                                پرداخت</h4>
                            <a class="btn btn-outline-secondary" href="{{ route('Accounting.treasury') }}">بازگشت</a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('Accounting.treasury.store') }}" method="POST">
                            @csrf
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">اطلاعات عملیات خزانه</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">نوع عملیات</label>
                                            <select name="transaction_type" class="form-select">
                                                <option value="receipt"
                                                    {{ old('transaction_type') === 'receipt' ? 'selected' : '' }}>
                                                    دریافت</option>
                                                <option value="payment"
                                                    {{ old('transaction_type') === 'payment' ? 'selected' : '' }}>
                                                    پرداخت</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">تاریخ سند</label>
                                            <input type="date" name="voucher_date_en" class="form-control"
                                                value="{{ old('voucher_date_en', $today) }}">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">روش</label>
                                            <select name="payment_method" class="form-select" id="payment-method">
                                                @foreach ($paymentMethods as $method)
                                                    <option value="{{ $method->legacy_code }}"
                                                        {{ old('payment_method') == $method->legacy_code ? 'selected' : '' }}>
                                                        {{ $method->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">مبلغ</label>
                                            <input type="number" min="0" step="0.01" name="amount"
                                                class="form-control text-end" value="{{ old('amount') }}">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">حساب خزانه</label>
                                            <select name="treasury_account_id" class="form-select account-select">
                                                <option value="">حساب سیستمی بر اساس روش انتخاب شود</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}"
                                                        {{ old('treasury_account_id') == $account->id ? 'selected' : '' }}>
                                                        {{ $account->code }} - {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">طرف حساب</label>
                                            <select name="counter_account_id" class="form-select account-select">
                                                <option value="">انتخاب طرف حساب</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}"
                                                        {{ old('counter_account_id') == $account->id ? 'selected' : '' }}>
                                                        {{ $account->code }} - {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4 terminal-field">
                                            <label class="form-label">پایانه پرداخت</label>
                                            <select name="payment_terminal_id" class="form-select">
                                                <option value="">انتخاب پایانه</option>
                                                @foreach ($terminals as $terminal)
                                                    <option value="{{ $terminal->id }}"
                                                        {{ old('payment_terminal_id') == $terminal->id ? 'selected' : '' }}>
                                                        {{ $terminal->title }} {{ $terminal->terminal_number }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4 cheque-field">
                                            <label class="form-label">بانک صادرکننده</label>
                                            <input type="text" name="issuing_bank" class="form-control"
                                                value="{{ old('issuing_bank') }}">
                                        </div>
                                        <div class="col-12 col-md-4 cheque-field outgoing-cheque-field">
                                            <label class="form-label">برگ دسته چک</label>
                                            <select name="cheque_leaf_id" class="form-select account-select">
                                                <option value="">شماره چک دستی / بدون دسته چک</option>
                                                @foreach ($chequeLeaves as $leaf)
                                                    <option value="{{ $leaf->id }}" @selected(old('cheque_leaf_id') == $leaf->id)>
                                                        {{ $leaf->leaf_number }} -
                                                        {{ optional($leaf->book?->account)->code }}
                                                        {{ optional($leaf->book?->account)->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-4 cheque-field">
                                            <label class="form-label">شماره چک</label>
                                            <input type="text" name="cheque_number" class="form-control"
                                                value="{{ old('cheque_number') }}">
                                        </div>
                                        <div class="col-12 col-md-4 cheque-field">
                                            <label class="form-label">سررسید چک</label>
                                            <input type="date" name="due_date" class="form-control"
                                                value="{{ old('due_date') }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">شرح</label>
                                            <input type="text" name="description" class="form-control"
                                                value="{{ old('description') }}" placeholder="شرح دریافت یا پرداخت">
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary">ثبت سند موقت</button>
                                </div>
                            </div>
                        </form>
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <script>
        $(function() {
            $('.account-select').select2({
                width: '100%'
            });

            function toggleMethodFields() {
                const method = $('#payment-method').val();
                $('.terminal-field').toggleClass('d-none', method !== '4');
                $('.cheque-field').toggleClass('d-none', method !== '2');
                $('.outgoing-cheque-field').toggleClass('d-none', method !== '2' || $('[name="transaction_type"]')
                    .val() !== 'payment');
            }

            $('#payment-method').on('change', toggleMethodFields);
            $('[name="transaction_type"]').on('change', toggleMethodFields);
            toggleMethodFields();
        });
    </script>
</body>

</html>
