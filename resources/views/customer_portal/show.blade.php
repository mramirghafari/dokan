<!DOCTYPE html>
<html class="light-style" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>پورتال مشتری - دکان دارمینو</title>
<link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <style>
        body {
            background: #f6f7fb;
        }

        .portal-shell {
            max-width: 1180px;
            margin: 0 auto;
            padding: 28px 16px;
        }

        .portal-hero {
            background: linear-gradient(135deg, #102a43, #1f7a8c);
            color: #fff;
            border-radius: 10px;
            padding: 24px;
        }

        .portal-kpis {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            margin: 18px 0;
        }

        .portal-kpis .card {
            border: 0;
            box-shadow: 0 8px 20px rgba(16, 42, 67, .08);
        }

        @media (max-width: 1000px) {
            .portal-kpis {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .portal-kpis {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <main class="portal-shell">
        <section class="portal-hero d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="mb-1">{{ $account->roleText() }}</div>
                <h2 class="mb-1">{{ $customer->name ?? 'پورتال مشتری' }}</h2>
                <div>مشاهده سفارش ها، ثبت پرداخت، پیگیری درخواست ها و اطلاعیه های اختصاصی</div>
            </div>
            <span
                class="badge bg-label-light text-dark">{{ optional($account->last_login_at)->format('Y-m-d H:i') }}</span>
        </section>

        @if (session('portal_success'))
            <div class="alert alert-success mt-3">{{ session('portal_success') }}</div>
        @endif

        <section class="portal-kpis">
            <div class="card">
                <div class="card-body"><span>سفارش های اخیر</span>
                    <h3 class="mt-2 mb-0">{{ number_format($stats['orders']) }}</h3>
                </div>
            </div>
            <div class="card">
                <div class="card-body"><span>درخواست باز</span>
                    <h3 class="mt-2 mb-0 text-warning">{{ number_format($stats['open_requests']) }}</h3>
                </div>
            </div>
            <div class="card">
                <div class="card-body"><span>پرداخت در انتظار</span>
                    <h3 class="mt-2 mb-0 text-danger">{{ number_format($stats['pending_payments']) }}</h3>
                </div>
            </div>
            <div class="card">
                <div class="card-body"><span>پورسانت قابل مشاهده</span>
                    <h3 class="mt-2 mb-0 text-info">{{ number_format($stats['commission_payable']) }}</h3>
                </div>
            </div>
            <div class="card">
                <div class="card-body"><span>مبلغ سفارش ها</span>
                    <h3 class="mt-2 mb-0 text-success">{{ number_format($stats['total_orders_amount']) }}</h3>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">سفارش ها و پیش فاکتورها</h5>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>شماره</th>
                                    <th>تاریخ</th>
                                    <th>مبلغ</th>
                                    <th>وضعیت فروش</th>
                                    <th>تحویل</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($orders as $order)
                                    <tr>
                                        <td>#{{ $order->id }}</td>
                                        <td>{{ optional($order->created_at)->format('Y-m-d') }}</td>
                                        <td>{{ number_format((float) str_replace(',', '', $order->fullPrice ?: $order->pat_price)) }}
                                        </td>
                                        <td><span
                                                class="badge bg-label-primary">{{ $order->sales_status ?? ($order->status ?? '-') }}</span>
                                        </td>
                                        <td>{{ $order->delivery_status ?? '-' }}</td>
                                </tr>@empty<tr>
                                        <td colspan="5" class="text-center text-muted">سفارشی برای نمایش وجود ندارد.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">پرداخت های ثبت شده</h5>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>تاریخ</th>
                                    <th>سفارش</th>
                                    <th>مبلغ</th>
                                    <th>روش</th>
                                    <th>وضعیت</th>
                                    <th>رسید</th>
                                    <th>پیگیری</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payments as $payment)
                                    <tr>
                                        <td>{{ optional($payment->submitted_at)->format('Y-m-d H:i') }}</td>
                                        <td>{{ $payment->pishfactor_id ? '#' . $payment->pishfactor_id : '-' }}</td>
                                        <td>{{ number_format((float) $payment->payable_amount) }}</td>
                                        <td>{{ $payment->methodText() }}</td>
                                        <td><span
                                                class="badge bg-label-{{ $payment->status === 'verified' ? 'success' : ($payment->status === 'rejected' ? 'danger' : 'warning') }}">{{ $payment->statusText() }}</span>
                                        </td>
                                        <td>{{ $payment->reference_number ?: '-' }}</td>
                                        <td>
                                            @if ($payment->accountingVoucher)
                                                <span class="badge bg-label-success">تسویه شده</span>
                                            @elseif ($payment->gateway_settlement_status === 'failed')
                                                <span class="badge bg-label-warning">در بررسی مالی</span>
                                            @else
                                                <span class="text-muted">{{ $payment->authority ?: '-' }}</span>
                                            @endif
                                        </td>
                                </tr>@empty<tr>
                                        <td colspan="7" class="text-center text-muted">پرداختی ثبت نشده است.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($account->role === 'representative')
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">پورسانت نماینده</h5>
                        </div>
                        <div class="table-responsive text-nowrap">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>دوره</th>
                                        <th>فروش خالص</th>
                                        <th>تحقق</th>
                                        <th>قابل پرداخت</th>
                                        <th>وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($commissions as $settlement)
                                        <tr>
                                            <td>{{ optional($settlement->period_start)->format('Y-m-d') }} تا
                                                {{ optional($settlement->period_end)->format('Y-m-d') }}</td>
                                            <td>{{ number_format((float) $settlement->net_amount) }}</td>
                                            <td>{{ number_format((float) $settlement->achievement_percent, 1) }}%</td>
                                            <td>{{ number_format((float) $settlement->payable_amount) }}</td>
                                            <td>{{ $settlement->status }}</td>
                                    </tr>@empty<tr>
                                            <td colspan="5" class="text-center text-muted">پورسانتی برای نمایش وجود
                                                ندارد.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">درخواست ها و پاسخ ها</h5>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>موضوع</th>
                                    <th>نوع</th>
                                    <th>وضعیت</th>
                                    <th>پاسخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($requests as $portalRequest)
                                    <tr>
                                        <td>{{ $portalRequest->subject }}</td>
                                        <td>{{ $portalRequest->typeText() }}</td>
                                        <td><span
                                                class="badge bg-label-secondary">{{ $portalRequest->statusText() }}</span>
                                        </td>
                                        <td>{{ $portalRequest->response ?: 'در انتظار پاسخ' }}</td>
                                </tr>@empty<tr>
                                        <td colspan="4" class="text-center text-muted">درخواستی ثبت نشده است.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">ثبت پرداخت</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('customer-portal.payments.store', $token) }}">@csrf<div
                                class="mb-3"><label class="form-label">سفارش مرتبط</label><select class="form-select"
                                    name="pishfactor_id">
                                    <option value="">بدون سفارش</option>
                                    @foreach ($orders as $order)
                                        <option value="{{ $order->id }}">#{{ $order->id }} -
                                            {{ number_format((float) str_replace(',', '', $order->fullPrice ?: $order->pat_price)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3"><label class="form-label">مبلغ</label><input class="form-control"
                                    name="amount" type="number" min="1" step="1000" required></div>
                            <div class="mb-3"><label class="form-label">روش پرداخت</label><select class="form-select"
                                    name="payment_method" required>
                                    @foreach ($paymentMethods as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3"><label class="form-label">شماره پیگیری</label><input
                                    class="form-control" name="reference_number"></div>
                            <div class="mb-3"><label class="form-label">شرح رسید</label>
                                <textarea class="form-control" name="proof_text" rows="3"></textarea>
                            </div><button class="btn btn-primary w-100" type="submit">ثبت پرداخت / انتقال به
                                درگاه</button>
                        </form>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">ثبت درخواست جدید</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('customer-portal.requests.store', $token) }}">@csrf<div
                                class="mb-3"><label class="form-label">نوع</label><select class="form-select"
                                    name="type" required>
                                    @foreach ($requestTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3"><label class="form-label">اولویت</label><select class="form-select"
                                    name="priority" required>
                                    @foreach ($requestPriorities as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3"><label class="form-label">موضوع</label><input class="form-control"
                                    name="subject" required></div>
                            <div class="mb-3"><label class="form-label">شرح</label>
                                <textarea class="form-control" name="description" rows="4"></textarea>
                            </div><button class="btn btn-outline-primary w-100" type="submit">ارسال درخواست</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">اطلاعیه ها</h5>
                    </div>
                    <div class="card-body">
                        @forelse ($announcements as $announcement)
                            <div class="border-bottom pb-3 mb-3"><span
                                    class="badge bg-label-{{ $announcement->priority === 'urgent' ? 'danger' : 'info' }}">{{ $announcement->priorityText() }}</span>
                                <h6 class="mt-2 mb-1">{{ $announcement->title }}</h6>
                                <div class="text-muted">{{ $announcement->body }}</div>
                        </div>@empty<div class="text-muted">اطلاعیه فعالی وجود ندارد.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
