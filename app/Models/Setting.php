<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /** Defaults used when a key has never been saved in admin. */
    public const DEFAULTS = [
        'shipping_free_threshold' => 2000,
        'shipping_khulna_free_threshold' => 500,
        'shipping_khulna_rate' => 70,
        'shipping_dhaka_rate' => 70,
        'shipping_other_rate' => 100,
        'bkash_number' => '',
        'nagad_number' => '',
        'store_phone' => '',
        'whatsapp_number' => '',
        'facebook_url' => '',
        'fb_pixel_id' => '',
        'fb_domain_verification' => '',
        'ga4_id' => '',
    ];

    public static function allValues(): array
    {
        return Cache::rememberForever('settings', function () {
            try {
                $saved = static::query()->pluck('value', 'key')->all();
            } catch (\Throwable) {
                $saved = [];
            }

            return array_merge(self::DEFAULTS, array_filter($saved, fn ($v) => $v !== null));
        });
    }

    public static function read(string $key, mixed $default = null): mixed
    {
        return static::allValues()[$key] ?? $default;
    }

    public static function write(array $values): void
    {
        foreach ($values as $key => $value) {
            if (array_key_exists($key, self::DEFAULTS)) {
                static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
            }
        }
        Cache::forget('settings');
    }
}
