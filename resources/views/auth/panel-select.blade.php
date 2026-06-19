<!DOCTYPE html>
<html class="light-style layout-wide customizer-hide" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>انتخاب پنل - دکان</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/pages/page-auth.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        .panel-select-card {
            border: 1px solid #eceef1;
            border-radius: 12px;
            padding: 1rem 1.1rem;
            cursor: pointer;
            transition: all .2s ease;
        }

        .panel-select-card:hover,
        .panel-select-card.active {
            border-color: #7367f0;
            background: #f8f7ff;
        }

        .panel-select-card input {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="mb-1">انتخاب پنل</h4>
                        <p class="mb-4 text-muted">شما به چند پنل دسترسی دارید. مشخص کنید می‌خواهید وارد کدام پنل شوید.</p>

                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form action="{{ route('panel.switch') }}" method="POST" id="panel-select-form">
                            @csrf
                            <div class="d-flex flex-column gap-3 mb-4">
                                @foreach ($availablePanels as $panel)
                                    <label class="panel-select-card @if(($activePanel['tenant_id'] ?? null) == $panel['tenant_id']) active @endif">
                                        <input type="radio" name="tenant_id" value="{{ $panel['tenant_id'] }}"
                                            @checked(($activePanel['tenant_id'] ?? $availablePanels->first()['tenant_id']) == $panel['tenant_id']) required>
                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                            <div>
                                                <div class="fw-semibold">{{ $panel['tenant_name'] }}</div>
                                                <small class="text-muted">{{ $panel['role_label'] }}</small>
                                            </div>
                                            <x-ui.icon name="building-store" class="ti-md text-primary" />
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <button class="btn btn-primary d-grid w-100" type="submit">ورود به پنل انتخاب‌شده</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.panel-select-card').forEach((card) => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.panel-select-card').forEach((item) => item.classList.remove('active'));
                card.classList.add('active');
                card.querySelector('input[type="radio"]').checked = true;
            });
        });
    </script>
</body>

</html>
