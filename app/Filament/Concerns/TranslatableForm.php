<?php

namespace App\Filament\Concerns;

use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;

/**
 * Bilingual fields: form state uses name.bn / name.en; spatie/laravel-translatable stores the array.
 * Used by Edit pages to load all translations instead of only the current locale.
 */
trait TranslatableForm
{
    /** @param  callable(string $locale): array  $fields */
    public static function localeTabs(callable $fields): Tabs
    {
        return Tabs::make('language')->tabs([
            Tab::make('বাংলা')->schema($fields('bn')),
            Tab::make('English')->schema($fields('en')),
        ])->columnSpanFull();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($this->getRecord()->translatable as $field) {
            $data[$field] = $this->getRecord()->getTranslations($field);
        }

        return $data;
    }
}
