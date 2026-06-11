<?php

namespace Database\Seeders;

use App\Services\ScaleTestSeedService;
use Illuminate\Database\Seeder;

class ScaleTestSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(ScaleTestSeedService::class);

        if (config('erp_scale.load_test.fresh', false)) {
            $service->purge();
        }

        $report = $service->seed();
        $this->command?->info('scale_test_seed=ok ' . json_encode($report, JSON_UNESCAPED_UNICODE));
    }
}
