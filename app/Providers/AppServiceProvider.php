<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The admin UI uses Bootstrap components, so force Bootstrap pagination views.
        Paginator::useBootstrapFive();

        // Force HTTPS in production (Railway runs behind a TLS-terminating proxy)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
