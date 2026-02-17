<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use App\Shared\Models\GuestGroup;

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
        // Set the correct scheme based on the request
        if ($this->app->environment('production') || str_contains(request()->getHost(), 'railway.app')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');
        }
        
        // Custom route model binding for GuestGroup
        Route::model('group', GuestGroup::class);
    }
}
