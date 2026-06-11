<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>پخش مویرگی و سفارش گیری - دکان دارمینو</title>
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
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <h4 class="mb-4"><span class="text-muted fw-light">پخش و فروش /</span> پخش مویرگی و سفارش گیری
                        </h4>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="card p-3"><small class="text-muted">برنامه های
                                        ویزیت</small><strong>{{ number_format($totals['plans']) }}</strong></div>
                            </div>
                            <div class="col-md-3">
                                <div class="card p-3"><small class="text-muted">ویزیت انجام
                                        شده</small><strong>{{ number_format($totals['visited']) }}</strong></div>
                            </div>
                            <div class="col-md-3">
                                <div class="card p-3"><small class="text-muted">سفارش
                                        ویزیتی</small><strong>{{ number_format($totals['orders']) }}</strong></div>
                            </div>
                            <div class="col-md-3">
                                <div class="card p-3"><small class="text-muted">پروموشن
                                        فعال</small><strong>{{ number_format($totals['promotions']) }}</strong></div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-lg-7">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">ساخت برنامه ویزیت</h5>
                                    </div>
                                    <form method="POST" action="{{ route('distribution.visitPlans.store') }}">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">ویزیتور</label>
                                                    <select class="form-select" name="visitor_id" required>
                                                        @foreach ($visitors as $visitor)
                                                            <option value="{{ $visitor->id }}">
                                                                {{ $visitor->name ?: $visitor->username }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">تاریخ برنامه</label>
                                                    <input class="form-control" name="planned_date_en" type="date"
                                                        value="{{ $today }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">نوع فروش</label>
                                                    <select class="form-select" name="sales_mode" required>
                                                        <option value="hot_sale">فروش گرم</option>
                                                        <option value="cold_sale">فروش سرد</option>
                                                        <option value="preorder">پیش سفارش</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">عنوان</label>
                                                    <input class="form-control" name="title"
                                                        value="برنامه ویزیت {{ verta($today)->format('Y/m/d') }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">کد مسیر</label>
                                                    <input class="form-control" name="route_code">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">شرح</label>
                                                    <input class="form-control" name="description">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">مشتری ها</label>
                                                    <x-erp-remote-select
                                                        entity="customers"
                                                        name="customer_ids[]"
                                                        :value="old('customer_ids', [])"
                                                        placeholder="جستجو و انتخاب مشتری"
                                                        class="form-select erp-remote-select"
                                                        :multiple="true"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer text-end">
                                            <button class="btn btn-primary" type="submit"
                                                @disabled($customers->isEmpty() || $visitors->isEmpty())>ثبت برنامه ویزیت</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">تعریف پروموشن</h5>
                                    </div>
                                    <form method="POST" action="{{ route('distribution.promotions.store') }}">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-7">
                                                    <label class="form-label">عنوان</label>
                                                    <input class="form-control" name="title" required>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label">نوع</label>
                                                    <select class="form-select" name="promotion_type" required>
                                                        <option value="discount_percent">درصد تخفیف</option>
                                                        <option value="discount_amount">مبلغ تخفیف</option>
                                                        <option value="gift">اشانتیون</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">شروع</label>
                                                    <input class="form-control" name="starts_at" type="date"
                                                        value="{{ $today }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">پایان</label>
                                                    <input class="form-control" name="ends_at" type="date">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">کالای هدف</label>
                                                    <x-erp-remote-select
                                                        entity="products"
                                                        name="product_id"
                                                        :value="old('product_id')"
                                                        placeholder="همه کالاها"
                                                        class="form-select erp-remote-select"
                                                        :filters="config('erp_scale.remote_lookup.product_filters')"
                                                    />
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">حداقل سفارش</label>
                                                    <input class="form-control" name="min_order_amount"
                                                        type="number" min="0" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">درصد</label>
                                                    <input class="form-control" name="discount_percent"
                                                        type="number" min="0" max="100" step="0.01"
                                                        value="0">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">مبلغ</label>
                                                    <input class="form-control" name="discount_amount" type="number"
                                                        min="0" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">سقف استفاده</label>
                                                    <input class="form-control" name="max_uses" type="number"
                                                        min="0" value="0">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">وضعیت</label>
                                                    <select class="form-select" name="status" required>
                                                        <option value="active">فعال</option>
                                                        <option value="inactive">غیرفعال</option>
                                                        <option value="expired">پایان یافته</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">شرح</label>
                                                    <input class="form-control" name="description">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer text-end">
                                            <button class="btn btn-outline-primary" type="submit">ثبت
                                                پروموشن</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">برنامه های ویزیت و سفارش گیری</h5>
                                <small class="text-muted">API موبایل: visit-plans، orders، sync/push</small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>برنامه</th>
                                            <th>ویزیتور</th>
                                            <th>تاریخ</th>
                                            <th>وضعیت</th>
                                            <th>شاخص ها</th>
                                            <th>توقف ها</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($plans as $plan)
                                            <tr>
                                                <td>{{ $plan->plan_number }}<br><small
                                                        class="text-muted">{{ $plan->title }}</small></td>
                                                <td>{{ optional($plan->visitor)->name ?: optional($plan->visitor)->username }}
                                                </td>
                                                <td>{{ $plan->planned_date_fa ?: optional($plan->planned_date_en)->format('Y-m-d') }}
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-label-{{ $plan->status === 'completed' ? 'success' : ($plan->status === 'planned' ? 'info' : 'secondary') }}">{{ $plan->status }}</span>
                                                </td>
                                                <td>
                                                    <div>برنامه: {{ number_format($plan->planned_customers_count) }}
                                                    </div>
                                                    <small class="text-muted">ویزیت
                                                        {{ number_format($plan->visited_count) }} | سفارش
                                                        {{ number_format($plan->ordered_count) }} | عدم سفارش
                                                        {{ number_format($plan->no_order_count) }}</small>
                                                </td>
                                                <td>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm mb-0">
                                                            <tbody>
                                                                @foreach ($plan->stops as $stop)
                                                                    <tr>
                                                                        <td>{{ $stop->sequence }}</td>
                                                                        <td>{{ optional($stop->customer)->name }}</td>
                                                                        <td><span
                                                                                class="badge bg-label-{{ $stop->visit_status === 'order_created' ? 'success' : ($stop->visit_status === 'no_order' ? 'warning' : 'secondary') }}">{{ $stop->visit_status }}</span>
                                                                        </td>
                                                                        <td>
                                                                            @if ($stop->pishfactor)
                                                                                سفارش
                                                                                {{ $stop->pishfactor->invoiceID }} -
                                                                                {{ number_format((float) $stop->pishfactor->fullPrice) }}
                                                                            @elseif ($stop->no_order_reason)
                                                                                {{ $stop->no_order_reason }}
                                                                            @else
                                                                                <span class="text-muted">در انتظار
                                                                                    ویزیت</span>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">هنوز برنامه
                                                    ویزیت ثبت نشده است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $plans->links() }}</div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">پروموشن های پخش</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead>
                                        <tr>
                                            <th>کد</th>
                                            <th>عنوان</th>
                                            <th>نوع</th>
                                            <th>کالا</th>
                                            <th>شرط/تخفیف</th>
                                            <th>وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($promotions as $promotion)
                                            <tr>
                                                <td>{{ $promotion->code }}</td>
                                                <td>{{ $promotion->title }}</td>
                                                <td>{{ $promotion->promotion_type }}</td>
                                                <td>{{ optional($promotion->product)->display_name ?: optional($promotion->product)->title ?: 'همه کالاها' }}
                                                </td>
                                                <td>حداقل {{ number_format((float) $promotion->min_order_amount) }} |
                                                    {{ number_format((float) $promotion->discount_percent, 2) }}٪ |
                                                    {{ number_format((float) $promotion->discount_amount) }}</td>
                                                <td><span
                                                        class="badge bg-label-{{ $promotion->status === 'active' ? 'success' : 'secondary' }}">{{ $promotion->status }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-3">پروموشنی ثبت
                                                    نشده است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    @include('partials.erp-remote-select-assets')
</body>

</html>
