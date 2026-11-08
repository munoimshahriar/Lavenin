# Decisions log

## 2026-11-08 — Laravel 12 instead of Laravel 11
Laravel 11 is out of security support and `composer audit` reported 5 advisories (1 critical: TokenGuard auth bypass) fixed only in 12.69+/13.x. Owner chose Laravel 12 (Filament v3 compatible, small upgrade). Laravel 12 security support ends ~Feb 2027 — plan an upgrade to 13 (with Filament v4) around launch.

## 2026-11-08 — Tests use in-memory SQLite
`phpunit.xml` runs tests on SQLite `:memory:` for speed; the app itself uses MySQL. Avoid MySQL-only SQL in queries.

## 2026-11-08 — Build decisions (phases 1–4)
- **Prices are whole taka** (`unsigned int`), no paisa — simpler and matches how BD shops price.
- **Shipping "city" = the district** chosen at checkout (Khulna / Dhaka districts get the special rates). Rates live in admin → Settings.
- **Admin panel is English-only** (`AdminLocale` middleware); the storefront is bn/en. Mixed-language admin labels were confusing.
- **Admin 2FA via `jeffgreco13/filament-breezy`** (TOTP + recovery codes). Forced for `owner`; optional for manager/staff. Breezy also adds the "Profile" page (change password).
- **Roles enforced in Filament resources** (`RestrictedToRoles` trait) rather than fine-grained spatie permissions — three fixed roles don't need a permission matrix.
- **Guest cart lives in the DB** (`carts.user_id = null`) keyed by `session('cart_id')`; merged into the user's cart on login/register.
- **Guests see their order page** only from the browser that placed it (order ids kept in session); logged-in users see their own orders.
- **CSP** (nonce-based) applies to the storefront only. It includes `'unsafe-eval'` because Alpine.js (standard build) needs it. The Filament admin gets the other security headers but no CSP (Livewire injects inline scripts).
- **Images**: uploads go to `storage/app/public/uploads-tmp`, then are re-encoded to WebP (1200px + 400px square thumb) with random names and the original deleted.
- **bKash/Nagad** only appear at checkout once their merchant number is set in Settings.
- **Seeder** creates roles, the owner (from `OWNER_*` in `.env`), 4 categories and 8 placeholder products with generated images.
