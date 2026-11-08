<a href="{{ route('product', $product) }}" class="card group overflow-hidden flex flex-col hover:-translate-y-0.5 transition">
    <div class="relative aspect-square bg-mint">
        @if ($thumb = $product->thumbUrl())
            <img src="{{ $thumb }}" alt="{{ $product->name }}" loading="lazy" width="400" height="400" class="h-full w-full object-cover">
        @else
            <div class="h-full w-full flex items-center justify-center font-display text-4xl text-teal/60">L</div>
        @endif
        @if ($product->isOnSale())
            <span class="absolute top-2 left-2 rounded-full bg-coral px-2.5 py-0.5 text-xs font-bold text-white">{{ __('shop.product.sale') }}</span>
        @endif
        @unless ($product->inStock())
            <span class="absolute inset-x-0 bottom-0 bg-slate/80 py-1 text-center text-xs font-semibold text-white">{{ __('shop.product.out_of_stock') }}</span>
        @endunless
    </div>
    <div class="p-3 flex-1 flex flex-col">
        <h3 class="font-sans text-sm font-semibold leading-snug line-clamp-2">{{ $product->name }}</h3>
        @if ($product->age_range)
            <p class="text-xs text-slate/60 mt-0.5">{{ $product->age_range }}</p>
        @endif
        <div class="mt-auto pt-2 flex items-baseline gap-2">
            <span class="font-bold text-teal-deep">৳{{ number_format($product->price) }}</span>
            @if ($product->isOnSale())
                <span class="text-xs text-slate/50 line-through">৳{{ number_format($product->compare_at_price) }}</span>
            @endif
        </div>
    </div>
</a>
