<?php

namespace App\Services;

use App\Models\TreasuryChequeBook;
use App\Models\TreasuryInstrument;
use Carbon\Carbon;

class TreasuryAlertService
{
    public function build($user, int $days = 7): array
    {
        $days = max(1, min(60, $days));
        $today = now()->toDateString();
        $toDate = Carbon::parse($today)->addDays($days)->toDateString();

        $openStatuses = ['received', 'deposited', 'issued'];
        $chequeQuery = TreasuryInstrument::with(['counterAccount', 'currentHolderAccount'])
            ->where('instrument_type', 'cheque')
            ->whereIn('status', $openStatuses)
            ->whereNotNull('due_date')
            ->when((int) $user?->isGod !== 1, fn($query) => $query->where('tenant_id', $this->tenantId($user)));

        $overdueCheques = (clone $chequeQuery)
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date')
            ->get();
        $upcomingCheques = (clone $chequeQuery)
            ->whereBetween('due_date', [$today, $toDate])
            ->orderBy('due_date')
            ->get();

        $books = TreasuryChequeBook::withCount([
            'leaves as available_leaves_count' => fn($query) => $query->where('status', 'available'),
            'leaves as issued_leaves_count' => fn($query) => $query->where('status', 'issued'),
        ])
            ->with('account')
            ->whereIn('status', ['active', 'finished'])
            ->when((int) $user?->isGod !== 1, fn($query) => $query->where('tenant_id', $this->tenantId($user)))
            ->orderBy('status')
            ->orderBy('id')
            ->get();

        $lowLeafBooks = $books->filter(fn($book) => (int) $book->available_leaves_count <= (int) $book->warning_threshold)->values();

        return [
            'days' => $days,
            'today' => $today,
            'to_date' => $toDate,
            'overdue_cheques' => $overdueCheques,
            'upcoming_cheques' => $upcomingCheques,
            'low_leaf_books' => $lowLeafBooks,
            'summary' => [
                'overdue_count' => $overdueCheques->count(),
                'overdue_amount' => round((float) $overdueCheques->sum('amount'), 2),
                'upcoming_count' => $upcomingCheques->count(),
                'upcoming_amount' => round((float) $upcomingCheques->sum('amount'), 2),
                'low_leaf_book_count' => $lowLeafBooks->count(),
                'available_leaf_count' => (int) $books->sum('available_leaves_count'),
            ],
        ];
    }

    private function tenantId($user): ?int
    {
        return $user?->tenant_id ?: $user?->tenants_id;
    }
}
