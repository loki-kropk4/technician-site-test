# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project status

This is a Laravel 13 site for **technician-store**, a computer repair/technician business ("expert diagnostics, repair, and maintenance for desktops, laptops..."). No domain models beyond the default `User`, no booking/service functionality yet — expect to be building most features from scratch. Auth (Breeze) and an Admin Panel for managing `User` records now exist — see below.

The initial site shell is complete and satisfies the original spec:
- Home page (`/`) with logo, welcome heading, and intro paragraph.
- Top navbar with a "Home" link, built to extend as more pages are added (see Architecture below).
- Footer with a "Contact us" heading and placeholder address/phone.
- Brand color palette applied with correct text/background contrast throughout.

New pages/features should follow the same patterns (layout component, navbar entry, brand color tokens) rather than introducing new ones.

An Admin Panel exists at `/admin/users` (list/sort/create/edit/delete user records) — see Architecture below. It is deliberately **not** linked from the navbar, and now requires being logged in **as the admin-role user specifically** (a real login system exists — see the Auth bullet below); a non-admin gets a 403.

**A real auth system now exists (Laravel Breeze, Blade stack)**: login (`/login`, name-or-email field), registration (`/register`, customer-role only, no auto-login), plus Breeze's default dashboard/profile/password-reset/email-verification pages (kept stock, unbranded, unlinked from the navbar — only login/register got the full brand restyle). The site is **closed by default**: a global `auth` middleware guards every route except `home` and the guest-only auth routes, which explicitly opt out via `->withoutMiddleware(Authenticate::class)` — see Architecture below before adding any new route, since it inherits this "must be logged in" default unless you deliberately exempt it.

Laravel Boost is not installed. If `AGENTS.md`/`CLAUDE.md` still show the `<laravel-boost-guidelines>` bootstrap block, run `composer require laravel/boost --dev && php artisan boost:install` before starting substantial work — it generates tailored guidelines and gives the agent extra Laravel-aware tools.

## Commands

Stack: PHP 8.3+, Laravel 13, MySQL (dev DB `technician_site`, per `.env`), Vite + Tailwind CSS v4 (+ `@tailwindcss/forms`), Alpine.js (added by Breeze, used by its own dropdown component only — the rest of the app is plain server-rendered Blade/vanilla JS), PHPUnit.

```sh
composer install && npm install     # install deps
cp .env.example .env && php artisan key:generate   # first-time env setup

php artisan dev                     # run app: serves PHP, queue listener, log tail (pail), and Vite together
# equivalent: composer run dev

npm run dev                         # Vite dev server only
npm run build                       # production asset build

composer test                       # clears config cache, then runs php artisan test
php artisan test                    # run full test suite
php artisan test --filter=testName  # run a single test by name
php artisan test tests/Feature/ExampleTest.php  # run a single test file

vendor/bin/pint                     # format PHP (Laravel Pint)
```

Migrations run against the MySQL DB configured in `.env` (create it first: `mysql -u root -e "CREATE DATABASE technician_site"`); tests run against an in-memory SQLite DB instead (see `phpunit.xml`), so keep migrations portable across both drivers (as the generated-column trick below does via `storedAs`).

