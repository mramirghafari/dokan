<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>مراکز درآمد - دکان دارمینو</title>
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
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> مراکز درآمد
                            </h4>
                            <a class="btn btn-outline-secondary" href="{{ route('Accounting.financialStatements') }}">
                                <i class="ti ti-chart-pie me-1"></i> صورت های مالی
                            </a>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-4">
                                <form class="card h-100" method="POST"
                                    action="{{ route('Accounting.revenueCenters.store') }}">
                                    @csrf
                                    <div class="card-header">
                                        <h5 class="mb-0">تعریف مرکز درآمد</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-12 col-md-5">
                                            <label class="form-label">کد</label>
                                            <input type="text" name="code" class="form-control"
                                                value="{{ old('code') }}">
                                        </div>
                                        <div class="col-12 col-md-7">
                                            <label class="form-label">نام مرکز درآمد</label>
                                            <input type="text" name="name" class="form-control"
                                                value="{{ old('name') }}" required>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">نوع مرکز</label>
                                            <select name="center_type" class="form-select">
                                                @foreach ($centerTypes as $type => $label)
                                                    <option value="{{ $type }}" @selected(old('center_type', 'branch') === $type)>
                                                        {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">شعبه/انبار مرتبط</label>
                                            <select name="store_id" class="form-select select2-basic">
                                                <option value="">بدون اتصال</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}" @selected((string) old('store_id') === (string) $store->id)>
                                                        {{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">مرکز بالادست</label>
                                            <select name="parent_id" class="form-select select2-basic">
                                                <option value="">بدون بالادست</option>
                                                @foreach ($parentCenters as $center)
                                                    <option value="{{ $center->id }}" @selected((string) old('parent_id') === (string) $center->id)>
                                                        {{ $center->code }} - {{ $center->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">مسئول/مدیر</label>
                                            <input type="text" name="manager_name" class="form-control"
                                                value="{{ old('manager_name') }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">شرح</label>
                                            <input type="text" name="description" class="form-control"
                                                value="{{ old('description') }}">
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button class="btn btn-primary" type="submit">ثبت مرکز درآمد</button>
                                    </div>
                                </form>
                            </div>

                            <div class="col-12 col-xl-8">
                                <form class="card mb-4" method="GET"
                                    action="{{ route('Accounting.revenueCenters') }}">
                                    <div class="card-body row g-3 align-items-end">
                                        <div class="col-12 col-md-5">
                                            <label class="form-label">نوع مرکز</label>
                                            <select name="center_type" class="form-select">
                                                <option value="">همه</option>
                                                @foreach ($centerTypes as $type => $label)
                                                    <option value="{{ $type }}" @selected(request('center_type') === $type)>
                                                        {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-5">
                                            <label class="form-label">شعبه/انبار</label>
                                            <select name="store_id" class="form-select select2-basic">
                                                <option value="">همه</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}" @selected((string) request('store_id') === (string) $store->id)>
                                                        {{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-2 text-end">
                                            <button class="btn btn-outline-primary w-100"
                                                type="submit">فیلتر</button>
                                        </div>
                                    </div>
                                </form>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">لیست مراکز درآمد</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>کد</th>
                                                    <th>نام</th>
                                                    <th>نوع</th>
                                                    <th>شعبه/انبار</th>
                                                    <th>مدیر</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($centers as $center)
                                                    <tr>
                                                        <td>{{ $center->code ?: '-' }}</td>
                                                        <td>
                                                            <div class="fw-semibold">{{ $center->name }}</div>
                                                            <small
                                                                class="text-muted">{{ optional($center->parent)->name ?: 'بدون بالادست' }}</small>
                                                        </td>
                                                        <td>{{ $centerTypes[$center->center_type] ?? $center->center_type }}
                                                        </td>
                                                        <td>{{ optional($center->store)->title ?: '-' }}</td>
                                                        <td>{{ $center->manager_name ?: '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">مرکز
                                                            درآمدی ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">{{ $centers->links() }}</div>
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
    <script>
        $(function() {
            $('.select2-basic').select2({
                width: '100%'
            });
        });
    </script>
</body>

</html>
