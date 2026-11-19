<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Meta (Facebook) Pixel standard events for ads/retargeting.
 *
 * Events that follow a redirect (AddToCart, Purchase, CompleteRegistration) are flashed to the
 * session so they fire exactly once on the next page; a reload doesn't fire them again.
 * Each event carries an eventID so a future Conversions API can de-duplicate against it.
 * Content IDs are product SKUs (or "p{id}"), matching the catalog feed at /feeds/meta.xml.
 */
class MetaPixel
{
    private const SESSION_KEY = 'meta_pixel_events';

    public static function enabled(): bool
    {
        return (bool) Setting::read('fb_pixel_id');
    }

    public static function contentId(Product $product): string
    {
        return $product->sku ?: 'p'.$product->id;
    }

    /** @return array{0: string, 1: array, 2: string} */
    public static function event(string $name, array $params = []): array
    {
        return [$name, $params, (string) Str::uuid()];
    }

    public static function flash(string $name, array $params = []): void
    {
        if (! self::enabled()) {
            return;
        }
        $events = session(self::SESSION_KEY, []);
        $events[] = self::event($name, $params);
        session()->flash(self::SESSION_KEY, $events);
    }

    /** Events flashed by the previous request. */
    public static function pending(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public static function productParams(Product $product, int $quantity = 1): array
    {
        return [
            'content_ids' => [self::contentId($product)],
            'content_type' => 'product',
            'content_name' => $product->getTranslation('name', 'en'),
            'contents' => [['id' => self::contentId($product), 'quantity' => $quantity]],
            'value' => $product->price * $quantity,
            'currency' => 'BDT',
        ];
    }

    /** @param Collection<int, \App\Models\CartItem> $items */
    public static function cartParams(Collection $items, int $value): array
    {
        return [
            'content_ids' => $items->map(fn ($i) => self::contentId($i->product))->values()->all(),
            'content_type' => 'product',
            'contents' => $items->map(fn ($i) => ['id' => self::contentId($i->product), 'quantity' => $i->quantity])->values()->all(),
            'num_items' => (int) $items->sum('quantity'),
            'value' => $value,
            'currency' => 'BDT',
        ];
    }

    public static function orderParams(Order $order): array
    {
        $order->loadMissing('items.product');

        return [
            'content_ids' => $order->items->map(fn ($i) => $i->product ? self::contentId($i->product) : 'p'.$i->product_id)->values()->all(),
            'content_type' => 'product',
            'contents' => $order->items->map(fn ($i) => [
                'id' => $i->product ? self::contentId($i->product) : 'p'.$i->product_id,
                'quantity' => $i->quantity,
                'item_price' => $i->price,
            ])->values()->all(),
            'num_items' => (int) $order->items->sum('quantity'),
            'value' => $order->total,
            'currency' => 'BDT',
            'order_id' => $order->number,
        ];
    }
}
