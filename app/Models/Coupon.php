<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = ['code', 'type', 'value', 'min_order', 'expires_at', 'usage_limit', 'used_count', 'is_active'];

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'min_order' => 'integer',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(fn (Coupon $c) => $c->code = strtoupper(trim($c->code)));
    }

    public static function findByCode(?string $code): ?self
    {
        return blank($code) ? null : static::where('code', strtoupper(trim($code)))->first();
    }

    /** Returns null when usable for this subtotal, otherwise a translation key explaining why not. */
    public function problemFor(int $subtotal): ?string
    {
        return match (true) {
            ! $this->is_active => 'shop.coupon.invalid',
            $this->expires_at && $this->expires_at->isPast() => 'shop.coupon.expired',
            $this->usage_limit !== null && $this->used_count >= $this->usage_limit => 'shop.coupon.used_up',
            $subtotal < $this->min_order => 'shop.coupon.min_order',
            default => null,
        };
    }

    public function discountFor(int $subtotal): int
    {
        $discount = $this->type === 'percent'
            ? intdiv($subtotal * min($this->value, 100), 100)
            : $this->value;

        return min($discount, $subtotal);
    }
}
