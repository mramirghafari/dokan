<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>{{ $Customer->name }} — پرونده مشتری | دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link rel="stylesheet" href="{{ asset('assets/vendor/libs/neshan-sdk/v1.1.5/index.css') }}" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <link href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
</head>

<body>
@include('partials.panel-toasts')
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('sections.sidebar')
        <div class="layout-page">
            @include('sections.header')
            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y customer-profile-page">

                    {{-- Hero --}}
                    <div class="customer-profile-hero card border-0 mb-4" id="tour-customer-hero">
                        <div class="card-body p-4 p-lg-5">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                                <div>
                                    <nav aria-label="breadcrumb" class="customer-profile-breadcrumb mb-2" id="tour-customer-breadcrumb">
                                        <ol>
                                            <li><a href="{{ route('customers.index') }}">مشتریان</a></li>
                                            @if ($taskContext)
                                                <li class="customer-profile-breadcrumb__sep" aria-hidden="true">/</li>
                                                <li><span>{{ $taskContext['region'] }} / {{ $taskContext['area'] }}</span></li>
                                            @endif
                                            <li class="customer-profile-breadcrumb__sep" aria-hidden="true">/</li>
                                            <li class="customer-profile-breadcrumb__current">پرونده مشتری</li>
                                        </ol>
                                    </nav>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2" id="tour-customer-identity">
                                        <h3 class="text-white mb-0">{{ $Customer->name }}</h3>
                                        @if ($badges['active'])
                                            <span class="customer-profile-pill customer-profile-pill--active">
                                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="8" cy="8" r="6.25" stroke="currentColor" stroke-width="1.5"/><path d="M5.25 8.1L7.1 9.95L10.85 6.2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                فعال
                                            </span>
                                        @endif
                                        @if ($badges['loyal'])
                                            <span class="customer-profile-pill customer-profile-pill--loyal">
                                                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 13.5S3.5 10.2 3.5 6.6C3.5 4.9 4.8 3.5 6.5 3.5C7.45 3.5 8.3 4 8 4.8C7.7 4 8.55 3.5 9.5 3.5C11.2 3.5 12.5 4.9 12.5 6.6C12.5 10.2 8 13.5 8 13.5Z" fill="currentColor"/></svg>
                                                وفادار
                                            </span>
                                        @endif
                                        @if ((int) $Customer->status !== 1)
                                            <span class="badge bg-label-danger">غیرفعال</span>
                                        @endif
                                    </div>
                                    <p class="text-white-50 mb-0">
                                        کد مشتری <strong class="text-white">#{{ $Customer->customer_code }}</strong>
                                        <span class="mx-2">·</span>
                                        عضویت {{ verta($Customer->created_at)->format('Y/m/d H:i') }}
                                    </p>
                                </div>
                                <div class="d-flex flex-wrap gap-2" id="tour-customer-actions">
                                    <a href="{{ route('customers.360', $Customer->id) }}" class="btn btn-light btn-sm">
                                        <x-ui.icon name="layout-grid" class="me-1" />پرونده ۳۶۰
                                    </a>
                                    <a href="{{ route('customers.edit', $Customer->id) }}" class="btn btn-warning btn-sm">
                                        <x-ui.icon name="edit" class="me-1" />ویرایش
                                    </a>
                                    <a href="{{ session('backlink', route('customers.index')) }}" class="btn btn-outline-light btn-sm">
                                        <span class="d-inline-flex align-items-center gap-1">
                                            بازگشت
                                            <x-ui.icon name="arrow-left" />
                                        </span>
                                    </a>
                                    @if ($canDelete)
                                        <form id="customer-delete-form" action="{{ route('customers.destroy', $Customer->id) }}" method="POST" class="d-inline">
                                            @csrf @method('delete')
                                            <button type="button" id="customer-delete-btn" class="btn btn-outline-danger btn-sm customer-profile-btn-delete">حذف</button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            @php
                                $heroKpis = [
                                    ['icon' => 'ti-shopping-cart', 'label' => 'کل سفارش‌ها', 'value' => number_format($metrics['orders_total'])],
                                    ['icon' => 'ti-receipt', 'label' => 'سفارش فعال', 'value' => number_format($metrics['orders_active'])],
                                    ['icon' => 'ti-currency-dollar', 'label' => 'مجموع خرید', 'value' => number_format($metrics['revenue_total'])],
                                    ['icon' => 'ti-wallet', 'label' => 'مانده حساب', 'value' => number_format($metrics['account_balance'])],
                                ];
                                if ($isSubscriptionPanel && $metrics['subscription']) {
                                    $heroKpis[] = [
                                        'icon' => 'ti-calendar-stats',
                                        'label' => 'مانده اشتراک',
                                        'value' => $metrics['subscription']['label'],
                                    ];
                                }
                            @endphp
                            <div class="customer-profile-kpi-row customer-profile-kpi-row--{{ count($heroKpis) }}" id="tour-customer-kpis">
                                @foreach ($heroKpis as $kpi)
                                    <div class="customer-profile-kpi">
                                        <div class="customer-profile-kpi__icon"><x-ui.icon :name="$kpi['icon']" /></div>
                                        <div>
                                            <div class="customer-profile-kpi__value">{{ $kpi['value'] }}</div>
                                            <div class="customer-profile-kpi__label">{{ $kpi['label'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        {{-- Sidebar --}}
                        <div class="col-xl-4 col-lg-5">
                            <div class="card mb-4" id="tour-customer-contact-card">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">اطلاعات تماس و هویت</h5>
                                </div>
                                <div class="card-body">
                                    <dl class="customer-profile-dl">
                                        <div><dt>نام کامل</dt><dd>{{ $Customer->name ?: '—' }}</dd></div>
                                        <div><dt>تابلو / برند</dt><dd>{{ $Customer->tablo ?: '—' }}</dd></div>
                                        <div><dt>موبایل</dt><dd dir="ltr">@if($Customer->mobile)<a href="tel:{{ $Customer->mobile }}">{{ $Customer->mobile }}</a>@else — @endif</dd></div>
                                        <div><dt>تلفن ثابت</dt><dd dir="ltr">@if($Customer->phone)<a href="tel:{{ $Customer->phone }}">{{ $Customer->phone }}</a>@else — @endif</dd></div>
                                        <div><dt>کد ملی</dt><dd dir="ltr">{{ $Customer->national_id ?: '—' }}</dd></div>
                                        <div><dt>صنف / کانال</dt><dd>{{ trim(($Customer->senf ?: '—') . ' / ' . ($Customer->channel ?: '—'), ' /') }}</dd></div>
                                        <div><dt>منطقه / مسیر</dt><dd>{{ trim(($team['region'] ?: '—') . ' / ' . ($team['area'] ?: '—'), ' /') }}</dd></div>
                                        <div><dt>بازاریاب</dt><dd>{{ $team['marketer'] ?: '—' }}</dd></div>
                                        <div><dt>ثبت‌کننده</dt><dd>{{ $team['registrar'] ?: '—' }}</dd></div>
                                        <div><dt>سرپرست</dt><dd>{{ $team['leader'] ?: '—' }}</dd></div>
                                        <div><dt>مدیر فروش</dt><dd>{{ $team['sales_manager'] ?: '—' }}</dd></div>
                                        <div><dt>آدرس</dt><dd>{{ $Customer->address ?: '—' }}</dd></div>
                                        @if ($Customer->store_address)
                                            <div><dt>آدرس انبار</dt><dd>{{ $Customer->store_address }}</dd></div>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </div>

                        {{-- Main tabs --}}
                        <div class="col-xl-8 col-lg-7">
                            <div class="card mb-4" id="tour-customer-tabs-card">
                                <div class="card-header border-bottom" id="tour-customer-tabs-header">
                                    <ul class="nav nav-tabs card-header-tabs" id="tour-customer-tabs-nav" role="tablist">
                                        <li class="nav-item"><button class="nav-link active" id="tour-customer-tab-crm" data-bs-toggle="tab" data-bs-target="#tab-crm" type="button">CRM و فروش</button></li>
                                        <li class="nav-item"><button class="nav-link" id="tour-customer-tab-orders" data-bs-toggle="tab" data-bs-target="#tab-orders" type="button">تاریخچه خرید</button></li>
                                        <li class="nav-item"><button class="nav-link" id="tour-customer-tab-financial" data-bs-toggle="tab" data-bs-target="#tab-financial" type="button">رفتار مالی</button></li>
                                        @if ($locationTabEnabled)
                                            <li class="nav-item"><button class="nav-link" id="tour-customer-tab-location" data-bs-toggle="tab" data-bs-target="#tab-location" type="button">موقعیت</button></li>
                                        @endif
                                    </ul>
                                    <div class="mt-3 mt-md-0" id="tour-customer-new-order">
                                        @if ($canNewOrder)
                                            <form method="GET" action="{{ route('products.index') }}" class="d-inline">
                                                <input type="hidden" name="Customer" value="{{ $Customer->id }}" />
                                                @if ($MyTask)<input type="hidden" name="Task" value="{{ $MyTask->id }}" />@endif
                                                <button class="btn btn-primary btn-sm" type="submit"><x-ui.icon name="plus" class="me-1" />سفارش جدید</button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-label-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modal-no-location">
                                                <x-ui.icon name="map-pin-off" class="me-1" />سفارش جدید
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body tab-content p-0">
                                    {{-- CRM tab --}}
                                    <div class="tab-pane fade show active p-4" id="tab-crm">
                                        @if (!empty($assignments['marketer']) || !empty($assignments['supervisor']) || !empty($assignments['sales_manager']))
                                            <div class="row g-3 mb-4" id="tour-customer-assignments">
                                                @if (!empty($assignments['marketer']))
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="customer-profile-assignment-card">
                                                            <div class="customer-profile-assignment-card__icon"><x-ui.icon name="user-star" /></div>
                                                            <div>
                                                                <small class="text-muted d-block mb-1">بازاریاب اختصاص‌یافته</small>
                                                                <div class="fw-semibold">{{ $assignments['marketer']['name'] }}</div>
                                                                @if ($assignments['marketer']['mobile'])
                                                                    <a href="tel:{{ $assignments['marketer']['mobile'] }}" class="small" dir="ltr">{{ $assignments['marketer']['mobile'] }}</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if (!empty($assignments['supervisor']))
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="customer-profile-assignment-card">
                                                            <div class="customer-profile-assignment-card__icon customer-profile-assignment-card__icon--supervisor"><x-ui.icon name="shield-check" /></div>
                                                            <div>
                                                                <small class="text-muted d-block mb-1">سرپرست اختصاص‌یافته</small>
                                                                <div class="fw-semibold">{{ $assignments['supervisor']['name'] }}</div>
                                                                @if ($assignments['supervisor']['mobile'])
                                                                    <a href="tel:{{ $assignments['supervisor']['mobile'] }}" class="small" dir="ltr">{{ $assignments['supervisor']['mobile'] }}</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                @if (!empty($assignments['sales_manager']))
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="customer-profile-assignment-card">
                                                            <div class="customer-profile-assignment-card__icon customer-profile-assignment-card__icon--sales-manager"><x-ui.icon name="briefcase" /></div>
                                                            <div>
                                                                <small class="text-muted d-block mb-1">مدیر فروش اختصاص‌یافته</small>
                                                                <div class="fw-semibold">{{ $assignments['sales_manager']['name'] }}</div>
                                                                @if ($assignments['sales_manager']['mobile'])
                                                                    <a href="tel:{{ $assignments['sales_manager']['mobile'] }}" class="small" dir="ltr">{{ $assignments['sales_manager']['mobile'] }}</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="row g-3 mb-4" id="tour-customer-crm-stats">
                                            @foreach ([
                                                ['label' => 'پیگیری باز', 'value' => $crm['stats']['open_followups'], 'class' => ''],
                                                ['label' => 'پیگیری معوق', 'value' => $crm['stats']['overdue_followups'], 'class' => 'text-danger'],
                                                ['label' => 'فرصت باز', 'value' => $crm['stats']['open_opportunities'], 'class' => ''],
                                                ['label' => 'ارزش فرصت', 'value' => number_format($crm['stats']['open_opportunity_amount']), 'class' => 'text-success'],
                                            ] as $stat)
                                                <div class="col-6 col-md-3">
                                                    <div class="border rounded p-3 h-100 text-center">
                                                        <small class="text-muted d-block">{{ $stat['label'] }}</small>
                                                        <div class="h5 mb-0 mt-1 {{ $stat['class'] }}">{{ $stat['value'] }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 mb-4" id="tour-customer-crm-links">
                                            <a class="btn btn-sm btn-label-primary" href="{{ route('crm.followups.index', ['subject_type' => 'customer', 'search' => $Customer->name]) }}">کارتابل پیگیری</a>
                                            <a class="btn btn-sm btn-label-info" href="{{ route('crm.opportunities.index', ['search' => $Customer->name]) }}">Pipeline فروش</a>
                                        </div>
                                        <div class="row g-4" id="tour-customer-crm-lists">
                                            <div class="col-lg-6">
                                                <h6 class="mb-3">آخرین پیگیری‌ها</h6>
                                                @forelse($crm['followups'] as $followup)
                                                    <div class="border rounded p-3 mb-2">
                                                        <div class="d-flex justify-content-between gap-2">
                                                            <strong class="small">{{ $followup->title }}</strong>
                                                            <span class="badge bg-label-{{ in_array($followup->status, ['open', 'in_progress'], true) ? 'warning' : 'secondary' }}">{{ $followup->statusText() }}</span>
                                                        </div>
                                                        <small class="text-muted">{{ $followup->typeText() }} — {{ optional($followup->assignedUser)->name ?: 'بدون مسئول' }}</small>
                                                    </div>
                                                @empty
                                                    <div class="customer-profile-empty py-4">
                                                        <p class="text-muted small mb-0">پیگیری CRM ثبت نشده است.</p>
                                                    </div>
                                                @endforelse
                                            </div>
                                            <div class="col-lg-6">
                                                <h6 class="mb-3">فرصت‌های فروش</h6>
                                                @forelse($crm['opportunities'] as $opportunity)
                                                    <div class="border rounded p-3 mb-2">
                                                        <div class="d-flex justify-content-between gap-2">
                                                            <strong class="small">{{ $opportunity->title }}</strong>
                                                            <span class="badge bg-label-{{ $opportunity->status === 'won' ? 'success' : ($opportunity->status === 'lost' ? 'danger' : 'primary') }}">{{ $opportunity->stageText() }}</span>
                                                        </div>
                                                        <small class="text-muted">{{ number_format($opportunity->amount) }} ریال — {{ optional($opportunity->assignedUser)->name ?: 'بدون مسئول' }}</small>
                                                    </div>
                                                @empty
                                                    <div class="customer-profile-empty py-4">
                                                        <p class="text-muted small mb-0">فرصت فروشی ثبت نشده است.</p>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Orders tab --}}
                                    <div class="tab-pane fade p-4" id="tab-orders">
                                        <div id="tour-customer-orders-panel">
                                        @if ($orders->isEmpty())
                                            <div class="customer-profile-empty text-center py-5">
                                                <div class="customer-profile-empty__icon"><x-ui.icon name="shopping-bag" /></div>
                                                <h6 class="mb-2">هنوز سفارشی ثبت نشده</h6>
                                                <p class="text-muted small mb-3">اولین فاکتور یا پیش‌فاکتور این مشتری از اینجا قابل پیگیری است.</p>
                                                @if ($canNewOrder)
                                                    <form method="GET" action="{{ route('products.index') }}">
                                                        <input type="hidden" name="Customer" value="{{ $Customer->id }}" />
                                                        <button class="btn btn-primary btn-sm" type="submit">ثبت اولین سفارش</button>
                                                    </form>
                                                @endif
                                            </div>
                                        @else
                                            <div class="table-responsive" id="tour-customer-orders-table">
                                                <table class="table table-hover align-middle mb-0 customer-profile-orders-table">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>سفارش</th>
                                                            <th>تاریخ</th>
                                                            <th class="text-end">مبلغ</th>
                                                            <th>وضعیت</th>
                                                            <th>پرداخت</th>
                                                            <th>بازاریاب</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($orders as $order)
                                                            <tr class="customer-profile-order-row" data-order-id="{{ $order['id'] }}">
                                                                <td>
                                                                    <div class="fw-medium">#{{ $order['id'] }}</div>
                                                                    @if ($order['invoice_id'])
                                                                        <small class="text-muted">فاکتور {{ $order['invoice_id'] }}</small>
                                                                    @endif
                                                                </td>
                                                                <td><small>{{ verta($order['created_at'])->format('Y/m/d') }}</small></td>
                                                                <td class="text-end fw-medium">{{ number_format($order['amount']) }}</td>
                                                                <td><span class="badge bg-label-{{ $order['status']['class'] }}">{{ $order['status']['label'] }}</span></td>
                                                                <td><span class="badge bg-label-{{ $order['payment']['class'] }}">{{ $order['payment']['label'] }}</span></td>
                                                                <td><small>{{ $order['visitor'] ?: '—' }}</small></td>
                                                                <td class="text-nowrap">
                                                                    <button type="button" class="btn btn-sm btn-icon btn-label-secondary order-toggle-items" title="اقلام">
                                                                        <x-ui.icon name="chevron-down" />
                                                                    </button>
                                                                    <a href="{{ $order['view_url'] }}" class="btn btn-sm btn-icon btn-label-primary" title="مشاهده">
                                                                        <x-ui.icon name="eye" />
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                            <tr class="customer-profile-order-items d-none" data-items-for="{{ $order['id'] }}">
                                                                <td colspan="7" class="bg-light-subtle">
                                                                    <div class="py-2 px-1">
                                                                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                                                            <strong class="small">{{ $order['items_count'] }} قلم کالا</strong>
                                                                            @if ($order['delivery_date'])
                                                                                <small class="text-muted">تحویل: {{ $order['delivery_date'] }}</small>
                                                                            @endif
                                                                            @if ($isSubscriptionPanel && $order['subscription_end'])
                                                                                <small class="text-muted">پایان اشتراک: {{ \Illuminate\Support\Carbon::parse($order['subscription_end'])->format('Y/m/d') }}</small>
                                                                            @endif
                                                                        </div>
                                                                        <ul class="list-unstyled mb-0 small">
                                                                            @foreach ($order['items'] as $item)
                                                                                <li class="d-flex justify-content-between py-1 border-bottom border-light">
                                                                                    <span>{{ $item['title'] }}</span>
                                                                                    <span class="text-muted">{{ number_format($item['quantity']) }} {{ $item['unit'] }}</span>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                        </div>
                                    </div>

                                    {{-- Financial tab --}}
                                    <div class="tab-pane fade p-4" id="tab-financial">
                                        <div id="tour-customer-financial-panel">
                                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4" id="tour-customer-financial-summary">
                                                <div>
                                                    <h5 class="mb-1">رفتار مالی مشتری</h5>
                                                    <p class="text-muted small mb-0">گردش خرید، واریزی‌ها و وضعیت بدهکار / بستانکار بر اساس سفارش‌های تأییدشده</p>
                                                </div>
                                                <span class="badge bg-label-{{ $financial['status'] === 'creditor' ? 'success' : ($financial['status'] === 'debtor' ? 'danger' : 'secondary') }} fs-6">
                                                    {{ $financial['status_label'] }}
                                                </span>
                                            </div>

                                            <div class="row g-3 mb-4">
                                                @foreach ([
                                                    ['label' => 'جمع خریدها', 'value' => number_format($financial['total_purchases']), 'class' => 'text-body'],
                                                    ['label' => 'جمع واریزی‌ها', 'value' => number_format($financial['total_payments']), 'class' => 'text-success'],
                                                    ['label' => 'تسویه‌نشده', 'value' => number_format($financial['unsettled_amount']), 'class' => 'text-danger'],
                                                    ['label' => 'مانده نهایی', 'value' => number_format(abs($financial['balance'])), 'class' => $financial['status'] === 'creditor' ? 'text-success' : ($financial['status'] === 'debtor' ? 'text-danger' : 'text-body')],
                                                ] as $finStat)
                                                    <div class="col-6 col-lg-3">
                                                        <div class="customer-profile-fin-stat border rounded p-3 h-100 text-center">
                                                            <small class="text-muted d-block">{{ $finStat['label'] }}</small>
                                                            <div class="h5 mb-0 mt-1 {{ $finStat['class'] }}">{{ $finStat['value'] }} <small class="fs-6 fw-normal">ریال</small></div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            @if (count($financial['transactions']) > 0)
                                                <div class="table-responsive" id="tour-customer-financial-ledger">
                                                    <table class="table table-hover align-middle mb-0 customer-profile-financial-table">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>تاریخ</th>
                                                                <th>نوع</th>
                                                                <th>شرح</th>
                                                                <th>روش پرداخت</th>
                                                                <th class="text-end">بدهکار</th>
                                                                <th class="text-end">بستانکار</th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($financial['transactions'] as $txn)
                                                                <tr>
                                                                    <td><small>{{ optional($txn['date'])->format('Y/m/d') }}</small></td>
                                                                    <td><span class="badge bg-label-{{ $txn['type_class'] }}">{{ $txn['type_label'] }}</span></td>
                                                                    <td>{{ $txn['description'] }}</td>
                                                                    <td><span class="badge bg-label-{{ $txn['payment']['class'] }}">{{ $txn['payment']['label'] }}</span></td>
                                                                    <td class="text-end {{ $txn['debit'] ? 'text-danger fw-medium' : 'text-muted' }}">
                                                                        {{ $txn['debit'] ? number_format($txn['debit']) : '—' }}
                                                                    </td>
                                                                    <td class="text-end {{ $txn['credit'] ? 'text-success fw-medium' : 'text-muted' }}">
                                                                        {{ $txn['credit'] ? number_format($txn['credit']) : '—' }}
                                                                    </td>
                                                                    <td class="text-nowrap">
                                                                        <a href="{{ $txn['view_url'] }}" class="btn btn-sm btn-icon btn-label-primary" title="مشاهده سفارش">
                                                                            <x-ui.icon name="eye" />
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="customer-profile-empty text-center py-5" id="tour-customer-financial-empty">
                                                    <div class="customer-profile-empty__icon"><x-ui.icon name="report-money" /></div>
                                                    <h6 class="mb-2">گردش مالی ثبت نشده</h6>
                                                    <p class="text-muted small mb-0">پس از ثبت و تأیید اولین سفارش یا واریز، گردش بدهکار/بستانکار اینجا نمایش داده می‌شود.</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($locationTabEnabled)
                                    {{-- Location tab --}}
                                    <div class="tab-pane fade p-4" id="tab-location">
                                        <form method="POST" action="{{ route('update_customer_loc', $Customer->id) }}">
                                            @csrf
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <p class="text-muted small mb-0">موقعیت فروشگاه و انبار را روی نقشه تنظیم و ذخیره کنید.</p>
                                                <button class="btn btn-success btn-sm" type="submit"><x-ui.icon name="device-floppy" class="me-1" />ذخیره موقعیت</button>
                                            </div>
                                            <div class="row g-3" id="tour-customer-location-maps">
                                                <div class="col-md-6">
                                                    <h6 class="mb-2">فروشگاه</h6>
                                                    <div id="map_get" class="customer-profile-map rounded"></div>
                                                    <input type="hidden" id="shop_lat" name="shop_lat" value="{{ $Customer->shop_lat }}" />
                                                    <input type="hidden" id="shop_lng" name="shop_lng" value="{{ $Customer->shop_lng }}" />
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="mb-2">انبار</h6>
                                                    <div id="map_get_store" class="customer-profile-map rounded"></div>
                                                    <input type="hidden" id="store_lat" name="store_lat" value="{{ $Customer->store_lat }}" />
                                                    <input type="hidden" id="store_lng" name="store_lng" value="{{ $Customer->store_lng }}" />
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                @include('sections.footer')
                <div class="content-backdrop fade"></div>
            </div>
        </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
    <div class="drag-target"></div>
</div>

<div class="modal fade" id="modal-no-location" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">ثبت سفارش ممکن نیست</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                @if ($locationTabEnabled)
                    برای این مشتری موقعیت فروشگاه ثبت نشده است. ابتدا در تب «موقعیت» لوکیشن را ثبت کنید.
                @else
                    امکان ثبت سفارش برای این مشتری در حال حاضر وجود ندارد.
                @endif
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
<script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
<script src="{{ asset('assets/') }}/js/main.js"></script>
@if ($locationTabEnabled)
<script src="{{ asset('assets/vendor/libs/neshan-sdk/v1.1.5/index.js') }}"></script>
@endif
<script>
    document.querySelectorAll('.order-toggle-items').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var row = btn.closest('tr');
            var id = row.dataset.orderId;
            var itemsRow = document.querySelector('[data-items-for="' + id + '"]');
            if (!itemsRow) return;
            itemsRow.classList.toggle('d-none');
            btn.querySelector('i').classList.toggle('ti-chevron-down');
            btn.querySelector('i').classList.toggle('ti-chevron-up');
        });
    });

    var deleteBtn = document.getElementById('customer-delete-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            Swal.fire({
                icon: 'warning',
                title: 'حذف مشتری',
                text: 'آیا از حذف این مشتری اطمینان دارید؟ این عمل قابل بازگشت نیست.',
                showCancelButton: true,
                confirmButtonText: 'بله، حذف شود',
                cancelButtonText: 'انصراف',
                confirmButtonColor: '#d33',
            }).then(function(result) {
                if (result.isConfirmed) {
                    document.getElementById('customer-delete-form').submit();
                }
            });
        });
    }

    @if ($locationTabEnabled)
    var mapsInitialized = false;
    function initCustomerMaps() {
        if (mapsInitialized) return;
        mapsInitialized = true;
        function initMap(containerId, latInputId, lngInputId, lat, lng) {
            var map = new nmp_mapboxgl.Map({
                mapType: nmp_mapboxgl.Map.mapTypes.neshanVector,
                container: containerId,
                zoom: 14,
                center: [lng, lat],
                minZoom: 2,
                maxZoom: 21,
                trackResize: true,
                mapKey: "web.69873d4db05f495bb49de6c13e8eb294",
                poi: false,
                traffic: false,
                mapTypeControllerOptions: { show: false }
            });
            var marker = new nmp_mapboxgl.Marker({ color: "#F9BA16", draggable: true })
                .setLngLat([lng, lat])
                .addTo(map);
            marker.on('dragend', function() {
                var p = marker.getLngLat();
                document.getElementById(latInputId).value = p.lat;
                document.getElementById(lngInputId).value = p.lng;
            });
        }
        initMap('map_get', 'shop_lat', 'shop_lng',
            {{ $Customer->shop_lat ?? 35.700954 }}, {{ $Customer->shop_lng ?? 51.391173 }});
        initMap('map_get_store', 'store_lat', 'store_lng',
            {{ $Customer->store_lat ?? 35.700954 }}, {{ $Customer->store_lng ?? 51.391173 }});
    }

    $(function() {
        var locationTab = document.querySelector('[data-bs-target="#tab-location"]');
        if (locationTab) {
            locationTab.addEventListener('shown.bs.tab', initCustomerMaps);
        }
    });
    @endif
</script>
</body>
</html>
