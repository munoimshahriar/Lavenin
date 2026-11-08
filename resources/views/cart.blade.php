@extends('layouts.app')

@section('title', __('shop.cart.title'))

@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
    <div class="mx-auto max-w-4xl px-4 pt-8">
        <h1 class="text-3xl font-bold mb-6">{{ __('shop.cart.title') }}</h1>

        @if ($items->isEmpty())
            <div class="card p-10 text-center">
                <p class="text-slate/70">{{ __('shop.cart.empty') }}</p>
                <a href="{{ route('shop') }}" class="btn-primary mt-6">{{ __('shop.cart.continue') }}</a>
            </div>
        @else
            <div class="card divide-y divide-mint">
                @foreach ($items as $item)
                    @php($p = $item->product)
                    <div class="p-4 flex gap-4 items-center">
                        <a href="{{ route('product', $p) }}" class="h-20 w-20 shrink-0 rounded-xl overflow-hidden bg-mint">
                            @if ($thumb = $p->thumbUrl())
                                <img src="{{ $thumb }}" alt="" class="h-full w-full object-cover">
                            @endif
                        </a>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('product', $p) }}" class="font-semibold line-clamp-2">{{ $p->name }}</a>
                            <div class="text-sm text-slate/60">৳{{ number_format($p->price) }}</div>
                            @if ($item->quantity > $p->stock)
                                <div class="text-sm text-coral">{{ __('shop.cart.not_enough_stock', ['name' => $p->name]) }}</div>
                            @endif
                            <div class="mt-2 flex items-center gap-3">
                                <form method="post" action="{{ route('cart.update', $p) }}" class="flex items-center gap-2">
                                    @csrf @method('patch')
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="0" max="{{ min(20, max($p->stock, 1)) }}"
                                           class="input w-20 py-1.5 text-center" onchange="this.form.submit()" aria-label="{{ __('shop.product.quantity') }}">
                                </form>
                                <form method="post" action="{{ route('cart.remove', $p) }}">
                                    @csrf @method('delete')
                                    <button class="text-sm text-slate/60 hover:text-coral underline">{{ __('shop.cart.remove') }}</button>
                                </form>
                            </div>
                        </div>
                        <div class="font-bold whitespace-nowrap">৳{{ number_format($p->price * $item->quantity) }}</div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 card p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="text-lg"><span class="font-semibold">{{ __('shop.cart.subtotal') }}:</span> <span class="font-bold text-teal-deep">৳{{ number_format($subtotal) }}</span></div>
                    <div class="text-sm text-slate/60">{{ __('shop.cart.shipping_note') }}</div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('shop') }}" class="btn-ghost">{{ __('shop.cart.continue') }}</a>
                    <a href="{{ route('checkout.show') }}" class="btn-cta">{{ __('shop.cart.checkout') }}</a>
                </div>
            </div>
        @endif
    </div>
@endsection
