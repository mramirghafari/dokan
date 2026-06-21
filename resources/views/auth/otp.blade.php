<!DOCTYPE html>
<html class="light-style layout-wide customizer-hide" data-assets-path="{{ asset('assets/') }}/" data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8"/>
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport"/>
    <title>کد پیامکی ورود - دکان دارمینو</title>
    <meta content="" name="description"/>
    <!-- Favicon -->
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon"/><!-- Icons -->
<!-- Core CSS -->
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet"/>
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet"/>
    <!-- Vendors CSS --><link href="{{ asset('assets/') }}/vendor/libs/typeahead-js/typeahead.css" rel="stylesheet"/>
    <!-- Vendor -->
    <link href="{{ asset('assets/') }}/vendor/libs/@form-validation/form-validation.css" rel="stylesheet"/>
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
        div#otp {
            max-width: 400px;
        }
        input.otp-input {
            width: 60px;
            height: 50px;
            margin: 5px;
            border: 1px solid #cdcaca;
            border-radius: 8px;
            text-align: center;
            font-size: 20px;
        }
    </style>
</head>

<body>
<!-- Content -->
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-4">
            <!-- Login -->
            <div class="card">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-4 mt-2">
                        <a class="app-brand-link gap-2" href="{{ asset('/') }}">
                            <svg width="67" height="71" viewBox="0 0 47 51" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11.8815 14.9595C11.8751 14.0606 11.151 13.3318 10.2545 13.3318C9.35811 13.3318 8.63396 14.0606 8.62758 14.9595V23.4672C8.61801 23.5699 8.60205 23.6694 8.60205 23.7786C8.60205 27.7275 5.5427 30.9315 1.70815 30.9411C1.70177 30.9411 1.68582 30.9411 1.67944 30.9411C1.67306 30.9411 1.66987 30.9443 1.66349 30.9443C1.65392 30.9443 1.64755 30.9443 1.63798 30.9443H1.5965C0.716023 30.97 0.0078125 31.6859 0.0078125 32.5785C0.0078125 33.471 0.716023 34.1869 1.5965 34.2126H1.66349C1.66349 34.2126 1.66349 34.2126 1.67944 34.2126C1.67944 34.2126 1.68264 34.2126 1.68583 34.2126C7.34195 34.2126 11.8879 29.5446 11.8879 23.7786C11.8879 23.7272 11.8879 23.6823 11.8879 23.6309V14.9595H11.8815Z" fill="#543C92"/>
                                <path d="M4.82349 26.6134C4.82349 27.9521 3.74204 29.0405 2.41175 29.0405C1.08146 29.0405 0 27.9521 0 26.6134C0 25.2746 1.08146 24.1863 2.41175 24.1863C3.74204 24.1863 4.82349 25.2746 4.82349 26.6134Z" fill="#543C92"/>
                                <path d="M45.4204 3.53346C46.588 3.08078 47.2547 1.96997 46.907 1.04857C46.5593 0.127167 45.3311 -0.258091 44.1571 0.181741L21.2168 8.93344C20.0556 9.39575 19.3952 10.4969 19.7398 11.4151C20.0843 12.3333 21.3093 12.7186 22.4769 12.2884L45.4172 3.53988L45.4204 3.53346Z" fill="#543C92"/>
                                <path d="M33.8412 12.4617C33.822 12.4617 33.8412 12.4681 33.8093 12.4681H33.7678C32.8235 12.497 32.0643 13.2675 32.0643 14.2242C32.0643 15.1809 32.8235 15.9514 33.7678 15.9803H33.7901C33.7901 15.9803 33.8156 15.9803 33.8284 15.9803C33.8316 15.9803 33.8348 15.9803 33.8412 15.9803C33.8475 15.9803 33.8507 15.9803 33.8539 15.9803C37.9852 15.9868 41.3093 19.4444 41.3093 23.6983C41.3093 27.9521 38.0171 31.4002 33.889 31.4098C33.8794 31.4098 33.8667 31.4098 33.8571 31.4098C33.8507 31.4098 33.8444 31.413 33.838 31.413C33.8284 31.413 33.8188 31.413 33.8093 31.413H33.7646C32.8171 31.4419 32.0547 32.2124 32.0547 33.1723C32.0547 34.1322 32.8171 34.9027 33.7646 34.9316H33.8348C33.8348 34.9316 33.8348 34.9316 33.8507 34.9349H33.8603C39.9503 34.9349 44.844 29.9105 44.844 23.7015C44.844 17.4925 39.9248 12.4681 33.8348 12.4681" fill="#543C92"/>
                                <path d="M40.0728 37.8274C40.0728 37.8274 40.0728 37.8274 40.06 37.8274H40.0409C39.6326 37.8403 39.3008 38.1742 39.3008 38.5883C39.3008 39.0025 39.6294 39.3363 40.0409 39.3492H40.0505C40.0505 39.3492 40.0632 39.3492 40.0664 39.3492C40.0664 39.3492 40.0664 39.3492 40.0728 39.3492C40.0728 39.3492 40.076 39.3492 40.0792 39.3492C41.872 39.3492 43.314 40.8517 43.314 42.6977C43.314 44.5437 41.8848 46.0398 40.0951 46.043C40.0919 46.043 40.0856 46.043 40.0824 46.043H40.076C40.0728 46.043 40.0664 46.043 40.0632 46.043H40.0441C39.6326 46.0558 39.304 46.3897 39.304 46.8071C39.304 47.2244 39.6357 47.5583 40.0441 47.5712H40.076C40.076 47.5712 40.076 47.5712 40.0824 47.5712C42.7238 47.5712 44.8484 45.3913 44.8484 42.6977C44.8484 40.0041 42.7142 37.8242 40.0728 37.8242V37.8274Z" fill="#543C92"/>
                                <path d="M38.1591 38.5819C38.1591 38.1614 37.8178 37.821 37.3999 37.821C36.982 37.821 36.6438 38.1614 36.6406 38.5819V46.82C36.647 47.2373 36.982 47.5776 37.3999 47.5776C37.8178 47.5776 38.1527 47.2405 38.1591 46.82V38.5819Z" fill="#543C92"/>
                                <path d="M25.5079 37.8274C22.8664 37.8274 20.7227 40.0105 20.7227 42.7041C20.7227 42.7554 20.7227 42.7972 20.7227 42.8485C20.7227 42.8582 20.7227 42.8774 20.7227 42.8903V43.0765V46.8263C20.729 47.2436 21.064 47.584 21.4819 47.584C21.8998 47.584 22.2348 47.2469 22.2412 46.8263V46.2677C23.0961 47.0831 24.2414 47.584 25.5047 47.584C28.1461 47.584 30.2899 45.4009 30.2899 42.7073C30.2899 40.0137 28.1461 37.8306 25.5047 37.8306L25.5079 37.8274ZM25.5079 46.0461C23.7118 46.0461 22.2571 44.5469 22.2571 42.7009C22.2571 40.8548 23.7118 39.3556 25.5079 39.3556C27.3039 39.3556 28.7586 40.8548 28.7586 42.7009C28.7586 44.5469 27.3039 46.0461 25.5079 46.0461Z" fill="#543C92"/>
                                <path d="M1.43127 39.2979C-0.460487 41.2017 -0.479624 44.2709 1.3898 46.1522C2.28304 47.0511 3.44425 47.5102 4.62141 47.5423L4.25773 47.9083C3.96743 48.2101 3.96743 48.6885 4.26411 48.9838C4.5608 49.2792 5.03613 49.2824 5.33281 48.9902L7.93915 46.3705L8.06995 46.2357C8.06995 46.2357 8.09228 46.2164 8.09866 46.2068C8.13694 46.1715 8.16247 46.1426 8.20075 46.104C10.0925 44.2002 10.1116 41.131 8.24222 39.2497C6.3728 37.3684 3.32621 37.3877 1.43127 39.2915V39.2979ZM2.51592 40.3894C3.81431 39.0828 5.89428 39.0603 7.16395 40.3349C8.43363 41.6126 8.40811 43.7058 7.10972 45.0125C5.81134 46.3191 3.73136 46.3416 2.46169 45.0671C1.19201 43.7925 1.21753 41.6961 2.51592 40.3894Z" fill="#543C92"/>
                                <path d="M35.4983 38.5819C35.4983 38.1614 35.157 37.821 34.7391 37.821C34.3211 37.821 33.983 38.1614 33.9798 38.5819V42.5565C33.9766 42.6046 33.967 42.6528 33.967 42.7009C33.967 44.547 32.5378 46.043 30.7482 46.0462C30.745 46.0462 30.7386 46.0462 30.7354 46.0462H30.729C30.7258 46.0462 30.7195 46.0462 30.7163 46.0462H30.6971C30.2856 46.0591 29.957 46.393 29.957 46.8103C29.957 47.2277 30.2888 47.5616 30.6971 47.5744H30.729C30.729 47.5744 30.729 47.5744 30.7354 47.5744C33.3769 47.5744 35.5015 45.3945 35.5015 42.7009C35.5015 42.6753 35.5015 42.656 35.5015 42.6335V38.5819H35.4983Z" fill="#543C92"/>
                                <path d="M19.5803 38.5819C19.5803 38.1614 19.239 37.821 18.8211 37.821C18.4032 37.821 18.065 38.1614 18.0618 38.5819V42.5565C18.0586 42.6046 18.0491 42.6528 18.0491 42.7009C18.0491 44.547 16.6199 46.043 14.8302 46.0462C14.827 46.0462 14.8206 46.0462 14.8175 46.0462H14.8111C14.8079 46.0462 14.8015 46.0462 14.7983 46.0462H14.7792C14.3676 46.0591 14.0391 46.393 14.0391 46.8103C14.0391 47.2277 14.3708 47.5616 14.7792 47.5744H14.8111C14.8111 47.5744 14.8111 47.5744 14.8175 47.5744C17.4589 47.5744 19.5835 45.3945 19.5835 42.7009C19.5835 42.6753 19.5835 42.656 19.5835 42.6335V38.5819H19.5803Z" fill="#543C92"/>
                                <path d="M14.6311 38.5819C14.6311 38.1614 14.2898 37.821 13.8719 37.821C13.454 37.821 13.1158 38.1614 13.1126 38.5819V42.5565C13.1094 42.6046 13.0999 42.6528 13.0999 42.7009C13.0999 44.547 11.6707 46.043 9.881 46.0462C9.87781 46.0462 9.87143 46.0462 9.86824 46.0462H9.86186C9.85867 46.0462 9.85229 46.0462 9.8491 46.0462H9.82996C9.41843 46.0591 9.08984 46.393 9.08984 46.8103C9.08984 47.2277 9.42162 47.5616 9.82996 47.5744H9.86186C9.86186 47.5744 9.86186 47.5744 9.86824 47.5744C12.5097 47.5744 14.6343 45.3945 14.6343 42.7009C14.6343 42.6753 14.6343 42.656 14.6343 42.6335V38.5819H14.6311Z" fill="#543C92"/>
                                <path d="M15.2626 50.9999C15.9427 50.9999 16.494 50.4451 16.494 49.7607C16.494 49.0763 15.9427 48.5215 15.2626 48.5215C14.5826 48.5215 14.0312 49.0763 14.0312 49.7607C14.0312 50.4451 14.5826 50.9999 15.2626 50.9999Z" fill="#543C92"/>
                                <path d="M18.3408 50.9999C19.0208 50.9999 19.5722 50.4451 19.5722 49.7607C19.5722 49.0763 19.0208 48.5215 18.3408 48.5215C17.6607 48.5215 17.1094 49.0763 17.1094 49.7607C17.1094 50.4451 17.6607 50.9999 18.3408 50.9999Z" fill="#543C92"/>
                                <path d="M11.2236 37.8564C11.9037 37.8564 12.455 37.3016 12.455 36.6172C12.455 35.9327 11.9037 35.3779 11.2236 35.3779C10.5435 35.3779 9.99219 35.9327 9.99219 36.6172C9.99219 37.3016 10.5435 37.8564 11.2236 37.8564Z" fill="#543C92"/>
                                <path d="M21.3201 13.1648C21.3041 13.1648 21.3201 13.1712 21.2914 13.1712H21.2531C20.3694 13.2001 19.658 13.9193 19.658 14.8182C19.658 15.7171 20.3694 16.4363 21.2531 16.4619H21.2754C21.2754 16.4619 21.3009 16.4619 21.3105 16.4619C21.3137 16.4619 21.3169 16.4619 21.3201 16.4619C21.3264 16.4619 21.3296 16.4619 21.3328 16.4619C25.2025 16.4684 28.3161 19.7077 28.3161 23.6919C28.3161 27.676 25.2312 30.9058 21.3647 30.9154C21.3584 30.9154 21.3424 30.9154 21.336 30.9154C21.3296 30.9154 21.3264 30.9186 21.3201 30.9186C21.3105 30.9186 21.3009 30.9186 21.2914 30.9186H21.2499C20.363 30.9443 19.6484 31.6699 19.6484 32.5688C19.6484 33.4677 20.363 34.1901 21.2499 34.2158H21.3169C21.3169 34.2158 21.3169 34.2158 21.3328 34.219C21.3328 34.219 21.336 34.219 21.3392 34.219C27.0432 34.219 31.6306 29.5092 31.6306 23.6951C31.6306 17.8809 27.024 13.1712 21.3169 13.1712L21.3201 13.1648Z" fill="#F9BA16"/>
                                <path d="M17.1896 14.9723C17.1832 14.0638 16.4527 13.3318 15.5499 13.3318C14.6471 13.3318 13.9165 14.067 13.9102 14.9723V32.3987H13.9133C13.9261 33.3008 14.6535 34.0328 15.5531 34.0328C16.4527 34.0328 17.18 33.304 17.1928 32.3987H17.196V14.9723H17.1896Z" fill="#F9BA16"/>
                            </svg>

                        </a>
                    </div>
                    <!-- /Logo -->
                    <h4 class="mb-1 pt-2">دکان دارمینو</h4>
                    <p class="mb-4">کد ارسال شده به شماره {{ session('mobile') }} را وارد کنید:</p>
                    @include('errors.errors')
                    @if(session('error'))
                        <p class="alert alert-danger m-0">{{ session('error') }}</p>
                    @endif
                    <form id="otpForm" action="{{ route('vilidationCode') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <div id="otp" class="inputs d-flex flex-row-reverse justify-content-center mt-2">
                                <input type="text" maxlength="1" class="otp-input" name="otp[]" inputmode="numeric" pattern="[0-9]*" autofocus>
                                <input type="text" maxlength="1" class="otp-input" name="otp[]" inputmode="numeric" pattern="[0-9]*">
                                <input type="text" maxlength="1" class="otp-input" name="otp[]" inputmode="numeric" pattern="[0-9]*">
                                <input type="text" maxlength="1" class="otp-input" name="otp[]" inputmode="numeric" pattern="[0-9]*">
                            </div>
                        </div>
                        <div class="mb-3">
                            <button class="btn btn-primary d-grid w-100 sbt" type="submit">ورود به دکان</button>
                        </div>
                    </form>
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
        var otpLength = 4;
        var form = document.getElementById('otpForm');
        var submitBtn = form ? form.querySelector('button[type="submit"]') : null;
        var isSubmitting = false;

        function setSubmitLoading() {
            if (!submitBtn || submitBtn.disabled) {
                return;
            }
            submitBtn.disabled = true;
            submitBtn.textContent = loadingText;
        }

        function getOtpValue() {
            var inputs = document.querySelectorAll('.otp-input');
            var code = '';
            inputs.forEach(function (input) {
                code += input.value;
            });
            return code;
        }

        function tryAutoSubmit() {
            if (!form || isSubmitting) {
                return;
            }
            if (getOtpValue().length === otpLength) {
                isSubmitting = true;
                setSubmitLoading();
                form.submit();
            }
        }

        if (form) {
            form.addEventListener('submit', function () {
                isSubmitting = true;
                setSubmitLoading();
            });
        }

        var inputs = document.querySelectorAll('.otp-input');
        inputs.forEach(function (input, index) {
            input.addEventListener('input', function () {
                var digit = input.value.replace(/\D/g, '').slice(0, 1);
                input.value = digit;

                if (digit.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }

                tryAutoSubmit();
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace' && input.value === '' && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', function (e) {
                e.preventDefault();
                var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, otpLength);
                pasted.split('').forEach(function (char, i) {
                    if (inputs[i]) {
                        inputs[i].value = char;
                    }
                });
                if (pasted.length > 0) {
                    var focusIndex = Math.min(pasted.length, inputs.length - 1);
                    inputs[focusIndex].focus();
                }
                tryAutoSubmit();
            });
        });
    })();
</script>
</body>

</html>
