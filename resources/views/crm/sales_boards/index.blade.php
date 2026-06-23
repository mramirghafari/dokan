<!DOCTYPE html>
<html class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" data-assets-path="{{ asset('assets/') }}/"
    data-template="vertical-menu-template-no-customizer" data-theme="theme-default" dir="rtl" lang="fa">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
        name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>کاریز فروش کانبان - دکان دارمینو</title>
    <link href="{{ asset('assets/') }}/img/favicon/favicon.ico" rel="icon" type="image/x-icon" /><link href="{{ asset('assets/') }}/vendor/css/rtl/core.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/vendor/css/rtl/theme-default.css" rel="stylesheet" />
    <link href="{{ asset('assets/') }}/css/demo.css" rel="stylesheet" /><link href="{{ asset('assets/') }}/vendor/libs/select2/select2.css" rel="stylesheet" /><script src="{{ asset('assets/') }}/js/config.js"></script>
    <link href="{{ asset('assets/') }}/css/rtl.css" rel="stylesheet" />
    <style>
        .board-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .board-card {
            border: 1px solid #e8eaf2;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(34, 41, 47, .05);
            color: inherit;
            display: flex;
            flex-direction: column;
            min-height: 320px;
            overflow: hidden;
            text-decoration: none;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .board-card:hover {
            box-shadow: 0 10px 28px rgba(34, 41, 47, .12);
            color: inherit;
            transform: translateY(-2px);
        }

        .board-cover {
            align-items: center;
            background:
                linear-gradient(135deg, rgba(115, 103, 240, .88), rgba(0, 207, 232, .76)),
                radial-gradient(circle at 20% 20%, rgba(255, 255, 255, .38), transparent 32%);
            color: #fff;
            display: flex;
            height: 118px;
            justify-content: center;
            overflow: hidden;
        }

        .board-cover img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        .board-card-body {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 16px;
        }

        .board-desc {
            color: #6f6b7d;
            min-height: 44px;
        }

        .board-stat-grid {
            display: grid;
            gap: 8px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: auto;
        }

        .board-stat {
            background: #f8f8fb;
            border-radius: 8px;
            padding: 10px;
        }

        .board-add-card {
            align-items: center;
            border: 2px dashed #b8b4f4;
            cursor: pointer;
            justify-content: center;
            min-height: 320px;
        }

        .board-add-icon,
        .list-add-icon {
            align-items: center;
            background: #f1f0ff;
            border-radius: 999px;
            color: #5e50ee;
            display: inline-flex;
            height: 58px;
            justify-content: center;
            width: 58px;
        }

        .board-detail-head {
            background: #fff;
            border: 1px solid #e8eaf2;
            border-radius: 8px;
            padding: 16px;
        }

        .kanban-board {
            display: flex;
            gap: 14px;
            min-height: 66vh;
            overflow-x: auto;
            padding: 4px 2px 16px;
            scroll-snap-type: x proximity;
        }

        .kanban-list {
            background: #f6f7fb;
            border: 1px solid #e8eaf2;
            border-radius: 8px;
            flex: 0 0 318px;
            max-height: calc(100vh - 240px);
            overflow: hidden;
            scroll-snap-align: start;
        }

        .kanban-list-header {
            background: #fff;
            border-top: 4px solid var(--list-color, #7367f0);
            padding: 12px;
        }

        .list-drag-handle {
            color: #a8aaae;
            cursor: grab;
        }

        .kanban-list:active .list-drag-handle {
            cursor: grabbing;
        }

        .kanban-cards {
            min-height: 170px;
            max-height: calc(100vh - 405px);
            overflow-y: auto;
            padding: 10px;
        }

        .kanban-card {
            background: #fff;
            border: 1px solid #e6e8ef;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(34, 41, 47, .04);
            cursor: grab;
            margin-bottom: 10px;
            padding: 12px;
        }

        .kanban-card:active {
            cursor: grabbing;
        }

        .kanban-drop-active {
            background: #edf8f5;
            outline: 2px dashed #00b894;
            outline-offset: -8px;
        }

        .kanban-list-dragging {
            opacity: .55;
        }

        .kanban-meta {
            color: #6f6b7d;
            font-size: 12px;
            line-height: 1.8;
        }

        .add-card-tile {
            align-items: center;
            background: #fff;
            border: 1px dashed #c8c5f6;
            border-radius: 8px;
            color: #5e50ee;
            cursor: pointer;
            display: flex;
            gap: 8px;
            justify-content: center;
            padding: 12px;
            width: 100%;
        }

        .priority-dot {
            border-radius: 999px;
            display: inline-block;
            height: 8px;
            margin-inline-end: 5px;
            width: 8px;
        }

        .priority-low {
            background: #28c76f;
        }

        .priority-normal {
            background: #00cfe8;
        }

        .priority-high {
            background: #ff9f43;
        }

        .priority-urgent {
            background: #ea5455;
        }

        .kanban-labels {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .automation-rule-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .automation-rule {
            border: 1px solid #e8eaf2;
            border-radius: 8px;
            padding: 12px;
        }

        .select2-container--open {
            z-index: 1095;
        }

        @media (max-width: 1400px) {
            .board-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 992px) {
            .board-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .kanban-list {
                max-height: none;
            }

            .kanban-cards {
                max-height: none;
            }

            .automation-rule-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .board-grid {
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
                        @include('crm.partials.hub_bar', ['hubActive' => 'boards'])
                        @include('partials.erp-remote-select-assets')
                        @if (!$activeBoard)
                            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
                                <div>
                                    <h4 class="mb-1"><span class="text-muted fw-light">CRM /</span> بوردهای کاریز فروش
                                    </h4>
                                    <div class="text-muted">برای هر تیم، کارمند، بازاریاب، منطقه یا فرآیند فروش یک بورد
                                        جدا بسازید.</div>
                                </div>
                                <a class="btn btn-outline-primary"
                                    href="{{ route('crm.opportunities.index') }}">Pipeline جدولی</a>
                            </div>

                            <div class="board-grid">
                                @foreach ($boards as $board)
                                    <a class="card board-card"
                                        href="{{ route('crm.sales-boards.index', ['board_id' => $board->id]) }}">
                                        <div class="board-cover">
                                            @if ($board->cover_image_path)
                                                <img src="{{ asset('storage/' . $board->cover_image_path) }}"
                                                    alt="{{ $board->title }}">
                                            @else
                                                <x-ui.icon name="layout-kanban" />
                                            @endif
                                        </div>
                                        <div class="board-card-body">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                <div>
                                                    <h5 class="mb-1">{{ $board->title }}</h5>
                                                    <small
                                                        class="text-muted">{{ optional($board->owner)->name ?: 'بدون مسئول مشخص' }}</small>
                                                </div>
                                                <span
                                                    class="badge bg-label-primary">{{ $board->type === 'after_sales' ? 'خدمات' : 'فروش' }}</span>
                                            </div>
                                            <p class="board-desc mb-3">
                                                {{ \Illuminate\Support\Str::limit($board->description ?: 'بورد کاریز برای مدیریت مشتریان، فرصت ها و تسک های فروش.', 105) }}
                                            </p>
                                            <div class="board-stat-grid">
                                                <div class="board-stat"><small>لیست</small><strong
                                                        class="d-block">{{ number_format($board->lists_count) }}</strong>
                                                </div>
                                                <div class="board-stat"><small>کل کارت</small><strong
                                                        class="d-block">{{ number_format($board->cards_count) }}</strong>
                                                </div>
                                                <div class="board-stat"><small>انجام نشده</small><strong
                                                        class="d-block text-warning">{{ number_format($board->todo_cards_count) }}</strong>
                                                </div>
                                                <div class="board-stat"><small>در حال انجام / انجام شده</small><strong
                                                        class="d-block"><span
                                                            class="text-info">{{ number_format($board->doing_cards_count) }}</span>
                                                        / <span
                                                            class="text-success">{{ number_format($board->done_cards_count) }}</span></strong>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach

                                <button class="card board-card board-add-card" type="button" data-bs-toggle="modal"
                                    data-bs-target="#createBoardModal">
                                    <span class="board-add-icon mb-3">
                                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none"
                                            aria-hidden="true">
                                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" />
                                        </svg>
                                    </span>
                                    <h5 class="mb-1">ساخت بورد جدید</h5>
                                    <p class="text-muted mb-0 px-4">برای کارمند، بازاریاب، مسیر، منطقه یا سناریوی فروش
                                        تازه یک کاریز بسازید.</p>
                                </button>
                            </div>
                        @else
                            <div class="board-detail-head mb-4">
                                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                    <div class="d-flex align-items-center gap-3">
                                        <a class="btn btn-icon btn-label-secondary"
                                            href="{{ route('crm.sales-boards.index') }}"><x-ui.icon name="arrow-right" /></a>
                                        <div>
                                            <h4 class="mb-1">{{ $activeBoard->title }}</h4>
                                            <div class="text-muted">مسئول:
                                                {{ optional($activeBoard->owner)->name ?: 'بدون مسئول' }} /
                                                {{ $activeBoard->description ?: 'کاریز فروش و مشتریان' }}</div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal"
                                            data-bs-target="#createListModal"><x-ui.icon name="columns-3" class="me-1" />
                                            افزودن لیست</button>
                                        <button class="btn btn-outline-info" type="button" data-bs-toggle="modal"
                                            data-bs-target="#createAutomationRuleModal"><x-ui.icon name="bolt" class="me-1" />
                                            قانون اتوماسیون</button>
                                        <button class="btn btn-primary" type="button" data-bs-toggle="modal"
                                            data-bs-target="#createBoardModal"><x-ui.icon name="plus" class="me-1" /> بورد
                                            جدید</button>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-sm-6 col-xl-3">
                                    <div class="card">
                                        <div class="card-body"><span>کارت های باز</span>
                                            <h3 class="mt-2 mb-0">{{ number_format($boardStats['open_cards']) }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <div class="card">
                                        <div class="card-body"><span>ارزش وزنی</span>
                                            <h3 class="mt-2 mb-0 text-info">
                                                {{ number_format($boardStats['weighted_amount']) }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <div class="card">
                                        <div class="card-body"><span>اقدام معوق</span>
                                            <h3 class="mt-2 mb-0 text-danger">
                                                {{ number_format($boardStats['overdue']) }}</h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-xl-3">
                                    <div class="card">
                                        <div class="card-body"><span>لیست های بورد</span>
                                            <h3 class="mt-2 mb-0">{{ number_format($activeBoard->lists->count()) }}
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div class="card-body">
                                    <form method="GET" class="row g-2 align-items-end">
                                        <input type="hidden" name="board_id" value="{{ $activeBoard->id }}">
                                        <div class="col-md-4"><label class="form-label">جستجو در کارت ها</label><input
                                                type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                                                class="form-control" placeholder="عنوان، توضیح یا مشتری"></div>
                                        <div class="col-md-3"><label class="form-label">مسئول</label><select
                                                name="assigned_user_id" class="form-select select2">
                                                <option value="">همه</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}" @selected(($filters['assigned_user_id'] ?? '') == $user->id)>
                                                        {{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2"><label class="form-label">اولویت</label><select
                                                name="priority" class="form-select">
                                                <option value="">همه</option>
                                                @foreach ($priorities as $key => $label)
                                                    <option value="{{ $key }}" @selected(($filters['priority'] ?? '') === $key)>
                                                        {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3"><button class="btn btn-outline-primary w-100"
                                                type="submit">اعمال فیلتر</button></div>
                                    </form>
                                </div>
                            </div>

                            <div class="card mb-4">
                                <div
                                    class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                    <div>
                                        <h5 class="mb-1">اتوماسیون کارت و پیگیری</h5>
                                        <small class="text-muted">بعد از ورود کارت به لیست هدف، پیگیری CRM ساخته می شود
                                            و اعلان برای مسئولان ارسال می شود.</small>
                                    </div>
                                    <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="modal"
                                        data-bs-target="#createAutomationRuleModal"><x-ui.icon name="plus" class="me-1" />افزودن قانون</button>
                                </div>
                                <div class="card-body">
                                    @if ($activeBoard->automationRules->isEmpty())
                                        <div class="alert alert-info mb-0">برای این بورد هنوز قانون اتوماسیون ساخته
                                            نشده است.</div>
                                    @else
                                        <div class="automation-rule-grid">
                                            @foreach ($activeBoard->automationRules as $rule)
                                                <div class="automation-rule">
                                                    <div
                                                        class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                        <strong>{{ $rule->title_template }}</strong>
                                                        <span
                                                            class="badge bg-label-{{ $rule->is_active ? 'success' : 'secondary' }}">
                                                            {{ $rule->is_active ? 'فعال' : 'غیرفعال' }}
                                                        </span>
                                                    </div>
                                                    <div class="kanban-meta">
                                                        <div>لیست هدف:
                                                            {{ optional($rule->list)->title ?: 'همه لیست ها' }}</div>
                                                        <div>نوع کارت: {{ $rule->cardTypeText() }}</div>
                                                        <div>مسئول پیگیری:
                                                            {{ optional($rule->assignedUser)->name ?: 'مسئول کارت / بورد' }}
                                                        </div>
                                                        <div>موعد: {{ number_format($rule->due_days) }} روز بعد /
                                                            اولویت
                                                            {{ $priorities[$rule->priority] ?? $rule->priority }}</div>
                                                        <div>اجرا شده: {{ number_format($rule->execution_count) }} بار
                                                        </div>
                                                    </div>
                                                    <form class="mt-3" method="POST"
                                                        action="{{ route('crm.sales-boards.automation-rules.toggle', $rule) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button
                                                            class="btn btn-sm btn-label-{{ $rule->is_active ? 'secondary' : 'success' }}"
                                                            type="submit">
                                                            {{ $rule->is_active ? 'غیرفعال کردن' : 'فعال کردن' }}
                                                        </button>
                                                    </form>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            @if ($activeBoard->lists->isEmpty())
                                <div class="card">
                                    <div class="card-body text-center py-5 text-muted">این بورد هنوز لیست ندارد. با
                                        دکمه «افزودن لیست» اولین ستون کاریز را بسازید.</div>
                                </div>
                            @else
                                <div class="kanban-board" data-board-id="{{ $activeBoard->id }}"
                                    data-reorder-url="{{ route('crm.sales-boards.lists.reorder') }}">
                                    @foreach ($activeBoard->lists as $list)
                                        @php
                                            $listCards = $cardsByList->get($list->id, collect());
                                            $count = (int) ($cardCounts[$list->id] ?? 0);
                                            $amount = (float) ($cardAmounts[$list->id] ?? 0);
                                        @endphp
                                        <section class="kanban-list" draggable="true"
                                            data-list-id="{{ $list->id }}"
                                            data-final-status="{{ $list->final_status }}"
                                            style="--list-color: {{ $list->color }}">
                                            <div class="kanban-list-header">
                                                <div class="d-flex justify-content-between align-items-start gap-2">
                                                    <div>
                                                        <h6 class="mb-1"><x-ui.icon name="grip-vertical" class="list-drag-handle me-1" />{{ $list->title }}
                                                        </h6>
                                                        <div class="kanban-meta">{{ number_format($count) }} کارت /
                                                            {{ number_format($amount) }} ریال</div>
                                                    </div>
                                                    <span
                                                        class="badge bg-label-primary">{{ $list->probability_percent }}%</span>
                                                </div>
                                                @if ($list->wip_limit && $count > $list->wip_limit)
                                                    <div class="alert alert-warning py-2 px-3 mt-2 mb-0">ظرفیت WIP این
                                                        لیست رد شده است.</div>
                                                @endif
                                            </div>
                                            <div class="kanban-cards" data-list-id="{{ $list->id }}">
                                                @foreach ($listCards as $card)
                                                    @php
                                                        $cardAssignedIds = collect(
                                                            $card->assigned_user_ids ?: [$card->assigned_user_id],
                                                        )
                                                            ->filter()
                                                            ->map(fn($id) => (int) $id)
                                                            ->unique();
                                                        $cardAssignedNames = $users
                                                            ->whereIn('id', $cardAssignedIds)
                                                            ->pluck('name')
                                                            ->implode('، ');
                                                        $cardLabels = collect($card->labels ?: [])->filter(
                                                            fn($labelKey) => isset($labelOptions[$labelKey]),
                                                        );
                                                    @endphp
                                                    <article class="kanban-card" draggable="true"
                                                        data-card-id="{{ $card->id }}"
                                                        data-move-url="{{ route('crm.sales-boards.cards.move', $card) }}">
                                                        <div
                                                            class="d-flex justify-content-between gap-2 align-items-start">
                                                            <strong>{{ $card->title }}</strong>
                                                            <span
                                                                class="badge bg-label-{{ in_array($card->status, ['done', 'won'], true) ? 'success' : ($card->status === 'in_progress' ? 'info' : ($card->status === 'lost' ? 'danger' : 'secondary')) }}">{{ $card->statusText() }}</span>
                                                        </div>
                                                        <div class="kanban-meta mt-2">
                                                            <div>{{ $card->typeText() }}
                                                                @if ($card->card_type !== 'task')
                                                                    /
                                                                    {{ optional($card->customer)->name ?: 'بدون مشتری' }}
                                                                @endif
                                                            </div>
                                                            <div>
                                                                {{ $cardAssignedNames ?: optional($card->assignedUser)->name ?: 'بدون مسئول' }}
                                                            </div>
                                                            @if ($card->card_type !== 'task')
                                                                <div>{{ number_format($card->amount) }} ریال / وزنی
                                                                    {{ number_format($card->weightedAmount()) }}</div>
                                                            @else
                                                                <div>تخمین زمان: {{ $card->estimateText() }}</div>
                                                                <div>شروع:
                                                                    {{ verta_datetime($card->started_at) }}
                                                                    / پایان:
                                                                    {{ verta_datetime($card->ended_at) }}
                                                                </div>
                                                            @endif
                                                            <div>اقدام بعدی:
                                                                {{ $card->next_action_date_fa ?: verta_date($card->next_action_date_en) }}
                                                            </div>
                                                        </div>
                                                        @if ($cardLabels->isNotEmpty())
                                                            <div class="kanban-labels mt-2">
                                                                @foreach ($cardLabels as $labelKey)
                                                                    <span
                                                                        class="badge bg-label-{{ $labelOptions[$labelKey]['color'] }}">{{ $labelOptions[$labelKey]['title'] }}</span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @if ($card->customer_id)
                                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                                <a class="btn btn-xs btn-label-secondary btn-sm py-0 px-2" href="{{ route('customers.360', $card->customer_id) }}" title="پرونده ۳۶۰"><x-ui.icon name="user-search" /></a>
                                                                <button type="button" class="btn btn-xs btn-label-info btn-sm py-0 px-2 crm-quick-note" data-note-url="{{ route('crm.quick.card-note', $card) }}" title="یادداشت سریع"><x-ui.icon name="note" /></button>
                                                                @if ($card->pishfactor_id)
                                                                    <a class="btn btn-xs btn-label-success btn-sm py-0 px-2" href="{{ url('/pishFactorInfo/' . $card->pishfactor_id) }}" title="پیش‌فاکتور"><x-ui.icon name="file-invoice" /></a>
                                                                @endif
                                                            </div>
                                                        @endif
                                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                                            <span><i class="priority-dot priority-{{ $card->priority }}"></i>{{ $card->priorityText() }}</span>
                                                            <a class="btn btn-sm btn-label-primary" href="{{ route('crm.sales-boards.cards.show', $card) }}">جزئیات</a>
                                                        </div>
                                                    </article>
                                                @endforeach

                                                <button class="add-card-tile" type="button"
                                                    data-list-id="{{ $list->id }}" data-bs-toggle="modal"
                                                    data-bs-target="#createCardModal">
                                                    <span class="list-add-icon"><svg width="22" height="22"
                                                            viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                            <path d="M12 5v14M5 12h14" stroke="currentColor"
                                                                stroke-width="2" stroke-linecap="round" />
                                                        </svg></span>
                                                    افزودن کارت
                                                </button>
                                                <button class="add-card-tile mt-2" type="button"
                                                    data-list-id="{{ $list->id }}" data-bs-toggle="modal"
                                                    data-bs-target="#addCustomersModal">
                                                    <x-ui.icon name="users-plus" /> افزودن مشتری به این لیست
                                                </button>
                                            </div>
                                        </section>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                    @include('sections/footer')
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
        <div class="drag-target"></div>
    </div>

    <div class="modal fade" id="createBoardModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" method="POST" action="{{ route('crm.sales-boards.store') }}"
                enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">ساخت بورد جدید</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">نام بورد</label><input class="form-control"
                                name="title" required maxlength="160" placeholder="مثلا کاریز بازاریاب شمال تهران">
                        </div>
                        <div class="col-md-6"><label class="form-label">کارمند/مسئول بورد</label><select
                                class="form-select select2-modal" name="owner_user_id">
                                <option value="">کاربر فعلی</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">قالب اولیه</label><select class="form-select"
                                name="template">
                                <option value="sales_pipeline">کاریز فروش استاندارد</option>
                                <option value="project_sales">فروش پروژه ای</option>
                                <option value="after_sales">خدمات پس از فروش</option>
                                <option value="blank">بورد خالی</option>
                            </select></div>
                        <div class="col-md-6"><label class="form-label">عکس بورد</label><input class="form-control"
                                type="file" name="cover_image" accept="image/*"></div>
                        <div class="col-12"><label class="form-label">توضیحات</label>
                            <textarea class="form-control" name="description" rows="3"
                                placeholder="هدف این بورد، منطقه، مسیر یا تیم فروش مربوطه"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-label-secondary" type="button"
                        data-bs-dismiss="modal">انصراف</button><button class="btn btn-primary" type="submit">ساخت
                        بورد</button></div>
            </form>
        </div>
    </div>

    @if ($activeBoard)
        <div class="modal fade" id="createListModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('crm.sales-boards.lists.store') }}">
                    @csrf
                    <input type="hidden" name="board_id" value="{{ $activeBoard->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">افزودن لیست</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">نام لیست</label><input class="form-control"
                                name="title" required maxlength="140" placeholder="مثلا تایید مدیر فروش"></div>
                        <div class="row g-2">
                            <div class="col-6"><label class="form-label">احتمال موفقیت</label><input
                                    class="form-control" type="number" name="probability_percent" min="0"
                                    max="100" value="20"></div>
                            <div class="col-6"><label class="form-label">ظرفیت WIP</label><input
                                    class="form-control" type="number" name="wip_limit" min="1"
                                    placeholder="اختیاری"></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-label-secondary" type="button"
                            data-bs-dismiss="modal">انصراف</button><button class="btn btn-primary"
                            type="submit">افزودن لیست</button></div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="createCardModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('crm.sales-boards.cards.store') }}">
                    @csrf
                    <input type="hidden" name="board_id" value="{{ $activeBoard->id }}">
                    <input type="hidden" name="list_id" id="card_list_id"
                        value="{{ optional($activeBoard->lists->first())->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">افزودن کارت</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8"><label class="form-label">عنوان کارت</label><input
                                    class="form-control" name="title" required maxlength="180"
                                    placeholder="مثلا مذاکره سفارش عمده"></div>
                            <div class="col-md-4"><label class="form-label">نوع</label><select class="form-select"
                                    id="card_type_select" name="card_type">
                                    @foreach ($cardTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 card-customer-fields"><label class="form-label">مشتری</label>
                                @include('partials.forms.erp-customer-select', [
                                    'name' => 'customer_id',
                                    'placeholder' => 'بدون مشتری',
                                    'class' => 'form-select select2-modal erp-remote-select',
                                ])
                            </div>
                            <div class="col-md-6 card-customer-fields"><label class="form-label">فرصت
                                    فروش</label><select class="form-select select2-modal" name="opportunity_id">
                                    <option value="">بدون اتصال</option>
                                    @foreach ($opportunities as $opportunity)
                                        <option value="{{ $opportunity->id }}">{{ $opportunity->title }} -
                                            {{ number_format($opportunity->amount) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 card-customer-fields"><label class="form-label">مبلغ</label><input
                                    class="form-control" type="number" name="amount" min="0"
                                    step="1000" value="0">
                            </div>
                            <div class="col-md-3 card-customer-fields"><label class="form-label">احتمال</label><input
                                    class="form-control" type="number" name="probability_percent" min="0"
                                    max="100" value="20"></div>
                            <div class="col-md-3"><label class="form-label">اولویت</label><select class="form-select"
                                    name="priority">
                                    @foreach ($priorities as $key => $label)
                                        <option value="{{ $key }}" @selected($key === 'normal')>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3"><label class="form-label">اقدام بعدی</label><input
                                    class="form-control" type="date" name="next_action_date_en"
                                    value="{{ now()->toDateString() }}"></div>
                            <div class="col-md-4"><label class="form-label">تخمین زمان دقیقه</label><input
                                    class="form-control" type="number" name="estimate_minutes" min="0"
                                    placeholder="مثلا 90"></div>
                            <div class="col-md-8"><label class="form-label">مسئولان / منشن ها</label><select
                                    class="form-select select2-modal" name="assigned_user_ids[]" multiple>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12"><label class="form-label">لیبل ها</label><select
                                    class="form-select select2-modal" name="labels[]" multiple>
                                    @foreach ($labelOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label['title'] }}</option>
                                    @endforeach
                                </select></div>
                            <div class="col-12"><label class="form-label">شرح</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-label-secondary" type="button"
                            data-bs-dismiss="modal">انصراف</button><button class="btn btn-primary"
                            type="submit">افزودن کارت</button></div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="createAutomationRuleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form class="modal-content" method="POST"
                    action="{{ route('crm.sales-boards.automation-rules.store') }}">
                    @csrf
                    <input type="hidden" name="board_id" value="{{ $activeBoard->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">قانون اتوماسیون کارت</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">برای اجرای این قوانین، گزینه «ساخت پیگیری بعد از جابجایی کارت
                            کاریز» در سناریوی فروش پنل باید فعال باشد.</div>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">وقتی کارت وارد این لیست شد</label><select
                                    class="form-select select2-modal" name="list_id">
                                    <option value="">همه لیست ها</option>
                                    @foreach ($activeBoard->lists as $list)
                                        <option value="{{ $list->id }}">{{ $list->title }}</option>
                                    @endforeach
                                </select></div>
                            <div class="col-md-6"><label class="form-label">نوع کارت</label><select
                                    class="form-select" name="card_type">
                                    @foreach ($automationCardTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select></div>
                            <div class="col-md-6"><label class="form-label">مسئول پیگیری</label><select
                                    class="form-select select2-modal" name="assigned_user_id">
                                    <option value="">مسئول کارت / مسئول بورد</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select></div>
                            <div class="col-md-3"><label class="form-label">موعد روز بعد</label><input
                                    class="form-control" type="number" name="due_days" min="0"
                                    max="90" value="1"></div>
                            <div class="col-md-3"><label class="form-label">اولویت</label><select class="form-select"
                                    name="priority">
                                    @foreach ($priorities as $key => $label)
                                        <option value="{{ $key }}" @selected($key === 'normal')>
                                            {{ $label }}</option>
                                    @endforeach
                                </select></div>
                            <div class="col-12"><label class="form-label">عنوان پیگیری</label><input
                                    class="form-control" name="title_template" required maxlength="220"
                                    value="پیگیری {card} در مرحله {to_list}"></div>
                            <div class="col-12"><label class="form-label">شرح پیگیری</label>
                                <textarea class="form-control" name="description_template" rows="3">کارت {card} از {from_list} به {to_list} منتقل شد. لطفا پیگیری بعدی با مشتری {customer} انجام شود.</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="switch switch-primary">
                                    <input class="switch-input" type="checkbox" name="notify_assignee"
                                        value="1" checked>
                                    <span class="switch-toggle-slider"><span class="switch-on"></span><span
                                            class="switch-off"></span></span>
                                    <span class="switch-label">اعلان به مسئول پیگیری</span>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="switch switch-primary">
                                    <input class="switch-input" type="checkbox" name="notify_board_owner"
                                        value="1" checked>
                                    <span class="switch-toggle-slider"><span class="switch-on"></span><span
                                            class="switch-off"></span></span>
                                    <span class="switch-label">اعلان به مسئول بورد</span>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="switch switch-warning">
                                    <input class="switch-input" type="checkbox" name="escalate_to_manager"
                                        value="1">
                                    <span class="switch-toggle-slider"><span class="switch-on"></span><span
                                            class="switch-off"></span></span>
                                    <span class="switch-label">ارسال هشدار به مدیران پنل</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-label-secondary" type="button"
                            data-bs-dismiss="modal">انصراف</button><button class="btn btn-info" type="submit">ثبت
                            قانون</button></div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="addCustomersModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form class="modal-content" method="POST"
                    action="{{ route('crm.sales-boards.customers.store') }}">
                    @csrf
                    <input type="hidden" name="board_id" value="{{ $activeBoard->id }}">
                    <input type="hidden" name="list_id" id="customers_list_id"
                        value="{{ optional($activeBoard->lists->first())->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">افزودن مشتری به لیست</h5><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">روش انتخاب</label><select
                                    class="form-select" name="customer_mode">
                                    <option value="selected">انتخاب دستی</option>
                                    <option value="active">مشتریان فعال</option>
                                    <option value="inactive">مشتریان غیرفعال</option>
                                    <option value="new">مشتریان جدید 30 روز اخیر</option>
                                    <option value="region">بر اساس منطقه</option>
                                    <option value="area">بر اساس مسیر/ناحیه</option>
                                </select></div>
                            <div class="col-md-4"><label class="form-label">منطقه</label><input class="form-control"
                                    type="number" name="region_id" placeholder="شناسه منطقه"></div>
                            <div class="col-md-4"><label class="form-label">مسیر/ناحیه</label><input
                                    class="form-control" type="number" name="area_id" placeholder="شناسه مسیر">
                            </div>
                            <div class="col-md-8"><label class="form-label">مشتریان انتخابی</label>
                                <x-erp-remote-select
                                    entity="customers"
                                    name="customer_ids[]"
                                    placeholder="جستجو و انتخاب مشتری"
                                    class="form-select select2-modal erp-remote-select"
                                    :multiple="true"
                                />
                            </div>
                            <div class="col-md-4"><label class="form-label">اولویت</label><select class="form-select"
                                    name="priority">
                                    @foreach ($priorities as $key => $label)
                                        <option value="{{ $key }}" @selected($key === 'normal')>
                                            {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label">لیبل مشتری ها</label><select
                                    class="form-select select2-modal" name="labels[]" multiple>
                                    @foreach ($labelOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label['title'] }}</option>
                                    @endforeach
                                </select></div>
                            <div class="col-md-6"><label class="form-label">مسئول کارت های مشتری</label><select
                                    class="form-select select2-modal" name="assigned_user_id">
                                    <option value="">مسئول بورد</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select></div>
                            <div class="col-12">
                                <div class="alert alert-info mb-0">برای حفظ سرعت، هر بار حداکثر 500 مشتری به لیست اضافه
                                    می شود و مشتری تکراری در همان بورد دوباره ساخته نمی شود.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-label-secondary" type="button"
                            data-bs-dismiss="modal">انصراف</button><button class="btn btn-primary"
                            type="submit">افزودن مشتریان</button></div>
                </form>
            </div>
        </div>
    @endif

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

            $('.modal').on('shown.bs.modal', function() {
                const $modal = $(this);
                window.ErpRemoteSelect && window.ErpRemoteSelect.init($modal[0]);
                $modal.find('.select2-modal:not(.erp-remote-select)').each(function() {
                    if ($(this).data('select2')) {
                        return;
                    }

                    $(this).select2({
                        dir: 'rtl',
                        width: '100%',
                        dropdownParent: $modal
                    });
                });
            });

            $('#createCardModal').on('show.bs.modal', function(event) {
                $('#card_list_id').val($(event.relatedTarget).data('list-id'));
            });

            $('#addCustomersModal').on('show.bs.modal', function(event) {
                $('#customers_list_id').val($(event.relatedTarget).data('list-id'));
            });

            function syncCardTypeFields() {
                const isTask = $('#card_type_select').val() === 'task';
                $('.card-customer-fields').toggleClass('d-none', isTask);
                $('.card-customer-fields').find('input, select').prop('disabled', isTask);
            }

            $('#createCardModal').on('shown.bs.modal', syncCardTypeFields);
            $('#card_type_select').on('change', syncCardTypeFields);
            syncCardTypeFields();

            let draggedCard = null;
            let draggedList = null;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            document.querySelectorAll('.kanban-card').forEach(function(card) {
                card.addEventListener('dragstart', function(event) {
                    draggedCard = card;
                    draggedList = null;
                    event.stopPropagation();
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', card.dataset.cardId);
                });
            });

            document.querySelectorAll('.kanban-cards').forEach(function(list) {
                list.addEventListener('dragover', function(event) {
                    if (!draggedCard) return;
                    event.preventDefault();
                    list.classList.add('kanban-drop-active');
                });

                list.addEventListener('dragleave', function() {
                    list.classList.remove('kanban-drop-active');
                });

                list.addEventListener('drop', function(event) {
                    if (!draggedCard) return;
                    event.preventDefault();
                    list.classList.remove('kanban-drop-active');

                    if (draggedCard.closest('.kanban-cards') === list) return;

                    const finalizeMove = function(lostReason) {
                        const payload = { list_id: list.dataset.listId };
                        if (lostReason) payload.lost_reason = lostReason;

                        fetch(draggedCard.dataset.moveUrl, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify(payload)
                        }).then(function(response) {
                            if (response.status === 422) {
                                return response.json().then(function() { throw new Error('validation'); });
                            }
                            if (!response.ok) throw new Error('move_failed');
                            return response.json();
                        }).then(function(data) {
                            list.insertBefore(draggedCard, list.querySelector('.add-card-tile'));
                            if (data && data.pishfactor_url) {
                                alert('پیش‌فاکتور از کارت برنده ساخته شد.');
                            }
                        }).catch(function(err) {
                            if (err && err.message === 'validation') {
                                alert('برای انتقال به لیست باخت، دلیل باخت الزامی است.');
                            }
                            window.location.reload();
                        });
                    };

                    if (list.dataset.finalStatus === 'lost') {
                        const reason = window.prompt('دلیل باخت را وارد کنید:');
                        if (!reason || !reason.trim()) {
                            window.location.reload();
                            return;
                        }
                        finalizeMove(reason.trim());
                        return;
                    }

                    finalizeMove(null);
                });
            });

            document.querySelectorAll('.kanban-list').forEach(function(list) {
                list.addEventListener('dragstart', function(event) {
                    if (event.target.classList.contains('kanban-card')) return;
                    draggedList = list;
                    draggedCard = null;
                    list.classList.add('kanban-list-dragging');
                    event.dataTransfer.effectAllowed = 'move';
                });

                list.addEventListener('dragend', function() {
                    list.classList.remove('kanban-list-dragging');
                });

                list.addEventListener('dragover', function(event) {
                    if (!draggedList || draggedList === list) return;
                    event.preventDefault();
                });

                list.addEventListener('drop', function(event) {
                    if (!draggedList || draggedList === list) return;
                    event.preventDefault();

                    const board = document.querySelector('.kanban-board');
                    const targetBox = list.getBoundingClientRect();
                    const before = event.clientX > targetBox.left + targetBox.width / 2;
                    board.insertBefore(draggedList, before ? list.nextSibling : list);

                    const listOrder = Array.from(board.querySelectorAll('.kanban-list')).map(
                        function(item) {
                            return item.dataset.listId;
                        });

                    fetch(board.dataset.reorderUrl, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            board_id: board.dataset.boardId,
                            list_order: listOrder
                        })
                    }).then(function(response) {
                        if (!response.ok) throw new Error('reorder_failed');
                    }).catch(function() {
                        window.location.reload();
                    });
                });
            });

            document.querySelectorAll('.crm-quick-note').forEach(function(button) {
                button.addEventListener('click', function() {
                    const body = window.prompt('یادداشت فروش:');
                    if (!body || !body.trim()) return;

                    fetch(button.dataset.noteUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ comment: body.trim() })
                    }).then(function(response) {
                        if (!response.ok) throw new Error('note_failed');
                        alert('یادداشت ثبت شد.');
                    }).catch(function() {
                        alert('ثبت یادداشت ناموفق بود.');
                    });
                });
            });
        });
    </script>
</body>

</html>
