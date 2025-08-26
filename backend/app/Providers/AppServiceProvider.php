<?php

namespace App\Providers;

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
        // Force HTTPS when behind a proxy (like ngrok)
        if (config('app.env') === 'local' && request()->header('x-forwarded-proto') === 'https') {
            \URL::forceScheme('https');
        }
    }
}
