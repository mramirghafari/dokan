<!DOCTYPE html>
<html lang="fa" class="light-style layout-menu-fixed" dir="rtl" data-theme="theme-default" data-assets-path="../../assets/" data-template="vertical-menu-template-free">
<head>
    @include('sections.head')
    <title>داشبورد Executive - دکان دارمینو</title>
</head>
<body>
@include('sweetalert::alert')
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('sections.sidebar')
        <div class="layout-page">
            @include('sections.navbar')
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    @include('bi.partials.executive_dashboard', ['dashboard' => $dashboard, 'title' => 'داشبورد Executive'])
                </div>
                @include('sections.footer')
            </div>
        </div>
    </div>
</div>
@include('sections.script')
</body>
</html>
