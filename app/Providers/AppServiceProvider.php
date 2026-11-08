<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Translatable\Facades\Translatable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // If an English (or Bangla) translation is missing, show whichever one exists.
        Translatable::fallback(fallbackAny: true);

        Paginator::defaultView('pagination::tailwind');

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
