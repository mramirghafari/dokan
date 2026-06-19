<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>انتقال بین حساب های خزانه - دکان دارمینو</title>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> انتقال بین
                                حساب های خزانه</h4>
                            <a class="btn btn-outline-secondary" href="{{ route('Accounting.treasury') }}">بازگشت</a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('Accounting.treasury.transfer.store') }}" method="POST">
                            @csrf
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">اطلاعات انتقال</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">تاریخ سند</label>
                                            <input type="date" name="voucher_date_en" class="form-control"
                                                value="{{ old('voucher_date_en', $today) }}">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">مبلغ</label>
                                            <input type="number" min="0" step="0.01" name="amount"
                                                class="form-control text-end" value="{{ old('amount') }}">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">شرح</label>
                                            <input type="text" name="description" class="form-control"
                                                value="{{ old('description') }}"
                                                placeholder="مثلا انتقال از صندوق به بانک">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">حساب مبدا</label>
                                            <select name="from_account_id" class="form-select account-select">
                                                <option value="">انتخاب حساب مبدا</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}" @selected(old('from_account_id') == $account->id)>
                                                        {{ $account->code }} - {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">حساب مقصد</label>
                                            <select name="to_account_id" class="form-select account-select">
                                                <option value="">انتخاب حساب مقصد</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}" @selected(old('to_account_id') == $account->id)>
                                                        {{ $account->code }} - {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">ثبت انتقال</button>
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
        });
    </script>
</body>

</html>
