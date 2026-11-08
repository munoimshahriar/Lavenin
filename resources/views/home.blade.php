@extends('layouts.app')

@section('title', __('shop.tagline'))

@section('content')
    <section class="mx-auto max-w-6xl px-4 pt-6 pb-10 md:pt-12">
        <div class="card bg-mint p-7 md:p-14 grid md:grid-cols-2 gap-8 items-center">
            <div>
                <h1 class="text-3xl md:text-5xl font-bold leading-tight">{{ __('shop.hero_title') }}</h1>
                <p class="mt-4 text-lg text-slate/80">{{ __('shop.hero_text') }}</p>
                <a href="{{ route('shop') }}" class="btn-cta mt-8">{{ __('shop.shop_now') }}</a>
            </div>
            <div class="hidden md:flex justify-center" aria-hidden="true">
                <div class="relative h-64 w-64">
                    <div class="absolute inset-0 rounded-full bg-white shadow-soft"></div>
                    <div class="absolute -top-2 right-4 h-16 w-16 rounded-full bg-coral/90"></div>
                    <div class="absolute bottom-4 -left-4 h-20 w-20 rounded-3xl bg-teal rotate-12"></div>
                    <div class="absolute inset-0 flex items-center justify-center font-display text-7xl font-bold text-teal-deep">L</div>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 grid gap-3 sm:grid-cols-3">
        @foreach (['free_shipping' => '🚚', 'cod' => '💵', 'safe' => '🧸'] as $perk => $icon)
            <div class="card p-4 flex items-center justify-center gap-2 font-semibold text-sm"><span aria-hidden="true">{{ $icon }}</span>{{ __('shop.perks.'.$perk) }}</div>
        @endforeach
    </section>

    @if ($categories->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 mt-14">
            <h2 class="text-2xl md:text-3xl font-bold mb-5">{{ __('shop.home.categories') }}</h2>
            <div class="flex gap-3 overflow-x-auto pb-2 -mx-4 px-4 snap-x">
                @foreach ($categories as $cat)
                    <a href="{{ route('category', $cat) }}" class="snap-start shrink-0 card px-5 py-4 hover:bg-mint transition min-w-36">
                        <div class="font-semibold">{{ $cat->name }}</div>
                        <div class="text-xs text-slate/60">{{ __('shop.shop.results', ['count' => $cat->products_count]) }}</div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @foreach (['featured' => $featured, 'new' => $newest] as $key => $list)
        @if ($list->isNotEmpty())
            <section class="mx-auto max-w-6xl px-4 mt-14">
                <div class="flex items-end justify-between mb-5">
                    <h2 class="text-2xl md:text-3xl font-bold">{{ __('shop.home.'.$key) }}</h2>
                    <a href="{{ route('shop') }}" class="font-semibold text-teal-deep hover:underline">{{ __('shop.home.view_all') }} →</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-5">
                    @foreach ($list as $product)
                        @include('partials.product-card')
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach
@endsection
