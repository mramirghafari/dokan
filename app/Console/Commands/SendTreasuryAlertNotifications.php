<?php

namespace App\Console\Commands;

use App\Models\Notifs;
use App\Models\User;
use App\Services\TreasuryAlertService;
use Illuminate\Console\Command;

class SendTreasuryAlertNotifications extends Command
{
    protected $signature = 'treasury:send-alert-notifications {--days=7 : Upcoming cheque alert horizon} {--user_id= : Send only for one user} {--dry-run : Count alerts without creating notifications}';

    protected $description = 'Create dashboard notifications for overdue cheques, upcoming cheques, and low cheque book leaves.';

    public function handle(TreasuryAlertService $alertService): int
    {
        $days = max(1, min(60, (int) $this->option('days')));
        $userId = $this->option('user_id');
        $dryRun = (bool) $this->option('dry-run');
        $created = 0;
        $skipped = 0;
        $candidates = 0;

        $query = User::query()
            ->when($userId, function ($query) use ($userId) {
                $query->where('id', $userId);
            }, function ($query) {
                $query->where('isActive', 1)
                    ->where(function ($query) {
                        $query->where('isAdmin', 1)->orWhere('isGod', 1);
                    });
            })
            ->orderBy('id');

        $query->chunkById(100, function ($users) use ($alertService, $days, $dryRun, &$created, &$skipped, &$candidates) {
            foreach ($users as $user) {
                $alerts = $alertService->build($user, $days);
                $payloads = $this->payloads($user, $alerts);
                $candidates += count($payloads);

                if ($dryRun) {
                    continue;
                }

                foreach ($payloads as $payload) {
                    $notification = Notifs::firstOrCreate([
                        'user_id' => $payload['user_id'],
                        'alert_key' => $payload['alert_key'],
                    ], $payload);

                    $notification->wasRecentlyCreated ? $created++ : $skipped++;
                }
            }
        });

        $mode = $dryRun ? 'Dry run' : 'Notifications sent';
        $this->info("{$mode}: candidates={$candidates}, created={$created}, skipped={$skipped}, days={$days}.");

        return self::SUCCESS;
    }

    private function payloads(User $user, array $alerts): array
    {
        $payloads = [];
        $tenantId = $user->tenant_id ?: $user->tenants_id;
        $now = now();
        $scheduledFor = $now->toDateString();

        foreach ($alerts['overdue_cheques'] as $cheque) {
            $payloads[] = [
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'title' => 'چک معوق خزانه',
                'content' => $this->chequeContent($cheque, 'از سررسید آن گذشته است'),
                'status' => false,
                'source' => 'treasury_alerts',
                'severity' => 'danger',
                'reference_type' => 'treasury_instrument',
                'reference_id' => $cheque->id,
                'alert_key' => 'treasury:overdue-cheque:' . $cheque->id . ':' . optional($cheque->due_date)->format('Y-m-d'),
                'scheduled_for' => $scheduledFor,
                'sent_at' => $now,
            ];
        }

        foreach ($alerts['upcoming_cheques'] as $cheque) {
            $payloads[] = [
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'title' => 'سررسید چک خزانه',
                'content' => $this->chequeContent($cheque, 'در افق هشدار به سررسید می رسد'),
                'status' => false,
                'source' => 'treasury_alerts',
                'severity' => 'warning',
                'reference_type' => 'treasury_instrument',
                'reference_id' => $cheque->id,
                'alert_key' => 'treasury:upcoming-cheque:' . $cheque->id . ':' . optional($cheque->due_date)->format('Y-m-d'),
                'scheduled_for' => $scheduledFor,
                'sent_at' => $now,
            ];
        }

        foreach ($alerts['low_leaf_books'] as $book) {
            $payloads[] = [
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'title' => 'کمبود برگ دسته چک',
                'content' => 'دسته چک ' . ($book->book_number ?: $book->id) . ' برای حساب ' . optional($book->account)->name . ' فقط ' . (int) $book->available_leaves_count . ' برگ آماده دارد.',
                'status' => false,
                'source' => 'treasury_alerts',
                'severity' => 'warning',
                'reference_type' => 'treasury_cheque_book',
                'reference_id' => $book->id,
                'alert_key' => 'treasury:low-leaf-book:' . $book->id,
                'scheduled_for' => $scheduledFor,
                'sent_at' => $now,
            ];
        }

        return $payloads;
    }

    private function chequeContent($cheque, string $message): string
    {
        $direction = $cheque->direction === 'incoming' ? 'دریافتنی' : 'پرداختنی';
        $counterparty = optional($cheque->counterAccount)->name ?: 'بدون طرف حساب';
        $dueDate = optional($cheque->due_date)->format('Y-m-d') ?: '-';

        return 'چک ' . $direction . ' شماره ' . ($cheque->cheque_number ?: $cheque->id) . ' به مبلغ ' . number_format((float) $cheque->amount) . ' برای ' . $counterparty . ' با سررسید ' . $dueDate . ' ' . $message . '.';
    }
}
