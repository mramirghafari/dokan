<!DOCTYPE html>
<html class="light-style layout-wide customizer-hide" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>ورود - دکان دارمینو</title>
    <meta content="" name="description" />
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet" />
    <!-- Vendor -->
    <link
        href="{{ asset('assets/') }}/vendor/libs/@form-validation/form-validation.css" rel="stylesheet"/>
    <!-- Page CSS -->
    <!-- Page -->
    <link href="{{ asset('assets/') }}/vendor/css/pages/page-auth.css" rel="stylesheet"/>
    <!-- Helpers --><!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <!-- Better experience of RTL -->
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet"/>

    <style>
        ul.alert.alert-danger {
            list-style-type: none;
        }

        body.login-page .authentication-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        body.login-page .authentication-inner {
            max-width: 29rem;
            width: 100%;
        }

        body.login-page .card {
            border: 1px solid rgba(84, 60, 146, 0.12);
            border-radius: 1rem;
            box-shadow: 0 0.75rem 2rem rgba(67, 53, 86, 0.12);
        }

        body.login-page .card-body {
            padding: 2rem 1.75rem 1.5rem;
        }

        body.login-page .login-subtitle {
            color: #6f6b7d;
            margin-bottom: 1.4rem;
            font-size: 0.93rem;
        }

        body.login-page .login-tabs-wrap {
            margin-bottom: 1.55rem;
        }

        body.login-page .login-tabs {
            border: 0;
            gap: 0.42rem;
            margin-bottom: 0;
            background: linear-gradient(180deg, rgba(84, 60, 146, 0.09) 0%, rgba(84, 60, 146, 0.06) 100%);
            border: 1px solid rgba(84, 60, 146, 0.14);
            border-radius: 0.95rem;
            padding: 0.4rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        body.login-page .login-tabs .nav-item {
            margin: 0;
            display: block;
            padding: 0.04rem;
        }

        body.login-page .login-tabs .nav-link {
            border: 0;
            border-radius: 0.72rem;
            color: #6f6b7d;
            font-weight: 650;
            font-size: 0.89rem;
            line-height: 1.35;
            padding: 0.7rem 0.95rem;
            margin: 0;
            width: 100%;
            text-align: center;
            transition: color 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        body.login-page .login-tabs .nav-link:hover {
            color: #4a347f;
            background-color: rgba(255, 255, 255, 0.55);
        }

        body.login-page .login-tabs .nav-link.active {
            background: #ffffff;
            color: #432e75;
            box-shadow: 0 0.24rem 0.75rem rgba(84, 60, 146, 0.14);
            transform: translateY(-1px);
        }

        body.login-page .login-tabs .nav-link:focus-visible {
            outline: 2px solid rgba(84, 60, 146, 0.35);
            outline-offset: 1px;
            box-shadow: 0 0 0 0.2rem rgba(84, 60, 146, 0.15);
        }

        body.login-page .login-tab-content {
            padding-top: 0.6rem;
        }

        body.login-page .form-label {
            font-size: 0.88rem;
            margin-bottom: 0.42rem !important;
            color: #4b465c;
        }

        body.login-page .form-control,
        body.login-page .input-group-text {
            border-radius: 0.7rem;
            border-color: rgba(75, 70, 92, 0.24);
            min-height: 2.9rem;
        }

        body.login-page .form-control {
            padding-inline: 0.9rem;
        }

        body.login-page .input-group-merge .input-group-text {
            border-right: 0;
            background: #fff;
        }

        body.login-page .input-group-merge .form-control:not(:last-child) {
            border-left: 0;
        }

        body.login-page .form-control:focus,
        body.login-page .input-group-text:focus-within,
        body.login-page .form-check-input:focus,
        body.login-page .btn:focus-visible,
        body.login-page .login-helper-link:focus-visible {
            border-color: #543C92;
            box-shadow: 0 0 0 0.2rem rgba(84, 60, 146, 0.16);
            outline: none;
        }

        body.login-page .form-password-toggle .d-flex {
            margin-bottom: 0.4rem;
            align-items: center;
        }

        body.login-page .password-toggle-wrap {
            position: relative !important;
        }

        body.login-page .password-toggle-wrap .password-eye-btn {
            position: absolute !important;
            inset-inline-start: 10px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            border: 0 !important;
            background: transparent !important;
            padding: 6px !important;
            margin: 0 !important;
            min-width: 40px !important;
            min-height: 40px !important;
            width: 40px !important;
            height: 40px !important;
            cursor: pointer !important;
            color: #697a8d !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            opacity: 1 !important;
            visibility: visible !important;
            z-index: 10 !important;
            line-height: 1 !important;
            box-shadow: none !important;
        }

        body.login-page .password-toggle-wrap .password-eye-btn:hover {
            color: #543C92 !important;
        }

        body.login-page .password-toggle-wrap .password-eye-btn:focus-visible {
            outline: 2px solid rgba(84, 60, 146, 0.35) !important;
            outline-offset: 2px !important;
            border-radius: 0.35rem !important;
        }

        body.login-page .password-toggle-wrap .password-eye-btn svg {
            width: 20px !important;
            height: 20px !important;
            display: block !important;
            flex-shrink: 0 !important;
            opacity: 1 !important;
            visibility: visible !important;
            color: #697a8d !important;
            stroke: currentColor !important;
        }

        body.login-page .password-toggle-wrap input.form-control#password {
            padding-inline-start: 2.75rem !important;
        }

        body.login-page .login-helper-link {
            color: #543C92;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 600;
            border-radius: 0.4rem;
            padding-inline: 0.2rem;
        }

        body.login-page .login-helper-link:hover {
            color: #432e75;
            text-decoration: underline;
        }

        body.login-page .form-check {
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.45rem;
        }

        body.login-page .form-check-input {
            margin-top: 0;
            width: 1rem;
            height: 1rem;
            border-color: rgba(75, 70, 92, 0.35);
        }

        body.login-page .form-check-input:checked {
            background-color: #543C92;
            border-color: #543C92;
        }

        body.login-page .form-check-label {
            color: #4b465c;
            font-size: 0.88rem;
        }

        body.login-page .btn-login-submit {
            border-radius: 0.75rem;
            min-height: 2.9rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            box-shadow: 0 0.45rem 1rem rgba(84, 60, 146, 0.25);
            transition: all 0.2s ease;
        }

        body.login-page .btn-login-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 0.65rem 1.1rem rgba(84, 60, 146, 0.3);
        }

        body.login-page .btn-login-submit:active {
            transform: translateY(0);
        }

        @media (max-width: 767px) {
            body.login-page .authentication-inner {
                max-width: 100%;
            }

            body.login-page .authentication-inner.py-4 {
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }

            body.login-page .container-xxl {
                padding-inline: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            body.login-page .authentication-wrapper.container-p-y {
                padding-top: 0.5rem !important;
                padding-bottom: 0.5rem !important;
            }

            body.login-page .card-body {
                padding: 0.85rem !important;
            }

            body.login-page .app-brand {
                margin-bottom: 0.35rem !important;
                margin-top: 0 !important;
            }

            body.login-page .app-brand-link {
                display: block;
                line-height: 0;
            }

            body.login-page .app-brand svg {
                max-width: 100px !important;
                width: 100% !important;
                height: auto !important;
            }

            body.login-page h4 {
                font-size: 1rem;
                padding-top: 0.15rem !important;
                margin-bottom: 0.25rem !important;
            }

            body.login-page .login-subtitle {
                margin-bottom: 0.65rem;
                font-size: 0.82rem;
            }

            body.login-page .login-tabs-wrap {
                margin-bottom: 0.85rem;
            }

            body.login-page .login-tabs {
                display: flex !important;
                padding: 0.25rem;
                gap: 0.2rem;
            }

            body.login-page .login-tabs .nav-item {
                flex: 1 1 0;
                min-width: 0;
            }

            body.login-page .login-tabs .nav-link {
                font-size: 12px !important;
                padding: 0.35rem 0.25rem !important;
                line-height: 1.2 !important;
                white-space: nowrap !important;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            body.login-page .login-tab-content {
                padding-top: 0.3rem;
            }

            body.login-page .form-control,
            body.login-page .input-group-text,
            body.login-page .btn-login-submit {
                min-height: 2.5rem;
            }
        }

        @media (max-width: 400px) {
            body.login-page .app-brand svg {
                max-width: 80px !important;
            }

            body.login-page .login-tabs .nav-link {
                font-size: 11px !important;
                padding: 0.32rem 0.2rem !important;
            }
        }
    </style>
</head>

<body class="login-page">
<!-- Content -->
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-4">
            <!-- Login -->
            <div class="card">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-4 mt-2">
                        <a class="app-brand-link gap-2" href="index.html">
                            <svg width="337" height="190" viewBox="0 0 337 190" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M231.981 71.3007C231.961 68.3733 229.767 66 227.05 66C224.334 66 222.14 68.3733 222.12 71.3007V99.0068C222.091 99.3413 222.043 99.6654 222.043 100.021C222.043 112.881 212.772 123.315 201.153 123.346C201.133 123.346 201.085 123.346 201.066 123.346C201.046 123.346 201.037 123.357 201.017 123.357C200.988 123.357 200.969 123.357 200.94 123.357H200.814C198.146 123.44 196 125.772 196 128.678C196 131.585 198.146 133.916 200.814 134H201.017C201.017 134 201.017 134 201.066 134C201.066 134 201.075 134 201.085 134C218.224 134 232 118.798 232 100.021C232 99.8536 232 99.7072 232 99.5399V71.3007H231.981Z" fill="#543C92"/>
                                <path d="M210.352 107.987C210.352 112.18 207.105 115.589 203.11 115.589C199.115 115.589 195.868 112.18 195.868 107.987C195.868 103.794 199.115 100.385 203.11 100.385C207.105 100.385 210.352 103.794 210.352 107.987Z" fill="#543C92"/>
                                <path d="M332.259 35.6981C335.766 34.2802 337.768 30.801 336.724 27.9151C335.679 25.0291 331.991 23.8225 328.466 25.2001L259.579 52.6115C256.092 54.0595 254.109 57.5085 255.144 60.3844C256.178 63.2603 259.857 64.467 263.363 63.1196L332.25 35.7182L332.259 35.6981Z" fill="#543C92"/>
                                <path d="M297.487 63.6624C297.429 63.6624 297.487 63.6825 297.391 63.6825H297.266C294.431 63.773 292.151 66.1863 292.151 69.1828C292.151 72.1794 294.431 74.5928 297.266 74.6833H297.333C297.333 74.6833 297.41 74.6833 297.448 74.6833C297.458 74.6833 297.468 74.6833 297.487 74.6833C297.506 74.6833 297.516 74.6833 297.525 74.6833C309.931 74.7034 319.913 85.5332 319.913 98.8568C319.913 112.18 310.026 122.98 297.63 123.01C297.602 123.01 297.563 123.01 297.535 123.01C297.516 123.01 297.496 123.02 297.477 123.02C297.448 123.02 297.42 123.02 297.391 123.02H297.257C294.412 123.111 292.122 125.524 292.122 128.531C292.122 131.537 294.412 133.951 297.257 134.041H297.468C297.468 134.041 297.468 134.041 297.516 134.051H297.544C315.832 134.051 330.527 118.314 330.527 98.8668C330.527 79.4194 315.755 63.6825 297.468 63.6825" fill="#543C92"/>
                                <path d="M259.889 65.8646C259.841 65.8646 259.889 65.8848 259.803 65.8848H259.688C257.034 65.9753 254.898 68.2277 254.898 71.0433C254.898 73.8588 257.034 76.1113 259.688 76.1917H259.755C259.755 76.1917 259.832 76.1917 259.86 76.1917C259.87 76.1917 259.88 76.1917 259.889 76.1917C259.908 76.1917 259.918 76.1917 259.927 76.1917C271.548 76.2118 280.897 86.3578 280.897 98.8368C280.897 111.316 271.634 121.432 260.023 121.462C260.004 121.462 259.956 121.462 259.937 121.462C259.918 121.462 259.908 121.472 259.889 121.472C259.86 121.472 259.832 121.472 259.803 121.472H259.678C257.015 121.552 254.869 123.825 254.869 126.64C254.869 129.456 257.015 131.718 259.678 131.799H259.88C259.88 131.799 259.88 131.799 259.927 131.809C259.927 131.809 259.937 131.809 259.947 131.809C277.075 131.809 290.85 117.057 290.85 98.8468C290.85 80.6362 277.017 65.8848 259.88 65.8848L259.889 65.8646Z" fill="#F9BA16"/>
                                <path d="M247.483 71.526C247.463 68.6803 245.27 66.3877 242.559 66.3877C239.848 66.3877 237.654 68.6904 237.635 71.526V126.108H237.644C237.683 128.933 239.867 131.226 242.568 131.226C245.27 131.226 247.454 128.943 247.492 126.108H247.502V71.526H247.483Z" fill="#F9BA16"/>
                                <path d="M13.4886 132C11.7873 132 10.5316 131.149 9.72152 129.448C9.47848 128.962 9.35696 128.476 9.35696 127.99V69.1747C9.35696 67.4734 10.1266 66.2582 11.6658 65.5291C12.1519 65.2861 12.6785 65.1646 13.2456 65.1646H48.243C49.3772 65.1646 50.2278 65.5291 50.7949 66.2582C51.362 66.9063 51.6456 67.6759 51.6456 68.5671C51.6456 69.4582 51.362 70.2684 50.7949 70.9975C50.2278 71.6456 49.3772 71.9696 48.243 71.9696H17.4987V94.2076H46.6633C47.8785 94.2076 48.7696 94.5722 49.3367 95.3013C49.9038 95.9494 50.1873 96.719 50.1873 97.6101C50.1873 98.4203 49.8633 99.1899 49.2152 99.919C48.6481 100.648 47.7975 101.013 46.6633 101.013H17.4987V125.195H49.4582C50.5924 125.195 51.443 125.559 52.0101 126.289C52.6582 126.937 52.9823 127.706 52.9823 128.597C52.9823 129.489 52.6987 130.299 52.1316 131.028C51.5646 131.676 50.6734 132 49.4582 132H13.4886ZM141.155 102.714V128.111C141.155 129.975 140.264 131.19 138.482 131.757C137.995 131.919 137.347 132 136.537 132C135.808 132 134.998 131.676 134.107 131.028C133.216 130.38 132.77 129.408 132.77 128.111V69.1747C132.77 68.1215 133.054 67.2709 133.621 66.6228C134.674 65.6506 135.808 65.1646 137.023 65.1646H153.793C164.406 65.1646 171.94 67.676 176.395 72.6987C179.069 75.6962 180.406 79.4228 180.406 83.8785C180.406 88.3342 179.069 92.1013 176.395 95.1797C172.102 100.203 164.446 102.714 153.428 102.714H141.155ZM155.008 96.1519C160.193 96.1519 164.082 95.3013 166.674 93.6C169.995 91.4937 171.656 88.2532 171.656 83.8785C171.656 79.5848 169.995 76.4253 166.674 74.4C163.92 72.6177 160.071 71.7266 155.13 71.7266H141.155V96.1519H155.008Z" fill="#543C92"/>
                                <path d="M68.1382 69.1747C68.1382 68.1215 68.4218 67.2709 68.9888 66.6228C70.042 65.6506 71.1357 65.1646 72.2699 65.1646H92.442C103.136 65.1646 110.305 68.081 113.951 73.9139C115.409 76.1013 116.138 79.0177 116.138 82.6633C116.138 86.3089 114.923 89.3873 112.493 91.8987C110.143 94.4101 106.781 96.1519 102.407 97.1241V97.3671C106.7 98.0152 109.738 99.5949 111.52 102.106C112.898 104.294 113.789 107.089 114.194 110.491C115.085 118.025 115.652 123.818 115.895 127.868C115.895 129.246 115.45 130.299 114.558 131.028C113.748 131.676 112.817 132 111.764 132C110.71 132 109.738 131.716 108.847 131.149C107.956 130.501 107.47 129.529 107.389 128.233C106.984 124.425 106.66 121.104 106.417 118.268C106.174 115.352 105.89 112.881 105.566 110.856C105.242 108.83 104.796 107.21 104.229 105.995C103.743 104.699 103.014 103.686 102.042 102.957C100.179 101.661 96.8977 101.013 92.199 101.013H76.4015V128.111C76.4015 129.975 75.5509 131.19 73.8496 131.757C73.2825 131.919 72.5939 132 71.7838 132C71.0547 132 70.2445 131.676 69.3534 131.028C68.5433 130.38 68.1382 129.408 68.1382 128.111V69.1747ZM88.6749 94.4506C96.6142 94.4506 101.88 93.1949 104.472 90.6835C106.417 88.9013 107.389 86.3494 107.389 83.0278C107.389 78.2481 105.647 75.0481 102.164 73.4278C99.7331 72.2937 96.2496 71.7266 91.7129 71.7266H76.4015V94.4506H88.6749Z" fill="#F9BA16"/>
                                </svg>
                                
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h4 class="mb-1 pt-2">سامانه یکپارچه دکان ERP</h4>
                    <p class="login-subtitle">ورود به حساب کاربری</p>
                    @include('errors.errors')
                    @if (session('error'))
                        <p class="alert alert-danger m-0">{{ session('error') }}</p> @endif
                    <div class="nav-align-top mb-4 login-tabs-wrap">
    <ul class="nav nav-tabs px-0 mx-0 login-tabs" role="tablist">
        <li class="nav-item">
            <button aria-controls="navs-top-password" aria-selected="true" class="nav-link active" id="tab-password-login"
                data-bs-target="#navs-top-password" data-bs-toggle="tab" role="tab" type="button">ورود با رمز عبور</button>
        </li>
        <li class="nav-item">
            <button aria-controls="navs-top-mobile" aria-selected="false" class="nav-link" id="tab-mobile-login"
                data-bs-target="#navs-top-mobile" data-bs-toggle="tab" role="tab" type="button">ورود با شماره موبایل</button>
        </li>
    </ul>
    <div class="tab-content px-0 login-tab-content">
        <div class="tab-pane fade show active" id="navs-top-password" role="tabpanel" aria-labelledby="tab-password-login">
            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">ایمیل، نام کاربری یا شماره همراه</label>
                    <input autofocus class="form-control" type="text" name="email" id="email" required=""
                        autocomplete="username" placeholder="ایمیل، یوزرنیم یا شماره همراه خود را وارد کنید.">
                </div>
                <div class="mb-3 form-password-toggle">
                    <div class="d-flex justify-content-between">
                        <label class="form-label" for="password">رمز عبور</label>
                        <a class="login-helper-link" href="auth-forgot-password-basic.html">
                            <small>فراموش کرده‌اید؟</small>
                        </a>
                    </div>
                    <div class="position-relative mb-0 password-toggle-wrap">
                        <input class="form-control" type="password" required="" name="password" id="password"
                            autocomplete="current-password" placeholder="" style="padding-inline-start: 2.75rem;">
                        <button type="button" id="togglePassword" class="password-eye-btn"
                            aria-label="نمایش رمز عبور"
                            style="position:absolute; inset-inline-start:10px; top:50%; transform:translateY(-50%); border:0; background:transparent; padding:6px; min-width:40px; min-height:40px; cursor:pointer; color:#697a8d; display:flex; align-items:center; justify-content:center; z-index:10; opacity:1; visibility:visible;">
                            <svg class="icon-eye-open" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="icon-eye-off" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path>
                                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" id="remember-me" name="remember" value="1" type="checkbox" />
                        <label class="form-check-label" for="remember-me">مرا به خاطر بسپار</label>
                    </div>
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary d-grid w-100 btn-login-submit" type="submit">ورود به سیستم</button>
                </div>
            </form>
        </div>
        <div class="tab-pane fade" id="navs-top-mobile" role="tabpanel" aria-labelledby="tab-mobile-login">
            <form action="{{ route('sendSmsLogin') }}" method="POST" id="mobile-login-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="mobile">شماره موبایل</label>
                    <input class="form-control" type="tel" name="mobile" id="mobile" required=""
                        inputmode="numeric" pattern="09[0-9]{9}" maxlength="11"
                        placeholder="09xx1234567" style="direction: ltr;" autocomplete="tel">
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary d-grid w-100 btn-login-submit" type="submit">ارسال پیامک</button>
                </div>
            </form>
        </div>
    </div>
    </div>


    </div>
    </div>
    <!-- /Register -->
    </div>
    </div>
    </div>
    <!-- / Content -->
    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/i18n/i18n.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <!-- endbuild -->
    <!-- Vendors JS -->
    <script src="{{ asset('assets/') }}/vendor/libs/@form-validation/popular.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/@form-validation/bootstrap5.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/@form-validation/auto-focus.js"></script>
    <!-- Main JS -->
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <!-- Page JS -->
    <script src="{{ asset('assets/') }}/js/pages-auth.js"></script>
    <script>
        (function () {
            var loadingText = 'در حال بررسی...';
            var mobilePattern = /^09[0-9]{9}$/;

            function toAsciiDigits(str) {
                var out = '';
                for (var i = 0; i < str.length; i++) {
                    var code = str.charCodeAt(i);
                    if (code >= 0x06F0 && code <= 0x06F9) {
                        out += String(code - 0x06F0);
                    } else if (code >= 0x0660 && code <= 0x0669) {
                        out += String(code - 0x0660);
                    } else if (code >= 0x30 && code <= 0x39) {
                        out += str.charAt(i);
                    }
                }
                return out;
            }

            function sanitizeMobileValue(raw) {
                var val = toAsciiDigits(String(raw || '')).replace(/\D/g, '');

                if (val.length > 0 && val.charAt(0) !== '0') {
                    val = '0' + val;
                }
                if (val.length > 1 && val.charAt(1) !== '9') {
                    val = '09' + val.slice(2);
                }

                return val.slice(0, 11);
            }

            function isAllowedMobileChar(ch) {
                if (!ch) {
                    return true;
                }
                return /^[0-9۰-۹٠-٩]$/.test(ch);
            }

            document.querySelectorAll('.login-tab-content form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    var btn = form.querySelector('button[type="submit"]');
                    if (!btn || btn.disabled) {
                        return;
                    }

                    if (form.id === 'mobile-login-form') {
                        var mobileInput = document.getElementById('mobile');
                        var mobileVal = mobileInput ? sanitizeMobileValue(mobileInput.value) : '';
                        if (mobileInput) {
                            mobileInput.value = mobileVal;
                        }
                        if (!mobilePattern.test(mobileVal)) {
                            e.preventDefault();
                            mobileInput && mobileInput.focus();
                            return;
                        }
                    }

                    btn.disabled = true;
                    btn.textContent = loadingText;
                });
            });

            var mobileInput = document.getElementById('mobile');
            if (mobileInput) {
                mobileInput.addEventListener('beforeinput', function (e) {
                    if (e.inputType === 'insertText' || e.inputType === 'insertFromPaste' || e.inputType === 'insertCompositionText') {
                        if (e.data && !isAllowedMobileChar(e.data)) {
                            e.preventDefault();
                        }
                    }
                });

                mobileInput.addEventListener('keydown', function (e) {
                    if (e.ctrlKey || e.metaKey || e.altKey) {
                        return;
                    }
                    var allowedKeys = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];
                    if (allowedKeys.indexOf(e.key) !== -1) {
                        return;
                    }
                    if (e.key.length === 1 && !isAllowedMobileChar(e.key)) {
                        e.preventDefault();
                    }
                });

                mobileInput.addEventListener('input', function () {
                    var val = sanitizeMobileValue(this.value);
                    this.value = val;

                    if (val.length === 11) {
                        this.blur();
                    }
                });

                mobileInput.addEventListener('paste', function (e) {
                    e.preventDefault();
                    var pasted = (e.clipboardData || window.clipboardData).getData('text');
                    mobileInput.value = sanitizeMobileValue(pasted);
                    mobileInput.dispatchEvent(new Event('input'));
                });

                mobileInput.addEventListener('compositionend', function () {
                    mobileInput.value = sanitizeMobileValue(mobileInput.value);
                });
            }
        })();
    </script>
    <script>
        document.getElementById('togglePassword')?.addEventListener('click', function () {
            var pwd = document.getElementById('password');
            var open = this.querySelector('.icon-eye-open');
            var off = this.querySelector('.icon-eye-off');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                open.style.display = 'none';
                off.style.display = 'block';
                this.setAttribute('aria-label', 'پنهان کردن رمز عبور');
            } else {
                pwd.type = 'password';
                open.style.display = 'block';
                off.style.display = 'none';
                this.setAttribute('aria-label', 'نمایش رمز عبور');
            }
        });
    </script>
    </body>

</html>
