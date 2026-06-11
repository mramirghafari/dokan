<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>قفسه و مکان انبار - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"
        rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
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
                            <span class="text-muted fw-light">انبار /</span>
                            قفسه و مکان انبار
                        </h4>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif
                        <div class="row mt-4">
                            <div class="col-12 col-lg-4 mb-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">تعریف مکان جدید</h5>
                                    </div>
                                    <div class="card-body">
                                        @include('warehouse_locations._form', [
                                            'action' => route('warehouse-locations.store'),
                                            'method' => 'POST',
                                            'submitLabel' => 'ایجاد مکان',
                                            'location' => null,
                                        ])
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-8 mb-4">
                                <div class="card">
                                    <div class="card-datatable table-responsive py-0">
                                        <table class="datatables-direct-basic table">
                                            <thead>
                                                <tr>
                                                    <th>ردیف</th>
                                                    <th>انبار</th>
                                                    <th>کد</th>
                                                    <th>عنوان</th>
                                                    <th>نوع</th>
                                                    <th>مسیر</th>
                                                    <th>وضعیت</th>
                                                    <th>عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($locations as $location)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $location->store->title ?? '-' }}</td>
                                                        <td><small>{{ $location->code }}</small></td>
                                                        <td>{{ $location->title }}</td>
                                                        <td>{{ $types[$location->type] ?? $location->type }}</td>
                                                        <td><small>{{ $location->path }}</small></td>
                                                        <td>
                                                            @if ($location->is_active)
                                                                <div class="badge badge-success">فعال</div>
                                                            @else
                                                                <div class="badge badge-danger">غیرفعال</div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('warehouse-locations.edit', $location->id) }}"
                                                                style="font-size:20px;float:right;margin-left:5px">
                                                                <i class="fa fa-edit" style="color:#04a9f5;"></i>
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <script>
        $('.basicdata').addClass('open');
        $('.basicdata .warehouse_locations').addClass('active open');
        $('.select2').select2({
            dir: 'rtl',
            width: '100%'
        });
        $('.datatables-direct-basic').DataTable({
            pageLength: 25,
            order: [
                [1, 'asc'],
                [2, 'asc']
            ],
            language: {
                url: '{{ asset('assets/json/i18n/fa.json') }}'
            }
        });
    </script>
</body>

</html>
