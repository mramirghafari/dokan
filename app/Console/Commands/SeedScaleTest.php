<?php

namespace App\Console\Commands;

use App\Services\ScaleTestSeedService;
use Illuminate\Console\Command;

class SeedScaleTest extends Command
{
    protected $signature = 'erp:seed-scale-test
                            {--fresh : Remove previous STSCALE rows before seeding}
                            {--customers= : Override customer count}
                            {--products= : Override product count}
                            {--pishfactors= : Override invoice count}';

    protected $description = 'Seed one tenant with scale-test customers, products and invoices.';

    public function handle(ScaleTestSeedService $service): int
    {
        if ($this->option('fresh')) {
            $deleted = $service->purge();
            $this->warn('scale_test_purge=' . json_encode($deleted));
        }

        $overrides = array_filter([
            'customers' => $this->option('customers'),
            'products' => $this->option('products'),
            'pishfactors' => $this->option('pishfactors'),
        ], fn ($value) => $value !== null && $value !== '');

        $report = $service->seed($overrides);
        $this->info('scale_test_seed=ok ' . json_encode($report, JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
