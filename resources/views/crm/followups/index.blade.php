<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>CRM و پیگیری مشتری/کارمند - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
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
                        <h4 class="py-3 mb-4"><span class="text-muted fw-light">CRM /</span> پیگیری مشتری و کارمند</h4>

                        <div class="row g-4 mb-4">
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>پیگیری باز</span>
                                        <h3 class="mt-2 mb-0">{{ number_format($stats['open']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>معوق</span>
                                        <h3 class="mt-2 mb-0 text-danger">{{ number_format($stats['overdue']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>موعد امروز</span>
                                        <h3 class="mt-2 mb-0 text-warning">{{ number_format($stats['today']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>انجام شده ماه</span>
                                        <h3 class="mt-2 mb-0 text-success">{{ number_format($stats['done_month']) }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-12 col-xl-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">ثبت پیگیری جدید</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('crm.followups.store') }}">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">نوع پرونده</label>
                                                <select name="subject_type" class="form-select" required>
                                                    <option value="customer">مشتری</option>
                                                    <option value="employee">کارمند</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">مشتری</label>
                                                @include('partials.forms.erp-customer-select', ['class' => 'form-select select2 erp-remote-select'])
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">کارمند</label>
                                                <x-erp-remote-select
                                                    entity="employees"
                                                    name="employee_id"
                                                    placeholder="انتخاب کارمند"
                                                    class="form-select select2 erp-remote-select"
                                                    :filters="config('erp_scale.remote_lookup.employee_filters')"
                                                />
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">نوع پیگیری</label>
                                                    <select name="type" class="form-select" required>
                                                        @foreach ($types as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">اولویت</label>
                                                    <select name="priority" class="form-select" required>
                                                        @foreach ($priorities as $key => $label)
                                                            <option value="{{ $key }}"
                                                                @selected($key === 'normal')>{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">مسئول پیگیری</label>
                                                <select name="assigned_user_id" class="form-select select2">
                                                    <option value="">کاربر فعلی</option>
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">عنوان</label>
                                                <input type="text" name="title" class="form-control" required
                                                    maxlength="180"
                                                    placeholder="مثلا تماس پیگیری سفارش یا بررسی عملکرد کارمند">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">موعد</label>
                                                <input type="date" name="due_date_en" class="form-control"
                                                    value="{{ now()->toDateString() }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">شرح</label>
                                                <textarea name="description" rows="3" class="form-control"
                                                    placeholder="شرح تماس، جلسه، شکایت، فرصت فروش یا پیگیری کارمند"></textarea>
                                            </div>
                                            <button class="btn btn-primary w-100" type="submit">ثبت پیگیری</button>
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
                                                    placeholder="عنوان یا شرح"></div>
                                            <div class="col-md-2"><label class="form-label">پرونده</label><select
                                                    name="subject_type" class="form-select">
                                                    <option value="">همه</option>
                                                    <option value="customer" @selected(($filters['subject_type'] ?? '') === 'customer')>مشتری
                                                    </option>
                                                    <option value="employee" @selected(($filters['subject_type'] ?? '') === 'employee')>کارمند
                                                    </option>
                                                </select></div>
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
                                                </select></div>
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
                                                    <th>پرونده</th>
                                                    <th>عنوان</th>
                                                    <th>نوع</th>
                                                    <th>موعد</th>
                                                    <th>مسئول</th>
                                                    <th>وضعیت</th>
                                                    <th>نتیجه</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($followups as $followup)
                                                    <tr>
                                                        <td><strong>{{ $followup->subjectName() }}</strong><br><small
                                                                class="text-muted">{{ $followup->subject_type === 'employee' ? 'کارمند' : 'مشتری' }}</small>
                                                        </td>
                                                        <td>{{ $followup->title }}<br><small
                                                                class="text-muted">{{ \Illuminate\Support\Str::limit($followup->description, 70) }}</small>
                                                        </td>
                                                        <td>{{ $followup->typeText() }}<br><span
                                                                class="badge bg-label-{{ in_array($followup->priority, ['high', 'urgent'], true) ? 'danger' : 'secondary' }}">{{ $followup->priorityText() }}</span>
                                                        </td>
                                                        <td>{{ $followup->due_date_fa ?: verta_date($followup->due_date_en) }}</td>
                                                        <td>{{ optional($followup->assignedUser)->name ?: '-' }}</td>
                                                        <td>
                                                            <form method="POST"
                                                                action="{{ route('crm.followups.status', $followup) }}"
                                                                class="d-flex gap-2">
                                                                @csrf
                                                                @method('PATCH')
                                                                <select name="status"
                                                                    class="form-select form-select-sm">
                                                                    @foreach ($statuses as $key => $label)
                                                                        <option value="{{ $key }}"
                                                                            @selected($followup->status === $key)>
                                                                            {{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button class="btn btn-sm btn-outline-primary"
                                                                    type="submit">ثبت</button>
                                                            </form>
                                                        </td>
                                                        <td>
                                                            <form method="POST"
                                                                action="{{ route('crm.followups.status', $followup) }}"
                                                                class="d-flex gap-2">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status"
                                                                    value="{{ $followup->status }}">
                                                                <input type="text" name="outcome"
                                                                    value="{{ $followup->outcome }}"
                                                                    class="form-control form-control-sm"
                                                                    placeholder="نتیجه پیگیری">
                                                                <button class="btn btn-sm btn-outline-secondary"
                                                                    type="submit">ثبت</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-muted py-4">پیگیری
                                                            CRM ثبت نشده است.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">{{ $followups->links() }}</div>
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
