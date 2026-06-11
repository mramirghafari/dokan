<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>حواله خروج انبار - دکان دارمینو</title>
    <meta content="" name="description"/>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/>
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/fonts/flag-icons.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet"/>
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <link href="{{ asset('assets/') }}/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

    <!-- FixedHeader CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/4.0.1/css/fixedHeader.bootstrap5.min.css">
    <link href="{{ asset('assets/') }}/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-table@1.24.2/dist/bootstrap-table.min.css">

    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet"/>
    <style>
        .dt-search {
            display: inline-block;
            background: #D0E4D3;
            width: 300px;
            padding: 10px;
            border-top-right-radius: 10px;
            border-top-left-radius: 10px;
            margin-top: 20px;
        }
    </style>
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
                        <span class="text-muted fw-light">مسیرها /</span>
                        حواله خروج انبار
                    </h4>
                    <form action="{{ url()->current() }}" method="GET">
                        @csrf
                        <div class="col-12 card mb-4">
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="form-group col-6 col-md-3 mb-3">
                                        <label for="city_id">نمایش بر اساس شهر:</label>
                                        <select class="select2 form-select" multiple id="city_id" name="city_id[]">
                                            <option value="">انتخاب کنید</option>
                                            @foreach ($Cities as $city)
                                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-6 col-md-3 mb-3">
                                        <label for="driver_id">نمایش بر اساس راننده:</label>
                                        <select class="select2 form-select" id="driver_id" name="driver_id[]">
                                            <option value="">انتخاب کنید</option>
                                            @foreach ($Drivers as $driver)
                                                <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-6 col-md-3 mb-3">
                                        <label>نمایش بر اساس منطقه:</label>
                                        <input type="text" class="form-control" name="from_date" data-jdp>
                                    </div>
                                    <div class="form-group col-6 col-md-3 mb-3">
                                        <label>نمایش بر اساس مسیر:</label>

                                    </div>
                                    <div class="form-group col-6 col-md-3 mb-3">
                                        <label>نمایش از تاریخ:</label>
                                        <input type="text" class="form-control" name="from_date" data-jdp>
                                    </div>
                                    <div class="form-group col-6 col-md-3 mb-3">
                                        <label>نمایش تا تاریخ:</label>
                                        <input type="text" class="form-control" name="to_date" data-jdp>
                                    </div>
                                    <div class="form-group col-6 col-md-3">
                                        <label>نمایش از تاریخ تحویل:</label>
                                        <input type="text" class="form-control" name="delivery_from_date" data-jdp>
                                    </div>
                                    <div class="form-group col-6 col-md-3">
                                        <label>نمایش تا تاریخ تحویل:</label>
                                        <input type="text" class="form-control" name="delivery_to_date" data-jdp>
                                    </div>
                                    <div class="form-group col-6 col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-info">فیلتر تاریخ</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card-datatable pt-0">
                                <style>
                                    table.ps th:not(.now), table.ps td:not(.now) { min-width: 70px !important;}
                                </style>
                                <table id="example" class="table table-bordered table-striped table-hover w-100">
                                    <thead>
                                    <?php
                                    if(is_array(json_decode(auth()->user()->organization_id))) {
                                        $UserOrgan = implode('', json_decode(auth()->user()->organization_id));
                                    }else {
                                        $UserOrgan = auth()->user()->organization_id;
                                    }

                                    ?>
                                    <tr class="text-center align-middle">
                                        <td><input type="checkbox" class="checkallactions" value="1" /></td>
                                        <th class="now">شماره</th>
                                        <th>مشتری</th>
                                        <th>تاریخ تحویل</th>
                                        <th>آدرس مشتری</th>
                                        <th>توضیحات</th>
                                        @if($UserOrgan != 3)
                                        <th>جمع کل</th>
                                        <th>جمع جزء</th>
                                        @endif
                                        @php($prUnit = null)
                                        @php($prSubUnit = null)

                                        @if($UserOrgan == 3)
                                            @foreach($uniqueItems as $pritem)
                                            <th style="min-width: 75px !important;font-size: 12px !important;text-align: center">
                                                {{ $pritem->product_name }} <hr class="m-0" style="height: 3px;color: #248230;" />
                                                    @if($pritem->sku != null) {{ $pritem->sku  }} <hr class="m-0" style="height: 3px;color: #248230;" /> @endif
                                                {{ $pritem->pr_unit }}
                                            </th>
                                            @endforeach
                                        @else
                                            @foreach($uniqueItems as $pritem)
                                                @if($pritem->item_sale_status == 1)
                                                    <th style="min-width: 75px !important;font-size: 12px !important;text-align: center">
                                                        {{ $pritem->product_name }} <hr class="m-0" style="height: 3px;color: #248230;" />
                                                        @if($pritem->sku != null) {{ $pritem->sku  }} <hr class="m-0" style="height: 3px;color: #248230;" /> @endif
                                                        {{ $pritem->pr_unit }}
                                                    </th>
                                                @endif
                                                @if($pritem->pack_sale_status == 1)
                                                        <th style="min-width: 75px !important;font-size: 12px !important;text-align: center">
                                                            {{ $pritem->product_name }} <hr class="m-0" style="height: 3px;color: #248230;" />
                                                            @if($pritem->sku != null) {{ $pritem->sku  }} <hr class="m-0" style="height: 3px;color: #248230;" /> @endif
                                                            {{ $pritem->pr_sub_unit }}
                                                        </th>
                                                @endif
                                            @endforeach
                                        @endif
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php($x = 1)
                                    @php($JamAllPacks = 0)
                                    @php($JamAlltedad = 0)
                                    @foreach($Factors as $factor)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="actions" name="item_{{ $factor->id }}" value="1" />
                                            </td>
                                            <th>{{ $x }}</th>
                                            <td><a href="{{ route('pishFactorInfo', $factor->id) }}">
                                                    {{ $factor->customer->name }} / {{ $factor->customer->tablo }}
                                                </a></td>
                                            <td>{{ $factor->recive_date }}</td>
                                            <td>{{ $factor->customer->address }}</td>
                                            <td>{{ $factor->tozihat }}</td>
                                            @if($UserOrgan != 3)
                                            <td>
                                                @php($Jamkol = 0)
                                                @php($Jamtedad = 0)
                                                @foreach($uniqueItems as $pritem)
                                                    <?php
                                                        $itemKey = $factor->id . '_' . $pritem->pr_id;
                                                        $CheckFactorItem = optional($factorItems->get($itemKey))->first() ?? null;

                                                        ?>
                                                    @if($CheckFactorItem)
                                                        @if($CheckFactorItem->pack > 0) @php($Jamkol += $CheckFactorItem->pack) @endif
                                                        @if($CheckFactorItem->tedad > 0) @php($Jamtedad += $CheckFactorItem->tedad) @endif
                                                    @endif
                                                @endforeach
                                                {{ $Jamkol }} <br /> @php($JamAllPacks += $Jamkol)
                                            </td>
                                            <td>
                                                {{ $Jamtedad }} @php($JamAlltedad += $Jamtedad)
                                            </td>
                                            @endif
                                            @foreach($uniqueItems as $pritem)
                                                <?php
                                                    $itemKey = $factor->id . '_' . $pritem->pr_id;
                                                    $CheckFactorItem = optional($factorItems->get($itemKey))->first() ?? null;
                                                ?>
                                                @if($pritem->item_sale_status == 1)
                                                    <td> {{  isset($CheckFactorItem->tedad) ? $CheckFactorItem->tedad : 0 }}</td>
                                                @endif
                                                @if($pritem->pack_sale_status == 1)
                                                    <td> {{  isset($CheckFactorItem->pack) ? $CheckFactorItem->pack : 0 }}</td>
                                                @endif



                                            @endforeach
                                        </tr>
                                        @php($x++)
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="card">
                            <p><strong>جمع کل: {{ $JamAllPacks }} {{ $prSubUnit }} و {{ $JamAlltedad }} {{ $prUnit }}</strong></p>
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
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/jquery-sticky/jquery-sticky.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>

<script src="{{ asset('assets/') }}/js/main.js"></script>
<script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
<link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
<script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
<script>
    $(document).ready(function() {
        jalaliDatepicker.startWatch();
    });
</script>
<style>
    .dt-scroll-body table thead {
        display: none !important;
    }
    #example_length {
        text-align: left;
    }
    .dt-buttons {
        float: right;
        flex-direction: row-reverse;
    }
</style>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<!-- Buttons Extension -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
<!-- FixedHeader -->
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css">
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

<script>
    $('.anbarotozi').addClass('open')
    $('.anbarotozi .Outgoing_by_items').addClass('active open');
</script>
<script>
    $(document).ready(function() {

        let table = $('#example').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 50,
            autoWidth: false,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/fa.json'
            },
            dom: '<"row"<"col-md-6"l><"col-md-6 text-end"B>>frtip',
            buttons: [
                { extend: 'copy', text: 'کپی' },
                { extend: 'excel', text: 'اکسل' },
                { extend: 'print', text: 'پرینت' },
                { extend: 'colvis', text: 'نمایش ستون‌ها' }
            ],
            columnDefs: [
                { targets: 0, width: '20px', className: 'text-center' },
            ]
        });
    });
</script>
</body>
</html>
