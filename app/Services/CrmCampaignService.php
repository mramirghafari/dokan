<?php

namespace App\Services;

use App\Jobs\DispatchCampaignSmsJob;
use App\Models\CrmCampaign;
use App\Models\CrmCampaignAudience;
use App\Models\CustomerLoyaltyAccount;
use App\Models\CustomerLoyaltyTransaction;
use App\Models\CustomerSegment;
use App\Models\Customers;
use App\Models\Pishfactor;
use App\Models\Tenants;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Kavenegar\Laravel\Facade as Kavenegar;
use Throwable;

class CrmCampaignService
{
    public function fixedMessageFor(CrmCampaign $campaign): string
    {
        $messages = (array) config('erp_scale.crm_campaign.fixed_messages', []);
        $template = $messages[$campaign->goal] ?? $messages['default'] ?? 'مشتری گرامی {name} — {campaign}';

        return strtr($template, [
            '{name}' => '{name}',
            '{campaign}' => $campaign->title,
            '{discount_code}' => $campaign->discount_code ?: '',
        ]);
    }

    public function dispatch(CrmCampaign $campaign, User $user, array $options = []): array
    {
        if (in_array($campaign->dispatch_status, ['queued', 'sending'], true)) {
            throw ValidationException::withMessages(['dispatch' => 'این کمپین در حال پردازش است.']);
        }

        if (!trim((string) $campaign->message_template)) {
            $campaign->update([
                'message_template' => $this->fixedMessageFor($campaign),
                'updated_by' => $user->id,
            ]);
        }

        $maxAudience = max(1, (int) config('erp_scale.crm_campaign.max_audience_per_dispatch', 100));
        $limit = max(1, min($maxAudience, (int) ($options['limit'] ?? $maxAudience)));

        $audiences = CrmCampaignAudience::query()
            ->with('customer')
            ->where('crm_campaign_id', $campaign->id)
            ->where('status', 'planned')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($audiences->isEmpty()) {
            throw ValidationException::withMessages([
                'audience' => 'مخاطب planned وجود ندارد. ابتدا مخاطب بسازید.',
            ]);
        }

        if (!config('erp_scale.crm_campaign.sms_enabled', false)) {
            return $this->markAudiencesPlannedSent($campaign, $user, $audiences);
        }

        if (!in_array($campaign->channel, ['sms', 'mixed'], true)) {
            throw ValidationException::withMessages(['channel' => 'ارسال SMS فقط برای کانال پیامک فعال است.']);
        }

        $audienceIds = $audiences->pluck('id')->all();
        $batchSize = max(1, (int) config('erp_scale.crm_campaign.sms_batch_size', 25));
        $chunks = array_chunk($audienceIds, $batchSize);

        $campaign->update([
            'dispatch_status' => 'queued',
            'dispatched_at' => now(),
            'updated_by' => $user->id,
        ]);

        foreach ($chunks as $index => $chunk) {
            DispatchCampaignSmsJob::dispatch(
                $campaign->id,
                $user->id,
                $chunk,
                $index === array_key_last($chunks)
            );
        }

        return [
            'queued_batches' => count($chunks),
            'audience_count' => count($audienceIds),
            'sms_enabled' => true,
        ];
    }

    private function markAudiencesPlannedSent(CrmCampaign $campaign, User $user, $audiences): array
    {
        $template = $this->fixedMessageFor($campaign);
        $marked = 0;

        foreach ($audiences as $audience) {
            $audience->update([
                'status' => 'sent',
                'sms_status' => 'fixed_template',
                'provider_message_id' => null,
                'sent_at' => now(),
                'notes' => trim(($audience->notes ? $audience->notes . "\n" : '') . 'پیام ثابت: ' . $this->renderMessage($campaign, $audience->customer)),
                'updated_by' => $user->id,
            ]);
            $marked++;
        }

        $campaign->update([
            'dispatch_status' => 'completed',
            'dispatched_at' => now(),
            'status' => $campaign->status === 'draft' ? 'active' : $campaign->status,
            'message_template' => $template,
            'updated_by' => $user->id,
        ]);

        $this->recalculateCampaign($campaign->fresh());

        return [
            'audience_count' => $marked,
            'sms_enabled' => false,
            'mode' => 'fixed_template_only',
        ];
    }

