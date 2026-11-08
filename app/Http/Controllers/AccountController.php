<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.index', [
            'orders' => $request->user()->orders()->withCount('items')->paginate(10),
        ]);
    }

    public function wishlist(Request $request): View
    {
        return view('account.wishlist', [
            'products' => $request->user()->wishlist()->active()->with('firstImage')->latest('wishlists.created_at')->get(),
        ]);
    }

    public function toggleWishlist(Request $request, Product $product): RedirectResponse
    {
        $request->user()->wishlist()->toggle($product->id);

        return back();
    }

    public function review(Request $request, Product $product): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'website' => ['prohibited'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! $user->hasPurchased($product)) {
            return back()->withErrors(['review' => __('shop.reviews.only_buyers')]);
        }

        if (Review::where('user_id', $user->id)->where('product_id', $product->id)->exists()) {
            return back()->withErrors(['review' => __('shop.reviews.already')]);
        }

        Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => $data['rating'],
            'body' => $data['body'] ?? null,
            'is_approved' => false,
        ]);

        return back()->with('status', __('shop.reviews.thanks'));
    }
}
