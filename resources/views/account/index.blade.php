<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>مدیریت حساب ها - دکان دارمینو</title>
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
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4">
                            <span class="text-muted fw-light">اطلاعات پایه /</span>
                            مدیریت حساب ها
                        </h4>
                        <!-- Sticky Actions -->
                        <div class="row mt-5">
                            <div class="col-12 col-md-4 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <form id="addStore" action="{{ route('Account.store') }}" method="POST"
                                            novalidate>
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label" for="name">نام حساب<small
                                                        style="color: red">*</small></label>
                                                <input class="form-control" id="name" placeholder="عنوان حساب"
                                                    type="text" name="name" required />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="code">کد حساب<small
                                                        style="color: red">*</small></label>
                                                <input class="form-control" id="code" placeholder="کد حساب"
                                                    type="text" name="code" required />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="description">توضیحات حساب</label>
                                                <textarea class="form-control" id="description" placeholder="توضیح" name="description"></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="level">سطح حساب</label>
                                                <select class="select2 form-select" id="level" name="level">
                                                    <option value="0">انتخاب کنید</option>
                                                    <option value="1">حساب کل</option>
                                                    <option value="2">حساب معین</option>
                                                    <option value="3">حساب تفصیلی</option>
                                                </select>
                                            </div>
                                            <div class="mb-3 parent_box d-none">
                                                <label class="form-label" for="parent_id">حساب والد:</label>
                                                <select class="select2 form-select" id="parent_id" name="parent_id">
                                                    <option value="0">انتخاب کنید</option>
                                                    @foreach ($Accounts as $acc)
                                                        <option value="{{ $acc->id }}"
                                                            data-level="{{ $acc->level }}">
                                                            @if ($acc->level == 1)
                                                                حساب کل:
                                                            @elseif($acc->level == 2)
                                                                حساب معین:
                                                            @elseif($acc->level == 3)
                                                                حساب تفصیلی:
                                                            @endif
                                                            {{ $acc->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="type">نوع حساب</label>
                                                <select class="select2 form-select" id="type" name="type">
                                                    <option value="0">انتخاب کنید</option>
                                                    <option value="1">بانک</option>
                                                    <option value="2">صندوق / وجه نقد</option>
                                                    <option value="3">حساب‌های دریافتنی (مطالبات)</option>
                                                    <option value="4">حساب‌های پرداختنی (بدهی‌ها)</option>
                                                    <option value="5">درآمد / فروش</option>
                                                    <option value="6">هزینه</option>
                                                    <option value="7">حقوق صاحبان سهام / سرمایه</option>
                                                    <option value="8">دارایی</option>
                                                    <option value="9">بدهی / تعهدات</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="account_category">طبقه حساب</label>
                                                <select class="select2 form-select" id="account_category"
                                                    name="account_category">
                                                    <option value="">انتخاب کنید</option>
                                                    <option value="asset">دارایی</option>
                                                    <option value="liability">بدهی</option>
                                                    <option value="equity">حقوق مالکانه</option>
                                                    <option value="income">درآمد</option>
                                                    <option value="expense">هزینه</option>
                                                    <option value="cost_of_goods">بهای تمام شده</option>
                                                    <option value="memo">انتظامی / آماری</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="detail_type">نوع تفصیل</label>
                                                <select class="select2 form-select" id="detail_type"
                                                    name="detail_type">
                                                    <option value="">بدون تفصیل مشخص</option>
                                                    <option value="customer">مشتری</option>
                                                    <option value="supplier">تامین کننده</option>
                                                    <option value="employee">پرسنل</option>
                                                    <option value="cost_center">مرکز هزینه</option>
                                                    <option value="store">انبار / شعبه</option>
                                                    <option value="project">پروژه</option>
                                                    <option value="asset">دارایی ثابت</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="is_control"
                                                        value="1" id="is_control">
                                                    <label class="form-check-label" for="is_control">حساب کنترلی
                                                        است</label>
                                                </div>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="cost_center_required" value="1"
                                                        id="cost_center_required">
                                                    <label class="form-check-label" for="cost_center_required">ثبت
                                                        مرکز هزینه برای این حساب الزامی است</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="floating_detail_required" value="1"
                                                        id="floating_detail_required">
                                                    <label class="form-check-label"
                                                        for="floating_detail_required">تفصیل شناور الزامی است</label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="nature">ماهیت حساب</label>
                                                <select class="select2 form-select" id="nature" name="nature">
                                                    <option value="0">خنثی</option>
                                                    <option value="1">بدهکار</option>
                                                    <option value="2">بستانکار</option>
                                                </select>
                                            </div>
                                            <div class="mb-3 con_bank d-none">
                                                <label class="form-label" for="account_number">شماره حساب</label>
                                                <input class="form-control" id="account_number" placeholder="کد حساب"
                                                    type="text" name="account_number" />
                                            </div>
                                            <div class="mb-3 con_bank d-none">
                                                <label class="form-label" for="card_number">شماره کارت</label>
                                                <input class="form-control" id="card_number" placeholder="شماره کارت"
                                                    type="text" name="card_number" />
                                            </div>
                                            <div class="mb-3 con_bank d-none">
                                                <label class="form-label" for="iban">شماره شبا</label>
                                                <input class="form-control" id="iban" placeholder="شماره شبا"
                                                    type="text" name="iban" />

                                            </div>
                                            <div class="mb-3 con_bank d-none">
                                                <label class="form-label" for="branch">نام شعبه</label>
                                                <input class="form-control" id="branch" placeholder="نام شعبه"
                                                    type="text" name="branch" />

                                            </div>
                                            <button class="btn btn-primary" type="submit">ایجاد حساب</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-8 order-1 order-lg-2 mb-4 mb-lg-0">

                                <div class="card">
                                    <div class="card-datatable table-responsive py-0">
                                        <table class="datatables-direct-basic table">
                                            <thead>
                                                <tr class="text-center">
                                                    <th>ردیف</th>
                                                    <th>کد حساب</th>
                                                    <th>نام حساب</th>
                                                    <th>سطح</th>
                                                    <th>نوع</th>
                                                    <th>وضعیت</th>
                                                    <th>عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php($x = 1)
                                                @foreach ($MainAccounts as $account)
                                                    <tr>
                                                        <th>{{ $x }}</th>
                                                        <td><a
                                                                href="{{ route('Account.edit', $account->id) }}">{{ $account->code }}</a>
                                                        </td>
                                                        <td><a
                                                                href="{{ route('Account.edit', $account->id) }}">{{ $account->name }}</a>
                                                        </td>
                                                        <td>
                                                            @if ($account->level == 1)
                                                                حساب کل
                                                            @elseif($account->level == 2)
                                                                حساب معین
                                                            @elseif($account->level == 3)
                                                                حساب تفصیلی
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($account->type == 1)
                                                                بانک
                                                            @elseif($account->type == 2)
                                                                صندوق / وجه نقد
                                                            @elseif($account->type == 3)
                                                                حساب‌های دریافتنی (مطالبات)
                                                            @elseif($account->type == 4)
                                                                حساب‌های پرداختنی (بدهی‌ها)
                                                            @elseif($account->type == 5)
                                                                درآمد / فروش
                                                            @elseif($account->type == 6)
                                                                هزینه
                                                            @elseif($account->type == 7)
                                                                حقوق صاحبان سهام / سرمایه
                                                            @elseif($account->type == 8)
                                                                دارایی
                                                            @elseif($account->type == 9)
                                                                بدهی / تعهدات
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($account->isActive == 1)
                                                                <span class="badge bg-label-success me-1">فعال</span>
                                                            @else
                                                                <span class="badge bg-label-danger me-1">غیرفعال</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $account->isActive }}</td>
                                                    </tr>
                                                    @php($x++)

                                                    <?php $ChildAccounts = DB::table('accounts')->where('parent_id', $account->id)->get(); ?>
                                                    @if (count($ChildAccounts) > 0)
                                                        @foreach ($ChildAccounts as $account)
                                                            <tr>
                                                                <th>{{ $x }}</th>
                                                                <td><a
                                                                        href="{{ route('Account.edit', $account->id) }}">{{ $account->code }}</a>
                                                                </td>
                                                                <td><a
                                                                        href="{{ route('Account.edit', $account->id) }}">
                                                                        --{{ $account->name }}</a></td>
                                                                <td>
                                                                    @if ($account->level == 1)
                                                                        حساب کل
                                                                    @elseif($account->level == 2)
                                                                        حساب معین
                                                                    @elseif($account->level == 3)
                                                                        حساب تفصیلی
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($account->type == 1)
                                                                        بانک
                                                                    @elseif($account->type == 2)
                                                                        صندوق / وجه نقد
                                                                    @elseif($account->type == 3)
                                                                        حساب‌های دریافتنی (مطالبات)
                                                                    @elseif($account->type == 4)
                                                                        حساب‌های پرداختنی (بدهی‌ها)
                                                                    @elseif($account->type == 5)
                                                                        درآمد / فروش
                                                                    @elseif($account->type == 6)
                                                                        هزینه
                                                                    @elseif($account->type == 7)
                                                                        حقوق صاحبان سهام / سرمایه
                                                                    @elseif($account->type == 8)
                                                                        دارایی
                                                                    @elseif($account->type == 9)
                                                                        بدهی / تعهدات
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if ($account->isActive == 1)
                                                                        <span
                                                                            class="badge bg-label-success me-1">فعال</span>
                                                                    @else
                                                                        <span
                                                                            class="badge bg-label-danger me-1">غیرفعال</span>
                                                                    @endif
                                                                </td>
                                                                <td>{{ $account->isActive }}</td>
                                                            </tr>
                                                            @php($x++)

                                                            <?php $TafsiliAccounts = DB::table('accounts')->where('parent_id', $account->id)->get(); ?>
                                                            @if (count($TafsiliAccounts) > 0)
                                                                @foreach ($TafsiliAccounts as $account)
                                                                    <tr>
                                                                        <th>{{ $x }}</th>
                                                                        <td><a
                                                                                href="{{ route('Account.edit', $account->id) }}">{{ $account->code }}</a>
                                                                        </td>
                                                                        <td><a
                                                                                href="{{ route('Account.edit', $account->id) }}">
                                                                                ---> {{ $account->name }}</a></td>
                                                                        <td>
                                                                            @if ($account->level == 1)
                                                                                حساب کل
                                                                            @elseif($account->level == 2)
                                                                                حساب معین
                                                                            @elseif($account->level == 3)
                                                                                حساب تفصیلی
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            @if ($account->type == 1)
                                                                                بانک
                                                                            @elseif($account->type == 2)
                                                                                صندوق / وجه نقد
                                                                            @elseif($account->type == 3)
                                                                                حساب‌های دریافتنی (مطالبات)
                                                                            @elseif($account->type == 4)
                                                                                حساب‌های پرداختنی (بدهی‌ها)
                                                                            @elseif($account->type == 5)
                                                                                درآمد / فروش
                                                                            @elseif($account->type == 6)
                                                                                هزینه
                                                                            @elseif($account->type == 7)
                                                                                حقوق صاحبان سهام / سرمایه
                                                                            @elseif($account->type == 8)
                                                                                دارایی
                                                                            @elseif($account->type == 9)
                                                                                بدهی / تعهدات
                                                                            @endif
                                                                        </td>
                                                                        <td>
                                                                            @if ($account->isActive == 1)
                                                                                <span
                                                                                    class="badge bg-label-success me-1">فعال</span>
                                                                            @else
                                                                                <span
                                                                                    class="badge bg-label-danger me-1">غیرفعال</span>
                                                                            @endif
                                                                        </td>
                                                                        <td>{{ $account->isActive }}</td>
                                                                    </tr>
                                                                    @php($x++)
                                                                @endforeach
                                                            @endif
                                                        @endforeach
                                                    @endif
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
        // datatable (jquery)
        $('.basicdata').addClass('open')
        $('.basicdata .accounts').addClass('active open')
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
                    pageLength: 10,
                });

                $('.datatables-direct-basic tbody').on('click', '.dropdown-item.delete-record', function() {
                    dt_without_ajax
                        .row($(this).parents('tr'))
                        .remove()
                        .draw();
                });
            }


        });
    </script>
    <script>
        $(document).ready(function() {
            $('#addStore').on('submit', function(e) {
                e.preventDefault(); // جلوگیری از ارسال ابتدا، تا ولیدیشن اجرا شود
                let isValid = true;
                $('.error-message').remove();

                $(this).find('input[required], select[required]').each(function() {
                    let $field = $(this);
                    let value = $field.val();

                    // تبدیل آرایه به رشته اگر multiple باشد
                    if (Array.isArray(value)) {
                        value = value.length ? value.join(',') : '';
                    }

                    if ($.trim(value) === '') {
                        isValid = false;
                        let errorMsg = $(
                            '<div class="error-message" style="color:red;font-size:12px;margin-top:4px;">این فیلد الزامی است</div>'
                            );

                        if ($field.next('.select2').length) {
                            $field.next('.select2').after(errorMsg);
                        } else {
                            $field.after(errorMsg);
                        }
                    }
                });

                if (isValid) {
                    this.submit(); // اگر معتبر بود، فرم رو ارسال کن
                }
            });

            $('#type').on('change', function() {
                var type = $(this).find('option:selected').val();
                if (type == 1) {
                    $('.con_bank').removeClass('d-none');
                } else {
                    $('.con_bank').addClass('d-none');
                }
            });

            $('#level').on('change', function() {
                var type = $(this).find('option:selected').val();
                if (type > 1) {
                    $('.parent_box').removeClass('d-none');
                } else {
                    $('.parent_box').addClass('d-none');
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {

            // ذخیره کامل همه آپشن‌ها
            let allOptions = [];
            $('#parent_id option').each(function() {
                allOptions.push({
                    id: $(this).val(),
                    text: $(this).text(),
                    level: $(this).data('level') ?? null
                });
            });

            $('#level').select2();
            $('#parent_id').select2();

            $('#level').on('change', function() {

                let selectedLevel = parseInt($(this).val());
                let allowed = null;

                if (selectedLevel === 2) allowed = 1; // معین ← والد کل
                if (selectedLevel === 3) allowed = 2; // تفصیلی ← والد معین
                if (selectedLevel === 1 || selectedLevel === 0) allowed = -1;

                // Destroy قبل از ساخت لیست جدید
                $('#parent_id').select2('destroy');

                // ساخت HTML جدید بر اساس فیلتر درست
                let newHtml = '<option value="0">انتخاب کنید</option>';

                if (allowed !== -1) {
                    allOptions.forEach(opt => {
                        if (opt.id !== "0" && opt.level === allowed) {
                            newHtml +=
                                `<option value="${opt.id}" data-level="${opt.level}">${opt.text}</option>`;
                        }
                    });
                }

                $('#parent_id').html(newHtml);

                // Init جدید؛ حالا Select2 دقیقاً فقط همین گزینه‌ها رو می‌بینه
                $('#parent_id').select2();

            });

        });
    </script>

</body>

</html>
