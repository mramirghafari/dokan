<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ثبت درخواست خرید - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">انبار و تامین /</span> درخواست خرید
                            </h4>
                            <a class="btn btn-outline-secondary"
                                href="{{ route('purchase-requisitions.index') }}">بازگشت</a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('purchase-requisitions.store') }}">
                            @csrf
                            <div class="card mb-4">
                                <div class="card-body row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="form-label">انبار مقصد</label>
                                        <select name="store_id" class="form-select" required>
                                            <option value="">انتخاب کنید</option>
                                            @foreach ($stores as $store)
                                                <option value="{{ $store->id }}" @selected((string) old('store_id') === (string) $store->id)>
                                                    {{ $store->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">تاریخ درخواست</label>
                                        <input type="date" name="request_date_en" class="form-control"
                                            value="{{ old('request_date_en', $today) }}">
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">اولویت</label>
                                        <select name="priority" class="form-select">
                                            <option value="low" @selected(old('priority') === 'low')>کم</option>
                                            <option value="normal" @selected(old('priority', 'normal') === 'normal')>عادی</option>
                                            <option value="high" @selected(old('priority') === 'high')>بالا</option>
                                            <option value="urgent" @selected(old('priority') === 'urgent')>فوری</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">شرح</label>
                                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">اقلام درخواست</h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead>
                                            <tr>
                                                <th>کالا</th>
                                                <th class="text-end">تعداد</th>
                                                <th>شرح قلم</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for ($i = 0; $i < 10; $i++)
                                                <tr>
                                                    <td>
                                                        <x-erp-remote-select
                                                            entity="products"
                                                            name="product_id[]"
                                                            :value="old('product_id.' . $i)"
                                                            placeholder="انتخاب کالا"
                                                            class="form-select erp-remote-select"
                                                            :filters="config('erp_scale.remote_lookup.product_filters')"
                                                        />
                                                    </td>
                                                    <td><input type="number" step="0.001" min="0"
                                                            name="quantity[]" class="form-control text-end"
                                                            value="{{ old('quantity.' . $i) }}"></td>
                                                    <td><input type="text" name="item_description[]"
                                                            class="form-control"
                                                            value="{{ old('item_description.' . $i) }}"></td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer text-end">
                                    <button class="btn btn-primary" type="submit">ثبت درخواست خرید</button>
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
    @include('partials.erp-remote-select-assets')
</body>

</html>
