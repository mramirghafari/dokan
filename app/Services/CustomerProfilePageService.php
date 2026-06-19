<?php

namespace App\Services;

use App\Models\CrmFollowup;
use App\Models\CrmOpportunity;
use App\Models\Customers;
use App\Models\Pishfactor;
use App\Models\Tasks;
use App\Models\TenantUserMembership;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CustomerProfilePageService
{
    public function __construct(
        private readonly CustomerListColumnService $columnService,
    ) {}

    public function build(Customers $customer, ?User $viewer = null, ?Tasks $task = null): array
    {
        $viewer = $viewer ?: auth()->user();
        $customer->loadMissing([
            'region:id,name,leader_id',
            'region.leader:id,name,mobile',
            'Area:id,name,leader_id,region_id',
            'Area.leader:id,name,mobile',
            'Area.region:id,name,leader_id',
            'Area.region.leader:id,name,mobile',
            'leader:id,name,mobile',
            'creator:id,name,mobile,leader_id',
            'creator.leader:id,name,mobile',
            'latestPishfactor.visitor:id,name,mobile',
            'latestPishfactor.leader:id,name,mobile',
        ]);

        $tenantId = $customer->tenant_id ?: $viewer?->tenant_id ?: $viewer?->tenants_id;
        $isSubscriptionPanel = $this->columnService->isSubscriptionPanel($tenantId ? (int) $tenantId : null);
        $locationTabEnabled = TenantSettings::enabled('feature_customer_location_map', $tenantId ? (int) $tenantId : null);

        $orders = Pishfactor::query()
            ->where('customer_id', $customer->id)
            ->with([
                'visitor:id,name',
                'leader:id,name',
                'items.product:id,title,pr_unit,pack_items,display_name',
            ])
            ->orderByDesc('id')
            ->get();

        $acceptedOrders = $orders->whereIn('status', [1, 4]);
        $purchasesCount = $orders->count();
        $financial = $this->buildFinancialSummary($acceptedOrders);
        $subscription = $this->buildSubscriptionSummary($acceptedOrders, $isSubscriptionPanel);

        $crmFollowupsQuery = CrmFollowup::query()->with(['assignedUser', 'creator'])->where('customer_id', $customer->id);
        $crmOpportunitiesQuery = CrmOpportunity::query()->with(['assignedUser'])->where('customer_id', $customer->id);

        if ($viewer && (int) $viewer->isGod !== 1) {
            $crmFollowupsQuery->forOrganizations($viewer);
            $crmOpportunitiesQuery->forOrganizations($viewer);
        }

        $crmFollowups = (clone $crmFollowupsQuery)
            ->orderByRaw("FIELD(status, 'open', 'in_progress', 'done', 'canceled')")
            ->orderByRaw('due_date_en IS NULL')
            ->orderBy('due_date_en')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $crmOpportunities = (clone $crmOpportunitiesQuery)
            ->orderByRaw("FIELD(status, 'open', 'won', 'lost', 'canceled')")
            ->orderByRaw("FIELD(stage, 'negotiation', 'proposal', 'qualified', 'new', 'won', 'lost')")
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $marketerUser = $this->resolveMarketerUser($customer);
        $supervisorUser = $this->resolveSupervisorUser($customer, $marketerUser);
        $salesManagerUser = $this->resolveSalesManagerUser($customer, $marketerUser, $supervisorUser);
        $marketerName = $marketerUser['name'] ?? optional($customer->creator)->name;

        return [
            'customer' => $customer,
            'myTask' => $task,
            'taskContext' => $this->taskContext($task),
            'isSubscriptionPanel' => $isSubscriptionPanel,
            'locationTabEnabled' => $locationTabEnabled,
            'badges' => [
                'active' => (int) $customer->status === 1,
                'loyal' => $purchasesCount > 1,
            ],
            'metrics' => [
                'orders_total' => $orders->count(),
                'orders_active' => $orders->whereIn('status', [1, 4])->count(),
                'revenue_total' => $acceptedOrders->sum(fn (Pishfactor $order) => $this->orderAmount($order)),
                'purchases_count' => $purchasesCount,
                'account_balance' => $financial['unsettled_amount'],
                'subscription' => $subscription,
            ],
            'financial' => $financial,
            'orders' => $orders->map(fn (Pishfactor $order) => $this->mapOrderRow($order))->values(),
            'crm' => [
                'stats' => [
                    'open_followups' => (clone $crmFollowupsQuery)->whereIn('status', ['open', 'in_progress'])->count(),
                    'overdue_followups' => (clone $crmFollowupsQuery)->whereIn('status', ['open', 'in_progress'])->whereDate('due_date_en', '<', now()->toDateString())->count(),
                    'open_opportunities' => (clone $crmOpportunitiesQuery)->where('status', 'open')->count(),
                    'open_opportunity_amount' => (clone $crmOpportunitiesQuery)->where('status', 'open')->sum('amount'),
                ],
                'followups' => $crmFollowups,
                'opportunities' => $crmOpportunities,
            ],
            'team' => [
                'marketer' => $marketerName,
                'registrar' => optional($customer->creator)->name,
                'leader' => $supervisorUser['name'] ?? null,
                'sales_manager' => $salesManagerUser['name'] ?? null,
                'region' => optional($customer->region)->name,
                'area' => optional($customer->Area)->name,
            ],
            'assignments' => [
                'marketer' => $marketerUser,
                'supervisor' => $supervisorUser,
                'sales_manager' => $salesManagerUser,
            ],
            'canDelete' => $viewer && (int) $viewer->isAdmin === 1 && $orders->whereIn('status', [1, 4])->isEmpty(),
            'canNewOrder' => !$locationTabEnabled
                || ($customer->shop_lat !== null && $customer->shop_lng !== null),
        ];
    }

    public function orderAmount(Pishfactor $order): int
    {
        return (int) str_replace([',', ' '], '', (string) ($order->fullPrice ?: 0));
    }

    public function orderStatusMeta(int $status): array
    {
        return match ($status) {
            0 => ['label' => 'در انتظار تأیید', 'class' => 'warning'],
            1 => ['label' => 'تأیید شده', 'class' => 'success'],
            3 => ['label' => 'رد شده', 'class' => 'danger'],
            4 => ['label' => 'تحویل شده', 'class' => 'success'],
            5 => ['label' => 'مرجوعی', 'class' => 'secondary'],
            default => ['label' => 'نامشخص', 'class' => 'secondary'],
        };
    }

    public function paymentTypeMeta(mixed $paymentType): array
    {
        return match ((int) $paymentType) {
            1 => ['label' => 'نقدی', 'class' => 'success'],
            2 => ['label' => 'چکی', 'class' => 'info'],
            4 => ['label' => 'کارت/بانک', 'class' => 'primary'],
            default => ['label' => 'تسویه نشده', 'class' => 'danger'],
        };
    }

    private function buildFinancialSummary(Collection $acceptedOrders): array
    {
        $balance = 0;
        $unsettled = 0;
        $totalPurchases = 0;
        $totalPayments = 0;
        $transactions = [];

        foreach ($acceptedOrders as $order) {
            $amount = $this->orderAmount($order);
            $isSettled = $this->orderIsSettled($order);
            $date = $order->created_at;

            $totalPurchases += $amount;

            $transactions[] = [
                'type' => 'charge',
                'type_label' => 'خرید / فاکتور',
                'type_class' => 'danger',
                'order_id' => $order->id,
                'invoice_id' => $order->invoiceID,
                'description' => 'سفارش #' . $order->id . ($order->invoiceID ? ' — فاکتور ' . $order->invoiceID : ''),
                'debit' => $amount,
                'credit' => 0,
                'amount' => $amount,
                'payment' => $this->paymentTypeMeta($order->payment_type),
                'is_settled' => $isSettled,
                'date' => $date,
                'view_url' => route('pishFactorView', $order->id),
            ];

            $balance -= $amount;

            if ($isSettled) {
                $totalPayments += $amount;
                $balance += $amount;

                $transactions[] = [
                    'type' => 'payment',
                    'type_label' => 'واریز / تسویه',
                    'type_class' => 'success',
                    'order_id' => $order->id,
                    'invoice_id' => $order->invoiceID,
                    'description' => 'تسویه سفارش #' . $order->id,
                    'debit' => 0,
                    'credit' => $amount,
                    'amount' => $amount,
                    'payment' => $this->paymentTypeMeta($order->payment_type),
                    'is_settled' => true,
                    'date' => $date,
                    'view_url' => route('pishFactorView', $order->id),
                ];
            } else {
                $unsettled += $amount;
            }
        }

        usort($transactions, function (array $a, array $b) {
            return ($b['date']?->timestamp ?? 0) <=> ($a['date']?->timestamp ?? 0)
                ?: ($b['order_id'] <=> $a['order_id'])
                ?: ($b['type'] <=> $a['type']);
        });

        $status = $balance > 0 ? 'creditor' : ($balance < 0 ? 'debtor' : 'settled');

        return [
            'balance' => $balance,
            'unsettled_amount' => $unsettled,
            'total_purchases' => $totalPurchases,
            'total_payments' => $totalPayments,
            'status' => $status,
            'status_label' => match ($status) {
                'creditor' => 'بستانکار',
                'debtor' => 'بدهکار',
                default => 'تسویه',
            },
            'entries' => array_values(array_filter($transactions, fn (array $row) => $row['type'] === 'charge')),
            'transactions' => $transactions,
        ];
    }

    private function orderIsSettled(Pishfactor $order): bool
    {
        $paymentType = (int) ($order->payment_type ?? 3);

        return in_array((string) $order->settlement_status, ['settled', 'paid'], true)
            || in_array($paymentType, [1, 2, 4], true);
    }

    private function buildSubscriptionSummary(Collection $acceptedOrders, bool $isSubscriptionPanel): ?array
    {
        if (!$isSubscriptionPanel) {
            return null;
        }

        $latestEnd = $acceptedOrders
            ->pluck('recive_date_en')
            ->filter()
            ->map(fn ($date) => Carbon::parse($date))
            ->sortDesc()
            ->first();

        if (!$latestEnd) {
            return [
                'end_date' => null,
                'days_remaining' => null,
                'label' => 'ثبت نشده',
                'class' => 'secondary',
            ];
        }

        $daysRemaining = now()->startOfDay()->diffInDays($latestEnd->copy()->startOfDay(), false);

        if ($daysRemaining > 0) {
            return [
                'end_date' => $latestEnd,
                'days_remaining' => $daysRemaining,
                'label' => number_format($daysRemaining) . ' روز مانده',
                'class' => 'success',
            ];
        }

        if ($daysRemaining === 0) {
            return [
                'end_date' => $latestEnd,
                'days_remaining' => 0,
                'label' => 'امروز پایان می‌یابد',
                'class' => 'warning',
            ];
        }

        return [
            'end_date' => $latestEnd,
            'days_remaining' => $daysRemaining,
            'label' => 'منقضی (' . number_format(abs($daysRemaining)) . ' روز)',
            'class' => 'danger',
        ];
    }

    private function mapOrderRow(Pishfactor $order): array
    {
        $items = $order->items->map(function ($item) {
            $product = $item->product;
            $qty = (int) $item->tedad + ((int) $item->pack * (int) optional($product)->pack_items);

            return [
                'title' => trim((optional($product)->title ?: 'کالا') . ' ' . (optional($product)->display_name ?: '')),
                'quantity' => $qty,
                'unit' => optional($product)->pr_unit ?: 'عدد',
                'line_total' => (int) str_replace([',', ' '], '', (string) ($item->line_total ?: $item->price ?: 0)),
            ];
        });

        return [
            'id' => $order->id,
            'invoice_id' => $order->invoiceID,
            'amount' => $this->orderAmount($order),
            'status' => $this->orderStatusMeta((int) $order->status),
            'payment' => $this->paymentTypeMeta($order->payment_type),
            'settlement_status' => $order->settlement_status,
            'visitor' => optional($order->visitor)->name,
            'leader' => optional($order->leader)->name,
            'created_at' => $order->created_at,
            'delivery_date' => $order->recive_date ?: optional($order->recive_date_en)?->format('Y/m/d'),
            'subscription_end' => $order->recive_date_en,
            'items' => $items,
            'items_count' => $items->count(),
            'view_url' => route('pishFactorView', $order->id),
        ];
    }

    private function taskContext(?Tasks $task): ?array
    {
        if (!$task) {
            return null;
        }

        $task->loadMissing('area.region');

        return [
            'region' => optional(optional($task->area)->region)->name,
            'area' => optional($task->area)->name,
        ];
    }

    private function resolveMarketerUser(Customers $customer): ?array
    {
        $visitorId = optional($customer->latestPishfactor)->visitor_id;

        if ($visitorId) {
            return $this->mapAssignedUser(User::query()->find($visitorId));
        }

        if ($customer->created_by) {
            return $this->mapAssignedUser($customer->creator);
        }

        return null;
    }

    private function resolveSupervisorUser(Customers $customer, ?array $marketerUser): ?array
    {
        if ($customer->leader_id) {
            return $this->mapAssignedUser(User::query()->find($customer->leader_id));
        }

        $sarparastId = Pishfactor::query()
            ->where('customer_id', $customer->id)
            ->whereNotNull('sarparast_id')
            ->orderByDesc('id')
            ->value('sarparast_id');

        if ($sarparastId) {
            $fromOrder = $this->mapAssignedUser(User::query()->find($sarparastId));

            if ($fromOrder) {
                return $fromOrder;
            }
        }

        if ($customer->Area?->leader_id) {
            $fromArea = $this->mapAssignedUser($customer->Area->leader);

            if ($fromArea) {
                return $fromArea;
            }
        }

        if ($marketerUser && $customer->creator?->leader_id) {
            $fromMarketerLeader = $this->mapAssignedUser($customer->creator->leader);

            if ($fromMarketerLeader && (!$marketerUser['id'] || $fromMarketerLeader['id'] !== $marketerUser['id'])) {
                return $fromMarketerLeader;
            }
        }

        $visitorId = optional($customer->latestPishfactor)->visitor_id;

        if ($visitorId) {
            $visitor = User::query()->with('leader:id,name,mobile,leader_id')->find($visitorId);
            $fromVisitorLeader = $this->mapAssignedUser(optional($visitor)->leader);

            if ($fromVisitorLeader && (!$marketerUser['id'] || $fromVisitorLeader['id'] !== $marketerUser['id'])) {
                return $fromVisitorLeader;
            }
        }

        $hierarchy = $this->walkSalesHierarchy($marketerUser['id'] ?? $customer->created_by);

        if ($hierarchy['supervisor']) {
            return $hierarchy['supervisor'];
        }

        return $this->resolveTenantRoleAssignment(
            $customer->tenant_id ? (int) $customer->tenant_id : null,
            ['leader', 'expert'],
            $marketerUser
        );
    }

    private function resolveSalesManagerUser(Customers $customer, ?array $marketerUser, ?array $supervisorUser): ?array
    {
        $candidates = [];

        if ($customer->region_id && $customer->region?->leader_id) {
            $candidates[] = $this->mapAssignedUser($customer->region->leader);
        }

        if ($customer->Area?->region?->leader_id) {
            $candidates[] = $this->mapAssignedUser($customer->Area->region->leader);
        }

        foreach ($candidates as $candidate) {
            if ($this->isDistinctAssignment($candidate, $marketerUser, $supervisorUser)) {
                return $candidate;
            }
        }

        $hierarchy = $this->walkSalesHierarchy($marketerUser['id'] ?? $customer->created_by);
        $fromHierarchy = $hierarchy['sales_manager'];

        if ($this->isDistinctAssignment($fromHierarchy, $marketerUser, $supervisorUser)) {
            return $fromHierarchy;
        }

        if ($supervisorUser && isset($supervisorUser['id'])) {
            $supervisorModel = User::query()->with('leader:id,name,mobile')->find($supervisorUser['id']);
            $fromSupervisorLeader = $this->mapAssignedUser(optional($supervisorModel)->leader);

            if ($this->isDistinctAssignment($fromSupervisorLeader, $marketerUser, $supervisorUser)) {
                return $fromSupervisorLeader;
            }
        }

        return $this->resolveTenantRoleAssignment(
            $customer->tenant_id ? (int) $customer->tenant_id : null,
            ['sales_manager', 'manager'],
            $marketerUser,
            $supervisorUser
        );
    }

    private function resolveTenantRoleAssignment(?int $tenantId, array $roleTitles, ?array ...$exclude): ?array
    {
        if (!$tenantId) {
            return null;
        }

        $userIds = User::query()
            ->where('tenants_id', $tenantId)
            ->pluck('id');

        $memberIds = TenantUserMembership::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->pluck('user_id');

        $allIds = $userIds->merge($memberIds)->unique()->filter()->values();

        if ($allIds->isEmpty()) {
            return null;
        }

        $users = User::query()
            ->whereIn('id', $allIds)
            ->where('isActive', 1)
            ->whereHas('roles', fn ($query) => $query->whereIn('title', $roleTitles))
            ->orderBy('id')
            ->get();

        $candidates = $users
            ->map(fn (User $user) => $this->mapAssignedUser($user))
            ->filter(function (?array $mapped) use ($exclude) {
                if (!$mapped) {
                    return false;
                }

                foreach ($exclude as $assignment) {
                    if ($this->isSameAssignment($mapped, $assignment)) {
                        return false;
                    }
                }

                return true;
            })
            ->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        if ($candidates->count() === 1) {
            return $candidates->first();
        }

        $marketerId = null;
        foreach ($exclude as $assignment) {
            if (!empty($assignment['id'])) {
                $marketerId = (int) $assignment['id'];
                break;
            }
        }

        if ($marketerId) {
            $directLeader = $users->first(function (User $user) use ($marketerId, $exclude) {
                $mapped = $this->mapAssignedUser($user);

                if (!$mapped) {
                    return false;
                }

                foreach ($exclude as $assignment) {
                    if ($this->isSameAssignment($mapped, $assignment)) {
                        return false;
                    }
                }

                return User::query()
                    ->where('id', $marketerId)
                    ->where('leader_id', $user->id)
                    ->exists();
            });

            if ($directLeader) {
                return $this->mapAssignedUser($directLeader);
            }
        }

        return null;
    }

    private function walkSalesHierarchy(?int $userId): array
    {
        $supervisor = null;
        $salesManager = null;

        if (!$userId) {
            return ['supervisor' => null, 'sales_manager' => null];
        }

        $user = User::query()
            ->with(['roles', 'leader.roles', 'leader.leader.roles'])
            ->find($userId);

        if (!$user?->leader_id || !$user->leader) {
            return ['supervisor' => null, 'sales_manager' => null];
        }

        $level1 = $user->leader;

        if ($this->userHasRoleTitle($level1, ['leader', 'expert'])) {
            $supervisor = $this->mapAssignedUser($level1);

            if ($level1->leader_id && $level1->leader) {
                $level2 = $level1->leader;

                if ($this->userHasRoleTitle($level2, ['sales_manager', 'manager', 'panel_manager'])) {
                    $salesManager = $this->mapAssignedUser($level2);
                }
            }
        } elseif ($this->userHasRoleTitle($level1, ['sales_manager', 'manager', 'panel_manager'])) {
            $salesManager = $this->mapAssignedUser($level1);
        }

        return ['supervisor' => $supervisor, 'sales_manager' => $salesManager];
    }

    private function userHasRoleTitle(User $user, array $titles): bool
    {
        $user->loadMissing('roles');

        return $user->roles->contains(function ($role) use ($titles) {
            return in_array((string) ($role->title ?? ''), $titles, true);
        });
    }

    private function isDistinctAssignment(?array $candidate, ?array $marketerUser, ?array $supervisorUser): bool
    {
        if (!$candidate) {
            return false;
        }

        return !$this->isSameAssignment($candidate, $marketerUser)
            && !$this->isSameAssignment($candidate, $supervisorUser);
    }

    private function isSameAssignment(?array $a, ?array $b): bool
    {
        return $a && $b && (int) $a['id'] === (int) $b['id'];
    }

    private function mapAssignedUser(?User $user): ?array
    {
        if (!$user) {
            return null;
        }

        $mobile = trim((string) ($user->mobile ?: ''));

        return [
            'id' => $user->id,
            'name' => $user->name,
            'mobile' => $mobile !== '' ? $mobile : null,
            'role' => $this->resolveUserRoleLabel($user),
        ];
    }

    private function resolveUserRoleLabel(User $user): ?string
    {
        $user->loadMissing('roles');

        $role = $user->roles->first();

        return $role?->title ?: $role?->name;
    }
}
