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
        if (request()->isSecure() || str_contains(request()->getHost(), 'ngrok')) {
            URL::forceScheme('https');
        } else {
            URL::forceScheme('http');
        }
        
        // Custom route model binding for GuestGroup
        Route::model('group', GuestGroup::class);
    }
}
