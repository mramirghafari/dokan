<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>ویرایش دسته بندی - دکان دارمینو</title>
    <meta content="" name="description"/>
    <!-- Favicon -->
    <link href="../assets/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/>
    <!-- Icons -->
    <link href="../assets/vendor/fonts/fontawesome.css" rel="stylesheet"/>
    <link href="../assets/vendor/fonts/tabler-icons.css" rel="stylesheet"/>
    <link href="../assets/vendor/fonts/flag-icons.css" rel="stylesheet"/>
    <!-- Core CSS -->
    <link href="../assets/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="../assets/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="../assets/css/demo.css" rel="stylesheet"/>
    <!-- Vendors CSS -->
    <link href="../assets/vendor/libs/node-waves/node-waves.css" rel="stylesheet"/>
    <link href="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet"/>
    <link href="../assets/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>
    <link href="../assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css" rel="stylesheet"/>
    <link href="../assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css" rel="stylesheet"/>
    <link href="../assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css" rel="stylesheet"/>

    <!-- Page CSS -->
    <link href="../assets/vendor/libs/select2/select2.css" rel="stylesheet"/>
    <!-- Helpers -->
    <script src="../assets/vendor/js/helpers.js"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="../assets/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="../assets/css/rtl.css" rel="stylesheet"/>
</head>

