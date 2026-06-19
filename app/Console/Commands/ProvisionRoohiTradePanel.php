<?php

namespace App\Console\Commands;

use App\Services\RoohiTradePanelProvisioner;
use Illuminate\Console\Command;

class ProvisionRoohiTradePanel extends Command
{
    protected $signature = 'panel:provision-roohi-trade {--prices-only : فقط بازه‌های قیمت اشتراک را همگام کند}';

    protected $description = 'ایجاد یا به‌روزرسانی پنل روحی ترید با مدیر، محصولات اشتراکی و تنظیمات پایه';

    public function handle(RoohiTradePanelProvisioner $provisioner): int
    {
        $report = $this->option('prices-only')
            ? $provisioner->syncPricePeriodsOnly()
            : $provisioner->provision();

        $this->info('پنل روحی ترید آماده شد.');
        $this->table(
            ['مورد', 'مقدار'],
            collect($report)
                ->except(['product_ids'])
                ->map(fn ($value, $key) => [$key, is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value])
                ->values()
                ->all()
        );

        $this->line('محصولات: ' . implode(', ', $report['product_ids']));
        $this->line('ورود: موبایل 09364352460 | کاربری roohi | رمز Roohi@13');

        return self::SUCCESS;
    }
}
