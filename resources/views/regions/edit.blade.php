<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ویرایش مناطق سامانه - دکان دارمینو</title>
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
                            ویرایش مناطق سامانه
                        </h4>
                        <!-- Sticky Actions -->
                        <div class="row mt-5">
                            <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <form action="{{ route('regions.update', $region->id) }}" class="row"
                                            method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <div class="mb-3 col-12 col-md-3">
                                                <label class="form-label" for="name">نام منطقه</label>
                                                <input class="form-control" id="name" name="name" required
                                                    placeholder="نام منطقه" value="{{ $region->name }}"
                                                    type="text" />
                                            </div>
                                            <div class="mb-3 col-12 col-md-3">
                                                <label class="form-label" for="city_id">انتخاب شهر</label>
                                                <select class="select2 form-control" data-allow-clear="true"
                                                    id="city_id" name="city_id">
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($Cities as $city)
                                                        <option value="{{ $city->id }}"
                                                            @if ($region->city_id == $city->id) selected @endif>
                                                            {{ $city->name }}
                                                            @if ($city->organization_id != null)
                                                                -
                                                                @php($Organ = DB::table('organizations')->where('id', $city->organization_id)->first())
                                                                {{ $Organ->title }}
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3 col-12 col-md-3">
                                                <label class="form-label" for="organization_id">انتخاب شعبه</label>
                                                <select class="select2 form-control" data-allow-clear="true"
                                                    id="organization_id" name="organization_id">
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($organizations as $organization)
                                                        <option value="{{ $organization->id }}"
                                                            @if ($region->organization_id == $organization->id) selected @endif>
                                                            {{ $organization->title }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @php($selectedLeaderIds = is_array(json_decode($region->leader_id, true)) ? json_decode($region->leader_id, true) : array_filter([(int) $region->leader_id]))
                                            <div class="mb-3 col-12 col-md-3">
                                                <label class="form-label" for="leader_id">انتخاب سرپرست منطقه</label>
                                                <select class="select2 form-control" data-allow-clear="true"
                                                    id="leader_id" name="leader_ids[]" multiple>
                                                    <option value="">انتخاب کنید</option>
                                                    @foreach ($Users as $user)
                                                        <option value="{{ $user->id }}"
                                                            @if (in_array($user->id, $selectedLeaderIds)) selected @endif>
                                                            {{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button class="btn btn-primary" type="submit">به روزرسانی منطقه</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 order-1 order-lg-2 mb-4 mb-lg-0">

                                <div class="card">
                                    <div class="card-datatable table-responsive py-0">
                                        <table class="datatables-direct-basic table">
                                            <thead>
                                                <tr>
                                                    <th width="30">#</th>
                                                    <th>عنوان منطقه</th>
                                                    <th>شهر</th>
                                                    <th>شعبه / پنل</th>
                                                    <th>سرپرست</th>
                                                    <th>عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php($x = 1)
                                                @foreach ($Regions as $region)
                                                    <tr>
                                                        <th width="30">{{ $x }}</th>
                                                        <td><a
                                                                href="{{ route('regions.edit', $region->id) }}">{{ $region->name }}</a>
                                                        </td>
                                                        @php($City = DB::table('cities')->where('id', $region->city_id)->first())
                                                        <td>
                                                            @if ($City)
                                                                {{ $City->name }}
                                                            @else
                                                                <small>وارد نشده</small>
                                                            @endif
                                                        </td>
                                                        @php($Organ = DB::table('organizations')->where('id', $region->organization_id)->first())
                                                        @if ($Organ)
                                                            @php($Tenant = DB::table('tenants')->where('id', $Organ->tenants_id)->first())
                                                        @endif
                                                        <td><small>
                                                                @if ($Organ && $Tenant)
                                                                    {{ $Organ->title }} / {{ $Tenant->name }}
                                                                @endif
                                                            </small></td>
                                                        <td>
                                                            <small>

                                                                @if (is_array(json_decode($region->leader_id)) && count(json_decode($region->leader_id)) > 0)
                                                                    @foreach (json_decode($region->leader_id) as $leaders)
                                                                        @php($Leader = DB::table('users')->where('id', $leaders)->first())
                                                                        {{ $Leader->name }},
                                                                    @endforeach
                                                                @elseif(!is_null($region->leader_id) && !is_array($region->leader_id))
                                                                    @php($Leader = DB::table('users')->where('id', $region->leader_id)->first())
                                                                    @if ($Leader)
                                                                        {{ $Leader->name }}
                                                                    @else
                                                                        <span class="text-danger">وارد نشده</span>
                                                                    @endif
                                                                @else
                                                                    <span class="text-danger">وارد نشده</span>
                                                                @endif
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('regions.edit', $region->id) }}"
                                                                style="font-size:20px;float: right;margin-left:25px"><i
                                                                    class="fa fa-edit" style="color:#04a9f5;"></i></a>

                                                            @if (auth()->user()->isAdmin == 1)
                                                                <form
                                                                    action="{{ route('regions.destroy', $region->id) }}"
                                                                    method="POST"
                                                                    onsubmit="return confirm('آیا از حذف رکورد مورد نظر اطمینان دارید؟');">
                                                                    @method('delete')
                                                                    @csrf
                                                                    <button type="submit"
                                                                        style="font-size:20px;border: none;background-color: transparent;float: right;">
                                                                        <i class="fa fa-trash"
                                                                            style="color:#dc3545;"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
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
        $('.basicdata').addClass('open')
        $('.basicdata .regions').addClass('active open')
        // datatable (jquery)
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
    </script>
</body>

</html>
