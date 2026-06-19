<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>{{ $scopeLabel }} - دکان دارمینو</title>
    <meta content="" name="description"/>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet"/><script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet"/>
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
                    <h4 class="py-3 mb-4">
                        <span class="text-muted fw-light">محصولات /</span>
                        {{ $scopeLabel }}
                    </h4>
                    <div class="row mt-3">
                        <div class="col-12 col-md-5 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">ثبت {{ $scopeLabel }} جدید</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('units.store') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="usage_scope" value="{{ $usageScope }}">
                                        <div class="mb-3">
                                            <label class="form-label" for="title">عنوان</label>
                                            <input type="text" class="form-control" name="title" id="title" placeholder="مثلاً عدد، کارتن، کیلوگرم" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="symbol">نماد</label>
                                            <input type="text" class="form-control" name="symbol" id="symbol" placeholder="نماد کوتاه">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="unit_type">نوع واحد</label>
                                            <select class="form-select" name="unit_type" id="unit_type">
                                                @foreach (\App\Models\Unit::UNIT_TYPE_LABELS as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="organization_id">واحد پخش</label>
                                            <select class="select2 form-select" name="organization_id" id="organization_id">
                                                <option value="">پیش‌فرض سازمان جاری</option>
                                                @foreach ($organizations as $organization)
                                                    <option value="{{ $organization->id }}">{{ $organization->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="description">توضیحات</label>
                                            <textarea class="form-control" name="description" id="description" rows="2"></textarea>
                                        </div>
                                        <button class="btn btn-primary" type="submit">ثبت</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-7 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">فهرست {{ $scopeLabel }}</h5>
                                    <span class="badge bg-label-primary">{{ $units->count() }} مورد</span>
                                </div>
                                <div class="card-datatable table-responsive py-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>عنوان</th>
                                            <th>نوع</th>
                                            <th>وضعیت</th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php($x = 1)
                                        @foreach ($units as $unit)
                                            <tr>
                                                <td>{{ $x++ }}</td>
                                                <td>{{ $unit->title }}</td>
                                                <td>{{ \App\Models\Unit::UNIT_TYPE_LABELS[$unit->unit_type] ?? $unit->unit_type }}</td>
                                                <td>
                                                    @if ($unit->isActive == 1)
                                                        <span class="badge bg-label-success">فعال</span>
                                                    @else
                                                        <span class="badge bg-label-danger">غیرفعال</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('units.edit', $unit->id) }}" class="btn btn-sm btn-icon btn-label-primary">
                                                        <x-ui.icon name="edit" />
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('sections.footer')
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
<script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/jquery-sticky/jquery-sticky.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<script src="{{ asset('assets/') }}/js/main.js"></script>
<script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
<script>
    $('.products').addClass('open');
    $('.products .units-{{ $usageScope }}').addClass('active open');

    $(function () {
        var dt_without_ajax_table = $('.datatables-direct-basic');
        if (dt_without_ajax_table.length) {
            dt_without_ajax_table.DataTable({
                searching: true,
                lengthChange: false,
                ordering: true,
                pageLength: 50,
            });
        }
    });
</script>
</body>
</html>
