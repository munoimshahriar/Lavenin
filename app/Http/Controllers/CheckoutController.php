<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkout) {}

    public function show(Request $request): View|RedirectResponse
    {
        $quote = $this->checkout->quote(old('district'), old('coupon'));

        if ($quote['items']->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $user = $request->user();

        return view('checkout', [
            'quote' => $quote,
            'methods' => PaymentMethod::available(),
            'districts' => config('bd.districts'),
            'prefill' => [
                'name' => $user?->name,
                'phone' => $user?->phone,
                'email' => $user?->email,
            ] + ($user?->orders()->first()?->only(['district', 'city', 'address']) ?? []),
        ]);
    }

    /** Live totals for the checkout page (district + coupon). Always computed server-side. */
    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate([
            'district' => ['nullable', 'string', 'max:40'],
            'coupon' => ['nullable', 'string', 'max:40'],
        ]);

        $q = $this->checkout->quote($data['district'] ?? null, $data['coupon'] ?? null);

        return response()->json([
            'subtotal' => $q['subtotal'],
            'shipping' => $q['shipping'],
            'discount' => $q['discount'],
            'total' => $q['total'],
            'coupon_error' => $q['coupon_error'] ? __($q['coupon_error']) : null,
            'coupon_applied' => (bool) $q['coupon'],
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $order = $this->checkout->place($request->validated());

        // Let this browser view its confirmation page without logging in.
        $request->session()->push('placed_orders', $order->id);

        return redirect()->route('orders.show', $order);
    }

    public function confirmation(Request $request, Order $order): View
    {
        $allowed = in_array($order->id, $request->session()->get('placed_orders', []), true)
            || ($order->user_id && $order->user_id === $request->user()?->id);

        abort_unless($allowed, 404);

        return view('order', ['order' => $order->load('items')]);
    }
}
