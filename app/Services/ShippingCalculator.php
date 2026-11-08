<?php

namespace App\Services;

use App\Models\Setting;

/**
 * Shipping rules (৳), rates editable in admin Settings:
 *   subtotal >= 2000                   → free (everywhere)
 *   Khulna && subtotal >= 500          → free
 *   Khulna                             → 70
 *   Dhaka                              → 70
 *   anywhere else                      → 100
 * "City" is matched on the district the customer picks at checkout.
 */
class ShippingCalculator
{
    public function __construct(private ?array $rates = null) {}

    public function calculate(int $subtotal, ?string $district): int
    {
        $r = $this->rates ?? Setting::allValues();
        $district = strtolower(trim((string) $district));

        if ($subtotal >= (int) $r['shipping_free_threshold']) {
            return 0;
        }

        return match ($district) {
            'khulna' => $subtotal >= (int) $r['shipping_khulna_free_threshold'] ? 0 : (int) $r['shipping_khulna_rate'],
            'dhaka' => (int) $r['shipping_dhaka_rate'],
            default => (int) $r['shipping_other_rate'],
        };
    }
}
