@extends('layouts.app')

@section('title', __('shop.auth.login'))

@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
    <div class="mx-auto max-w-md px-4 pt-10">
        <form method="post" action="{{ route('login') }}" class="card p-6 md:p-8 space-y-4">
            @csrf
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
            <h1 class="text-2xl font-bold">{{ __('shop.auth.login_title') }}</h1>
            <div>
                <label class="label" for="login">{{ __('shop.auth.login_id') }}</label>
                <input id="login" name="login" class="input" required autocomplete="username" value="{{ old('login') }}" autofocus>
                @error('login') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label" for="password">{{ __('shop.auth.password') }}</label>
                <input id="password" name="password" type="password" class="input" required autocomplete="current-password">
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1" class="rounded text-teal"> {{ __('shop.auth.remember') }}</label>
            <button class="btn-primary w-full">{{ __('shop.auth.login') }}</button>
            <p class="text-sm text-center">{{ __('shop.auth.no_account') }} <a href="{{ route('register') }}" class="font-semibold text-teal-deep underline">{{ __('shop.auth.register') }}</a></p>
        </form>
    </div>
@endsection
