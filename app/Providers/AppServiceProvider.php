<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
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

        // Breezy registers its profile/2FA Livewire components only while the admin panel boots, but
        // Livewire verifies a component on /livewire/update before that happens → "419 Page Expired"
        // on every 2FA/profile action. Register them up front.
        Livewire::component('two_factor_authentication', \Jeffgreco13\FilamentBreezy\Livewire\TwoFactorAuthentication::class);
        Livewire::component('personal_info', \Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo::class);
        Livewire::component('update_password', \Jeffgreco13\FilamentBreezy\Livewire\UpdatePassword::class);
        Livewire::component('two-factor-page', \Jeffgreco13\FilamentBreezy\Pages\TwoFactorPage::class);

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
