<footer class="bg-slate text-cream/80 mt-16">
    <div class="mx-auto max-w-6xl px-4 py-10 grid gap-8 md:grid-cols-3">
        <div>
            <div class="font-display text-xl font-bold text-white">Lavenin</div>
            <p class="text-sm mt-1">{{ __('shop.tagline') }}</p>
        </div>
        <div class="text-sm space-y-1">
            <a href="{{ route('shop') }}" class="block hover:text-white">{{ __('shop.nav.shop') }}</a>
            <a href="{{ route('contact') }}" class="block hover:text-white">{{ __('shop.nav.contact') }}</a>
            @if ($settings['store_phone'])
                <a href="tel:{{ $settings['store_phone'] }}" class="block hover:text-white">☎ {{ $settings['store_phone'] }}</a>
            @endif
            @if ($settings['facebook_url'])
                <a href="{{ $settings['facebook_url'] }}" rel="noopener" target="_blank" class="block hover:text-white">{{ __('shop.contact.facebook') }}</a>
            @endif
        </div>
        <div class="text-sm">
            <div class="font-semibold text-white mb-2">{{ __('shop.footer.payments') }}</div>
            <div class="flex flex-wrap gap-2">
                @foreach (\App\Enums\PaymentMethod::available() as $method)
                    <span class="rounded-full bg-white/10 px-3 py-1">{{ $method->getLabel() }}</span>
                @endforeach
            </div>
        </div>
    </div>
    <div class="border-t border-white/10">
        <p class="mx-auto max-w-6xl px-4 py-4 text-xs">&copy; {{ date('Y') }} Lavenin. {{ __('shop.footer.rights') }}</p>
    </div>
</footer>
