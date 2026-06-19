<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>جزئیات فاکتور شماره {{ $PishFactor->id }} - دکان دارمینو</title>
    <meta content="" name="description" />
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/sweetalert2/sweetalert2.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <!-- Page CSS -->
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <?php $Organ = App\Models\Organization::find($PishFactor->organization_id); ?>

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
                        <div class="row">
                            <div class="col-8">
                                <h5 class="pt-3 pt-1 mb-1">
                                    <span class="text-muted fw-light">سفارشات و فاکتورها /</span>
                                    <a href="{{ route('pishFactorInfo', $PishFactor->id) }}"> جزئیات سفارش شماره
                                        {{ $PishFactor->id }}</a>
                                    @if ($PishFactor->status == 0)
                                        <span class="badge bg-label-warning">در انتظار تایید سرپرست</span>
                                    @elseif($PishFactor->status == 1)
                                        <span class="badge bg-label-success">تایید شده</span>
                                    @elseif($PishFactor->status == 3)
                                        <span class="badge bg-label-danger">رد شده</span>
                                    @endif
                                </h5>
                                <p class="badge btn-outline-dark">تاریخ تحویل: {{ $PishFactor->recive_date }}</p>
                                <p class="badge btn-outline-dark">تاریخ ثبت سفارش:
                                    {{ Verta($PishFactor->created_at)->format('H:i:s - %d %B ، %Y ') }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">

                                <?php
                                $pishfactorId = $PishFactor->id;
                                $itemsByStore = \App\Models\PishFactorItems::with('product')
                                    ->where('pishfactor_id', $pishfactorId)
                                    ->get()
                                    ->groupBy(function ($item) {
                                        // چون store_id محصول JSON است، باید decode کنیم
                                        $storeIds = json_decode($item->product->store_id, true);
                                
                                        // اگر چند انبار دارد، می‌توان اولین یا همه را برگرداند
                                        return $storeIds[0] ?? 'no_store';
                                    })
                                    ->map(function ($items, $storeId) {
                                        // پیدا کردن نام انبار
                                        $storeTitle = \App\Models\Store::find($storeId)->title ?? '(بدون انبار)';
                                
                                        return [
                                            'store_id' => $storeId,
                                            'store_name' => $storeTitle,
                                            'products' => $items->map(function ($item) {
                                                // اینجا همچنان Model هست، پس میتوانی $item->pack و ... را استفاده کنی
                                                // $item->product_id    = $item->product->id;
                                                // $item->product_title = $item->product->title;
                                                // $item->quantity      = ($item->pack * ($item->product->pack_items ?? 1)) + $item->tedad;
                                                return $item;
                                            }),
                                        ];
                                    });
                                
                                ?>

                                @php($xx = 1)
                                @foreach ($itemsByStore as $storeGroup)
                                    <?php
                                    $store_ID = $storeGroup['store_id'];
                                    $FactorSetting = \App\Models\factorMaker::whereJsonContains('store_id', ["$store_ID"])->get();
                                    ?>
                                    <h3>{{ $storeGroup['store_name'] }}</h3>

                                    @foreach ($FactorSetting as $factorMaker)
                                        <div class="card mb-4">
                                            <div class="row justify-content-between align-items-center">
                                                <div class="col-4 text-start">
                                                    <button id="pdf{{ $xx }}" type="button"
                                                        onclick="printFreezDiv{{ $xx }}()"
                                                        class="m-3 dt-button buttons-print btn rounded-pill btn-label-warning waves-effect"
                                                        style="width: 100px">پرینت</button>
                                                </div>

                                            </div>
                                            <div id="factorbox{{ $xx }}"
                                                class="card-datatable table-responsive p-3">

                                                <div class="row">
                                                    <div class="col">
                                                        <div class="w-100 pe-3 text-start">
                                                            شماره فاکتور: {{ $PishFactor->invoiceID }} <br />
                                                            تاریخ تحویل: {{ $PishFactor->recive_date }} </div>
                                                    </div>
                                                    <div class="col">
                                                        <h5 class="text-center">{{ $factorMaker->name }}</h5>
                                                    </div>
                                                    <div class="col text-end">
                                                        <div class="w-100 pe-3 text-end">تاریخ ثبت فاکتور:
                                                            {{ Verta($PishFactor->created_at)->format('H:i:s - %d %B ، %Y ') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <table id="factor{{ $xx }}"
                                                    class="factor_table nowrap w-100">
                                                    <thead>
                                                        <tr class="x_border">
                                                            <th class="text-center" colspan="14"
                                                                style="text-align: center;padding: 2px !important;">
                                                                مشخصات فروشنده</th>
                                                        </tr>
                                                        <tr class="x_border">
                                                            <td colspan="5" class="border-0"><span
                                                                    style="font-size: 13px">نام شخص حقیقی/حقوقی:</span>
                                                                <strong>{{ $factorMaker->seller_name }}</strong></td>
                                                            <td colspan="5" class="border-0">شناسه اقتصادی/کدملی:
                                                                <strong>{{ $factorMaker->seller_economic_number }}</strong>
                                                            </td>
                                                            <td colspan="4" class="border-0">شماره ثبت:
                                                                <strong>{{ $factorMaker->seller_registration_number }}</strong>
                                                            </td>
                                                        </tr>
                                                        <tr class="no_border x_border">
                                                            <td colspan="5">نشانی کامل:
                                                                <strong>{{ $factorMaker->seller_address }}</strong>
                                                            </td>
                                                            <td colspan="5">شماره تلفن: <strong
                                                                    style="display: inline-block;direction: ltr !important;">{{ $factorMaker->seller_phone }}</strong>
                                                            </td>
                                                            <td colspan="4">کدپستی 10 رقمی:
                                                                <strong>{{ $factorMaker->seller_zip_code }}</strong>
                                                            </td>
                                                        </tr>
                                                        <tr class="x_border">
                                                            <th class="text-center" colspan="14"
                                                                style="text-align: center;padding: 2px !important;">
                                                                مشخصات خریدار</th>
                                                        </tr>
                                                        <tr class="x_border sp">
                                                            <?php $Customer_aRea = $PishFactor->customer_id ? DB::table('areas')->where('id', $PishFactor->customer->area)->first() : null; ?>
                                                            <?php $Customer_Region = $Customer_aRea ? DB::table('regions')->where('id', $Customer_aRea->region_id)->first() : null; ?>
                                                            <?php $Visitor = DB::table('users')->where('id', $PishFactor->visitor_id)->first(); ?>
                                                            <?php $Leader = DB::table('users')->where('id', $PishFactor->sarparast_id)->first(); ?>

                                                            <td colspan="5" class="border-0">نام خریدار:
                                                                <strong>
                                                                    @if ($factorMaker->buyer_name == 1)
                                                                        {{ $PishFactor->customer->name }}
                                                                    @elseif($factorMaker->buyer_name == 2)
                                                                        {{ $PishFactor->customer->tablo }}
                                                                    @elseif($factorMaker->buyer_name == 3)
                                                                        {{ $PishFactor->customer->name }} -
                                                                        {{ $PishFactor->customer->tablo }}
                                                                    @elseif($factorMaker->buyer_name == 4)
                                                                        {{ $PishFactor->customer->tablo }} /
                                                                        {{ $PishFactor->customer->name }} /
                                                                        {{ $Customer_Region ? $Customer_Region->name : 'ندارد' }}
                                                                        /
                                                                        {{ $Customer_aRea ? $Customer_aRea->name : 'ندارد' }}
                                                                        / {{ $PishFactor->customer->address }}
                                                                    @endif
                                                                </strong>
                                                            </td>
                                                            @if ($factorMaker->buyer_econimic_code == 1)
                                                                <td colspan="5" class="border-0">شناسه اقتصادی/کدملی:
                                                                    <strong>{{ $PishFactor->customer->buyer_econimic_code }}</strong>
                                                                </td>
                                                            @endif
                                                            @if ($factorMaker->buyer_registration_number == 1)
                                                                <td colspan="4" class="border-0">شماره ثبت/شماره
                                                                    ملی:
                                                                    {{ $PishFactor->customer->buyer_registration_number }}</strong>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                        <tr class="no_border x_border">
                                                            <td colspan="5">نشانی کامل:
                                                                <strong>{{ $PishFactor->customer->address }}</strong>
                                                            </td>
                                                            @if ($factorMaker->buyer_zip_code == 1)
                                                                <td colspan="5">کدپستی 10 رقمی:
                                                                    <strong>{{ $PishFactor->customer->zipcode }}</strong>
                                                                </td>
                                                            @endif
                                                            @if ($factorMaker->buyer_phone == 1)
                                                                <td colspan="4">شماره تلفن:
                                                                    <strong>{{ $PishFactor->customer->phone }}</strong>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                        <tr class="no_border x_border">
                                                            @if ($factorMaker->buyer_region_area == 1)
                                                                <td colspan="3">منطقه/مسیر: <strong>
                                                                        {{ $Customer_Region->name }} /
                                                                        {{ $Customer_aRea->name }} </strong></td>
                                                            @endif
                                                            @if ($factorMaker->buyer_map_code == 1)
                                                                <td colspan="2">مپ کد: <strong>
                                                                        {{ $PishFactor->customer->mapcode }} </strong>
                                                                </td>
                                                            @endif
                                                            @if ($factorMaker->visitor_display != 3)
                                                                <td colspan="5">نام بازاریاب:
                                                                    <strong>
                                                                        @if ($factorMaker->visitor_display == 1)
                                                                            {{ $Visitor->name }}
                                                                        @else
                                                                            {{ $Visitor->username }}
                                                                        @endif
                                                                    </strong>
                                                                </td>
                                                            @endif
                                                            @if ($factorMaker->visitor_mobile == 1)
                                                                <td colspan="4">شماره همراه بازاریاب: <strong>
                                                                        {{ $Visitor->mobile }} </strong></td>
                                                            @endif

                                                        </tr>
                                                        @include('invoices.partials.factor_line_table', [
                                                            'factorMaker' => $factorMaker,
                                                            'storeGroup' => $storeGroup,
                                                            'PishFactor' => $PishFactor,
                                                        ])

                                                </table>
                                            </div>
                                            <script>
                                                function printFreezDiv{{ $xx }}() {
                                                    let divContents = document.getElementById("factorbox{{ $xx }}").innerHTML;
                                                    let printWindow = window.open('', '', '');
                                                    printWindow.document.open();
                                                    printWindow.document.write(`
                <html>
                <head moznomarginboxes mozdisallowselectionprint>
                    <title>فاکتور {{ $PishFactor->invoiceID }}-{{ $PishFactor->customer->name }} </title>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/>

    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <style>
@page { size: A4 landscape; }
#factorbox{{ $xx }} {
    width: 99% !important;
    margin: 0px auto;
}
        .handle-counter button {display:none;}
        .handle-counter input {border: 0 none;background-color: transparent;}
        table, td, th,strong {
            font-size: 11px !important;
        }
        .table td, .table th {font-size: 12px !important;padding: 4px !important;}
        tr td, tr th {
            border-color: #000 !important;
            border-width: 2px !important;

        }
        tfoot tr {
            border-right: 2px solid #000 !important;
            border-left: 2px solid #000 !important;
            border-bottom: 2px solid #000 !important;
        }
        .td-left-border td, .td-left-border th, tfoot td, tfoot th {
            border-left: 2px solid #000 !important;
        }
        tr.no_border td, tr.no_border th {
            border-top: 0px solid transparent !important;
            border: 0 none !important;
        }
        .x_border {
            border-right: 2px solid #000 !important;
            border-left: 2px solid #000 !important;
        }
        tfoot label {
            display: inline-block;
            margin-right: 50px;
            position: relative;
        }
        tfoot label:before {
            content: " ";
            display: inline-block;
            width: 20px;
            height: 20px;
            outline: 1px solid black;
            position: relative;
            top: 5px;
            left: 5px;
        }
        tfoot label.active:before {
            background-color: #000;
            border: 2px solid #fff;
        }
        tr.sp td, tr.sp th {padding: 2px !important}
        .kalaname {width: 230px !important}
        .dis_col {width: 60px !important}
        .moadian {width: 70px !important}
        .boxcol {width: 50px !important}
        .discount_changer {text-align:center;}
        .discount_changer input {width: 40px;text-align: center;margin: 0 auto;}
        .filled {
    display: inline-block;
    width: 15px;
    height: 15px;
    position: absolute;
    top: 8px;
    right: -4px;
    fill: #000;
    font-size: 42px;
    line-height: 15px;
    color: #000;
}
    </style>
                </head>
                <body>
                    ${divContents}
                </body>
                </html>
            `);
                                                    printWindow.document.close();
                                                    printWindow.print();
                                                }
                                            </script>
                                        </div>
                                        @php($xx++)
                                    @endforeach
                                @endforeach


                            </div>
                        </div>
                        @if ($nextItem)
                            <a href="{{ route('pishFactorInfo', $nextItem) }}"
                                style="position: fixed; bottom: 50px;left: 50px"
                                class="next_btn btn rounded-pill btn-icon btn-primary waves-effect waves-light"
                                data-bs-original-title="برو به فاکتور بعدی" data-bs-placement="top"
                                data-bs-toggle="tooltip" type="button"><x-ui.icon name="arrow-forward-up" /></a>
                        @endif
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
    <!-- Vendors JS -->

    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/sweetalert2/sweetalert2.js"></script>
    <script src="{{ asset('assets/') }}/js/form-layouts.js"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <!-- Page JS -->
    <link rel="stylesheet" href="{{ asset('/css/jalalidatepicker.min.css') }}" />
    <script src="{{ asset('/js/jalalidatepicker.min.js') }}"></script>
    <script src="{{ asset('/js/wordifyfa.min.js') }}"></script>

    <script>
        $('.factors').addClass('open')
        @if ($PishFactor->status == 0)
            $('.factors .waiting').addClass('active open');
        @elseif ($PishFactor->status == 1)
            $('.factors .accepted').addClass('active open');
        @elseif ($PishFactor->status == 3)
            $('.factors .denciled').addClass('active open');
        @elseif ($PishFactor->status == 4)
            $('.factors .compeleted ').addClass('active open');
        @endif

        $(document).ready(function() {
            jalaliDatepicker.startWatch({
                time: true
            });
            document.querySelector("[data-jdp-miladi-input]").addEventListener("jdp:change", function(e) {
                var miladiInput = document.getElementById(this.getAttribute("data-jdp-miladi-input"));
                if (!this.value) {
                    miladiInput.value = "";
                    return;
                }
                var date = this.value.split("/");
                miladiInput.value = jalali_to_gregorian(date[0], date[1], date[2]).join("/")
            });

            function jalali_to_gregorian(jy, jm, jd) {
                jy = Number(jy);
                jm = Number(jm);
                jd = Number(jd);
                var gy = (jy <= 979) ? 621 : 1600;
                jy -= (jy <= 979) ? 0 : 979;
                var days = (365 * jy) + ((parseInt(jy / 33)) * 8) + (parseInt(((jy % 33) + 3) / 4)) +
                    78 + jd + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
                gy += 400 * (parseInt(days / 146097));
                days %= 146097;
                if (days > 36524) {
                    gy += 100 * (parseInt(--days / 36524));
                    days %= 36524;
                    if (days >= 365) days++;
                }
                gy += 4 * (parseInt((days) / 1461));
                days %= 1461;
                gy += parseInt((days - 1) / 365);
                if (days > 365) days = (days - 1) % 365;
                var gd = days + 1;
                var sal_a = [0, 31, ((gy % 4 == 0 && gy % 100 != 0) || (gy % 400 == 0)) ? 29 : 28, 31, 30, 31, 30,
                    31, 31, 30, 31, 30, 31
                ];
                var gm
                for (gm = 0; gm < 13; gm++) {
                    var v = sal_a[gm];
                    if (gd <= v) break;
                    gd -= v;
                }
                return [gy, gm, gd];
            }


            var sum_price_with_tax = $('.full_prices span').html();
            var sum = sum_price_with_tax.replaceAll(",", "");
            $('.factor_table th.horof').html(wordifyfa(sum) +
                " @if ($Organ->currency_type == 1) تومان  @else ریال @endif "
                );



            $('#step').on('change', function() {
                var stp = $(this).val();
                if (stp == 3) {
                    $('.select_driver').removeClass('d-none');
                } else {
                    $('.select_driver').addClass('d-none');
                }
            });


        });
    </script>

</body>

</html>
