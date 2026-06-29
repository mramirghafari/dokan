<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ویرایش گروه مشتری - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
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
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="py-3 mb-0">
                                <span class="text-muted fw-light">مشتریان / گروه مشتری /</span>
                                ویرایش
                            </h4>
                            <a class="btn btn-label-secondary" href="{{ route('customer-groups.index') }}">بازگشت به
                                لیست</a>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <form action="{{ route('customer-groups.update', $group) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="title">عنوان گروه <span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control" id="title" name="title" type="text"
                                                value="{{ old('title', $group->title) }}" required />
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="sort_order">ترتیب نمایش</label>
                                            <input class="form-control" id="sort_order" name="sort_order" type="number"
                                                min="0" value="{{ old('sort_order', $group->sort_order) }}" />
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" id="isActive" name="isActive"
                                                    type="checkbox" @if (old('isActive', $group->isActive)) checked @endif />
                                                <label class="form-check-label" for="isActive">فعال</label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="description">توضیحات</label>
                                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $group->description) }}</textarea>
                                        </div>
                                    </div>
                                    <div class="mt-4 d-flex gap-2 flex-wrap">
                                        <button class="btn btn-primary" type="submit">ذخیره تغییرات</button>
                                    </div>
                                </form>
                                <form class="mt-2" action="{{ route('customer-groups.destroy', $group) }}" method="POST"
                                    onsubmit="return confirm('آیا از حذف این گروه مطمئن هستید؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-label-danger" type="submit">حذف گروه</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @include('sections.footer')
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
</body>

</html>
