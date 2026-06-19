<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>پورتال مشتری و نماینده - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />`n<script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        .portal-kpis {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }

        .portal-form-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .portal-link {
            direction: ltr;
            unicode-bidi: plaintext;
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 1200px) {
            .portal-kpis {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 768px) {

            .portal-kpis,
            .portal-form-grid {
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
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">CRM /</span> پورتال مشتری و نماینده
                                </h4>
                                <div class="text-muted">دسترسی توکنی، پرداخت قابل پیگیری، درخواست های مشتری و نماینده،
                                    پاسخ دهی و اطلاعیه های هدفمند.</div>
                            </div>
                            <a class="btn btn-outline-primary" href="{{ route('crm.dashboard.index') }}">داشبورد
                                CRM</a>
                        </div>

                        <div class="portal-kpis mb-4">
                            <div class="card">
                                <div class="card-body"><span>دسترسی فعال</span>
                                    <h3 class="mt-2 mb-0 text-primary">{{ number_format($stats['active_accounts']) }}
                                    </h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>نماینده ها</span>
                                    <h3 class="mt-2 mb-0 text-info">{{ number_format($stats['representatives']) }}</h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>درخواست باز</span>
                                    <h3 class="mt-2 mb-0 text-warning">{{ number_format($stats['open_requests']) }}
                                    </h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>پرداخت در انتظار</span>
                                    <h3 class="mt-2 mb-0 text-danger">{{ number_format($stats['pending_payments']) }}
                                    </h3>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body"><span>اطلاعیه فعال</span>
                                    <h3 class="mt-2 mb-0 text-success">{{ number_format($stats['announcements']) }}
                                    </h3>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-xl-7">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">ایجاد دسترسی پورتال</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST"
                                            action="{{ route('crm.customer-portal.accounts.store') }}">
                                            @csrf
                                            <div class="portal-form-grid">
                                                <div><label class="form-label">مشتری</label><select class="form-select"
                                                        name="customer_id" required>
                                                        @foreach ($customers as $customer)
                                                            <option value="{{ $customer->id }}">
                                                                {{ $customer->name ?? 'مشتری #' . $customer->id }}
                                                                {{ $customer->mobile ? '- ' . $customer->mobile : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div><label class="form-label">نقش</label><select class="form-select"
                                                        name="role" required>
                                                        @foreach ($roles as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div><label class="form-label">کاربر نماینده</label><select
                                                        class="form-select" name="user_id">
                                                        <option value="">بدون اتصال</option>
                                                        @foreach ($users as $portalUser)
                                                            <option value="{{ $portalUser->id }}">
                                                                {{ $portalUser->name }}
                                                                {{ $portalUser->mobile ? '- ' . $portalUser->mobile : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div><label class="form-label">عنوان دسترسی</label><input
                                                        class="form-control" name="title"
                                                        placeholder="مثلا نماینده غرب"></div>
                                                <div><label class="form-label">نام مخاطب</label><input
                                                        class="form-control" name="contact_name"></div>
                                                <div><label class="form-label">موبایل</label><input class="form-control"
                                                        name="contact_mobile"></div>
                                                <div><label class="form-label">ایمیل</label><input class="form-control"
                                                        name="contact_email" type="email"></div>
                                                <div><label class="form-label">انقضا</label><input class="form-control"
                                                        type="date" name="expires_at"></div>
                                            </div>
                                            <button class="btn btn-primary mt-3" type="submit">ساخت لینک امن</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-5">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">اطلاعیه پورتال</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST"
                                            action="{{ route('crm.customer-portal.announcements.store') }}">
                                            @csrf
                                            <div class="row g-3">
                                                <div class="col-md-6"><label class="form-label">مخاطب</label><select
                                                        class="form-select" name="audience_type">
                                                        @foreach ($announcementAudiences as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6"><label class="form-label">اولویت</label><select
                                                        class="form-select" name="priority">
                                                        @foreach ($announcementPriorities as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12"><label class="form-label">عنوان</label><input
                                                        class="form-control" name="title" required></div>
                                                <div class="col-12"><label class="form-label">متن</label>
                                                    <textarea class="form-control" name="body" rows="3"></textarea>
                                                </div>
                                                <div class="col-md-6"><label class="form-label">شروع</label><input
                                                        class="form-control" name="starts_at" type="datetime-local">
                                                </div>
                                                <div class="col-md-6"><label class="form-label">پایان</label><input
                                                        class="form-control" name="ends_at" type="datetime-local">
                                                </div>
                                            </div>
                                            <button class="btn btn-outline-primary mt-3" type="submit">انتشار
                                                اطلاعیه</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">پرداخت های پورتال</h5><span
                                    class="badge bg-label-secondary">{{ number_format($payments->count()) }}</span>
                            </div>
                            <div class="table-responsive text-nowrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>مشتری</th>
                                            <th>سفارش</th>
                                            <th>مبلغ</th>
                                            <th>روش</th>
                                            <th>رسید</th>
                                            <th>وضعیت</th>
                                            <th>تسویه حسابداری</th>
                                            <th>اقدام</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($payments as $payment)
                                            <tr>
                                                <td>{{ optional($payment->customer)->name ?? '-' }}<div
                                                        class="text-muted small">
                                                        {{ optional($payment->submitted_at)->format('Y-m-d H:i') }}
                                                    </div>
                                                </td>
                                                <td>{{ $payment->pishfactor_id ? '#' . $payment->pishfactor_id : '-' }}
                                                </td>
                                                <td>{{ number_format((float) $payment->payable_amount) }}</td>
                                                <td>{{ $payment->methodText() }}</td>
                                                <td>{{ $payment->reference_number ?: '-' }}<div
                                                        class="text-muted small">{{ $payment->proof_text }}</div>
                                                    @if ($payment->gateway_provider || $payment->authority)
                                                        <div class="text-muted small">
                                                            {{ $payment->gateway_provider ?: 'gateway' }} /
                                                            {{ $payment->authority ?: '-' }}</div>
                                                    @endif
                                                </td>
                                                <td><span
                                                        class="badge bg-label-{{ $payment->status === 'verified' ? 'success' : ($payment->status === 'rejected' ? 'danger' : 'warning') }}">{{ $payment->statusText() }}</span>
                                                </td>
                                                <td>
                                                    @if ($payment->accountingVoucher)
                                                        <span class="badge bg-label-success">سند
                                                            #{{ $payment->accountingVoucher->voucher_number }}</span>
                                                        <div class="text-muted small">
                                                            {{ optional($payment->gateway_settled_at)->format('Y-m-d H:i') }}
                                                        </div>
                                                    @elseif ($payment->gateway_settlement_status === 'failed')
                                                        <span class="badge bg-label-danger">خطای سند</span>
                                                        <div class="text-muted small">
                                                            {{ data_get($payment->metadata, 'accounting_settlement_error.message') }}
                                                        </div>
                                                    @elseif ($payment->status === 'verified')
                                                        <span class="badge bg-label-warning">در انتظار سند</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td style="min-width: 320px;">
                                                    <form method="POST"
                                                        action="{{ route('crm.customer-portal.payments.update', $payment) }}">
                                                        @csrf @method('PATCH')<div class="d-flex gap-2"><select
                                                                class="form-select form-select-sm" name="status">
                                                                @foreach ($paymentStatuses as $key => $label)
                                                                    <option value="{{ $key }}"
                                                                        @selected($payment->status === $key)>
                                                                        {{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button class="btn btn-sm btn-primary"
                                                                type="submit">ثبت</button>
                                                        </div><input class="form-control form-control-sm mt-2"
                                                            name="response" placeholder="پاسخ قابل مشاهده در پورتال">
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">پرداختی از پورتال
                                                    ثبت نشده است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-xl-5">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">دسترسی ها</h5><span
                                            class="badge bg-label-secondary">{{ number_format($accounts->total()) }}</span>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>مشتری</th>
                                                    <th>نقش</th>
                                                    <th>وضعیت</th>
                                                    <th>لینک</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($accounts as $account)
                                                    <tr>
                                                        <td>{{ optional($account->customer)->name ?? '-' }}<div
                                                                class="text-muted small">{{ $account->contact_name }}
                                                            </div>
                                                        </td>
                                                        <td>{{ $account->roleText() }}</td>
                                                        <td>{{ $account->statusText() }}</td>
                                                        <td><a class="d-inline-block portal-link" target="_blank"
                                                                href="{{ route('customer-portal.show', $account->access_token) }}">{{ route('customer-portal.show', $account->access_token) }}</a>
                                                        </td>
                                                </tr>@empty<tr>
                                                        <td colspan="4" class="text-center text-muted">هنوز دسترسی
                                                            ساخته نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">{{ $accounts->links() }}</div>
                                </div>
                            </div>
                            <div class="col-xl-7">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">درخواست های مشتری/نماینده</h5>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>موضوع</th>
                                                    <th>مشتری</th>
                                                    <th>نوع</th>
                                                    <th>اولویت</th>
                                                    <th>وضعیت و پاسخ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($requests as $portalRequest)
                                                    <tr>
                                                        <td>{{ $portalRequest->subject }}<div
                                                                class="text-muted small">
                                                                {{ $portalRequest->description }}</div>
                                                        </td>
                                                        <td>{{ optional($portalRequest->customer)->name ?? '-' }}</td>
                                                        <td>{{ $portalRequest->typeText() }}</td>
                                                        <td><span
                                                                class="badge bg-label-warning">{{ $portalRequest->priorityText() }}</span>
                                                        </td>
                                                        <td style="min-width: 320px;">
                                                            <form method="POST"
                                                                action="{{ route('crm.customer-portal.requests.update', $portalRequest) }}">
                                                                @csrf @method('PATCH')<div class="d-flex gap-2">
                                                                    <select class="form-select form-select-sm"
                                                                        name="status">
                                                                        @foreach ($requestStatuses as $key => $label)
                                                                            <option value="{{ $key }}"
                                                                                @selected($portalRequest->status === $key)>
                                                                                {{ $label }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <button class="btn btn-sm btn-primary"
                                                                        type="submit">ثبت</button>
                                                                </div>
                                                                <textarea class="form-control form-control-sm mt-2" name="response" rows="2"
                                                                    placeholder="پاسخ قابل مشاهده در پورتال">{{ $portalRequest->response }}</textarea>
                                                            </form>
                                                        </td>
                                                </tr>@empty<tr>
                                                        <td colspan="5" class="text-center text-muted">درخواستی ثبت
                                                            نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
