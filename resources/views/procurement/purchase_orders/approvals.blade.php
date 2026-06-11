<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>تایید و بودجه خرید - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('assets/') }}/js/config.js"></script>
</head>

<body>
    @include('sweetalert::alert')
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections/sidebar')
            <div class="layout-page">
                @include('sections/header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">انبار و تامین /</span> تایید و بودجه
                                خرید</h4>
                            <div class="d-flex gap-2">
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-orders.index') }}">
                                    <i class="ti ti-shopping-cart me-1"></i> سفارش خرید
                                </a>
                                <a class="btn btn-outline-secondary" href="{{ route('purchase-requisitions.index') }}">
                                    <i class="ti ti-list-check me-1"></i> استعلام بها
                                </a>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <small class="text-muted">در انتظار تایید</small>
                                        <h4 class="mb-0 text-end">{{ number_format($totals['pending']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <small class="text-muted">خارج از بودجه</small>
                                        <h4 class="mb-0 text-end">{{ number_format($totals['over_budget']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <small class="text-muted">مبلغ ردیف های صفحه</small>
                                        <h4 class="mb-0 text-end">{{ number_format($totals['amount']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-5">
                                <form class="card h-100" method="POST"
                                    action="{{ route('purchase-orders.budgets.store') }}">
                                    @csrf
                                    <div class="card-header">
                                        <h5 class="mb-0">تعریف بودجه ماهانه خرید</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-12 col-md-5">
                                            <label class="form-label">انبار</label>
                                            <select name="store_id" class="form-select" required>
                                                <option value="">انتخاب کنید</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}" @selected((string) old('store_id') === (string) $store->id)>
                                                        {{ $store->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">ماه</label>
                                            <input type="month" name="period" class="form-control"
                                                value="{{ old('period', $currentPeriod) }}" required>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">سقف بودجه</label>
                                            <input type="number" min="0" step="0.01" name="budget_amount"
                                                class="form-control text-end" value="{{ old('budget_amount') }}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="card-footer text-end">
                                        <button class="btn btn-primary" type="submit">ثبت بودجه</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-12 col-xl-7">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">بودجه های اخیر</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>ماه</th>
                                                    <th>انبار</th>
                                                    <th class="text-end">بودجه</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($budgets as $budget)
                                                    <tr>
                                                        <td>{{ $budget->period }}</td>
                                                        <td>{{ optional($budget->store)->title }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $budget->budget_amount) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted py-4">بودجه ای
                                                            ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form class="card mb-4" method="GET" action="{{ route('purchase-orders.approvals') }}">
                            <div class="card-body row g-3 align-items-end">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">وضعیت تایید</label>
                                    <select name="approval_status" class="form-select">
                                        <option value="">همه موارد فعال</option>
                                        <option value="pending_approval" @selected(request('approval_status') === 'pending_approval')>در انتظار تایید
                                        </option>
                                        <option value="approved" @selected(request('approval_status') === 'approved')>تایید شده</option>
                                        <option value="rejected" @selected(request('approval_status') === 'rejected')>برگشت خورده</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">وضعیت بودجه</label>
                                    <select name="budget_status" class="form-select">
                                        <option value="">همه</option>
                                        <option value="within_budget" @selected(request('budget_status') === 'within_budget')>داخل بودجه</option>
                                        <option value="over_budget" @selected(request('budget_status') === 'over_budget')>خارج از بودجه</option>
                                        <option value="no_budget" @selected(request('budget_status') === 'no_budget')>بدون بودجه تعریف شده
                                        </option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <button class="btn btn-outline-primary w-100" type="submit">اعمال فیلتر</button>
                                </div>
                            </div>
                        </form>

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>شماره</th>
                                            <th>تامین کننده</th>
                                            <th>انبار</th>
                                            <th>ماه بودجه</th>
                                            <th class="text-end">مبلغ سفارش</th>
                                            <th class="text-end">بودجه</th>
                                            <th class="text-end">مصرف شده</th>
                                            <th class="text-end">مانده پس از سفارش</th>
                                            <th>وضعیت</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($purchaseOrders as $purchaseOrder)
                                            <tr>
                                                <td>{{ $purchaseOrder->order_number }}</td>
                                                <td>{{ optional($purchaseOrder->supplier)->title ?: optional($purchaseOrder->supplier)->name }}
                                                </td>
                                                <td>{{ optional($purchaseOrder->store)->title }}</td>
                                                <td>{{ $purchaseOrder->budget_period ?: '-' }}</td>
                                                <td class="text-end">
                                                    {{ number_format((float) $purchaseOrder->total_amount) }}</td>
                                                <td class="text-end">
                                                    {{ $purchaseOrder->budget_amount !== null ? number_format((float) $purchaseOrder->budget_amount) : '-' }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $purchaseOrder->budget_consumed_amount) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $purchaseOrder->budget_remaining_amount) }}
                                                </td>
                                                <td>
                                                    @if ($purchaseOrder->approval_status === 'pending_approval')
                                                        <span class="badge bg-label-warning">در انتظار تایید</span>
                                                    @elseif ($purchaseOrder->approval_status === 'approved')
                                                        <span class="badge bg-label-success">تایید شده</span>
                                                    @elseif ($purchaseOrder->approval_status === 'rejected')
                                                        <span class="badge bg-label-danger">برگشت خورده</span>
                                                    @endif
                                                    @if ($purchaseOrder->budget_status === 'over_budget')
                                                        <span class="badge bg-label-danger mt-1">خارج از بودجه</span>
                                                    @elseif ($purchaseOrder->budget_status === 'within_budget')
                                                        <span class="badge bg-label-success mt-1">داخل بودجه</span>
                                                    @elseif ($purchaseOrder->budget_status === 'no_budget')
                                                        <span class="badge bg-label-secondary mt-1">بدون بودجه</span>
                                                    @endif
                                                </td>
                                                <td style="min-width: 280px">
                                                    @if ($purchaseOrder->approval_status === 'pending_approval')
                                                        <form method="POST"
                                                            action="{{ route('purchase-orders.approval.approve', $purchaseOrder) }}"
                                                            class="row g-2 mb-2">
                                                            @csrf
                                                            <div class="col-12">
                                                                <input type="text" name="approval_note"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="یادداشت تایید">
                                                            </div>
                                                            <div class="col-12">
                                                                <button class="btn btn-sm btn-success w-100"
                                                                    type="submit">تایید خرید</button>
                                                            </div>
                                                        </form>
                                                        <form method="POST"
                                                            action="{{ route('purchase-orders.approval.reject', $purchaseOrder) }}"
                                                            class="row g-2">
                                                            @csrf
                                                            <div class="col-12">
                                                                <input type="text" name="approval_note"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="دلیل برگشت">
                                                            </div>
                                                            <div class="col-12">
                                                                <button class="btn btn-sm btn-outline-danger w-100"
                                                                    type="submit">برگشت برای اصلاح</button>
                                                            </div>
                                                        </form>
                                                    @else
                                                        <span class="text-muted">تصمیم ثبت شده است.</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if ($purchaseOrder->approvalEvents->isNotEmpty())
                                                <tr>
                                                    <td></td>
                                                    <td colspan="9">
                                                        @foreach ($purchaseOrder->approvalEvents->sortByDesc('id')->take(3) as $event)
                                                            <div class="small text-muted">
                                                                {{ $event->created_at?->format('Y-m-d H:i') }} -
                                                                {{ ['requested' => 'ارسال برای تایید', 'approved' => 'تایید', 'rejected' => 'برگشت'][$event->event_type] ?? $event->event_type }}
                                                                @if ($event->description)
                                                                    - {{ $event->description }}
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            @endif
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4">سفارشی برای
                                                    تایید پیدا نشد.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $purchaseOrders->links() }}</div>
                        </div>
                    </div>
                    @include('sections/footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>

    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
</body>

</html>