<body>
<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include('sections/sidebar.php') ?>
        <!-- Layout container -->
        <div class="layout-page">
           <?php include('sections/header.php') ?>
            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->
                <div class="container-xxl flex-grow-1 container-p-y">
                    <h4 class="py-3 mb-4">
                        <span class="text-muted fw-light">اطلاعات پایه /</span>
                        ویرایش دسته بندی
                    </h4>
                    <!-- Sticky Actions -->
                    <div class="row mt-5">
                        <div class="col-12 col-md-5 order-1 order-lg-2 mb-4 mb-lg-0">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <form>
                                        <div class="mb-3">
                                            <label class="form-label" for="multicol-country">انتخاب دسته بندی</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="multicol-country">
                                                <option value="">انتخاب کنید</option>
                                                <option value="Australia" selected>مرغ</option>
                                                <option value="Bangladesh">بنگلادش</option>
                                                <option value="Belarus">بلاروس</option>
                                                <option value="Brazil">برزیل</option>
                                                <option value="Canada">کانادا</option>
                                                <option value="China">چین</option>
                                                <option value="France">فرانسه</option>
                                                <option value="Germany">آلمان</option>
                                                <option value="India">هندوستان</option>
                                                <option value="Indonesia">اندونزی</option>
                                                <option value="Israel">اسرائيل</option>
                                                <option value="Italy">ایتالیا</option>
                                                <option value="Japan">ژاپن</option>
                                                <option value="Korea">جمهوری کره</option>
                                                <option value="Mexico">مکزیک</option>
                                                <option value="Philippines">فیلیپین</option>
                                                <option value="Russia">فدراسیون روسیه</option>
                                                <option value="South Africa">آفریقای جنوبی</option>
                                                <option value="Thailand">تایلند</option>
                                                <option value="Turkey">ترکیه</option>
                                                <option value="Ukraine">اوکراین</option>
                                                <option value="United Arab Emirates">امارات متحده عربی</option>
                                                <option value="United Kingdom">انگلستان</option>
                                                <option value="United States">ایالات متحده</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="basic-default-company">توضیح</label>
                                            <input class="form-control" id="basic-default-company" placeholder="توضیح" type="text"/>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="parent">انتخاب دسته بندی مادر</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="parent">
                                                <option value="">انتخاب کنید</option>
                                                <option value="Australia">استرالیا</option>
                                                <option value="Bangladesh">بنگلادش</option>
                                                <option value="Belarus">بلاروس</option>
                                                <option value="Brazil">برزیل</option>
                                                <option value="Canada">کانادا</option>
                                                <option value="China">چین</option>
                                                <option value="France">فرانسه</option>
                                                <option value="Germany">آلمان</option>
                                                <option value="India">هندوستان</option>
                                                <option value="Indonesia">اندونزی</option>
                                                <option value="Israel">اسرائيل</option>
                                                <option value="Italy">ایتالیا</option>
                                                <option value="Japan">ژاپن</option>
                                                <option value="Korea">جمهوری کره</option>
                                                <option value="Mexico">مکزیک</option>
                                                <option value="Philippines">فیلیپین</option>
                                                <option value="Russia">فدراسیون روسیه</option>
                                                <option value="South Africa">آفریقای جنوبی</option>
                                                <option value="Thailand">تایلند</option>
                                                <option value="Turkey">ترکیه</option>
                                                <option value="Ukraine">اوکراین</option>
                                                <option value="United Arab Emirates">امارات متحده عربی</option>
                                                <option value="United Kingdom">انگلستان</option>
                                                <option value="United States">ایالات متحده</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="organization_id">شعبه</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="organization_id">
                                                <option value="">انتخاب کنید</option>
                                                <option value="Australia">استرالیا</option>
                                                <option value="Bangladesh">بنگلادش</option>
                                                <option value="Belarus">بلاروس</option>
                                                <option value="Brazil">برزیل</option>
                                                <option value="Canada">کانادا</option>
                                                <option value="China">چین</option>
                                                <option value="France">فرانسه</option>
                                                <option value="Germany">آلمان</option>
                                                <option value="India">هندوستان</option>
                                                <option value="Indonesia">اندونزی</option>
                                                <option value="Israel">اسرائيل</option>
                                                <option value="Italy">ایتالیا</option>
                                                <option value="Japan">ژاپن</option>
                                                <option value="Korea">جمهوری کره</option>
                                                <option value="Mexico">مکزیک</option>
                                                <option value="Philippines">فیلیپین</option>
                                                <option value="Russia">فدراسیون روسیه</option>
                                                <option value="South Africa">آفریقای جنوبی</option>
                                                <option value="Thailand">تایلند</option>
                                                <option value="Turkey">ترکیه</option>
                                                <option value="Ukraine">اوکراین</option>
                                                <option value="United Arab Emirates">امارات متحده عربی</option>
                                                <option value="United Kingdom">انگلستان</option>
                                                <option value="United States">ایالات متحده</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="store">انبار</label>
                                            <select class="select2 form-select" data-allow-clear="true" id="store">
                                                <option value="">انتخاب کنید</option>
                                                <option value="Australia">استرالیا</option>
                                                <option value="Bangladesh">بنگلادش</option>
                                                <option value="Belarus">بلاروس</option>
                                                <option value="Brazil">برزیل</option>
                                                <option value="Canada">کانادا</option>
                                                <option value="China">چین</option>
                                                <option value="France">فرانسه</option>
                                                <option value="Germany">آلمان</option>
                                                <option value="India">هندوستان</option>
                                                <option value="Indonesia">اندونزی</option>
                                                <option value="Israel">اسرائيل</option>
                                                <option value="Italy">ایتالیا</option>
                                                <option value="Japan">ژاپن</option>
                                                <option value="Korea">جمهوری کره</option>
                                                <option value="Mexico">مکزیک</option>
                                                <option value="Philippines">فیلیپین</option>
                                                <option value="Russia">فدراسیون روسیه</option>
                                                <option value="South Africa">آفریقای جنوبی</option>
                                                <option value="Thailand">تایلند</option>
                                                <option value="Turkey">ترکیه</option>
                                                <option value="Ukraine">اوکراین</option>
                                                <option value="United Arab Emirates">امارات متحده عربی</option>
                                                <option value="United Kingdom">انگلستان</option>
                                                <option value="United States">ایالات متحده</option>
                                            </select>
                                        </div>
                                        <button class="btn btn-primary" type="submit">ارسال</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-7 order-1 order-lg-2 mb-4 mb-lg-0">

                            <div class="card">
                                <div class="card-datatable table-responsive pt-0">
                                    <table class="datatables-direct-basic table">
                                        <thead>
                                        <tr>
                                            <th>عنوان دسته بندی</th>
                                            <th>توضیح</th>
                                            <th>دسته والد</th>
                                            <th>وضعیت </th>
                                            <th>عملیات</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><bdi>مرغ</bdi></td>
                                            <td>
                                                -
                                            </td>
                                            <td>
                                               -
                                            </td>
                                            <td>
                                                <span class="badge  bg-label-success">فعال</span>
                                            </td>
                                            <td>
                                                <div class="d-inline-block">
                                                    <a href="javascript:;" class="btn btn-sm btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="text-primary ti ti-dots-vertical"></i></a>
                                                    <ul class="dropdown-menu dropdown-menu-end m-0">
                                                        <li><a href="category-edit.php" class="dropdown-item">ویرایش</a></li>
                                                        <li><a href="javascript:;" class="dropdown-item">غیرفعال</a></li>
                                                        <div class="dropdown-divider"></div>
                                                        <li><a href="javascript:;" class="dropdown-item text-danger delete-record">حذف</a></li>
                                                    </ul>
                                                </div>
                                                
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Sticky Actions -->
                </div>
                <!-- / Content -->
               <?php include('sections/footer.php'); ?>
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
<script src="../assets/vendor/libs/jquery/jquery.js"></script>
<script src="../assets/vendor/libs/popper/popper.js"></script>
<script src="../assets/vendor/js/bootstrap.js"></script>
<script src="../assets/vendor/libs/node-waves/node-waves.js"></script>
<script src="../assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
<script src="../assets/vendor/libs/hammer/hammer.js"></script>
<script src="../assets/vendor/libs/typeahead-js/typeahead.js"></script>
<script src="../assets/vendor/js/menu.js"></script>
<!-- endbuild -->
<script src="../assets/vendor/libs/jquery-sticky/jquery-sticky.js"></script>
<script src="../assets/vendor/libs/cleavejs/cleave.js"></script>
<script src="../assets/vendor/libs/cleavejs/cleave-phone.js"></script>
<script src="../assets/vendor/libs/select2/select2.js"></script>
<script src="../assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js"></script>
<!-- Main JS -->
<script src="../assets/js/main.js"></script>
<!-- Page JS -->
<script src="../assets/js/form-layouts.js"></script>
<script>
    // datatable (jquery)
    $('.basicdata').addClass('open')
    $('.basicdata .categories').addClass('active open')
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

</script>
</body>

</html>
