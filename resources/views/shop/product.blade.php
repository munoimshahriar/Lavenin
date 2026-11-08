@extends('layouts.app')

@php
    $images = $product->images;
    $mainImage = $images->first()?->url();
    $plainDescription = trim(strip_tags((string) $product->description));
@endphp

@section('title', $product->name)
@section('description', $plainDescription ?: __('shop.hero_text'))
@section('og_type', 'product')
@if ($mainImage)
    @section('og_image', $mainImage)
@endif

@push('head')
    <script type="application/ld+json">
        {!! json_encode(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => Str::limit($plainDescription, 500),
            'sku' => $product->sku,
            'image' => $images->map->url()->values()->all(),
            'brand' => ['@type' => 'Brand', 'name' => 'Lavenin'],
            'offers' => [
                '@type' => 'Offer',
                'priceCurrency' => 'BDT',
                'price' => $product->price,
                'availability' => $product->inStock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'url' => route('product', $product),
            ],
            'aggregateRating' => $reviews->isNotEmpty() ? [
                '@type' => 'AggregateRating',
                'ratingValue' => round($avgRating, 1),
                'reviewCount' => $reviews->count(),
            ] : null,
        ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('content')
    <div class="mx-auto max-w-6xl px-4 pt-6">
        <nav class="text-sm text-slate/60 mb-4">
            <a href="{{ route('shop') }}" class="hover:underline">{{ __('shop.nav.shop') }}</a>
            @if ($product->category)
                / <a href="{{ route('category', $product->category) }}" class="hover:underline">{{ $product->category->name }}</a>
            @endif
        </nav>

        <div class="grid md:grid-cols-2 gap-8">
            {{-- Gallery --}}
            <div x-data="{ active: 0 }">
                <div class="card overflow-hidden aspect-square bg-mint">
                    @forelse ($images as $i => $img)
                        <img x-show="active === {{ $i }}" @if ($i) x-cloak @endif src="{{ $img->url() }}" alt="{{ $product->name }}"
                             width="1200" height="1200" class="h-full w-full object-cover" @if ($i) loading="lazy" @endif>
                    @empty
                        <div class="h-full w-full flex items-center justify-center font-display text-7xl text-teal/60">L</div>
                    @endforelse
                </div>
                @if ($images->count() > 1)
                    <div class="mt-3 flex gap-2 overflow-x-auto">
                        @foreach ($images as $i => $img)
                            <button type="button" @click="active = {{ $i }}" class="shrink-0 h-16 w-16 rounded-xl overflow-hidden ring-2 transition"
                                    :class="active === {{ $i }} ? 'ring-teal' : 'ring-transparent opacity-70'">
                                <img src="{{ $img->thumbUrl() }}" alt="" class="h-full w-full object-cover" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div>
                <h1 class="text-2xl md:text-4xl font-bold leading-tight">{{ $product->name }}</h1>

                @if ($reviews->isNotEmpty())
                    <p class="mt-2 text-sm"><span class="text-amber-500">{{ str_repeat('★', (int) round($avgRating)) }}</span> {{ number_format($avgRating, 1) }} ({{ $reviews->count() }})</p>
                @endif

                <div class="mt-4 flex items-baseline gap-3">
                    <span class="text-3xl font-bold text-teal-deep">৳{{ number_format($product->price) }}</span>
                    @if ($product->isOnSale())
                        <span class="text-lg text-slate/50 line-through">৳{{ number_format($product->compare_at_price) }}</span>
                        <span class="rounded-full bg-coral px-2.5 py-0.5 text-xs font-bold text-white">−{{ round(100 - $product->price * 100 / $product->compare_at_price) }}%</span>
                    @endif
                </div>

                <dl class="mt-4 flex flex-wrap gap-2 text-sm">
                    @if ($product->age_range)
                        <div class="rounded-full bg-mint px-3 py-1"><dt class="inline font-semibold">{{ __('shop.product.age') }}:</dt> <dd class="inline">{{ $product->age_range }}</dd></div>
                    @endif
                    <div @class(['rounded-full px-3 py-1 font-semibold', 'bg-mint text-teal-deep' => $product->inStock(), 'bg-coral/10 text-coral' => ! $product->inStock()])>
                        @if (! $product->inStock())
                            {{ __('shop.product.out_of_stock') }}
                        @elseif ($product->isLowStock())
                            {{ __('shop.product.only_left', ['count' => $product->stock]) }}
                        @else
                            {{ __('shop.product.in_stock') }}
                        @endif
                    </div>
                </dl>

                @if ($product->inStock())
                    <form method="post" action="{{ route('cart.add', $product) }}" class="mt-6 flex flex-wrap items-end gap-3" x-data="{ qty: 1 }">
                        @csrf
                        <div>
                            <label class="label" for="qty">{{ __('shop.product.quantity') }}</label>
                            <div class="flex items-center rounded-full border border-teal/30 bg-white">
                                <button type="button" class="px-4 py-3 text-lg" @click="qty = Math.max(1, qty - 1)" aria-label="−">−</button>
                                <input id="qty" name="quantity" type="number" min="1" max="{{ min(20, $product->stock) }}" x-model.number="qty" class="w-12 text-center border-0 bg-transparent focus:ring-0">
                                <button type="button" class="px-4 py-3 text-lg" @click="qty = Math.min({{ min(20, $product->stock) }}, qty + 1)" aria-label="+">+</button>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary flex-1 min-w-40">{{ __('shop.product.add_to_cart') }}</button>
                        <button type="submit" name="buy_now" value="1" class="btn-cta flex-1 min-w-40">{{ __('shop.product.buy_now') }}</button>
                    </form>
                @endif

                @auth
                    <form method="post" action="{{ route('wishlist.toggle', $product) }}" class="mt-3">
                        @csrf
                        <button class="inline-flex items-center gap-2 text-sm font-semibold {{ $inWishlist ? 'text-coral' : 'text-slate/70' }} hover:text-coral">
                            <svg class="h-5 w-5" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-4.4-9.3-9A5.3 5.3 0 0 1 12 6a5.3 5.3 0 0 1 9.3 6c-2.3 4.6-9.3 9-9.3 9z"/></svg>
                            {{ $inWishlist ? __('shop.product.wishlist_remove') : __('shop.product.wishlist_add') }}
                        </button>
                    </form>
                @endauth

                @if ($product->description)
                    <div class="mt-8">
                        <h2 class="text-xl font-bold mb-2">{{ __('shop.product.description') }}</h2>
                        <div class="prose-sm max-w-none text-slate/85 space-y-2 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_a]:text-teal-deep [&_a]:underline">
                            {!! \App\Support\Html::clean($product->description) !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if ($embed = $product->videoEmbedUrl())
            <section class="mt-12">
                <h2 class="text-2xl font-bold mb-4">{{ __('shop.product.video') }}</h2>
                <div class="card overflow-hidden aspect-video max-w-3xl">
                    <iframe src="{{ $embed }}" title="{{ $product->name }}" class="h-full w-full" loading="lazy"
                            allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                </div>
            </section>
        @endif

        {{-- Reviews --}}
        <section class="mt-12 max-w-3xl">
            <h2 class="text-2xl font-bold mb-4">{{ __('shop.reviews.title') }}</h2>
            @forelse ($reviews as $review)
                <div class="card p-4 mb-3">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold">{{ $review->user->name }}</span>
                        <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}<span class="text-slate/20">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                    </div>
                    @if ($review->body)
                        <p class="mt-2 text-slate/80">{{ $review->body }}</p>
                    @endif
                </div>
            @empty
                <p class="text-slate/60">{{ __('shop.reviews.none') }}</p>
            @endforelse

            @error('review') <p class="error">{{ $message }}</p> @enderror

            @if ($canReview)
                <form method="post" action="{{ route('reviews.store', $product) }}" class="card p-5 mt-4 space-y-4" x-data="{ rating: 5 }">
                    @csrf
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <h3 class="font-bold text-lg">{{ __('shop.reviews.write') }}</h3>
                    <div>
                        <span class="label">{{ __('shop.reviews.rating') }}</span>
                        <div class="flex gap-1 text-3xl">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" @click="rating = {{ $i }}" :class="rating >= {{ $i }} ? 'text-amber-500' : 'text-slate/20'" aria-label="{{ $i }}">★</button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" :value="rating">
                    </div>
                    <div>
                        <label class="label" for="body">{{ __('shop.reviews.body') }}</label>
                        <textarea id="body" name="body" rows="3" maxlength="1000" class="input">{{ old('body') }}</textarea>
                    </div>
                    <button class="btn-primary">{{ __('shop.reviews.submit') }}</button>
                </form>
            @elseif (! auth()->check())
                <p class="mt-4 text-sm"><a href="{{ route('login') }}" class="text-teal-deep font-semibold underline">{{ __('shop.reviews.login') }}</a></p>
            @endif
        </section>

        @if ($related->isNotEmpty())
            <section class="mt-14">
                <h2 class="text-2xl font-bold mb-5">{{ __('shop.product.related') }}</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 md:gap-5">
                    @foreach ($related as $product)
                        @include('partials.product-card')
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
