# Worker Marketplace (Laravel 12 starter)

A clean, database-backed starter for an India-focused service marketplace. It includes customer registration/login, worker onboarding with admin approval, service discovery, booking requests, worker booking actions, admin overview, reviews, seed data and local SVG demo images.

> This is a runnable MVP foundation, not a claim that every external integration is production-ready. Razorpay, SMS/WhatsApp, Google Maps, refunds, payouts, advanced slot locking and full support workflows need provider credentials and further hardening before launch.

## 1. Requirements
- PHP 8.2+
- Composer 2
- MySQL 8+ or MariaDB 10.4+
- Node.js 20+ and npm (optional for asset compilation; the starter CSS works without it)
- Laravel 12

## 2. Create the Laravel framework project
From the folder where you keep projects:

```bash
composer create-project laravel/laravel worker-marketplace "^12.0"
```

Copy/merge the contents of this ZIP into that new Laravel project. If Windows asks whether to replace files, replace only files included in this bundle. Keep Laravel's `vendor/`, `.env`, and framework files.

## 3. Configure database
Create a database named `worker_marketplace` in phpMyAdmin, then update `.env`:

```env
APP_NAME="Worker Marketplace"
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=worker_marketplace
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Generate key and migrate/seed:

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```
Open http://127.0.0.1:8000.

## 4. Demo accounts
All demo accounts use password `password`:
- Admin: `admin@example.com`
- Worker (approved): `worker@example.com`
- Customer: `customer@example.com`

Change these credentials immediately and never use demo credentials in production.

## 5. Main folder map

```text
app/
  Http/Controllers/       Web controllers (auth, services, bookings, worker, admin)
  Http/Middleware/        Role access middleware
  Models/                 Eloquent models and relationships
  Services/               Booking business logic
 database/
  migrations/              Marketplace tables
  seeders/                 Demo categories, services and accounts
 resources/views/
  layouts/                 Shared public/dashboard layout
  auth/                    Login and registration
  services/                Browse and service details
  bookings/                Booking form and customer bookings
  worker/                  Worker onboarding and dashboard
  admin/                   Admin dashboard and worker approvals
 public/assets/
  css/                     Main responsive stylesheet
  js/                      Small frontend interactions
  images/                  Local SVG dummy illustrations
 routes/web.php            Web routes
 tests/Feature/            Basic feature tests
```

## 6. Core workflows included
1. Customer registers and logs in.
2. Worker submits a service-provider profile; account is pending until admin approval.
3. Admin approves workers.
4. Customer browses services and creates a booking request.
5. Worker accepts/rejects the request and updates job status.
6. Customer can review a completed booking.

## 7. Important production work before accepting real bookings
- Install and configure a supported Laravel authentication/2FA/OTP flow; current starter uses email/password authentication.
- Implement and test Razorpay Orders API, server-side signature verification, webhook verification and idempotency. Do not mark payments paid from browser callbacks alone.
- Add a transactional availability/slot reservation service with database locking and expiry to prevent overlapping jobs under concurrent requests.
- Add worker KYC consent, private document storage, access controls, retention/deletion policies and manual verification procedures.
- Add real SMS/WhatsApp/email providers, queues, retries and delivery logs.
- Add tax invoices, cancellation/refund rules, commission ledger, payout reconciliation and accounting review.
- Configure Google Maps with restricted keys; verify address/PIN coverage and distance calculations server-side.
- Add policies, rate limits, audit trails, backups, monitoring, HTTPS, CSP/security headers and automated tests.
- Review Indian legal, tax, consumer protection and privacy obligations with qualified professionals.

## 8. Useful commands
```bash
php artisan route:list
php artisan test
php artisan migrate:fresh --seed   # DELETES ALL DATABASE DATA
```

## 9. Image assets
The `public/assets/images` folder contains local SVG placeholder illustrations. Replace them with licensed worker/service photos. Do not use customer/worker identity photos without permission.
