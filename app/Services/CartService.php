<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Guests: cart id kept in the session. Logged-in users: cart tied to user_id.
 * The session cart id survives login (session data is kept on regenerate), so it can be merged.
 */
class CartService
{
    public const MAX_QTY = 20;

    public function current(bool $create = false): ?Cart
    {
        $user = auth()->user();

        if ($user) {
            return $create ? Cart::firstOrCreate(['user_id' => $user->id]) : Cart::where('user_id', $user->id)->first();
        }

        $cart = ($id = session('cart_id')) ? Cart::whereNull('user_id')->find($id) : null;

        if (! $cart && $create) {
            $cart = Cart::create();
            session(['cart_id' => $cart->id]);
        }

        return $cart;
    }

    /** @return Collection<int, CartItem> active, in-stock-aware items with products loaded */
    public function items(): Collection
    {
        $cart = $this->current();
        if (! $cart) {
            return collect();
        }

        return $cart->items()
            ->with(['product' => fn ($q) => $q->with('firstImage')])
            ->get()
            ->filter(fn (CartItem $i) => $i->product && $i->product->is_active)
            ->values();
    }

    public function count(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    public function subtotal(?Collection $items = null): int
    {
        return (int) ($items ?? $this->items())->sum(fn (CartItem $i) => $i->product->price * $i->quantity);
    }

    public function add(Product $product, int $qty = 1): void
    {
        $cart = $this->current(create: true);
        $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
        $item->quantity = $this->clamp(($item->quantity ?? 0) + $qty, $product);
        $item->save();
    }

    public function update(Product $product, int $qty): void
    {
        $cart = $this->current();
        if (! $cart) {
            return;
        }

        if ($qty <= 0) {
            $cart->items()->where('product_id', $product->id)->delete();

            return;
        }

        $cart->items()->where('product_id', $product->id)->update(['quantity' => $this->clamp($qty, $product)]);
    }

    public function clear(): void
    {
        $this->current()?->items()->delete();
    }

    /** Move a guest cart's items into the user's cart after login. */
    public function mergeGuestCartInto(User $user): void
    {
        $guestId = session('cart_id');
        $guest = $guestId ? Cart::whereNull('user_id')->with('items.product')->find($guestId) : null;
        session()->forget('cart_id');

        if (! $guest) {
            return;
        }

        DB::transaction(function () use ($guest, $user) {
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);
            foreach ($guest->items as $gi) {
                if (! $gi->product) {
                    continue;
                }
                $item = $cart->items()->firstOrNew(['product_id' => $gi->product_id]);
                $item->quantity = $this->clamp(($item->quantity ?? 0) + $gi->quantity, $gi->product);
                $item->save();
            }
            $guest->delete();
        });
    }

    private function clamp(int $qty, Product $product): int
    {
        return max(1, min($qty, self::MAX_QTY, max($product->stock, 1)));
    }
}
