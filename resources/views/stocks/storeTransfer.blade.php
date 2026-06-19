<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>انتقال بین انبار - دکان دارمینو</title>
    <meta content="" name="description" />
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css"
        rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css"
        rel="stylesheet" />

    <!-- Page CSS -->
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
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
                    <form method="POST" action="{{ route('receipt.storeTransfer') }}">
                        @csrf
                        <div class="container-fluid flex-grow-1 container-p-y">
                            <h4 class="py-3 mb-4">
                                <span class="text-muted fw-light">رسید انبار /</span>
                                انتقال بین انبار
                            </h4>
                            <div class="col d-flex justify-content-end">
                                <div class="col-2 mr-auto text-end mb-2">
                                    <button class="btn btn-primary sbt" type="submit" disabled>ثبت رسید</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="card col-12 mb-4">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <label class="form-label">نوع رسید</label>
                                                    <div class="form-check col form-check-primary mt-3">
                                                        <input checked class="form-check-input" id="transfer"
                                                            name="type" type="radio" value="6" />
                                                        <label class="form-check-label " for="transfer">انتقال بین
                                                            انبار</label>
                                                    </div>
                                                    <div class="form-check col form-check-primary mt-3">
                                                        <input class="form-check-input" id="sale" name="type"
                                                            type="radio" value="7" />
                                                        <label class="form-check-label " for="sale">فروش</label>
                                                    </div>
                                                    <div class="form-check col form-check-primary mt-3">
                                                        <input class="form-check-input" id="masraf" name="type"
                                                            type="radio" value="8" />
                                                        <label class="form-check-label " for="masraf">مصرف</label>
                                                    </div>
                                                    <div class="form-check col form-check-primary mt-3">
                                                        <input class="form-check-input" id="sayer" name="type"
                                                            type="radio" value="9" />
                                                        <label class="form-check-label " for="sayer">سایر</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6"></div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="from_store_id">انتخاب انبار:</label>
                                                <select class="select2 form-select" name="store_id" id="from_store_id">
                                                    <option value="0">انتخاب کنید...</option>
                                                    @foreach ($Stores as $store)
                                                        <option value="{{ $store->id }}">{{ $store->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="to_store_id">انتخاب انبار مقصد:</label>
                                                <select class="select2 form-select" name="to_store_id"
                                                    id="to_store_id">
                                                    <option value="0">انتخاب کنید...</option>
                                                    @foreach ($Stores as $store)
                                                        <option value="{{ $store->id }}">{{ $store->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label" for="number">شماره</label>
                                                <input type="text" class="form-control" id="number"
                                                    name="number" placeholder="شماره رسید" />
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label" for="date_fa">تاریخ</label>
                                                <input type="text" class="form-control" id="date_fa"
                                                    name="date_fa" placeholder="تاریخ رسید" data-jdp />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="sender">سفارش محصول</label>
                                                <input type="text" class="form-control" id="sender"
                                                    name="sender" placeholder="نام تحویل دهنده" />
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
                                                    <button class="btn btn-warning additem" type="button">+ ثبت
                                                        محصول</button>
                                                </div>
                                            </div>
                                            @php($WarehouseLocationMode = $WarehouseLocationMode ?? 'optional_locations')
                                            <table id="prlist" class="table editfactor_table table-bordered">
                                                <thead>
                                                    <tr class="text-center">
                                                        <th width="30" class="smallcol">ردیف</th>
                                                        <th width="80" style="width: 80px">کد محصول</th>
                                                        <th width="300">نام محصول</th>
                                                        @if ($WarehouseLocationMode !== 'store_only')
                                                            <th width="220">مکان مبدا</th>
                                                            <th width="220">مکان مقصد</th>
                                                        @endif
                                                        <th>واحد اصلی</th>
                                                        <th>واحد فرعی</th>
                                                        <th width="30" style="width: 30px !important;">عملیات</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="item_1" data-item="1">
                                                        <td class="smallcol">1</td>
                                                        <td class="sku_box" style="width: 80px">
                                                            <input type="number" class="form-control" name="codes[]"
                                                                value="" />
                                                        </td>
                                                        <td class="text-center">
                                                            <select class="select2 form-select pr_ids" name="pr_id[]">
                                                                <option value="">انتخاب کنید</option>

                                                            </select>
                                                        </td>
                                                        @if ($WarehouseLocationMode !== 'store_only')
                                                            <td class="from_location_box">
                                                                <select
                                                                    class="select2 form-select from_warehouse_locations"
                                                                    name="from_warehouse_location_id[]"
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
                                                            <td class="to_location_box">
                                                                <select
                                                                    class="select2 form-select to_warehouse_locations"
                                                                    name="to_warehouse_location_id[]"
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
                                                            <div class="row px-3">
                                                                <div class="col-9">
                                                                    <input type="text" class="form-control unit"
                                                                        name="unit[]" value="" />
                                                                    <span class="entity_details"
                                                                        style="font-size: 11px"></span>
                                                                </div>
                                                                <span
                                                                    class="unit_text col-3 badge bg-label-dark rounded-pill text-center d-flex justify-content-center align-items-center"
                                                                    style="height: 30px"> </span>
                                                            </div>
                                                        </td>
                                                        <td class="sub_unit_box">
                                                            <div class="row px-3">
                                                                <div class="col-9">
                                                                    <input type="text"
                                                                        class="form-control sub_unit"
                                                                        name="sub_unit[]" value="" />
                                                                    <span class="sub_entity_details"
                                                                        style="font-size: 11px"></span>
                                                                </div><span
                                                                    class="sub_unit_text col-3 badge bg-label-dark rounded-pill text-center d-flex justify-content-center align-items-center"
                                                                    style="height: 30px"> </span>
                                                            </div>
                                                        </td>

                                                        <td class="text-center" width="30"
                                                            style="width: 30px !important; max-width: 30px !important;">
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
                    </form>
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
    <link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
    <script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
    <script>
        jalaliDatepicker.startWatch({
            time: true
        });
    </script>
    <script>
        // datatable (jquery)

        //getStore
        $('#from_store_id').on('change', function() {
            var store_id = $(this).val();
            syncWarehouseLocations(store_id, '.from_warehouse_locations');
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
                                    pr.total_entity + '" data-subentity="' + pr
                                    .sub_unit_from_entity + '">' + pr.title + ' ' + (pr
                                        .display_name ?? '') + '</option>');
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

        $('#to_store_id').on('change', function() {
            syncWarehouseLocations($(this).val(), '.to_warehouse_locations');
        });

        $('.pr_ids').on('change', function() {
            $('.sbt').removeAttr('disabled');
            var pr_sku = $(this).find('option:selected').attr('data-code');
            var pr_unit = $(this).find('option:selected').attr('data-unit');
            var pr_sub_unit = $(this).find('option:selected').attr('data-subunit');
            var pack_items = $(this).find('option:selected').attr('data-items');
            var total_entity = $(this).find('option:selected').attr('data-entity');
            var total_subentity = $(this).find('option:selected').attr('data-subentity');
            if (parseInt(total_entity) > 0) {
                total_entity = total_entity;
            } else {
                total_entity = 0;
            }
            if (parseInt(total_subentity) > 0) {
                total_subentity = total_subentity;
            } else {
                total_subentity = 0;
            }

            $(this).parents('td').siblings('.sku_box').find('input').val(pr_sku);
            $(this).parents('td').siblings('.unit_box').find('.unit_text').html(pr_unit);
            $(this).parents('td').siblings('.sub_unit_box').find('.sub_unit_text').html(pr_sub_unit);
            $(this).parents('td').siblings('.unit_box').find('.entity_details').html('موجودی واحد اصلی: ' +
                parseInt(total_entity));
            $(this).parents('td').siblings('.sub_unit_box').find('.sub_entity_details').html('موجودی واحد فرعی: ' +
                parseInt(total_subentity));

            $(this).parents('td').siblings('.unit_box').find('input').on('input', function() {
                let v = $(this).val().replace(/[^0-9.]/g, ''); // فقط رقم و نقطه
                v = v.replace(/(\..*)\./g, '$1'); // فقط یک نقطه
                if (/\./.test(v)) v = v.replace(/(\.\d{3})\d+$/, '$1'); // اعشار حداکثر 3 رقم
                if (v && parseFloat(v) > total_entity) v = total_entity.toString(); // سقف mymax
                $(this).val(v);
            }).on('blur', function() {
                let n = parseFloat($(this).val());
                if (!isNaN(n)) $(this).val(parseFloat(n.toFixed(3)).toString()); // حذف .000
            });
            $(this).parents('td').siblings('.sub_unit_box').find('input').on('input', function() {
                let v = $(this).val().replace(/[^0-9.]/g, ''); // فقط رقم و نقطه
                v = v.replace(/(\..*)\./g, '$1'); // فقط یک نقطه
                if (/\./.test(v)) v = v.replace(/(\.\d{3})\d+$/, '$1'); // اعشار حداکثر 3 رقم
                if (v && parseFloat(v) > total_subentity) v = total_subentity.toString(); // سقف mymax
                $(this).val(v);
            }).on('blur', function() {
                let n = parseFloat($(this).val());
                if (!isNaN(n)) $(this).val(parseFloat(n.toFixed(3)).toString()); // حذف .000
            });

        });

        $('.unit').on('keyup', function() {
            var entity = $(this).val();
            var per_pack = $('.pr_ids').find('option:selected').attr('data-items');
            var entity_sub_unit = entity / per_pack
            if (hasNonZeroDecimal(entity_sub_unit)) {
                var ent_sub_unit = entity_sub_unit.toFixed(3);
            } else {
                var ent_sub_unit = entity_sub_unit;
            }

            $(this).parents('td').siblings('.sub_unit_box').find('.sub_unit').val(ent_sub_unit);
        })

        $('.sub_unit').on('keyup', function() {
            var entity_sub_unit = $(this).val();
            var per_pack = $('.pr_ids').find('option:selected').attr('data-items');
            var entity = entity_sub_unit * per_pack;

            if (hasNonZeroDecimal(entity)) {
                var ent = entity.toFixed(3);
            } else {
                var ent = entity;
            }
            $(this).parents('td').siblings('.sub_unit_box').find('.unit').val(ent);
        })

        function hasNonZeroDecimal(num) {
            // Check if it's a number and not an integer (i.e., has a decimal part)
            // And then check if the decimal part itself is not zero
            return typeof num === 'number' && num % 1 !== 0;
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
            var
                dt_without_ajax_table = $('.datatables-direct-basic');

            // DataTable Direct
            // --------------------------------------------------------------------
            if (dt_without_ajax_table.length) {
                dt_without_ajax = dt_without_ajax_table.DataTable({
                    searching: false,
                    lengthChange: false,
                    ordering: false,
                    pageLength: 5,
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
            var $tableBody = $('#prlist');
            var $lastTr = $tableBody.find('tr:last');


            // Destroy Select2 on the original row's dropdowns before cloning
            $lastTr.find('select.select2').select2('destroy');

            var $clonedTr = $lastTr.clone(true);

            // Clear values and remove IDs from the cloned row
            $clonedTr.find('input, textarea').val('');
            $clonedTr.find('select').each(function() {
                $(this).removeAttr('id').removeAttr('data-select2-id');
                $(this).find('option').removeAttr('data-select2-id');
            });

            $tableBody.append($clonedTr);

            // Re-initialize Select2 on the cloned row's dropdowns
            $clonedTr.find('select.select2').select2();

            // Re-initialize Select2 on the original row's dropdowns
            $lastTr.find('select.select2').select2();
            syncWarehouseLocations($('#from_store_id').val(), '.from_warehouse_locations');
            syncWarehouseLocations($('#to_store_id').val(), '.to_warehouse_locations');
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
</body>

</html>
