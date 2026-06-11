<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ثبت رسید جدید - دکان دارمینو</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta content="" name="description" />
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <!-- Icons -->
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/flag-icons.css" rel="stylesheet" />
    <!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"
        rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css"
        rel="stylesheet" />

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <!-- Helpers -->
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
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
                    <form id="importStock" method="POST" action="{{ route('receipt.store') }}">
                        @csrf
                        <div class="container-fluid flex-grow-1 container-p-y">
                            <h4 class="py-3 mb-4">
                                <span class="text-muted fw-light">رسید انبار /</span>
                                ثبت رسید جدید
                            </h4>
                            <div class="col d-flex justify-content-end">
                                <div class="col-2 mr-auto text-end mb-2">
                                    <button class="btn btn-primary" type="submit">ثبت رسید</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="card col-12 mb-4">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label" for="type">نوع رسید</label>
                                                <select class="select2 form-select" id="type" name="type"
                                                    required>
                                                    <option value="0">انتخاب کنید</option>
                                                    <option value="1">خرید (داخلی)</option>
                                                    <option value="2">خرید (وارداتی)</option>
                                                    <option value="3">تولید</option>
                                                    <option value="4">سایر</option>
                                                    <option value="5">موجودی اول دوره</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="store_id">انتخاب انبار:</label>
                                                <select class="select2 form-select" name="store_id" id="store_id"
                                                    required>
                                                    <option value="0">انتخاب کنید...</option>
                                                    @foreach ($Stores as $store)
                                                        <option value="{{ $store->id }}">{{ $store->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label" for="number">شماره</label>
                                                <input type="text" class="form-control" id="number" name="number"
                                                    placeholder="شماره رسید" />
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label" for="date_fa">تاریخ</label>
                                                <input type="text" class="form-control" id="date_fa" name="date_fa"
                                                    placeholder="تاریخ رسید"
                                                    value="{{ Verta::today()->format('Y/m/d') }}" data-jdp />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="sender">تحویل دهنده</label>
                                                <input type="text" class="form-control" id="sender"
                                                    name="sender" placeholder="نام تحویل دهنده" />
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label class="form-label" for="moeen">حساب معین:</label>
                                                        <input type="text" class="form-control" id="moeen"
                                                            name="moeen" placeholder="حساب معین" />
                                                    </div>
                                                    <div class="col-md-8">
                                                        <label class="form-label" for="moeen2">حساب معین:</label>
                                                        <select class="select2 form-select" id="moeen2"
                                                            name="moeen2">
                                                            <option value="0">انتخاب کنید...</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label" for="driver">حمل کننده</label>
                                                <input type="text" class="form-control" id="driver"
                                                    name="driver" placeholder="نام حمل کننده" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="vehicle_plate">پلاک خودرو</label>
                                                <input type="text" class="form-control" id="vehicle_plate"
                                                    name="vehicle_plate" placeholder="مثال: 12 ع 345 ایران 67" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="scale_ticket_number">شماره قبض
                                                    باسکول</label>
                                                <input type="text" class="form-control" id="scale_ticket_number"
                                                    name="scale_ticket_number" placeholder="شماره قبض" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="waybill_number">شماره بارنامه</label>
                                                <input type="text" class="form-control" id="waybill_number"
                                                    name="waybill_number" placeholder="شماره بارنامه" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="gross_weight">وزن ناخالص</label>
                                                <input type="number" step="0.001"
                                                    class="form-control weighbridge-input" id="gross_weight"
                                                    name="gross_weight" placeholder="وزن با بار" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="tare_weight">پارسنگ</label>
                                                <input type="number" step="0.001"
                                                    class="form-control weighbridge-input" id="tare_weight"
                                                    name="tare_weight" placeholder="وزن خالی" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="net_weight">وزن خالص</label>
                                                <input type="number" step="0.001" class="form-control"
                                                    id="net_weight" name="net_weight"
                                                    placeholder="خودکار از باسکول" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="tozihat">توضیحات</label>
                                                <input type="text" class="form-control" id="tozihat"
                                                    name="tozihat" placeholder="توضیحات" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="weighing_notes">یادداشت باسکول</label>
                                                <input type="text" class="form-control" id="weighing_notes"
                                                    name="weighing_notes"
                                                    placeholder="توضیح اختلاف وزن یا شرایط توزین" />
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row pr_list_box d-none">
                                <div class="card col-12 mb-4">

                                    <div class="card-body">
                                        <div class="col-12">
                                            <div class="col d-flex justify-content-end">
                                                <div class="col-2 mr-auto text-end mb-2">
                                                    <button class="btn btn-info additem" type="button">افزودن محصول
                                                        جدید</button>
                                                </div>
                                            </div>
                                            @php($WarehouseLocationMode = $WarehouseLocationMode ?? 'optional_locations')
                                            <table id="prlist" class="table editfactor_table table-bordered">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th width="30">ردیف</th>
                                                        <th width="200">کد محصول</th>
                                                        <th width="300">نام محصول</th>
                                                        @if ($WarehouseLocationMode !== 'store_only')
                                                            <th width="220">مکان/قفسه</th>
                                                        @endif
                                                        <th>واحد اصلی</th>
                                                        <th>واحد فرعی</th>
                                                        <th width="360">ردیابی batch / serial</th>
                                                        <th>عملیات</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="item_1" data-item="1">
                                                        <td>1</td>
                                                        <td class="sku_box">
                                                            <select class="select2 form-select codes" name="codes[]">
                                                                <option value="">انتخاب کنید</option>

                                                            </select>
                                                        </td>
                                                        <td class="text-center pr_name_box">
                                                            <select class="select2 form-select pr_ids" name="pr_id[]">
                                                                <option value="">انتخاب کنید</option>

                                                            </select>
                                                        </td>
                                                        @if ($WarehouseLocationMode !== 'store_only')
                                                            <td class="location_box">
                                                                <select class="select2 form-select warehouse_locations"
                                                                    name="warehouse_location_id[]"
                                                                    @if ($WarehouseLocationMode === 'required_locations') required @endif>
                                                                    <option value="0">
                                                                        {{ $WarehouseLocationMode === 'required_locations' ? 'انتخاب مکان' : 'بدون مکان' }}
                                                                    </option>
                                                                    @foreach ($WarehouseLocations as $location)
                                                                        <option value="{{ $location->id }}"
                                                                            data-store-id="{{ $location->store_id }}">
                                                                            {{ $location->store->title ?? 'انبار' }} -
                                                                            {{ $location->path ?: $location->code }} -
                                                                            {{ $location->title }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                        @endif
                                                        <td class="unit_box">
                                                            <div class="row">
                                                                <div class="col-9">
                                                                    <input type="text" class="form-control unit"
                                                                        name="unit[]" value="" />
                                                                    <span class="entity_details"></span>
                                                                </div>
                                                                <span
                                                                    class="unit_text col-3 badge bg-label-dark rounded-pill text-center d-flex justify-content-center align-items-center">
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td class="sub_unit_box">
                                                            <div class="row">
                                                                <div class="col-9">
                                                                    <input type="text"
                                                                        class="form-control sub_unit"
                                                                        name="sub_unit[]" value="" />
                                                                </div><span
                                                                    class="sub_unit_text col-3 badge bg-label-dark rounded-pill text-center d-flex justify-content-center align-items-center">
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td class="trace_box">
                                                            <div class="row g-1">
                                                                <div class="col-4"><input
                                                                        class="form-control form-control-sm"
                                                                        name="batch_no[]" placeholder="Batch"></div>
                                                                <div class="col-4"><input
                                                                        class="form-control form-control-sm"
                                                                        name="lot_no[]" placeholder="Lot"></div>
                                                                <div class="col-4"><input
                                                                        class="form-control form-control-sm"
                                                                        name="serial_no[]" placeholder="Serial"></div>
                                                                <div class="col-6"><input
                                                                        class="form-control form-control-sm"
                                                                        type="date" name="manufactured_at[]"
                                                                        title="تاریخ تولید"></div>
                                                                <div class="col-6"><input
                                                                        class="form-control form-control-sm"
                                                                        type="date" name="expiry_date[]"
                                                                        title="تاریخ انقضا"></div>
                                                                <div class="col-4"><input
                                                                        class="form-control form-control-sm"
                                                                        name="color[]" placeholder="رنگ"></div>
                                                                <div class="col-4"><input
                                                                        class="form-control form-control-sm"
                                                                        name="size[]" placeholder="سایز"></div>
                                                                <div class="col-4"><input
                                                                        class="form-control form-control-sm"
                                                                        name="quality_grade[]" placeholder="Grade">
                                                                </div>
                                                                <div class="col-4"><input
                                                                        class="form-control form-control-sm"
                                                                        type="number" step="0.001" name="weight[]"
                                                                        placeholder="وزن"></div>
                                                                <div class="col-8"><input
                                                                        class="form-control form-control-sm"
                                                                        name="tracking_notes[]"
                                                                        placeholder="یادداشت trace"></div>
                                                            </div>
                                                        </td>

                                                        <td class="text-center">
                                                            <span class="removeitem" style="cursor:pointer;"><svg
                                                                    width="16" height="17" viewBox="0 0 16 17"
                                                                    fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                    <path
                                                                        d="M10.3248 6.03874L10.0313 13.4232M5.96873 13.4232L5.67515 6.03874M14.1328 3.40497C14.423 3.44763 14.7115 3.49276 15 3.54117M14.1328 3.40497L13.2267 14.7958C13.1897 15.2596 12.973 15.6928 12.62 16.0087C12.267 16.3247 11.8037 16.5001 11.3227 16.5H4.67733C4.19632 16.5001 3.73299 16.3247 3.37998 16.0087C3.02698 15.6928 2.81032 15.2596 2.77333 14.7958L1.86715 3.40497M14.1328 3.40497C13.1536 3.2618 12.1693 3.15315 11.1818 3.07923M1.86715 3.40497C1.57697 3.44681 1.28848 3.49194 1 3.54035M1.86715 3.40497C2.84642 3.26181 3.83074 3.15316 4.81818 3.07923M11.1818 3.07923V2.32766C11.1818 1.35948 10.4097 0.552119 9.40848 0.52176C8.46973 0.492747 7.53027 0.492747 6.59152 0.52176C5.5903 0.552119 4.81818 1.3603 4.81818 2.32766V3.07923M11.1818 3.07923C9.06376 2.92094 6.93624 2.92094 4.81818 3.07923"
                                                                        stroke="#FF0000" stroke-width="0.5"
                                                                        stroke-linecap="round"
                                                                        stroke-linejoin="round" />
                                                                </svg></span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Sticky Actions -->
                            </div>
                        </div>
                    </form>
                    <div class="container-fluid flex-grow-1 container-p-y">
                        <div class="row">
                            <div class="card py-3 col-12">
                                <div class="card-title">
                                    <h3>ورود دیتا با هوش مصنوعی</h3>
                                </div>
                                <p>از طریق فرم زیر میتوانید با گرفتن عکس از لیست محصولات فاکتور با هوش مصنوعی تمامی
                                    محصولات را وارد و ثبت نمایید.</p>
                                <p>توجه داشته باشید در صورتی که از قبل جدول لیست محصولات در بالا ایجاد شده و محصولات ثبت
                                    شده است با انتخاب تصویر و استخراج محصولات یکسان فقط به روز میشوند.</p>
                                <form id="receiptForm">
                                    @csrf
                                    <input class="form-control" type="file" name="receipt_photo" id="photo"
                                        accept="image/*">
                                    <button id="sbt_ai" type="button"
                                        class="btn btn-primary my-3 d-flex align-items-center gap-2">
                                        <span class="btn-text">ثبت تصویر و استخراج لیست</span>

                                        <span class="spinner-border spinner-border-sm d-none" role="status"
                                            aria-hidden="true"></span>
                                    </button>
                                    <img id="receiptPreview" class="receipt_placeholder col-12 d-none"
                                        src="" />
                                </form>
                            </div>
                        </div>
                    </div>
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
    <script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/cleavejs/cleave-phone.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
    <link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
    <script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
    <script>
        jalaliDatepicker.startWatch();
    </script>
    <script>
        // datatable (jquery)

        //getStore
        $('#store_id').on('change', function() {
            var store_id = $(this).val();
            syncWarehouseLocations(store_id, '.warehouse_locations');
            if (store_id) {
                $('.pr_list_box').removeClass('d-none');
                $.ajax({
                    url: "{{ route('products.productsByStore') }}/" + store_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        if (data) {
                            $('.pr_ids').empty();
                            $('.pr_ids').append(
                                '<option value="">انتخاب محصول</option>');
                            $.each(data, function(key, pr) {
                                $('.pr_ids').append('<option value="' + pr.id +
                                    '" data-code="' + pr.sku + '" data-unit="' + pr
                                    .pr_unit + '"  data-subunit="' + pr.pr_sub_unit +
                                    '" data-items="' + pr.pack_items + '" data-entity="' +
                                    pr.total_entity + '">' + pr.title + ' ' + (pr
                                        .display_name ?? '') + '</option>');
                            });

                            $('.codes').empty();
                            $('.codes').append(
                                '<option value="">انتخاب کد محصول</option>');
                            $.each(data, function(key, pr) {
                                $('.codes').append('<option value="' + pr.id + '" data-code="' +
                                    pr.sku + '" data-unit="' + pr.pr_unit +
                                    '"  data-subunit="' + pr.pr_sub_unit +
                                    '" data-items="' + pr.pack_items + '" data-entity="' +
                                    pr.total_entity + '">' + pr.sku + '</option>');
                            });

                            $('.pr_ids').select2({
                                closeOnSelect: true
                            });
                            $('.codes').select2({
                                closeOnSelect: true
                            });
                            $('.warehouse_locations').select2({
                                closeOnSelect: true
                            });

                        } else {
                            $('#store_id').empty();
                        }
                    }
                });
            } else {
                $('#store_id').empty();
                $('.pr_list_box').addClass('d-none');
            }
        });

        $('.pr_ids').on('change', function() {
            var pr_sku = $(this).find('option:selected').attr('data-code');
            var pr_unit = $(this).find('option:selected').attr('data-unit');
            var pr_sub_unit = $(this).find('option:selected').attr('data-subunit');
            var pack_items = $(this).find('option:selected').attr('data-items');
            var total_entity = $(this).find('option:selected').attr('data-entity');
            // $(this).parents('td').siblings('.sku_box').find('input').val(pr_sku);
            $(this).parents('td').siblings('.unit_box').find('.unit_text').html(pr_unit);
            $(this).parents('td').siblings('.sub_unit_box').find('.sub_unit_text').html(pr_sub_unit);


        });

        $('#prlist').on('keyup', '.unit', function() {
            let $row = $(this).closest('tr');
            // پیدا کردن select.pr_ids در td با کلاس pr_name_box
            let per_pack = $row.find('.pr_name_box .pr_ids option:selected').attr('data-items');

            if (!per_pack || isNaN(per_pack)) per_pack = 1; // حالت ایمنی

            let entity = parseFloat($(this).val()) || 0;
            let entity_sub_unit = entity / per_pack;

            let ent_sub_unit = hasNonZeroDecimal(entity_sub_unit) ? entity_sub_unit.toFixed(3) : entity_sub_unit;

            $row.find('.sub_unit_box .sub_unit').val(ent_sub_unit);
        });

        $('#prlist').on('keyup', '.sub_unit', function() {
            let $row = $(this).closest('tr');
            // پیدا کردن select.pr_ids در td با کلاس pr_name_box
            let per_pack = $row.find('.pr_name_box .pr_ids option:selected').attr('data-items');

            if (!per_pack || isNaN(per_pack)) per_pack = 1; // حالت ایمنی

            let entity_sub_unit = parseFloat($(this).val()) || 0;
            let entity = entity_sub_unit * per_pack;

            let ent = hasNonZeroDecimal(entity) ? entity.toFixed(3) : entity;

            $row.find('.unit_box .unit').val(ent);
        });

        function hasNonZeroDecimal(num) {
            return typeof num === 'number' && !isNaN(num) && num % 1 !== 0;
        }


        $(document).ready(function() {

            $('body').on('click', '.removeitem', function() {
                var listitems = $("#prlist tbody tr").length;
                if (listitems > 1) {
                    $(this).parents('tr').remove();
                }

            });
        });

        $(function() {
            $('.weighbridge-input').on('input', function() {
                const gross = parseFloat($('#gross_weight').val()) || 0;
                const tare = parseFloat($('#tare_weight').val()) || 0;
                if (gross > 0 && tare >= 0 && gross >= tare) {
                    $('#net_weight').val((gross - tare).toFixed(3));
                }
            });

            var
                dt_without_ajax_table = $('.datatables-direct-basic');

            // DataTable Direct
            // --------------------------------------------------------------------
            if (dt_without_ajax_table.length) {
                dt_without_ajax = dt_without_ajax_table.DataTable({
                    searching: false,
                    lengthChange: false,
                    ordering: false,
                    pageLength: 150,
                    autoWidth: false, // خاموش کردن تعیین عرض خودکار
                    columnDefs: [{
                            width: "30px",
                            targets: 0
                        },
                        {
                            width: "150px",
                            targets: 1
                        },
                        {
                            width: "250px",
                            targets: 2
                        },
                        {
                            width: "200px",
                            targets: 3
                        },
                        {
                            width: "50px",
                            targets: 5
                        }
                    ],
                });

                $('.datatables-direct-basic tbody').on('click', '.dropdown-item.delete-record', function() {
                    dt_without_ajax
                        .row($(this).parents('tr'))
                        .remove()
                        .draw();
                });
            }


        });

        $(document).ready(function() {
            $('.pack_weight_selector li').click(function() {
                var selected_weight = $(this).html();
                $('.selected_pack_weight').html(selected_weight);
                $('#pack_weight_txt').val(selected_weight);

            });
        });


        function duplicateLastTableRow() {
            const table = document.getElementById('prlist'); // Get the table by its ID
            if (!table) {
                console.error('Table with ID "myTable" not found.');
                return;
            }

            const tbody = table.querySelector('tbody'); // Get the tbody (or table itself if no tbody)
            if (!tbody) {
                console.error('Table does not contain a tbody element.');
                return;
            }

            const lastRow = tbody.lastElementChild; // Get the last child element (which should be the last tr)
            if (!lastRow || lastRow.tagName.toLowerCase() !== 'tr') {
                console.warn('No valid last table row found to duplicate.');
                return;
            }

            const clonedRow = lastRow.cloneNode(true); // Create a deep clone of the last row

            // Optional: Modify content of the cloned row if needed (e.g., clear input values)
            clonedRow.querySelectorAll('input').forEach(input => input.value = '');

            tbody.appendChild(clonedRow); // Append the cloned row to the tbody
        }
        $('.additem').on('click', function() {

            var $tableBody = $('#prlist tbody');
            var $lastTr = $tableBody.find('tr:last');

            // شماره ردیف جدید
            var rowCount = $tableBody.find('tr').length;
            var newIndex = rowCount + 1;

            // Destroy Select2 قبل از clone
            $lastTr.find('select.select2').select2('destroy');

            // Clone
            var $clonedTr = $lastTr.clone(false);

            /* =========================
               ریست مقادیر
            ========================== */
            $clonedTr.find('input, textarea').val('');
            $clonedTr.find('.unit_text, .sub_unit_text').html('');
            $clonedTr.find('.entity_details').html('');

            /* =========================
               اصلاح شماره ردیف (TD اول)
            ========================== */
            $clonedTr.find('td:first').text(newIndex);

            /* =========================
               اصلاح کلاس و data-item
            ========================== */
            $clonedTr
                .attr('data-item', newIndex)
                .removeClass(function(index, className) {
                    return (className.match(/item_\d+/g) || []).join(' ');
                })
                .addClass('item_' + newIndex);

            /* =========================
               حذف id و دیتاهای select2
            ========================== */
            $clonedTr.find('select').each(function() {
                $(this)
                    .removeAttr('id')
                    .removeAttr('data-select2-id')
                    .find('option')
                    .removeAttr('data-select2-id');
            });

            // Append به جدول
            $tableBody.append($clonedTr);

            // فعال‌سازی مجدد select2
            $clonedTr.find('select.select2').select2({
                closeOnSelect: true
            });
            $lastTr.find('select.select2').select2({
                closeOnSelect: true
            });
            syncWarehouseLocations($('#store_id').val(), '.warehouse_locations');

        });

        function syncWarehouseLocations(storeId, selector) {
            $(selector).each(function() {
                var currentValue = $(this).val();
                $(this).find('option[data-store-id]').each(function() {
                    $(this).prop('disabled', String($(this).data('store-id')) !== String(storeId));
                });
                if ($(this).find('option:selected').prop('disabled')) {
                    $(this).val('0');
                } else {
                    $(this).val(currentValue || '0');
                }
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).trigger('change.select2');
                }
            });
        }
    </script>

    <script>
        $(document).ready(function() {

            $('#prlist select.select2').each(function() {
                initSelect2WithEnter($(this));
            });

            // وقتی pr_ids عوض شد → codes رو ست کن
            $('table').on('change', '.pr_ids', function() {
                let value = $(this).val();
                let row = $(this).closest('tr');
                row.find('.codes').val(value).trigger('change.select2');
                $ //(this).trigger('change');
            });

            // وقتی codes عوض شد → pr_ids رو ست کن
            $('table').on('change', '.codes', function() {
                let value = $(this).val();
                let row = $(this).closest('tr');
                row.find('.pr_ids').val(value).trigger('change.select2');
                //row.find('.pr_ids').trigger('change');
            });

            document.querySelector('#importStock').addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault(); // جلوگیری از ثبت فرم
                }
            });

            $('#prlist').on('select2:select', 'select.select2', function(e) {
                let $cell = $(this).closest('td');
                let $row = $(this).closest('tr');
                let colIndex = $cell.index();
                let $rows = $('#prlist tbody tr');

                // هدف: خانه بعدی در همان ردیف
                let nextColIndex = colIndex + 1;
                let $target = $row.find(`td:eq(${nextColIndex}) :input`);

                // اگر سلول بعدی موجود نبود، برو به اولین سلول ردیف بعدی
                if (!$target.length) {
                    let rowIndex = $row.index();
                    if (rowIndex < $rows.length - 1) {
                        $target = $rows.eq(rowIndex + 1).find('td:eq(0) :input');
                    }
                }

                if ($target.length) {
                    $target.focus();

                    // اگر سلول بعدی Select2 باشه → بازش کن
                    if ($target.is('select.select2')) {
                        $target.select2('open');
                    }
                }
            });

            $('#prlist').on('keydown', ':input', function(e) {
                let $cell = $(this).closest('td');
                let $row = $(this).closest('tbody tr'); // فقط ردیف‌های دیتا، نه thead
                let rowIndex = $row.index();
                let colIndex = $cell.index();

                let $rows = $('#prlist tbody tr'); // همه ردیف‌ها داخل tbody
                let $target;

                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (rowIndex > 0) {
                        $target = $rows.eq(rowIndex - 1).find(`td:eq(${colIndex}) :input`);
                    }
                }
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (rowIndex < $rows.length - 1) {
                        $target = $rows.eq(rowIndex + 1).find(`td:eq(${colIndex}) :input`);
                    }
                }
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    if (colIndex > 0) {
                        $target = $row.find(`td:eq(${colIndex + 1}) :input`);
                    }
                }
                if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    if (colIndex < $row.find('td').length - 1) {
                        $target = $row.find(`td:eq(${colIndex - 1}) :input`);
                    }
                }

                if ($target && $target.length) {
                    $target.focus();

                    // اگر select2 باشه، بازش کن
                    if ($target.is('select.select2')) {
                        $target.select2('open');
                    }
                }
            });

            function initSelect2WithEnter($el) {
                $el.select2({
                        closeOnSelect: true
                    })
                    // بعد از انتخاب با ماوس یا Enter
                    .on('select2:select', function(e) {
                        let $this = $(this);
                        // بستن dropdown دستی
                        $this.select2('close');

                        // فوکوس به بعدی
                        focusNextInput(this);
                    });

                // کنترل Enter داخل input جستجوی Select2
                $el.on('select2:open', function() {
                    // پیدا کردن input سرچ داخلی
                    let searchInput = document.querySelector(
                        '.select2-container--open .select2-search__field');
                    if (searchInput) {
                        searchInput.addEventListener('keydown', function(ev) {
                            if (ev.key === 'Enter') {
                                ev.preventDefault();
                                // trigger انتخاب گزینه highlight شده
                                let $this = $el;
                                $this.select2('close');
                                focusNextInput($this[0]);
                            }
                        }, {
                            once: true
                        });
                    }
                });
            }

            // تابع فوکوس دادن به اینپوت بعدی
            function focusNextInput(current) {
                let $inputs = $('#prlist').find(':input:visible');
                let idx = $inputs.index(current);
                if (idx > -1 && $inputs.eq(idx + 1).length) {
                    $inputs.eq(idx + 1).focus();
                }
            }

        });
    </script>
    <script>
        $(document).ready(function() {
            $('#importStock').on('submit', function(e) {
                let isValid = true;

                // پاک کردن پیام‌های قبلی
                $('.error-message').remove();

                // چک کردن تمام فیلدهای این فرم که الزامی هستند
                $(this).find('input[required], select[required]').each(function() {
                    let $field = $(this);
                    let value = $.trim($field.val());

                    // شرط: اگر select باشد و مقدارش 0 باشد، یا اینپوت خالی باشد
                    if (($field.is('select') && value === '0') ||
                        ($field.is('input') && value === '')) {

                        isValid = false;

                        // ساخت پیام خطا
                        let errorMsg = $(
                            '<div class="error-message" style="color:red;font-size:12px;margin-top:4px;">این فیلد الزامی است</div>'
                        );

                        // درج پیام بعد از فیلد
                        if ($field.next('.select2').length) {
                            // اگر select2 هست، پیام رو بعد از container select2 بذاریم
                            $field.next('.select2').after(errorMsg);
                        } else {
                            $field.after(errorMsg);
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault(); // جلوگیری از ارسال فرم
                }
            });
        });
    </script>
    <style>
        #sbt_ai:disabled {
            opacity: 0.8;
            cursor: not-allowed;
        }
    </style>
    <script>
        document.getElementById('photo').addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (!file) return;

            // چک ساده نوع فایل
            if (!file.type.startsWith('image/')) {
                alert('لطفاً یک فایل تصویری انتخاب کنید');
                return;
            }

            const preview = document.getElementById('receiptPreview');

            // ساخت URL موقتی برای تصویر
            const imageUrl = URL.createObjectURL(file);

            // ست کردن تصویر
            preview.src = imageUrl;

            // نمایش placeholder
            preview.classList.remove('d-none');
        });
    </script>
    <script>
        $('#sbt_ai').on('click', function() {
            let fileInput = $('#photo')[0];
            let file = fileInput.files[0];

            if (!file) {
                alert('لطفا ابتدا یک تصویر انتخاب کنید.');
                return;
            }

            // نمایش اسپینر و غیرفعال کردن دکمه
            let $btn = $(this);
            let $spinner = $btn.find('.spinner-border');
            $btn.prop('disabled', true);
            $spinner.removeClass('d-none');

            let fd = new FormData();
            fd.append('receipt_photo', file);
            fd.append('_token', $('meta[name="csrf-token"]').attr('content'));

            $.ajax({
                url: "{{ route('stocks.importReceiptAi') }}",
                method: "POST",
                data: fd,
                processData: false,
                contentType: false,
                success: function(res) {
                    // بازگرداندن دکمه به حالت اول
                    $btn.prop('disabled', false);
                    $spinner.addClass('d-none');

                    if (!res.success) {
                        alert('خطا در پردازش تصویر');
                        return;
                    }

                    let aiData = res.ai_data || [];
                    let matched = res.matched_items || [];
                    let $tbody = $('#prlist tbody');

                    // --- مرحله 1: تهیه قالب (Template) ---
                    // ابتدا اولین سطر را میگیریم تا به عنوان الگو استفاده کنیم
                    let $firstRow = $tbody.find('tr:first');

                    // نکته مهم: Select2 را روی سطر الگو غیرفعال میکنیم تا کلون تمیز باشد
                    if ($firstRow.find('.select2').hasClass("select2-hidden-accessible")) {
                        $firstRow.find('.select2').select2('destroy');
                    }

                    let $templateRow = $firstRow.clone();

                    // دوباره Select2 سطر اول را فعال میکنیم (که ظاهرش خراب نشود)
                    $firstRow.find('.select2').select2({
                        closeOnSelect: true
                    });

                    // پاک کردن جدول و نمایش باکس
                    $tbody.empty();
                    $('.pr_list_box').removeClass('d-none');

                    // --- مرحله 2: حلقه روی داده‌های هوش مصنوعی ---
                    aiData.forEach(function(aiItem, i) {
                        let match = matched[i] || {};

                        // کلون کردن سطر جدید از روی الگو
                        let $row = $templateRow.clone();

                        // تنظیم شماره ردیف
                        $row.attr('data-item', i + 1);
                        $row.find('td:first').text(i + 1);

                        // پاک کردن مقادیر قبلی اینپوت‌ها
                        $row.find('input').val('');

                        // اضافه کردن سطر به جدول (قبل از اینیشیالایز کردن سلکت2 باید در DOM باشد)
                        $tbody.append($row);

                        // فعال‌سازی Select2 برای سطر جدید
                        $row.find('.select2').select2({
                            closeOnSelect: true
                        });

                        // --- مرحله 3: انتخاب محصول (در صورت مچ شدن) ---
                        if (match.status === 'matched' && match.product_id) {
                            let $prSelect = $row.find('.pr_ids');
                            let $codeSelect = $row.find('.codes');

                            // تابع کمکی برای انتخاب گزینه (اگر آپشن نبود، می‌سازد)
                            function setOption($el, id, text) {
                                if ($el.find("option[value='" + id + "']").length === 0) {
                                    let newOption = new Option(text, id, true, true);
                                    $el.append(newOption);
                                }
                                $el.val(id).trigger('change'); // تریگر برای سینک شدن کد و نام
                            }

                            // ست کردن نام محصول
                            setOption($prSelect, match.product_id, match.title);

                            // ست کردن کد محصول (معمولا با تریگر بالایی خودکار پر میشه ولی برای اطمینان)
                            // setOption($codeSelect, match.product_id, match.sku);
                        }

                        // --- مرحله 4: پر کردن مقادیر عددی ---
                        if (aiItem.pr_item !== null) {
                            // قرار دادن مقدار در اینپوت Unit
                            let $unitInput = $row.find('.unit');
                            $unitInput.val(aiItem.pr_item);

                            // تریگر کردن keyup برای اینکه محاسبات واحد فرعی (Sub Unit) انجام شود
                            $unitInput.trigger('keyup');
                        }

                        // اگر پک هم داشتیم (اختیاری)
                        if (aiItem.pr_pack !== null && aiItem.pr_pack !== undefined) {
                            // اگر نیاز بود کد مربوط به pr_pack را اینجا بگذارید
                        }
                    });
                },
                error: function(xhr) {
                    $btn.prop('disabled', false);
                    $spinner.addClass('d-none');
                    console.error(xhr.responseText);
                    alert('ارتباط با سرور برقرار نشد.');
                }
            });
        });
    </script>

</body>

</html>
