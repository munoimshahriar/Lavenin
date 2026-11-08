<?php

namespace App\Support;

use Illuminate\Support\Str;

class Slug
{
    /** Rough Bangla → Latin transliteration so Bangla-only names still get readable URLs. */
    private const BN = [
        'ক্ষ' => 'ksh', 'জ্ঞ' => 'gg',
        'অ' => 'o', 'আ' => 'a', 'ই' => 'i', 'ঈ' => 'i', 'উ' => 'u', 'ঊ' => 'u', 'ঋ' => 'ri', 'এ' => 'e', 'ঐ' => 'oi', 'ও' => 'o', 'ঔ' => 'ou',
        'ক' => 'k', 'খ' => 'kh', 'গ' => 'g', 'ঘ' => 'gh', 'ঙ' => 'ng', 'চ' => 'ch', 'ছ' => 'chh', 'জ' => 'j', 'ঝ' => 'jh', 'ঞ' => 'n',
        'ট' => 't', 'ঠ' => 'th', 'ড' => 'd', 'ঢ' => 'dh', 'ণ' => 'n', 'ত' => 't', 'থ' => 'th', 'দ' => 'd', 'ধ' => 'dh', 'ন' => 'n',
        'প' => 'p', 'ফ' => 'f', 'ব' => 'b', 'ভ' => 'bh', 'ম' => 'm', 'য' => 'j', 'র' => 'r', 'ল' => 'l', 'শ' => 'sh', 'ষ' => 'sh',
        'স' => 's', 'হ' => 'h', 'ড়' => 'r', 'ঢ়' => 'rh', 'য়' => 'y', 'ৎ' => 't', 'ং' => 'ng', 'ঃ' => 'h', 'ঁ' => 'n',
        'া' => 'a', 'ি' => 'i', 'ী' => 'i', 'ু' => 'u', 'ূ' => 'u', 'ৃ' => 'ri', 'ে' => 'e', 'ৈ' => 'oi', 'ো' => 'o', 'ৌ' => 'ou', '্' => '',
        '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
    ];

    public static function make(?string $text): string
    {
        $slug = Str::slug(strtr((string) $text, self::BN));

        return $slug !== '' ? $slug : Str::lower(Str::random(8));
    }

    /** @param class-string<\Illuminate\Database\Eloquent\Model> $model */
    public static function unique(string $model, ?string $text): string
    {
        $base = self::make($text);
        $slug = $base;
        $i = 2;
        while ($model::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
