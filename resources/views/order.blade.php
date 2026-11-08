@extends('layouts.app')

@section('title', __('shop.order.number').' '.$order->number)

@push('head')<meta name="robots" content="noindex">@endpush

@php
    $wa = \App\Models\Setting::read('whatsapp_number');
    $waText = __('shop.order.whatsapp_text', ['number' => $order->number, 'total' => number_format($order->total)]);
@endphp

@section('content')
    <div class="mx-auto max-w-2xl px-4 pt-10">
        <div class="card p-6 md:p-8 text-center">
            <div class="mx-auto h-16 w-16 rounded-full bg-mint flex items-center justify-center text-3xl text-teal-deep">✓</div>
            <h1 class="mt-4 text-2xl md:text-3xl font-bold">{{ __('shop.order.thanks') }}</h1>
            <p class="mt-2 text-slate/70">{{ __('shop.order.number') }}</p>
            <p class="font-display text-3xl font-bold text-teal-deep tracking-wide select-all">{{ $order->number }}</p>
            <p class="mt-4 text-sm text-slate/80">
                @if ($order->payment_method->requiresTransaction())
                    {{ __('shop.order.verify_note', ['trx' => $order->trx_id]) }}
                @else
                    {{ __('shop.order.we_will_call', ['phone' => $order->phone]) }}
                @endif
            </p>
            @if ($wa)
                <a href="https://wa.me/{{ $wa }}?text={{ rawurlencode($waText) }}" target="_blank" rel="noopener"
                   class="btn mt-6 bg-[#25D366] text-white hover:brightness-95">{{ __('shop.order.whatsapp') }}</a>
            @endif
        </div>

        <div class="card p-6 mt-6">
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-slate/60">{{ __('shop.order.status') }}:</span> <strong>{{ $order->status->getLabel() }}</strong></div>
                <div><span class="text-slate/60">{{ __('shop.order.payment') }}:</span> <strong>{{ $order->payment_method->getLabel() }}</strong> · {{ $order->payment_status->getLabel() }}</div>
                <div class="col-span-2"><span class="text-slate/60">{{ __('shop.checkout.address') }}:</span> {{ $order->address }}, {{ $order->city }}, {{ $order->district }}</div>
            </div>
            <ul class="mt-4 divide-y divide-mint text-sm">
                @foreach ($order->items as $item)
                    <li class="py-2 flex justify-between"><span>{{ $item->name }} × {{ $item->quantity }}</span><span>৳{{ number_format($item->lineTotal()) }}</span></li>
                @endforeach
            </ul>
            <dl class="mt-3 space-y-1 text-sm border-t border-mint pt-3">
                <div class="flex justify-between"><dt>{{ __('shop.cart.subtotal') }}</dt><dd>৳{{ number_format($order->subtotal) }}</dd></div>
                @if ($order->discount)
                    <div class="flex justify-between"><dt>{{ __('shop.checkout.discount') }}</dt><dd class="text-coral">− ৳{{ number_format($order->discount) }}</dd></div>
                @endif
                <div class="flex justify-between"><dt>{{ __('shop.checkout.shipping') }}</dt><dd>{{ $order->shipping ? '৳'.number_format($order->shipping) : __('shop.checkout.free') }}</dd></div>
                <div class="flex justify-between font-bold text-base"><dt>{{ __('shop.checkout.total') }}</dt><dd class="text-teal-deep">৳{{ number_format($order->total) }}</dd></div>
            </dl>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('shop') }}" class="btn-ghost">{{ __('shop.cart.continue') }}</a>
        </div>
    </div>
@endsection
