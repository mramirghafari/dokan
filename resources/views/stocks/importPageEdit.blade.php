<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ویرایش رسید انبار - دکان دارمینو</title>
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
                    <form method="POST" id="importStock" action="{{ route('receipt.update', $receipt->id) }}">
                        @csrf
                        @method('PATCH')
                        <div class="container-fluid flex-grow-1 container-p-y">
                            <h4 class="py-3 mb-4">
                                <span class="text-muted fw-light">رسید انبار /</span>
                                ویرایش رسید {{ $store->title }}
                            </h4>
                            <div class="col d-flex justify-content-end"
                                style="position: sticky;top: 85px;z-index: 9999999;">
                                <div class="col-2 mr-auto text-end mb-2 me-4">
                                    <button class="btn btn-primary" type="submit">بروزرسانی رسید</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="card col-12 mb-4">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label" for="type">نوع رسید</label>
                                                <select class="select2 form-select" id="type" name="type">
                                                    <option value="0">انتخاب کنید</option>
                                                    <option value="1"
                                                        @if ($receipt->type == 1) selected @endif>خرید (داخلی)
                                                    </option>
                                                    <option value="2"
                                                        @if ($receipt->type == 2) selected @endif>خرید (وارداتی)
                                                    </option>
                                                    <option value="3"
                                                        @if ($receipt->type == 3) selected @endif>تولید</option>
                                                    <option value="4"
                                                        @if ($receipt->type == 4) selected @endif>سایر</option>
                                                    <option value="5"
                                                        @if ($receipt->type == 5) selected @endif>موجودی اول
                                                        دوره</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="store_id">انتخاب انبار:</label>
                                                <select class="select2 form-select" name="store_id" id="store_id">
                                                    <option value="0">انتخاب کنید...</option>
                                                    @foreach ($Stores as $store)
                                                        <option value="{{ $store->id }}"
                                                            @if ($receipt->store_id == $store->id) selected @endif>
                                                            {{ $store->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label" for="input_id">شماره</label>
                                                <input type="text" class="form-control" id="input_id"
                                                    name="input_id" placeholder="شماره رسید"
                                                    value="{{ $receipt->number ? $receipt->number : 100 + $receipt->id }}" />
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label" for="input_id">تاریخ</label>
                                                <input type="text" class="form-control" id="input_id" name="date_fa"
                                                    placeholder="تاریخ رسید" data-jdp
                                                    value="{{ $receipt->date_fa ? $receipt->date_fa : verta($receipt->created_at)->format('Y/m/d') }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="sender">تحویل دهنده</label>
                                                <input type="text" class="form-control" id="sender"
                                                    name="sender" placeholder="نام تحویل دهنده"
                                                    value="{{ $receipt->sender }}" />
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label class="form-label" for="sender">حساب معین:</label>
                                                        <input type="text" class="form-control" id="moeen"
                                                            name="moeen" placeholder="حساب معین"
                                                            value="{{ $receipt->moeen }}" />
                                                    </div>
                                                    <div class="col-md-8">
                                                        <label class="form-label" for="sender">حساب معین:</label>
                                                        <select class="select2 form-select" id="moeen">
                                                            <option>انتخاب کنید...</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label" for="driver">حمل کننده</label>
                                                <input type="text" class="form-control" id="driver"
                                                    name="driver" placeholder="نام حمل کننده"
                                                    value="{{ $receipt->driver }}" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="vehicle_plate">پلاک خودرو</label>
                                                <input type="text" class="form-control" id="vehicle_plate"
                                                    name="vehicle_plate" placeholder="مثال: 12 ع 345 ایران 67"
                                                    value="{{ $receipt->vehicle_plate }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="scale_ticket_number">شماره قبض
                                                    باسکول</label>
                                                <input type="text" class="form-control" id="scale_ticket_number"
                                                    name="scale_ticket_number" placeholder="شماره قبض"
                                                    value="{{ $receipt->scale_ticket_number }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="waybill_number">شماره بارنامه</label>
                                                <input type="text" class="form-control" id="waybill_number"
                                                    name="waybill_number" placeholder="شماره بارنامه"
                                                    value="{{ $receipt->waybill_number }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="gross_weight">وزن ناخالص</label>
                                                <input type="number" step="0.001"
                                                    class="form-control weighbridge-input" id="gross_weight"
                                                    name="gross_weight" placeholder="وزن با بار"
                                                    value="{{ $receipt->gross_weight }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="tare_weight">پارسنگ</label>
                                                <input type="number" step="0.001"
                                                    class="form-control weighbridge-input" id="tare_weight"
                                                    name="tare_weight" placeholder="وزن خالی"
                                                    value="{{ $receipt->tare_weight }}" />
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label" for="net_weight">وزن خالص</label>
                                                <input type="number" step="0.001" class="form-control"
                                                    id="net_weight" name="net_weight" placeholder="خودکار از باسکول"
                                                    value="{{ $receipt->net_weight }}" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="desc">توضیحات</label>
                                                <input type="text" class="form-control" id="desc"
                                                    name="tozihat" placeholder="توضیحات"
                                                    value="{{ $receipt->toziht }}" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label" for="weighing_notes">یادداشت باسکول</label>
                                                <input type="text" class="form-control" id="weighing_notes"
                                                    name="weighing_notes"
                                                    placeholder="توضیح اختلاف وزن یا شرایط توزین"
                                                    value="{{ $receipt->weighing_notes }}" />
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row pr_list_box @if (count($Depots) == 0) d-none @endif ">
                                <div class="card col-12 mb-4">

                                    <div class="card-body">
                                        <div class="col-12  position-relative">
                                            <div class="col d-flex justify-content-end"
                                                style="position: sticky;top: 125px;background: #fff;z-index: 99999;">
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
                                                        <th width="100">کد محصول</th>
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
                                                    @php($x = 1)
                                                    @foreach ($Depots as $depot)
                                                        <tr class="item_{{ $x }}"
                                                            data-item="{{ $x }}">
                                                            <td>{{ $x }}</td>
                                                            <td class="sku_box">
                                                                <select class="select2 form-select codes"
                                                                    name="codes[]">
                                                                    <option value="">انتخاب کنید</option>
                                                                    @foreach ($Products as $pr)
                                                                        <option value="{{ $pr->id }}"
                                                                            @if ($depot->product->id == $pr->id) selected @endif
                                                                            data-code="{{ $pr->sku }}"
                                                                            data-unit="{{ $pr->pr_unit }}"
                                                                            data-subunit="{{ $pr->pr_sub_unit }}"
                                                                            data-items="{{ $pr->pack_items }}">
                                                                            {{ $pr->sku }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td class="text-center pr_ids_box">
                                                                <select class="select2 form-select pr_ids"
                                                                    name="pr_id[]">
                                                                    <option value="">انتخاب کنید</option>
                                                                    @foreach ($Products as $pr)
                                                                        <option value="{{ $pr->id }}"
                                                                            @if ($depot->product->id == $pr->id) selected @endif
                                                                            data-code="{{ $pr->sku }}"
                                                                            data-unit="{{ $pr->pr_unit }}"
                                                                            data-subunit="{{ $pr->pr_sub_unit }}"
                                                                            data-items="{{ $pr->pack_items }}">
                                                                            {{ $pr->title }}
                                                                            {{ $pr->display_name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            @if ($WarehouseLocationMode !== 'store_only')
                                                                <td class="location_box">
                                                                    <select
                                                                        class="select2 form-select warehouse_locations"
                                                                        name="warehouse_location_id[]"
                                                                        @if ($WarehouseLocationMode === 'required_locations') required @endif>
                                                                        <option value="0">
                                                                            {{ $WarehouseLocationMode === 'required_locations' ? 'انتخاب مکان' : 'بدون مکان' }}
                                                                        </option>
                                                                        @foreach ($WarehouseLocations as $location)
                                                                            <option value="{{ $location->id }}"
                                                                                data-store-id="{{ $location->store_id }}"
                                                                                @if ((int) $depot->warehouse_location_id === (int) $location->id) selected @endif>
                                                                                {{ $location->store->title ?? 'انبار' }}
                                                                                -
                                                                                {{ $location->path ?: $location->code }}
                                                                                -
                                                                                {{ $location->title }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                            @endif
                                                            <td class="unit_box">
                                                                <div class="row">
                                                                    <div class="col-9">
                                                                        <input type="number"
                                                                            class="form-control unit" name="unit[]"
                                                                            value="{{ $depot->entity }}" />
                                                                    </div>
                                                                    <span style="max-width: 95px"
                                                                        class="unit_text col-3 badge bg-label-dark rounded-pill text-center d-flex justify-content-center align-items-center">{{ $depot->product->pr_unit }}</span>
                                                                </div>
                                                            </td>
                                                            <td class="sub_unit_box">
                                                                <div class="row">
                                                                    <div class="col-9">
                                                                        <input type="number"
                                                                            class="form-control sub_unit"
                                                                            name="sub_unit[]"
                                                                            value="{{ intval($depot->product->pack_items) > 0 ? intval($depot->entity) / intval($depot->product->pack_items) : 0 }}" />
                                                                    </div><span style="max-width: 95px"
                                                                        class="sub_unit_text col-3 badge bg-label-dark rounded-pill text-center d-flex justify-content-center align-items-center">{{ $depot->product->pr_sub_unit }}</span>
                                                                </div>
                                                            </td>
                                                            <td class="trace_box">
                                                                <div class="row g-1">
                                                                    <div class="col-4"><input
                                                                            class="form-control form-control-sm"
                                                                            name="batch_no[]"
                                                                            value="{{ $depot->batch_no }}"
                                                                            placeholder="Batch"></div>
                                                                    <div class="col-4"><input
                                                                            class="form-control form-control-sm"
                                                                            name="lot_no[]"
                                                                            value="{{ $depot->lot_no }}"
                                                                            placeholder="Lot"></div>
                                                                    <div class="col-4"><input
                                                                            class="form-control form-control-sm"
                                                                            name="serial_no[]"
                                                                            value="{{ $depot->serial_no }}"
                                                                            placeholder="Serial"></div>
                                                                    <div class="col-6"><input
                                                                            class="form-control form-control-sm"
                                                                            type="date" name="manufactured_at[]"
                                                                            value="{{ $depot->manufactured_at }}"
                                                                            title="تاریخ تولید"></div>
                                                                    <div class="col-6"><input
                                                                            class="form-control form-control-sm"
                                                                            type="date" name="expiry_date[]"
                                                                            value="{{ $depot->expiry_date }}"
                                                                            title="تاریخ انقضا"></div>
                                                                    <div class="col-4"><input
                                                                            class="form-control form-control-sm"
                                                                            name="color[]"
                                                                            value="{{ $depot->color }}"
                                                                            placeholder="رنگ"></div>
                                                                    <div class="col-4"><input
                                                                            class="form-control form-control-sm"
                                                                            name="size[]"
                                                                            value="{{ $depot->size }}"
                                                                            placeholder="سایز"></div>
                                                                    <div class="col-4"><input
                                                                            class="form-control form-control-sm"
                                                                            name="quality_grade[]"
                                                                            value="{{ $depot->quality_grade }}"
                                                                            placeholder="Grade"></div>
                                                                    <div class="col-4"><input
                                                                            class="form-control form-control-sm"
                                                                            type="number" step="0.001"
                                                                            name="weight[]"
                                                                            value="{{ $depot->weight }}"
                                                                            placeholder="وزن"></div>
                                                                    <div class="col-8"><input
                                                                            class="form-control form-control-sm"
                                                                            name="tracking_notes[]"
                                                                            value="{{ $depot->tracking_notes }}"
                                                                            placeholder="یادداشت trace"></div>
                                                                </div>
                                                            </td>

                                                            <td class="text-center">
                                                                <span class="removeitem" style="cursor:pointer;"><svg
                                                                        width="16" height="17"
                                                                        viewBox="0 0 16 17" fill="none"
                                                                        xmlns="http://www.w3.org/2000/svg">
                                                                        <path
                                                                            d="M10.3248 6.03874L10.0313 13.4232M5.96873 13.4232L5.67515 6.03874M14.1328 3.40497C14.423 3.44763 14.7115 3.49276 15 3.54117M14.1328 3.40497L13.2267 14.7958C13.1897 15.2596 12.973 15.6928 12.62 16.0087C12.267 16.3247 11.8037 16.5001 11.3227 16.5H4.67733C4.19632 16.5001 3.73299 16.3247 3.37998 16.0087C3.02698 15.6928 2.81032 15.2596 2.77333 14.7958L1.86715 3.40497M14.1328 3.40497C13.1536 3.2618 12.1693 3.15315 11.1818 3.07923M1.86715 3.40497C1.57697 3.44681 1.28848 3.49194 1 3.54035M1.86715 3.40497C2.84642 3.26181 3.83074 3.15316 4.81818 3.07923M11.1818 3.07923V2.32766C11.1818 1.35948 10.4097 0.552119 9.40848 0.52176C8.46973 0.492747 7.53027 0.492747 6.59152 0.52176C5.5903 0.552119 4.81818 1.3603 4.81818 2.32766V3.07923M11.1818 3.07923C9.06376 2.92094 6.93624 2.92094 4.81818 3.07923"
                                                                            stroke="#FF0000" stroke-width="0.5"
                                                                            stroke-linecap="round"
                                                                            stroke-linejoin="round" />
                                                                    </svg></span>
                                                            </td>
                                                        </tr>
                                                        @php($x++)
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Sticky Actions -->
                            </div>
                        </div>
                    </form>
                    @php
                        $returnableRows = ($ReturnableDepots ?? collect())->filter(function ($depot) {
                            return (float) $depot->returnable_quantity > 0;
                        });
                    @endphp
                    <div class="container-fluid flex-grow-1 container-p-y">
                        <div class="row">
                            <div class="card py-3 col-12">
                                <div class="card-title d-flex justify-content-between align-items-center">
                                    <h3 class="mb-0">ثبت برگشت از سند انبار</h3>
                                    @if ($receipt->return_source_receipt_id)
                                        <span class="badge bg-label-info">این سند، برگشت از رسید
                                            #{{ $receipt->return_source_receipt_id }} است</span>
                                    @endif
                                </div>
                                @if ($receipt->document_status === 'approved' && !$receipt->return_source_receipt_id && $returnableRows->count())
                                    <form method="POST"
                                        action="{{ route('stocks.receipts.returns', $receipt->id) }}">
                                        @csrf
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label">تاریخ برگشت</label>
                                                <input type="date" class="form-control" name="return_date"
                                                    value="{{ now()->toDateString() }}">
                                            </div>
                                            <div class="col-md-9">
                                                <label class="form-label">علت برگشت</label>
                                                <input type="text" class="form-control" name="return_reason"
                                                    required maxlength="1000"
                                                    placeholder="مثال: مغایرت فیزیکی، خرابی، اصلاح حواله یا برگشت از مصرف">
                                            </div>
                                        </div>
                                        <div class="table-responsive text-nowrap">
                                            <table class="table table-bordered table-sm align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>کالا</th>
                                                        <th>انبار/مکان</th>
                                                        <th>مقدار اصلی</th>
                                                        <th>قبلا برگشت</th>
                                                        <th>قابل برگشت</th>
                                                        <th>مقدار برگشت</th>
                                                        <th>واحد فرعی</th>
                                                        <th>وزن</th>
                                                        <th>نوع اثر</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($returnableRows as $depot)
                                                        <tr>
                                                            <td>
                                                                <input type="hidden" name="source_depot_id[]"
                                                                    value="{{ $depot->id }}">
                                                                {{ optional($depot->product)->title }}
                                                                {{ optional($depot->product)->display_name }}
                                                                @if ($depot->batch_no || $depot->serial_no)
                                                                    <br><small class="text-muted">Batch:
                                                                        {{ $depot->batch_no ?: '-' }} / Serial:
                                                                        {{ $depot->serial_no ?: '-' }}</small>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                {{ optional($depot->store)->title ?: '-' }}
                                                                <br><small
                                                                    class="text-muted">{{ optional($depot->warehouseLocation)->path ?: 'بدون مکان' }}</small>
                                                            </td>
                                                            <td>{{ number_format(abs((float) $depot->entity), 3) }}
                                                            </td>
                                                            <td>{{ number_format((float) $depot->returned_quantity, 3) }}
                                                            </td>
                                                            <td>{{ number_format((float) $depot->returnable_quantity, 3) }}
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.001" min="0"
                                                                    max="{{ $depot->returnable_quantity }}"
                                                                    class="form-control form-control-sm"
                                                                    name="return_quantity[]" placeholder="0">
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.001" min="0"
                                                                    max="{{ $depot->returnable_sub_unit }}"
                                                                    class="form-control form-control-sm"
                                                                    name="return_sub_unit[]"
                                                                    placeholder="{{ $depot->returnable_sub_unit ?: 0 }}">
                                                            </td>
                                                            <td>
                                                                <input type="number" step="0.001" min="0"
                                                                    max="{{ $depot->returnable_weight }}"
                                                                    class="form-control form-control-sm"
                                                                    name="return_weight[]"
                                                                    placeholder="{{ $depot->returnable_weight ?: 0 }}">
                                                            </td>
                                                            <td>
                                                                @if ((int) $depot->status === 1)
                                                                    <span class="badge bg-label-danger">خروج از
                                                                        موجودی</span>
                                                                @else
                                                                    <span class="badge bg-label-success">ورود به
                                                                        موجودی</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <button class="btn btn-warning mt-3" type="submit"
                                            onclick="return confirm('سند برگشت جدید ثبت و اثر موجودی/حسابداری معکوس می شود. ادامه می دهید؟')">ثبت
                                            سند برگشت</button>
                                    </form>
                                @elseif ($receipt->document_status !== 'approved')
                                    <p class="text-muted mb-0">برای ثبت برگشت، ابتدا سند انبار باید تایید شده باشد.</p>
                                @elseif ($receipt->return_source_receipt_id)
                                    <p class="text-muted mb-0">این سند خودش برگشت است. برای اصلاح بیشتر، سند اصلاحی
                                        جدید ثبت کنید.</p>
                                @else
                                    <p class="text-muted mb-0">همه ردیف های این سند قبلا برگشت خورده اند یا مانده قابل
                                        برگشت ندارند.</p>
                                @endif
                            </div>
                        </div>
                    </div>
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
        jalaliDatepicker.startWatch({
            time: false
        });
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
                                    '" data-items="' + pr.pack_items + '">' + pr.title +
                                    ' ' + (pr.display_name ?? '') + '</option>');
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

            $(this).parents('td').siblings('.sku_box').find('input').val(pr_sku);
            $(this).parents('td').siblings('.unit_box').find('.unit_text').html(pr_unit);
            $(this).parents('td').siblings('.sub_unit_box').find('.sub_unit_text').html(pr_sub_unit);


        });

        $('.unit').on('keyup', function() {
            var entity = $(this).val();
            var per_pack = $(this).parents('td').siblings('.pr_ids_box').find('.pr_ids').find('option:selected')
                .attr('data-items');
            var entity_sub_unit = entity / per_pack
            if (hasNonZeroDecimal(entity_sub_unit)) {
                var ent_sub_unit = entity_sub_unit.toFixed(2);
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
                var ent = entity.toFixed(2);
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
                    pageLength: 5,
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
            var $tableBody = $('#prlist');
            var $lastTr = $tableBody.find('tr:last');

            // نابود کردن Select2های فعال در آخرین ردیف قبل از کلون
            $lastTr.find('select.select2').select2('destroy');

            // کلون‌کردن ردیف به همراه eventها
            var $clonedTr = $lastTr.clone(true);

            // شماره ردیف جدید = شماره قبلی + 1
            var lastIndex = parseInt($lastTr.find('td:first').text()) || 0;
            $clonedTr.find('td:first').text(lastIndex + 1);

            // پاک‌کردن مقادیر و انتخاب‌ها
            $clonedTr.find('input, textarea').each(function() {
                $(this).val(''); // پاک کردن مقدار قبلی
            });

            $clonedTr.find('select').each(function() {
                $(this).val('').removeAttr('id').removeAttr('data-select2-id');
                $(this).find('option').removeAttr('data-select2-id').prop('selected', false);
            });

            // افزودن به جدول
            $tableBody.append($clonedTr);

            // فعال‌سازی مجدد Select2
            $clonedTr.find('select.select2').select2();
            $lastTr.find('select.select2').select2();
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
                let $row = $(this).closest('tr');
                let rowIndex = $row.index();
                let colIndex = $cell.index();

                let $table = $('#prlist');
                let $target;

                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    $target = $table.find(`tr:eq(${rowIndex}) td:eq(${colIndex}) :input`);
                }
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    $target = $table.find(`tr:eq(${rowIndex+1}) td:eq(${colIndex}) :input`);
                }
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    $target = $row.find(`td:eq(${colIndex+1}) :input`);
                }
                if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    $target = $row.find(`td:eq(${colIndex-1}) :input`);
                }

                if ($target && $target.length) {
                    $target.focus();
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


            $('#prlist').on('keydown', ':input', function(e) {
                let $cell = $(this).closest('td');
                let $row = $(this).closest('tbody tr'); // فقط از ردیف‌های دیتا
                let rowIndex = $row.index();
                let colIndex = $cell.index();

                let $rows = $('#prlist tbody tr');
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
        const sbtBtn = document.getElementById('sbt_ai');
        const btnText = sbtBtn.querySelector('.btn-text');
        const spinner = sbtBtn.querySelector('.spinner-border');

        function setButtonState({
            text,
            loading,
            disabled
        }) {
            btnText.innerText = text;

            if (loading) {
                spinner.classList.remove('d-none');
            } else {
                spinner.classList.add('d-none');
            }

            sbtBtn.disabled = disabled;
        }
    </script>
    <script>
        sbtBtn.addEventListener('click', function() {
            const fileInput = document.getElementById('photo');
            const file = fileInput.files[0];

            if (!file) {
                alert('لطفاً ابتدا تصویر را انتخاب کنید');
                return;
            }

            const formData = new FormData();
            formData.append('receipt_photo', file);
            formData.append('_token', '{{ csrf_token() }}');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', '{{ route('stocks.importReceiptAi') }}', true);

            // 🔹 شروع ارسال
            setButtonState({
                text: 'در حال آپلود...',
                loading: true,
                disabled: true
            });

            // 🔹 پایان آپلود (هنوز AI در حال پردازشه)
            xhr.upload.addEventListener('load', function() {
                setButtonState({
                    text: 'در حال بررسی...',
                    loading: true,
                    disabled: true
                });
            });

            // 🔹 دریافت پاسخ نهایی
            xhr.onload = function() {

                if (xhr.status !== 200) {
                    errorState();
                    return;
                }

                const response = JSON.parse(xhr.responseText);
                if (!response.success || !response.matched_items) {
                    errorState();
                    return;
                }

                const $tbody = $('#prlist tbody');

                response.matched_items.forEach(item => {

                    const ai = item.ai_item;
                    const matched = item.matched;

                    if (!matched || !matched.product_id) return;

                    const productId = matched.product_id;
                    const newQty = parseFloat(ai.pr_item) || 0;

                    /* ------------------------------------------------
                       1. آیا این محصول قبلاً در جدول هست؟
                    ------------------------------------------------- */

                    let $existingRow = null;

                    $tbody.find('.pr_ids').each(function() {
                        if ($(this).val() == productId) {
                            $existingRow = $(this).closest('tr');
                            return false; // break loop
                        }
                    });

                    /* ------------------------------------------------
                       2. اگر وجود داشت → UPDATE
                    ------------------------------------------------- */

                    if ($existingRow) {

                        let $unitInput = $existingRow.find('.unit');
                        let oldQty = parseFloat($unitInput.val()) || 0;

                        let finalQty = oldQty + newQty; // ✅ جمع می‌کنیم
                        // اگر می‌خوای overwrite بشه، اینو بگذار:
                        // let finalQty = newQty;

                        $unitInput
                            .val(finalQty)
                            .trigger('keyup')
                            .trigger('change');

                        // وضعیت بصری
                        $existingRow
                            .removeClass('table-danger table-warning')
                            .addClass('table-success');

                        return; // ⛔ مهم: دیگه ردیف جدید نساز
                    }

                    /* ------------------------------------------------
                       3. اگر وجود نداشت → ADD NEW ROW
                    ------------------------------------------------- */

                    let $lastRow = $tbody.find('tr:last');

                    let isEmptyRow = !$lastRow.find('.pr_ids').val() &&
                        !$lastRow.find('.codes').val() &&
                        !$lastRow.find('.unit').val();

                    let $row;

                    if (isEmptyRow) {
                        $row = $lastRow;
                    } else {
                        $('.additem').trigger('click');
                        $row = $tbody.find('tr:last');
                    }

                    // set selects
                    $row.find('.pr_ids')
                        .val(productId)
                        .trigger('change.select2')
                        .trigger('change');

                    $row.find('.codes')
                        .val(productId)
                        .trigger('change.select2')
                        .trigger('change');

                    // set quantity
                    $row.find('.unit')
                        .val(newQty)
                        .trigger('keyup')
                        .trigger('change');

                    // وضعیت match
                    $row
                        .removeClass('table-success table-warning table-danger')
                        .addClass(
                            item.status === 'auto_matched' ?
                            'table-success' :
                            item.status === 'need_review' ?
                            'table-warning' :
                            'table-danger'
                        );
                });

                setButtonState({
                    text: 'پردازش انجام شد ✅',
                    loading: false,
                    disabled: false
                });
            };



            xhr.onerror = errorState;

            function errorState() {
                setButtonState({
                    text: 'خطا در پردازش ❌',
                    loading: false,
                    disabled: false
                });
            }

            xhr.send(formData);
        });
    </script>

</body>

</html>
