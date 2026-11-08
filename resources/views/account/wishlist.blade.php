@extends('layouts.app')

@section('title', __('shop.wishlist.title'))

@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
    <div class="mx-auto max-w-6xl px-4 pt-8">
        <h1 class="text-3xl font-bold mb-6">{{ __('shop.wishlist.title') }}</h1>
        @if ($products->isEmpty())
            <div class="card p-10 text-center">
                <p class="text-slate/70">{{ __('shop.wishlist.empty') }}</p>
                <a href="{{ route('shop') }}" class="btn-primary mt-6">{{ __('shop.shop_now') }}</a>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-5">
                @foreach ($products as $product)
                    <div class="relative">
                        @include('partials.product-card')
                        <form method="post" action="{{ route('wishlist.toggle', $product) }}" class="absolute top-2 right-2">
                            @csrf
                            <button class="h-9 w-9 rounded-full bg-white/90 shadow-soft text-coral flex items-center justify-center" aria-label="{{ __('shop.product.wishlist_remove') }}">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21s-7-4.4-9.3-9A5.3 5.3 0 0 1 12 6a5.3 5.3 0 0 1 9.3 6c-2.3 4.6-9.3 9-9.3 9z"/></svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
