<?php

namespace App\Services;

use App\Models\Accounts;
use App\Models\TreasuryChequeBook;
use App\Models\TreasuryChequeLeaf;
use App\Models\TreasuryInstrument;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TreasuryChequeBookService
{
    public function createBook(array $payload, $user): TreasuryChequeBook
    {
        $account = Accounts::findOrFail((int) Arr::get($payload, 'account_id'));
        $this->ensureModelTenant($account, $user, 'حساب بانکی دسته چک');

        $first = (int) Arr::get($payload, 'first_leaf_number');
        $last = (int) Arr::get($payload, 'last_leaf_number');

        if ($first <= 0 || $last < $first) {
            throw ValidationException::withMessages(['first_leaf_number' => 'بازه شماره برگ های دسته چک معتبر نیست.']);
        }

        $leafCount = $last - $first + 1;
        if ($leafCount > 500) {
            throw ValidationException::withMessages(['last_leaf_number' => 'برای هر دسته چک حداکثر 500 برگ قابل ایجاد است.']);
        }

        $tenantId = $this->tenantId($user);
        $organizationId = $this->organizationId($user);

        return DB::transaction(function () use ($payload, $user, $tenantId, $organizationId, $account, $first, $last, $leafCount) {
            $book = TreasuryChequeBook::create([
                'tenant_id' => $tenantId,
                'organization_id' => $organizationId,
                'account_id' => $account->id,
                'book_number' => Arr::get($payload, 'book_number'),
                'cheque_prefix' => Arr::get($payload, 'cheque_prefix'),
                'first_leaf_number' => $first,
                'last_leaf_number' => $last,
                'next_leaf_number' => $first,
                'leaf_count' => $leafCount,
                'warning_threshold' => max(0, (int) Arr::get($payload, 'warning_threshold', 5)),
                'bank_name' => Arr::get($payload, 'bank_name') ?: $account->bank_name,
                'branch_name' => Arr::get($payload, 'branch_name') ?: $account->branch,
                'account_number' => Arr::get($payload, 'account_number') ?: $account->account_number,
                'status' => Arr::get($payload, 'status', 'active'),
                'description' => Arr::get($payload, 'description'),
                'created_by' => $user?->id,
            ]);

            for ($number = $first; $number <= $last; $number++) {
                TreasuryChequeLeaf::create([
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'treasury_cheque_book_id' => $book->id,
                    'account_id' => $account->id,
                    'leaf_number' => $this->formatLeafNumber($book->cheque_prefix, $number),
                    'status' => 'available',
                    'created_by' => $user?->id,
                ]);
            }

            return $book->load(['account', 'leaves']);
        });
    }

    public function issueLeaf(TreasuryChequeLeaf $leaf, TreasuryInstrument $instrument, $user): TreasuryChequeLeaf
    {
        $leaf->loadMissing('book');
        $this->ensureModelTenant($leaf, $user, 'برگ چک');

        if ($leaf->status !== 'available') {
            throw ValidationException::withMessages(['cheque_leaf_id' => 'برگ چک انتخاب شده قابل استفاده نیست.']);
        }

        if ((int) $leaf->account_id !== (int) $instrument->voucherItem?->account_id) {
            throw ValidationException::withMessages(['cheque_leaf_id' => 'برگ چک با حساب خزانه سند همخوانی ندارد.']);
        }

        return DB::transaction(function () use ($leaf, $instrument, $user) {
            $leaf->update([
                'treasury_instrument_id' => $instrument->id,
                'payee_account_id' => $instrument->counter_account_id,
                'status' => 'issued',
                'issued_date' => optional($instrument->status_date)->toDateString(),
                'due_date' => optional($instrument->due_date)->toDateString(),
                'amount' => $instrument->amount,
                'payee_name' => optional($instrument->counterAccount)->name,
                'description' => $instrument->description,
                'updated_by' => $user?->id,
            ]);

            $nextAvailable = TreasuryChequeLeaf::where('treasury_cheque_book_id', $leaf->treasury_cheque_book_id)
                ->where('status', 'available')
                ->orderBy('id')
                ->first();

            $leaf->book?->update([
                'next_leaf_number' => $nextAvailable ? (int) preg_replace('/\D+/', '', $nextAvailable->leaf_number) : null,
                'status' => $nextAvailable ? $leaf->book->status : 'finished',
                'updated_by' => $user?->id,
            ]);

            return $leaf->fresh(['book', 'instrument']) ?: $leaf;
        });
    }

    public function availableLeaves($user, ?int $accountId = null)
    {
        return TreasuryChequeLeaf::with(['book.account'])
            ->where('status', 'available')
            ->when($accountId, fn($query) => $query->where('account_id', $accountId))
            ->when((int) $user?->isGod !== 1, fn($query) => $query->where('tenant_id', $this->tenantId($user)))
            ->orderBy('account_id')
            ->orderBy('id')
            ->get();
    }

    private function formatLeafNumber(?string $prefix, int $number): string
    {
        return ($prefix ?: '') . $number;
    }

    private function ensureModelTenant($model, $user, string $label): void
    {
        if ((int) $user?->isGod === 1) {
            return;
        }

        $tenantId = (int) $this->tenantId($user);
        $modelTenantId = (int) ($model->tenant_id ?: $model->tenants_id);

        if ($tenantId !== $modelTenantId) {
            throw ValidationException::withMessages(['tenant' => $label . ' با پنل فعلی همخوانی ندارد.']);
        }
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }

    private function organizationId($user): ?int
    {
        $organizationId = $user?->organization_id;
        $decoded = is_string($organizationId) ? json_decode($organizationId, true) : null;

        if (is_array($decoded)) {
            return isset($decoded[0]) ? (int) $decoded[0] : null;
        }

        return $organizationId ? (int) $organizationId : null;
    }
}
