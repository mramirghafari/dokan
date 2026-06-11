<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <title>خدمات پس از فروش CRM - دکان دارمینو</title>
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
        .ticket-form-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ticket-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        @media (max-width: 768px) {
            .ticket-form-grid {
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
                                <h4 class="mb-1"><span class="text-muted fw-light">CRM /</span> خدمات پس از فروش</h4>
                                <div class="text-muted">کارتابل تیکت، شکایت، SLA و رضایت مشتری.</div>
                            </div>
                            <a class="btn btn-outline-primary" href="{{ route('crm.followups.index') }}">کارتابل
                                پیگیری</a>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>تیکت باز</span>
                                        <h3 class="mt-2 mb-0">{{ number_format($stats['open']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>تاخیر SLA</span>
                                        <h3 class="mt-2 mb-0 text-danger">{{ number_format($stats['overdue']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>حل شده این ماه</span>
                                        <h3 class="mt-2 mb-0 text-success">
                                            {{ number_format($stats['resolved_month']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-3">
                                <div class="card">
                                    <div class="card-body"><span>میانگین رضایت</span>
                                        <h3 class="mt-2 mb-0 text-info">{{ $stats['satisfaction_avg'] ?: '-' }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-12 col-xl-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">ثبت تیکت جدید</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('crm.service-tickets.store') }}">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label">مشتری</label>
                                                @include('partials.forms.erp-customer-select', [
                                                    'class' => 'form-select select2 erp-remote-select',
                                                    'placeholder' => 'بدون اتصال به مشتری',
                                                ])
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">موضوع</label>
                                                <input class="form-control" name="subject" required maxlength="180"
                                                    placeholder="مثلا مشکل تحویل سفارش یا درخواست سرویس">
                                            </div>
                                            <div class="ticket-form-grid">
                                                <div><label class="form-label">نوع</label><select class="form-select"
                                                        name="type" required>
                                                        @foreach ($types as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}
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
                                                <div><label class="form-label">موعد SLA</label><input
                                                        class="form-control" type="datetime-local" name="due_at"></div>
                                                <div><label class="form-label">نام تماس گیرنده</label><input
                                                        class="form-control" name="contact_name" maxlength="180"></div>
                                                <div><label class="form-label">شماره تماس</label><input
                                                        class="form-control" name="contact_phone" maxlength="40">
                                                </div>
                                            </div>
                                            <div class="mt-3 mb-3">
                                                <label class="form-label">مسئول رسیدگی</label>
                                                <select class="form-select select2" name="assigned_user_id">
                                                    <option value="">کاربر فعلی</option>
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3"><label class="form-label">شرح درخواست</label>
                                                <textarea class="form-control" name="description" rows="4"></textarea>
                                            </div>
                                            <button class="btn btn-primary w-100" type="submit">ثبت تیکت</button>
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
                                                    placeholder="کد، موضوع، تماس"></div>
                                            <div class="col-md-2"><label class="form-label">نوع</label><select
                                                    class="form-select" name="type">
                                                    <option value="">همه</option>
                                                    @foreach ($types as $key => $label)
                                                        <option value="{{ $key }}"
                                                            @selected(($filters['type'] ?? '') === $key)>{{ $label }}</option>
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
                                            <div class="col-md-2"><label class="form-label">اولویت</label><select
                                                    class="form-select" name="priority">
                                                    <option value="">همه</option>
                                                    @foreach ($priorities as $key => $label)
                                                        <option value="{{ $key }}"
                                                            @selected(($filters['priority'] ?? '') === $key)>{{ $label }}</option>
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
                                                    <th>تیکت</th>
                                                    <th>مشتری / تماس</th>
                                                    <th>مسئول</th>
                                                    <th>SLA</th>
                                                    <th>وضعیت</th>
                                                    <th>عملیات</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($tickets as $ticket)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $ticket->subject }}</strong>
                                                            <div class="text-muted small">{{ $ticket->code }} /
                                                                {{ $ticket->typeText() }}</div>
                                                        </td>
                                                        <td>{{ optional($ticket->customer)->name ?: '-' }}
                                                            <div class="text-muted small">
                                                                {{ $ticket->contact_name ?: '-' }}
                                                                {{ $ticket->contact_phone ? '/ ' . $ticket->contact_phone : '' }}
                                                            </div>
                                                        </td>
                                                        <td>{{ optional($ticket->assignedUser)->name ?: '-' }}
                                                            <div class="text-muted small">{{ $ticket->channelText() }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            {{ $ticket->due_at ? $ticket->due_at->format('Y-m-d H:i') : '-' }}
                                                            @if ($ticket->isOverdue())
                                                                <div class="text-danger small">تاخیر در SLA</div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="ticket-badges">
                                                                <span
                                                                    class="badge bg-label-{{ $ticket->status === 'closed' ? 'secondary' : ($ticket->status === 'resolved' ? 'success' : ($ticket->status === 'pending' ? 'warning' : 'primary')) }}">{{ $ticket->statusText() }}</span>
                                                                <span
                                                                    class="badge bg-label-{{ $ticket->priority === 'urgent' ? 'danger' : ($ticket->priority === 'high' ? 'warning' : 'info') }}">{{ $ticket->priorityText() }}</span>
                                                            </div>
                                                            @if ($ticket->satisfaction_score)
                                                                <div class="text-muted small mt-1">رضایت:
                                                                    {{ $ticket->satisfaction_score }}/5</div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-primary" type="button"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#ticketStatus{{ $ticket->id }}">رسیدگی</button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">تیکتی
                                                            با این فیلتر پیدا نشد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">{{ $tickets->links() }}</div>
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

    @foreach ($tickets as $ticket)
        <div class="modal fade" id="ticketStatus{{ $ticket->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST"
                    action="{{ route('crm.service-tickets.status', $ticket) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-header">
                        <h5 class="modal-title">رسیدگی به {{ $ticket->code }}</h5><button type="button"
                            class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">وضعیت</label><select class="form-select"
                                name="status" required>
                                @foreach ($statuses as $key => $label)
                                    <option value="{{ $key }}" @selected($ticket->status === $key)>
                                        {{ $label }}</option>
                                @endforeach
                            </select></div>
                        <div class="mb-3"><label class="form-label">نتیجه رسیدگی</label>
                            <textarea class="form-control" name="resolution" rows="4">{{ $ticket->resolution }}</textarea>
                        </div>
                        <div class="mb-3"><label class="form-label">امتیاز رضایت مشتری</label><select
                                class="form-select" name="satisfaction_score">
                                <option value="">ثبت نشده</option>
                                @for ($score = 1; $score <= 5; $score++)
                                    <option value="{{ $score }}" @selected($ticket->satisfaction_score === $score)>
                                        {{ $score }}</option>
                                @endfor
                            </select></div>
                        <div><label class="form-label">یادداشت رضایت</label>
                            <textarea class="form-control" name="satisfaction_note" rows="2">{{ $ticket->satisfaction_note }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-label-secondary" type="button"
                            data-bs-dismiss="modal">انصراف</button><button class="btn btn-primary" type="submit">ثبت
                            رسیدگی</button></div>
                </form>
            </div>
        </div>
    @endforeach

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
        });
    </script>
</body>

</html>
