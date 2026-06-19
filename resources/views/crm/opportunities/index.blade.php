<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>Pipeline فرصت های فروش - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        .pipeline-stage-card {
            min-height: 128px;
            border-inline-start: 4px solid #7367f0;
        }

        .pipeline-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        @media (max-width: 768px) {
            .pipeline-form-grid {
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
                        <h4 class="py-3 mb-4"><span class="text-muted fw-light">CRM /</span> Pipeline فرصت های فروش</h4>

                        <div class="row g-4 mb-4">
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>فرصت باز</span>
                                        <h3 class="mt-2 mb-0">{{ number_format($stats['open_count']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>ارزش فرصت های باز</span>
                                        <h3 class="mt-2 mb-0">{{ number_format($stats['open_amount']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>ارزش وزنی</span>
                                        <h3 class="mt-2 mb-0 text-info">{{ number_format($stats['weighted_amount']) }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>اقدام معوق / برد ماه</span>
                                        <h3 class="mt-2 mb-0"><span
                                                class="text-danger">{{ number_format($stats['overdue']) }}</span> /
                                            <span class="text-success">{{ number_format($stats['won_month']) }}</span>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            @foreach ($stages as $stageKey => $stageLabel)
                                @php $summary = $stageSummaries->get($stageKey); @endphp
                                <div class="col-sm-6 col-xl-2">
                                    <div class="card pipeline-stage-card">
                                        <div class="card-body">
                                            <span class="fw-medium">{{ $stageLabel }}</span>
                                            <h5 class="mt-2 mb-1">{{ number_format(optional($summary)->count ?: 0) }}
                                                فرصت</h5>
                                            <small
                                                class="text-muted">{{ number_format(optional($summary)->amount ?: 0) }}
                                                ریال</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row g-4">
                            <div class="col-12 col-xl-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">ثبت فرصت فروش</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('crm.opportunities.store') }}">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">مشتری</label>
                                                @include('partials.forms.erp-customer-select', ['class' => 'form-select select2 erp-remote-select'])
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">عنوان فرصت</label>
                                                <input type="text" name="title" class="form-control"
                                                    maxlength="180" required
                                                    placeholder="مثلا تمدید قرارداد یا سفارش عمده">
                                            </div>
                                            <div class="pipeline-form-grid">
                                                <div>
                                                    <label class="form-label">مرحله</label>
                                                    <select name="stage" class="form-select" required>
                                                        @foreach ($stages as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label">اولویت</label>
                                                    <select name="priority" class="form-select" required>
                                                        @foreach ($priorities as $key => $label)
                                                            <option value="{{ $key }}"
                                                                @selected($key === 'normal')>{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label">مبلغ احتمالی</label>
                                                    <input type="number" min="0" step="1000" name="amount"
                                                        class="form-control" value="0">
                                                </div>
                                                <div>
                                                    <label class="form-label">احتمال موفقیت</label>
                                                    <input type="number" min="0" max="100"
                                                        name="probability_percent" class="form-control"
                                                        value="20" required>
                                                </div>
                                                <div>
                                                    <label class="form-label">تاریخ بستن احتمالی</label>
                                                    <input type="date" name="expected_close_date_en"
                                                        class="form-control">
                                                </div>
                                                <div>
                                                    <label class="form-label">اقدام بعدی</label>
                                                    <input type="date" name="next_action_date_en"
                                                        class="form-control" value="{{ now()->toDateString() }}">
                                                </div>
                                            </div>
                                            <div class="mt-3 mb-3">
                                                <label class="form-label">مسئول فروش</label>
                                                <select name="assigned_user_id" class="form-select select2">
                                                    <option value="">کاربر فعلی</option>
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">شرح</label>
                                                <textarea name="description" rows="3" class="form-control"
                                                    placeholder="نیاز مشتری، کالا/خدمت مورد نظر، شرط پرداخت یا نکته مذاکره"></textarea>
                                            </div>
                                            <button class="btn btn-primary w-100" type="submit">ثبت در
                                                pipeline</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-8">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <form method="GET" class="row g-2 align-items-end">
                                            <div class="col-md-3"><label class="form-label">جستجو</label><input
                                                    type="text" name="search"
                                                    value="{{ $filters['search'] ?? '' }}" class="form-control"
                                                    placeholder="کد، عنوان یا مشتری"></div>
                                            <div class="col-md-2"><label class="form-label">مرحله</label><select
                                                    name="stage" class="form-select">
                                                    <option value="">همه</option>
                                                    @foreach ($stages as $key => $label)
                                                        <option value="{{ $key }}"
                                                            @selected(($filters['stage'] ?? '') === $key)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2"><label class="form-label">وضعیت</label><select
                                                    name="status" class="form-select">
                                                    <option value="">همه</option>
                                                    @foreach ($statuses as $key => $label)
                                                        <option value="{{ $key }}"
                                                            @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2"><label class="form-label">اولویت</label><select
                                                    name="priority" class="form-select">
                                                    <option value="">همه</option>
                                                    @foreach ($priorities as $key => $label)
                                                        <option value="{{ $key }}"
                                                            @selected(($filters['priority'] ?? '') === $key)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3"><button type="submit"
                                                    class="btn btn-outline-primary w-100">فیلتر</button></div>
                                        </form>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="table-responsive text-nowrap">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>فرصت</th>
                                                    <th>مشتری</th>
                                                    <th>ارزش</th>
                                                    <th>مرحله</th>
                                                    <th>اقدام بعدی</th>
                                                    <th>بروزرسانی</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($opportunities as $opportunity)
                                                    <tr>
                                                        <td><strong>{{ $opportunity->title }}</strong><br><small
                                                                class="text-muted">{{ $opportunity->code }} -
                                                                {{ \Illuminate\Support\Str::limit($opportunity->description, 60) }}</small>
                                                        </td>
                                                        <td>{{ optional($opportunity->customer)->name ?: '-' }}<br><small
                                                                class="text-muted">{{ optional($opportunity->assignedUser)->name ?: '-' }}</small>
                                                        </td>
                                                        <td>{{ number_format($opportunity->amount) }}<br><small
                                                                class="text-muted">وزنی:
                                                                {{ number_format($opportunity->weightedAmount()) }} /
                                                                {{ $opportunity->probability_percent }}%</small></td>
                                                        <td><span
                                                                class="badge bg-label-{{ in_array($opportunity->status, ['won'], true) ? 'success' : (in_array($opportunity->status, ['lost', 'canceled'], true) ? 'danger' : 'primary') }}">{{ $opportunity->stageText() }}</span><br><small
                                                                class="text-muted">{{ $opportunity->priorityText() }}
                                                                - {{ $opportunity->statusText() }}</small></td>
                                                        <td>{{ optional($opportunity->next_action_date_en)->format('Y-m-d') ?: '-' }}<br><small
                                                                class="text-muted">{{ $opportunity->next_action_date_fa }}</small>
                                                        </td>
                                                        <td>
                                                            <form method="POST"
                                                                action="{{ route('crm.opportunities.stage', $opportunity) }}"
                                                                class="d-grid gap-2" style="min-width: 360px;">
                                                                @csrf
                                                                @method('PATCH')
                                                                <div class="row g-2">
                                                                    <div class="col-6"><select name="stage"
                                                                            class="form-select form-select-sm">
                                                                            @foreach ($stages as $key => $label)
                                                                                <option value="{{ $key }}"
                                                                                    @selected($opportunity->stage === $key)>
                                                                                    {{ $label }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-6"><select name="status"
                                                                            class="form-select form-select-sm">
                                                                            @foreach ($statuses as $key => $label)
                                                                                <option value="{{ $key }}"
                                                                                    @selected($opportunity->status === $key)>
                                                                                    {{ $label }}</option>
                                                                            @endforeach
                                                                        </select></div>
                                                                    <div class="col-6"><input type="number"
                                                                            min="0" max="100"
                                                                            name="probability_percent"
                                                                            value="{{ $opportunity->probability_percent }}"
                                                                            class="form-control form-control-sm"
                                                                            placeholder="درصد"></div>
                                                                    <div class="col-6"><input type="date"
                                                                            name="next_action_date_en"
                                                                            value="{{ optional($opportunity->next_action_date_en)->format('Y-m-d') }}"
                                                                            class="form-control form-control-sm"></div>
                                                                    <div class="col-6"><input type="text"
                                                                            name="outcome"
                                                                            value="{{ $opportunity->outcome }}"
                                                                            class="form-control form-control-sm"
                                                                            placeholder="نتیجه"></div>
                                                                    <div class="col-6"><input type="text"
                                                                            name="lost_reason"
                                                                            value="{{ $opportunity->lost_reason }}"
                                                                            class="form-control form-control-sm"
                                                                            placeholder="علت از دست رفتن"></div>
                                                                </div>
                                                                <button class="btn btn-sm btn-outline-primary"
                                                                    type="submit">ثبت تغییر</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">فرصت
                                                            فروشی ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">{{ $opportunities->links() }}</div>
                                </div>
                            </div>
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
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js">
</script>
<script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
<script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>

    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    @include('partials.erp-remote-select-assets')
    <script>
        $(function() {
            $('.select2:not(.erp-remote-select)').select2({
                dir: 'rtl',
                width: '100%'
            });
        });
    </script>
</body>

</html>
