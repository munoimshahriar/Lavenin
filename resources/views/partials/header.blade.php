@php($other = app()->getLocale() === 'bn' ? 'en' : 'bn')
<header x-data="{ open: false }" class="sticky top-0 z-30 bg-cream/90 backdrop-blur border-b border-mint">
    <div class="mx-auto max-w-6xl px-4 h-16 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="font-display text-2xl font-bold text-teal-deep">Lavenin</a>

        <nav class="hidden md:flex items-center gap-6 font-semibold">
            <a href="{{ route('home') }}" class="hover:text-teal-deep">{{ __('shop.nav.home') }}</a>
            <a href="#" class="hover:text-teal-deep">{{ __('shop.nav.shop') }}</a>
            <a href="#" class="hover:text-teal-deep">{{ __('shop.nav.contact') }}</a>
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ route('locale.switch', $other) }}"
               class="rounded-full border border-teal px-3 py-1 text-sm font-semibold text-teal-deep hover:bg-mint">
                {{ __('shop.language') }}
            </a>
            <a href="#" class="rounded-full p-2 hover:bg-mint" aria-label="{{ __('shop.nav.cart') }}">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 2-1.5L21 8H6.2"/><circle cx="10" cy="20" r="1"/><circle cx="17" cy="20" r="1"/></svg>
            </a>
            <button @click="open = !open" class="md:hidden rounded-full p-2 hover:bg-mint" aria-label="Menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>
    </div>

    <nav x-show="open" x-cloak x-transition class="md:hidden border-t border-mint px-4 py-3 flex flex-col gap-3 font-semibold">
        <a href="{{ route('home') }}">{{ __('shop.nav.home') }}</a>
        <a href="#">{{ __('shop.nav.shop') }}</a>
        <a href="#">{{ __('shop.nav.contact') }}</a>
    </nav>
</header>
