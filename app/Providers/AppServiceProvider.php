<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerActivitylogAliases();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // scramble
        Gate::define('viewApiDocs', function (User $user) {
            return $user->hasRole(['super_admin', 'admin', 'apiuser']);
        });
    }

    private function registerActivitylogAliases(): void
    {
        if (
            trait_exists('Spatie\\Activitylog\\Models\\Concerns\\LogsActivity')
            && ! trait_exists('Spatie\\Activitylog\\Traits\\LogsActivity', false)
        ) {
            class_alias(
                'Spatie\\Activitylog\\Models\\Concerns\\LogsActivity',
                'Spatie\\Activitylog\\Traits\\LogsActivity'
            );
        }

        if (
            class_exists('Spatie\\Activitylog\\Support\\LogOptions')
            && ! class_exists('Spatie\\Activitylog\\LogOptions', false)
        ) {
            class_alias(
                'Spatie\\Activitylog\\Support\\LogOptions',
                'Spatie\\Activitylog\\LogOptions'
            );
        }
    }
}
