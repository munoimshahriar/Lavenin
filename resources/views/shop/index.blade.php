@extends('layouts.app')

@section('title', $category?->name ?? __('shop.shop.title'))
@section('description', $category?->description ?: __('shop.hero_text'))

@section('content')
    <div class="mx-auto max-w-6xl px-4 pt-8">
        <h1 class="text-3xl font-bold">{{ $category?->name ?? __('shop.shop.title') }}</h1>
        @if ($category?->description)
            <p class="mt-2 text-slate/70">{{ $category->description }}</p>
        @endif

        <form method="get" action="{{ $category ? route('category', $category) : route('shop') }}" class="mt-6 flex flex-col sm:flex-row gap-3">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('shop.shop.search') }}" class="input sm:max-w-xs">
            <select name="sort" class="input sm:max-w-56" onchange="this.form.submit()" aria-label="{{ __('shop.shop.sort') }}">
                <option value="new" @selected(request('sort', 'new') === 'new')>{{ __('shop.shop.sort_new') }}</option>
                <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('shop.shop.sort_price_asc') }}</option>
                <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('shop.shop.sort_price_desc') }}</option>
            </select>
        </form>

        <div class="mt-4 flex gap-2 overflow-x-auto pb-2 -mx-4 px-4">
            <a href="{{ route('shop') }}" @class(['shrink-0 rounded-full px-4 py-1.5 text-sm font-semibold', 'bg-teal text-white' => ! $category, 'bg-white shadow-soft' => $category])>{{ __('shop.shop.all_categories') }}</a>
            @foreach ($categories as $cat)
                <a href="{{ route('category', $cat) }}" @class(['shrink-0 rounded-full px-4 py-1.5 text-sm font-semibold', 'bg-teal text-white' => $category?->is($cat), 'bg-white shadow-soft' => ! $category?->is($cat)])>{{ $cat->name }}</a>
            @endforeach
        </div>

        <p class="mt-4 text-sm text-slate/60">{{ __('shop.shop.results', ['count' => $products->total()]) }}</p>

        @if ($products->isEmpty())
            <div class="card p-10 mt-4 text-center text-slate/70">{{ __('shop.shop.empty') }}</div>
        @else
            <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-5">
                @foreach ($products as $product)
                    @include('partials.product-card')
                @endforeach
            </div>
            <div class="mt-8">{{ $products->links() }}</div>
        @endif
    </div>
@endsection
