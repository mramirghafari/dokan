<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>کمپین و وفاداری CRM - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        .campaign-form-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .campaign-result-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .campaign-kpis {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(6, minmax(0, 1fr));
        }

        @media (max-width: 1200px) {

            .campaign-kpis,
            .campaign-result-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .campaign-form-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {

            .campaign-kpis,
            .campaign-form-grid,
            .campaign-result-grid {
                grid-template-columns: 1fr;
            }
        }
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
                        @include('crm.partials.hub_bar', ['hubActive' => 'campaigns'])
                        @include('partials.erp-remote-select-assets')
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">CRM /</span> کمپین و وفاداری</h4>
                                <div class="text-muted">ارسال live پیامک (کاوه‌نگار + queue)، مخاطب segment، ROI و باشگاه مشتریان.</div>
                            </div>
                            <span class="badge bg-label-secondary">پیام ثابت — بدون ارسال SMS</span>
                        </div>

                        <div class="campaign-kpis mb-4">
                            <div class="card">
                                <div class="card-body"><span>کمپین فعال</span>
                                    <h3 class="mt-2 mb-0 text-primary">{{ number_format($stats['active']) }}</h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>مخاطبان</span>
                                    <h3 class="mt-2 mb-0">{{ number_format($stats['audience']) }}</h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>تبدیل</span>
                                    <h3 class="mt-2 mb-0 text-success">{{ number_format($stats['conversions']) }}</h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>درآمد منتسب</span>
                                    <h3 class="mt-2 mb-0 text-success">{{ number_format($stats['revenue']) }}</h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>اعضای باشگاه</span>
                                    <h3 class="mt-2 mb-0 text-info">{{ number_format($stats['loyalty_accounts']) }}
                                    </h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>مانده امتیاز</span>
                                    <h3 class="mt-2 mb-0 text-warning">{{ number_format($stats['points_balance']) }}
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-xl-8">
                                <div class="card h-100">
                                    <div
                                        class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                        <h5 class="mb-0">تعریف کمپین</h5>
                                        <span class="badge bg-label-primary">SMS / Email / Phone / Mixed</span>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('crm.campaigns.store') }}" method="POST">
                                            @csrf
                                            <div class="campaign-form-grid">
                                                <div>
                                                    <label class="form-label">عنوان</label>
                                                    <input class="form-control" name="title" required
                                                        value="{{ old('title') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">کانال جذب</label>
                                                    <select class="form-select select2" name="channel" required>
                                                        @foreach ($channels as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label">هدف</label>
                                                    <select class="form-select select2" name="goal" required>
                                                        @foreach ($goals as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label">وضعیت</label>
                                                    <select class="form-select" name="status" required>
                                                        @foreach ($statuses as $key => $label)
                                                            <option value="{{ $key }}"
                                                                @selected($key === 'draft')>{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label">مخاطب هدف</label>
                                                    <select class="form-select select2" name="target_segment_id">
                                                        <option value="">همه مشتریان مجاز</option>
                                                        @foreach ($segments as $segment)
                                                            <option value="{{ $segment->id }}">{{ $segment->title }}
                                                                - {{ $segment->type }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label">مالک</label>
                                                    <select class="form-select select2" name="owner_user_id">
                                                        <option value="">کاربر جاری</option>
                                                        @foreach ($users as $owner)
                                                            <option value="{{ $owner->id }}">{{ $owner->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label">شروع</label>
                                                    <input class="form-control" name="starts_at" type="date"
                                                        value="{{ old('starts_at') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">پایان</label>
                                                    <input class="form-control" name="ends_at" type="date"
                                                        value="{{ old('ends_at') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">کد تخفیف</label>
                                                    <input class="form-control" name="discount_code"
                                                        value="{{ old('discount_code') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">هزینه</label>
                                                    <input class="form-control" min="0" name="budget_amount"
                                                        step="1000" type="number"
                                                        value="{{ old('budget_amount') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">درآمد هدف</label>
                                                    <input class="form-control" min="0"
                                                        name="expected_revenue" step="1000" type="number"
                                                        value="{{ old('expected_revenue') }}">
                                                </div>
                                                <div>
                                                    <label class="form-label">پیام (ثابت سیستمی)</label>
                                                    <input class="form-control" value="بر اساس هدف کمپین خودکار تنظیم می‌شود" disabled>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label">توضیحات و مزایا</label>
                                                <textarea class="form-control" name="description" rows="2">{{ old('description') }}</textarea>
                                            </div>
                                            <div class="text-end mt-3">
                                                <button class="btn btn-primary" type="submit">ثبت کمپین</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">تراکنش دستی وفاداری</h5>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('crm.loyalty.transactions.store') }}" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">مشتری</label>
                                                @include('partials.forms.erp-customer-select', [
                                                    'class' => 'form-select select2 erp-remote-select',
                                                ])
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label">نوع</label>
                                                    <select class="form-select" name="type" required>
                                                        @foreach ($transactionTypes as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">امتیاز</label>
                                                    <input class="form-control" name="points" required
                                                        type="number" value="10">
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label">مبلغ/ارزش</label>
                                                <input class="form-control" min="0" name="amount"
                                                    step="1000" type="number">
                                            </div>
                                            <div class="mt-3">
                                                <label class="form-label">دلیل</label>
                                                <input class="form-control" name="reason">
                                            </div>
                                            <button class="btn btn-outline-primary w-100 mt-3" type="submit">ثبت
                                                امتیاز</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                <h5 class="mb-0">کمپین‌ها</h5>
                                <form class="d-flex gap-2 flex-wrap" method="GET">
                                    <input class="form-control" name="search" placeholder="جستجو"
                                        style="width: 170px" value="{{ $filters['search'] ?? '' }}">
                                    <select class="form-select" name="status" style="width: 150px">
                                        <option value="">همه وضعیت‌ها</option>
                                        @foreach ($statuses as $key => $label)
                                            <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <select class="form-select" name="channel" style="width: 150px">
                                        <option value="">همه کانال‌ها</option>
                                        @foreach ($channels as $key => $label)
                                            <option value="{{ $key }}" @selected(($filters['channel'] ?? '') === $key)>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-outline-primary" type="submit">فیلتر</button>
                                </form>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>کمپین</th>
                                            <th>کانال/هدف</th>
                                            <th>مخاطب/تبدیل</th>
                                            <th>هزینه و درآمد</th>
                                            <th>ROI</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($campaigns as $campaign)
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $campaign->title }}</div>
                                                    <small class="text-muted">{{ $campaign->code }}
                                                        {{ optional($campaign->targetSegment)->title ? ' - ' . optional($campaign->targetSegment)->title : '' }}</small>
                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                        <span class="badge bg-label-secondary">{{ $campaign->statusText() }}</span>
                                                        <span class="badge bg-label-{{ in_array($campaign->dispatch_status, ['completed'], true) ? 'success' : (in_array($campaign->dispatch_status, ['queued','sending'], true) ? 'info' : 'secondary') }}">{{ $campaign->dispatchStatusText() }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ $campaign->channelText() }}<br><small
                                                        class="text-muted">{{ $campaign->goalText() }}</small></td>
                                                <td>{{ number_format($campaign->audience_count) }} مخاطب<br><small
                                                        class="text-success">{{ number_format($campaign->conversion_count) }}
                                                        تبدیل - {{ $campaign->conversionRate() }}%</small></td>
                                                <td>{{ number_format($campaign->budget_amount) }} هزینه<br><small
                                                        class="text-success">{{ number_format($campaign->actual_revenue) }}
                                                        درآمد</small></td>
                                                <td><span
                                                        class="badge bg-label-{{ $campaign->roiPercent() >= 0 ? 'success' : 'danger' }}">{{ $campaign->roiPercent() }}%</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-1">
                                                        <form class="d-inline" action="{{ route('crm.campaigns.audience.sync', $campaign) }}" method="POST">
                                                            @csrf
                                                            <input name="limit" type="hidden" value="100">
                                                            <button class="btn btn-sm btn-outline-info" type="submit">مخاطب</button>
                                                        </form>
                                                        @if(in_array($campaign->channel, ['sms', 'mixed'], true))
                                                            <form class="d-inline" action="{{ route('crm.campaigns.dispatch', $campaign) }}" method="POST" onsubmit="return confirm('ثبت برنامه برای حداکثر ۱۰۰ مخاطب؟');">
                                                                @csrf
                                                                <input name="limit" type="hidden" value="100">
                                                                <button class="btn btn-sm btn-primary" type="submit" @disabled(in_array($campaign->dispatch_status, ['queued','sending','completed'], true))>
                                                                    <x-ui.icon name="check" class="me-1" />فعال‌سازی
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-center text-muted py-4" colspan="6">کمپینی ثبت نشده
                                                    است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $campaigns->links() }}</div>
                        </div>

                        <div class="row g-4">
                            <div class="col-xl-7">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">ثبت نتیجه مخاطب و فروش منتسب</h5>
                                    </div>
                                    <div class="card-body">
                                        <form
                                            action="{{ $campaigns->first() ? route('crm.campaigns.result', $campaigns->first()) : '#' }}"
                                            id="campaign-result-form" method="POST">
                                            @csrf
                                            <div class="campaign-result-grid">
                                                <div>
                                                    <label class="form-label">کمپین</label>
                                                    <select class="form-select select2" id="campaign-route-selector"
                                                        required>
                                                        @foreach ($campaigns as $campaign)
                                                            <option
                                                                data-action="{{ route('crm.campaigns.result', $campaign) }}"
                                                                value="{{ $campaign->id }}">{{ $campaign->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label">مشتری</label>
                                                    @include('partials.forms.erp-customer-select', [
                                                        'class' => 'form-select select2 erp-remote-select',
                                                    ])
                                                </div>
                                                <div>
                                                    <label class="form-label">وضعیت</label>
                                                    <select class="form-select" name="status" required>
                                                        @foreach ($audienceStatuses as $key => $label)
                                                            <option value="{{ $key }}"
                                                                @selected($key === 'converted')>{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label">امتیاز</label>
                                                    <input class="form-control" min="0"
                                                        name="loyalty_points_awarded" type="number" value="10">
                                                </div>
                                                <div>
                                                    <label class="form-label">سفارش</label>
                                                    <select class="form-select select2" name="pishfactor_id">
                                                        <option value="">بدون سفارش</option>
                                                        @foreach ($orders as $order)
                                                            <option value="{{ $order->id }}">
                                                                #{{ $order->invoiceID ?: $order->id }} -
                                                                {{ number_format((float) ($order->fullPrice ?: $order->pat_price)) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label">درآمد</label>
                                                    <input class="form-control" min="0" name="revenue_amount"
                                                        step="1000" type="number">
                                                </div>
                                                <div class="grid-column-full">
                                                    <label class="form-label">یادداشت</label>
                                                    <input class="form-control" name="notes">
                                                </div>
                                            </div>
                                            <div class="text-end mt-3">
                                                <button class="btn btn-success" type="submit"
                                                    @disabled(!$campaigns->first())>ثبت نتیجه</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-5">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">باشگاه مشتریان</h5>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>مشتری</th>
                                                    <th>سطح</th>
                                                    <th>مانده</th>
                                                    <th>Retention</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($loyaltyAccounts as $account)
                                                    <tr>
                                                        <td>{{ optional($account->customer)->name ?: 'مشتری #' . $account->customer_id }}
                                                        </td>
                                                        <td><span
                                                                class="badge bg-label-primary">{{ $tiers[$account->tier] ?? $account->tier }}</span>
                                                        </td>
                                                        <td>{{ number_format($account->points_balance) }}</td>
                                                        <td>{{ $retentionStatuses[$account->retention_status] ?? $account->retention_status }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-center text-muted py-4" colspan="4">عضوی
                                                            ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
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

    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    @include('partials.erp-remote-select-assets')
    <script>
        $(function() {
            $('.select2:not(.erp-remote-select)').select2({
                width: '100%'
            });

            $('#campaign-route-selector').on('change', function() {
                var action = $(this).find(':selected').data('action');
                if (action) {
                    $('#campaign-result-form').attr('action', action);
                }
            }).trigger('change');
        });
    </script>
</body>

</html>
