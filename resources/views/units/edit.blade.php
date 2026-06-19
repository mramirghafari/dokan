<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>ویرایش {{ $scopeLabel }} - دکان دارمینو</title>
    <meta content="" name="description"/>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet"/>
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
                        <a href="{{ route($indexRoute) }}" class="text-muted fw-light">{{ $scopeLabel }} /</a>
                        ویرایش
                    </h4>
                    <div class="row mt-3">
                        <div class="col-12 col-md-5 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">ویرایش {{ $scopeLabel }}</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('units.update', $unit->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="mb-3">
                                            <label class="form-label" for="title">عنوان</label>
                                            <input type="text" class="form-control" name="title" id="title" value="{{ $unit->title }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="symbol">نماد</label>
                                            <input type="text" class="form-control" name="symbol" id="symbol" value="{{ $unit->symbol }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="unit_type">نوع واحد</label>
                                            <select class="form-select" name="unit_type" id="unit_type">
                                                @foreach (\App\Models\Unit::UNIT_TYPE_LABELS as $value => $label)
                                                    <option value="{{ $value }}" @selected($unit->unit_type === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="organization_id">واحد پخش</label>
                                            <select class="select2 form-select" name="organization_id" id="organization_id">
                                                @foreach ($organizations as $organization)
                                                    <option value="{{ $organization->id }}" @selected($unit->organization_id == $organization->id)>{{ $organization->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="description">توضیحات</label>
                                            <textarea class="form-control" name="description" id="description" rows="2">{{ $unit->description }}</textarea>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" name="isActive" id="isActive" @checked($unit->isActive)>
                                            <label class="form-check-label" for="isActive">فعال</label>
                                        </div>
                                        <button class="btn btn-primary" type="submit">ذخیره</button>
                                        <a href="{{ route($indexRoute) }}" class="btn btn-label-secondary">بازگشت</a>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-7 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card">
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
                                        @foreach ($units as $row)
                                            <tr>
                                                <td>{{ $x++ }}</td>
                                                <td>{{ $row->title }}</td>
                                                <td>{{ \App\Models\Unit::UNIT_TYPE_LABELS[$row->unit_type] ?? $row->unit_type }}</td>
                                                <td>
                                                    @if ($row->isActive == 1)
                                                        <span class="badge bg-label-success">فعال</span>
                                                    @else
                                                        <span class="badge bg-label-danger">غیرفعال</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('units.edit', $row->id) }}" class="btn btn-sm btn-icon btn-label-primary">
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
<script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<script src="{{ asset('assets/') }}/js/main.js"></script>
<script>
    $('.products').addClass('open');
    $('.products .units-{{ $usageScope }}').addClass('active open');
</script>
</body>
</html>
