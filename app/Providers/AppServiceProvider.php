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
        if (
            request()->isSecure() 
            || request()->header('x-forwarded-proto') === 'https' 
            || str_contains(config('app.url'), 'https://')
            || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        ) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
