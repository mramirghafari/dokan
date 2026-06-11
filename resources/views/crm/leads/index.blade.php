<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>سرنخ های CRM - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="{{ asset('assets/') }}/vendor/fonts/fontawesome.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/fonts/tabler-icons.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" />
    <script src="{{ asset('assets/') }}/vendor/js/helpers.js"></script>
    <script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        .lead-form-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .lead-badge-stack {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        @media (max-width: 768px) {
            .lead-form-grid {
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
                        @include('crm.partials.hub_bar', ['hubActive' => 'leads'])
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">CRM /</span> سرنخ‌های CRM</h4>
                                <div class="text-muted">ثبت دستی، import اکسل با dedupe، تبدیل به مشتری و فرصت.</div>
                            </div>
                            <div class="d-flex gap-2">
                                <a class="btn btn-label-secondary" href="{{ route('crm.leads.import.template') }}"><i class="ti ti-download me-1"></i>قالب CSV</a>
                                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#leadImportModal"><i class="ti ti-upload me-1"></i>Import اکسل</button>
                            </div>
                        </div>

                        @if(isset($recentImports) && $recentImports->isNotEmpty())
                            <div class="alert alert-info py-2 mb-4">
                                آخرین import:
                                @foreach($recentImports as $import)
                                    <span class="badge bg-label-{{ $import->status === 'completed' ? 'success' : ($import->status === 'processing' ? 'info' : 'warning') }} me-1">#{{ $import->id }} {{ $import->status }} ({{ $import->success_rows }}/{{ $import->total_rows }})</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="row g-4 mb-4">
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>سرنخ باز</span>
                                        <h3 class="mt-2 mb-0">{{ number_format($stats['open']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>تبدیل شده</span>
                                        <h3 class="mt-2 mb-0 text-success">{{ number_format($stats['converted']) }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>تکراری</span>
                                        <h3 class="mt-2 mb-0 text-warning">{{ number_format($stats['duplicate']) }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>رد شده</span>
                                        <h3 class="mt-2 mb-0 text-danger">{{ number_format($stats['rejected']) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-12 col-xl-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">ثبت سرنخ جدید</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('crm.leads.store') }}">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">نام سرنخ</label>
                                                <input class="form-control" name="name" required maxlength="180"
                                                    placeholder="نام شخص یا مخاطب">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">نام شرکت / فروشگاه</label>
                                                <input class="form-control" name="company_name" maxlength="180"
                                                    placeholder="اختیاری">
                                            </div>
                                            <div class="lead-form-grid">
                                                <div><label class="form-label">موبایل</label><input class="form-control"
                                                        name="mobile" maxlength="30"></div>
                                                <div><label class="form-label">تلفن</label><input class="form-control"
                                                        name="phone" maxlength="30"></div>
                                                <div><label class="form-label">ایمیل</label><input class="form-control"
                                                        type="email" name="email" maxlength="160"></div>
                                                <div><label class="form-label">شهر</label><input class="form-control"
                                                        name="city" maxlength="120"></div>
                                                <div><label class="form-label">منبع جذب</label><select
                                                        class="form-select" name="source" required>
                                                        @foreach ($sources as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select></div>
                                                <div><label class="form-label">کمپین</label><input
                                                        class="form-control" name="campaign" maxlength="160"></div>
                                                <div><label class="form-label">امتیاز</label><input
                                                        class="form-control" type="number" name="score"
                                                        min="0" max="100" value="20"></div>
                                                <div><label class="form-label">اولویت</label><select
                                                        class="form-select" name="priority" required>
                                                        @foreach ($priorities as $key => $label)
                                                            <option value="{{ $key }}"
                                                                @selected($key === 'normal')>{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select></div>
                                            </div>
                                            <div class="mt-3 mb-3">
                                                <label class="form-label">مالک سرنخ</label>
                                                <select class="form-select select2" name="owner_user_id">
                                                    <option value="">کاربر فعلی</option>
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3"><label class="form-label">یادداشت</label>
                                                <textarea class="form-control" name="notes" rows="3"></textarea>
                                            </div>
                                            <button class="btn btn-primary w-100" type="submit">ثبت سرنخ</button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-8">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <form method="GET" class="row g-2 align-items-end">
                                            <div class="col-md-3"><label class="form-label">جستجو</label><input
                                                    class="form-control" name="search"
                                                    value="{{ $filters['search'] ?? '' }}"
                                                    placeholder="نام، موبایل، کمپین"></div>
                                            <div class="col-md-2"><label class="form-label">مرحله</label><select
                                                    class="form-select" name="stage">
                                                    <option value="">همه</option>
                                                    @foreach ($stages as $key => $label)
                                                        <option value="{{ $key }}"
                                                            @selected(($filters['stage'] ?? '') === $key)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2"><label class="form-label">وضعیت</label><select
                                                    class="form-select" name="status">
                                                    <option value="">همه</option>
                                                    @foreach ($statuses as $key => $label)
                                                        <option value="{{ $key }}"
                                                            @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2"><label class="form-label">منبع</label><select
                                                    class="form-select" name="source">
                                                    <option value="">همه</option>
                                                    @foreach ($sources as $key => $label)
                                                        <option value="{{ $key }}"
                                                            @selected(($filters['source'] ?? '') === $key)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3"><button class="btn btn-outline-primary w-100"
                                                    type="submit">فیلتر</button></div>
                                        </form>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="table-responsive text-nowrap">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>سرنخ</th>
                                                    <th>منبع / کمپین</th>
                                                    <th>مالک</th>
                                                    <th>وضعیت</th>
                                                    <th>تبدیل</th>
                                                    <th>عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($leads as $lead)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $lead->name }}</strong>
                                                            <div class="text-muted small">{{ $lead->code }} /
                                                                {{ $lead->mobile ?: '-' }}</div>
                                                            @if ($lead->company_name)
                                                                <div class="text-muted small">
                                                                    {{ $lead->company_name }}</div>
                                                            @endif
                                                        </td>
                                                        <td>{{ $lead->sourceText() }}<div class="text-muted small">
                                                                {{ $lead->campaign ?: '-' }}</div>
                                                        </td>
                                                        <td>{{ optional($lead->owner)->name ?: '-' }}<div
                                                                class="text-muted small">امتیاز: {{ $lead->score }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="lead-badge-stack">
                                                                <span
                                                                    class="badge bg-label-{{ $lead->status === 'converted' ? 'success' : ($lead->status === 'rejected' ? 'danger' : ($lead->status === 'duplicate' ? 'warning' : 'primary')) }}">{{ $lead->statusText() }}</span>
                                                                <span
                                                                    class="badge bg-label-secondary">{{ $lead->stageText() }}</span>
                                                                <span
                                                                    class="badge bg-label-info">{{ $lead->priorityText() }}</span>
                                                            </div>
                                                            @if ($lead->duplicate_status !== 'none')
                                                                <div class="text-warning small mt-1">تکراری با
                                                                    {{ optional($lead->duplicateCustomer)->name ?: optional($lead->duplicateLead)->name ?: 'رکورد قبلی' }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($lead->customer)
                                                                <div class="small">مشتری: {{ $lead->customer->name }}
                                                                </div>
                                                            @endif
                                                            @if ($lead->opportunity)
                                                                <div class="small">فرصت:
                                                                    {{ $lead->opportunity->code }}</div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (!in_array($lead->status, ['converted', 'rejected'], true))
                                                                <button class="btn btn-sm btn-success" type="button"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#convertLead{{ $lead->id }}">تبدیل</button>
                                                                <button class="btn btn-sm btn-label-danger"
                                                                    type="button" data-bs-toggle="modal"
                                                                    data-bs-target="#rejectLead{{ $lead->id }}">رد</button>
                                                            @else
                                                                <span class="text-muted small">بسته شده</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">سرنخی
                                                            با این فیلتر پیدا نشد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">{{ $leads->links() }}</div>
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

    @foreach ($leads as $lead)
        @if (!in_array($lead->status, ['converted', 'rejected'], true))
            <div class="modal fade" id="convertLead{{ $lead->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <form class="modal-content" method="POST" action="{{ route('crm.leads.convert', $lead) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">تبدیل سرنخ {{ $lead->name }}</h5><button type="button"
                                class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">اتصال به مشتری موجود</label>
                                    @include('partials.forms.erp-customer-select', [
                                        'name' => 'customer_id',
                                        'value' => $lead->duplicate_customer_id,
                                        'class' => 'form-select select2-modal erp-remote-select',
                                        'placeholder' => 'ساخت مشتری جدید از سرنخ',
                                    ])
                                </div>
                                <div class="col-md-6"><label class="form-label">عنوان فرصت</label><input
                                        class="form-control" name="opportunity_title"
                                        value="فرصت فروش {{ $lead->name }}" maxlength="180"></div>
                                <div class="col-md-4"><label class="form-label">مبلغ احتمالی</label><input
                                        class="form-control" type="number" name="amount" min="0"
                                        step="1000" value="0"></div>
                                <div class="col-md-4"><label class="form-label">احتمال موفقیت</label><input
                                        class="form-control" type="number" name="probability_percent"
                                        min="0" max="100" value="{{ max(20, min(90, $lead->score)) }}">
                                </div>
                                <div class="col-md-4"><label class="form-label">اقدام بعدی</label><input
                                        class="form-control" type="date" name="next_action_date_en"
                                        value="{{ now()->toDateString() }}"></div>
                                <div class="col-md-6"><label class="form-label">تاریخ بستن احتمالی</label><input
                                        class="form-control" type="date" name="expected_close_date_en"></div>
                                <div class="col-md-6 d-flex align-items-end"><label
                                        class="switch switch-primary"><input class="switch-input" type="checkbox"
                                            name="create_opportunity" value="1" checked><span
                                            class="switch-toggle-slider"><span class="switch-on"></span><span
                                                class="switch-off"></span></span><span class="switch-label">همزمان
                                            فرصت فروش بساز</span></label></div>
                            </div>
                        </div>
                        <div class="modal-footer"><button class="btn btn-label-secondary" type="button"
                                data-bs-dismiss="modal">انصراف</button><button class="btn btn-success"
                                type="submit">تبدیل</button></div>
                    </form>
                </div>
            </div>
            <div class="modal fade" id="rejectLead{{ $lead->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form class="modal-content" method="POST" action="{{ route('crm.leads.reject', $lead) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">رد سرنخ</h5><button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body"><label class="form-label">دلیل رد</label>
                            <textarea class="form-control" name="reject_reason" required rows="4"
                                placeholder="مثلا شماره نامعتبر، خارج از بازار هدف، عدم نیاز"></textarea>
                        </div>
                        <div class="modal-footer"><button class="btn btn-label-secondary" type="button"
                                data-bs-dismiss="modal">انصراف</button><button class="btn btn-danger"
                                type="submit">ثبت رد</button></div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach

    <div class="modal fade" id="leadImportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="POST" action="{{ route('crm.leads.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Import سرنخ از CSV</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">ستون‌ها: name, mobile, source, campaign, score, priority — dedupe بر اساس موبایل.</p>
                    <div class="mb-3">
                        <label class="form-label">فایل CSV</label>
                        <input type="file" name="file" class="form-control" accept=".csv,.txt" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">منبع پیش‌فرض</label>
                            <select name="default_source" class="form-select">
                                @foreach($sources as $key => $label)
                                    <option value="{{ $key }}" @selected($key === 'campaign')>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">نام کمپین</label>
                            <input class="form-control" name="default_campaign" placeholder="اختیاری">
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="update_existing" value="1" id="leadUpdateExisting">
                        <label class="form-check-label" for="leadUpdateExisting">بروزرسانی سرنخ تکراری (موبایل یکسان)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">شروع Import (queue)</button>
                </div>
            </form>
        </div>
    </div>

    <script src="{{ asset('assets/') }}/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/node-waves/node-waves.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/hammer/hammer.js"></script>
    <script src="{{ asset('assets/') }}/vendor/libs/select2/select2.js"></script>
    <script src="{{ asset('assets/') }}/vendor/js/menu.js"></script>
    <script src="{{ asset('assets/') }}/js/main.js"></script>
    @include('partials.erp-remote-select-assets')
    <script>
        $(function() {
            $('.select2:not(.erp-remote-select)').select2({
                dir: 'rtl',
                width: '100%'
            });
            $('.modal').on('shown.bs.modal', function() {
                const $modal = $(this);
                window.ErpRemoteSelect && window.ErpRemoteSelect.init($modal[0]);
                $modal.find('.select2-modal:not(.erp-remote-select)').each(function() {
                    if ($(this).data('select2')) return;
                    $(this).select2({
                        dir: 'rtl',
                        width: '100%',
                        dropdownParent: $modal
                    });
                });
            });
        });
    </script>
</body>

</html>
