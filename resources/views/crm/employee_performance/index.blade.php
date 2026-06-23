<!DOCTYPE html>
<html lang="fa" class="light-style layout-menu-fixed" dir="rtl" data-theme="theme-default"
    data-assets-path="../../assets/" data-template="vertical-menu-template-free">

<head>
    @include('sections.head')
    <title>عملکرد و coaching کارمندان - دکان دارمینو</title>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.navbar')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @php($filters = $state['filters'])
                        @php($summary = $state['summary'])
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">CRM /</span> عملکرد و coaching
                                    کارمندان</h4>
                                <div class="text-muted">امتیاز فروش، پشتیبانی، پیگیری و تماس از داده عملیاتی CRM محاسبه
                                    می شود.</div>
                            </div>
                            <form method="POST" action="{{ route('crm.employee-performance.refresh') }}"
                                class="d-flex gap-2">
                                @csrf
                                <input type="hidden" name="period_start" value="{{ $filters['period_start'] }}">
                                <input type="hidden" name="period_end" value="{{ $filters['period_end'] }}">
                                <input type="hidden" name="role_scope" value="{{ $filters['role_scope'] }}">
                                <input type="hidden" name="user_id" value="{{ $filters['user_id'] }}">
                                <button class="btn btn-primary" type="submit">محاسبه و ذخیره snapshot</button>
                            </form>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form class="row g-3 align-items-end" method="GET"
                                    action="{{ route('crm.employee-performance.index') }}">
                                    <div class="col-md-3">
                                        <label class="form-label">از تاریخ میلادی</label>
                                        <input class="form-control" type="date" name="period_start"
                                            value="{{ $filters['period_start'] }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">تا تاریخ میلادی</label>
                                        <input class="form-control" type="date" name="period_end"
                                            value="{{ $filters['period_end'] }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">نوع امتیازدهی</label>
                                        <select class="form-select" name="role_scope">
                                            @foreach ($state['role_scopes'] as $key => $label)
                                                <option value="{{ $key }}" @selected($filters['role_scope'] === $key)>
                                                    {{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">کارمند/کاربر</label>
                                        <select class="form-select" name="user_id">
                                            <option value="">همه کاربران فعال</option>
                                            @foreach ($state['users'] as $user)
                                                <option value="{{ $user->id }}" @selected((int) $filters['user_id'] === (int) $user->id)>
                                                    {{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 text-end">
                                        <button class="btn btn-label-primary" type="submit">اعمال فیلتر</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">کاربر بررسی شده</div>
                                        <h3 class="mb-0">{{ number_format($summary['users']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">میانگین امتیاز</div>
                                        <h3 class="mb-0">{{ $summary['average_score'] }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">coaching فوری</div>
                                        <h3 class="mb-0 text-danger">{{ number_format($summary['high_priority']) }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">نیازمند توجه</div>
                                        <h3 class="mb-0 text-warning">{{ number_format($summary['medium_priority']) }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">بهترین امتیاز</div>
                                        <h3 class="mb-0">{{ $summary['best_score'] }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">کمترین امتیاز</div>
                                        <h3 class="mb-0">{{ $summary['lowest_score'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-xl-8">
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">Scorecard زنده بازه انتخابی</h5>
                                        <small class="text-muted">مرتب شده بر اساس نیاز coaching</small>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>کارمند</th>
                                                    <th>کل</th>
                                                    <th>فروش</th>
                                                    <th>پشتیبانی</th>
                                                    <th>پیگیری</th>
                                                    <th>تماس</th>
                                                    <th>ریسک</th>
                                                    <th>coaching</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($state['rows'] as $row)
                                                    @php($metrics = $row['metrics'])
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $row['user']->name }}</strong>
                                                            <div class="text-muted small">{{ $row['recommendation'] }}
                                                            </div>
                                                        </td>
                                                        <td><span
                                                                class="badge bg-label-{{ $row['total_score'] < 55 ? 'danger' : ($row['total_score'] < 70 ? 'warning' : 'success') }}">{{ $row['total_score'] }}</span>
                                                        </td>
                                                        <td>{{ $row['sales_score'] }}</td>
                                                        <td>{{ $row['support_score'] }}</td>
                                                        <td>{{ $row['followup_score'] }}</td>
                                                        <td>{{ $row['call_score'] }}</td>
                                                        <td>
                                                            <div>پیگیری معوق:
                                                                {{ number_format($metrics['followups_overdue']) }}
                                                            </div>
                                                            <div>تیکت معوق:
                                                                {{ number_format($metrics['tickets_overdue']) }}</div>
                                                            <div>missed call:
                                                                {{ number_format($metrics['calls_missed']) }}</div>
                                                        </td>
                                                        <td>
                                                            <form method="POST"
                                                                action="{{ route('crm.employee-performance.coaching.store') }}"
                                                                class="d-flex flex-column gap-2">
                                                                @csrf
                                                                <input type="hidden" name="user_id"
                                                                    value="{{ $row['user_id'] }}">
                                                                <input type="hidden" name="type"
                                                                    value="{{ $row['sales_score'] < $row['support_score'] ? 'sales' : 'support' }}">
                                                                <input type="hidden" name="priority"
                                                                    value="{{ $row['coaching_priority'] === 'high' ? 'high' : 'medium' }}">
                                                                <input type="hidden" name="title"
                                                                    value="coaching عملکرد {{ $row['user']->name }}">
                                                                <input type="hidden" name="target_metric"
                                                                    value="total_score">
                                                                <input type="hidden" name="target_value"
                                                                    value="75">
                                                                <input type="hidden" name="action_plan"
                                                                    value="{{ $row['recommendation'] }}">
                                                                <button class="btn btn-sm btn-label-warning"
                                                                    type="submit">ساخت برنامه</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center text-muted py-4">کاربر
                                                            فعالی برای نمایش وجود ندارد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">آخرین snapshotهای ذخیره شده</h5>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>کارمند</th>
                                                    <th>بازه</th>
                                                    <th>امتیاز</th>
                                                    <th>اولویت</th>
                                                    <th>ریسک ها</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($state['snapshots'] as $snapshot)
                                                    <tr>
                                                        <td>{{ optional($snapshot->user)->name ?: 'کاربر حذف شده' }}
                                                        </td>
                                                        <td>{{ verta_date($snapshot->period_start) }} تا {{ verta_date($snapshot->period_end) }}</td>
                                                        <td>{{ $snapshot->total_score }}</td>
                                                        <td>{{ $snapshot->priorityText() }}</td>
                                                        <td>{{ implode(' | ', array_slice($snapshot->risks ?: [], 0, 2)) }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">هنوز
                                                            snapshot ذخیره نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">ثبت دستی برنامه coaching</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST"
                                            action="{{ route('crm.employee-performance.coaching.store') }}"
                                            class="row g-3">
                                            @csrf
                                            <div class="col-12">
                                                <label class="form-label">کارمند/کاربر</label>
                                                <select class="form-select" name="user_id" required>
                                                    @foreach ($state['users'] as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">نوع</label>
                                                <select class="form-select" name="type">
                                                    @foreach ($state['coaching_types'] as $key => $label)
                                                        <option value="{{ $key }}">{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">اولویت</label>
                                                <select class="form-select" name="priority">
                                                    @foreach ($state['coaching_priorities'] as $key => $label)
                                                        <option value="{{ $key }}">{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">عنوان</label>
                                                <input class="form-control" name="title" required maxlength="180">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">شاخص هدف</label>
                                                <input class="form-control" name="target_metric"
                                                    placeholder="مثلا total_score">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">مقدار هدف</label>
                                                <input class="form-control" name="target_value" type="number"
                                                    step="0.01">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">موعد</label>
                                                <input class="form-control" name="due_at" type="datetime-local">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">برنامه اقدام</label>
                                                <textarea class="form-control" name="action_plan" rows="3"></textarea>
                                            </div>
                                            <div class="col-12 text-end"><button class="btn btn-primary"
                                                    type="submit">ثبت coaching</button></div>
                                        </form>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">برنامه های coaching باز</h5>
                                    </div>
                                    <div class="card-body">
                                        @forelse ($state['coachings'] as $plan)
                                            <div class="border rounded p-3 mb-3">
                                                <div class="d-flex justify-content-between gap-2 mb-2">
                                                    <strong>{{ $plan->title }}</strong>
                                                    <span
                                                        class="badge bg-label-{{ $plan->priority === 'high' ? 'danger' : ($plan->priority === 'medium' ? 'warning' : 'secondary') }}">{{ $plan->priorityText() }}</span>
                                                </div>
                                                <div class="text-muted small mb-2">
                                                    {{ optional($plan->user)->name ?: 'کاربر حذف شده' }} -
                                                    {{ $plan->typeText() }}</div>
                                                <div class="mb-2">{{ $plan->action_plan }}</div>
                                                <form method="POST"
                                                    action="{{ route('crm.employee-performance.coaching.status', $plan) }}"
                                                    class="row g-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="col-12">
                                                        <select class="form-select form-select-sm" name="status">
                                                            @foreach ($state['coaching_statuses'] as $key => $label)
                                                                <option value="{{ $key }}"
                                                                    @selected($plan->status === $key)>{{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <textarea class="form-control form-control-sm" name="outcome" rows="2" placeholder="نتیجه coaching">{{ $plan->outcome }}</textarea>
                                                    </div>
                                                    <div class="col-12 text-end"><button
                                                            class="btn btn-sm btn-label-primary"
                                                            type="submit">بروزرسانی</button></div>
                                                </form>
                                            </div>
                                        @empty
                                            <div class="text-center text-muted py-4">برنامه coaching باز وجود ندارد.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('sections.footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    @include('sections.script')
</body>

</html>
