<?php

namespace App\Filament\Concerns;

/**
 * Role-based access for Filament resources.
 * Set $viewRoles (can list/view/create/edit) and $deleteRoles (can delete) on the resource.
 */
trait RestrictedToRoles
{
    protected static function userHasRole(array $roles): bool
    {
        return (bool) auth()->user()?->hasAnyRole($roles);
    }

    public static function canViewAny(): bool
    {
        return static::userHasRole(static::$viewRoles);
    }

    public static function canView($record): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::userHasRole(static::$editRoles ?? static::$viewRoles);
    }

    public static function canEdit($record): bool
    {
        return static::userHasRole(static::$editRoles ?? static::$viewRoles);
    }

    public static function canDelete($record): bool
    {
        return static::userHasRole(static::$deleteRoles);
    }

    public static function canDeleteAny(): bool
    {
        return static::userHasRole(static::$deleteRoles);
    }
}
