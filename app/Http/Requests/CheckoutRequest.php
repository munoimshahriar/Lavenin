<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => User::normalizePhone($this->input('phone')),
            'sender_number' => User::normalizePhone($this->input('sender_number')),
            'trx_id' => $this->filled('trx_id') ? strtoupper(trim($this->input('trx_id'))) : null,
        ]);
    }

    public function rules(): array
    {
        $method = PaymentMethod::tryFrom((string) $this->input('payment_method'));
        $needsTrx = $method?->requiresTransaction() ?? false;
        $phone = 'regex:'.config('bd.phone_regex');

        return [
            'website' => ['prohibited'], // honeypot
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', $phone],
            'email' => ['nullable', 'email:rfc', 'max:190'],
            'district' => ['required', Rule::in(array_keys(config('bd.districts')))],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
            'coupon' => ['nullable', 'string', 'max:40'],
            'payment_method' => ['required', Rule::in(array_map(fn ($m) => $m->value, PaymentMethod::available()))],
            'sender_number' => [Rule::requiredIf($needsTrx), 'nullable', $phone],
            'trx_id' => [Rule::requiredIf($needsTrx), 'nullable', 'alpha_num', 'between:6,30'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => __('shop.validation.phone'),
            'sender_number.regex' => __('shop.validation.phone'),
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('shop.checkout.name'),
            'phone' => __('shop.checkout.phone'),
            'district' => __('shop.checkout.district'),
            'city' => __('shop.checkout.city'),
            'address' => __('shop.checkout.address'),
            'payment_method' => __('shop.checkout.payment'),
            'trx_id' => 'TrxID',
        ];
    }
}