    public function processSmsBatch(CrmCampaign $campaign, User $user, array $audienceIds, bool $finalize): void
    {
        $campaign->update(['dispatch_status' => 'sending']);

        $audiences = CrmCampaignAudience::query()
            ->with('customer')
            ->where('crm_campaign_id', $campaign->id)
            ->whereIn('id', $audienceIds)
            ->get();

        $failed = 0;

        foreach ($audiences as $audience) {
            $result = $this->sendSmsToAudience($campaign, $audience, $user);

            if ($result['ok']) {
                $audience->update([
                    'status' => 'sent',
                    'sms_status' => $result['dry_run'] ? 'dry_run' : 'sent',
                    'provider_message_id' => $result['message_id'],
                    'sent_at' => now(),
                    'updated_by' => $user->id,
                ]);
            } else {
                $failed++;
                $audience->update([
                    'sms_status' => 'failed',
                    'sms_error' => $result['error'],
                    'updated_by' => $user->id,
                ]);
            }
        }

        $campaign->increment('failed_send_count', $failed);
        $this->recalculateCampaign($campaign->fresh());

        if ($finalize) {
            $fresh = $campaign->fresh();
            $campaign->update([
                'dispatch_status' => $failed > 0 && (int) $fresh->sent_count === 0 ? 'failed' : 'completed',
                'status' => $campaign->status === 'draft' ? 'active' : $campaign->status,
                'updated_by' => $user->id,
            ]);
        }
    }

    private function sendSmsToAudience(CrmCampaign $campaign, CrmCampaignAudience $audience, User $user): array
    {
        $customer = $audience->customer;
        $mobile = $this->normalizeMobile($customer?->mobile ?: $customer?->phone);

        if (!$mobile) {
            return ['ok' => false, 'error' => 'موبایل مشتری خالی است.', 'dry_run' => false, 'message_id' => null];
        }

        $message = $this->renderMessage($campaign, $customer);
        $dryRun = (bool) config('erp_scale.crm_campaign.sms_dry_run', false);

        if ($dryRun) {
            Log::info('crm_campaign_sms_dry_run', [
                'campaign_id' => $campaign->id,
                'audience_id' => $audience->id,
                'mobile' => $mobile,
                'message' => $message,
            ]);

            return ['ok' => true, 'dry_run' => true, 'message_id' => 'dry-run', 'error' => null];
        }

        $tenantId = $campaign->tenant_id;
        $sender = trim((string) TenantSettings::get('sms_sender_number', $tenantId, ''));

        if ($sender === '') {
            return ['ok' => false, 'error' => 'شماره فرستنده SMS تنظیم نشده است.', 'dry_run' => false, 'message_id' => null];
        }

        $tenant = $tenantId ? Tenants::find($tenantId) : null;
        $unitPrice = (float) ($tenant->sms_unit_price_toman ?? TenantSettings::get('sms_unit_price_toman', $tenantId, 0));

        if ($unitPrice > 0 && $tenant && (float) $tenant->wallet_balance < $unitPrice) {
            return ['ok' => false, 'error' => 'موجودی کیف پیامک tenant کافی نیست.', 'dry_run' => false, 'message_id' => null];
        }

        try {
            $response = Kavenegar::Send($sender, $mobile, $message);
            $messageId = is_object($response) && isset($response[0]->messageid)
                ? (string) $response[0]->messageid
                : null;

            if ($unitPrice > 0 && $tenant) {
                $tenant->decrement('wallet_balance', $unitPrice);
            }

            return ['ok' => true, 'dry_run' => false, 'message_id' => $messageId, 'error' => null];
        } catch (Throwable $exception) {
            return ['ok' => false, 'error' => $exception->getMessage(), 'dry_run' => false, 'message_id' => null];
        }
    }

    private function renderMessage(CrmCampaign $campaign, ?Customers $customer): string
    {
        $template = (string) $campaign->message_template;

        return strtr($template, [
            '{name}' => $customer?->name ?: 'مشتری',
            '{mobile}' => $customer?->mobile ?: '',
            '{discount_code}' => $campaign->discount_code ?: '',
            '{campaign}' => $campaign->title,
        ]);
    }

    private function normalizeMobile(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        return strlen($digits) >= 10 ? $digits : null;
    }

