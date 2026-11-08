<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Case-insensitive search over the JSON `name` column (both languages).
 * MariaDB stores JSON with a binary collation, so a plain LIKE is case-sensitive.
 */
trait SearchesTranslatedName
{
    public function scopeSearchName(Builder $query, string $term): Builder
    {
        $like = '%'.mb_strtolower(addcslashes($term, '%_\\')).'%';

        return $query->whereRaw('LOWER(name) LIKE ?', [$like]);
    }
}
