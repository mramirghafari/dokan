<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>پرونده ۳۶۰ {{ $customer->name }} - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <style>
        .timeline-360 { position: relative; padding-right: 1.25rem; }
        .timeline-360::before { content: ''; position: absolute; top: 0; bottom: 0; right: .45rem; width: 2px; background: #e7e7e8; }
        .timeline-360-item { position: relative; padding: 0 0 1.25rem 0; }
        .timeline-360-item::before { content: ''; position: absolute; right: -.05rem; top: .35rem; width: .75rem; height: .75rem; border-radius: 50%; background: var(--bs-primary); box-shadow: 0 0 0 4px #fff; }
    </style>
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
                    @include('crm.partials.hub_bar', ['hubActive' => ''])
                    @include('partials.erp-remote-select-assets')
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                        <div>
                            <h4 class="mb-1">پرونده ۳۶۰ مشتری</h4>
                            <p class="text-muted mb-0">{{ $customer->name }} @if($customer->mobile) — {{ $customer->mobile }} @endif</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-label-secondary">پروفایل کلاسیک</a>
                            <a href="{{ route('customers.index') }}" class="btn btn-label-dark">لیست مشتریان</a>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        @foreach ([
                            ['label' => 'سفارش‌ها', 'value' => $stats['orders_total']],
                            ['label' => 'سفارش فعال', 'value' => $stats['orders_active']],
                            ['label' => 'فروش تاییدشده', 'value' => number_format($stats['revenue_total'])],
                            ['label' => 'پیگیری باز', 'value' => $stats['open_followups']],
                            ['label' => 'فرصت باز', 'value' => $stats['open_opportunities']],
                            ['label' => 'تیکت باز', 'value' => $stats['open_tickets']],
                        ] as $card)
                            <div class="col-6 col-md-4 col-xl-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <small class="text-muted">{{ $card['label'] }}</small>
                                        <h4 class="mb-0 mt-1">{{ $card['value'] }}</h4>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">اقدام سریع</h5>
                        </div>
                        <div class="card-body d-flex flex-wrap gap-2">
                            @foreach ($quick_actions as $action)
                                <a href="{{ $action['url'] }}" class="btn btn-outline-primary">
                                    <i class="ti {{ $action['icon'] }} me-1"></i>{{ $action['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">تایم‌لاین تعاملات</h5>
                            <span class="badge bg-label-primary">{{ count($events) }} رویداد</span>
                        </div>
                        <div class="card-body">
                            @if(count($events) === 0)
                                <p class="text-muted mb-0">هنوز رویدادی برای این مشتری ثبت نشده است.</p>
                            @else
                                <div class="timeline-360">
                                    @foreach ($events as $event)
                                        <div class="timeline-360-item pe-4">
                                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                                <div>
                                                    <span class="badge bg-label-{{ $event['badge'] }} mb-2">{{ $event['type_label'] }}</span>
                                                    <h6 class="mb-1">
                                                        @if(!empty($event['url']) && $event['url'] !== '#')
                                                            <a href="{{ $event['url'] }}">{{ $event['title'] }}</a>
                                                        @else
                                                            {{ $event['title'] }}
                                                        @endif
                                                    </h6>
                                                    @if(!empty($event['description']))
                                                        <p class="text-muted mb-0 small">{{ \Illuminate\Support\Str::limit($event['description'], 180) }}</p>
                                                    @endif
                                                </div>
                                                <small class="text-muted">{{ $event['occurred_at']->format('Y-m-d H:i') }}</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
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
