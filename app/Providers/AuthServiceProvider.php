<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        try {
            foreach (Permission::with('aliases')->where('isActive', 1)->get() as $permission) {
                collect([$permission->title, $permission->canonical_title])
                    ->merge($permission->aliases->where('is_active', true)->pluck('alias_title'))
                    ->filter()
                    ->unique()
                    ->each(function ($ability) use ($permission) {
                        Gate::define($ability, function ($user) use ($permission) {
                            return $user->hasPermission($permission);
                        });
                    });
            }
        } catch (\Exception $e) {
        }
    }
}
