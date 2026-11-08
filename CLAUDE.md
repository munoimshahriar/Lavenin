# Lavenin — baby toy e-commerce (lavenin.com)

Read this file fully before any work. Follow `docs/PLAN.md` phase by phase; do not jump ahead or add unrequested features. Keep answers short; the owner is token-conscious.

## Decisions (locked — do not re-ask)

| Topic | Decision |
|---|---|
| Market | Bangladesh only. Currency BDT (৳). |
| Hosting target | Hostinger **shared** hosting → PHP 8.2+ / MySQL. No Node runtime in production (Node only at build time for assets). |
| Stack | Laravel 11, Blade + Tailwind CSS + Alpine.js storefront, **Filament v3** for admin, MySQL, `spatie/laravel-permission` for roles. |
| Languages | Bangla + English toggle (Laravel `lang/bn`, `lang/en`; cookie/session-based locale, default **bn**). Product name/description stored in both languages (JSON columns via `spatie/laravel-translatable`). |
| Payments | COD, or manual **bKash / Nagad** "Send Money": customer sees the merchant number, enters sender number + TrxID at checkout; admin verifies and marks paid. No gateway at launch (SSLCommerz later — keep a `PaymentMethod` abstraction). |
| Shipping | See rules below. |
| Videos | YouTube / Facebook embed URLs stored on product (no video uploads). Images uploaded to `storage/app/public`, auto-resized (Intervention Image) to 1200px + 400px thumb, WebP. |
| Admin | Multiple admins with roles (see Roles). 2FA (TOTP) mandatory for `owner`. |
| Repo | github.com/<owner>/lavenin, public → **never commit secrets** (`.env`, keys). |

## Shipping rules (৳)

```
subtotal >= 2000            → free (everywhere)
city == Khulna && subtotal >= 500 → free
city == Khulna              → 70
city == Dhaka               → 70
else                        → 100
```
Implement in one `ShippingCalculator` service with unit tests. Rates editable in admin Settings (store these as defaults).

## Roles

- `owner` — everything, incl. admin user management & settings.
- `manager` — products, categories, orders, coupons, reviews, inventory.
- `staff` — orders only (view, change status, verify payment). Cannot delete anything.

## Data model (minimum)

`users` (customers + admins, `is_admin` via role), `categories`, `products` (slug, name{bn,en}, description{bn,en}, price, compare_at_price, sku, stock, low_stock_threshold, is_active, video_url, age_range), `product_images` (sortable), `carts`/`cart_items` (guest via session, merged on login), `orders` (number, status, shipping address incl. district/city, phone, subtotal, shipping, discount, total, payment_method, payment_status, trx_id, sender_number), `order_items` (snapshot name+price), `coupons`, `wishlists`, `reviews` (verified-purchase only, admin-approved), `settings`.

Order statuses: `pending → confirmed → packed → shipped → delivered | cancelled`. Payment: `unpaid | pending_verification | paid | refunded`. Stock decrements on `confirmed`, restores on `cancelled`.

## Security checklist (non-negotiable)

- Admin at `/manage` (not `/admin`), rate-limited login (5/min), TOTP 2FA, session timeout 2h, HTTPS-only cookies, `SameSite=Lax`.
- All forms CSRF-protected; all input validated via Form Requests; prices/totals recomputed server-side, never trusted from client.
- Uploads: validate MIME by content, re-encode images, random filenames, max 5 MB, served from `/storage` only.
- Mass-assignment guarded; no raw SQL with interpolation; Eloquent only.
- Security headers via middleware (CSP allowing YouTube/Facebook iframes, X-Frame-Options, Referrer-Policy, HSTS).
- Phone-number-based customer login allowed (BD norm) with OTP-less password; enforce min 8 chars + rate limit.
- Checkout endpoints throttled; honeypot on register/review.
- `APP_DEBUG=false` in prod; `.env.example` committed, `.env` never.

## Design direction

Brand palette (from owner, approved; refined for a baby brand):
- `--cream: #FAF7F2` page background (warm off-white; replaces flat grey)
- `--teal: #76A9AD` primary / buttons / links (owner's teal, kept)
- `--teal-deep: #4F8488` hover / headings accent
- `--slate: #30363F` body text / footer (owner's dark slate)
- `--coral: #FF5722` CTA accents, badges, sale tags (owner's orange — use sparingly, <10% of surface)
- `--mint: #E3EFEF` section backgrounds / cards
Type: `Fredoka` (display, rounded, friendly) + `Nunito` (body); Bangla: `Hind Siliguri`. Rounded 16–24px corners, soft shadows, generous whitespace, playful but not childish. Mobile-first (most BD traffic is phone).

## Workflow rules

- Commit after each phase with a conventional message. Run `php artisan test` before committing.
- Seed 8 placeholder products + categories + an owner account (`php artisan db:seed`); real content is entered via admin.
- Never run `migrate:fresh` on anything but local.
- When unsure between two options, pick the simpler one and note it in `docs/DECISIONS.md` instead of asking.
