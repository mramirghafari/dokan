<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;

use App\Models\CrmOpportunity;
use App\Models\CrmAutomationRule;
use App\Models\CrmSalesBoard;
use App\Models\CrmSalesBoardCard;
use App\Models\CrmSalesBoardCardAttachment;
use App\Models\CrmSalesBoardCardChecklistItem;
use App\Models\CrmSalesBoardCardComment;
use App\Models\CrmSalesBoardList;
use App\Models\Customers;
use App\Models\User;
use App\Services\CrmAdvancedAutomationService;
use App\Services\CrmCardAutomationService;
use App\Services\PanelNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class CrmSalesBoardController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers,user')->only(['index', 'storeBoard', 'storeList', 'storeCard', 'storeCustomers', 'storeAutomationRule', 'toggleAutomationRule', 'moveCard', 'reorderLists', 'showCard', 'updateCard', 'storeChecklistItem', 'updateChecklistItem', 'storeComment', 'storeAttachment']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $boardsQuery = CrmSalesBoard::query()
            ->with(['owner'])
            ->withCount([
                'lists',
                'cards',
                'cards as todo_cards_count' => fn($query) => $query->where('status', 'open'),
                'cards as doing_cards_count' => fn($query) => $query->where('status', 'in_progress'),
                'cards as done_cards_count' => fn($query) => $query->whereIn('status', ['done', 'won']),
            ])
            ->orderBy('position')
            ->orderBy('id');

        if ((int) $user->isGod !== 1) {
            $boardsQuery->forOrganizations($user);
        }

        $boards = $boardsQuery->get();
        $activeBoard = null;

        if ($boards->isNotEmpty() && $request->filled('board_id')) {
            $activeBoard = $boards->firstWhere('id', (int) $request->integer('board_id'));

            if ($activeBoard) {
                $activeBoard->load(['lists', 'automationRules.list', 'automationRules.assignedUser']);
            }
        }

        $cardsByList = collect();
        $cardCounts = collect();
        $cardAmounts = collect();
        $boardStats = ['open_cards' => 0, 'weighted_amount' => 0, 'overdue' => 0];

        if ($activeBoard) {
            $listIds = $activeBoard->lists->pluck('id');
            $cardsQuery = CrmSalesBoardCard::query()
                ->with(['customer', 'assignedUser', 'opportunity'])
                ->where('board_id', $activeBoard->id)
                ->whereIn('list_id', $listIds)
                ->when($request->filled('search'), function ($query) use ($request) {
                    $search = '%' . trim($request->search) . '%';
                    $query->where(function ($inner) use ($search) {
                        $inner->where('title', 'like', $search)
                            ->orWhere('description', 'like', $search)
                            ->orWhereHas('customer', fn($customerQuery) => $customerQuery->where('name', 'like', $search));
                    });
                })
                ->when($request->filled('assigned_user_id'), fn($query) => $query->where('assigned_user_id', $request->assigned_user_id))
                ->when($request->filled('priority'), fn($query) => $query->where('priority', $request->priority))
                ->orderBy('position')
                ->orderByDesc('id')
                ->limit(600);

            if ((int) $user->isGod !== 1) {
                $cardsQuery->forOrganizations($user);
            }

            $cardsByList = $cardsQuery->get()->groupBy('list_id');

            $cardCounts = CrmSalesBoardCard::query()
                ->where('board_id', $activeBoard->id)
                ->whereIn('list_id', $listIds)
                ->select('list_id', DB::raw('COUNT(*) as count'))
                ->groupBy('list_id')
                ->pluck('count', 'list_id');

            $cardAmounts = CrmSalesBoardCard::query()
                ->where('board_id', $activeBoard->id)
                ->whereIn('list_id', $listIds)
                ->where('status', 'open')
                ->select('list_id', DB::raw('COALESCE(SUM(amount), 0) as amount'))
                ->groupBy('list_id')
                ->pluck('amount', 'list_id');

            $boardStats = [
                'open_cards' => CrmSalesBoardCard::query()->where('board_id', $activeBoard->id)->where('status', 'open')->count(),
                'weighted_amount' => CrmSalesBoardCard::query()->where('board_id', $activeBoard->id)->where('status', 'open')->selectRaw('COALESCE(SUM(amount * probability_percent / 100), 0) as weighted_amount')->value('weighted_amount'),
                'overdue' => CrmSalesBoardCard::query()->where('board_id', $activeBoard->id)->where('status', 'open')->whereDate('next_action_date_en', '<', now()->toDateString())->count(),
            ];
        }

        $userColumns = array_values(array_filter(['id', 'name', Schema::hasColumn('users', 'tenant_id') ? 'tenant_id' : null, 'organization_id', 'isActive']));

        $usersQuery = User::query()->select($userColumns)->where('isActive', 1)->orderBy('name')->limit(200);
        $opportunitiesQuery = CrmOpportunity::query()->select(['id', 'title', 'customer_id', 'amount', 'stage', 'status', 'tenant_id', 'organization_id'])->where('status', 'open')->orderByDesc('id')->limit(200);

        if ((int) $user->isGod !== 1) {
            $usersQuery->forOrganizations($user);
            $opportunitiesQuery->forOrganizations($user);
        }

        return view('crm.sales_boards.index', [
            'boards' => $boards,
            'activeBoard' => $activeBoard,
            'cardsByList' => $cardsByList,
            'cardCounts' => $cardCounts,
            'cardAmounts' => $cardAmounts,
            'boardStats' => $boardStats,
            'users' => $usersQuery->get(),
            'opportunities' => $opportunitiesQuery->get(),
            'priorities' => CrmSalesBoardCard::PRIORITIES,
            'cardTypes' => CrmSalesBoardCard::TYPES,
            'labelOptions' => CrmSalesBoardCard::LABELS,
            'automationCardTypes' => CrmAutomationRule::CARD_TYPES,
            'filters' => $request->only(['board_id', 'search', 'assigned_user_id', 'priority']),
        ]);
    }

    public function storeBoard(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'owner_user_id' => ['nullable', 'integer'],
            'description' => ['nullable', 'string'],
            'template' => ['nullable', 'in:blank,sales_pipeline,after_sales,project_sales'],
            'cover_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $user = Auth::user();
        $coverImagePath = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('crm-sales-boards', 'public')
            : null;

        $board = DB::transaction(function () use ($data, $user, $coverImagePath) {
            $position = (int) CrmSalesBoard::query()->where('tenant_id', $user->tenant_id)->max('position') + 1;
            $board = CrmSalesBoard::create([
                'tenant_id' => $user->tenant_id ?: $user->tenants_id,
                'organization_id' => $this->organizationId($user),
                'owner_user_id' => $data['owner_user_id'] ?: $user->id,
                'title' => $data['title'],
                'type' => $data['template'] === 'after_sales' ? 'after_sales' : 'sales_pipeline',
                'visibility' => 'team',
                'description' => $data['description'] ?? null,
                'cover_image_path' => $coverImagePath,
                'is_default' => CrmSalesBoard::query()->where('tenant_id', $user->tenant_id)->doesntExist(),
                'position' => $position,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            if (($data['template'] ?? 'sales_pipeline') !== 'blank') {
                $this->createTemplateLists($board, $user, $data['template'] ?? 'sales_pipeline');
            }

            return $board;
        });

        ActivityLogService::safeLog('create', 'CRM: Board', null, ['section' => 'crm', 'event_key' => 'crm.storeBoard']);

        Alert::success('ثبت شد', 'بورد کاریز فروش ساخته شد.');

        return redirect()->route('crm.sales-boards.index', ['board_id' => $board->id]);
    }

    public function storeList(Request $request)
    {
        $data = $request->validate([
            'board_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:140'],
            'probability_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'wip_limit' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $user = Auth::user();
        $board = $this->resolveBoard($data['board_id'], $user);
        $position = (int) CrmSalesBoardList::query()->where('board_id', $board->id)->max('position') + 1;

        CrmSalesBoardList::create([
            'board_id' => $board->id,
            'tenant_id' => $board->tenant_id,
            'organization_id' => $board->organization_id,
            'title' => $data['title'],
            'stage_key' => 'custom_' . $position,
            'color' => $data['color'] ?? '#7367f0',
            'probability_percent' => $data['probability_percent'] ?? 0,
            'wip_limit' => $data['wip_limit'] ?? null,
            'position' => $position,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        ActivityLogService::safeLog('create', 'CRM: List', null, ['section' => 'crm', 'event_key' => 'crm.storeList']);

        Alert::success('ثبت شد', 'لیست جدید به بورد اضافه شد.');

        return redirect()->route('crm.sales-boards.index', ['board_id' => $board->id]);
    }

    public function storeCard(Request $request)
    {
        $data = $request->validate([
            'board_id' => ['required', 'integer'],
            'list_id' => ['required', 'integer'],
            'customer_id' => ['nullable', 'integer'],
            'opportunity_id' => ['nullable', 'integer'],
            'assigned_user_id' => ['nullable', 'integer'],
            'assigned_user_ids' => ['nullable', 'array'],
            'assigned_user_ids.*' => ['integer'],
            'card_type' => ['nullable', 'in:' . implode(',', array_keys(CrmSalesBoardCard::TYPES))],
            'labels' => ['nullable', 'array'],
            'labels.*' => ['in:' . implode(',', array_keys(CrmSalesBoardCard::LABELS))],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:' . implode(',', array_keys(CrmSalesBoardCard::PRIORITIES))],
            'estimate_minutes' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'probability_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date_en' => ['nullable', 'date'],
            'next_action_date_en' => ['nullable', 'date'],
        ]);

        $user = Auth::user();
        $board = $this->resolveBoard($data['board_id'], $user);
        $list = $this->resolveList($data['list_id'], $board);
        $cardType = $data['card_type'] ?? 'task';
        $customer = $cardType !== 'task' && !empty($data['customer_id']) ? $this->resolveCustomer($data['customer_id'], $user) : null;
        $opportunity = $cardType !== 'task' && !empty($data['opportunity_id']) ? $this->resolveOpportunity($data['opportunity_id'], $user) : null;
        $expectedCloseDate = !empty($data['expected_close_date_en']) ? Carbon::parse($data['expected_close_date_en'])->toDateString() : null;
        $nextActionDate = !empty($data['next_action_date_en']) ? Carbon::parse($data['next_action_date_en'])->toDateString() : null;
        $position = (int) CrmSalesBoardCard::query()->where('list_id', $list->id)->max('position') + 1;
        $assignedUserIds = collect($data['assigned_user_ids'] ?? [])
            ->push($data['assigned_user_id'] ?? null)
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        if ($assignedUserIds->isEmpty()) {
            $assignedUserIds = collect([$board->owner_user_id ?: $user->id]);
        }

        $card = CrmSalesBoardCard::create([
            'board_id' => $board->id,
            'list_id' => $list->id,
            'tenant_id' => $board->tenant_id,
            'organization_id' => $board->organization_id,
            'customer_id' => optional($customer)->id ?: optional($opportunity)->customer_id,
            'opportunity_id' => optional($opportunity)->id,
            'assigned_user_id' => $assignedUserIds->first(),
            'assigned_user_ids' => $assignedUserIds->all(),
            'card_type' => $cardType ?: ($opportunity ? 'opportunity' : ($customer ? 'customer' : 'task')),
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'],
            'estimate_minutes' => $data['estimate_minutes'] ?? null,
            'status' => $list->final_status ?: 'open',
            'amount' => $cardType === 'task' ? 0 : ($data['amount'] ?? optional($opportunity)->amount ?? 0),
            'probability_percent' => $cardType === 'task' ? 0 : ($data['probability_percent'] ?? $list->probability_percent),
            'expected_close_date_en' => $cardType === 'task' ? null : $expectedCloseDate,
            'expected_close_date_fa' => $cardType === 'task' || !$expectedCloseDate ? null : verta($expectedCloseDate)->format('Y/m/d'),
            'next_action_date_en' => $nextActionDate,
            'next_action_date_fa' => $nextActionDate ? verta($nextActionDate)->format('Y/m/d') : null,
            'labels' => array_values($data['labels'] ?? []),
            'activity_logs' => [[
                'type' => 'created',
                'user_id' => $user->id,
                'user_name' => $user->name,
                'list_id' => $list->id,
                'list_title' => $list->title,
                'at' => now()->toDateTimeString(),
            ]],
            'position' => $position,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        app(PanelNotificationService::class)->dispatch(
            $card->card_type === 'customer' ? 'crm_customer_card_created' : 'crm_card_created',
            $assignedUserIds->push($board->owner_user_id)->all(),
            $this->notificationPayload($card, $board, $list, $user),
            $board->tenant_id
        );

        ActivityLogService::safeLog('create', 'CRM: Card', null, ['section' => 'crm', 'event_key' => 'crm.storeCard']);

        Alert::success('ثبت شد', 'کارت فروش به کاریز اضافه شد.');

        return redirect()->route('crm.sales-boards.index', ['board_id' => $board->id]);
    }

    public function storeAutomationRule(Request $request)
    {
        $data = $request->validate([
            'board_id' => ['required', 'integer'],
            'list_id' => ['nullable', 'integer'],
            'card_type' => ['nullable', 'in:task,customer,opportunity'],
            'assigned_user_id' => ['nullable', 'integer'],
            'due_days' => ['required', 'integer', 'min:0', 'max:90'],
            'priority' => ['required', 'in:' . implode(',', array_keys(CrmSalesBoardCard::PRIORITIES))],
            'title_template' => ['required', 'string', 'max:220'],
            'description_template' => ['nullable', 'string', 'max:2000'],
            'notify_assignee' => ['nullable', 'boolean'],
            'notify_board_owner' => ['nullable', 'boolean'],
            'escalate_to_manager' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $board = $this->resolveBoard($data['board_id'], $user);
        $list = !empty($data['list_id']) ? $this->resolveList($data['list_id'], $board) : null;

        CrmAutomationRule::create([
            'board_id' => $board->id,
            'list_id' => optional($list)->id,
            'tenant_id' => $board->tenant_id,
            'organization_id' => $board->organization_id,
            'trigger_event' => 'card_moved_to_list',
            'card_type' => $data['card_type'] ?? null,
            'action_type' => 'create_followup',
            'assigned_user_id' => $data['assigned_user_id'] ?? null,
            'due_days' => $data['due_days'],
            'priority' => $data['priority'],
            'title_template' => $data['title_template'],
            'description_template' => $data['description_template'] ?? null,
            'notify_assignee' => $request->boolean('notify_assignee'),
            'notify_board_owner' => $request->boolean('notify_board_owner'),
            'escalate_to_manager' => $request->boolean('escalate_to_manager'),
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        ActivityLogService::safeLog('create', 'CRM: Automation Rule', null, ['section' => 'crm', 'event_key' => 'crm.storeAutomationRule']);

        Alert::success('ثبت شد', 'قانون اتوماسیون CRM برای بورد ساخته شد.');

        return redirect()->route('crm.sales-boards.index', ['board_id' => $board->id]);
    }

    public function toggleAutomationRule(CrmAutomationRule $rule)
    {
        $user = Auth::user();
        $this->resolveBoard($rule->board_id, $user);

        $rule->update([
            'is_active' => !$rule->is_active,
            'updated_by' => $user->id,
        ]);

        Alert::success('بروزرسانی شد', 'وضعیت قانون اتوماسیون تغییر کرد.');

        return redirect()->route('crm.sales-boards.index', ['board_id' => $rule->board_id]);
    }

    public function storeCustomers(Request $request)
    {
        $data = $request->validate([
            'board_id' => ['required', 'integer'],
            'list_id' => ['required', 'integer'],
            'assigned_user_id' => ['nullable', 'integer'],
            'customer_mode' => ['required', 'in:selected,active,inactive,new,region,area'],
            'customer_ids' => ['nullable', 'array'],
            'customer_ids.*' => ['integer'],
            'region_id' => ['nullable', 'integer'],
            'area_id' => ['nullable', 'integer'],
            'labels' => ['nullable', 'array'],
            'labels.*' => ['in:' . implode(',', array_keys(CrmSalesBoardCard::LABELS))],
            'priority' => ['required', 'in:' . implode(',', array_keys(CrmSalesBoardCard::PRIORITIES))],
        ]);

        $user = Auth::user();
        $board = $this->resolveBoard($data['board_id'], $user);
        $list = $this->resolveList($data['list_id'], $board);
        $customersQuery = Customers::query()->select(['id', 'name', 'mobile', 'status', 'region_id', 'area', 'tenant_id', 'organization_id']);

        if ((int) $user->isGod !== 1) {
            $customersQuery->forOrganizations($user);
        }

        match ($data['customer_mode']) {
            'selected' => $customersQuery->whereIn('id', $data['customer_ids'] ?? []),
            'active' => $customersQuery->where('status', 1),
            'inactive' => $customersQuery->where(function ($query) {
                $query->where('status', 0)->orWhereNull('status');
            }),
            'new' => $customersQuery->where('created_at', '>=', now()->subDays(30)),
            'region' => $customersQuery->where('region_id', $data['region_id'] ?? 0),
            'area' => $customersQuery->where('area', $data['area_id'] ?? 0),
        };

        $customers = $customersQuery->orderByDesc('id')->limit(500)->get();
        $position = (int) CrmSalesBoardCard::query()->where('list_id', $list->id)->max('position');
        $created = 0;

        DB::transaction(function () use ($customers, $board, $list, $data, $user, &$position, &$created) {
            foreach ($customers as $customer) {
                $exists = CrmSalesBoardCard::query()
                    ->where('board_id', $board->id)
                    ->where('customer_id', $customer->id)
                    ->where('card_type', 'customer')
                    ->exists();

                if ($exists) {
                    continue;
                }

                $position++;
                CrmSalesBoardCard::create([
                    'board_id' => $board->id,
                    'list_id' => $list->id,
                    'tenant_id' => $board->tenant_id,
                    'organization_id' => $board->organization_id,
                    'customer_id' => $customer->id,
                    'assigned_user_id' => $data['assigned_user_id'] ?: $board->owner_user_id ?: $user->id,
                    'assigned_user_ids' => [$data['assigned_user_id'] ?: $board->owner_user_id ?: $user->id],
                    'card_type' => 'customer',
                    'title' => $customer->name,
                    'description' => $customer->mobile ? 'مشتری: ' . $customer->mobile : null,
                    'priority' => $data['priority'],
                    'labels' => array_values($data['labels'] ?? []),
                    'status' => 'open',
                    'probability_percent' => $list->probability_percent,
                    'position' => $position,
                    'source_filter' => [
                        'mode' => $data['customer_mode'],
                        'region_id' => $data['region_id'] ?? null,
                        'area_id' => $data['area_id'] ?? null,
                    ],
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
                $created++;
            }
        });

        ActivityLogService::safeLog('create', 'CRM: Customers', null, ['section' => 'crm', 'event_key' => 'crm.storeCustomers']);

        Alert::success('ثبت شد', number_format($created) . ' مشتری به لیست اضافه شد.');

        if ($created > 0) {
            app(PanelNotificationService::class)->dispatch('crm_customer_card_created', [
                $data['assigned_user_id'] ?: $board->owner_user_id ?: $user->id,
            ], [
                'tenant_id' => $board->tenant_id,
                'card_title' => number_format($created) . ' مشتری',
                'board_title' => $board->title,
                'to_list' => $list->title,
                'actor_name' => $user->name,
                'time' => now()->format('H:i'),
                'reference_type' => CrmSalesBoard::class,
                'reference_id' => $board->id,
            ], $board->tenant_id);
        }

        return redirect()->route('crm.sales-boards.index', ['board_id' => $board->id]);
    }

    public function moveCard(Request $request, CrmSalesBoardCard $card)
    {
        $user = Auth::user();
        $this->authorizeCard($card, $user);

        $data = $request->validate([
            'list_id' => ['required', 'integer'],
            'position' => ['nullable', 'integer', 'min:0'],
            'lost_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $fromList = $card->list;
        $targetList = $this->resolveList($data['list_id'], $card->board);
        $automation = app(CrmAdvancedAutomationService::class);

        if ($targetList->final_status === 'lost') {
            $automation->assertLostReason($data['lost_reason'] ?? $card->lost_reason, $card->tenant_id);
        }
        $position = $data['position'] ?? ((int) CrmSalesBoardCard::query()->where('list_id', $targetList->id)->max('position') + 1);
        $now = now();
        $activityLogs = $card->activity_logs ?: [];
        $activityLogs[] = [
            'type' => 'moved',
            'user_id' => $user->id,
            'user_name' => $user->name,
            'from_list_id' => optional($fromList)->id,
            'from_list_title' => optional($fromList)->title,
            'to_list_id' => $targetList->id,
            'to_list_title' => $targetList->title,
            'at' => $now->toDateTimeString(),
        ];
        $startedAt = $card->started_at;
        $endedAt = $card->ended_at;
        $startTriggered = false;
        $endTriggered = false;
        $status = $targetList->final_status ?: ($card->status === 'won' || $card->status === 'lost' ? 'open' : $card->status);
        $lostReason = $targetList->final_status === 'lost'
            ? ($data['lost_reason'] ?? $card->lost_reason)
            : $card->lost_reason;

        if ($card->card_type === 'task') {
            if (!$targetList->final_status && !$startedAt && (int) $targetList->id !== (int) $fromList?->id) {
                $startedAt = $now;
                $startTriggered = true;
                $status = 'in_progress';
                $activityLogs[] = [
                    'type' => 'started',
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'list_id' => $targetList->id,
                    'list_title' => $targetList->title,
                    'at' => $now->toDateTimeString(),
                ];
            }

            if ($targetList->final_status && !$endedAt) {
                $endedAt = $now;
                $endTriggered = true;
                $activityLogs[] = [
                    'type' => 'ended',
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'list_id' => $targetList->id,
                    'list_title' => $targetList->title,
                    'at' => $now->toDateTimeString(),
                ];
            }
        }

        $card->update([
            'list_id' => $targetList->id,
            'status' => $status,
            'lost_reason' => $lostReason,
            'probability_percent' => $targetList->probability_percent ?: $card->probability_percent,
            'position' => $position,
            'moved_at' => $now,
            'started_at' => $startedAt,
            'ended_at' => $endedAt,
            'activity_logs' => $activityLogs,
            'updated_by' => $user->id,
        ]);

        $pishfactor = $status === 'won'
            ? $automation->convertWonCardToPishfactor($card->fresh(['customer', 'board']), $user)
            : null;

        $recipients = collect($card->assigned_user_ids ?: [])
            ->push($card->assigned_user_id)
            ->push($card->created_by)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $payload = $this->notificationPayload($card->fresh(), $card->board, $targetList, $user, optional($fromList)->title);
        app(PanelNotificationService::class)->dispatch('crm_card_moved', $recipients, $payload, $card->tenant_id);

        if ($card->card_type === 'task' && $startTriggered) {
            app(PanelNotificationService::class)->dispatch('crm_task_started', $recipients, $payload, $card->tenant_id);
        }

        if ($card->card_type === 'task' && $endTriggered) {
            app(PanelNotificationService::class)->dispatch('crm_task_finished', $recipients, $payload, $card->tenant_id);
        }

        app(CrmCardAutomationService::class)->handleCardMoved($card->fresh(['board', 'list', 'customer']), $fromList, $targetList, $user);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'pishfactor_id' => $pishfactor?->id,
                'pishfactor_url' => $pishfactor ? url('/pishFactorInfo/' . $pishfactor->id) : null,
            ]);
        }

        if ($pishfactor) {
            Alert::success('کارت برده شد', 'پیش‌فاکتور #' . $pishfactor->invoiceID . ' از کارت کاریز ساخته شد.');
        }

        Alert::success('بروزرسانی شد', 'کارت به لیست جدید منتقل شد.');

        return redirect()->route('crm.sales-boards.index', ['board_id' => $card->board_id]);
    }

    public function showCard(CrmSalesBoardCard $card)
    {
        $user = Auth::user();
        $this->authorizeCard($card, $user);

        $card->load([
            'board',
            'list',
            'customer',
            'opportunity',
            'assignedUser',
            'checklistItems.creator',
            'checklistItems.doneBy',
            'comments.user',
            'attachments.user',
        ]);

        $usersQuery = User::query()->select(['id', 'name', 'tenant_id', 'organization_id', 'isActive'])->where('isActive', 1)->orderBy('name')->limit(200);

        if ((int) $user->isGod !== 1) {
            $usersQuery->forOrganizations($user);
        }

        return view('crm.sales_boards.show_card', [
            'card' => $card,
            'users' => $usersQuery->get(),
            'priorities' => CrmSalesBoardCard::PRIORITIES,
            'statuses' => CrmSalesBoardCard::STATUSES,
            'labelOptions' => CrmSalesBoardCard::LABELS,
        ]);
    }

    public function updateCard(Request $request, CrmSalesBoardCard $card)
    {
        $user = Auth::user();
        $this->authorizeCard($card, $user);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'assigned_user_id' => ['nullable', 'integer'],
            'assigned_user_ids' => ['nullable', 'array'],
            'assigned_user_ids.*' => ['integer'],
            'labels' => ['nullable', 'array'],
            'labels.*' => ['in:' . implode(',', array_keys(CrmSalesBoardCard::LABELS))],
            'priority' => ['required', 'in:' . implode(',', array_keys(CrmSalesBoardCard::PRIORITIES))],
            'status' => ['required', 'in:' . implode(',', array_keys(CrmSalesBoardCard::STATUSES))],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'probability_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date_en' => ['nullable', 'date'],
            'next_action_date_en' => ['nullable', 'date'],
            'lost_reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['status'] === 'lost') {
            app(CrmAdvancedAutomationService::class)->assertLostReason($data['lost_reason'] ?? null, $card->tenant_id);
        }

        $assignedUserIds = collect($data['assigned_user_ids'] ?? [])
            ->push($data['assigned_user_id'] ?? null)
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values();

        $expectedCloseDate = !empty($data['expected_close_date_en']) ? Carbon::parse($data['expected_close_date_en'])->toDateString() : null;
        $nextActionDate = !empty($data['next_action_date_en']) ? Carbon::parse($data['next_action_date_en'])->toDateString() : null;

        $card->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'assigned_user_id' => $assignedUserIds->first(),
            'assigned_user_ids' => $assignedUserIds->all(),
            'labels' => array_values($data['labels'] ?? []),
            'priority' => $data['priority'],
            'status' => $data['status'],
            'lost_reason' => $data['lost_reason'] ?? $card->lost_reason,
            'amount' => $card->card_type === 'task' ? 0 : ($data['amount'] ?? 0),
            'probability_percent' => $card->card_type === 'task' ? 0 : ($data['probability_percent'] ?? 0),
            'expected_close_date_en' => $card->card_type === 'task' ? null : $expectedCloseDate,
            'expected_close_date_fa' => $card->card_type === 'task' || !$expectedCloseDate ? null : verta($expectedCloseDate)->format('Y/m/d'),
            'next_action_date_en' => $nextActionDate,
            'next_action_date_fa' => $nextActionDate ? verta($nextActionDate)->format('Y/m/d') : null,
            'updated_by' => $user->id,
        ]);

        $this->appendCardActivity($card, 'updated', $user, ['status' => $data['status']]);

        if ($data['status'] === 'won') {
            $pishfactor = app(CrmAdvancedAutomationService::class)->convertWonCardToPishfactor($card->fresh(['customer', 'board']), $user);
            if ($pishfactor) {
                ActivityLogService::safeLog('update', 'CRM: Card', null, ['section' => 'crm', 'event_key' => 'crm.updateCard']);
                Alert::success('بروزرسانی شد', 'کارت برده شد و پیش‌فاکتور #' . $pishfactor->invoiceID . ' ساخته شد.');
                return redirect()->route('crm.sales-boards.cards.show', $card);
            }
        }

        Alert::success('بروزرسانی شد', 'جزئیات کارت کاریز ذخیره شد.');

        return redirect()->route('crm.sales-boards.cards.show', $card);
    }

    public function storeChecklistItem(Request $request, CrmSalesBoardCard $card)
    {
        $user = Auth::user();
        $this->authorizeCard($card, $user);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:220'],
        ]);

        $position = (int) CrmSalesBoardCardChecklistItem::query()->where('card_id', $card->id)->max('position') + 1;

        CrmSalesBoardCardChecklistItem::create([
            'card_id' => $card->id,
            'tenant_id' => $card->tenant_id,
            'organization_id' => $card->organization_id,
            'title' => $data['title'],
            'position' => $position,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->appendCardActivity($card, 'checklist_added', $user, ['title' => $data['title']]);

        ActivityLogService::safeLog('create', 'CRM: Checklist Item', null, ['section' => 'crm', 'event_key' => 'crm.storeChecklistItem']);

        Alert::success('ثبت شد', 'آیتم چک لیست به کارت اضافه شد.');

        return redirect()->route('crm.sales-boards.cards.show', $card);
    }

    public function updateChecklistItem(Request $request, CrmSalesBoardCard $card, CrmSalesBoardCardChecklistItem $item)
    {
        $user = Auth::user();
        $this->authorizeCard($card, $user);
        abort_unless((int) $item->card_id === (int) $card->id, 404);

        $data = $request->validate([
            'is_done' => ['required', 'boolean'],
        ]);

        $isDone = (bool) $data['is_done'];
        $item->update([
            'is_done' => $isDone,
            'done_at' => $isDone ? now() : null,
            'done_by' => $isDone ? $user->id : null,
            'updated_by' => $user->id,
        ]);

        $this->appendCardActivity($card, $isDone ? 'checklist_done' : 'checklist_reopened', $user, ['title' => $item->title]);

        return redirect()->route('crm.sales-boards.cards.show', $card);
    }

    public function storeComment(Request $request, CrmSalesBoardCard $card)
    {
        $user = Auth::user();
        $this->authorizeCard($card, $user);

        $data = $request->validate([
            'comment' => ['required', 'string', 'max:5000'],
            'mentions' => ['nullable', 'array'],
            'mentions.*' => ['integer'],
        ]);

        CrmSalesBoardCardComment::create([
            'card_id' => $card->id,
            'tenant_id' => $card->tenant_id,
            'organization_id' => $card->organization_id,
            'user_id' => $user->id,
            'comment' => $data['comment'],
            'mentions' => array_values($data['mentions'] ?? []),
        ]);

        $this->appendCardActivity($card, 'comment_added', $user);

        app(PanelNotificationService::class)->dispatch(
            'crm_card_comment_added',
            collect($card->assigned_user_ids ?: [])->push($card->assigned_user_id)->merge($data['mentions'] ?? [])->filter()->unique()->values()->all(),
            $this->notificationPayload($card, $card->board, $card->list, $user),
            $card->tenant_id
        );

        ActivityLogService::safeLog('create', 'CRM: Comment', null, ['section' => 'crm', 'event_key' => 'crm.storeComment']);

        Alert::success('ثبت شد', 'کامنت روی کارت ثبت شد.');

        return redirect()->route('crm.sales-boards.cards.show', $card);
    }

    public function storeAttachment(Request $request, CrmSalesBoardCard $card)
    {
        $user = Auth::user();
        $this->authorizeCard($card, $user);

        $data = $request->validate([
            'attachment' => ['required', 'file', 'max:5120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $file = $data['attachment'];
        $path = $file->store('crm-card-attachments/' . $card->id, 'public');

        CrmSalesBoardCardAttachment::create([
            'card_id' => $card->id,
            'tenant_id' => $card->tenant_id,
            'organization_id' => $card->organization_id,
            'user_id' => $user->id,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'description' => $data['description'] ?? null,
        ]);

        $this->appendCardActivity($card, 'attachment_added', $user, ['file' => $file->getClientOriginalName()]);

        ActivityLogService::safeLog('create', 'CRM: Attachment', null, ['section' => 'crm', 'event_key' => 'crm.storeAttachment']);

        Alert::success('ثبت شد', 'پیوست کارت بارگذاری شد.');

        return redirect()->route('crm.sales-boards.cards.show', $card);
    }

    public function reorderLists(Request $request)
    {
        $data = $request->validate([
            'board_id' => ['required', 'integer'],
            'list_order' => ['required', 'array'],
            'list_order.*' => ['integer'],
        ]);

        $user = Auth::user();
        $board = $this->resolveBoard($data['board_id'], $user);
        $allowedIds = CrmSalesBoardList::query()->where('board_id', $board->id)->pluck('id')->map(fn($id) => (int) $id)->all();

        DB::transaction(function () use ($data, $allowedIds, $user) {
            foreach (array_values($data['list_order']) as $index => $listId) {
                if (!in_array((int) $listId, $allowedIds, true)) {
                    continue;
                }

                CrmSalesBoardList::query()->whereKey($listId)->update([
                    'position' => $index + 1,
                    'updated_by' => $user->id,
                ]);
            }
        });

        return response()->json(['ok' => true]);
    }

    private function createTemplateLists(CrmSalesBoard $board, $user, string $template): void
    {
        $templates = [
            'sales_pipeline' => [
                ['سرنخ اولیه', 'new', 10, '#6c757d', null],
                ['پیگیری', 'followup', 25, '#00bad1', null],
                ['ارسال پیشنهاد/پیش فاکتور', 'proposal', 45, '#7367f0', null],
                ['مذاکره و تایید', 'negotiation', 70, '#ff9f43', null],
                ['برنده شده', 'won', 100, '#28c76f', 'won'],
                ['از دست رفته', 'lost', 0, '#ea5455', 'lost'],
            ],
            'after_sales' => [
                ['درخواست جدید', 'ticket_new', 10, '#6c757d', null],
                ['در حال بررسی', 'checking', 35, '#00bad1', null],
                ['ارجاع به کارشناس', 'assigned', 55, '#7367f0', null],
                ['منتظر مشتری', 'waiting_customer', 65, '#ff9f43', null],
                ['حل شده', 'done', 100, '#28c76f', 'won'],
            ],
            'project_sales' => [
                ['مقدار اولیه', 'lead', 10, '#6c757d', null],
                ['برآورد نیاز', 'analysis', 30, '#00bad1', null],
                ['پیشنهاد فنی/مالی', 'proposal', 50, '#7367f0', null],
                ['تایید پیش فاکتور', 'approval', 75, '#ff9f43', null],
                ['ارسال به برنامه ریزی و تولید', 'production', 90, '#28c76f', null],
            ],
        ];

        foreach ($templates[$template] ?? $templates['sales_pipeline'] as $index => $item) {
            CrmSalesBoardList::create([
                'board_id' => $board->id,
                'tenant_id' => $board->tenant_id,
                'organization_id' => $board->organization_id,
                'title' => $item[0],
                'stage_key' => $item[1],
                'probability_percent' => $item[2],
                'color' => $item[3],
                'position' => $index + 1,
                'is_final' => !empty($item[4]),
                'final_status' => $item[4],
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        }
    }

    private function resolveBoard($boardId, $user): CrmSalesBoard
    {
        $query = CrmSalesBoard::query();

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->findOrFail($boardId);
    }

    private function resolveList($listId, CrmSalesBoard $board): CrmSalesBoardList
    {
        return CrmSalesBoardList::query()->where('board_id', $board->id)->findOrFail($listId);
    }

    private function resolveCustomer($customerId, $user): Customers
    {
        $query = Customers::query();

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->findOrFail($customerId);
    }

    private function resolveOpportunity($opportunityId, $user): CrmOpportunity
    {
        $query = CrmOpportunity::query();

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->findOrFail($opportunityId);
    }

    private function authorizeCard(CrmSalesBoardCard $card, $user): void
    {
        if ((int) $user->isGod === 1) {
            return;
        }

        abort_unless(CrmSalesBoardCard::query()->whereKey($card->id)->forOrganizations($user)->exists(), 403);
    }

    private function notificationPayload(CrmSalesBoardCard $card, CrmSalesBoard $board, CrmSalesBoardList $list, $user, ?string $fromListTitle = null): array
    {
        return [
            'tenant_id' => $board->tenant_id,
            'card_title' => $card->title,
            'board_title' => $board->title,
            'from_list' => $fromListTitle,
            'to_list' => $list->title,
            'actor_name' => $user->name,
            'time' => now()->format('H:i'),
            'reference_type' => CrmSalesBoardCard::class,
            'reference_id' => $card->id,
        ];
    }

    private function appendCardActivity(CrmSalesBoardCard $card, string $type, $user, array $meta = []): void
    {
        $activityLogs = $card->activity_logs ?: [];
        $activityLogs[] = array_merge([
            'type' => $type,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'at' => now()->toDateTimeString(),
        ], $meta);

        $card->forceFill(['activity_logs' => $activityLogs])->save();
    }

    private function organizationId($user): ?int
    {
        if (!$user || empty($user->organization_id)) {
            return null;
        }

        $decoded = json_decode((string) $user->organization_id, true);

        if (is_array($decoded)) {
            return (int) ($decoded[0] ?? 0) ?: null;
        }

        return (int) $user->organization_id ?: null;
    }
}
