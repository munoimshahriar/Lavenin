# Lavenin

Baby toy store — lavenin.com. Laravel 12 + Filament 3, Bangla/English, BDT. See `CLAUDE.md` (architecture), `docs/PLAN.md` (phases), `docs/DECISIONS.md` (choices made) and `docs/DEPLOY-HOSTINGER.md` (going live).

## Local setup

Requirements: PHP 8.2+ (gd, intl, zip, pdo_mysql), Composer, Node 18+, MySQL/MariaDB.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# create an empty MySQL database named "lavenin" (utf8mb4); set OWNER_EMAIL / OWNER_PASSWORD in .env
php artisan migrate --seed
php artisan storage:link
npm run build        # or: npm run dev while editing
php artisan serve    # http://localhost:8000  (use "localhost", not 127.0.0.1, so images match APP_URL)
```

- Storefront: `/` (Bangla by default; toggle in the header)
- Admin: `/manage` — log in with the owner account; you'll be asked to set up 2FA (Google Authenticator etc.) on first login.

## What's where

| Area | Code |
|---|---|
| Shipping rules | `app/Services/ShippingCalculator.php` (+ admin Settings) |
| Cart / checkout | `app/Services/CartService.php`, `CheckoutService.php`, `CheckoutController.php` |
| Payment methods | `app/Enums/PaymentMethod.php` (add a gateway as a new case) |
| Order statuses + stock movement | `app/Enums/OrderStatus.php`, `Order::transitionTo()` |
| Admin screens | `app/Filament/Resources/*`, `app/Filament/Pages/Settings.php` |
| Text (bn/en) | `lang/bn/shop.php`, `lang/en/shop.php` |
| Security headers / CSP | `app/Http/Middleware/SecurityHeaders.php` |

## Tests

```bash
php artisan test
```

Uses in-memory SQLite. Covers shipping rules, checkout (server-side totals, bKash/Nagad, coupons, stock), auth + rate limiting, reviews, wishlist, role access and every admin page.