    public function syncAudience(CrmCampaign $campaign, $user, array $options = []): int
    {
        $limit = max(1, min(500, (int) ($options['limit'] ?? 300)));
        $customers = $this->customerQueryForCampaign($campaign, $user, $options)->limit($limit)->get();
        $createdCount = 0;

        DB::transaction(function () use ($campaign, $customers, $user, &$createdCount) {
            foreach ($customers as $customer) {
                $audience = CrmCampaignAudience::firstOrCreate([
                    'crm_campaign_id' => $campaign->id,
                    'customer_id' => $customer->id,
                ], [
                    'tenant_id' => $customer->tenant_id ?: $campaign->tenant_id,
                    'organization_id' => $customer->organization_id ?: $campaign->organization_id,
                    'status' => 'planned',
                    'estimated_value' => $this->customerEstimatedValue($customer),
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);

                if ($audience->wasRecentlyCreated) {
                    $createdCount++;
                }

                $this->accountForCustomer($customer, $user);
            }

            $this->recalculateCampaign($campaign);
        });

        return $createdCount;
    }

    public function recordAudienceResult(CrmCampaign $campaign, $user, array $data): CrmCampaignAudience
    {
        $customer = $this->resolveCustomer((int) $data['customer_id'], $user);
        $order = !empty($data['pishfactor_id']) ? $this->resolveOrder((int) $data['pishfactor_id'], $customer, $user) : null;
        $status = $data['status'];
        $revenueAmount = array_key_exists('revenue_amount', $data) && $data['revenue_amount'] !== null
            ? (float) $data['revenue_amount']
            : ($order ? $this->orderAmount($order) : 0.0);
        $points = (int) ($data['loyalty_points_awarded'] ?? 0);

        return DB::transaction(function () use ($campaign, $user, $customer, $order, $status, $revenueAmount, $points, $data) {
            $audience = CrmCampaignAudience::updateOrCreate([
                'crm_campaign_id' => $campaign->id,
                'customer_id' => $customer->id,
            ], [
                'tenant_id' => $customer->tenant_id ?: $campaign->tenant_id,
                'organization_id' => $customer->organization_id ?: $campaign->organization_id,
                'status' => $status,
                'estimated_value' => $this->customerEstimatedValue($customer),
                'revenue_amount' => $revenueAmount,
                'loyalty_points_awarded' => $points,
                'pishfactor_id' => optional($order)->id,
                'notes' => $data['notes'] ?? null,
                'updated_by' => $user->id,
                'sent_at' => in_array($status, ['sent', 'responded', 'converted'], true) ? now() : null,
                'responded_at' => in_array($status, ['responded', 'converted'], true) ? now() : null,
                'converted_at' => $status === 'converted' ? now() : null,
            ]);

            if ($points !== 0) {
                $this->addLoyaltyTransaction($customer, $user, [
                    'campaign' => $campaign,
                    'audience' => $audience,
                    'order' => $order,
                    'type' => $points > 0 ? 'earn' : 'redeem',
                    'points' => $points,
                    'amount' => $revenueAmount,
                    'reason' => $campaign->title,
                    'description' => $data['notes'] ?? null,
                ]);
            } elseif ($revenueAmount > 0) {
                $account = $this->accountForCustomer($customer, $user);
                $this->updateAccountAfterValue($account, $revenueAmount, $user);
            }

            $this->recalculateCampaign($campaign);

            return $audience->refresh();
        });
    }

    public function addManualLoyaltyTransaction($user, array $data): CustomerLoyaltyTransaction
    {
        $customer = $this->resolveCustomer((int) $data['customer_id'], $user);
        $campaign = !empty($data['crm_campaign_id']) ? $this->resolveCampaign((int) $data['crm_campaign_id'], $user) : null;
        $order = !empty($data['pishfactor_id']) ? $this->resolveOrder((int) $data['pishfactor_id'], $customer, $user) : null;

        return DB::transaction(function () use ($customer, $user, $campaign, $order, $data) {
            return $this->addLoyaltyTransaction($customer, $user, [
                'campaign' => $campaign,
                'order' => $order,
                'type' => $data['type'],
                'points' => (int) $data['points'],
                'amount' => (float) ($data['amount'] ?? 0),
                'reason' => $data['reason'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
        });
    }

    public function recalculateCampaign(CrmCampaign $campaign): void
    {
        $audiences = CrmCampaignAudience::query()->where('crm_campaign_id', $campaign->id);

        $campaign->update([
            'audience_count' => (clone $audiences)->count(),
            'sent_count' => (clone $audiences)->whereIn('status', ['sent', 'responded', 'converted'])->count(),
            'response_count' => (clone $audiences)->whereIn('status', ['responded', 'converted'])->count(),
            'conversion_count' => (clone $audiences)->where('status', 'converted')->count(),
            'actual_revenue' => (clone $audiences)->sum('revenue_amount'),
        ]);
    }

    private function customerQueryForCampaign(CrmCampaign $campaign, $user, array $options)
    {
        $query = Customers::query()->orderByDesc('id');

        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        if (!empty($options['customer_ids']) && is_array($options['customer_ids'])) {
            $query->whereIn('id', array_filter($options['customer_ids']));
        }

        if ($campaign->target_segment_id) {
            $segment = CustomerSegment::query()->find($campaign->target_segment_id);
            if ($segment) {
                $column = match ($segment->type) {
                    'customer_group' => 'customer_group_id',
                    'sales_channel' => 'sales_channel_id',
                    'customer_status' => 'customer_status_id',
                    default => null,
                };

                if ($column && Schema::hasColumn('customers', $column)) {
                    $query->where($column, $segment->id);
                }
            }
        }

        if (($options['only_active'] ?? true) && Schema::hasColumn('customers', 'status')) {
            $query->where('status', 1);
        }

        return $query;
    }

    private function addLoyaltyTransaction(Customers $customer, $user, array $data): CustomerLoyaltyTransaction
    {
        $account = $this->accountForCustomer($customer, $user);
        $points = (int) $data['points'];
        $amount = (float) ($data['amount'] ?? 0);

        $transaction = CustomerLoyaltyTransaction::create([
            'tenant_id' => $account->tenant_id,
            'organization_id' => $account->organization_id,
            'customer_loyalty_account_id' => $account->id,
            'customer_id' => $customer->id,
            'crm_campaign_id' => optional($data['campaign'] ?? null)->id,
            'crm_campaign_audience_id' => optional($data['audience'] ?? null)->id,
            'pishfactor_id' => optional($data['order'] ?? null)->id,
            'type' => $data['type'],
            'points' => $points,
            'amount' => $amount,
            'reason' => $data['reason'] ?? null,
            'description' => $data['description'] ?? null,
            'created_by' => $user->id,
        ]);

        $this->updateAccountAfterPoints($account, $points, $amount, $user);

        return $transaction;
    }

    private function accountForCustomer(Customers $customer, $user): CustomerLoyaltyAccount
    {
        return CustomerLoyaltyAccount::firstOrCreate(['customer_id' => $customer->id], [
            'tenant_id' => $customer->tenant_id ?: $this->tenantId($user),
            'organization_id' => $customer->organization_id ?: $this->organizationId($user),
            'tier' => 'bronze',
            'retention_status' => 'new',
            'points_balance' => 0,
            'lifetime_points' => 0,
            'lifetime_value' => 0,
            'last_activity_at' => now(),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    private function updateAccountAfterPoints(CustomerLoyaltyAccount $account, int $points, float $amount, $user): void
    {
        $newLifetimePoints = (int) $account->lifetime_points + max(0, $points);
        $newBalance = (int) $account->points_balance + $points;
        $newLifetimeValue = (float) $account->lifetime_value + max(0, $amount);

        $account->update([
            'points_balance' => max(0, $newBalance),
            'lifetime_points' => $newLifetimePoints,
            'lifetime_value' => $newLifetimeValue,
            'tier' => $this->tierForPoints($newLifetimePoints),
            'retention_status' => $this->retentionStatus($account, true),
            'last_purchase_at' => $amount > 0 ? now() : $account->last_purchase_at,
            'last_activity_at' => now(),
            'updated_by' => $user->id,
        ]);
    }

    private function updateAccountAfterValue(CustomerLoyaltyAccount $account, float $amount, $user): void
    {
        $account->update([
            'lifetime_value' => (float) $account->lifetime_value + $amount,
            'retention_status' => $this->retentionStatus($account, true),
            'last_purchase_at' => $amount > 0 ? now() : $account->last_purchase_at,
            'last_activity_at' => now(),
            'updated_by' => $user->id,
        ]);
    }

    private function resolveCampaign(int $campaignId, $user): CrmCampaign
    {
        $query = CrmCampaign::query();
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->findOrFail($campaignId);
    }

    private function resolveCustomer(int $customerId, $user): Customers
    {
        $query = Customers::query();
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->findOrFail($customerId);
    }

    private function resolveOrder(int $orderId, Customers $customer, $user): Pishfactor
    {
        $query = Pishfactor::query()->where('customer_id', $customer->id);
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query->findOrFail($orderId);
    }

    private function customerEstimatedValue(Customers $customer): float
    {
        if (!$customer->exists) {
            return 0.0;
        }

        return (float) Pishfactor::query()
            ->where('customer_id', $customer->id)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->sum(fn(Pishfactor $order) => $this->orderAmount($order));
    }

    private function orderAmount(Pishfactor $order): float
    {
        return (float) str_replace([',', ' '], '', (string) ($order->fullPrice ?: $order->pat_price ?: 0));
    }

    private function tierForPoints(int $points): string
    {
        return match (true) {
            $points >= 5000 => 'platinum',
            $points >= 2000 => 'gold',
            $points >= 500 => 'silver',
            default => 'bronze',
        };
    }

    private function retentionStatus(CustomerLoyaltyAccount $account, bool $hasCurrentActivity = false): string
    {
        if ($hasCurrentActivity) {
            return (int) $account->lifetime_points > 0 || (float) $account->lifetime_value > 0 ? 'loyal' : 'new';
        }

        if (!$account->last_activity_at) {
            return 'new';
        }

        $days = $account->last_activity_at->diffInDays(now());

        return match (true) {
            $days > 180 => 'lost',
            $days > 90 => 'at_risk',
            (int) $account->lifetime_points > 0 || (float) $account->lifetime_value > 0 => 'loyal',
            default => 'new',
        };
    }

    private function tenantId($user): ?int
    {
        return $user ? (int) ($user->tenant_id ?: $user->tenants_id) ?: null : null;
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
