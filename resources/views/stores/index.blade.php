<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>مدیریت انبارهای سامانه - دکان دارمینو</title>
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
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="py-3 mb-4">
                            <span class="text-muted fw-light">اطلاعات پایه /</span>
                            مدیریت انبارهای سامانه
                        </h4>
                        <!-- Sticky Actions -->
                        <div class="row mt-5">
                            <div class="col-12 col-md-5 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <form id="addStore" action="{{ route('stores.store') }}" method="POST"
                                            novalidate>
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label" for="title">عنوان انبار<small
                                                        style="color: red">*</small></label>
                                                <input class="form-control" id="title" placeholder="عنوان انبار"
                                                    type="text" name="title" required />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="description">توضیحات انبار</label>
                                                <input class="form-control" id="description" placeholder="توضیح"
                                                    type="text" name="description" />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="code">کد انبار<small
                                                        style="color: red">*</small></label>
                                                <input class="form-control" id="code" placeholder="کد انبار"
                                                    type="number" name="code" required />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="store_type">نوع انبار<small
                                                        style="color: red">*</small></label>
                                                <select class="form-select" id="store_type" name="store_type" required>
                                                    @foreach ($storeTypes as $value => $label)
                                                        <option value="{{ $value }}"
                                                            @if (old('store_type', 'main') === $value) selected @endif>
                                                            {{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="stock_tracking_mode">سیاست کنترل
                                                    موجودی<small style="color: red">*</small></label>
                                                <select class="form-select" id="stock_tracking_mode"
                                                    name="stock_tracking_mode" required>
                                                    @foreach ($stockTrackingModes as $value => $label)
                                                        <option value="{{ $value }}"
                                                            @if (old('stock_tracking_mode', 'tracked') === $value) selected @endif>
                                                            {{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="transfer_policy">سیاست انتقال<small
                                                        style="color: red">*</small></label>
                                                <select class="form-select" id="transfer_policy"
                                                    name="transfer_policy" required>
                                                    @foreach ($transferPolicies as $value => $label)
                                                        <option value="{{ $value }}"
                                                            @if (old('transfer_policy', 'in_out') === $value) selected @endif>
                                                            {{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="organization_id">ارتباط با واحد
                                                    پخش<small style="color: red">*</small></label>
                                                <select class="select2 form-select" data-allow-clear="true"
                                                    id="organization_id" name="organization_id[]" multiple required>
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($organizations as $organization)
                                                        <option value="{{ $organization->id }}"
                                                            @if (in_array($organization->id, old('organization_id', []))) selected @endif>
                                                            {{ $organization->title ?: $organization->description }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="lat">عرض جغرافیایی انبار</label>
                                                <input class="form-control" id="lat" placeholder="lat"
                                                    type="text" name="lat" value="{{ old('lat') }}" />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="lang">طول جغرافیایی انبار</label>
                                                <input class="form-control" id="lang" placeholder="lng"
                                                    type="text" name="lang" value="{{ old('lang') }}" />
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="pr_ids">ارتباط با
                                                    محصولات/خدمات</label>
                                                <select class="select2 form-select" data-allow-clear="true"
                                                    id="pr_ids" name="pr_ids[]" multiple>
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($Products as $pr)
                                                        <option value="{{ $pr->id }}">
                                                            {{ $pr->title }} {{ $pr->display_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label" for="roles">نقش های کاربری مجاز </label>
                                                <select class="select2 form-select" data-allow-clear="true"
                                                    id="roles" name="roles[]" multiple>
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->id }}">{{ $role->description }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button class="btn btn-primary" type="submit">ایجاد انبار</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-7 order-1 order-lg-2 mb-4 mb-lg-0">

                                <div class="card">
                                    <div class="card-datatable table-responsive py-0">
                                        <table class="datatables-direct-basic table">
                                            <thead>
                                                <tr>
                                                    <th>ردیف</th>
                                                    <th>کد انبار</th>
                                                    <th>عنوان انبار</th>
                                                    <th>نوع</th>
                                                    <th>پنل</th>
                                                    <th>شعبه</th>
                                                    <th>کنترل موجودی</th>
                                                    <th>انتقال</th>
                                                    <th>اول دوره</th>
                                                    <th>وضعیت</th>
                                                    <th>عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php($x = 1)
                                                @foreach ($stores as $store)
                                                    <tr>
                                                        <td>{{ $x }}</td>
                                                        <td><small>{{ $store->code }}</small></td>
                                                        <td>{{ $store->title }}</td>
                                                        <td><small>{{ $store->storeTypeText() }}</small></td>
                                                        <td><small>{{ optional($store->tenants)->name ?: '-' }}</small>
                                                        </td>
                                                        <td><small>{{ $store->organizationNamesText() }}</small></td>
                                                        <td><small>{{ $store->stockTrackingText() }}</small></td>
                                                        <td><small>{{ $store->transferPolicyText() }}</small></td>
                                                        <td><small>{{ $store->openingInventoryStatusText() }}</small>
                                                        </td>
                                                        <td>
                                                            @if ($store->isActive == 1)
                                                                <div class='badge badge-success'>فعال</div>
                                                            @else
                                                                <div class='badge badge-danger'>غیرفعال</div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('stores.edit', $store->id) }}"
                                                                style="font-size:20px;float: right;margin-left:5px"><x-ui.icon name="fa-edit" /></a>
                                                            {{-- <form action="{{ route('stores.destroy', $store->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('آیا از حذف رکورد مورد نظر اطمینان دارید؟');">
                                                        @method('delete')
                                                        @csrf
                                                        <button type="submit"
                                                            style="font-size:20px;border: none;background-color: transparent;float: right;">
                                                            <x-ui.icon name="fa-trash" />
                                                        </button>
                                                    </form> --}}
                                                        </td>
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
        $('.basicdata').addClass('open')
        $('.basicdata .stores').addClass('active open')
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
        });
    </script>



</body>

</html>
