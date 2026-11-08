<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private CartService $cart,
        private ShippingCalculator $shipping,
    ) {}

    /**
     * Price everything on the server from the database; nothing price-related is taken from the client.
     *
     * @return array{items: \Illuminate\Support\Collection, subtotal: int, shipping: int, discount: int, total: int, coupon: ?Coupon, coupon_error: ?string}
     */
    public function quote(?string $district, ?string $couponCode): array
    {
        $items = $this->cart->items();
        $subtotal = $this->cart->subtotal($items);
        $shipping = $items->isEmpty() ? 0 : $this->shipping->calculate($subtotal, $district);

        $coupon = Coupon::findByCode($couponCode);
        $couponError = null;
        $discount = 0;

        if (filled($couponCode)) {
            if (! $coupon) {
                $couponError = 'shop.coupon.invalid';
            } elseif ($problem = $coupon->problemFor($subtotal)) {
                $couponError = $problem;
                $coupon = null;
            } else {
                $discount = $coupon->discountFor($subtotal);
            }
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'discount' => $discount,
            'total' => max(0, $subtotal - $discount) + $shipping,
            'coupon' => $coupon,
            'coupon_error' => $couponError,
        ];
    }

    public function place(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $quote = $this->quote($data['district'], $data['coupon'] ?? null);

            if ($quote['items']->isEmpty()) {
                throw ValidationException::withMessages(['cart' => __('shop.cart.empty')]);
            }
            if ($quote['coupon_error']) {
                throw ValidationException::withMessages(['coupon' => __($quote['coupon_error'])]);
            }
            foreach ($quote['items'] as $item) {
                if ($item->quantity > $item->product->stock) {
                    throw ValidationException::withMessages([
                        'cart' => __('shop.cart.not_enough_stock', ['name' => $item->product->name]),
                    ]);
                }
            }

            $method = PaymentMethod::from($data['payment_method']);

            $order = Order::create([
                'number' => Order::generateNumber(),
                'user_id' => auth()->id(),
                'coupon_id' => $quote['coupon']?->id,
                'status' => OrderStatus::Pending,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'district' => $data['district'],
                'city' => $data['city'],
                'address' => $data['address'],
                'note' => $data['note'] ?? null,
                'subtotal' => $quote['subtotal'],
                'shipping' => $quote['shipping'],
                'discount' => $quote['discount'],
                'total' => $quote['total'],
                'payment_method' => $method,
                'payment_status' => $method->initialPaymentStatus(),
                'sender_number' => $method->requiresTransaction() ? $data['sender_number'] : null,
                'trx_id' => $method->requiresTransaction() ? strtoupper($data['trx_id']) : null,
            ]);

            foreach ($quote['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'name' => $item->product->getTranslation('name', app()->getLocale()),
                    'price' => $item->product->price,
                    'quantity' => $item->quantity,
                ]);
            }

            if ($quote['coupon']) {
                Coupon::whereKey($quote['coupon']->id)->increment('used_count');
            }

            $this->cart->clear();

            return $order;
        });
    }
}
