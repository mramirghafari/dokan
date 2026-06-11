<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>سامانه مودیان - دکان دارمینو</title>
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
                        @php
                            $statusLabels = [
                                'draft' => 'پیش نویس',
                                'sent' => 'ارسال شده',
                                'failed' => 'خطای ارسال',
                                'accepted' => 'تایید شده',
                                'rejected' => 'رد شده',
                            ];
                            $patternLabels = [
                                'sales' => 'فروش',
                                'sales_return' => 'برگشت فروش',
                                'service' => 'خدمات',
                                'contracting' => 'پیمانکاری',
                                'fixed_asset' => 'فروش دارایی',
                            ];
                        @endphp

                        <h4 class="mb-4"><span class="text-muted fw-light">مالی و حسابداری /</span> سامانه مودیان و
                            تکالیف قانونی</h4>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="card p-3"><small class="text-muted">پیش
                                        نویس</small><strong>{{ number_format($totals['draft']) }}</strong></div>
                            </div>
                            <div class="col-md-3">
                                <div class="card p-3"><small class="text-muted">ارسال
                                        شده</small><strong>{{ number_format($totals['sent']) }}</strong></div>
                            </div>
                            <div class="col-md-3">
                                <div class="card p-3"><small class="text-muted">تایید
                                        شده</small><strong>{{ number_format($totals['accepted']) }}</strong></div>
                            </div>
                            <div class="col-md-3">
                                <div class="card p-3"><small class="text-muted">خطا / رد
                                        شده</small><strong>{{ number_format($totals['failed']) }}</strong></div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-lg-7">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">تنظیمات کارپوشه و ارسال</h5>
                                    </div>
                                    <form method="POST" action="{{ route('taxpayer.settings.store') }}">
                                        @csrf
                                        <input name="id" type="hidden"
                                            value="{{ optional($activeSetting)->id }}">
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-4"><label class="form-label">عنوان</label><input
                                                        class="form-control" name="title"
                                                        value="{{ optional($activeSetting)->title ?: 'تنظیمات سامانه مودیان' }}">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">روش ارسال</label>
                                                    <select class="form-select" name="send_mode" required>
                                                        <option value="trusted_company" @selected(optional($activeSetting)->send_mode === 'trusted_company')>
                                                            شرکت معتمد</option>
                                                        <option value="direct" @selected(optional($activeSetting)->send_mode === 'direct')>ارسال مستقیم
                                                        </option>
                                                        <option value="manual" @selected(optional($activeSetting)->send_mode === 'manual')>ثبت دستی
                                                            وضعیت</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">محیط</label>
                                                    <select class="form-select" name="environment" required>
                                                        <option value="sandbox" @selected(optional($activeSetting)->environment === 'sandbox')>آزمایشی
                                                        </option>
                                                        <option value="production" @selected(optional($activeSetting)->environment === 'production')>اصلی
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4"><label class="form-label">شناسه حافظه
                                                        مالیاتی</label><input class="form-control" name="memory_id"
                                                        value="{{ optional($activeSetting)->memory_id }}"></div>
                                                <div class="col-md-4"><label class="form-label">کد شعبه
                                                        مالیاتی</label><input class="form-control"
                                                        name="branch_tax_code"
                                                        value="{{ optional($activeSetting)->branch_tax_code }}"></div>
                                                <div class="col-md-4"><label class="form-label">کد اقتصادی
                                                        فروشنده</label><input class="form-control"
                                                        name="economic_number"
                                                        value="{{ optional($activeSetting)->economic_number }}"></div>
                                                <div class="col-md-4"><label class="form-label">شناسه ملی
                                                        فروشنده</label><input class="form-control"
                                                        name="seller_national_id"
                                                        value="{{ optional($activeSetting)->seller_national_id }}">
                                                </div>
                                                <div class="col-md-4"><label class="form-label">کد پستی
                                                        فروشنده</label><input class="form-control"
                                                        name="seller_postal_code"
                                                        value="{{ optional($activeSetting)->seller_postal_code }}">
                                                </div>
                                                <div class="col-md-4"><label class="form-label">شرکت
                                                        معتمد</label><input class="form-control"
                                                        name="trusted_company_name"
                                                        value="{{ optional($activeSetting)->trusted_company_name }}">
                                                </div>
                                                <div class="col-md-6"><label class="form-label">Endpoint</label><input
                                                        class="form-control" name="endpoint_url"
                                                        value="{{ optional($activeSetting)->endpoint_url }}"></div>
                                                <div class="col-md-3"><label class="form-label">Alias
                                                        گواهی</label><input class="form-control"
                                                        name="certificate_alias"
                                                        value="{{ optional($activeSetting)->certificate_alias }}">
                                                </div>
                                                <div class="col-md-3 d-flex align-items-end gap-3">
                                                    <label class="form-check"><input class="form-check-input"
                                                            name="auto_send" type="checkbox" value="1"
                                                            @checked(optional($activeSetting)->auto_send)> ارسال خودکار</label>
                                                    <label class="form-check"><input class="form-check-input"
                                                            name="is_active" type="checkbox" value="1"
                                                            @checked(optional($activeSetting)->is_active ?? true)> فعال</label>
                                                </div>
                                                <div class="col-12"><label class="form-label">شرح</label><input
                                                        class="form-control" name="description"
                                                        value="{{ optional($activeSetting)->description }}"></div>
                                            </div>
                                        </div>
                                        <div class="card-footer text-end"><button class="btn btn-primary"
                                                type="submit">ذخیره تنظیمات</button></div>
                                    </form>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">نگاشت کالا و خدمت</h5>
                                    </div>
                                    <form method="POST" action="{{ route('taxpayer.mappings.store') }}">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">کالا / خدمت داخلی</label>
                                                    <select class="form-select" name="product_id">
                                                        <option value="">بدون کالا، نگاشت عمومی</option>
                                                        @foreach ($products as $product)
                                                            <option value="{{ $product->id }}">
                                                                {{ $product->sku ?: $product->id }} -
                                                                {{ $product->display_name ?: $product->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">نوع داخلی</label>
                                                    <select class="form-select" name="local_type" required>
                                                        <option value="product">کالا</option>
                                                        <option value="service">خدمت</option>
                                                        <option value="fixed_asset">دارایی ثابت</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4"><label class="form-label">کد داخلی</label><input
                                                        class="form-control" name="local_code"></div>
                                                <div class="col-md-4"><label class="form-label">عنوان
                                                        داخلی</label><input class="form-control" name="local_title">
                                                </div>
                                                <div class="col-md-6"><label class="form-label">شناسه کالا/خدمت
                                                        مالیاتی</label><input class="form-control" name="tax_item_id"
                                                        required></div>
                                                <div class="col-md-6"><label class="form-label">عنوان
                                                        مالیاتی</label><input class="form-control"
                                                        name="tax_item_title"></div>
                                                <div class="col-md-4"><label class="form-label">واحد
                                                        مالیاتی</label><input class="form-control"
                                                        name="measurement_unit_code" value="C62"></div>
                                                <div class="col-md-4">
                                                    <label class="form-label">الگوی صورتحساب</label>
                                                    <select class="form-select" name="invoice_pattern" required>
                                                        @foreach ($patternLabels as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4"><label class="form-label">نرخ مالیات پیش
                                                        فرض</label><input class="form-control" name="default_tax_rate"
                                                        type="number" min="0" max="100" step="0.01"
                                                        value="0"></div>
                                                <div class="col-md-6"><label class="form-check mt-4"><input
                                                            class="form-check-input" name="is_active" type="checkbox"
                                                            value="1" checked> فعال</label></div>
                                                <div class="col-md-6"><input class="form-control mt-3"
                                                        name="description" placeholder="شرح"></div>
                                            </div>
                                        </div>
                                        <div class="card-footer text-end"><button class="btn btn-outline-primary"
                                                type="submit">ثبت نگاشت</button></div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-lg-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">فاکتورهای فروش آماده ارسال</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>فاکتور</th>
                                                    <th>مشتری</th>
                                                    <th>مبلغ</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($salesCandidates as $factor)
                                                    <tr>
                                                        <td>{{ $factor->invoiceID ?: $factor->id }}</td>
                                                        <td>{{ optional($factor->customer)->name }}</td>
                                                        <td>{{ number_format($factor->fullPrice) }}</td>
                                                        <td>
                                                            <form method="POST"
                                                                action="{{ route('taxpayer.sales.prepare', $factor) }}">
                                                                @csrf<button class="btn btn-sm btn-outline-primary"
                                                                    type="submit">آماده سازی</button></form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-muted" colspan="4">فاکتور قطعی یافت نشد.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">صورت وضعیت های پیمانکاری</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>شماره</th>
                                                    <th>پروژه</th>
                                                    <th>مبلغ</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($contractingCandidates as $statement)
                                                    <tr>
                                                        <td>{{ $statement->statement_number }}</td>
                                                        <td>{{ optional($statement->project)->title }}</td>
                                                        <td>{{ number_format($statement->current_amount) }}</td>
                                                        <td>
                                                            <form method="POST"
                                                                action="{{ route('taxpayer.contracting.prepare', $statement) }}">
                                                                @csrf<button class="btn btn-sm btn-outline-primary"
                                                                    type="submit">آماده سازی</button></form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-muted" colspan="4">صورت وضعیت ثبت شده یافت
                                                            نشد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">صورت حساب فروش دارایی</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th>شماره</th>
                                                    <th>دارایی</th>
                                                    <th>مبلغ</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($assetCandidates as $assetInvoice)
                                                    <tr>
                                                        <td>{{ $assetInvoice->invoice_number }}</td>
                                                        <td>{{ $assetInvoice->asset_name }}</td>
                                                        <td>{{ number_format($assetInvoice->total_amount) }}</td>
                                                        <td>
                                                            <form method="POST"
                                                                action="{{ route('taxpayer.assets.prepare', $assetInvoice) }}">
                                                                @csrf<button class="btn btn-sm btn-outline-primary"
                                                                    type="submit">اتصال عمومی</button></form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td class="text-muted" colspan="4">صورت حساب دارایی یافت
                                                            نشد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">صورتحساب های مودیان</h5>
                                <form class="d-flex gap-2" method="GET">
                                    <select class="form-select form-select-sm" name="status">
                                        <option value="">همه وضعیت ها</option>
                                        @foreach ($statusLabels as $key => $label)
                                            <option value="{{ $key }}" @selected(request('status') === $key)>
                                                {{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-outline-secondary" type="submit">فیلتر</button>
                                </form>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>شماره</th>
                                            <th>الگو</th>
                                            <th>خریدار</th>
                                            <th>مبلغ</th>
                                            <th>وضعیت</th>
                                            <th>شناسه/مرجع</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($invoices as $invoice)
                                            <tr>
                                                <td><strong>{{ $invoice->invoice_number }}</strong><br><small
                                                        class="text-muted">{{ $invoice->source_number }}</small></td>
                                                <td>{{ $patternLabels[$invoice->invoice_pattern] ?? $invoice->invoice_pattern }}
                                                </td>
                                                <td>{{ $invoice->buyer_name ?: optional($invoice->customer)->name }}
                                                </td>
                                                <td>{{ number_format($invoice->total_amount) }}<br><small
                                                        class="text-muted">مالیات
                                                        {{ number_format($invoice->tax_amount) }}</small></td>
                                                <td><span
                                                        class="badge bg-label-{{ in_array($invoice->status, ['failed', 'rejected'], true) ? 'danger' : ($invoice->status === 'accepted' ? 'success' : 'secondary') }}">{{ $statusLabels[$invoice->status] ?? $invoice->status }}</span>
                                                </td>
                                                <td><small>{{ $invoice->tax_id ?: '-' }}<br>{{ $invoice->reference_number ?: '-' }}</small>
                                                </td>
                                                <td style="min-width: 280px">
                                                    <form class="row g-1" method="POST"
                                                        action="{{ route('taxpayer.invoices.status', $invoice) }}">
                                                        @csrf
                                                        <div class="col-4">
                                                            <select class="form-select form-select-sm" name="status">
                                                                <option value="sent">ارسال</option>
                                                                <option value="accepted">تایید</option>
                                                                <option value="failed">خطا</option>
                                                                <option value="rejected">رد</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-4"><input
                                                                class="form-control form-control-sm" name="tax_id"
                                                                placeholder="Tax ID" value="{{ $invoice->tax_id }}">
                                                        </div>
                                                        <div class="col-4"><input
                                                                class="form-control form-control-sm"
                                                                name="reference_number" placeholder="Reference"
                                                                value="{{ $invoice->reference_number }}"></div>
                                                        <div class="col-8"><input
                                                                class="form-control form-control-sm"
                                                                name="error_message" placeholder="پیام خطا / پاسخ">
                                                        </div>
                                                        <div class="col-4"><button
                                                                class="btn btn-sm btn-primary w-100"
                                                                type="submit">ثبت</button></div>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-muted" colspan="7">هنوز صورتحساب مودیان ثبت نشده
                                                    است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">{{ $invoices->links() }}</div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">نگاشت های فعال</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>داخلی</th>
                                            <th>شناسه مالیاتی</th>
                                            <th>واحد</th>
                                            <th>الگو</th>
                                            <th>نرخ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($mappings as $mapping)
                                            <tr>
                                                <td>{{ $mapping->local_title }}</td>
                                                <td>{{ $mapping->tax_item_id }} - {{ $mapping->tax_item_title }}</td>
                                                <td>{{ $mapping->measurement_unit_code }}</td>
                                                <td>{{ $patternLabels[$mapping->invoice_pattern] ?? $mapping->invoice_pattern }}
                                                </td>
                                                <td>{{ $mapping->default_tax_rate }}%</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td class="text-muted" colspan="5">نگاشتی ثبت نشده است.</td>
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
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
</body>

</html>
