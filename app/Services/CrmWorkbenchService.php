<?php

namespace App\Services;

use App\Models\CrmCallLog;
use App\Models\CrmCollaborationComment;
use App\Models\CrmCollaborationMention;
use App\Models\CrmEmployeeCoachingPlan;
use App\Models\CrmFollowup;
use App\Models\CrmOpportunity;
use App\Models\CrmServiceTicket;
use App\Models\CrmWorkbenchPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CrmWorkbenchService
{
    public const TARGETS = [
        'followup' => CrmFollowup::class,
        'opportunity' => CrmOpportunity::class,
        'ticket' => CrmServiceTicket::class,
        'call' => CrmCallLog::class,
        'coaching' => CrmEmployeeCoachingPlan::class,
    ];

    public const TARGET_LABELS = [
        'followup' => 'پیگیری',
        'opportunity' => 'فرصت فروش',
        'ticket' => 'تیکت خدمات',
        'call' => 'تماس',
        'coaching' => 'coaching',
    ];

    public function state(User $user, array $filters = []): array
    {
        $preference = $this->preference($user);
        $focusScope = $this->focusScope($filters['focus_scope'] ?? $preference->focus_scope ?? 'mine', $user);
        $targetType = $filters['target_type'] ?? null;

        $followups = $this->workbenchQuery(CrmFollowup::query()->with(['customer', 'employee', 'assignedUser']), $user, $focusScope, 'assigned_user_id')
            ->whereIn('status', ['open', 'in_progress'])
            ->orderByRaw('due_date_en IS NULL')
            ->orderBy('due_date_en')
            ->latest('id')
            ->limit(25)
            ->get();

        $opportunities = $this->workbenchQuery(CrmOpportunity::query()->with(['customer', 'assignedUser']), $user, $focusScope, 'assigned_user_id')
            ->where('status', 'open')
            ->orderByRaw('next_action_date_en IS NULL')
            ->orderBy('next_action_date_en')
            ->latest('id')
            ->limit(25)
            ->get();

        $tickets = $this->workbenchQuery(CrmServiceTicket::query()->with(['customer', 'assignedUser']), $user, $focusScope, 'assigned_user_id')
            ->whereIn('status', ['open', 'pending'])
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->latest('id')
            ->limit(25)
            ->get();

        $calls = $this->workbenchQuery(CrmCallLog::query()->with(['customer', 'assignedUser']), $user, $focusScope, 'assigned_user_id')
            ->where(function ($query) {
                $query->where('direction', 'missed')
                    ->orWhere('status', 'needs_followup')
                    ->orWhereNotNull('next_action_at');
            })
            ->orderByRaw('next_action_at IS NULL')
            ->orderBy('next_action_at')
            ->latest('id')
            ->limit(25)
            ->get();

        $coachings = $this->workbenchQuery(CrmEmployeeCoachingPlan::query()->with(['user', 'coach']), $user, $focusScope, 'user_id')
            ->whereIn('status', ['open', 'in_progress'])
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'normal', 'low')")
            ->orderByRaw('due_at IS NULL')
            ->orderBy('due_at')
            ->latest('id')
            ->limit(20)
            ->get();

        $mentions = CrmCollaborationMention::query()->with(['comment.user'])
            ->where('mentioned_user_id', $user->id)
            ->whereNull('read_at')
            ->latest('id')
            ->limit(20)
            ->get();

        $commentsQuery = CrmCollaborationComment::query()->with(['user', 'mentions.mentionedUser'])
            ->latest('id')
            ->limit(30);

        if ((int) $user->isGod !== 1) {
            $commentsQuery->forOrganizations($user);
        }

        if ($targetType && isset(self::TARGETS[$targetType])) {
            $commentsQuery->where('commentable_type', self::TARGETS[$targetType]);
        }

        return [
            'filters' => [
                'focus_scope' => $focusScope,
                'target_type' => $targetType,
            ],
            'preference' => $preference,
            'focus_scopes' => CrmWorkbenchPreference::FOCUS_SCOPES,
            'target_labels' => self::TARGET_LABELS,
            'users' => $this->mentionableUsers($user),
            'followups' => $followups,
            'opportunities' => $opportunities,
            'tickets' => $tickets,
            'calls' => $calls,
            'coachings' => $coachings,
            'mentions' => $mentions,
            'comments' => $commentsQuery->get(),
            'stats' => [
                'followups' => $followups->count(),
                'opportunities' => $opportunities->count(),
                'tickets' => $tickets->count(),
                'calls' => $calls->count(),
                'coachings' => $coachings->count(),
                'mentions' => $mentions->count(),
            ],
        ];
    }

    public function updatePreference(User $user, array $data): CrmWorkbenchPreference
    {
        $focusScope = $this->focusScope($data['focus_scope'] ?? 'mine', $user);

        return CrmWorkbenchPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'tenant_id' => $this->tenantId($user),
                'organization_id' => $this->organizationId($user),
                'focus_scope' => $focusScope,
                'enabled_widgets' => $data['enabled_widgets'] ?? ['followups', 'opportunities', 'tickets', 'calls', 'mentions', 'coachings'],
                'filters' => $data['filters'] ?? [],
            ]
        );
    }

    public function storeComment(User $user, array $data): CrmCollaborationComment
    {
        $target = $this->resolveTarget($user, $data['target_type'], (int) $data['target_id']);
        $mentionedUserIds = $this->validMentionUserIds($user, $data['mentioned_user_ids'] ?? []);

        return DB::transaction(function () use ($user, $target, $data, $mentionedUserIds) {
            $comment = CrmCollaborationComment::create([
                'tenant_id' => $target->tenant_id ?: $this->tenantId($user),
                'organization_id' => $target->organization_id ?: $this->organizationId($user),
                'commentable_type' => $target::class,
                'commentable_id' => $target->id,
                'user_id' => $user->id,
                'body' => $data['body'],
                'mentioned_user_ids' => $mentionedUserIds,
                'visibility' => $data['visibility'] ?? 'team',
                'source' => $data['source'] ?? 'workbench',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $this->createMentions($comment, $target, $user, $mentionedUserIds);

            return $comment->load(['user', 'mentions.mentionedUser']);
        });
    }

    public function markMentionRead(User $user, CrmCollaborationMention $mention): void
    {
        abort_unless((int) $mention->mentioned_user_id === (int) $user->id || (int) $user->isGod === 1, 403);
        $mention->forceFill(['read_at' => now()])->save();
    }

    public function targetTitle(Model $target): string
    {
        return match (true) {
            $target instanceof CrmFollowup => $target->title,
            $target instanceof CrmOpportunity => $target->title,
            $target instanceof CrmServiceTicket => $target->subject,
            $target instanceof CrmCallLog => $target->subject,
            $target instanceof CrmEmployeeCoachingPlan => $target->title,
            default => 'رکورد CRM',
        };
    }

    public function targetType(Model $target): string
    {
        return array_search($target::class, self::TARGETS, true) ?: 'record';
    }

    private function preference(User $user): CrmWorkbenchPreference
    {
        return CrmWorkbenchPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'tenant_id' => $this->tenantId($user),
                'organization_id' => $this->organizationId($user),
                'focus_scope' => 'mine',
                'enabled_widgets' => ['followups', 'opportunities', 'tickets', 'calls', 'mentions', 'coachings'],
                'filters' => [],
            ]
        );
    }

    private function workbenchQuery(Builder $query, User $user, string $focusScope, string $ownerField): Builder
    {
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        if ($focusScope === 'mine' || ((int) $user->isGod !== 1 && $focusScope === 'all')) {
            $query->where($ownerField, $user->id);
        }

        if ($focusScope === 'team') {
            $teamUserIds = $this->teamUserIds($user);
            $query->whereIn($ownerField, $teamUserIds->isNotEmpty() ? $teamUserIds->all() : [$user->id]);
        }

        return $query;
    }

    private function focusScope(string $focusScope, User $user): string
    {
        if (!array_key_exists($focusScope, CrmWorkbenchPreference::FOCUS_SCOPES)) {
            return 'mine';
        }

        if ($focusScope === 'all' && (int) $user->isGod !== 1 && (int) $user->isAdmin !== 1) {
            return 'mine';
        }

        return $focusScope;
    }

    private function resolveTarget(User $user, string $targetType, int $targetId): Model
    {
        abort_unless(isset(self::TARGETS[$targetType]), 422);

        $class = self::TARGETS[$targetType];
        $query = $class::query()->whereKey($targetId);

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->firstOrFail();
    }

    private function validMentionUserIds(User $user, array $userIds): array
    {
        $ids = collect($userIds)->filter()->map(fn($id) => (int) $id)->reject(fn($id) => $id === (int) $user->id)->unique()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $query = User::query()->whereIn('id', $ids)->where('isActive', 1);

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->pluck('id')->map(fn($id) => (int) $id)->values()->all();
    }

    private function mentionableUsers(User $user)
    {
        $query = User::query()->select(['id', 'name', 'tenant_id', 'tenants_id', 'organization_id', 'isActive'])->where('isActive', 1)->orderBy('name')->limit(200);

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->get();
    }

    private function createMentions(CrmCollaborationComment $comment, Model $target, User $actor, array $mentionedUserIds): void
    {
        if (empty($mentionedUserIds)) {
            return;
        }

        foreach ($mentionedUserIds as $mentionedUserId) {
            CrmCollaborationMention::create([
                'tenant_id' => $comment->tenant_id,
                'organization_id' => $comment->organization_id,
                'comment_id' => $comment->id,
                'mentioned_user_id' => $mentionedUserId,
                'mentioned_by_user_id' => $actor->id,
                'mentionable_type' => $target::class,
                'mentionable_id' => $target->id,
                'notified_at' => now(),
            ]);
        }

        app(PanelNotificationService::class)->dispatch('crm_comment_mention', $mentionedUserIds, [
            'tenant_id' => $comment->tenant_id,
            'record_title' => $this->targetTitle($target),
            'comment_body' => mb_substr(strip_tags($comment->body), 0, 160),
            'actor_name' => $actor->name,
            'time' => now()->format('H:i'),
            'source' => 'crm_workbench_mention',
            'severity' => 'info',
            'reference_type' => $target::class,
            'reference_id' => $target->id,
        ], $comment->tenant_id);
    }

    private function teamUserIds(User $user)
    {
        $query = User::query()->where('isActive', 1);

        if ($this->tenantId($user)) {
            $query->where(function ($inner) use ($user) {
                $inner->where('tenant_id', $this->tenantId($user))->orWhere('tenants_id', $this->tenantId($user));
            });
        } elseif ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->pluck('id')->map(fn($id) => (int) $id)->unique()->values();
    }

    private function tenantId(User $user): ?int
    {
        return $user->tenant_id ?: $user->tenants_id;
    }

    private function organizationId(User $user): ?int
    {
        $value = $user->organization_id;
        $decoded = is_string($value) ? json_decode($value, true) : null;

        if (is_array($decoded)) {
            return isset($decoded[0]) ? (int) $decoded[0] : null;
        }

        return $value !== null ? (int) $value : null;
    }
}
