<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>شعبه ها - دکان دارمینو</title>
    <meta content="" name="description"/>
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/>
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" rel="stylesheet"/>

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet"/>
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet"/>
</head>

<body>
@include('sweetalert::alert')
<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('sections/sidebar')
        <!-- Layout container -->
        <div class="layout-page">
            @include('sections/header')
            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4">
                        <span class="text-muted fw-light">اطلاعات پایه /</span>
                        شعبه ها
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 col-sm-12 order-1 order-lg-2 mb-4 mb-lg-0">

                            <div class="card">
                                <div class="card-header sticky-element bg-label-secondary d-flex justify-content-sm-between align-items-sm-center flex-column flex-sm-row">
                                    <h5 class="card-title mb-sm-0 me-2">لیست شعبه ها</h5>
                                    <div class="action-btns">
                                        <button class="btn btn-primary" data-bs-target="#modalTop" data-bs-toggle="modal" type="button">ثبت شعبه جدید</button>
                                    </div>
                                    <div class="modal modal-top fade" id="modalTop" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form class="modal-content" id="addOrgan" action="{{ route('organizations.store') }}" method="POST" novalidate>
                                                    @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalTopTitle">ثبت شعبه جدید</h5>
                                                    <button aria-label="بستن" class="btn-close" data-bs-dismiss="modal" type="button"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col mb-3">
                                                            <label class="form-label" for="title">نام شعبه <small style="color: red;">*</small></label>
                                                            <input type="text" class="form-control" name="title" required id="title">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col mb-3">
                                                            <label for="panel">انتخاب پنل: <small style="color: red;">*</small></label>
                                                            <select class="form-control" name="tenants_id" id="panel" required>
                                                                <option value="0">-- انتخاب کنید --</option>
                                                                @foreach($Tenants as $tenant)
                                                                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col mb-3">
                                                            <label for="exampleInputEmail111">انتخاب نوع سفارش گیری: <small style="color: red;">*</small></label>
                                                            <select class="form-control" name="type" required>
                                                                <option value="0">-- انتخاب کنید --</option>
                                                                <option value="1">ثبت سفارش بر اساس موجودی</option>
                                                                <option value="2">پیش سفارش (موجودی بر اساس سفارشات)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col mb-3">
                                                            <label for="exampleInputEmail111">واحد پولی شعبه:<small style="color: red;">*</small></label>
                                                            <select class="form-control" name="currency_type" required>
                                                                <option value="0">-- انتخاب کنید --</option>
                                                                <option value="1">تومان</option>
                                                                <option value="2">ریال</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2">
                                                        <div class="col mb-0">
                                                            <label for="description">توضیح:</label>
                                                            <input type="text" class="form-control" name="description" id="description">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button class="btn btn-primary" type="submit">ثبت شعبه</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-datatable table-responsive pt-0 pb-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr>
                                            <th>ردیف</th>
                                            <th>نام شعبه</th>
                                            <th>تعداد اعضا</th>
                                            <th>تعداد مشتریان</th>
                                            <th>تعداد فاکتور فروش</th>
                                            <th>مجموع فروش</th>
                                            <th>مناطق/مسیرها</th>
                                            <th>وضعیت شعبه</th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php($x = 1)
                                        @foreach ($organizations as $organization)
                                            <tr>
                                                <td>{{ $x }}</td>
                                                <td><a href="{{ route('organizations.edit',$organization->id) }}">{{ $organization->title }}</a></td>
                                                <?php $OrganUsersCount = DB::table('users')->where('organization_id', $organization->id)->count(); ?>
                                                <td>{{ $OrganUsersCount }}</td>

                                                <?php $OrganCustomersCount = DB::table('customers')->where('organization_id', $organization->id)->count(); ?>
                                                <td>{{ $OrganCustomersCount }}</td>

                                                    <?php $OrganFactorsCount = DB::table('pishfactors')->where('organization_id', $organization->id)->whereIn('status', [2,4])->count(); ?>
                                                <td>{{ $OrganFactorsCount }}</td>

                                                    <?php $OrganFactors = DB::table('pishfactors')->where('organization_id', $organization->id)->whereIn('status', [2,4])->get(); ?>

                                                    @php($factorfullprices = 0)
                                                    @foreach($OrganFactors as $ofac)
                                                        @php($facfullprice = intval(str_replace(',','',$ofac->fullPrice)))
                                                        @php($factorfullprices += $facfullprice)
                                                    @endforeach
                                                <td>{{ number_format($factorfullprices) }} <small>ریال</small></td>

                                                    <?php $OrganRegionsCount = DB::table('regions')->where('organization_id', $organization->id)->count(); ?>
                                                    <?php $OrganRegionsId = DB::table('regions')->where('organization_id', $organization->id)->pluck('id'); ?>
                                                    <?php $OrganAreasCount = DB::table('areas')->whereIn('region_id', $OrganRegionsId)->count(); ?>
                                                <td>{{ $OrganRegionsCount }} <small>منطقه</small> و {{ $OrganAreasCount }} <small>مسیر</small></td>
                                                <td>
                                                    @if ($organization->isActive == 1 )
                                                        <div class='badge badge-success'>فعال</div>
                                                    @else
                                                        <div class='badge badge-danger'>غیرفعال</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('organizations.edit',$organization->id) }}" style="font-size:20px;float: right;margin-left:5px"><x-ui.icon name="fa-edit" /></a>
                                                     <form action="{{ route('organizations.destroy',$organization->id) }}" method="POST" onsubmit="return confirm('آیا از حذف شعبه مورد نظر اطمینان دارید؟');">
                                                        @method('delete')
                                                        @csrf
                                                        <button type="submit" style="font-size:20px;border: none;background-color: transparent;float: right;">
                                                            <x-ui.icon name="fa-trash" />
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @php($x++)
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Sticky Actions -->
                </div>
                <!-- / Content -->
                @include('sections/footer')
                <div class="content-backdrop fade"></div>
            </div>
            <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>
    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
