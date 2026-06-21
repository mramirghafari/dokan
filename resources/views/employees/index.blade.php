<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>پرسنل و کارگزینی - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/js/config.js"></script>
</head>

<body>
    @include('partials.panel-toasts')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="mb-4"><span class="text-muted fw-light">منابع انسانی /</span> پرسنل و کارگزینی</h4>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">تعریف پرسنل جدید</h5>
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#newEmployeeForm">نمایش / بستن فرم</button>
                            </div>
                            <div class="collapse show" id="newEmployeeForm">
                                <form method="POST" action="{{ route('employees.store') }}">
                                    @csrf
                                    @include('employees._form', ['employee' => null])
                                    <div class="card-footer text-end">
                                        <button class="btn btn-primary" type="submit">ثبت پرسنل</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">لیست پرسنل ({{ $employees->count() }})</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>نام</th>
                                            <th>کد پرسنلی</th>
                                            <th>کد ملی</th>
                                            <th>سمت</th>
                                            <th>نوع همکاری</th>
                                            <th>تاریخ استخدام</th>
                                            <th>وضعیت</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($employees as $employee)
                                            <tr>
                                                <td>{{ $employee->name }}</td>
                                                <td>{{ $employee->personnel_code ?: '-' }}</td>
                                                <td>{{ $employee->national_code ?: '-' }}</td>
                                                <td>{{ $employee->job_title ?: '-' }}</td>
                                                <td>{{ $employee->employment_type_label }}</td>
                                                <td>{{ $employee->hire_date_fa ?: '-' }}</td>
                                                <td>
                                                    @if ($employee->employment_status === 'terminated')
                                                        <span class="badge bg-label-secondary">خاتمه همکاری</span>
                                                    @elseif ($employee->employment_status === 'suspended')
                                                        <span class="badge bg-label-warning">تعلیق</span>
                                                    @elseif (!$employee->isActive)
                                                        <span class="badge bg-label-secondary">غیرفعال</span>
                                                    @else
                                                        <span class="badge bg-label-success">شاغل</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a class="btn btn-sm btn-outline-primary"
                                                        href="{{ route('employees.edit', $employee) }}">ویرایش</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">هنوز پرسنلی ثبت
                                                    نشده است. برای صدور حکم کارگزینی و محاسبهٔ حقوق ابتدا پرسنل را
                                                    تعریف کنید.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer text-muted">
                                پس از تعریف پرسنل، از صفحهٔ «حقوق و دستمزد» برای آن‌ها حکم کارگزینی (قرارداد حقوقی)
                                صادر کنید.
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
</body>

</html>
