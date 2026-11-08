@php
    $metaTitle = trim($__env->yieldContent('title')) ?: __('shop.tagline');
    $metaDescription = trim($__env->yieldContent('description')) ?: __('shop.hero_text');
    $metaImage = trim($__env->yieldContent('og_image')) ?: asset('og-default.png');
    $settings = \App\Models\Setting::allValues();
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $metaTitle }} | Lavenin</title>
    <meta name="description" content="{{ Str::limit(strip_tags($metaDescription), 160) }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="theme-color" content="#76A9AD">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <meta property="og:site_name" content="Lavenin">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($metaDescription), 200) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'bn' ? 'bn_BD' : 'en_US' }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;700&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')

    @if ($settings['ga4_id'])
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $settings['ga4_id'] }}"></script>
        <script nonce="{{ Vite::cspNonce() }}">
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', @json($settings['ga4_id']));
        </script>
    @endif
    @if ($settings['fb_pixel_id'])
        <script nonce="{{ Vite::cspNonce() }}">
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
            document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', @json($settings['fb_pixel_id']));
            fbq('track', 'PageView');
        </script>
    @endif
</head>
<body class="min-h-screen flex flex-col">
    @include('partials.header')

    @if (session('status'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition
             class="fixed bottom-4 inset-x-4 sm:inset-x-auto sm:right-4 z-50 card bg-slate text-white px-5 py-3 text-sm font-semibold" role="status">
            {{ session('status') }}
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer', ['settings' => $settings])
    @stack('scripts')
</body>
</html>
