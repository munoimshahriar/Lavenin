<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;

/**
 * Filament's auth check, but a logged-in customer (non-admin) who opens /manage is logged out and
 * sent to the admin login form instead of hitting a dead-end 403.
 */
class LogoutNonAdmins extends Authenticate
{
    protected function authenticate($request, array $guards): void
    {
        $user = Filament::auth()->user();

        if ($user && ! $user->canAccessPanel(Filament::getCurrentPanel())) {
            Filament::auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        parent::authenticate($request, $guards);
    }
}
