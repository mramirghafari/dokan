<?php

namespace App\Services;

use App\Models\ApprovalWorkflow;
use Illuminate\Database\Eloquent\Collection;

class ApprovalWorkflowService
{
    public function workflowFor(string $documentType, array $context = [], ?float $amount = null): ?ApprovalWorkflow
    {
        $query = ApprovalWorkflow::with('steps')
            ->where('document_type', $documentType)
            ->where('isActive', true);

        if ($amount !== null) {
            $query->where(function ($query) use ($amount) {
                $query->whereNull('amount_threshold')->orWhere('amount_threshold', '<=', $amount);
            });
        }

        return $query->where(function ($query) use ($context) {
            $tenantId = $context['tenant_id'] ?? null;
            $organizationId = $context['organization_id'] ?? null;

            $query->where(function ($query) use ($tenantId, $organizationId) {
                $query->where('tenant_id', $tenantId)->where('organization_id', $organizationId);
            })->orWhere(function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId)->whereNull('organization_id');
            })->orWhere(function ($query) {
                $query->whereNull('tenant_id')->whereNull('organization_id');
            });
        })
            ->orderByRaw('organization_id is null')
            ->orderByRaw('tenant_id is null')
            ->orderByDesc('amount_threshold')
            ->first();
    }

    public function isRequired(string $documentType, array $context = [], ?float $amount = null): bool
    {
        $workflow = $this->workflowFor($documentType, $context, $amount);

        return $workflow ? (bool) $workflow->is_required : false;
    }

    public function stepsFor(string $documentType, array $context = [], ?float $amount = null): Collection
    {
        $workflow = $this->workflowFor($documentType, $context, $amount);

        return $workflow ? $workflow->steps : new Collection();
    }

    public function decisionSnapshot(string $documentType, string $decision, array $context = [], ?float $amount = null): array
    {
        $workflow = $this->workflowFor($documentType, $context, $amount);

        return [
            'document_type' => $documentType,
            'decision' => $decision,
            'workflow_id' => $workflow?->id,
            'workflow_title' => $workflow?->title,
            'is_required' => $workflow ? (bool) $workflow->is_required : false,
            'steps_count' => $workflow ? $workflow->steps->count() : 0,
        ];
    }
}
