@extends('layouts.app')

@section('title', __('shop.contact.title'))

@php($s = \App\Models\Setting::allValues())

@section('content')
    <div class="mx-auto max-w-2xl px-4 pt-10">
        <div class="card p-6 md:p-10 text-center">
            <h1 class="text-3xl font-bold">{{ __('shop.contact.title') }}</h1>
            <p class="mt-3 text-slate/70">{{ __('shop.contact.text') }}</p>
            <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
                @if ($s['store_phone'])
                    <a href="tel:{{ $s['store_phone'] }}" class="btn-primary">☎ {{ __('shop.contact.call') }} {{ $s['store_phone'] }}</a>
                @endif
                @if ($s['whatsapp_number'])
                    <a href="https://wa.me/{{ $s['whatsapp_number'] }}" target="_blank" rel="noopener" class="btn bg-[#25D366] text-white">{{ __('shop.contact.whatsapp') }}</a>
                @endif
                @if ($s['facebook_url'])
                    <a href="{{ $s['facebook_url'] }}" target="_blank" rel="noopener" class="btn-ghost">{{ __('shop.contact.facebook') }}</a>
                @endif
            </div>
        </div>
    </div>
@endsection