`npm run build`/`npm run dev` may print `[lightningcss minify] Unknown at rule: @theme/@source/@plugin` warnings — cosmetic (Vite's CSS minifier doesn't recognize Tailwind v4's at-rules syntax before Tailwind processes them), the build still succeeds and the compiled CSS is correct; don't chase these.

## Architecture

- **Routing**: all routes go through `routes/web.php`, plus Breeze's `routes/auth.php` (required at the bottom of `web.php`). `GET /` → `HomeController@index`; `admin.users.*` (`index`/`create`/`store`/`edit`/`update`/`destroy`) → `App\Http\Controllers\Admin\AdminUserController` via `Route::resource('admin/users', ...)->except(['show'])->middleware('can:admin')`; Breeze's `dashboard`/`profile.*` routes are kept stock.
- **Auth posture — default-deny**: `bootstrap/app.php` appends `Illuminate\Auth\Middleware\Authenticate` to the global `web` middleware group and sets `redirectGuestsTo('/login')`, so **every route requires login unless it explicitly opts out** via `->withoutMiddleware(Authenticate::class)`. Only two places currently opt out: the `home` route in `routes/web.php`, and the whole guest-only group in `routes/auth.php` (register/login/forgot-password/reset-password — exempted once at the group level, not per-route). When adding a new route that should be public, you must add this exemption explicitly; the safe-by-default behavior is intentional, not a bug to route around silently.
- **Admin Gate**: `App\Providers\AppServiceProvider::boot()` defines `Gate::define('admin', fn (?User $user) => $user?->role === UserRole::Admin)`, applied to the admin resource route via `can:admin` middleware (global `auth` already covers "must be logged in"; `can:admin` is the additive role check).
- **Login/Registration** (`App\Http\Controllers\Auth\{AuthenticatedSessionController,RegisteredUserController}`, `App\Http\Requests\Auth\LoginRequest`, views `resources/views/auth/{login,register}.blade.php`): customized from Breeze's stock scaffolding —
  - Login accepts **name or email** in a single `login` field (`LoginRequest::authenticate()` picks the `email` or `name` column via `filter_var(..., FILTER_VALIDATE_EMAIL)`); throttling is keyed off `login` instead of `email`. Known accepted limitation: `name` has no DB uniqueness constraint, so a duplicate name could match the wrong user.
  - Login redirects to `route('home')` on success (not Breeze's default `dashboard`); `redirect()->intended(...)` is kept so a guest bounced off a protected page returns there instead.
  - Registration only collects name/email/password (no confirmation field, no role field), always creates `UserRole::Customer`, does **not** auto-login, and redirects to `route('login')` with a `success` flash instead of Breeze's default dashboard auto-login.
  - Both views reuse the existing `<x-flash-message />`/`<x-error-summary />` components (the latter extracted from the admin form during this work — reused in 3 places now) and `<x-application-logo>` (repointed to the site logo, not the Laravel mark — see the Assets bullet). Password fields have an eye-toggle button wired to a small vanilla-JS handler in `resources/js/app.js` (`data-password-toggle="#id"` + `data-icon-visible`/`data-icon-hidden` — reusable by any future password field).
  - Breeze's other default pages (dashboard, profile edit, forgot/reset-password, email verification) are intentionally left with Breeze's own stock Tailwind styling, unlinked from the navbar — not restyled, not deleted.
- **Views**: Blade components, not a JS framework. `resources/views/components/layout.blade.php` is the shared page shell (`<x-layout>`), composed with `<x-navbar>` and `<x-footer>` components. New pages should follow this pattern: a controller returning a `view()` wrapped in `<x-layout :title="...">`.
- **Navbar** (`resources/views/components/navbar.blade.php`): plain links are driven by a `$navItems` array (`['label' => ..., 'route' => ...]`) at the top of the component, with active-state styling via `request()->routeIs(...)`. Add new plain-link pages to that array rather than hand-writing `<a>` tags. Auth-aware controls (Login when a guest / user name + Logout when authenticated) are rendered separately after that loop via `@auth`/`@else`, since Logout has to be a `<form method="POST">` and doesn't fit the array-of-links model.
- **Footer** (`resources/views/components/footer.blade.php`): static "Contact us" block with placeholder address/phone — update these in place once real contact info is available, no new component needed.
- **Styling**: Tailwind CSS v4, configured via `@theme` in `resources/css/app.css` (not a `tailwind.config.js` — v4 uses CSS-based config). Brand palette is defined there as `--color-brand-{darkest,primary,light,pale}` mapping to `rgb(9,21,64) / rgb(27,44,193) / rgb(118,146,255) / rgb(171,210,250)` respectively — this exact 4-color palette is a project requirement, not a placeholder, and every future page must stick to it and maintain readable text/background contrast (the established pattern: `brand-darkest` backgrounds with `brand-pale`/`brand-light` text, `brand-pale` page background with `brand-darkest` text). Use the existing Tailwind utility classes (`text-brand-darkest`, `bg-brand-pale`, etc.) rather than introducing new ad hoc colors.
- **Assets**: built via `laravel-vite-plugin`; entrypoints are `resources/css/app.css` and `resources/js/app.js`, included in layouts with `@vite([...])`. `app.js` now bootstraps Alpine.js (added by Breeze, needed only by Breeze's own `dropdown.blade.php`) plus a small vanilla-JS password-visibility-toggle handler — the rest of the app is still plain server-rendered Blade + vanilla JS (e.g. `onsubmit="return confirm(...)"` for delete prompts), not built around Alpine. Static/public-facing files (like the logo, currently at `storage/app/public/main_page/logo.svg`) live under `storage/app/public/` and are served through the `public/storage` symlink via `Storage::url(...)`, not `public/`. **UI icons** (not user-uploadable content) are the one exception: hand-authored `.svg` files live in `public/icons/` and are referenced via `asset('icons/....svg')` — see `sort.svg`/`edit.svg`/`trash.svg` (brand-pale, for icons on dark buttons) and `eye.svg`/`eye-slash.svg` (brand-darkest, for icons inside light input fields) — never inline `<svg>` markup in Blade views. `resources/views/components/application-logo.blade.php` (Breeze's component) is repointed to this same logo file rather than the Laravel mark — reuse `<x-application-logo>` anywhere a logo is needed instead of duplicating the `Storage::url(...)` call.
- **Data layer**: only the stock `User` model/migration/factory exist, plus a `role` column (`VARCHAR(12)`, added via a separate migration rather than editing the original `create_users_table` migration since it had already run). No other domain-specific models yet — new features (services, bookings, technicians, etc.) will need their own migrations, models, and factories under `app/Models` and `database/migrations`.
- **User roles**: `role` is restricted to `App\Enums\UserRole` (`customer` / `technician` / `admin`) and cast to that enum on the model. Validate it with `User::roleRules($ignoreUserId = null)` (pass the user's own id on updates) rather than hand-writing rules — it bundles the allowed-values check with `App\Rules\UniqueAdminRole`, which enforces that only one user can hold `admin`. That uniqueness is also enforced at the DB level via a generated `admin_slot` column + unique index (see `database/migrations/2026_09_01_064551_add_admin_role_uniqueness_to_users_table.php`) as a race-condition backstop — don't remove it without replacing the guarantee some other way. Covered by `tests/Feature/UserRoleTest.php`. `UserFactory` has `->admin()`/`->technician()` state helpers (alongside `->unverified()`) for tests that need a specific role — remember only one `->admin()` row can exist per test/DB at a time (the DB constraint above applies in tests too).
- **Admin Panel** (`app/Http/Controllers/Admin/AdminUserController.php`, `app/Http/Requests/Admin/{Store,Update}UserRequest.php`, views under `resources/views/admin/users/`): lists/sorts/creates/edits/deletes `users` rows.
  - `index` sorts via `?sort=&direction=` query params, checked against a hardcoded column whitelist (`SORTABLE_COLUMNS`) before hitting `orderBy()` — never widen that whitelist without keeping it an allowlist (it's the SQL-injection guard for a user-controlled order-by column). It also looks up the sole `UserRole::Admin` user (if any) to greet them by name in the heading — since only the admin-role user can reach this page (see the Admin Gate bullet above), that lookup and the acting user are effectively the same person.
  - `create`/`store` only ever produce `UserRole::Technician` accounts — `store()` hardcodes the role and never reads it from the request, so the create form has no role field and can't be used to mint another admin/customer.
  - `edit`/`update` share one Blade view (`admin/users/form.blade.php`) with `create`/`store`; heading and password fields switch based on whether `$user` is null. Editing doesn't change `role`.
  - Password change on `update` uses "Old Password"/"New Password" fields: `UpdateUserRequest` only requires/checks `old_password` (via `Hash::check`) when `new_password` is actually filled in (see its `withValidator` after-hook) — leaving both blank keeps the password untouched.
  - Flash success messages (`session('success')`, rendered by `<x-flash-message />`) go through `admin.users.index` after create/update/delete; validation failures use Laravel's default redirect-back-with-`$errors` behavior, rendered as a banner above the form heading — no custom error-flash plumbing needed.
  - Covered by `tests/Feature/AdminUserControllerTest.php`, whose `setUp()` now creates and `actingAs()`s the sole admin user for every test (routes are `can:admin`-gated) — don't add a second `->admin()` factory call anywhere in that class, reuse `$this->admin`. Guest/non-admin/admin access itself is covered separately in `tests/Feature/AdminAccessTest.php`.
