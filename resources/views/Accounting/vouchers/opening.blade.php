<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>سند افتتاحیه - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
</head>

<body>
    @include('partials.panel-toasts')
    @php
        $rows = $rows ?? [];
        $oldAccounts = old('account_id', []);
        $rowCount = max(2, count($oldAccounts), count($rows));
    @endphp
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections/sidebar')
            <div class="layout-page">
                @include('sections/header')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span>
                                سند افتتاحیه (افتتاح دوره)</h4>
                            <a class="btn btn-outline-secondary" href="{{ route('Accounting.vouchers') }}">بازگشت به
                                اسناد</a>
                        </div>

                        <div class="alert alert-info">
                            <h6 class="alert-heading mb-1">سند افتتاحیه چیست؟</h6>
                            <span class="small">
                                مانده ابتدای دورهٔ همهٔ حساب‌های دائمی (دارایی، بدهی و سرمایه) را به ابتدای سال مالی منتقل می‌کند.
                                ردیف‌های زیر بر اساس «مانده افتتاحیه» ثبت‌شده روی هر سرفصل و ماهیت حساب از پیش پر شده‌اند؛ مبالغ را اصلاح کنید،
                                سند باید <strong>متوازن</strong> (جمع بدهکار = جمع بستانکار) باشد. پس از ثبت موقت، آن را «دائمی» کنید.
                            </span>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        @if (!$selectedFiscalYear)
                            <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div>
                                    <strong>سال مالی ثبت نشده است.</strong>
                                    قبل از سند افتتاحیه باید سال مالی جاری ({{ verta()->format('Y') }}) را برای پنل تعریف کنید.
                                    مسیر: <span class="text-muted">مالی و حسابداری ← بستن دوره مالی</span>
                                </div>
                                <a href="{{ route('Accounting.fiscalClosing') }}" class="btn btn-warning flex-shrink-0">
                                    ایجاد سال مالی {{ verta()->format('Y') }}
                                </a>
                            </div>
                        @endif

                        @if (!empty($existingDraft))
                            <div class="alert alert-secondary">
                                یک سند افتتاحیهٔ <strong>موقت</strong> (شماره {{ $existingDraft->voucher_number }}) برای این سال مالی وجود دارد و در فرم زیر بارگذاری شد. با ثبت مجدد، همان سند به‌روزرسانی می‌شود.
                            </div>
                        @endif

                        <form action="{{ route('Accounting.vouchers.opening') }}" method="GET" id="fiscal-year-form" class="card mb-4">
                            <div class="card-header"><h5 class="mb-0">سربرگ سند</h5></div>
                            <div class="card-body">
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">نوع سند</label>
                                        <input type="text" class="form-control" value="افتتاحیه" readonly>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">دوره مالی</label>
                                        <select name="fiscal_year_id" class="form-select" onchange="document.getElementById('fiscal-year-form').submit()">
                                            @foreach ($fiscalYears as $fiscalYear)
                                                <option value="{{ $fiscalYear->id }}"
                                                    @selected($selectedFiscalYear && (int) $selectedFiscalYear->id === (int) $fiscalYear->id)>
                                                    {{ $fiscalYear->title }} ({{ $fiscalYear->status === 'open' ? 'باز' : 'بسته' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">وضعیت</label>
                                        <input type="text" class="form-control" value="موقت (پیش‌نویس)" readonly>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <span class="text-muted small">با تغییر دوره مالی، مانده‌ها دوباره از سرفصل‌ها خوانده می‌شوند.</span>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <form action="{{ route('Accounting.vouchers.opening.store') }}" method="POST" id="voucher-form">
                            @csrf
                            <input type="hidden" name="fiscal_year_id" value="{{ $selectedFiscalYear->id ?? '' }}">

                            <div class="card mb-4">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-md-3">
                                            @include('partials.accounting.voucher-date-field')
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label">شماره عطف</label>
                                            <input type="text" name="reference_number" class="form-control"
                                                value="{{ old('reference_number', $existingDraft->reference_number ?? '') }}"
                                                placeholder="اختیاری">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">شرح سند</label>
                                            <input type="text" name="description" class="form-control"
                                                value="{{ old('description', $existingDraft->description ?? ('سند افتتاحیه ' . ($selectedFiscalYear->title ?? ''))) }}"
                                                placeholder="شرح کلی سند افتتاحیه">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">اقلام سند افتتاحیه</h5>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-row">افزودن ردیف</button>
                                </div>
                                <div class="card-datatable table-responsive">
                                    <table class="table align-middle" id="voucher-items">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px">ردیف</th>
                                                <th style="min-width: 260px">حساب (کل / معین)</th>
                                                <th>شرح آرتیکل</th>
                                                <th style="min-width: 420px">تفصیل شناور</th>
                                                <th style="min-width: 320px">ارز و نرخ</th>
                                                <th style="width: 160px">بدهکار</th>
                                                <th style="width: 160px">بستانکار</th>
                                                <th style="width: 50px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @for ($i = 0; $i < $rowCount; $i++)
                                                @php
                                                    $row = $rows[$i] ?? [];
                                                    $selectedAccountId = old('account_id.' . $i, $row['account_id'] ?? '');
                                                @endphp
                                                <tr>
                                                    <td class="text-center row-index">{{ $i + 1 }}</td>
                                                    <td>
                                                        @include('partials.accounting.account-cascader-cell', ['accounts' => $accounts, 'selectedAccountId' => $selectedAccountId])
                                                    </td>
                                                    <td><input type="text" name="item_description[]" class="form-control"
                                                            placeholder="شرح"
                                                            value="{{ old('item_description.' . $i, $row['description'] ?? '') }}"></td>
                                                    <td>
                                                        <div class="row g-2">
                                                            <div class="col-12 col-md-6">
                                                                <select name="cost_center_id[]" class="form-select form-select-sm select2-basic">
                                                                    <option value="">مرکز هزینه</option>
                                                                    @foreach ($costCenters as $costCenter)
                                                                        <option value="{{ $costCenter->id }}"
                                                                            @selected((string) old('cost_center_id.' . $i, $row['cost_center_id'] ?? '') === (string) $costCenter->id)>
                                                                            {{ $costCenter->code }} - {{ $costCenter->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <select name="revenue_center_id[]" class="form-select form-select-sm select2-basic">
                                                                    <option value="">مرکز درآمد</option>
                                                                    @foreach ($revenueCenters as $revenueCenter)
                                                                        <option value="{{ $revenueCenter->id }}"
                                                                            @selected((string) old('revenue_center_id.' . $i, $row['revenue_center_id'] ?? '') === (string) $revenueCenter->id)>
                                                                            {{ $revenueCenter->code }} - {{ $revenueCenter->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <select name="branch_id[]" class="form-select form-select-sm select2-basic">
                                                                    <option value="">شعبه/انبار</option>
                                                                    @foreach ($branches as $branch)
                                                                        <option value="{{ $branch->id }}"
                                                                            @selected((string) old('branch_id.' . $i, $row['branch_id'] ?? '') === (string) $branch->id)>
                                                                            {{ $branch->title }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <x-erp-remote-select
                                                                    entity="customers"
                                                                    name="customer_id[]"
                                                                    :value="old('customer_id.' . $i, $row['customer_id'] ?? null)"
                                                                    placeholder="مشتری / تامین‌کننده"
                                                                    class="form-select form-select-sm erp-remote-select" />
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <input type="text" name="project_code[]" class="form-control form-control-sm"
                                                                    placeholder="کد پروژه"
                                                                    value="{{ old('project_code.' . $i, $row['project_code'] ?? '') }}">
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <x-erp-remote-select
                                                                    entity="products"
                                                                    name="product_id[]"
                                                                    :value="old('product_id.' . $i, $row['product_id'] ?? null)"
                                                                    placeholder="کالا"
                                                                    class="form-select form-select-sm erp-remote-select"
                                                                    :filters="config('erp_scale.remote_lookup.product_filters')" />
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="row g-2">
                                                            <div class="col-12">
                                                                <select name="currency_id[]" class="form-select form-select-sm select2-basic">
                                                                    <option value="">ارز پایه / بدون ارز</option>
                                                                    @foreach ($currencies as $currency)
                                                                        <option value="{{ $currency->id }}"
                                                                            @selected((string) old('currency_id.' . $i, $row['currency_id'] ?? '') === (string) $currency->id)>
                                                                            {{ $currency->code }} - {{ $currency->title }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-6">
                                                                <input type="number" min="0" step="0.0001" name="foreign_debit_amount[]"
                                                                    class="form-control form-control-sm foreign-debit text-end" placeholder="ارزی بدهکار"
                                                                    value="{{ old('foreign_debit_amount.' . $i, $row['foreign_debit_amount'] ?? '') }}">
                                                            </div>
                                                            <div class="col-6">
                                                                <input type="number" min="0" step="0.0001" name="foreign_credit_amount[]"
                                                                    class="form-control form-control-sm foreign-credit text-end" placeholder="ارزی بستانکار"
                                                                    value="{{ old('foreign_credit_amount.' . $i, $row['foreign_credit_amount'] ?? '') }}">
                                                            </div>
                                                            <div class="col-12">
                                                                <input type="number" min="0" step="0.000001" name="exchange_rate[]"
                                                                    class="form-control form-control-sm exchange-rate text-end" placeholder="نرخ تبدیل"
                                                                    value="{{ old('exchange_rate.' . $i, $row['exchange_rate'] ?? '') }}">
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td><input type="number" min="0" step="0.01" name="debit_amount[]"
                                                            class="form-control debit text-end"
                                                            value="{{ old('debit_amount.' . $i, $row['debit_amount'] ?? 0) }}"></td>
                                                    <td><input type="number" min="0" step="0.01" name="credit_amount[]"
                                                            class="form-control credit text-end"
                                                            value="{{ old('credit_amount.' . $i, $row['credit_amount'] ?? 0) }}"></td>
                                                    <td><button type="button"
                                                            class="btn btn-sm btn-icon btn-outline-danger remove-row"><x-ui.icon name="trash" /></button></td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="5" class="text-end">جمع کل سند</th>
                                                <th class="text-end" id="total-debit">0</th>
                                                <th class="text-end" id="total-credit">0</th>
                                                <th></th>
                                            </tr>
                                            <tr>
                                                <th colspan="5" class="text-end">اختلاف (باید صفر باشد)</th>
                                                <th colspan="2" class="text-end" id="voucher-diff">0</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="card-body d-flex justify-content-end gap-2">
                                    <button type="submit" class="btn btn-primary" id="submit-opening" {{ $selectedFiscalYear ? '' : 'disabled' }}>ثبت سند افتتاحیه (موقت)</button>
                                </div>
                            </div>
                        </form>
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
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    @include('partials.erp-remote-select-assets')
    @include('partials.accounting.account-cascader-script', ['accounts' => $accounts])
    @include('partials.accounting.voucher-date-picker-assets')
    <script>
        $(function() {
            function initSelects() {
                $('.select2-basic:not(.erp-remote-select)').select2({ width: '100%' });
                if (window.ErpRemoteSelect) {
                    window.ErpRemoteSelect.init(document);
                }
            }

            function numberValue(input) {
                return parseFloat(String($(input).val()).replace(/,/g, '')) || 0;
            }

            function reindexRows() {
                $('#voucher-items tbody tr').each(function(index) {
                    $(this).find('.row-index').text(index + 1);
                });
            }

            function recalculate() {
                let totalDebit = 0;
                let totalCredit = 0;
                $('.debit').each(function() { totalDebit += numberValue(this); });
                $('.credit').each(function() { totalCredit += numberValue(this); });

                $('#total-debit').text(totalDebit.toLocaleString('en-US'));
                $('#total-credit').text(totalCredit.toLocaleString('en-US'));
                const diff = Math.abs(totalDebit - totalCredit);
                $('#voucher-diff').text(diff.toLocaleString('en-US'));
                $('#voucher-diff').toggleClass('text-danger', totalDebit !== totalCredit);
                $('#voucher-diff').toggleClass('text-success', totalDebit === totalCredit && totalDebit > 0);
            }

            function recalculateCurrencyRow(row) {
                const rate = numberValue(row.find('.exchange-rate'));
                const foreignDebit = numberValue(row.find('.foreign-debit'));
                const foreignCredit = numberValue(row.find('.foreign-credit'));
                if (rate > 0 && foreignDebit > 0) {
                    row.find('.debit').val((foreignDebit * rate).toFixed(2));
                    row.find('.credit').val('0');
                }
                if (rate > 0 && foreignCredit > 0) {
                    row.find('.credit').val((foreignCredit * rate).toFixed(2));
                    row.find('.debit').val('0');
                }
            }

            $('#add-row').on('click', function() {
                if (window.ErpRemoteSelect) {
                    window.ErpRemoteSelect.destroy('#voucher-items tbody tr:first');
                }
                $('.select2-basic:not(.erp-remote-select)').select2('destroy');
                const row = $('#voucher-items tbody tr:first').clone();
                row.find('select').val('');
                row.find('input').val('');
                row.find('.debit, .credit').val('0');
                $('#voucher-items tbody').append(row);
                if (window.AccountCascader) {
                    window.AccountCascader.resetRow(row.find('.account-cascader'));
                }
                initSelects();
                reindexRows();
                recalculate();
            });

            $(document).on('click', '.remove-row', function() {
                if ($('#voucher-items tbody tr').length > 2) {
                    $(this).closest('tr').remove();
                    reindexRows();
                    recalculate();
                }
            });

            $(document).on('input', '.debit, .credit', recalculate);
            $(document).on('input', '.foreign-debit, .foreign-credit, .exchange-rate', function() {
                recalculateCurrencyRow($(this).closest('tr'));
                recalculate();
            });

            $('#voucher-form').on('submit', function(event) {
                const totalDebit = $('.debit').toArray().reduce((sum, el) => sum + numberValue(el), 0);
                const totalCredit = $('.credit').toArray().reduce((sum, el) => sum + numberValue(el), 0);
                if (totalDebit <= 0 || Math.round(totalDebit) !== Math.round(totalCredit)) {
                    event.preventDefault();
                    alert('سند افتتاحیه متوازن نیست. جمع بدهکار و بستانکار باید برابر و بزرگ‌تر از صفر باشد.');
                }
            });

            if (window.AccountCascader) {
                window.AccountCascader.initAll(document);
            }
            initSelects();
            reindexRows();
            recalculate();
        });
    </script>
</body>

</html>
