@extends('layouts.app')

@section('title', __('shop.auth.register'))

@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
    <div class="mx-auto max-w-md px-4 pt-10">
        <form method="post" action="{{ route('register') }}" class="card p-6 md:p-8 space-y-4">
            @csrf
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
            <h1 class="text-2xl font-bold">{{ __('shop.auth.register_title') }}</h1>
            <div>
                <label class="label" for="name">{{ __('shop.auth.name') }}</label>
                <input id="name" name="name" class="input" required maxlength="100" autocomplete="name" value="{{ old('name') }}">
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="phone">{{ __('shop.auth.phone') }}</label>
                <input id="phone" name="phone" class="input" required inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" value="{{ old('phone') }}">
                @error('phone') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="email">{{ __('shop.auth.email') }}</label>
                <input id="email" name="email" type="email" class="input" autocomplete="email" value="{{ old('email') }}">
                @error('email') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="password">{{ __('shop.auth.password') }}</label>
                <input id="password" name="password" type="password" class="input" required minlength="8" autocomplete="new-password">
                @error('password') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="password_confirmation">{{ __('shop.auth.password_confirm') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="input" required minlength="8" autocomplete="new-password">
            </div>
            <button class="btn-primary w-full">{{ __('shop.auth.register') }}</button>
            <p class="text-sm text-center">{{ __('shop.auth.have_account') }} <a href="{{ route('login') }}" class="font-semibold text-teal-deep underline">{{ __('shop.auth.login') }}</a></p>
        </form>
    </div>
@endsection
