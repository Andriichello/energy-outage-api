<?php

namespace App\Providers;

use App\Models\UpdatedInformation;
use App\Observers\UpdatedInformationObserver;
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
        UpdatedInformation::observe(UpdatedInformationObserver::class);
    }
}
