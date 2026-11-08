<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(): View
    {
        $items = $this->cart->items();

        return view('cart', ['items' => $items, 'subtotal' => $this->cart->subtotal($items)]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active && $product->inStock(), 404);
        $data = $request->validate(['quantity' => ['nullable', 'integer', 'min:1', 'max:'.CartService::MAX_QTY]]);

        $this->cart->add($product, (int) ($data['quantity'] ?? 1));

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.show');
        }

        return back()->with('status', __('shop.product.added'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:'.CartService::MAX_QTY]]);
        $this->cart->update($product, (int) $data['quantity']);

        return back()->with('status', __('shop.cart.updated'));
    }

    public function remove(Product $product): RedirectResponse
    {
        $this->cart->update($product, 0);

        return back()->with('status', __('shop.cart.updated'));
    }
}
