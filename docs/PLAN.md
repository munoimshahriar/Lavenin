# Build plan — run one phase per Claude Code session

Each phase ends with: tests green, `git commit`, one-paragraph summary. Owner reviews in browser before the next phase.

## Phase 0 — Scaffold (small)
- `composer create-project laravel/laravel .` (Laravel 11), Tailwind + Alpine via Vite, MySQL `.env.example`.
- Install Filament v3 (panel path `/manage`), spatie permission + translatable, Intervention Image.
- Locale middleware (bn/en toggle), base layout with palette tokens + fonts from CLAUDE.md.
- `README.md` with local setup (`composer install`, `npm run build`, `php artisan migrate --seed`).

## Phase 1 — Catalog + Admin products
- Migrations/models: categories, products, product_images, settings.
- Filament resources: Category, Product (bilingual tabs, multi-image sortable upload, video URL, stock), Settings page (shipping rates, bKash/Nagad numbers, store phone/WhatsApp).
- Roles/permissions seeded; Filament user resource (owner only); TOTP 2FA.
- Storefront: home (hero, featured, categories), category page, product page (gallery, embedded video, age range, BDT price, add-to-cart, wishlist heart).
- Seed 8 placeholder products.

## Phase 2 — Cart, checkout, orders
- Session cart (guest) + DB cart (logged in), merge on login.
- Checkout: name, phone, district select (64 districts), city/area, address, note; `ShippingCalculator` with tests; coupon field; payment choice (COD / bKash / Nagad with instructions + TrxID + sender number).
- Order confirmation page + SMS-less: show order number, WhatsApp "send receipt" link.
- Filament Order resource: list with filters, status transitions, payment verification, printable packing slip, stock movement on confirm/cancel, low-stock badge on dashboard.
- Customer auth (phone or email + password), account page with order history.

## Phase 3 — Growth features
- Coupons (percent/fixed, min order, expiry, usage limit) in admin.
- Reviews (verified purchase, admin approval), wishlist page.
- SEO: meta, OpenGraph, sitemap, JSON-LD Product, Bangla slugs transliterated.
- Facebook Pixel + GA4 via settings (optional IDs).

## Phase 4 — Hardening + deploy
- Security headers middleware, throttles, honeypots, upload hardening (all items in CLAUDE.md checklist), `php artisan optimize`.
- `docs/DEPLOY-HOSTINGER.md`: upload build, point document root to `/public` (or `.htaccess` redirect), set `.env`, `storage:link`, cron for `schedule:run`, nightly DB backup via Hostinger.
- Lighthouse pass (mobile ≥ 85), bn/en copy review.

## Explicitly out of scope (for now)
SSLCommerz gateway, SMS OTP, multi-currency, marketplace/vendors, mobile app.
