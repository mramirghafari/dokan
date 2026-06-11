<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>ویرایش فاکتور شماره {{ $PishFactor->invoiceID }} - دکان دارمینو</title>
    <meta content="" name="description"/>
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/>
    <!-- Icons -->
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/fonts/flag-icons.css" rel="stylesheet"/>
    <!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/>
    <!-- Vendors CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" rel="stylesheet"/>

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet"/>
    <!-- Helpers -->
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet"/>
</head>

<body>
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
                    <h4 class="row justify-content-between py-3 mb-2">
                        <div class="col-9">
                            <a href="{{ session('backlink') }}" class="text-muted fw-light">محصولات /</a>
                            ویرایش اقلام فاکتور
                        </div>
                        <div class="col-3 text-end">
                            <a href="{{ url()->previous() }}" class="btn btn-label-dark waves-effect" type="button">
                                بازگشت
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.75 19.5L8.25 12L15.75 4.5" stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </div>
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card">
                                <h5 class="card-header">به روزرسانی اقلام</h5>
                                <div class="col d-flex px-4 justify-content-end">
                                    <div class="col-2 mr-auto text-end">
                                        <button class="btn btn-primary additem" type="button">افزودن آیتم جدید</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('UpdateFactorItems',$PishFactor->id) }}">
                                        @csrf
                                        <div class="text-nowrap">
                                            <?php $Organ = App\Models\Organization::find($PishFactor->organization_id); ?>
                                            <table class="table editfactor_table table-bordered">
                                                <thead>
                                                <tr class="text-center">
                                                    <th width="40">ردیف</th>
                                                    <th>کد کالا</th>
                                                    <th>عنوان کالا</th>
                                                    <th>{{ $Organ->unit_order }}</th>
                                                    <th>{{ $Organ->sub_unit }}</th>
                                                    @if($Organ->type == 2)
                                                        <th>قیمت</th>
                                                    @endif
                                                    <th>تخفیف</th>
                                                    <th>عملیات</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @php($x = 1)
                                                @foreach($Items as $item)
                                                        <?php $pr = DB::table('products')->where('id', $item->pr_id)->first() ?>
                                                    <tr class="item_{{ $x }}" data-item="{{ $x }}">
                                                        <td>{{ $x }}</td>
                                                        <td class="text-center">
                                                            <select class="select2 form-select" name="old_pr_sku[]" >
                                                                <option value="">انتخاب کنید</option>
                                                                @foreach($Products as $pr_items)
                                                                    <option value="{{ $pr_items->id }}" @if($item->pr_id == $pr_items->id) selected @endif>{{ $pr_items->sku }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <select class="select2 form-select" name="old_pr_name[]" >
                                                                <option value="">انتخاب کنید</option>
                                                                @foreach($Products as $pr_items)
                                                                    <option value="{{ $pr_items->id }}" @if($item->pr_id == $pr_items->id) selected @endif>{{ $pr_items->title }} {{ $pr_items->display_name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" name="old_item[]" value="{{ intval($item->tedad) }}" />
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" name="old_pack[]" value="{{ intval($item->pack) }}" />
                                                        </td>
                                                        @if($Organ->type == 2)
                                                            <td>
                                                                <input type="number" class="form-control" name="old_price[]" value="{{ intval($item->price) }}" />
                                                            </td>
                                                        @endif

                                                        <td>
                                                            <input type="number" class="form-control" name="old_discount[]" value="{{ intval($item->discount) }}" />
                                                        </td>
                                                        <td class="text-center"><span class="removeitem" style="cursor:pointer;"><svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M10.3248 6.03874L10.0313 13.4232M5.96873 13.4232L5.67515 6.03874M14.1328 3.40497C14.423 3.44763 14.7115 3.49276 15 3.54117M14.1328 3.40497L13.2267 14.7958C13.1897 15.2596 12.973 15.6928 12.62 16.0087C12.267 16.3247 11.8037 16.5001 11.3227 16.5H4.67733C4.19632 16.5001 3.73299 16.3247 3.37998 16.0087C3.02698 15.6928 2.81032 15.2596 2.77333 14.7958L1.86715 3.40497M14.1328 3.40497C13.1536 3.2618 12.1693 3.15315 11.1818 3.07923M1.86715 3.40497C1.57697 3.44681 1.28848 3.49194 1 3.54035M1.86715 3.40497C2.84642 3.26181 3.83074 3.15316 4.81818 3.07923M11.1818 3.07923V2.32766C11.1818 1.35948 10.4097 0.552119 9.40848 0.52176C8.46973 0.492747 7.53027 0.492747 6.59152 0.52176C5.5903 0.552119 4.81818 1.3603 4.81818 2.32766V3.07923M11.1818 3.07923C9.06376 2.92094 6.93624 2.92094 4.81818 3.07923" stroke="#FF0000" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round"/>
    </svg></span></td>
                                                    </tr>
                                                    @php($x++)
                                                @endforeach
                                                </tbody>
                                            </table>
                                            <div class="pt-4">
                                                <button class="btn btn-success me-sm-3 me-1" type="submit">به روزرسانی فاکتور</button>
                                            </div>
                                        </div>
                                    </form>
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
<script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
<!-- endbuild -->
<script src="{{ asset('assets/') }}/vendor/libs/jquery-sticky/jquery-sticky.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
<
<!-- Main JS -->
<script src="{{ asset('assets/') }}/js/main.js"></script>
<!-- Page JS -->
<script src="{{ asset('assets/') }}/js/form-layouts.js"></script>

<script>
    $(document).ready(function() {
        $('.additem').click(function() {
            $('.editfactor_table tbody').append('<tr><td>1</td><td class="text-center"><select class="select2 form-select" name="pr_sku[]" ><option value="">انتخاب کنید</option>@foreach($Products as $pr_items)<option value="{{ $pr_items->id }}" >{{ $pr_items->sku }}</option>@endforeach</select></td><td class="text-center"><select class="select2 form-select" id="pr_name" name="pr_name[]" ><option value="">انتخاب کنید</option>@foreach($Products as $pr_items)<option value="{{ $pr_items->id }}">{{ $pr_items->title }} {{ $pr_items->display_name }}</option>@endforeach</select></td><td><input type="number" class="form-control" name="newitem[]" value="0" /></td><td><input type="number" class="form-control"  name="newpack[]" value="0" /></td><td><input type="number" class="form-control" min="0" max="100" name="newdis[]" value="0" /></td><td class="text-center"><span class="removeitem" style="cursor:pointer;"><svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M10.3248 6.03874L10.0313 13.4232M5.96873 13.4232L5.67515 6.03874M14.1328 3.40497C14.423 3.44763 14.7115 3.49276 15 3.54117M14.1328 3.40497L13.2267 14.7958C13.1897 15.2596 12.973 15.6928 12.62 16.0087C12.267 16.3247 11.8037 16.5001 11.3227 16.5H4.67733C4.19632 16.5001 3.73299 16.3247 3.37998 16.0087C3.02698 15.6928 2.81032 15.2596 2.77333 14.7958L1.86715 3.40497M14.1328 3.40497C13.1536 3.2618 12.1693 3.15315 11.1818 3.07923M1.86715 3.40497C1.57697 3.44681 1.28848 3.49194 1 3.54035M1.86715 3.40497C2.84642 3.26181 3.83074 3.15316 4.81818 3.07923M11.1818 3.07923V2.32766C11.1818 1.35948 10.4097 0.552119 9.40848 0.52176C8.46973 0.492747 7.53027 0.492747 6.59152 0.52176C5.5903 0.552119 4.81818 1.3603 4.81818 2.32766V3.07923M11.1818 3.07923C9.06376 2.92094 6.93624 2.92094 4.81818 3.07923" stroke="#FF0000" stroke-width="0.5" stroke-linecap="round" stroke-linejoin="round"/></svg></span></td></tr>');

            $(".editfactor_table .select2").select2();

        });

        $('body').on('click','.removeitem',function() {
            $(this).parents('tr').remove();
        });
    });

    $(document).ready(function() {

        // هر تغییری در هر select2 داخل جدول
        $('table').on('change', '.select2', function () {
            let selectedValue = $(this).val();
            let row = $(this).closest('tr');

            // همه select2های همین سطر رو سینک کن
            row.find('.select2').each(function() {
                $(this).val(selectedValue).trigger('change.select2');
            });
        });

        // وقتی آیتم جدید اضافه شد، select2 رو فعال کن
        $('table').on('DOMNodeInserted', function(e) {
            if ($(e.target).find('.select2').length) {
                $(e.target).find('.select2').select2();
            }
        });

    });
</script>

</body>

</html>
