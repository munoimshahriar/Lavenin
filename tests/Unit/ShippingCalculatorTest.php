<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\ShippingCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ShippingCalculatorTest extends TestCase
{
    public static function cases(): array
    {
        return [
            'free everywhere at 2000' => [2000, 'Sylhet', 0],
            'free everywhere above 2000' => [5000, 'Dhaka', 0],
            'khulna free at 500' => [500, 'Khulna', 0],
            'khulna free at 1999' => [1999, 'Khulna', 0],
            'khulna under 500' => [499, 'Khulna', 70],
            'dhaka under 2000' => [1999, 'Dhaka', 70],
            'dhaka 500 is not free' => [500, 'Dhaka', 70],
            'other district' => [800, 'Rangpur', 100],
            'case insensitive' => [100, ' khulna ', 70],
            'unknown district' => [100, null, 100],
        ];
    }

    #[DataProvider('cases')]
    public function test_shipping_rules(int $subtotal, ?string $district, int $expected): void
    {
        $calc = new ShippingCalculator(Setting::DEFAULTS);

        $this->assertSame($expected, $calc->calculate($subtotal, $district));
    }

    public function test_rates_are_configurable(): void
    {
        $calc = new ShippingCalculator(array_merge(Setting::DEFAULTS, ['shipping_other_rate' => 130, 'shipping_free_threshold' => 3000]));

        $this->assertSame(130, $calc->calculate(2500, 'Bogura'));
        $this->assertSame(0, $calc->calculate(3000, 'Bogura'));
    }
}
