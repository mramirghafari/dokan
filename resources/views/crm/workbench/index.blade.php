<!DOCTYPE html>
<html lang="fa" class="light-style layout-menu-fixed" dir="rtl" data-theme="theme-default"
    data-assets-path="../../assets/" data-template="vertical-menu-template-free">

<head>
    @include('sections.head')
    <title>کارتابل شخصی CRM - دکان دارمینو</title>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.navbar')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @include('crm.partials.hub_bar', ['hubActive' => 'workbench'])
                        @include('partials.erp-remote-select-assets')
                        @php($stats = $state['stats'])
                        @php($filters = $state['filters'])
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">CRM /</span> کارتابل شخصی و
                                    mentionها</h4>
                                <div class="text-muted">پیگیری، فرصت، تیکت، تماس، coaching و commentهای CRM در یک نمای
                                    عملیاتی.</div>
                            </div>
                            <form method="POST" action="{{ route('crm.workbench.preferences.update') }}"
                                class="d-flex gap-2 align-items-center">
                                @csrf
                                <select class="form-select" name="focus_scope">
                                    @foreach ($state['focus_scopes'] as $key => $label)
                                        <option value="{{ $key }}" @selected($filters['focus_scope'] === $key)>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-primary" type="submit">ذخیره نما</button>
                            </form>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">پیگیری</div>
                                        <h3 class="mb-0">{{ number_format($stats['followups']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">فرصت</div>
                                        <h3 class="mb-0">{{ number_format($stats['opportunities']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">تیکت</div>
                                        <h3 class="mb-0">{{ number_format($stats['tickets']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">تماس</div>
                                        <h3 class="mb-0">{{ number_format($stats['calls']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">coaching</div>
                                        <h3 class="mb-0">{{ number_format($stats['coachings']) }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="text-muted mb-1">mention</div>
                                        <h3 class="mb-0 text-warning">{{ number_format($stats['mentions']) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-xl-8">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">کارتابل عملیاتی</h5>
                                    </div>
                                    <div class="table-responsive text-nowrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>نوع</th>
                                                    <th>عنوان</th>
                                                    <th>مسئول</th>
                                                    <th>وضعیت</th>
                                                    <th>موعد</th>
                                                    <th>کامنت سریع</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php($rows = collect())
                                                @foreach ($state['followups'] as $item)
                                                    @php($rows->push(['followup', 'پیگیری', $item, $item->title, optional($item->assignedUser)->name, $item->statusText(), $item->due_date_en]))
                                                @endforeach
                                                @foreach ($state['opportunities'] as $item)
                                                    @php($rows->push(['opportunity', 'فرصت', $item, $item->title, optional($item->assignedUser)->name, $item->stageText(), $item->next_action_date_en]))
                                                @endforeach
                                                @foreach ($state['tickets'] as $item)
                                                    @php($rows->push(['ticket', 'تیکت', $item, $item->subject, optional($item->assignedUser)->name, $item->statusText(), $item->due_at]))
                                                @endforeach
                                                @foreach ($state['calls'] as $item)
                                                    @php($rows->push(['call', 'تماس', $item, $item->subject, optional($item->assignedUser)->name, $item->statusText(), $item->next_action_at]))
                                                @endforeach
                                                @foreach ($state['coachings'] as $item)
                                                    @php($rows->push(['coaching', 'coaching', $item, $item->title, optional($item->user)->name, $item->statusText(), $item->due_at]))
                                                @endforeach

                                                @forelse ($rows as $row)
                                                    <tr>
                                                        <td>{{ $row[1] }}</td>
                                                        <td><strong>{{ $row[3] }}</strong></td>
                                                        <td>{{ $row[4] ?: '-' }}</td>
                                                        <td>{{ $row[5] }}</td>
                                                        <td>{{ $row[6] ? \Illuminate\Support\Carbon::parse($row[6])->format('Y-m-d H:i') : '-' }}
                                                        </td>
                                                        <td style="min-width: 280px;">
                                                            <form method="POST"
                                                                action="{{ route('crm.workbench.comments.store') }}"
                                                                class="d-flex gap-2">
                                                                @csrf
                                                                <input type="hidden" name="target_type"
                                                                    value="{{ $row[0] }}">
                                                                <input type="hidden" name="target_id"
                                                                    value="{{ $row[2]->id }}">
                                                                <input type="hidden" name="visibility" value="team">
                                                                <input class="form-control form-control-sm"
                                                                    name="body" placeholder="کامنت سریع..." required>
                                                                <button class="btn btn-sm btn-label-primary"
                                                                    type="submit">ثبت</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">برای این
                                                            نما کار باز یا موعددار دیده نشد.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">ثبت comment و mention روی رکورد CRM</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST" action="{{ route('crm.workbench.comments.store') }}"
                                            class="row g-3">
                                            @csrf
                                            <div class="col-md-3">
                                                <label class="form-label">نوع رکورد</label>
                                                <select class="form-select" name="target_type" required>
                                                    @foreach ($state['target_labels'] as $key => $label)
                                                        <option value="{{ $key }}">{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">شناسه رکورد</label>
                                                <input class="form-control" name="target_id" type="number"
                                                    min="1" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">سطح مشاهده</label>
                                                <select class="form-select" name="visibility">
                                                    @foreach (\App\Models\CrmCollaborationComment::VISIBILITIES as $key => $label)
                                                        <option value="{{ $key }}">{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">mention</label>
                                                <select class="form-select" name="mentioned_user_ids[]" multiple>
                                                    @foreach ($state['users'] as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">متن comment</label>
                                                <textarea class="form-control" name="body" rows="3" required></textarea>
                                            </div>
                                            <div class="col-12 text-end"><button class="btn btn-primary"
                                                    type="submit">ثبت comment</button></div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-4">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">Inbox منشن ها</h5>
                                    </div>
                                    <div class="card-body">
                                        @forelse ($state['mentions'] as $mention)
                                            <div class="border rounded p-3 mb-3 border-warning">
                                                <div class="d-flex justify-content-between gap-2 mb-2">
                                                    <strong>{{ optional($mention->comment?->user)->name ?: 'کاربر حذف شده' }}</strong>
                                                    <span class="badge bg-label-warning">جدید</span>
                                                </div>
                                                <div class="mb-2">{{ optional($mention->comment)->body }}</div>
                                                <div class="text-muted small mb-2">
                                                    {{ optional($mention->created_at)->format('Y-m-d H:i') }}</div>
                                                <form method="POST"
                                                    action="{{ route('crm.workbench.mentions.read', $mention) }}"
                                                    class="text-end">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-sm btn-label-primary"
                                                        type="submit">خواندم</button>
                                                </form>
                                            </div>
                                        @empty
                                            <div class="text-center text-muted py-4">mention خوانده نشده وجود ندارد.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">کامنت های اخیر CRM</h5>
                                    </div>
                                    <div class="card-body">
                                        @forelse ($state['comments'] as $comment)
                                            <div class="border-bottom pb-3 mb-3">
                                                <div class="d-flex justify-content-between gap-2 mb-1">
                                                    <strong>{{ optional($comment->user)->name ?: 'کاربر حذف شده' }}</strong>
                                                    <span
                                                        class="text-muted small">{{ optional($comment->created_at)->format('Y-m-d H:i') }}</span>
                                                </div>
                                                <div>{{ $comment->body }}</div>
                                                @if (($comment->mentioned_user_ids ?: []) !== [])
                                                    <div class="text-muted small mt-2">mention:
                                                        {{ implode('، ', $comment->mentions->pluck('mentionedUser.name')->filter()->all()) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-center text-muted py-4">کامنتی ثبت نشده است.</div>
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
