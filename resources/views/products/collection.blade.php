<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>کاتالوگ محصولات - دکان دارمینو</title>
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
    <style>
        .light-style .select2-container--default .select2-selection--single {
            height: 50px;
        }
        @media(min-width: 768px) {
            .card-img-top {
                width: 330px;
                height: 330px;
            }
        }
        @media(max-width: 768px) {
            .card-img-top {
                width: 270px;
                height: 270px;
            }
            h5.card-title {
                height: 60px;
            }
        }
    </style>
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
                    <h4 class="py-3 mb-2">
                        <span class="text-muted fw-light">محصولات /</span>
                        کاتالوگ محصولات
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row prlist mt-3">
                        <div class="col-12 mb-3">
                            <input type="text" class="form-control form-control-lg" id="searchInput" placeholder="جستجوی محصول..." />
                        </div>
                        @foreach ($products as $product)
                            <div class="col-6 col-lg-3 mb-3 pr_item">
                                <div class="card">
                                    <img class="card-img-top" src="{{ $product->photo == null ? asset('/img/core-img/placeholder-image.png') : asset("/storage/uploads/$product->photo") }}" />
                                    <div class="card-body p-2">
                                        <h5 class="card-title mb-2" style="font-size: 17px">{{ $product->title }} {{ $product->display_name }}</h5>
                                        <p class="mb-1">کد محصول: <strong>{{ $product->sku }}</strong></p>
                                        <p class="mb-1">
                                            انبار مربوطه:
                                            <strong>
                                                @if(is_array(json_decode($product->store_id)))
                                                    @foreach(json_decode($product->store_id) as $storeid)
                                                            <?php $Store = DB::table('stores')->where('id', $storeid)->first();
                                                            echo $Store->title;
                                                            ?>
                                                    @endforeach
                                                @endif
                                            </strong>
                                        </p>
                                        <p class="mb-1">تعداد در {{ $product->pr_sub_unit }}: <strong>{{ $product->pack_items }}</strong></p>
                                    </div>
                                    @if($product->set_price == 0)
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item text-end pricepr">{{ number_format(intval($product->price)) }} ریال</li>
                                        </ul>
                                    @endif

                                    @if($product->set_price == 1)
                                        <label class="col-12 mt-2 px-3">
                                            <strong class="d-block"> قیمت هر {{ $product->pr_unit }} به <small>ریال</small>:</strong>
                                            <input type="text" class="form-control price"  name="price_{{ $product->id }}" value="{{ intval($product->price) > 0 ? number_format(intval($product->price)) : '0' }}" style="background-color: #F3F0F0" >
                                        </label>
                                    @endif
                                    <div class="card-body col-12 w-100">
                                        <a href="#" class="btn btn-outline-secondary w-100" type="button">
                                            جزئیات محصول
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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
<script>

    $('.orders').addClass('open')
    $('.orders .add-order').addClass('active open')
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

    $('.counterbox .plus').click(function() {
        itemcount = $(this).siblings('.inputcount').val();
        if(parseInt(itemcount) > 0) {
            $(this).siblings('.inputcount').val(eval(parseInt(itemcount)+1))
        }else {
            $(this).siblings('.inputcount').val(1)
        }
    });

    $('.counterbox .minus').click(function() {
        itemcount = $(this).siblings('.inputcount').val();
        if(parseInt(itemcount) > 0) {
            $(this).siblings('.inputcount').val(eval(parseInt(itemcount)-1))
        }
    });

    $(document).ready(function() {
        $('.set_customer').on('click', function() {

            if($(this).hasClass('btn-success')) {
                $(this).attr('type','submit');
                $('#addFactor').submit();
            }else {
                $(this).removeClass('btn-info');
                $(this).addClass('btn-success');
                $(this).html('ثبت سفارش مشتری');
                $('.prlist').removeClass('d-none');

            }

        });

    });

</script>
<script>
    document.querySelectorAll('.price').forEach(input => {
        input.addEventListener('input', e => {
            // حذف غیر عدد و نگه داشتن متن فعلی
            let pos = e.target.selectionStart;
            let raw = e.target.value.replace(/[^\d]/g, '');

            // فرمت سه‌رقم سه‌رقم
            let withCommas = raw.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            e.target.value = withCommas;

            // برگردوندن مکان کرسر تا وسط تایپ نپره
            let diff = withCommas.length - raw.length;
            e.target.selectionEnd = pos + diff;
        });
    });
</script>
<script>
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let value = this.value.toLowerCase().trim();
        let items = document.querySelectorAll(".pr_item");

        items.forEach(function(item) {
            let text = item.innerText.toLowerCase();
            if (text.includes(value)) {
                item.style.display = ""; // نمایش
            } else {
                item.style.display = "none"; // مخفی کردن
            }
        });
    });
</script>

</body>

</html>
