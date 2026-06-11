<?php

namespace App\Services;

use App\Models\CrmServiceTicket;
use App\Models\CustomerPortalAccount;
use App\Models\CustomerPortalAnnouncement;
use App\Models\CustomerPortalPayment;
use App\Models\CustomerPortalRequest;
use App\Models\CommissionSettlement;
use App\Models\Customers;
use App\Models\Pishfactor;
use App\Models\User;
use Throwable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CustomerPortalService
{
    public function adminState($user): array
    {
        return [
            'accounts' => $this->accountQuery($user)->with('customer')->latest('id')->paginate(20),
            'requests' => $this->requestQuery($user)->with(['customer', 'account'])->latest('id')->limit(40)->get(),
            'payments' => $this->paymentQuery($user)->with(['customer', 'account', 'order', 'accountingVoucher'])->latest('id')->limit(40)->get(),
            'announcements' => $this->announcementQuery($user)->latest('id')->limit(20)->get(),
            'customers' => $this->customerQuery($user)->limit(250)->get(),
            'users' => $this->userQuery($user)->limit(250)->get(),
            'stats' => [
                'active_accounts' => (clone $this->accountQuery($user))->where('status', 'active')->count(),
                'representatives' => (clone $this->accountQuery($user))->where('role', 'representative')->count(),
                'open_requests' => (clone $this->requestQuery($user))->whereIn('status', ['new', 'in_review'])->count(),
                'pending_payments' => (clone $this->paymentQuery($user))->whereIn('status', ['initiated', 'submitted'])->count(),
                'announcements' => (clone $this->announcementQuery($user))->where('is_active', true)->count(),
            ],
        ];
    }

    public function createAccess($user, array $data): array
    {
        $customer = $this->resolveCustomer((int) $data['customer_id'], $user);
        $token = Str::random(64);

        $account = CustomerPortalAccount::create([
            'tenant_id' => $customer->tenant_id ?: $this->tenantId($user),
            'organization_id' => $customer->organization_id ?: $this->organizationId($user),
            'customer_id' => $customer->id,
            'user_id' => $data['user_id'] ?? null,
            'role' => $data['role'],
            'access_token' => $token,
            'status' => 'active',
            'title' => $data['title'] ?? null,
            'contact_name' => $data['contact_name'] ?? $customer->name,
            'contact_mobile' => $data['contact_mobile'] ?? $customer->mobile,
            'contact_email' => $data['contact_email'] ?? null,
            'permissions' => $data['permissions'] ?? $this->defaultPermissions($data['role']),
            'expires_at' => $data['expires_at'] ?? null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return [$account, $token];
    }

    public function createAnnouncement($user, array $data): CustomerPortalAnnouncement
    {
        return CustomerPortalAnnouncement::create([
            'tenant_id' => $this->tenantId($user),
            'organization_id' => $this->organizationId($user),
            'audience_type' => $data['audience_type'],
            'priority' => $data['priority'],
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    public function publicState(string $token): array
    {
        $account = CustomerPortalAccount::query()->with('customer')->where('access_token', $token)->firstOrFail();
        abort_unless($account->isAccessible(), 403);

        $account->update(['last_login_at' => now()]);
        $customer = $account->customer;
        $orders = Pishfactor::query()
            ->where('customer_id', $customer->id)
            ->latest('id')
            ->limit(15)
            ->get();
        $tickets = CrmServiceTicket::query()
            ->where('customer_id', $customer->id)
            ->latest('id')
            ->limit(10)
            ->get();
        $requests = CustomerPortalRequest::query()
            ->where('customer_portal_account_id', $account->id)
            ->latest('id')
            ->limit(15)
            ->get();
        $payments = CustomerPortalPayment::query()
            ->with('accountingVoucher')
            ->where('customer_portal_account_id', $account->id)
            ->latest('id')
            ->limit(12)
            ->get();
        $commissions = $this->commissionQuery($account)->limit(12)->get();

        return [
            'account' => $account,
            'customer' => $customer,
            'orders' => $orders,
            'tickets' => $tickets,
            'requests' => $requests,
            'payments' => $payments,
            'commissions' => $commissions,
            'announcements' => $this->announcementsFor($account),
            'stats' => [
                'orders' => $orders->count(),
                'open_tickets' => $tickets->whereIn('status', ['open', 'pending'])->count(),
                'open_requests' => $requests->whereIn('status', ['new', 'in_review'])->count(),
                'pending_payments' => $payments->whereIn('status', ['initiated', 'submitted'])->count(),
                'commission_payable' => $commissions->whereIn('status', ['calculated', 'approved'])->sum('payable_amount'),
                'total_orders_amount' => $orders->sum(fn(Pishfactor $order) => $this->numericAmount($order->fullPrice ?: $order->pat_price)),
            ],
        ];
    }

    public function submitPublicRequest(string $token, array $data): CustomerPortalRequest
    {
        $state = $this->publicState($token);
        $account = $state['account'];
        $customer = $state['customer'];

        return DB::transaction(function () use ($account, $customer, $data) {
            $ticketId = null;
            if (in_array($data['type'], ['support', 'complaint'], true)) {
                $ticket = CrmServiceTicket::create([
                    'tenant_id' => $account->tenant_id,
                    'organization_id' => $account->organization_id,
                    'customer_id' => $customer->id,
                    'type' => $data['type'] === 'complaint' ? 'complaint' : 'support',
                    'channel' => 'website',
                    'priority' => $data['priority'],
                    'status' => 'open',
                    'subject' => $data['subject'],
                    'contact_name' => $account->contact_name,
                    'contact_phone' => $account->contact_mobile,
                    'description' => $data['description'] ?? null,
                    'created_by' => null,
                    'updated_by' => null,
                ]);
                $ticketId = $ticket->id;
            }

            return CustomerPortalRequest::create([
                'tenant_id' => $account->tenant_id,
                'organization_id' => $account->organization_id,
                'customer_portal_account_id' => $account->id,
                'customer_id' => $customer->id,
                'crm_service_ticket_id' => $ticketId,
                'type' => $data['type'],
                'priority' => $data['priority'],
                'status' => 'new',
                'subject' => $data['subject'],
                'description' => $data['description'] ?? null,
                'requested_amount' => $data['requested_amount'] ?? 0,
                'metadata' => ['source' => 'customer_portal'],
                'submitted_at' => now(),
            ]);
        });
    }

    public function updateRequest($user, CustomerPortalRequest $request, array $data): CustomerPortalRequest
    {
        abort_unless($this->requestQuery($user)->whereKey($request->id)->exists(), 403);

        $request->update([
            'status' => $data['status'],
            'response' => $data['response'] ?? $request->response,
            'responded_at' => in_array($data['status'], ['answered', 'closed'], true) ? now() : $request->responded_at,
            'closed_at' => $data['status'] === 'closed' ? now() : $request->closed_at,
            'updated_by' => $user->id,
        ]);

        return $request->refresh();
    }

    public function submitPayment(string $token, array $data): CustomerPortalPayment
    {
        $state = $this->publicState($token);
        $account = $state['account'];
        $customer = $state['customer'];
        $order = null;

        if (!empty($data['pishfactor_id'])) {
            $order = Pishfactor::query()
                ->where('customer_id', $customer->id)
                ->whereKey((int) $data['pishfactor_id'])
                ->firstOrFail();
        }

        return DB::transaction(function () use ($account, $customer, $order, $data) {
            $amount = $this->numericAmount($data['amount']);
            $isGateway = ($data['payment_method'] ?? null) === 'online_gateway';
            $payment = CustomerPortalPayment::create([
                'tenant_id' => $account->tenant_id,
                'organization_id' => $account->organization_id,
                'customer_portal_account_id' => $account->id,
                'customer_id' => $customer->id,
                'pishfactor_id' => $order ? $order->id : null,
                'amount' => $amount,
                'payable_amount' => $amount,
                'status' => $isGateway ? 'initiated' : 'submitted',
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'proof_text' => $data['proof_text'] ?? null,
                'metadata' => ['source' => 'customer_portal', 'order_number' => $order ? $order->id : null, 'gateway_requested' => $isGateway],
                'requested_at' => now(),
                'submitted_at' => $isGateway ? null : now(),
            ]);

            $request = CustomerPortalRequest::create([
                'tenant_id' => $account->tenant_id,
                'organization_id' => $account->organization_id,
                'customer_portal_account_id' => $account->id,
                'customer_id' => $customer->id,
                'pishfactor_id' => $order ? $order->id : null,
                'type' => 'payment_followup',
                'priority' => 'normal',
                'status' => 'new',
                'subject' => $isGateway ? 'شروع پرداخت آنلاین پورتال' : 'ثبت پرداخت پورتال',
                'description' => 'پرداخت به مبلغ ' . number_format($amount) . ' با روش ' . (CustomerPortalPayment::METHODS[$data['payment_method']] ?? $data['payment_method']) . ' ثبت شد.',
                'requested_amount' => $amount,
                'metadata' => ['payment_id' => $payment->id, 'reference_number' => $payment->reference_number],
                'submitted_at' => now(),
            ]);

            $payment->update(['customer_portal_request_id' => $request->id]);

            return $payment->refresh();
        });
    }

    public function updatePayment($user, CustomerPortalPayment $payment, array $data): CustomerPortalPayment
    {
        abort_unless($this->paymentQuery($user)->whereKey($payment->id)->exists(), 403);

        $payment->update([
            'status' => $data['status'],
            'reference_number' => $data['reference_number'] ?? $payment->reference_number,
            'proof_text' => $data['proof_text'] ?? $payment->proof_text,
            'verified_at' => $data['status'] === 'verified' ? now() : $payment->verified_at,
            'rejected_at' => $data['status'] === 'rejected' ? now() : $payment->rejected_at,
            'verified_by' => in_array($data['status'], ['verified', 'rejected'], true) ? $user->id : $payment->verified_by,
            'updated_by' => $user->id,
        ]);

        if ($payment->request) {
            $payment->request->update([
                'status' => $data['status'] === 'verified' ? 'closed' : ($data['status'] === 'rejected' ? 'answered' : 'in_review'),
                'response' => $data['response'] ?? $payment->request->response,
                'responded_at' => in_array($data['status'], ['verified', 'rejected'], true) ? now() : $payment->request->responded_at,
                'closed_at' => $data['status'] === 'verified' ? now() : $payment->request->closed_at,
                'updated_by' => $user->id,
            ]);
        }

        $payment = $payment->refresh();
        $this->settleVerifiedPayment($payment, $user);

        return $payment->refresh();
    }

    public function verifyGatewayPayment(string $token, CustomerPortalPayment $payment, array $verification): CustomerPortalPayment
    {
        $state = $this->publicState($token);
        $account = $state['account'];
        abort_unless((int) $payment->customer_portal_account_id === (int) $account->id, 403);

        $metadata = $payment->metadata ?: [];
        $metadata['gateway_verification'] = [
            'success' => (bool) $verification['success'],
            'authority' => $verification['authority'] ?? null,
            'message' => $verification['message'] ?? null,
            'raw' => $verification['raw'] ?? [],
            'verified_at' => now()->toDateTimeString(),
        ];

        $payment->update([
            'status' => $verification['success'] ? 'verified' : 'rejected',
            'reference_number' => $verification['reference_number'] ?? $payment->reference_number,
            'proof_text' => $verification['message'] ?? $payment->proof_text,
            'submitted_at' => $verification['success'] ? now() : $payment->submitted_at,
            'verified_at' => $verification['success'] ? now() : $payment->verified_at,
            'rejected_at' => $verification['success'] ? $payment->rejected_at : now(),
            'metadata' => $metadata,
        ]);

        if ($payment->request) {
            $payment->request->update([
                'status' => $verification['success'] ? 'closed' : 'answered',
                'response' => $verification['message'] ?? $payment->request->response,
                'responded_at' => now(),
                'closed_at' => $verification['success'] ? now() : $payment->request->closed_at,
            ]);
        }

        $payment = $payment->refresh();
        $this->settleVerifiedPayment($payment);

        return $payment->refresh();
    }

    private function settleVerifiedPayment(CustomerPortalPayment $payment, $user = null): void
    {
        if ($payment->status !== 'verified') {
            return;
        }

        try {
            app(AccountingPostingService::class)->postCustomerPortalPaymentVoucher($payment, $user);
        } catch (Throwable $exception) {
            $metadata = $payment->metadata ?: [];
            $metadata['accounting_settlement_error'] = [
                'message' => $exception->getMessage(),
                'failed_at' => now()->toDateTimeString(),
            ];

            $updates = ['metadata' => $metadata];
            if (Schema::hasColumn('customer_portal_payments', 'gateway_settlement_status')) {
                $updates['gateway_settlement_status'] = 'failed';
            }

            $payment->update($updates);
        }
    }

    private function announcementsFor(CustomerPortalAccount $account)
    {
        return CustomerPortalAnnouncement::query()
            ->where('is_active', true)
            ->where(function ($query) use ($account) {
                $query->whereNull('tenant_id')->orWhere('tenant_id', $account->tenant_id);
            })
            ->where(function ($query) use ($account) {
                $query->whereNull('organization_id')->orWhere('organization_id', $account->organization_id);
            })
            ->whereIn('audience_type', ['all', $account->role])
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest('id')
            ->limit(8)
            ->get();
    }

    private function accountQuery($user)
    {
        $query = CustomerPortalAccount::query();
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function requestQuery($user)
    {
        $query = CustomerPortalRequest::query();
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function paymentQuery($user)
    {
        $query = CustomerPortalPayment::query();
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function announcementQuery($user)
    {
        $query = CustomerPortalAnnouncement::query();
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function customerQuery($user)
    {
        $columns = array_values(array_filter(['id', 'name', 'mobile', 'phone', Schema::hasColumn('customers', 'tenant_id') ? 'tenant_id' : null, 'organization_id']));
        $query = Customers::query()->select($columns)->latest('id');
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function userQuery($user)
    {
        $query = User::query()->select(['id', 'name', 'mobile', 'tenant_id', 'tenants_id', 'organization_id'])->where('isActive', 1)->orderBy('name');
        if ((int) $user->isGod !== 1) {
            $query->forOrganizations($user);
        }

        return $query;
    }

    private function commissionQuery(CustomerPortalAccount $account)
    {
        $query = CommissionSettlement::query()->with('plan')->latest('period_end')->latest('id');
        if ($account->role !== 'representative' || !$account->user_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where('user_id', $account->user_id)
            ->where(function ($scope) use ($account) {
                $scope->whereNull('tenant_id')->orWhere('tenant_id', $account->tenant_id);
            })
            ->where(function ($scope) use ($account) {
                $scope->whereNull('organization_id')->orWhere('organization_id', $account->organization_id);
            });
    }

    private function resolveCustomer(int $customerId, $user): Customers
    {
        return $this->customerQuery($user)->findOrFail($customerId);
    }

    private function defaultPermissions(string $role): array
    {
        return $role === 'representative'
            ? ['orders.view', 'orders.request', 'tickets.create', 'commission.view', 'announcements.view']
            : ['orders.view', 'tickets.create', 'payments.followup', 'announcements.view'];
    }

    private function numericAmount($value): float
    {
        return (float) str_replace([',', ' '], '', (string) $value);
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
