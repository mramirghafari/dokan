<?php



namespace App\Services;



use App\Models\User;



class PanelDashboardWidgetService

{

    public function __construct(

        private SettingService $settings,

        private TenantContextService $tenantContext

    ) {

    }



    public function definitions(): array

    {

        return collect((array) config('panel_settings.definitions', []))

            ->filter(fn ($definition) => ($definition['group'] ?? '') === 'dashboard_widgets')

            ->map(fn ($definition, $key) => array_merge($definition, ['key' => $key]))

            ->values()

            ->all();

    }



    public function visibilityMap(?User $user = null, array $context = []): array

    {

        $context = $context ?: $this->tenantContext->settingContext($user);

        $map = [];



        foreach ($this->definitions() as $definition) {

            $key = $definition['key'];

            $map[$key] = $this->isEnabled($key, $context);

        }



        return $map;

    }



    public function isEnabled(string $key, array $context = []): bool

    {

        $definition = config("panel_settings.definitions.$key");



        if (!$definition) {

            return false;

        }



        $default = ($definition['default'] ?? 'no') === 'yes' ? 'yes' : 'no';

        $value = $this->settings->get($key, $context, $default);



        return in_array($value, ['yes', '1', 1, true, 'on'], true);

    }

}


