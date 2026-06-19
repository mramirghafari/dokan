<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>پیمانکاری و پروژه - دکان دارمینو</title>
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
                            $statusLabels = [
                                'draft' => 'پیش نویس',
                                'active' => 'فعال',
                                'suspended' => 'تعلیق',
                                'closed' => 'خاتمه یافته',
                                'posted' => 'ثبت شده',
                                'approved' => 'تایید شده',
                                'paid' => 'تسویه شده',
                            ];
                            $guaranteeLabels = [
                                'bid' => 'شرکت در مناقصه',
                                'performance' => 'حسن انجام کار',
                                'advance' => 'پیش پرداخت',
                                'retention' => 'آزادسازی سپرده',
                                'other' => 'سایر',
                            ];
                            $costTypeLabels = [
                                'direct' => 'مستقیم',
                                'material' => 'مصالح و کالا',
                                'labor' => 'دستمزد',
                                'equipment' => 'ماشین آلات',
                                'subcontract' => 'پیمانکار جزء',
                                'overhead' => 'سربار',
                                'other' => 'سایر',
                            ];
                        @endphp

                        <h4 class="mb-4"><span class="text-muted fw-light">مالی و حسابداری /</span> پیمانکاری و پروژه
                        </h4>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-2">
                                <div class="card p-3"><small class="text-muted">کل پروژه
                                        ها</small><strong>{{ number_format($totals['projects']) }}</strong></div>
                            </div>
                            <div class="col-md-2">
                                <div class="card p-3"><small
                                        class="text-muted">فعال</small><strong>{{ number_format($totals['active']) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card p-3"><small class="text-muted">مبلغ
                                        قرارداد</small><strong>{{ number_format($totals['contract_amount']) }}</strong>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card p-3"><small class="text-muted">صورت وضعیت
                                        ها</small><strong>{{ number_format($totals['statements']) }}</strong></div>
                            </div>
                            <div class="col-md-2">
                                <div class="card p-3"><small class="text-muted">هزینه
                                        پروژه</small><strong>{{ number_format($totals['costs']) }}</strong></div>
                            </div>
                            <div class="col-md-2">
                                <div class="card p-3"><small class="text-muted">ضمانت
                                        فعال</small><strong>{{ number_format($totals['guarantees']) }}</strong></div>
                            </div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">ثبت قرارداد و فهرست بها</h5>
                            </div>
                            <form method="POST" action="{{ route('contracting.projects.store') }}">
                                @csrf
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">عنوان پروژه</label>
                                            <input class="form-control" name="title" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">کارفرما / مشتری</label>
                                            @include('partials.forms.erp-customer-select', [
                                                'placeholder' => 'انتخاب نشده',
                                                'class' => 'form-select erp-remote-select',
                                            ])
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">شماره قرارداد</label>
                                            <input class="form-control" name="contract_number">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">نوع قرارداد</label>
                                            <select class="form-select" name="contract_type" required>
                                                <option value="construction">اجرایی / عمرانی</option>
                                                <option value="service">خدماتی</option>
                                                <option value="maintenance">نگهداری</option>
                                                <option value="supply">تامین کالا</option>
                                                <option value="other">سایر</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">وضعیت</label>
                                            <select class="form-select" name="status" required>
                                                <option value="active">فعال</option>
                                                <option value="draft">پیش نویس</option>
                                                <option value="suspended">تعلیق</option>
                                                <option value="closed">خاتمه یافته</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">شروع</label>
                                            <input class="form-control" name="start_date_en" type="date"
                                                value="{{ $today }}">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">پایان</label>
                                            <input class="form-control" name="end_date_en" type="date">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">مبلغ قرارداد</label>
                                            <input class="form-control" name="contract_amount" type="number"
                                                min="0" step="0.01" value="0">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">بودجه مصوب</label>
                                            <input class="form-control" name="approved_budget" type="number"
                                                min="0" step="0.01" value="0">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">سپرده %</label>
                                            <input class="form-control" name="retention_percent" type="number"
                                                min="0" max="100" step="0.01" value="10">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">کسر پیش دریافت %</label>
                                            <input class="form-control" name="advance_payment_percent" type="number"
                                                min="0" max="100" step="0.01" value="0">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">ارزش افزوده %</label>
                                            <input class="form-control" name="vat_percent" type="number"
                                                min="0" max="100" step="0.01" value="0">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">مدیر پروژه</label>
                                            <select class="form-select" name="project_manager_id">
                                                <option value="">انتخاب نشده</option>
                                                @foreach ($managers as $manager)
                                                    <option value="{{ $manager->id }}">
                                                        {{ $manager->name ?: $manager->username }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">مرکز هزینه</label>
                                            <select class="form-select" name="cost_center_id">
                                                <option value="">انتخاب نشده</option>
                                                @foreach ($costCenters as $costCenter)
                                                    <option value="{{ $costCenter->id }}">{{ $costCenter->code }} -
                                                        {{ $costCenter->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">شرح قرارداد</label>
                                            <input class="form-control" name="description">
                                        </div>
                                        <div class="col-12">
                                            <details>
                                                <summary class="mb-3">حساب های پیش فرض قرارداد</summary>
                                                <div class="row g-3">
                                                    @foreach ([
        'receivable_account_id' => 'دریافتنی صورت وضعیت',
        'revenue_account_id' => 'درآمد پیمانکاری',
        'advance_account_id' => 'پیش دریافت',
        'retention_account_id' => 'سپرده دریافتنی',
        'tax_account_id' => 'مالیات فروش',
        'cost_account_id' => 'بهای تمام شده پروژه',
        'payable_account_id' => 'پرداختنی پروژه',
    ] as $field => $label)
                                                        <div class="col-md-3">
                                                            <label class="form-label">{{ $label }}</label>
                                                            <select class="form-select" name="{{ $field }}">
                                                                <option value="">سیستمی</option>
                                                                @foreach ($accounts as $account)
                                                                    <option value="{{ $account->id }}">
                                                                        {{ $account->code }} - {{ $account->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </details>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">ردیف های فهرست بها</label>
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle">
                                                    <thead>
                                                        <tr>
                                                            <th>کد</th>
                                                            <th>شرح ردیف</th>
                                                            <th>واحد</th>
                                                            <th>مقدار</th>
                                                            <th>نرخ</th>
                                                            <th>توضیح</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @for ($i = 0; $i < 6; $i++)
                                                            <tr>
                                                                <td><input class="form-control form-control-sm"
                                                                        name="items[{{ $i }}][item_code]">
                                                                </td>
                                                                <td><input class="form-control form-control-sm"
                                                                        name="items[{{ $i }}][title]"
                                                                        @if ($i === 0) required @endif>
                                                                </td>
                                                                <td><input class="form-control form-control-sm"
                                                                        name="items[{{ $i }}][unit]"
                                                                        value="عدد"></td>
                                                                <td><input class="form-control form-control-sm"
                                                                        name="items[{{ $i }}][quantity]"
                                                                        type="number" min="0" step="0.0001"
                                                                        value="{{ $i === 0 ? 1 : 0 }}"></td>
                                                                <td><input class="form-control form-control-sm"
                                                                        name="items[{{ $i }}][unit_price]"
                                                                        type="number" min="0" step="0.01"
                                                                        value="0"></td>
                                                                <td><input class="form-control form-control-sm"
                                                                        name="items[{{ $i }}][description]">
                                                                </td>
                                                            </tr>
                                                        @endfor
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-end">
                                    <button class="btn btn-primary" type="submit">ثبت قرارداد پروژه</button>
                                </div>
                            </form>
                        </div>

                        @forelse ($projects as $project)
                            @php
                                $statementTotal = $project->progressStatements->sum('current_amount');
                                $costTotal = $project->costEntries->sum('total_amount');
                                $profit = $statementTotal - $costTotal;
                            @endphp
                            <div class="card mb-4">
                                <div class="card-header d-flex flex-wrap justify-content-between gap-2">
                                    <div>
                                        <h5 class="mb-1">{{ $project->project_code }} - {{ $project->title }}</h5>
                                        <small
                                            class="text-muted">{{ optional($project->customer)->name ?: 'بدون کارفرما' }}
                                            | {{ $project->contract_number ?: 'بدون شماره قرارداد' }} |
                                            {{ $statusLabels[$project->status] ?? $project->status }}</small>
                                    </div>
                                    <div class="text-end">
                                        <div>مبلغ قرارداد:
                                            <strong>{{ number_format($project->contract_amount) }}</strong></div>
                                        <div>سود/زیان پروژه: <strong
                                                class="{{ $profit < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($profit) }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-3">
                                            <div class="border rounded p-2"><small class="text-muted">صورت وضعیت
                                                    جاری</small>
                                                <div class="fw-bold">{{ number_format($statementTotal) }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-2"><small class="text-muted">هزینه ثبت
                                                    شده</small>
                                                <div class="fw-bold">{{ number_format($costTotal) }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-2"><small class="text-muted">سپرده نگهداری
                                                    شده</small>
                                                <div class="fw-bold">
                                                    {{ number_format($project->progressStatements->sum('retention_amount')) }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="border rounded p-2"><small class="text-muted">ضمانت نامه
                                                    فعال</small>
                                                <div class="fw-bold">
                                                    {{ number_format($project->guarantees->where('status', 'active')->sum('amount')) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-lg-7">
                                            <div class="border rounded p-3 h-100">
                                                <h6>ثبت صورت وضعیت</h6>
                                                <form method="POST"
                                                    action="{{ route('contracting.progressStatements.store', $project) }}">
                                                    @csrf
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-md-3"><input class="form-control"
                                                                name="statement_date_en" type="date"
                                                                value="{{ $today }}"></div>
                                                        <div class="col-md-3"><input class="form-control"
                                                                name="period_from_en" type="date"
                                                                placeholder="از تاریخ"></div>
                                                        <div class="col-md-3"><input class="form-control"
                                                                name="period_to_en" type="date"
                                                                placeholder="تا تاریخ"></div>
                                                        <div class="col-md-3">
                                                            <select class="form-select" name="status">
                                                                <option value="posted">ثبت شده</option>
                                                                <option value="draft">پیش نویس</option>
                                                                <option value="approved">تایید شده</option>
                                                                <option value="paid">تسویه شده</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-4"><input class="form-control"
                                                                name="retention_amount" type="number" min="0"
                                                                step="0.01"
                                                                placeholder="سپرده دستی / خالی = درصد قرارداد"></div>
                                                        <div class="col-md-4"><input class="form-control"
                                                                name="advance_deduction_amount" type="number"
                                                                min="0" step="0.01"
                                                                placeholder="کسر پیش دریافت"></div>
                                                        <div class="col-md-4"><input class="form-control"
                                                                name="tax_amount" type="number" min="0"
                                                                step="0.01"
                                                                placeholder="مالیات دستی / خالی = درصد قرارداد"></div>
                                                        <div class="col-12"><input class="form-control"
                                                                name="description" placeholder="شرح صورت وضعیت"></div>
                                                    </div>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm align-middle">
                                                            <thead>
                                                                <tr>
                                                                    <th>ردیف</th>
                                                                    <th>قراردادی</th>
                                                                    <th>اجرا شده قبلی</th>
                                                                    <th>مقدار این دوره</th>
                                                                    <th>نرخ</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($project->items as $item)
                                                                    <tr>
                                                                        <td>{{ $item->item_code ?: $item->id }} -
                                                                            {{ $item->title }}</td>
                                                                        <td>{{ number_format($item->quantity, 4) }}
                                                                            {{ $item->unit }}</td>
                                                                        <td>{{ number_format($item->executed_quantity, 4) }}
                                                                        </td>
                                                                        <td>
                                                                            <input
                                                                                name="items[{{ $item->id }}][contracting_project_item_id]"
                                                                                type="hidden"
                                                                                value="{{ $item->id }}">
                                                                            <input class="form-control form-control-sm"
                                                                                name="items[{{ $item->id }}][quantity]"
                                                                                type="number" min="0"
                                                                                step="0.0001" value="0">
                                                                        </td>
                                                                        <td>{{ number_format($item->unit_price) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="text-end"><button
                                                            class="btn btn-outline-primary btn-sm" type="submit">ثبت
                                                            صورت وضعیت و سند</button></div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="col-lg-5">
                                            <div class="border rounded p-3 mb-3">
                                                <h6>ضمانت نامه پروژه</h6>
                                                <form method="POST"
                                                    action="{{ route('contracting.guarantees.store', $project) }}">
                                                    @csrf
                                                    <div class="row g-2">
                                                        <div class="col-md-6">
                                                            <select class="form-select" name="guarantee_type"
                                                                required>
                                                                @foreach ($guaranteeLabels as $key => $label)
                                                                    <option value="{{ $key }}">
                                                                        {{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6"><input class="form-control"
                                                                name="amount" type="number" min="1"
                                                                step="0.01" placeholder="مبلغ" required></div>
                                                        <div class="col-md-6"><input class="form-control"
                                                                name="issuer" placeholder="بانک/صادرکننده"></div>
                                                        <div class="col-md-6"><input class="form-control"
                                                                name="beneficiary" placeholder="ذینفع"></div>
                                                        <div class="col-md-6"><input class="form-control"
                                                                name="issue_date_en" type="date"
                                                                value="{{ $today }}"></div>
                                                        <div class="col-md-6"><input class="form-control"
                                                                name="expiry_date_en" type="date"></div>
                                                        <div class="col-md-6">
                                                            <select class="form-select" name="status" required>
                                                                <option value="active">فعال</option>
                                                                <option value="released">آزاد شده</option>
                                                                <option value="expired">منقضی</option>
                                                                <option value="confiscated">ضبط شده</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6"><input class="form-control"
                                                                name="description" placeholder="شرح"></div>
                                                        <div class="col-12 text-end"><button
                                                                class="btn btn-outline-secondary btn-sm"
                                                                type="submit">ثبت ضمانت و سند انتظامی</button></div>
                                                    </div>
                                                </form>
                                            </div>
                                            <div class="border rounded p-3">
                                                <h6>هزینه مستقیم پروژه</h6>
                                                <form method="POST"
                                                    action="{{ route('contracting.costs.store', $project) }}">
                                                    @csrf
                                                    <div class="row g-2">
                                                        <div class="col-md-6">
                                                            <select class="form-select" name="cost_type" required>
                                                                @foreach ($costTypeLabels as $key => $label)
                                                                    <option value="{{ $key }}">
                                                                        {{ $label }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6"><input class="form-control"
                                                                name="cost_date_en" type="date"
                                                                value="{{ $today }}"></div>
                                                        <div class="col-md-6"><input class="form-control"
                                                                name="amount" type="number" min="1"
                                                                step="0.01" placeholder="مبلغ هزینه" required>
                                                        </div>
                                                        <div class="col-md-6"><input class="form-control"
                                                                name="tax_amount" type="number" min="0"
                                                                step="0.01" placeholder="مالیات" value="0">
                                                        </div>
                                                        <div class="col-12">
                                                            <select class="form-select" name="supplier_id">
                                                                <option value="">بدون تامین کننده</option>
                                                                @foreach ($suppliers as $supplier)
                                                                    <option value="{{ $supplier->id }}">
                                                                        {{ $supplier->title ?: $supplier->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-12"><input class="form-control"
                                                                name="description" placeholder="شرح هزینه"></div>
                                                        <input name="status" type="hidden" value="posted">
                                                        <div class="col-12 text-end"><button
                                                                class="btn btn-outline-danger btn-sm"
                                                                type="submit">ثبت هزینه و سند</button></div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-4 mt-1">
                                        <div class="col-lg-4">
                                            <h6>صورت وضعیت ها</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>شماره</th>
                                                            <th>جاری</th>
                                                            <th>پرداختنی</th>
                                                            <th>سند</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($project->progressStatements as $statement)
                                                            <tr>
                                                                <td>{{ $statement->statement_number }}</td>
                                                                <td>{{ number_format($statement->current_amount) }}
                                                                </td>
                                                                <td>{{ number_format($statement->payable_amount) }}
                                                                </td>
                                                                <td>{{ optional($statement->voucher)->voucher_number }}
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td class="text-muted" colspan="4">صورت وضعیتی ثبت
                                                                    نشده است.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <h6>ضمانت نامه ها</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>شماره</th>
                                                            <th>نوع</th>
                                                            <th>مبلغ</th>
                                                            <th>وضعیت</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($project->guarantees as $guarantee)
                                                            <tr>
                                                                <td>{{ $guarantee->guarantee_number }}</td>
                                                                <td>{{ $guaranteeLabels[$guarantee->guarantee_type] ?? $guarantee->guarantee_type }}
                                                                </td>
                                                                <td>{{ number_format($guarantee->amount) }}</td>
                                                                <td>{{ $guarantee->status }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td class="text-muted" colspan="4">ضمانتی ثبت نشده
                                                                    است.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <h6>هزینه ها</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>شماره</th>
                                                            <th>نوع</th>
                                                            <th>مبلغ</th>
                                                            <th>سند</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($project->costEntries as $costEntry)
                                                            <tr>
                                                                <td>{{ $costEntry->cost_number }}</td>
                                                                <td>{{ $costTypeLabels[$costEntry->cost_type] ?? $costEntry->cost_type }}
                                                                </td>
                                                                <td>{{ number_format($costEntry->total_amount) }}</td>
                                                                <td>{{ optional($costEntry->voucher)->voucher_number }}
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td class="text-muted" colspan="4">هزینه ای ثبت
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
                        @empty
                            <div class="alert alert-info">هنوز پروژه پیمانکاری ثبت نشده است. اولین قرارداد را از فرم
                                بالا وارد کنید.</div>
                        @endforelse

                        {{ $projects->links() }}
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
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    @include('partials.erp-remote-select-assets')
</body>

</html>
