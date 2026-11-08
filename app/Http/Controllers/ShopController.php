<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function home(): View
    {
        $base = Product::active()->with('firstImage');

        return view('home', [
            'featured' => (clone $base)->where('is_featured', true)->latest()->take(8)->get(),
            'newest' => (clone $base)->latest()->take(8)->get(),
            'categories' => Category::active()->orderBy('sort')->withCount(['products' => fn ($q) => $q->active()])->get(),
        ]);
    }

    public function index(Request $request, ?Category $category = null): View
    {
        abort_if($category && ! $category->is_active, 404);

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'sort' => ['nullable', 'in:new,price_asc,price_desc'],
        ]);

        $query = Product::active()->with('firstImage');

        if ($category) {
            $query->where('category_id', $category->id);
        }

        if ($q = $validated['q'] ?? null) {
            // Search the raw JSON text (both languages) and SKU. Bangla is stored unescaped (see AppServiceProvider).
            $query->where(fn ($w) => $w->searchName($q)->orWhere('sku', $q));
        }

        match ($validated['sort'] ?? 'new') {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default => $query->latest(),
        };

        return view('shop.index', [
            'products' => $query->paginate(12)->withQueryString(),
            'category' => $category,
            'categories' => Category::active()->orderBy('sort')->get(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['images', 'category']);

        $reviews = $product->reviews()->where('is_approved', true)->with('user:id,name')->latest()->get();

        return view('shop.product', [
            'product' => $product,
            'reviews' => $reviews,
            'avgRating' => $reviews->avg('rating'),
            'related' => Product::active()->with('firstImage')
                ->where('id', '!=', $product->id)
                ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
                ->inRandomOrder()->take(4)->get(),
            'inWishlist' => auth()->check() && auth()->user()->wishlist()->whereKey($product->id)->exists(),
            'canReview' => auth()->check() && auth()->user()->hasPurchased($product)
                && ! $product->reviews()->where('user_id', auth()->id())->exists(),
        ]);
    }

    public function contact(): View
    {
        return view('contact');
    }
}
