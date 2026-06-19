<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>ویرایش انبارهای سامانه - دکان دارمینو</title>
    <meta content="" name="description"/>
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/>
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-fixedheader-bs5/fixedheader.bootstrap5.css') }}" />


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
        @include('sections.sidebar')
        <!-- Layout container -->
        <div class="layout-page">
           @include('sections.header')
            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4">
                        <span class="text-muted fw-light">اطلاعات پایه /</span>
                        ویرایش مشخصات انبار
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 col-md-5 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form action="{{ route('stores.update', $store->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="mb-3">
                                            <label class="form-label" for="title">عنوان انبار</label>
                                            <input type="text" class="form-control" name="title"
                                                   value="{{ $store->title }}" required id="title">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="description">توضیحات انبار</label>
                                            <input class="form-control" id="description" placeholder="توضیح" type="text"  name="description"
                                                   value="{{ $store->description }}" required/>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="code">کد انبار</label>
                                            <input class="form-control" id="code" placeholder="کد انبار" type="number"  name="code"
                                                   value="{{ $store->code }}" required/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="exampleInputEmail12">ارتباط با واحد پخش:</label>
                                            <select class="select2 form-select"
                                                    name="organization_id[]" multiple style="width: 100%;">
                                                <option value="">--هیچکدام--</option>
                                                @foreach ($organizations as $organization)
                                                    <option value="{{ $organization->id }}"
                                                        @if(is_array($store->organization_id) && in_array($organization->id, json_decode($store->organization_id))) selected @endif >
                                                        {{ $organization->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="pr_ids">ارتباط با محصولات/خدمات:</label>
                                            <select class="select2 form-select" id="pr_ids"
                                                    name="pr_ids[]" multiple style="width: 100%;">
                                                <option value="">--هیچکدام--</option>
                                                @foreach ($Products as $pr)
                                                    <option value="{{ $pr->id }}" @if(is_array($store->pr_ids) && in_array($pr->id, $store->pr_ids)) selected @endif >
                                                        {{ $pr->title }} {{ $pr->display_name }}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="pr_ids">موقعیت مکانی انبار lat:</label>
                                            <input type="text" class="form-control" name="lat" placeholder="lat..." value="{{ $store->lat }}" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="pr_ids">موقعیت مکانی انبار lang:</label>
                                            <input type="text" class="form-control" name="lang" placeholder="lang..." value="{{ $store->lang }}" />
                                        </div>
                                        <div class="mb-3">
                                            <label for="roles">نقش های کاربری مجاز:</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="roles" name="roles[]" multiple>
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->id }}"  {{ in_array($role->id , $store->roles->pluck('id')->toArray()) ? 'selected' : '' }} >                                                        {{ $role->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <input type="checkbox" name="isActive" id="isActive"
                                                {{ $store->isActive ? 'checked' : '' }}>
                                            <label for="isActive" class="cr">فعال</label>
                                        </div>
                                        <button class="btn btn-primary" type="submit">ویرایش انبار</button>
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
                                            <th>کد انبار</th>
                                            <th>عنوان انبار</th>
                                            <th>پنل</th>
                                            <th>شعبه</th>
                                            <th>وضعیت</th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php($x = 1)
                                        @foreach ($stores as $store)
                                            <tr>
                                                <td>{{ $x }}</td>
                                                <td><small>{{ $store->code }}</small></td>
                                                <td>{{ $store->title }}</td>

                                                <td><small>{{ $store->tenants->name }}</small></td>
                                                <td>
                                                    <small>@if(is_array(json_decode($store->organization_id)))
                                                            @php($Organs = App\Models\Organization::whereIn('id',json_decode($store->organization_id))->get())
                                                            @foreach($Organs as $organ)
                                                                {{ $organ->title }} ,
                                                            @endforeach
                                                        @endif</small>
                                                </td>
                                                <td>
                                                    @if ($store->isActive == 1)
                                                        <div class='badge badge-success'>فعال</div>
                                                    @else
                                                        <div class='badge badge-danger'>غیرفعال</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('stores.edit', $store->id) }}"
                                                       style="font-size:20px;float: right;margin-left:5px"><x-ui.icon name="fa-edit" /></a>
                                                    {{-- <form action="{{ route('stores.destroy', $store->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('آیا از حذف رکورد مورد نظر اطمینان دارید؟');">
                                                        @method('delete')
                                                        @csrf
                                                        <button type="submit"
                                                            style="font-size:20px;border: none;background-color: transparent;float: right;">
                                                            <x-ui.icon name="fa-trash" />
                                                        </button>
                                                    </form> --}}
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
                @include('sections.footer')
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
    // datatable (jquery)
    $('.basicdata').addClass('open')
    $('.basicdata .stores').addClass('active open')
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
</body>

</html>
