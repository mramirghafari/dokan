<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>لیست محصولات - دکان دارمینو</title>
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
    <style>
        div#DataTables_Table_0_length {
            text-align: left;
            padding-left: 15px;
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
                            لیست محصولات
                        </h4>
                        <!-- Sticky Actions -->

                        <form action="{{ route('Accounting.ProductsSales') }}" method="POST">
                            @csrf
                            <div class="col-12 card mb-4">
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="form-group col-6 col-md-3">
                                            <label for="from_date">نمایش از تاریخ ثبت:</label>
                                            <input type="text" class="form-control" name="from_date" id="from_date"
                                                data-jdp
                                                value="{{ isset($_POST['from_date']) ? $_POST['from_date'] : '' }}">
                                        </div>

                                        <div class="form-group col-6 col-md-3">
                                            <label for="to_date">نمایش تا تاریخ ثبت:</label>
                                            <input type="text" class="form-control" name="to_date" id="to_date"
                                                data-jdp
                                                value="{{ isset($_POST['to_date']) ? $_POST['to_date'] : '' }}">
                                        </div>

                                        <div class="form-group col-6 col-md-3">
                                            <label for="delivery_from_date">نمایش از تاریخ تحویل:</label>
                                            <input type="text" class="form-control" name="delivery_from_date"
                                                id="delivery_from_date" data-jdp
                                                value="{{ isset($_POST['delivery_from_date']) ? $_POST['delivery_from_date'] : '' }}">
                                        </div>

                                        <div class="form-group col-6 col-md-3">
                                            <label for="delivery_to_date">نمایش تا تاریخ تحویل:</label>
                                            <input type="text" class="form-control" name="delivery_to_date"
                                                id="delivery_to_date" data-jdp
                                                value="{{ isset($_POST['delivery_to_date']) ? $_POST['delivery_to_date'] : '' }}">
                                        </div>


                                        <div class="form-group col-12 d-flex align-items-end mt-3">
                                            <button type="submit" class="btn btn-info w-100">فیلتر تاریخ</button>
                                        </div>

                                    </div>
                                    @if (!isset($_POST['from_date']) && !isset($_POST['to_date']))
                                        <p class="text-danger">لیست زیر شامل مرور کلی محصولات بدون تاریخ میباشد.</p>
                                    @elseif($_POST['from_date'] != null && $_POST['to_date'] != null && $_POST['from_date'] == $_POST['to_date'])
                                        <p class="text-danger">حواله خروج مربوط به تاریخ <span
                                                style="display: inline-block;direction: ltr">{{ $fromDate }}</span>
                                            میباشد</p>
                                    @elseif($_POST['from_date'] != null && $_POST['to_date'] != null && $_POST['from_date'] != $_POST['to_date'])
                                        <p class="text-danger">حواله خروج مربوط به تاریخ <span
                                                style="display: inline-block;direction: ltr">{{ $_POST['from_date'] }}</span>
                                            تا <span
                                                style="display: inline-block;direction: ltr">{{ $_POST['to_date'] }}</span>
                                            میباشد</p>
                                    @endif
                                </div>
                            </div>
                        </form>

                        <div class="row my-3">
                            <div class="card px-0  mb-5">
                                <div class="card-datatable table-responsive tablelist py-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>کد کالا</th>
                                                <th>نام کالا</th>
                                                <th>انبار</th>
                                                <th>موجودی کالا</th>
                                                <th>واحد اصلی</th>
                                                <th>واحد فرعی</th>
                                                <th>مقدار فروش</th>
                                                <th>کل مبلغ فروش</th>
                                                <th>بهای تمام‌شده فروش</th>
                                                <th>سود ناخالص</th>
                                                <th>ارزش موجودی</th>
                                                <th>قیمت</th>
                                                <th>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php($x = 1)
                                            @foreach ($products as $product)
                                                <tr>
                                                    <th>{{ $x }}</th>
                                                    <td><a
                                                            href="{{ route('products.edit', $product->id) }}">{{ $product->sku }}</a>
                                                    </td>
                                                    <td><a href="{{ route('products.edit', $product->id) }}">{{ $product->title }}
                                                            {{ $product->display_name }}</a></td>
                                                    <td>
                                                        @if (is_array(json_decode($product->store_id)))
                                                            @foreach (json_decode($product->store_id) as $storeid)
                                                                <?php $Store = DB::table('stores')->where('id', $storeid)->first();
                                                                echo $Store->title;
                                                                ?>
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td>{{ $product->currentStock() }}</td>
                                                    <td>{{ $product->pr_unit }}</td>
                                                    <td>{{ $product->pr_sub_unit }}</td>
                                                    <td>{{ $product->total_qty }}</td>
                                                    <td>{{ number_format($product->total_amount) }}</td>
                                                    <td>{{ number_format((float) $product->cost_of_goods_sold) }}</td>
                                                    <td>{{ number_format((float) $product->total_amount - (float) $product->cost_of_goods_sold) }}
                                                    </td>
                                                    <td>{{ number_format($product->stock_value) }}</td>
                                                    <td>{{ $product->price > 0 ? number_format(intval($product->price)) : intval($product->price) }}
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('products.edit', $product->id) }}"
                                                            style="font-size:20px;float: right;margin-left:5px"><i
                                                                class="fa fa-edit" style="color:#04a9f5;"></i></a>
                                                        {{-- <form action="{{ route('stores.destroy', $region->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('آیا از حذف رکورد مورد نظر اطمینان دارید؟');">
                                                    @method('delete')
                                                    @csrf
                                                    <button type="submit"
                                                        style="font-size:20px;border: none;background-color: transparent;float: right;">
                                                        <i class="fa fa-trash" style="color:#dc3545;"></i>
                                                    </button>
                                                </form> --}}
                                                    </td>
                                                </tr>
                                                @php($x++)
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="4">جمع</th>
                                                <th colspan="10" id="totalSales" class="text-end">0</th>
                                            </tr>
                                        </tfoot>
                                    </table>
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
        $(document).ready(function() {
            jalaliDatepicker.startWatch();
        });
    </script>
    <script>
        $('.accounting').addClass('open')
        $('.accounting .ProductsSales').addClass('active open')
        // datatable (jquery)

        $(document).ready(function() {
            var table = $('.datatables-direct-basic').DataTable({
                searching: true,
                lengthChange: true,
                ordering: true,
                pageLength: 50,
                language: {
                    search: 'جستجو: ',
                    searchPlaceholder: 'جستجو کنید...',
                    info: 'نمایش صفحه _PAGE_ از _PAGES_',
                    infoEmpty: 'موردی وجود ندارد.',
                    infoFiltered: '(فیلتر شده _MAX_ از records)',
                    lengthMenu: 'نمایش _MENU_ مورد در صفحه',
                    zeroRecords: 'متاسفانه موردی پیدا نشد',
                    paginate: {
                        previous: 'قبلی',
                        next: 'بعدی'
                    }
                },
                autoWidth: false, // خاموش کردن تعیین عرض خودکار
                columnDefs: [{
                        width: "30px",
                        targets: 0
                    }, // ستون شماره
                    {
                        width: "70px",
                        targets: 1
                    }, // ستون نام کالا
                    {
                        width: "250px",
                        targets: 2
                    }, // ستون نام کالا
                    {
                        width: "180px",
                        targets: 3
                    }, // ستون انبار
                    {
                        width: "80px",
                        targets: 4
                    }, // موجودی کالا
                    {
                        width: "60px",
                        targets: 5
                    }, // واحد اصلی
                    {
                        width: "60px",
                        targets: 6
                    }, // واحد فرعی
                    {
                        width: "80px",
                        targets: 7
                    }, // قیمت
                    {
                        width: "120px",
                        targets: 8
                    }, // عملیات
                    {
                        width: "120px",
                        targets: 9
                    }, // عملیات
                    {
                        width: "120px",
                        targets: 10
                    }, // عملیات
                    {
                        width: "120px",
                        targets: 11
                    }, // عملیات
                    {
                        width: "120px",
                        targets: 12
                    }, // عملیات
                    {
                        width: "50px",
                        targets: 13
                    }, // عملیات
                ],
                initComplete: function() {
                    var api = this.api();

                    var $select = $(
                        '<select class="form-select" style="width:auto; display:inline-block; margin-right:40px;">' +
                        '<option value="">همه انبارها</option>' +
                        '</select>');

                    api.column(3).data().unique().sort().each(function(d) {
                        d = d.trim();
                        if (d.length) $select.append('<option value="' + d + '">' + d +
                            '</option>');
                    });

                    // قرار دادن کنار جستجو
                    $('.dataTables_filter label').append($select);

                    // اعمال فیلتر روی ستون
                    $select.on('change', function() {
                        var val = $.fn.dataTable.util.escapeRegex($(this).val());
                        api.column(3).search(val ? '^' + val + '$' : '', true, false).draw();
                    });
                },
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    // تابع تبدیل متن به عدد (حذف جداکننده هزار و ...)
                    var parseValue = function(i) {
                        return typeof i === 'string' ?
                            parseFloat(i.replace(/[\$,]/g, '')) :
                            typeof i === 'number' ? i : 0;
                    };

                    // 🔹 جمع کل بدون فیلتر
                    var total = api
                        .column(8) // شماره ایندکس ستون مبلغ
                        .data()
                        .reduce(function(a, b) {
                            return a + parseValue(b);
                        }, 0);

                    // 🔹 جمع با فیلتر فعلی
                    var pageTotal = api
                        .column(8, {
                            page: 'current'
                        })
                        .data()
                        .reduce(function(a, b) {
                            return a + parseValue(b);
                        }, 0);

                    // نمایش جمع در فوتِر
                    $(api.column(8).footer()).html(
                        pageTotal.toLocaleString('fa-IR') + ' (کل: ' + total.toLocaleString(
                        'fa-IR') + ')'
                    );
                }
            });
        });





        $('.counterbox .plus').click(function() {
            itemcount = $(this).siblings('.inputcount').val();
            if (parseInt(itemcount) > 0) {
                $(this).siblings('.inputcount').val(eval(parseInt(itemcount) + 1))
            } else {
                $(this).siblings('.inputcount').val(1)
            }
        });

        $('.counterbox .minus').click(function() {
            itemcount = $(this).siblings('.inputcount').val();
            if (parseInt(itemcount) > 0) {
                $(this).siblings('.inputcount').val(eval(parseInt(itemcount) - 1))
            }
        });
    </script>
</body>

</html>
