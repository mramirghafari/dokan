<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>مرکز تماس CRM - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        .call-form-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .call-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        @media (max-width: 768px) {
            .call-form-grid {
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
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">CRM /</span> مرکز تماس</h4>
                                <div class="text-muted">ثبت تماس ورودی، خروجی و از دست رفته با نتیجه، کیفیت و پیگیری
                                    بعدی.</div>
                            </div>
                            <a class="btn btn-outline-primary" href="{{ route('crm.service-tickets.index') }}">خدمات پس
                                از فروش</a>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>تماس امروز</span>
                                        <h3 class="mt-2 mb-0">{{ number_format($stats['today']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>باز / نیازمند پیگیری</span>
                                        <h3 class="mt-2 mb-0 text-warning">{{ number_format($stats['open']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>از دست رفته امروز</span>
                                        <h3 class="mt-2 mb-0 text-danger">{{ number_format($stats['missed']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>میانگین کیفیت</span>
                                        <h3 class="mt-2 mb-0 text-info">{{ $stats['quality_avg'] ?: '-' }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-12 col-xl-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">ثبت تماس جدید</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('crm.call-center.store') }}">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">موضوع تماس</label>
                                                <input class="form-control" name="subject" required maxlength="180"
                                                    placeholder="مثلا تماس پیگیری شکایت یا معرفی محصول">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">مشتری</label>
                                                @include('partials.forms.erp-customer-select', [
                                                    'class' => 'form-select select2 erp-remote-select',
                                                    'placeholder' => 'بدون اتصال به مشتری',
                                                ])
                                            </div>
                                            <div class="call-form-grid">
                                                <div><label class="form-label">جهت تماس</label><select
                                                        class="form-select" name="direction" required>
                                                        @foreach ($directions as $key => $label)
                                                            <option value="{{ $key }}"
                                                                @selected($key === 'outbound')>{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select></div>
                                                <div><label class="form-label">کانال</label><select class="form-select"
                                                        name="channel" required>
                                                        @foreach ($channels as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select></div>
                                                <div><label class="form-label">اولویت</label><select class="form-select"
                                                        name="priority" required>
                                                        @foreach ($priorities as $key => $label)
                                                            <option value="{{ $key }}"
                                                                @selected($key === 'normal')>{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select></div>
                                                <div><label class="form-label">زمان تماس</label><input
                                                        class="form-control" type="datetime-local"
                                                        name="call_started_at"></div>
                                                <div><label class="form-label">نام مخاطب</label><input
                                                        class="form-control" name="contact_name" maxlength="180"></div>
                                                <div><label class="form-label">شماره تماس</label><input
                                                        class="form-control" name="phone_number" maxlength="40">
                                                </div>
                                            </div>
                                            <div class="mt-3 mb-3">
                                                <label class="form-label">تیکت مرتبط</label>
                                                <select class="form-select select2" name="service_ticket_id">
                                                    <option value="">بدون تیکت مرتبط</option>
                                                    @foreach ($tickets as $ticket)
                                                        <option value="{{ $ticket->id }}">{{ $ticket->code }} -
                                                            {{ $ticket->subject }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">اپراتور / مسئول</label>
                                                <select class="form-select select2" name="assigned_user_id">
                                                    <option value="">کاربر فعلی</option>
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3"><label class="form-label">اقدام بعدی</label><input
                                                    class="form-control" type="datetime-local" name="next_action_at">
                                            </div>
                                            <div class="mb-3"><label class="form-label">لینک ضبط
                                                    مکالمه</label><input class="form-control" name="recording_url"
                                                    maxlength="500"></div>
                                            <div class="mb-3"><label class="form-label">یادداشت تماس</label>
                                                <textarea class="form-control" name="notes" rows="3"></textarea>
                                            </div>
                                            <button class="btn btn-primary w-100" type="submit">ثبت تماس</button>
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
                                                    placeholder="کد، موضوع، شماره"></div>
                                            <div class="col-md-2"><label class="form-label">جهت</label><select
                                                    class="form-select" name="direction">
                                                    <option value="">همه</option>
                                                    @foreach ($directions as $key => $label)
                                                        <option value="{{ $key }}"
                                                            @selected(($filters['direction'] ?? '') === $key)>{{ $label }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-2"><label class="form-label">وضعیت</label><select
                                                    class="form-select" name="status">
                                                    <option value="">همه</option>
                                                    @foreach ($statuses as $key => $label)
                                                        <option value="{{ $key }}"
                                                            @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                                                    @endforeach
                                                </select></div>
                                            <div class="col-md-2"><label class="form-label">نتیجه</label><select
                                                    class="form-select" name="result">
                                                    <option value="">همه</option>
                                                    @foreach ($results as $key => $label)
                                                        <option value="{{ $key }}"
                                                            @selected(($filters['result'] ?? '') === $key)>{{ $label }}</option>
                                                    @endforeach
                                                </select></div>
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
                                                    <th>تماس</th>
                                                    <th>مشتری / شماره</th>
                                                    <th>اپراتور</th>
                                                    <th>زمان / مدت</th>
                                                    <th>وضعیت</th>
                                                    <th>عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($callLogs as $callLog)
                                                    <tr>
                                                        <td><strong>{{ $callLog->subject }}</strong>
                                                            <div class="text-muted small">{{ $callLog->code }} /
                                                                {{ $callLog->directionText() }}</div>
                                                            @if ($callLog->serviceTicket)
                                                                <div class="text-muted small">تیکت:
                                                                    {{ $callLog->serviceTicket->code }}</div>
                                                            @endif
                                                        </td>
                                                        <td>{{ optional($callLog->customer)->name ?: ($callLog->contact_name ?: '-') }}
                                                            <div class="text-muted small">
                                                                {{ $callLog->phone_number ?: '-' }}</div>
                                                        </td>
                                                        <td>{{ optional($callLog->assignedUser)->name ?: '-' }}
                                                            <div class="text-muted small">
                                                                {{ $callLog->channelText() }}</div>
                                                        </td>
                                                        <td>{{ optional($callLog->call_started_at)->format('Y-m-d H:i') ?: '-' }}
                                                            <div class="text-muted small">
                                                                {{ number_format($callLog->duration_seconds) }} ثانیه
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="call-badges">
                                                                <span
                                                                    class="badge bg-label-{{ $callLog->status === 'completed' ? 'success' : ($callLog->status === 'failed' ? 'danger' : ($callLog->status === 'needs_followup' ? 'warning' : 'primary')) }}">{{ $callLog->statusText() }}</span>
                                                                <span
                                                                    class="badge bg-label-info">{{ $callLog->priorityText() }}</span>
                                                            </div>
                                                            <div class="text-muted small mt-1">
                                                                {{ $callLog->resultText() }}</div>
                                                            @if ($callLog->quality_score)
                                                                <div class="text-muted small">کیفیت:
                                                                    {{ $callLog->quality_score }}/5</div>
                                                            @endif
                                                        </td>
                                                        <td><button class="btn btn-sm btn-outline-primary"
                                                                type="button" data-bs-toggle="modal"
                                                                data-bs-target="#callOutcome{{ $callLog->id }}">نتیجه</button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">تماسی
                                                            با این فیلتر پیدا نشد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">{{ $callLogs->links() }}</div>
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

    @foreach ($callLogs as $callLog)
        <div class="modal fade" id="callOutcome{{ $callLog->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST"
                    action="{{ route('crm.call-center.outcome', $callLog) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">ثبت نتیجه {{ $callLog->code }}</h5><button type="button"
                            class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">وضعیت</label><select class="form-select"
                                    name="status" required>
                                    @foreach ($statuses as $key => $label)
                                        <option value="{{ $key }}" @selected($callLog->status === $key)>
                                            {{ $label }}</option>
                                    @endforeach
                                </select></div>
                            <div class="col-md-6"><label class="form-label">نتیجه</label><select class="form-select"
                                    name="result">
                                    <option value="">ثبت نشده</option>
                                    @foreach ($results as $key => $label)
                                        <option value="{{ $key }}" @selected($callLog->result === $key)>
                                            {{ $label }}</option>
                                    @endforeach
                                </select></div>
                            <div class="col-md-6"><label class="form-label">زمان پایان</label><input
                                    class="form-control" type="datetime-local" name="call_ended_at"></div>
                            <div class="col-md-6"><label class="form-label">مدت تماس - ثانیه</label><input
                                    class="form-control" type="number" min="0" name="duration_seconds"
                                    value="{{ $callLog->duration_seconds }}"></div>
                            <div class="col-md-6"><label class="form-label">اقدام بعدی</label><input
                                    class="form-control" type="datetime-local" name="next_action_at"></div>
                            <div class="col-md-6"><label class="form-label">امتیاز کیفیت</label><input
                                    class="form-control" type="number" name="quality_score" min="1"
                                    max="5" value="{{ $callLog->quality_score }}"></div>
                        </div>
                        <div class="mt-3"><label class="form-label">خروجی تماس</label>
                            <textarea class="form-control" name="outcome" rows="4">{{ $callLog->outcome }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-label-secondary" type="button"
                            data-bs-dismiss="modal">انصراف</button><button class="btn btn-primary" type="submit">ثبت
                            نتیجه</button></div>
                </form>
            </div>
        </div>
    @endforeach

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
