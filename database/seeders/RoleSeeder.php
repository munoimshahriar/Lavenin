<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Access per role is enforced in each Filament resource (see App\Filament\Concerns\RestrictedToRoles). */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['owner', 'manager', 'staff'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
