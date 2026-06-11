<?php

namespace App\Services;

use App\Models\CrmFollowup;
use App\Models\CrmSalesBoardCard;
use App\Models\CrmSalesBoardCardComment;
use App\Models\Customers;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CrmQuickActionService
{
    public function createFollowup(User $user, array $data): CrmFollowup
    {
        $customer = $this->resolveCustomer((int) $data['customer_id'], $user);

        $dueDate = !empty($data['due_date_en'])
            ? Carbon::parse($data['due_date_en'])->toDateString()
            : Carbon::today()->toDateString();

        return CrmFollowup::create([
            'tenant_id' => $customer->tenant_id ?: $user->tenant_id,
            'organization_id' => $customer->organization_id ?: $user->organization_id,
            'subject_type' => 'customer',
            'customer_id' => $customer->id,
            'assigned_user_id' => $data['assigned_user_id'] ?? $user->id,
            'type' => $data['type'] ?? 'followup',
            'priority' => $data['priority'] ?? 'normal',
            'status' => 'open',
            'title' => $data['title'],
            'due_date_en' => $dueDate,
            'due_date_fa' => verta($dueDate)->format('Y/m/d'),
            'description' => $data['description'] ?? null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    public function addCardNote(User $user, CrmSalesBoardCard $card, string $body): CrmSalesBoardCardComment
    {
        $comment = CrmSalesBoardCardComment::create([
            'card_id' => $card->id,
            'tenant_id' => $card->tenant_id,
            'organization_id' => $card->organization_id,
            'user_id' => $user->id,
            'comment' => $body,
            'mentions' => [],
        ]);

        $activityLogs = $card->activity_logs ?: [];
        $activityLogs[] = [
            'type' => 'note_added',
            'user_id' => $user->id,
            'user_name' => $user->name,
            'comment_id' => $comment->id,
            'at' => now()->toDateTimeString(),
        ];
        $card->forceFill(['activity_logs' => $activityLogs, 'updated_by' => $user->id])->save();

        return $comment;
    }

    private function resolveCustomer(int $customerId, User $user): Customers
    {
        $query = Customers::query();

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        $customer = $query->find($customerId);

        if (!$customer) {
            throw ValidationException::withMessages(['customer_id' => 'مشتری یافت نشد.']);
        }

        return $customer;
    }
}
