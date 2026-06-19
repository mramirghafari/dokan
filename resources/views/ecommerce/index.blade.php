<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>فروشگاه اینترنتی - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
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
                        @php
                            $platformLabels = [
                                'custom' => 'اختصاصی',
                                'woocommerce' => 'WooCommerce',
                                'shopify' => 'Shopify',
                                'prestashop' => 'PrestaShop',
                                'magento' => 'Magento',
                            ];
                            $statusColors = [
                                'processed' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                'conflict' => 'danger',
                            ];
                        @endphp

                        <h4 class="mb-4"><span class="text-muted fw-light">فروش /</span> فروشگاه اینترنتی و وب سرویس
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
                                <div class="card p-3"><small class="text-muted">کانال
                                        ها</small><strong>{{ number_format($totals['channels']) }}</strong></div>
                            </div>
                            <div class="col-md-3">
                                <div class="card p-3"><small class="text-muted">نگاشت
                                        کالا</small><strong>{{ number_format($totals['mappings']) }}</strong></div>
                            </div>
                            <div class="col-md-3">
                                <div class="card p-3"><small class="text-muted">سفارش sync
                                        شده</small><strong>{{ number_format($totals['orders']) }}</strong></div>
                            </div>
                            <div class="col-md-3">
                                <div class="card p-3"><small class="text-muted">خطا /
                                        تعارض</small><strong>{{ number_format($totals['errors']) }}</strong></div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-lg-7">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">کانال فروش آنلاین</h5>
                                        <form method="GET">
                                            <select class="form-select form-select-sm" name="channel_id"
                                                onchange="this.form.submit()">
                                                @foreach ($channels as $channel)
                                                    <option value="{{ $channel->id }}" @selected(optional($activeChannel)->id === $channel->id)>
                                                        {{ $channel->title }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </div>
                                    <form method="POST" action="{{ route('ecommerce.channels.store') }}">
                                        @csrf
                                        <input name="id" type="hidden"
                                            value="{{ optional($activeChannel)->id }}">
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-4"><label class="form-label">عنوان
                                                        کانال</label><input class="form-control" name="title" required
                                                        value="{{ optional($activeChannel)->title ?: 'فروشگاه اینترنتی' }}">
                                                </div>
                                                <div class="col-md-4"><label class="form-label">کد کانال
                                                        API</label><input class="form-control" name="code"
                                                        value="{{ optional($activeChannel)->code ?: 'online-shop' }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">پلتفرم</label>
                                                    <select class="form-select" name="platform" required>
                                                        @foreach ($platformLabels as $key => $label)
                                                            <option value="{{ $key }}"
                                                                @selected(optional($activeChannel)->platform === $key)>{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6"><label class="form-label">آدرس
                                                        فروشگاه</label><input class="form-control" name="base_url"
                                                        value="{{ optional($activeChannel)->base_url }}"></div>
                                                <div class="col-md-6"><label class="form-label">توکن API
                                                        جدید</label><input class="form-control" name="api_token"
                                                        placeholder="فقط هنگام تغییر توکن پر شود"></div>
                                                <div class="col-md-3">
                                                    <label class="form-label">قیمت ارسالی</label>
                                                    <select class="form-select" name="price_policy">
                                                        <option value="consumer" @selected(optional($activeChannel)->price_policy === 'consumer')>مصرف کننده
                                                        </option>
                                                        <option value="wholesale" @selected(optional($activeChannel)->price_policy === 'wholesale')>عمده
                                                        </option>
                                                        <option value="representative" @selected(optional($activeChannel)->price_policy === 'representative')>
                                                            نماینده</option>
                                                        <option value="purchase" @selected(optional($activeChannel)->price_policy === 'purchase')>خرید
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3"><label class="form-label">انبار پیش
                                                        فرض</label><input class="form-control" name="default_store_id"
                                                        type="number"
                                                        value="{{ optional($activeChannel)->default_store_id }}">
                                                </div>
                                                <div class="col-md-3"><label class="form-label">ویزیتور پیش
                                                        فرض</label><input class="form-control"
                                                        name="default_visitor_id" type="number"
                                                        value="{{ optional($activeChannel)->default_visitor_id ?: auth()->id() }}">
                                                </div>
                                                <div class="col-md-3"><label class="form-label">سرپرست پیش
                                                        فرض</label><input class="form-control"
                                                        name="default_leader_id" type="number"
                                                        value="{{ optional($activeChannel)->default_leader_id ?: auth()->id() }}">
                                                </div>
                                                <div class="col-md-3"><label class="form-label">روش پرداخت پیش
                                                        فرض</label><input class="form-control"
                                                        name="default_payment_method" type="number" min="1"
                                                        max="4"
                                                        value="{{ optional($activeChannel)->default_payment_method ?: 3 }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">وضعیت سفارش داخلی</label>
                                                    <select class="form-select" name="order_status_policy">
                                                        <option value="draft" @selected(optional($activeChannel)->order_status_policy !== 'approved')>پیش نویس/در
                                                            انتظار</option>
                                                        <option value="approved" @selected(optional($activeChannel)->order_status_policy === 'approved')>تایید
                                                            شده</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 d-flex align-items-end gap-3">
                                                    <label class="form-check"><input class="form-check-input"
                                                            name="auto_create_customer" type="checkbox"
                                                            value="1" @checked(optional($activeChannel)->auto_create_customer ?? true)> ساخت
                                                        مشتری</label>
                                                    <label class="form-check"><input class="form-check-input"
                                                            name="auto_reserve_inventory" type="checkbox"
                                                            value="1" @checked(optional($activeChannel)->auto_reserve_inventory)> رزرو
                                                        موجودی</label>
                                                    <label class="form-check"><input class="form-check-input"
                                                            name="is_active" type="checkbox" value="1"
                                                            @checked(optional($activeChannel)->is_active ?? true)> فعال</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer text-end"><button class="btn btn-primary"
                                                type="submit">ذخیره کانال</button></div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">Endpointهای فروشگاه</h5>
                                    </div>
                                    <div class="card-body">
                                        @if ($activeChannel)
                                            <div class="mb-3"><small class="text-muted">کاتالوگ کالا و
                                                    موجودی</small><code class="d-block text-wrap">GET
                                                    /api/ecommerce/{{ $activeChannel->code }}/products</code></div>
                                            <div class="mb-3"><small class="text-muted">ثبت سفارش جدید</small><code
                                                    class="d-block text-wrap">POST
                                                    /api/ecommerce/{{ $activeChannel->code }}/orders</code></div>
                                            <div class="mb-3"><small class="text-muted">بروزرسانی وضعیت
                                                    خارجی</small><code class="d-block text-wrap">POST
                                                    /api/ecommerce/{{ $activeChannel->code }}/orders/{external_order_id}/status</code>
                                            </div>
                                            <p class="text-muted mb-0">توکن را با headerهای <code>Authorization: Bearer
                                                    TOKEN</code> یا <code>X-Ecommerce-Token</code> بفرستید.</p>
                                        @else
                                            <p class="text-muted mb-0">ابتدا یک کانال فروشگاه بسازید.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-lg-5">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">نگاشت کالا</h5>
                                    </div>
                                    <form method="POST" action="{{ route('ecommerce.mappings.store') }}">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">کانال</label>
                                                    <select class="form-select" name="ecommerce_channel_id" required>
                                                        @foreach ($channels as $channel)
                                                            <option value="{{ $channel->id }}"
                                                                @selected(optional($activeChannel)->id === $channel->id)>{{ $channel->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">کالای داخلی</label>
                                                    <select class="form-select" name="product_id" required>
                                                        @foreach ($products as $product)
                                                            <option value="{{ $product->id }}">
                                                                {{ $product->sku ?: $product->id }} -
                                                                {{ $product->display_name ?: $product->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6"><label class="form-label">شناسه کالا در
                                                        فروشگاه</label><input class="form-control"
                                                        name="external_product_id" required></div>
                                                <div class="col-md-6"><label class="form-label">شناسه
                                                        variant</label><input class="form-control"
                                                        name="external_variant_id"></div>
                                                <div class="col-md-6"><label class="form-label">SKU
                                                        خارجی</label><input class="form-control" name="external_sku">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">جهت sync</label>
                                                    <select class="form-select" name="sync_direction">
                                                        <option value="both">دوطرفه</option>
                                                        <option value="export_only">فقط ارسال کالا</option>
                                                        <option value="import_only">فقط دریافت سفارش</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4"><label class="form-label">قیمت
                                                        override</label><input class="form-control"
                                                        name="price_override" type="number" min="0"
                                                        step="0.01"></div>
                                                <div class="col-md-4"><label class="form-label">بافر
                                                        موجودی</label><input class="form-control" name="stock_buffer"
                                                        type="number" min="0" step="0.01" value="0">
                                                </div>
                                                <div class="col-md-4 d-flex align-items-end gap-2">
                                                    <label class="form-check"><input class="form-check-input"
                                                            name="sync_price" type="checkbox" value="1" checked>
                                                        قیمت</label>
                                                    <label class="form-check"><input class="form-check-input"
                                                            name="sync_stock" type="checkbox" value="1" checked>
                                                        موجودی</label>
                                                    <label class="form-check"><input class="form-check-input"
                                                            name="is_active" type="checkbox" value="1" checked>
                                                        فعال</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer text-end"><button class="btn btn-outline-primary"
                                                type="submit">ثبت نگاشت</button></div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">ورود تستی سفارش</h5>
                                    </div>
                                    @if ($activeChannel)
                                        <form method="POST"
                                            action="{{ route('ecommerce.orders.sample', $activeChannel) }}">
                                            @csrf
                                            <div class="card-body">
                                                <div class="row g-3">
                                                    <div class="col-md-4"><label class="form-label">شناسه سفارش
                                                            خارجی</label><input class="form-control"
                                                            name="external_order_id" required
                                                            value="ORD-{{ now()->format('His') }}"></div>
                                                    <div class="col-md-4"><label class="form-label">شماره
                                                            سفارش</label><input class="form-control"
                                                            name="external_order_number"></div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">پرداخت</label>
                                                        <select class="form-select" name="payment_status">
                                                            <option value="unknown">نامشخص</option>
                                                            <option value="pending">در انتظار</option>
                                                            <option value="paid">پرداخت شده</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4"><label class="form-label">نام
                                                            مشتری</label><input class="form-control"
                                                            name="customer_name" required value="مشتری فروشگاه"></div>
                                                    <div class="col-md-4"><label
                                                            class="form-label">موبایل</label><input
                                                            class="form-control" name="customer_mobile"></div>
                                                    <div class="col-md-4"><label class="form-label">آدرس</label><input
                                                            class="form-control" name="customer_address"></div>
                                                    <div class="col-md-4"><label class="form-label">شناسه کالای
                                                            فروشگاه</label><input class="form-control"
                                                            name="external_product_id" required></div>
                                                    <div class="col-md-2"><label
                                                            class="form-label">تعداد</label><input
                                                            class="form-control" name="quantity" type="number"
                                                            min="0.0001" step="0.01" value="1" required>
                                                    </div>
                                                    <div class="col-md-2"><label class="form-label">قیمت</label><input
                                                            class="form-control" name="price" type="number"
                                                            min="0" step="0.01" required></div>
                                                    <div class="col-md-2"><label
                                                            class="form-label">تخفیف</label><input
                                                            class="form-control" name="discount_amount"
                                                            type="number" min="0" step="0.01"
                                                            value="0"></div>
                                                    <div class="col-md-2"><label
                                                            class="form-label">مالیات</label><input
                                                            class="form-control" name="tax_amount" type="number"
                                                            min="0" step="0.01" value="0"></div>
                                                    <div class="col-md-2"><label
                                                            class="form-label">ارسال</label><input
                                                            class="form-control" name="shipping_amount"
                                                            type="number" min="0" step="0.01"
                                                            value="0"></div>
                                                </div>
                                            </div>
                                            <div class="card-footer text-end"><button class="btn btn-primary"
                                                    type="submit">تبدیل به سفارش داخلی</button></div>
                                        </form>
                                    @else
                                        <div class="card-body text-muted">برای تست سفارش ابتدا یک کانال بسازید.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">سفارش های اینترنتی sync شده</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>کانال</th>
                                            <th>سفارش خارجی</th>
                                            <th>فاکتور داخلی</th>
                                            <th>مشتری</th>
                                            <th>مبلغ</th>
                                            <th>وضعیت</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($orders as $order)
                                            <tr>
                                                <td>{{ optional($order->channel)->title }}</td>
                                                <td><strong>{{ $order->external_order_id }}</strong><br><small
                                                        class="text-muted">{{ $order->external_order_number }}</small>
                                                </td>
                                                <td>{{ optional($order->pishfactor)->invoiceID ?: '-' }}</td>
                                                <td>{{ optional(optional($order->pishfactor)->customer)->name ?: optional($order->customer)->name }}
                                                </td>
                                                <td>{{ number_format($order->net_amount) }}</td>
                                                <td><span
                                                        class="badge bg-label-{{ $statusColors[$order->sync_status] ?? 'secondary' }}">{{ $order->sync_status }}</span><br><small>{{ $order->payment_status }}
                                                        / {{ $order->delivery_status }}</small></td>
                                                <td style="min-width:260px">
                                                    <form class="row g-1" method="POST"
                                                        action="{{ route('ecommerce.orders.status', $order) }}">
                                                        @csrf
                                                        <div class="col-4"><input
                                                                class="form-control form-control-sm"
                                                                name="order_status" placeholder="order"
                                                                value="{{ $order->order_status }}"></div>
                                                        <div class="col-4"><input
                                                                class="form-control form-control-sm"
                                                                name="payment_status" placeholder="payment"
                                                                value="{{ $order->payment_status }}"></div>
                                                        <div class="col-4"><input
                                                                class="form-control form-control-sm"
                                                                name="delivery_status" placeholder="delivery"
                                                                value="{{ $order->delivery_status }}"></div>
                                                        <div class="col-8"><input
                                                                class="form-control form-control-sm" name="message"
                                                                placeholder="پیام sync"></div>
                                                        <div class="col-4"><button
                                                                class="btn btn-sm btn-outline-primary w-100"
                                                                type="submit">ثبت</button></div>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-muted" colspan="7">هنوز سفارشی از فروشگاه وارد نشده
                                                    است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $orders->links() }}</div>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">نگاشت های فعال</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>کالای داخلی</th>
                                                    <th>شناسه فروشگاه</th>
                                                    <th>SKU</th>
                                                    <th>جهت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($mappings as $mapping)
                                                    <tr>
                                                        <td>{{ optional($mapping->product)->display_name ?: optional($mapping->product)->title }}
                                                        </td>
                                                        <td>{{ $mapping->external_product_id }}
                                                            {{ $mapping->external_variant_id ? '/ ' . $mapping->external_variant_id : '' }}
                                                        </td>
                                                        <td>{{ $mapping->external_sku }}</td>
                                                        <td>{{ $mapping->sync_direction }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-muted" colspan="4">نگاشتی ثبت نشده است.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">پیش نمایش خروجی کالا</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>SKU</th>
                                                    <th>عنوان</th>
                                                    <th>قیمت</th>
                                                    <th>موجودی</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($exportPreview as $item)
                                                    <tr>
                                                        <td>{{ $item['sku'] }}</td>
                                                        <td>{{ $item['title'] }}</td>
                                                        <td>{{ number_format($item['price']) }}</td>
                                                        <td>{{ number_format($item['stock'], 2) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-muted" colspan="4">برای پیش نمایش ابتدا
                                                            کانال فعال بسازید.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">آخرین لاگ های sync</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>زمان</th>
                                            <th>کانال</th>
                                            <th>جهت</th>
                                            <th>موجودیت</th>
                                            <th>عملیات</th>
                                            <th>وضعیت</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($logs as $log)
                                            <tr>
                                                <td>{{ $log->created_at }}</td>
                                                <td>{{ optional($log->channel)->title }}</td>
                                                <td>{{ $log->direction }}</td>
                                                <td>{{ $log->entity_type }} {{ $log->entity_key }}</td>
                                                <td>{{ $log->action }}</td>
                                                <td><span
                                                        class="badge bg-label-{{ $statusColors[$log->status] ?? 'secondary' }}">{{ $log->status }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-muted" colspan="6">لاگی ثبت نشده است.</td>
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

<script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
</body>

</html>
