@extends('layouts.app')

@section('title', 'Lavenin')

@section('content')
    <section class="mx-auto max-w-6xl px-4 pt-10 pb-12 md:pt-16">
        <div class="card bg-mint p-8 md:p-14 grid md:grid-cols-2 gap-8 items-center">
            <div>
                <h1 class="text-3xl md:text-5xl font-bold leading-tight">{{ __('shop.hero_title') }}</h1>
                <p class="mt-4 text-lg text-slate/80">{{ __('shop.hero_text') }}</p>
                <a href="#" class="btn-cta mt-8">{{ __('shop.shop_now') }}</a>
            </div>
            <div class="hidden md:flex justify-center" aria-hidden="true">
                <div class="h-56 w-56 rounded-full bg-white shadow-soft flex items-center justify-center">
                    <span class="font-display text-6xl font-bold text-teal">L</span>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 grid gap-4 sm:grid-cols-3">
        @foreach (['free_shipping', 'cod', 'safe'] as $perk)
            <div class="card p-5 text-center font-semibold">{{ __('shop.perks.'.$perk) }}</div>
        @endforeach
    </section>
@endsection
