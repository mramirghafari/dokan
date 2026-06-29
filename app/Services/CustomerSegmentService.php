<?php

namespace App\Services;

use App\Models\CustomerSegment;
use App\Models\User;

class CustomerSegmentService
{
    /**
     * @return array<int, array{title: string, code: string, is_default: bool, sort_order: int}>
     */
    public function defaultStatusDefinitions(): array
    {
        return [
            ['title' => 'فعال', 'code' => 'customer_status_active', 'is_default' => true, 'sort_order' => 1],
            ['title' => 'غیرفعال', 'code' => 'customer_status_inactive', 'is_default' => false, 'sort_order' => 2],
            ['title' => 'مسدود', 'code' => 'customer_status_blocked', 'is_default' => false, 'sort_order' => 3],
        ];
    }

    public function ensureDefaultStatuses(User $user, ?int $organizationId = null, $tenantId = null): void
    {
        $organizationId = $organizationId ?: $this->primaryOrganizationId($user);
        $tenantId = $tenantId ?: ($user->tenant_id ?: $user->tenants_id);

        foreach ($this->defaultStatusDefinitions() as $definition) {
            CustomerSegment::firstOrCreate(
                [
                    'type' => 'customer_status',
                    'organization_id' => $organizationId,
                    'title' => $definition['title'],
                ],
                [
                    'tenant_id' => $tenantId,
                    'code' => $definition['code'],
                    'sort_order' => $definition['sort_order'],
                    'is_default' => $definition['is_default'],
                    'isActive' => 1,
                ]
            );
        }
    }

    public function customerGroupsQuery(User $user)
    {
        $query = CustomerSegment::query()
            ->where('type', 'customer_group')
            ->orderBy('sort_order')
            ->orderBy('title');

        if (!$user || (int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    public function activeCustomerGroups(User $user)
    {
        return $this->customerGroupsQuery($user)->where('isActive', 1)->get();
    }

    public function activeSegments(User $user, string $type)
    {
        $query = CustomerSegment::where('type', $type)->where('isActive', 1);

        if (!$user || (int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->orderBy('sort_order')->orderBy('title')->get();
    }

    private function primaryOrganizationId(User $user): ?int
    {
        $rawOrganizationId = $user->organization_id;

        if (empty($rawOrganizationId)) {
            return null;
        }

        $decodedOrganizationIds = json_decode($rawOrganizationId, true);

        if (is_array($decodedOrganizationIds)) {
            return (int) reset($decodedOrganizationIds);
        }

        return (int) $rawOrganizationId;
    }
}
