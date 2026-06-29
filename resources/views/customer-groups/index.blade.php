<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>گروه‌های مشتری - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
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
                            <span class="text-muted fw-light">مشتریان /</span>
                            گروه مشتری
                        </h4>

                        <div class="row">
                            <div class="col-12 col-lg-5 mb-4">
                                <div class="card">
                                    <div class="card-header border-bottom">
                                        <h5 class="mb-0">ثبت گروه جدید</h5>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('customer-groups.store') }}" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label" for="title">عنوان گروه <span
                                                        class="text-danger">*</span></label>
                                                <input class="form-control" id="title" name="title" type="text"
                                                    value="{{ old('title') }}" placeholder="مثلاً: عمده‌فروش، خرده‌فروش"
                                                    required />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="description">توضیحات</label>
                                                <textarea class="form-control" id="description" name="description" rows="3"
                                                    placeholder="توضیح کوتاه درباره گروه">{{ old('description') }}</textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="sort_order">ترتیب نمایش</label>
                                                <input class="form-control" id="sort_order" name="sort_order" type="number"
                                                    min="0" value="{{ old('sort_order', 0) }}" />
                                            </div>
                                            <button class="btn btn-primary" type="submit">ثبت گروه</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-7 mb-4">
                                <div class="card">
                                    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">لیست گروه‌های مشتری</h5>
                                        <small class="text-muted">برای تخصیص گروه، در فرم ثبت/ویرایش مشتری از لیست زیر
                                            استفاده کنید.</small>
                                    </div>
                                    <div class="card-datatable table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>عنوان</th>
                                                    <th>توضیحات</th>
                                                    <th>وضعیت</th>
                                                    <th>عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($groups as $index => $group)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $group->title }}</td>
                                                        <td><small class="text-muted">{{ $group->description ?: '—' }}</small></td>
                                                        <td>
                                                            @if ($group->isActive)
                                                                <span class="badge bg-label-success">فعال</span>
                                                            @else
                                                                <span class="badge bg-label-secondary">غیرفعال</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a class="btn btn-sm btn-label-primary"
                                                                href="{{ route('customer-groups.edit', $group) }}">ویرایش</a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">
                                                            هنوز گروهی ثبت نشده است. از فرم سمت چپ اولین گروه را ایجاد
                                                            کنید.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('sections.footer')
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
</body>

</html>
