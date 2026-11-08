<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'lastmod' => null],
            ['loc' => route('shop'), 'lastmod' => null],
            ['loc' => route('contact'), 'lastmod' => null],
        ])
            ->merge(Category::active()->get()->map(fn ($c) => ['loc' => route('category', $c), 'lastmod' => $c->updated_at]))
            ->merge(Product::active()->get(['slug', 'updated_at'])->map(fn ($p) => ['loc' => route('product', $p), 'lastmod' => $p->updated_at]));

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $body = "User-agent: *\nDisallow: /manage\nDisallow: /cart\nDisallow: /checkout\nDisallow: /account\n\nSitemap: ".route('sitemap')."\n";

        return response($body)->header('Content-Type', 'text/plain');
    }
}
