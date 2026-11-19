<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\MetaPixel;
use Illuminate\Http\Response;

/**
 * Product catalog feed for Meta Commerce Manager (Facebook/Instagram catalog ads).
 * Add https://lavenin.com/feeds/meta.xml as a scheduled data feed (daily). IDs match the Pixel's content_ids.
 */
class FeedController extends Controller
{
    public function meta(): Response
    {
        $products = Product::active()->with(['images', 'category'])->orderBy('id')->get()
            ->filter(fn (Product $p) => $p->images->isNotEmpty()) // Meta rejects items without an image
            ->map(function (Product $p) {
                $onSale = $p->isOnSale();
                $description = trim(preg_replace('/\s+/', ' ', strip_tags((string) $p->getTranslation('description', 'en'))));

                return [
                    'id' => MetaPixel::contentId($p),
                    'title' => mb_substr($p->getTranslation('name', 'en'), 0, 150),
                    'description' => mb_substr($description ?: $p->getTranslation('name', 'en'), 0, 5000),
                    'availability' => $p->inStock() ? 'in stock' : 'out of stock',
                    'price' => number_format($onSale ? $p->compare_at_price : $p->price, 2, '.', '').' BDT',
                    'sale_price' => $onSale ? number_format($p->price, 2, '.', '').' BDT' : null,
                    'link' => route('product', $p),
                    'image_link' => $p->images->first()->url(),
                    'additional_image_link' => $p->images->slice(1, 10)->map->url()->implode(','),
                    'product_type' => $p->category?->getTranslation('name', 'en'),
                    'quantity_to_sell_on_facebook' => max(0, $p->stock),
                ];
            });

        return response()
            ->view('seo.meta-feed', ['products' => $products])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
