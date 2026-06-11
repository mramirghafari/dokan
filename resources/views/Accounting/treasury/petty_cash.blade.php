<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>تنخواه - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
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
                            <h4 class="mb-0"><span class="text-muted fw-light">مالی و حسابداری /</span> تنخواه</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-dark"
                                    href="{{ route('Accounting.treasury.liquidity') }}">مانده نقدینگی</a>
                                <a class="btn btn-outline-secondary"
                                    href="{{ route('Accounting.treasury') }}">بازگشت</a>
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
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">تنخواه فعال</small>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format((int) $report['totals']['active_count']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">مانده تنخواه</small>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format((float) $report['totals']['balance']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">سقف مصوب</small>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format((float) $report['totals']['ceiling']) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card h-100">
                                    <div class="card-body"><small class="text-muted">تعداد صندوق</small>
                                        <h4 class="mb-0 text-end">
                                            {{ number_format((int) $report['totals']['fund_count']) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-3">
                                <form class="card h-100" method="POST"
                                    action="{{ route('Accounting.treasury.pettyCash.funds.store') }}">
                                    @csrf
                                    <div class="card-header">
                                        <h5 class="mb-0">تعریف تنخواه</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-12"><label class="form-label">عنوان</label><input type="text"
                                                name="title" class="form-control" required></div>
                                        <div class="col-12"><label class="form-label">کد</label><input type="text"
                                                name="fund_code" class="form-control" placeholder="خالی = خودکار"></div>
                                        <div class="col-12"><label class="form-label">حساب تنخواه</label><select
                                                name="account_id" class="form-select account-select" required>
                                                <option value="">انتخاب حساب</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->code }} -
                                                        {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12"><label class="form-label">تنخواه دار</label><select
                                                name="custodian_user_id" class="form-select account-select">
                                                <option value="">بدون کاربر</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">
                                                        {{ $user->name ?: $user->username }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12"><label class="form-label">نام تحویل گیرنده</label><input
                                                type="text" name="custodian_name" class="form-control"></div>
                                        <div class="col-12"><label class="form-label">سقف مصوب</label><input
                                                type="number" min="0" step="0.01" name="ceiling_amount"
                                                class="form-control text-end" value="0"></div>
                                        <div class="col-12"><label class="form-label">وضعیت</label><select
                                                name="status" class="form-select">
                                                <option value="active">فعال</option>
                                                <option value="inactive">غیرفعال</option>
                                                <option value="closed">بسته شده</option>
                                            </select></div>
                                        <div class="col-12"><label class="form-label">شرح</label><input
                                                type="text" name="description" class="form-control"></div>
                                    </div>
                                    <div class="card-footer text-end"><button class="btn btn-primary"
                                            type="submit">ثبت تنخواه</button></div>
                                </form>
                            </div>

                            <div class="col-12 col-xl-9">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h5 class="mb-0">مانده و کنترل تنخواه ها</h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>تنخواه</th>
                                                    <th>تنخواه دار</th>
                                                    <th>حساب</th>
                                                    <th class="text-end">سقف</th>
                                                    <th class="text-end">مانده دفتر</th>
                                                    <th>وضعیت</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($funds as $fund)
                                                    <tr>
                                                        <td>{{ $fund->fund_code }}<br><small>{{ $fund->title }}</small>
                                                        </td>
                                                        <td>{{ optional($fund->custodian)->name ?: ($fund->custodian_name ?: '-') }}
                                                        </td>
                                                        <td>{{ optional($fund->account)->code }} -
                                                            {{ optional($fund->account)->name }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) $fund->ceiling_amount) }}</td>
                                                        <td class="text-end">
                                                            {{ number_format((float) ($report['balances'][$fund->account_id] ?? 0)) }}
                                                        </td>
                                                        <td><span
                                                                class="badge bg-label-{{ $fund->status === 'active' ? 'success' : 'secondary' }}">{{ $fund->status === 'active' ? 'فعال' : ($fund->status === 'closed' ? 'بسته شده' : 'غیرفعال') }}</span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">هنوز
                                                            تنخواهی تعریف نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-12 col-xl-4">
                                <form class="card h-100" method="POST"
                                    action="{{ $funds->first() ? route('Accounting.treasury.pettyCash.charge', $funds->first()) : '#' }}"
                                    data-action-template="{{ url('Accounting/treasury/petty-cash') }}/__fund__/charge">
                                    @csrf
                                    <div class="card-header">
                                        <h5 class="mb-0">شارژ تنخواه</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-12"><label class="form-label">تنخواه</label><select
                                                class="form-select petty-fund-select" required>
                                                <option value="">انتخاب</option>
                                                @foreach ($funds as $fund)
                                                    <option value="{{ $fund->id }}">{{ $fund->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12"><label class="form-label">تاریخ</label><input
                                                type="date" name="transaction_date_en" class="form-control"
                                                value="{{ $today }}"></div>
                                        <div class="col-12"><label class="form-label">حساب پرداخت کننده</label><select
                                                name="counter_account_id" class="form-select account-select" required>
                                                <option value="">صندوق/بانک</option>
                                                @foreach ($treasuryAccounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->code }} -
                                                        {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12"><label class="form-label">مبلغ</label><input
                                                type="number" min="0.01" step="0.01" name="amount"
                                                class="form-control text-end" required></div>
                                        <div class="col-12"><label class="form-label">شماره مرجع</label><input
                                                type="text" name="reference_number" class="form-control"></div>
                                        <div class="col-12"><label class="form-label">شرح</label><input
                                                type="text" name="description" class="form-control"></div>
                                    </div>
                                    <div class="card-footer text-end"><button class="btn btn-success" type="submit"
                                            @disabled($funds->isEmpty())>ثبت شارژ</button></div>
                                </form>
                            </div>

                            <div class="col-12 col-xl-4">
                                <form class="card h-100" method="POST"
                                    action="{{ $funds->first() ? route('Accounting.treasury.pettyCash.expense', $funds->first()) : '#' }}"
                                    data-action-template="{{ url('Accounting/treasury/petty-cash') }}/__fund__/expense">
                                    @csrf
                                    <div class="card-header">
                                        <h5 class="mb-0">ثبت هزینه تنخواه</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-12"><label class="form-label">تنخواه</label><select
                                                class="form-select petty-fund-select" required>
                                                <option value="">انتخاب</option>
                                                @foreach ($funds as $fund)
                                                    <option value="{{ $fund->id }}">{{ $fund->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6"><label class="form-label">تاریخ</label><input
                                                type="date" name="transaction_date_en" class="form-control"
                                                value="{{ $today }}"></div>
                                        <div class="col-12 col-md-6"><label class="form-label">مرجع</label><input
                                                type="text" name="reference_number" class="form-control"></div>
                                        <div class="col-12"><label class="form-label">مرکز هزینه</label><select
                                                name="cost_center_id" class="form-select account-select" required>
                                                <option value="">انتخاب</option>
                                                @foreach ($costCenters as $costCenter)
                                                    <option value="{{ $costCenter->id }}">{{ $costCenter->code }} -
                                                        {{ $costCenter->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12"><label class="form-label">نوع هزینه</label><select
                                                name="expense_type_id" class="form-select account-select" required>
                                                <option value="">انتخاب</option>
                                                @foreach ($expenseTypes as $expenseType)
                                                    <option value="{{ $expenseType->id }}">{{ $expenseType->code }} -
                                                        {{ $expenseType->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12"><label class="form-label">حساب هزینه</label><select
                                                name="expense_account_id" class="form-select account-select">
                                                <option value="">حساب نوع هزینه/سیستمی</option>
                                                @foreach ($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->code }} -
                                                        {{ $account->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6"><label class="form-label">مبلغ</label><input
                                                type="number" min="0.01" step="0.01" name="amount"
                                                class="form-control text-end" required></div>
                                        <div class="col-12 col-md-6"><label class="form-label">مالیات</label><input
                                                type="number" min="0" step="0.01" name="tax_amount"
                                                class="form-control text-end" value="0"></div>
                                        <div class="col-12"><label class="form-label">شرح</label><input
                                                type="text" name="description" class="form-control"></div>
                                    </div>
                                    <div class="card-footer text-end"><button class="btn btn-primary" type="submit"
                                            @disabled($funds->isEmpty())>ثبت هزینه</button></div>
                                </form>
                            </div>

                            <div class="col-12 col-xl-4">
                                <form class="card h-100" method="POST"
                                    action="{{ $funds->first() ? route('Accounting.treasury.pettyCash.settlement', $funds->first()) : '#' }}"
                                    data-action-template="{{ url('Accounting/treasury/petty-cash') }}/__fund__/settlement">
                                    @csrf
                                    <div class="card-header">
                                        <h5 class="mb-0">تسویه/برگشت تنخواه</h5>
                                    </div>
                                    <div class="card-body row g-3">
                                        <div class="col-12"><label class="form-label">تنخواه</label><select
                                                class="form-select petty-fund-select" required>
                                                <option value="">انتخاب</option>
                                                @foreach ($funds as $fund)
                                                    <option value="{{ $fund->id }}">{{ $fund->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12"><label class="form-label">تاریخ</label><input
                                                type="date" name="transaction_date_en" class="form-control"
                                                value="{{ $today }}"></div>
                                        <div class="col-12"><label class="form-label">حساب دریافت کننده</label><select
                                                name="counter_account_id" class="form-select account-select" required>
                                                <option value="">صندوق/بانک</option>
                                                @foreach ($treasuryAccounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->code }} -
                                                        {{ $account->name }}</option>
                                                @endforeach
                                            </select></div>
                                        <div class="col-12"><label class="form-label">مبلغ برگشت</label><input
                                                type="number" min="0.01" step="0.01" name="amount"
                                                class="form-control text-end" required></div>
                                        <div class="col-12"><label class="form-label">شماره مرجع</label><input
                                                type="text" name="reference_number" class="form-control"></div>
                                        <div class="col-12"><label class="form-label">شرح</label><input
                                                type="text" name="description" class="form-control"></div>
                                    </div>
                                    <div class="card-footer text-end"><button class="btn btn-warning" type="submit"
                                            @disabled($funds->isEmpty())>ثبت تسویه</button></div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">گردش تنخواه</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>تاریخ</th>
                                            <th>تنخواه</th>
                                            <th>نوع</th>
                                            <th class="text-end">مبلغ</th>
                                            <th>مرجع</th>
                                            <th>سند</th>
                                            <th>شرح</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($report['transactions'] as $transaction)
                                            <tr>
                                                <td>{{ optional($transaction->transaction_date_en)->format('Y-m-d') ?: '-' }}
                                                </td>
                                                <td>{{ optional($transaction->fund)->title }}</td>
                                                <td>{{ ['charge' => 'شارژ', 'expense' => 'هزینه', 'settlement' => 'تسویه'][$transaction->transaction_type] ?? $transaction->transaction_type }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format((float) $transaction->total_amount) }}</td>
                                                <td>{{ $transaction->reference_number ?: '-' }}</td>
                                                <td>{{ optional($transaction->voucher)->voucher_number ?: '-' }}</td>
                                                <td>{{ $transaction->description ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-4">گردشی ثبت نشده
                                                    است.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-3">{{ $report['transactions']->links() }}</div>
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
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    <script>
        $(function() {
            $('.account-select').select2({
                width: '100%'
            });
            $('.petty-fund-select').on('change', function() {
                const form = $(this).closest('form');
                const template = form.data('action-template');
                const fundId = $(this).val();
                if (template && fundId) {
                    form.attr('action', template.replace('__fund__', fundId));
                }
            });
        });
    </script>
</body>

</html>
