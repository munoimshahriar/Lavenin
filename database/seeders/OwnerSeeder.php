<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Creates the store owner account from OWNER_NAME / OWNER_EMAIL / OWNER_PASSWORD in .env.
 * If OWNER_PASSWORD is empty a random one is generated and printed once.
 */
class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('lavenin.owner.email');

        if (User::where('email', $email)->exists()) {
            return;
        }

        $password = config('lavenin.owner.password') ?: Str::password(16, symbols: false);

        $user = User::create([
            'name' => config('lavenin.owner.name'),
            'email' => $email,
            'password' => $password,
        ]);
        $user->assignRole('owner');

        if (! config('lavenin.owner.password')) {
            $this->command?->warn("Owner account: {$email} / {$password}  (change it after first login)");
        }
    }
}
