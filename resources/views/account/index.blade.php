@extends('layouts.app')

@section('title', __('shop.account.title'))

@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
    <div class="mx-auto max-w-4xl px-4 pt-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-3xl font-bold">{{ __('shop.account.title') }}</h1>
                <p class="text-slate/70">{{ auth()->user()->name }} · {{ auth()->user()->phone ?? auth()->user()->email }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('wishlist') }}" class="btn-ghost py-2">{{ __('shop.nav.wishlist') }}</a>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-ghost py-2">{{ __('shop.nav.logout') }}</button>
                </form>
            </div>
        </div>

        <h2 class="text-xl font-bold mt-8 mb-3">{{ __('shop.account.orders') }}</h2>
        @if ($orders->isEmpty())
            <div class="card p-8 text-center text-slate/70">{{ __('shop.account.no_orders') }}</div>
        @else
            <div class="card divide-y divide-mint">
                @foreach ($orders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="p-4 flex flex-wrap items-center gap-x-6 gap-y-1 hover:bg-mint/50">
                        <span class="font-bold text-teal-deep">{{ $order->number }}</span>
                        <span class="text-sm text-slate/60">{{ $order->created_at->format('d M Y') }}</span>
                        <span class="text-sm">{{ $order->status->getLabel() }}</span>
                        <span class="text-sm text-slate/60">{{ $order->payment_status->getLabel() }}</span>
                        <span class="ml-auto font-semibold">৳{{ number_format($order->total) }}</span>
                    </a>
                @endforeach
            </div>
            <div class="mt-4">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
