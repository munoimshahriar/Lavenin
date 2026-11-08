<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Customer accounts: log in with phone or email + password (no OTP). */
class AuthController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->merge(['login' => trim((string) $request->input('login'))]);
        $data = $request->validate([
            'website' => ['prohibited'],
            'login' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string', 'max:200'],
        ]);

        $key = 'login:'.Str::lower($data['login']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['login' => __('shop.auth.throttle', ['seconds' => RateLimiter::availableIn($key)])]);
        }

        $field = str_contains($data['login'], '@') ? 'email' : 'phone';
        $value = $field === 'phone' ? User::normalizePhone($data['login']) : Str::lower($data['login']);

        if (! Auth::attempt([$field => $value, 'password' => $data['password']], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['login' => __('shop.auth.failed')]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        $this->cart->mergeGuestCartInto($request->user());

        return redirect()->intended(route('account'));
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'phone' => User::normalizePhone($request->input('phone')),
            'email' => $request->filled('email') ? Str::lower(trim($request->input('email'))) : null,
        ]);

        $data = $request->validate([
            'website' => ['prohibited'],
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:'.config('bd.phone_regex'), Rule::unique('users', 'phone')],
            'email' => ['nullable', 'email:rfc', 'max:190', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], ['phone.regex' => __('shop.validation.phone')]);

        unset($data['website']);
        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();
        $this->cart->mergeGuestCartInto($user);

        return redirect()->intended(route('account'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
