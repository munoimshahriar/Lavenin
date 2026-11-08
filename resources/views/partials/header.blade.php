@php
    $other = app()->getLocale() === 'bn' ? 'en' : 'bn';
    $cartCount = app(\App\Services\CartService::class)->count();
@endphp
<header x-data="{ open: false }" class="sticky top-0 z-30 bg-cream/90 backdrop-blur border-b border-mint">
    <div class="mx-auto max-w-6xl px-4 h-16 flex items-center justify-between gap-3">
        <a href="{{ route('home') }}" class="font-display text-2xl font-bold text-teal-deep shrink-0">Lavenin</a>

        <nav class="hidden md:flex items-center gap-6 font-semibold">
            <a href="{{ route('home') }}" class="hover:text-teal-deep">{{ __('shop.nav.home') }}</a>
            <a href="{{ route('shop') }}" class="hover:text-teal-deep">{{ __('shop.nav.shop') }}</a>
            <a href="{{ route('contact') }}" class="hover:text-teal-deep">{{ __('shop.nav.contact') }}</a>
        </nav>

        <form action="{{ route('shop') }}" method="get" class="hidden lg:block flex-1 max-w-xs">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('shop.shop.search') }}"
                   class="input rounded-full py-2 text-sm">
        </form>

        <div class="flex items-center gap-1">
            <a href="{{ route('locale.switch', $other) }}"
               class="rounded-full border border-teal px-3 py-1 text-sm font-semibold text-teal-deep hover:bg-mint">
                {{ __('shop.language') }}
            </a>
            @auth
                <a href="{{ route('wishlist') }}" class="hidden sm:inline-flex rounded-full p-2 hover:bg-mint" aria-label="{{ __('shop.nav.wishlist') }}">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s-7-4.4-9.3-9A5.3 5.3 0 0 1 12 6a5.3 5.3 0 0 1 9.3 6c-2.3 4.6-9.3 9-9.3 9z"/></svg>
                </a>
            @endauth
            <a href="{{ auth()->check() ? route('account') : route('login') }}" class="rounded-full p-2 hover:bg-mint" aria-label="{{ __('shop.nav.account') }}">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 21a8 8 0 0 1 16 0"/></svg>
            </a>
            <a href="{{ route('cart.index') }}" class="relative rounded-full p-2 hover:bg-mint" aria-label="{{ __('shop.nav.cart') }}">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6.2"/><circle cx="10" cy="20" r="1"/><circle cx="17" cy="20" r="1"/></svg>
                @if ($cartCount)
                    <span class="absolute -top-0.5 -right-0.5 min-w-5 h-5 px-1 rounded-full bg-coral text-white text-xs font-bold flex items-center justify-center">{{ $cartCount }}</span>
                @endif
            </a>
            <button @click="open = !open" class="md:hidden rounded-full p-2 hover:bg-mint" aria-label="{{ __('shop.nav.menu') }}" :aria-expanded="open">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </div>

    <nav x-show="open" x-cloak x-transition class="md:hidden border-t border-mint px-4 py-3 flex flex-col gap-3 font-semibold">
        <form action="{{ route('shop') }}" method="get">
            <input type="search" name="q" placeholder="{{ __('shop.shop.search') }}" class="input rounded-full py-2 text-sm">
        </form>
        <a href="{{ route('home') }}">{{ __('shop.nav.home') }}</a>
        <a href="{{ route('shop') }}">{{ __('shop.nav.shop') }}</a>
        <a href="{{ route('contact') }}">{{ __('shop.nav.contact') }}</a>
        @auth
            <a href="{{ route('wishlist') }}">{{ __('shop.nav.wishlist') }}</a>
            <a href="{{ route('account') }}">{{ __('shop.nav.account') }}</a>
        @else
            <a href="{{ route('login') }}">{{ __('shop.nav.login') }}</a>
        @endauth
    </nav>
</header>
