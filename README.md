# Lavenin

Baby toy store — lavenin.com. See `CLAUDE.md` for architecture and `docs/PLAN.md` for the build phases.

## Local setup

Requirements: PHP 8.2+, Composer, Node 18+, MySQL/MariaDB.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# create an empty MySQL database named "lavenin" (utf8mb4)
php artisan migrate --seed
php artisan storage:link
npm run build        # or: npm run dev (during development)
php artisan serve    # http://localhost:8000
```

- Storefront: `/` (Bangla by default; toggle in the header)
- Admin panel: `/manage`

Run tests with `php artisan test` (uses in-memory SQLite).
