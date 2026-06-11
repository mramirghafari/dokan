<!DOCTYPE html>
<html lang="fa" class="light-style layout-menu-fixed" dir="rtl" data-theme="theme-default"
    data-assets-path="../../assets/" data-template="vertical-menu-template-free">

<head>
    @include('sections.head')
    <title>جزئیات کارت کاریز - دکان دارمینو</title>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('sections.sidebar')
            <div class="layout-page">
                @include('sections.navbar')
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                            <div>
                                <h4 class="mb-1"><span class="text-muted fw-light">CRM / کاریز فروش /</span>
                                    {{ $card->title }}</h4>
                                <div class="text-muted">{{ optional($card->board)->title }} /
                                    {{ optional($card->list)->title }}</div>
                            </div>
                            <div class="d-flex gap-2">
                                @if ($card->pishfactor_id)
                                    <a class="btn btn-label-success" href="{{ url('/pishFactorInfo/' . $card->pishfactor_id) }}">پیش‌فاکتور متصل</a>
                                @endif
                                @if ($card->customer_id)
                                    <a class="btn btn-label-primary" href="{{ route('customers.360', $card->customer_id) }}">پرونده ۳۶۰</a>
                                @endif
                                <a class="btn btn-label-secondary" href="{{ route('crm.sales-boards.index', ['board_id' => $card->board_id]) }}">بازگشت به بورد</a>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">ویرایش سریع کارت</h5>
                                        <span class="badge bg-label-primary">{{ $card->typeText() }}</span>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST"
                                            action="{{ route('crm.sales-boards.cards.update', $card) }}">
                                            @csrf
                                            @method('PATCH')
                                            <div class="row g-3">
                                                <div class="col-md-8">
                                                    <label class="form-label">عنوان</label>
                                                    <input class="form-control" name="title"
                                                        value="{{ old('title', $card->title) }}" required>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">وضعیت</label>
                                                    <select class="form-select" name="status" required>
                                                        @foreach ($statuses as $key => $label)
                                                            <option value="{{ $key }}"
                                                                @selected(old('status', $card->status) === $key)>{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">دلیل باخت (در صورت از دست رفتن)</label>
                                                    <input class="form-control" name="lost_reason" value="{{ old('lost_reason', $card->lost_reason) }}" placeholder="الزامی هنگام باخت — طبق تنظیمات tenant">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">اولویت</label>
                                                    <select class="form-select" name="priority" required>
                                                        @foreach ($priorities as $key => $label)
                                                            <option value="{{ $key }}"
                                                                @selected(old('priority', $card->priority) === $key)>{{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">مسئول اصلی</label>
                                                    <select class="form-select" name="assigned_user_id">
                                                        <option value="">بدون مسئول</option>
                                                        @foreach ($users as $user)
                                                            <option value="{{ $user->id }}"
                                                                @selected((int) old('assigned_user_id', $card->assigned_user_id) === (int) $user->id)>{{ $user->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">اقدام بعدی</label>
                                                    <input type="date" class="form-control"
                                                        name="next_action_date_en"
                                                        value="{{ old('next_action_date_en', optional($card->next_action_date_en)->format('Y-m-d')) }}">
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">مسئولان و منشن ها</label>
                                                    <select class="form-select" name="assigned_user_ids[]" multiple>
                                                        @foreach ($users as $user)
                                                            <option value="{{ $user->id }}"
                                                                @selected(
    collect(old('assigned_user_ids', $card->assigned_user_ids ?: []))
        ->map(fn($id) => (int) $id)
        ->contains((int) $user->id),
)>{{ $user->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">برچسب ها</label>
                                                    <div class="d-flex flex-wrap gap-3">
                                                        @foreach ($labelOptions as $key => $label)
                                                            <label class="form-check mb-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="labels[]" value="{{ $key }}"
                                                                    @checked(collect(old('labels', $card->labels ?: []))->contains($key))>
                                                                <span
                                                                    class="form-check-label badge bg-label-{{ $label['color'] }}">{{ $label['title'] }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @if ($card->card_type !== 'task')
                                                    <div class="col-md-4">
                                                        <label class="form-label">مبلغ</label>
                                                        <input type="number" min="0" step="1000"
                                                            class="form-control" name="amount"
                                                            value="{{ old('amount', (float) $card->amount) }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">احتمال</label>
                                                        <input type="number" min="0" max="100"
                                                            class="form-control" name="probability_percent"
                                                            value="{{ old('probability_percent', $card->probability_percent) }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">تاریخ close</label>
                                                        <input type="date" class="form-control"
                                                            name="expected_close_date_en"
                                                            value="{{ old('expected_close_date_en', optional($card->expected_close_date_en)->format('Y-m-d')) }}">
                                                    </div>
                                                @endif
                                                <div class="col-12">
                                                    <label class="form-label">شرح</label>
                                                    <textarea class="form-control" name="description" rows="4">{{ old('description', $card->description) }}</textarea>
                                                </div>
                                                <div class="col-12 text-end">
                                                    <button class="btn btn-primary" type="submit">ذخیره
                                                        تغییرات</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">چک لیست</h5>
                                        <span
                                            class="badge bg-label-secondary">{{ $card->checklistItems->where('is_done', true)->count() }}
                                            / {{ $card->checklistItems->count() }}</span>
                                    </div>
                                    <div class="card-body">
                                        <form class="d-flex gap-2 mb-3" method="POST"
                                            action="{{ route('crm.sales-boards.cards.checklist.store', $card) }}">
                                            @csrf
                                            <input class="form-control" name="title"
                                                placeholder="آیتم جدید چک لیست" required>
                                            <button class="btn btn-primary" type="submit">افزودن</button>
                                        </form>
                                        @forelse ($card->checklistItems as $item)
                                            <form
                                                class="d-flex justify-content-between align-items-center border rounded p-2 mb-2"
                                                method="POST"
                                                action="{{ route('crm.sales-boards.cards.checklist.update', [$card, $item]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="is_done"
                                                    value="{{ $item->is_done ? 0 : 1 }}">
                                                <div>
                                                    <div
                                                        class="{{ $item->is_done ? 'text-decoration-line-through text-muted' : '' }}">
                                                        {{ $item->title }}</div>
                                                    <small
                                                        class="text-muted">{{ $item->is_done ? 'تکمیل توسط ' . optional($item->doneBy)->name . ' در ' . optional($item->done_at)->format('Y-m-d H:i') : 'باز' }}</small>
                                                </div>
                                                <button
                                                    class="btn btn-sm {{ $item->is_done ? 'btn-label-secondary' : 'btn-label-success' }}"
                                                    type="submit">{{ $item->is_done ? 'بازگشایی' : 'تکمیل' }}</button>
                                            </form>
                                        @empty
                                            <div class="text-muted py-3">هنوز آیتمی برای این کارت ثبت نشده است.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">کامنت داخلی</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST"
                                            action="{{ route('crm.sales-boards.cards.comments.store', $card) }}"
                                            class="mb-4">
                                            @csrf
                                            <div class="mb-3">
                                                <textarea class="form-control" name="comment" rows="3" placeholder="یادداشت، نتیجه تماس یا هماهنگی داخلی"
                                                    required></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">منشن کاربران</label>
                                                <select class="form-select" name="mentions[]" multiple>
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button class="btn btn-primary" type="submit">ثبت کامنت</button>
                                        </form>
                                        @forelse ($card->comments as $comment)
                                            <div class="border rounded p-3 mb-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <strong>{{ optional($comment->user)->name ?: 'کاربر حذف شده' }}</strong>
                                                    <small
                                                        class="text-muted">{{ optional($comment->created_at)->format('Y-m-d H:i') }}</small>
                                                </div>
                                                <div style="white-space: pre-line">{{ $comment->comment }}</div>
                                            </div>
                                        @empty
                                            <div class="text-muted py-3">هنوز کامنتی برای این کارت ثبت نشده است.</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">خلاصه کارت</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2"><span class="text-muted">مشتری:</span>
                                            {{ optional($card->customer)->name ?: '-' }}</div>
                                        <div class="mb-2"><span class="text-muted">فرصت:</span>
                                            {{ optional($card->opportunity)->title ?: '-' }}</div>
                                        <div class="mb-2"><span class="text-muted">مبلغ وزنی:</span>
                                            {{ number_format($card->weightedAmount()) }}</div>
                                        <div class="mb-2"><span class="text-muted">تخمین زمان:</span>
                                            {{ $card->estimateText() }}</div>
                                        <div><span class="text-muted">آخرین جابجایی:</span>
                                            {{ optional($card->moved_at)->format('Y-m-d H:i') ?: '-' }}</div>
                                    </div>
                                </div>

                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0">پیوست ها</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST"
                                            action="{{ route('crm.sales-boards.cards.attachments.store', $card) }}"
                                            enctype="multipart/form-data" class="mb-4">
                                            @csrf
                                            <div class="mb-3">
                                                <input class="form-control" type="file" name="attachment"
                                                    required>
                                            </div>
                                            <div class="mb-3">
                                                <textarea class="form-control" name="description" rows="2" placeholder="توضیح پیوست"></textarea>
                                            </div>
                                            <button class="btn btn-primary w-100" type="submit">بارگذاری
                                                پیوست</button>
                                        </form>
                                        @forelse ($card->attachments as $attachment)
                                            <div class="border rounded p-2 mb-2">
                                                <a href="{{ $attachment->url() }}"
                                                    target="_blank">{{ $attachment->original_name }}</a>
                                                <div class="text-muted small">
                                                    {{ number_format($attachment->size / 1024, 1) }} KB /
                                                    {{ optional($attachment->user)->name }}</div>
                                                @if ($attachment->description)
                                                    <div class="small mt-1">{{ $attachment->description }}</div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-muted py-3">پیوستی ثبت نشده است.</div>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Activity log</h5>
                                    </div>
                                    <div class="card-body">
                                        @forelse (collect($card->activity_logs ?: [])->reverse() as $activity)
                                            <div class="border-bottom pb-2 mb-2">
                                                <div>{{ $activity['user_name'] ?? 'سیستم' }} /
                                                    {{ $activity['type'] ?? '-' }}</div>
                                                <small class="text-muted">{{ $activity['at'] ?? '-' }}</small>
                                                @if (!empty($activity['title']))
                                                    <div class="small">{{ $activity['title'] }}</div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-muted py-3">فعالیتی ثبت نشده است.</div>
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
