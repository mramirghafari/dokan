<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ثبت انبارگردانی - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
</head>

<body>
    @include('sweetalert::alert')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4"><span class="text-muted fw-light">انبار و تامین /</span> ثبت انبارگردانی
                        </h4>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('stocks.inventoryAdjustments.store') }}">
                            @csrf
                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">انبار</label>
                                            <select class="form-select" name="store_id" required>
                                                <option value="">انتخاب کنید</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}"
                                                        @if (old('store_id') == $store->id) selected @endif>
                                                        {{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">تاریخ شمسی</label>
                                            <input class="form-control" name="date_fa" value="{{ old('date_fa') }}"
                                                placeholder="1403/01/01">
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">توضیحات</label>
                                            <input class="form-control" name="notes" value="{{ old('notes') }}"
                                                placeholder="شرح سند انبارگردانی">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">ردیف های شمارش</h5>
                                    <button class="btn btn-sm btn-label-primary" id="add-adjustment-row"
                                        type="button">افزودن ردیف</button>
                                </div>
                                <div class="table-responsive text-nowrap">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>کالا</th>
                                                <th>مکان</th>
                                                <th>شمارش نهایی</th>
                                                <th>بهای واحد</th>
                                                <th>شرح ردیف</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="adjustment-items">
                                            @include('stocks.inventory_adjustments.row', [
                                                'index' => 0,
                                                'warehouseLocations' => $warehouseLocations,
                                            ])
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer d-flex gap-2">
                                    <button class="btn btn-primary" type="submit">ثبت پیش نویس</button>
                                    <a class="btn btn-label-secondary"
                                        href="{{ route('stocks.inventoryAdjustments') }}">بازگشت</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    @include('sections.footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>

    <template id="adjustment-row-template">
        @include('stocks.inventory_adjustments.row', [
            'index' => '__INDEX__',
            'warehouseLocations' => $warehouseLocations,
        ])
    </template>

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
        $('.anbarotozi').addClass('open');
        $('.anbarotozi .inventory_adjustments').addClass('active');

        let nextAdjustmentRow = 1;
        document.getElementById('add-adjustment-row').addEventListener('click', function() {
            const template = document.getElementById('adjustment-row-template').innerHTML.replaceAll('__INDEX__',
                nextAdjustmentRow++);
            document.getElementById('adjustment-items').insertAdjacentHTML('beforeend', template);
            if (window.ErpRemoteSelect) {
                window.ErpRemoteSelect.init(document.getElementById('adjustment-items'));
            }
        });

        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-adjustment-row')) {
                event.target.closest('tr').remove();
            }
        });
    </script>
</body>

</html>
