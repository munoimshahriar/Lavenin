@extends('layouts.app')

@section('title', __('shop.checkout.title'))

@push('head')<meta name="robots" content="noindex">@endpush

@php
    $methodData = collect($methods)->map(fn ($m) => [
        'value' => $m->value,
        'label' => $m->getLabel(),
        'needsTrx' => $m->requiresTransaction(),
        'number' => $m->merchantNumber(),
    ])->values();
    $selectedMethod = old('payment_method', $methods[0]->value ?? 'cod');
    $freeThreshold = (int) \App\Models\Setting::read('shipping_free_threshold');
@endphp

@section('content')
    <div class="mx-auto max-w-6xl px-4 pt-8"
         x-data="checkout({
            quoteUrl: @js(route('checkout.quote')),
            district: @js(old('district', $prefill['district'] ?? '')),
            coupon: @js(old('coupon', '')),
            method: @js($selectedMethod),
            methods: @js($methodData),
            totals: @js(['subtotal' => $quote['subtotal'], 'shipping' => $quote['shipping'], 'discount' => $quote['discount'], 'total' => $quote['total']]),
            freeThreshold: {{ $freeThreshold }},
         })">
        <h1 class="text-3xl font-bold mb-6">{{ __('shop.checkout.title') }}</h1>

        @if ($errors->has('cart'))
            <div class="card p-4 mb-4 border-l-4 border-coral text-coral">{{ $errors->first('cart') }}</div>
        @endif

        <form method="post" action="{{ route('checkout.store') }}" class="grid lg:grid-cols-5 gap-6" novalidate>
            @csrf
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

            <div class="lg:col-span-3 space-y-6">
                <section class="card p-5 md:p-6 space-y-4">
                    <h2 class="text-xl font-bold">{{ __('shop.checkout.details') }}</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="label" for="name">{{ __('shop.checkout.name') }}</label>
                            <input id="name" name="name" class="input" required maxlength="100" autocomplete="name" value="{{ old('name', $prefill['name'] ?? '') }}">
                            @error('name') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label" for="phone">{{ __('shop.checkout.phone') }}</label>
                            <input id="phone" name="phone" class="input" required inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" value="{{ old('phone', $prefill['phone'] ?? '') }}">
                            @error('phone') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label" for="email">{{ __('shop.checkout.email') }}</label>
                            <input id="email" name="email" type="email" class="input" autocomplete="email" value="{{ old('email', $prefill['email'] ?? '') }}">
                            @error('email') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label" for="district">{{ __('shop.checkout.district') }}</label>
                            <select id="district" name="district" class="input" required x-model="district" @change="refresh()">
                                <option value="">{{ __('shop.checkout.choose_district') }}</option>
                                @foreach ($districts as $en => $bn)
                                    <option value="{{ $en }}">{{ app()->getLocale() === 'bn' ? $bn : $en }}</option>
                                @endforeach
                            </select>
                            @error('district') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label" for="city">{{ __('shop.checkout.city') }}</label>
                            <input id="city" name="city" class="input" required maxlength="100" value="{{ old('city', $prefill['city'] ?? '') }}">
                            @error('city') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label" for="address">{{ __('shop.checkout.address') }}</label>
                            <textarea id="address" name="address" rows="2" class="input" required maxlength="500" autocomplete="street-address">{{ old('address', $prefill['address'] ?? '') }}</textarea>
                            @error('address') <p class="error">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label" for="note">{{ __('shop.checkout.note') }}</label>
                            <textarea id="note" name="note" rows="2" class="input" maxlength="500">{{ old('note') }}</textarea>
                        </div>
                    </div>
                </section>

                <section class="card p-5 md:p-6 space-y-4">
                    <h2 class="text-xl font-bold">{{ __('shop.checkout.payment') }}</h2>
                    @error('payment_method') <p class="error">{{ $message }}</p> @enderror
                    <div class="grid sm:grid-cols-3 gap-3">
                        <template x-for="m in methods" :key="m.value">
                            <label class="cursor-pointer rounded-2xl border-2 p-4 font-semibold text-center transition"
                                   :class="method === m.value ? 'border-teal bg-mint' : 'border-mint bg-white'">
                                <input type="radio" name="payment_method" :value="m.value" x-model="method" class="sr-only">
                                <span x-text="m.label"></span>
                            </label>
                        </template>
                    </div>

                    <template x-if="current().needsTrx">
                        <div class="rounded-2xl bg-mint p-4 space-y-4">
                            <p class="text-sm" x-text="sendMoneyText()"></p>
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="label" for="sender_number" x-text="senderLabel()"></label>
                                    <input id="sender_number" name="sender_number" class="input" inputmode="tel" placeholder="01XXXXXXXXX" value="{{ old('sender_number') }}">
                                </div>
                                <div>
                                    <label class="label" for="trx_id">{{ __('shop.checkout.trx_id') }}</label>
                                    <input id="trx_id" name="trx_id" class="input uppercase" maxlength="30" autocomplete="off" value="{{ old('trx_id') }}">
                                </div>
                            </div>
                        </div>
                    </template>
                    @error('sender_number') <p class="error">{{ $message }}</p> @enderror
                    @error('trx_id') <p class="error">{{ $message }}</p> @enderror
                </section>
            </div>

            <aside class="lg:col-span-2">
                <div class="card p-5 md:p-6 lg:sticky lg:top-24 space-y-4">
                    <h2 class="text-xl font-bold">{{ __('shop.checkout.summary') }}</h2>
                    <ul class="divide-y divide-mint text-sm">
                        @foreach ($quote['items'] as $item)
                            <li class="py-2 flex justify-between gap-3">
                                <span>{{ $item->product->name }} <span class="text-slate/60">× {{ $item->quantity }}</span></span>
                                <span class="font-semibold whitespace-nowrap">৳{{ number_format($item->product->price * $item->quantity) }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div>
                        <label class="label" for="coupon">{{ __('shop.checkout.coupon') }}</label>
                        <div class="flex gap-2">
                            <input id="coupon" name="coupon" class="input uppercase py-2" maxlength="40" x-model="coupon" @keydown.enter.prevent="refresh()">
                            <button type="button" class="btn-ghost py-2" @click="refresh()">{{ __('shop.checkout.apply') }}</button>
                        </div>
                        <p class="error" x-show="couponError" x-text="couponError" x-cloak></p>
                        <p class="mt-1 text-sm text-teal-deep" x-show="couponApplied" x-cloak>{{ __('shop.coupon.applied') }} ✓</p>
                        @error('coupon') <p class="error">{{ $message }}</p> @enderror
                    </div>

                    <dl class="space-y-1.5 text-sm border-t border-mint pt-4">
                        <div class="flex justify-between"><dt>{{ __('shop.cart.subtotal') }}</dt><dd x-text="money(totals.subtotal)"></dd></div>
                        <div class="flex justify-between" x-show="totals.discount > 0" x-cloak><dt>{{ __('shop.checkout.discount') }}</dt><dd class="text-coral" x-text="'− ' + money(totals.discount)"></dd></div>
                        <div class="flex justify-between"><dt>{{ __('shop.checkout.shipping') }}</dt>
                            <dd x-text="!district ? '—' : (totals.shipping === 0 ? @js(__('shop.checkout.free')) : money(totals.shipping))"></dd></div>
                        <div class="flex justify-between text-lg font-bold pt-2 border-t border-mint"><dt>{{ __('shop.checkout.total') }}</dt><dd class="text-teal-deep" x-text="money(totals.total)"></dd></div>
                    </dl>
                    <p class="text-xs text-teal-deep" x-show="totals.shipping > 0 && freeThreshold > totals.subtotal" x-cloak x-text="freeHint()"></p>

                    <button type="submit" class="btn-cta w-full text-lg">{{ __('shop.checkout.place_order') }}</button>
                </div>
            </aside>
        </form>
    </div>

    <script nonce="{{ Vite::cspNonce() }}">
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkout', (cfg) => ({
                ...cfg,
                couponError: null,
                couponApplied: false,
                init() { if (this.district || this.coupon) this.refresh(); },
                current() { return this.methods.find(m => m.value === this.method) || {}; },
                money(n) { return '৳' + Number(n).toLocaleString('en-IN'); },
                sendMoneyText() {
                    return @js(__('shop.checkout.send_money_to'))
                        .replace(':amount', Number(this.totals.total).toLocaleString('en-IN'))
                        .replace(':method', this.current().label)
                        .replace(':number', this.current().number);
                },
                senderLabel() { return @js(__('shop.checkout.sender_number')).replace(':method', this.current().label); },
                freeHint() { return @js(__('shop.checkout.free_shipping_hint')).replace(':amount', (this.freeThreshold - this.totals.subtotal).toLocaleString('en-IN')); },
                async refresh() {
                    const params = new URLSearchParams({ district: this.district || '', coupon: this.coupon || '' });
                    const res = await fetch(this.quoteUrl + '?' + params, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();
                    this.totals = data;
                    this.couponError = data.coupon_error;
                    this.couponApplied = data.coupon_applied;
                },
            }));
        });
    </script>
@endsection