</div>
<!-- / Layout wrapper -->
<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
<!-- endbuild -->
<script src="{{ asset('assets/') }}/vendor/libs/jquery-sticky/jquery-sticky.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave-phone.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<!-- Main JS -->
<script src="{{ asset('assets/') }}/js/main.js"></script>
<!-- Page JS -->
<script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
<script>
    $('.basicdata').addClass('open')
    $('.basicdata .organizations').addClass('active open')
    // datatable (jquery)
    $(function () {
        var
            dt_without_ajax_table = $('.datatables-direct-basic');

        // DataTable Direct
        // --------------------------------------------------------------------
        if(dt_without_ajax_table.length){
            dt_without_ajax = dt_without_ajax_table.DataTable({
                searching: false,
                lengthChange: false,
                ordering: false,
                pageLength: 5,
            });

            $('.datatables-direct-basic tbody').on( 'click', '.dropdown-item.delete-record', function () {
                dt_without_ajax
                    .row( $(this).parents('tr') )
                    .remove()
                    .draw();
            } );
        }


    });

</script>
<script>
    $(document).ready(function () {
        $('#addOrgan').on('submit', function (e) {
            e.preventDefault(); // جلوگیری از ارسال ابتدا، تا ولیدیشن اجرا شود
            let isValid = true;
            $('.error-message').remove();

            $(this).find('input[required], select[required]').each(function () {
                let $field = $(this);
                let value = $field.val();

                // تبدیل آرایه به رشته اگر multiple باشد
                if (Array.isArray(value)) {
                    value = value.length ? value.join(',') : '';
                }

                if ($.trim(value) === '') {
                    isValid = false;
                    let errorMsg = $('<div class="error-message" style="color:red;font-size:12px;margin-top:4px;">این فیلد الزامی است</div>');

                    if ($field.next('.select2').length) {
                        $field.next('.select2').after(errorMsg);
                    } else {
                        $field.after(errorMsg);
                    }
                }
            });

            if (isValid) {
                this.submit(); // اگر معتبر بود، فرم رو ارسال کن
            }
        });
    });
</script>
</body>

</html>
