<?php

namespace App\Providers;

use App\Models\Customers;
use App\Models\License;
use App\Models\Pishfactor;
use App\Services\CustomerListSummaryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerCustomerListSummaryInvalidation();
        $this->registerSlowQueryMonitor();

        view()->composer('*', function ($view) {
            $user = Auth::user();

            if ($user) {
                $user->loadMissing('roles');
                $panels = app(\App\Services\PanelMembershipService::class);
                $view->with('availablePanels', $panels->accessiblePanelsForUser($user));
                $view->with('activePanel', $panels->activePanel($user));
                $view->with('userSideLabel', $panels->roleLabelForActivePanel($user));
            }

            $view->with('user', $user);
        });
    }

    private function registerCustomerListSummaryInvalidation(): void
    {
        $invalidate = function (?Customers $customer) {
            if (!$customer) {
                return;
            }

            app(CustomerListSummaryService::class)->invalidateForCustomer($customer);
        };

        Customers::saved(static fn (Customers $customer) => $invalidate($customer));
        Customers::deleted(static fn (Customers $customer) => $invalidate($customer));

        Pishfactor::saved(function (Pishfactor $pishfactor) {
            app(CustomerListSummaryService::class)->invalidateForPishfactor($pishfactor);
        });

        Pishfactor::deleted(function (Pishfactor $pishfactor) {
            app(CustomerListSummaryService::class)->invalidateForPishfactor($pishfactor);
        });
    }

    private function registerSlowQueryMonitor(): void
    {
        if (!config('erp_scale.slow_query.enabled', true)) {
            return;
        }

        $threshold = (int) config('erp_scale.slow_query.threshold_ms', 750);
        $channel = (string) config('erp_scale.slow_query.channel', 'slow_query');

        DB::listen(function ($query) use ($threshold, $channel) {
            if ((float) $query->time < $threshold) {
                return;
            }

            Log::channel($channel)->warning('slow_query_detected', [
                'time_ms' => round((float) $query->time, 2),
                'connection' => $query->connectionName,
                'sql' => $query->sql,
                'route' => optional(request()->route())->getName(),
                'url' => request()->path(),
            ]);
        });
    }
}
