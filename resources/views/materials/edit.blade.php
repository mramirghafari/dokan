<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>ویرایش مواد اولیه - دکان دارمینو</title>
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
        @include('sections.sidebar')
        <!-- Layout container -->
        <div class="layout-page">
            @include('sections.header')
            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4">
                        <span class="text-muted fw-light">مواد اولیه /</span>
                        ویرایش مواد اولیه
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-4">
                                <form class="card-body" method="POST" action="{{ route('Materials.update', $Material->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    @include('errors.errors')
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label" for="name">نام مواد اولیه</label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{ $Material->name }}" placeholder="نام مواد اولیه" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="unit">واحد اصلی اندازه گیری</label>
                                            <select class="select2 form-select" id="unit" name="unit">
                                                <option value="">انتخاب کنید</option>
                                                <option @if($Material->unit = 'عدد') selected @endif>عدد</option>
                                                <option @if($Material->unit = 'کیلوگرم') selected @endif>کیلوگرم</option>
                                                <option @if($Material->unit = 'گرم') selected @endif>گرم</option>
                                                <option @if($Material->unit = 'مثقال') selected @endif>مثقال</option>
                                                <option @if($Material->unit = 'سی سی') selected @endif>سی سی</option>
                                                <option @if($Material->unit = 'میلی لیتر') selected @endif>میلی لیتر</option>
                                                <option @if($Material->unit = 'لیتر') selected @endif>لیتر</option>
                                                <option @if($Material->unit = 'تن') selected @endif>تن</option>
                                                <option @if($Material->unit = 'متر مکعب') selected @endif>متر مکعب</option>
                                                <option @if($Material->unit = 'گالن') selected @endif>گالن</option>
                                                <option @if($Material->unit = 'فله') selected @endif>فله</option>
                                                <option @if($Material->unit = 'متر') selected @endif>متر</option>
                                                <option @if($Material->unit = 'سانتی متر') selected @endif>سانتی متر</option>
                                                <option @if($Material->unit = 'میلی متر') selected @endif>میلی متر</option>
                                                <option @if($Material->unit = 'ویال') selected @endif>ویال</option>
                                                <option @if($Material->unit = 'بطری') selected @endif>بطری</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="sub_unit">واحد فرعی اندازه گیری</label>
                                            <select class="select2 form-select" name="sub_unit" id="sub_unit">
                                                <option @if($Material->sub_unit = 'کارتن') selected @endif>کارتن</option>
                                                <option @if($Material->sub_unit = 'جعبه') selected @endif>جعبه</option>
                                                <option @if($Material->sub_unit = 'سبد') selected @endif>سبد</option>
                                                <option @if($Material->sub_unit = 'نایلون') selected @endif>نایلون</option>
                                                <option @if($Material->sub_unit = 'گونی') selected @endif>گونی</option>
                                                <option @if($Material->sub_unit = 'فله') selected @endif>فله</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="entity">مقدار ورودی اصلی</label>
                                            <input type="text" class="form-control" id="entity" name="entity" value="{{ $Material->entity }}" placeholder="ورودی اصلی مواد اولیه" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="entity_sub_unit">مقدار ورودی فرعی</label>
                                            <input type="text" class="form-control" id="entity_sub_unit" name="entity_sub_unit" value="{{ $Material->entity_sub_unit }}" placeholder="ورودی فرعی مواد اولیه" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="pack_items">تعداد در واحد فرعی</label>
                                            <input type="text" class="form-control" id="pack_items" name="pack_items" value="{{ $Material->pack_items }}" placeholder="تعداد در واحد فرعی" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="pack_weight">وزن واحد فرعی: </label>
                                            <div class="input-group">
                                                <input aria-label="وزن واحد فرعی" class="form-control" id="pack_weight"  value="{{ $Material->pack_weight }}" name="pack_weight" type="text"/>
                                                <button aria-expanded="false" class="btn btn-outline-primary dropdown-toggle selected_pack_weight" data-bs-toggle="dropdown" type="button">
                                                    @if( $Material->pack_weight_unit != null) {{ $Material->pack_weight_unit }} @else واحد وزن @endif
                                                </button>
                                                <input type="hidden" name="pack_weight_unit"  id="pack_weight_txt">
                                                <ul class="dropdown-menu pack_weight_selector dropdown-menu-end">
                                                    <li class="dropdown-item">گرم</li>
                                                    <li class="dropdown-item">کیلوگرم</li>
                                                    <li class="dropdown-item">مثقال</li>
                                                    <li class="dropdown-item">سی سی</li>
                                                    <li class="dropdown-item">میلی لیتر</li>
                                                    <li class="dropdown-item">لیتر</li>
                                                    <li class="dropdown-item">تن</li>
                                                    <li class="dropdown-item">متر مکعب</li>
                                                    <li class="dropdown-item">گالن</li>
                                                    <li class="dropdown-item">فله</li>
                                                    <li class="dropdown-item">متر</li>
                                                    <li class="dropdown-item">سانتی متر</li>
                                                    <li class="dropdown-item">میلی متر</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            @php($Organ = DB::table('organizations')->where('id',auth()->user()->organization_id)->first())
                                            <label class="form-label" for="price">قیمت هر واحد اصلی به ریال</label>
                                            <input class="form-control" id="price" name="price" value="{{ $Material->price }}" placeholder="قیمت واحد اصلی" type="text"/>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="material_store_id">انتخاب انبار:</label>
                                            <select class="select2 form-select" name="material_store_id" id="material_store_id">
                                                <option value="0">انتخاب کنید...</option>
                                                @foreach($stores as $store)
                                                <option value="{{ $store->id }}" @if($Material->material_store_id == $store->id) selected @endif>{{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                    </div>
                                    <div class="pt-4">
                                        <button class="btn btn-primary me-sm-3 me-1" type="submit">ویرایش ماده اولیه</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">

                            <div class="card">
                                <div class="card-datatable table-responsive py-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>عنوان مواد اولیه</th>
                                            <th>واحد اصلی اندازه گیری</th>
                                            <th>واحد فرعی اندازه گیری</th>
                                            <th>وضعیت</th>
                                            <th>موجودی اصلی</th>
                                            <th>موجودی فرعی</th>
                                            <th>موجودی کل</th>
                                            <th>تاریخ ثبت</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php($x = 1)
                                        @foreach($Materials as $Material)
                                        <tr>
                                            <td><bdi>{{ $x }}</bdi></td>
                                            <td><a href="{{ route('Materials.edit',$Material->id) }}">{{ $Material->name }}</a></td>
                                            <td>{{ $Material->unit }}</td>
                                            <td>{{ $Material->sub_unit }}</td>
                                            <td>{{ $Material->price }}</td>
                                            <td>{{ $Material->entity }}</td>
                                            <td>{{ $Material->entity_sub_unit }}</td>
                                            <td>{{ intval($Material->entity_sub_unit * $Material->pack_items) + $Material->entity }}</td>
                                            <td>{{ Verta($Material->created_at)->format('Y-m-d - H:i:s') }}</td>
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

    $(document).ready(function() {
        $('.pack_weight_selector li').click(function(){
            var selected_weight = $(this).html();
            $('.selected_pack_weight').html(selected_weight);
            $('#pack_weight_txt').val(selected_weight);

        });
    });

</script>
</body>

</html>
