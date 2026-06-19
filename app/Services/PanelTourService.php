<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class PanelTourService
{
    public function menuTexts(): array
    {
        $menus = config('panel_tours.menus', []);

        return collect($menus)->mapWithKeys(function ($item, $key) {
            return [$key => [
                'title' => $item['title'] ?? $key,
                'text' => $item['text'] ?? '',
                'expand' => !empty($item['expand']),
            ]];
        })->all();
    }

    public function pageSteps(?string $routeName = null): array
    {
        $routeName = $routeName ?? Route::currentRouteName();
        $pages = config('panel_tours.pages', []);
        $patterns = array_merge(
            config('panel_tours.route_patterns', []),
            config('panel_tour_workflows', [])
        );
        $fallback = config('panel_tours.fallback', []);

        if ($routeName && isset($pages[$routeName])) {
            return $this->normalizeSteps($pages[$routeName]);
        }

        if ($routeName) {
            $matched = $this->matchRoutePattern($routeName, $patterns);

            if ($matched !== null) {
                if (in_array($routeName, ['settings.index', 'settings.salesScenario'], true)) {
                    $matched = $this->mergeSettingsTourSteps($routeName, $matched);
                }

                return $this->normalizeSteps($matched);
            }
        }

        foreach ($pages as $pattern => $steps) {
            if ($routeName && str_ends_with($routeName, '.' . $pattern)) {
                return $this->normalizeSteps($steps);
            }
        }

        return $this->normalizeSteps($fallback);
    }

    public function fullDashboardTourSteps(): array
    {
        $steps = [
            [
                'title' => 'کارت راه‌اندازی',
                'text' => 'پیشرفت کلی پنل، درصد تکمیل و آمار مراحل انجام‌شده و باقی‌مانده را اینجا می‌بینید.',
                'target' => '.ob-hero',
                'optional' => true,
            ],
            [
                'title' => 'نوار پیشرفت',
                'text' => 'هر مرحله‌ای که تکمیل کنید، این نوار جلو می‌رود تا بفهمید چقدر تا داشبورد کامل فاصله دارید.',
                'target' => '.ob-hero__progress-wrap',
                'optional' => true,
            ],
            [
                'title' => 'مراحل راه‌اندازی',
                'text' => 'هر کارت یک مرحله است. روی «شروع» بزنید تا مستقیم به همان بخش بروید.',
                'target' => '.ob-grid',
                'optional' => true,
            ],
            [
                'title' => 'تور راهنما',
                'text' => 'هر زمان روی این دکمه بزنید، تور اختصاصی همان صفحه اجرا می‌شود.',
                'target' => '#panel-tour-trigger',
            ],
            [
                'title' => 'اعلانات',
                'text' => 'پیام‌ها و هشدارهای مهم پنل اینجا نمایش داده می‌شوند.',
                'target' => '.dropdown-notifications',
            ],
            [
                'title' => 'سوییچ پنل',
                'text' => 'اگر چند پنل دارید، از اینجا بین آن‌ها جابه‌جا شوید.',
                'target' => '.dropdown-panel-switch, .navbar-current-panel',
                'optional' => true,
            ],
            [
                'title' => 'منوی کناری',
                'text' => 'تمام بخش‌های پنل در این منوست. از اینجا به فروش، انبار، مالی، CRM و تنظیمات دسترسی دارید. در ادامه هر آیتم را جداگانه توضیح می‌دهیم.',
                'target' => '#layout-menu',
            ],
        ];

        return $this->normalizeSteps($steps);
    }

    public function tourPayload(): array
    {
        return [
            'menuTexts' => $this->menuTexts(),
            'pageSteps' => $this->pageSteps(),
            'route' => Route::currentRouteName(),
            'intro' => $this->introSteps(),
            'onboardingRoutes' => Auth::check() ? [
                'welcome' => route('panel.onboarding.welcome'),
                'tour' => route('panel.onboarding.tour'),
                'complete' => route('panel.onboarding.complete'),
            ] : [],
        ];
    }

    public function introSteps(?string $routeName = null): array
    {
        $routeName = $routeName ?? Route::currentRouteName();
        $routesWithHeaderIntro = ['index'];

        if (!in_array($routeName, $routesWithHeaderIntro, true)) {
            return [];
        }

        return $this->normalizeSteps(config('panel_tours.intro', []));
    }

    private function normalizeSteps(array $steps): array
    {
        return array_values(array_map(function ($step) {
            return [
                'title' => $step['title'] ?? '',
                'text' => $step['text'] ?? '',
                'target' => $step['target'] ?? null,
                'optional' => !empty($step['optional']),
                'expand' => !empty($step['expand']),
                'openMenu' => $step['openMenu'] ?? null,
                'activateTab' => $step['activateTab'] ?? null,
                'scroll' => $step['scroll'] ?? null,
            ];
        }, $steps));
    }

    private function mergeSettingsTourSteps(string $routeName, array $workflowSteps): array
    {
        $outroIndex = null;

        foreach ($workflowSteps as $index => $step) {
            if ($this->isSettingsOutroStep($step)) {
                $outroIndex = $index;
                break;
            }
        }

        $fieldSteps = app(PanelTourSettingsStepBuilder::class)->buildFieldSteps($routeName);

        if ($outroIndex === null) {
            return array_merge($workflowSteps, $fieldSteps);
        }

        return array_merge(
            array_slice($workflowSteps, 0, $outroIndex),
            $fieldSteps,
            array_slice($workflowSteps, $outroIndex)
        );
    }

    private function isSettingsOutroStep(array $step): bool
    {
        $target = (string) ($step['target'] ?? '');

        return str_contains($target, '#tour-settings-save')
            || str_contains($target, '#panel-tour-trigger');
    }

    private function matchRoutePattern(string $routeName, array $patterns): ?array
    {
        $keys = array_keys($patterns);
        usort($keys, fn (string $a, string $b) => strlen($b) <=> strlen($a));

        foreach ($keys as $key) {
            if (str_ends_with($key, '.*')) {
                $prefix = substr($key, 0, -2);
                if (str_starts_with($routeName, $prefix)) {
                    return $patterns[$key];
                }

                continue;
            }

            if ($routeName === $key || str_starts_with($routeName, $key . '.')) {
                return $patterns[$key];
            }
        }

        return null;
    }
}
