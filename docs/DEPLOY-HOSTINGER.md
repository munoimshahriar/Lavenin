# Deploying Lavenin to Hostinger (shared hosting)

Hostinger shared hosting runs PHP + MySQL but no Node, so assets are built on your PC and uploaded.

## 1. Hostinger panel (hPanel) setup — once

1. **Websites → lavenin.com → Advanced → PHP Configuration**: choose **PHP 8.3**. Enable extensions: `gd`, `intl`, `fileinfo`, `mbstring`, `pdo_mysql`, `zip`, `exif`.
2. **Databases → MySQL Databases**: create a database + user. Note the DB name, user and password (they look like `u123456789_lavenin`).
3. **Security → SSL**: install the free SSL certificate and turn on **Force HTTPS**.
4. **Advanced → SSH Access**: enable SSH (needed for `php artisan` commands).

## 2. Build on your PC

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

## 3. Upload

Upload the whole project (except `node_modules`, `.git`, `tests`, `.env`) to a folder **outside** `public_html`, e.g. `/home/USER/lavenin`.

Then make `public_html` serve Laravel's `public/` folder. Simplest on shared hosting — over SSH:

```bash
cd ~
rm -rf public_html           # only if it's empty / the default page!
ln -s ~/lavenin/public public_html
```

(If symlinks are not allowed: copy the *contents* of `lavenin/public` into `public_html` and edit `public_html/index.php` so the two `__DIR__.'/../...'` paths point to `__DIR__.'/../lavenin/...'`.)

## 4. Configure `.env` on the server

```bash
cd ~/lavenin
cp .env.example .env
php artisan key:generate
```

Edit `.env` (hPanel File Manager or `nano .env`):

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lavenin.com

DB_HOST=localhost
DB_DATABASE=u123456789_lavenin
DB_USERNAME=u123456789_lavenin
DB_PASSWORD=********

SESSION_SECURE_COOKIE=true
SESSION_LIFETIME=120

OWNER_NAME="Your Name"
OWNER_EMAIL=you@example.com
OWNER_PASSWORD=          # leave empty: a random one is printed once by the seeder
```

**Never** set `APP_DEBUG=true` on the live site, and never commit `.env`.

## 5. First deploy commands

```bash
cd ~/lavenin
php artisan migrate --force
php artisan db:seed --force        # roles + owner + 8 placeholder products (skip products: see below)
php artisan storage:link
php artisan optimize
php artisan filament:optimize
chmod -R 775 storage bootstrap/cache
```

Then log in at **https://lavenin.com/manage**, set up two-factor authentication (required for the owner), and fill in **Settings** (bKash/Nagad numbers, phone, WhatsApp). Delete the placeholder products and add real ones.

> Don't want placeholder products on the live site? Run `php artisan db:seed --class=RoleSeeder --force` and `php artisan db:seed --class=OwnerSeeder --force` instead of the full seed.

## 6. Cron (scheduler)

hPanel → **Advanced → Cron Jobs** → every minute:

```
cd /home/USER/lavenin && php artisan schedule:run >> /dev/null 2>&1
```

## 7. Backups

hPanel → **Files → Backups**: Hostinger takes daily backups on most plans. Also enable a **daily database backup** and download one occasionally. Product images live in `storage/app/public` — include that folder in file backups.

## Updating the site later

```bash
# on your PC
composer install --no-dev --optimize-autoloader && npm run build
# upload changed files (app/, resources/, public/build/, routes/, config/, database/, vendor/ if composer changed)
# on the server
php artisan down
php artisan migrate --force
php artisan optimize:clear && php artisan optimize && php artisan filament:optimize
php artisan up
```

Never run `migrate:fresh` or `db:wipe` on the server — it deletes all orders.

## Security checklist (live)

- `APP_DEBUG=false`, `APP_ENV=production`, HTTPS forced, `SESSION_SECURE_COOKIE=true`.
- Owner account has 2FA enabled (the panel forces it on first login).
- Admin is at `/manage` (not `/admin`); login is rate-limited.
- If you put Cloudflare in front of the site, add its IP ranges to `trustProxies` in `bootstrap/app.php` so rate limits see real visitor IPs.
