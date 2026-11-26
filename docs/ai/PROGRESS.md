# Progress

Update this file at the end of every work session, before committing.

## Current focus
Brand and UI shell on branch `brand-ui`, task 3 (avatar, footer, new public pages) in progress after tasks 1–2 completed and pushed (checkpoint 1 done, browser check pending). Task 3 implementation in progress; checkpoint 2 checks pending.

## Checklist
- [x] Laravel scaffolded
- [x] Git initialised and first commit
- [x] Docker setup (Dockerfile, dev compose, /up health check)
- [x] Auth and roles (starter kit, role enum, seeders, tests)
- [x] Database schema and models (companies, professionals, listings, verification checks, audit log, inquiries)
- [x] Listings: public list with filters, detail page, WhatsApp button
- [x] Verification workflow: verifier dashboard, checklist, badge display
- [x] Partner profiles and inquiry forms
- [x] Static/content pages: fees, verification process, blog
- [x] GitHub Actions CI (tests against Postgres service container)
- [x] Production deploy files (docker-compose.prod.yml, Caddyfile, deploy workflow, backup script)
- [x] UI layouts: public layout, mobile navigation, footer
- [x] Auth and profile restyle with shared Blade components
- [x] UI foundation: landing page and role dashboards (README last)
- [x] Inquiry management: list, detail, status transitions with audit
- [x] Inquiry email notifications: queued recipient mail, spam protection
- [x] Professional consultations: public verified profiles and consultation requests
- [x] Pre-launch hardening: seeders, email verification, passwords, error pages, legal, env hygiene (stage 2 fixes applied; infra items open)
- [ ] Brand and UI shell (U1-U3)

Checkpoint (b) verified: user_id on inquiries, policy split, 11 inquiry tests, isolated test database.

## Checkpoint (c): Public layout
- Public pages use `<x-public-layout>` / `layouts/public.blade.php`, with one `@livewireStyles`/`@livewireScripts` pair; the footer is brand/tagline only.
- Mobile nav uses Alpine; the fixed header is visible by default with an optional persisted auto-hide after 2250ms idle once scrolled.
- Bunny fonts removed; Node 22 build emits 0 `.woff` assets.
- Full suite after this checkpoint: 105 passed.

## Stage 2: Authenticated UI
- Added the `brand` palette and repaired `input`, `button`, `card`, `alert`, `page-header`.
- Restyled the authenticated nav, app/guest layouts, six auth pages, profile page and partials; Volt logic untouched.
- Auth/profile tests assert `config('app.name')` and `wire:model` bindings; guests redirect from `/dashboard` and `/profile`.
- Full suite after this stage: 106 passed.

## Stage 3: Landing page
- `welcome.blade.php` is server-rendered with `<x-public-layout>`: hero, verification steps, three service arms, trust points, featured listings, partner placeholders.
- `layouts/public.blade.php` accepts an optional `head` slot for meta/OG tags; no external fonts or images.
- Featured section queries published + verified listings, eager loads images/company, and shows an empty state.
- `LandingPageTest` covers render, CTAs, verified-only filtering and the empty state; 109 passed overall.

## Stage 4: Role dashboards
- One `/dashboard` route now uses `DashboardController` and `App\Dashboard\DashboardData`; the service switches on role and returns counts plus recent rows, and aborts 403 when no role is assigned.
- super_admin gets company/listing/inquiry counts, the verification breakdown and the five latest audit entries; verifier gets the waiting counts and five oldest listings; company, professional and customer get scoped status counts and their five most recent inquiries.
- Company inquiries are scoped through `listing.company_id`; professional through `professional_id`; customer through `user_id`. The Blade view contains no queries and renders only the service payload.
- Empty states cover every list; only existing routes are linked, and unfinished actions are disabled "Coming soon" items. `DashboardDataTest` and `DashboardTest` cover all roles and the second-company isolation case.

## Inquiry flow fix (branch `inquiry-flow`)
- Public listing and inquiry routes now use explicit `{listing:slug}` binding: `listings.show`, `inquiries.create` and `inquiries.store`. `ListingController@show` accepts a `Listing` and aborts 404 unless it is published.
- `InquiryController` keeps a single published guard, assigns `user_id` only from the authenticated user, and redirects to the listing with a success `status`.
- The inquiry form uses the shared `x-input`, `x-button` and `x-input-error` components, shows for authenticated users.
- Published listing pages show a 44px "Request an inspection or consultation" action next to WhatsApp; `x-button` now renders an anchor when `href` is supplied.
- `InquiryTest` covers published/draft create pages, the detail-page link, guest submissions, authenticated customer ownership plus `DashboardData` visibility, and ignoring a posted `user_id`.

## Listing detail actions and README (branch `listing-buttons-readme`)
- The published listing detail page places "Inquire" and "WhatsApp" directly below the company name in one left-aligned, wrapping row. Both keep the 44px tap target, and `grow` makes each action fill the row when they wrap.
- "Inquire" carries `aria-label="Request an inspection or consultation"` so the shorter visible label does not reduce the accessible name.
- `README.md` now documents the project, Phase 1 scope and status, the verified local setup, demo accounts, tests, Pint, asset builds, troubleshooting, project structure, environment variables and deployment files.
- README setup commands were run on this checkout; clone and `cp .env.example .env` were not run because the repository and local `.env` already existed.

### Handoff notes
- All UI-foundation work, including the README rewrite, is complete and awaiting review.
- Root cause of the earlier inquiry bug: listing routes bound `{listing}` by `id`, so slug URLs caused `SQLSTATE[22P02] invalid input syntax for type bigint`; explicit `{listing:slug}` binding fixes both the detail and inquiry routes.
- Full suite: 130 passed (410 assertions); Node 22 build clean with 0 `.woff` assets.
- Pint passes for every file touched by the recent work; repository-wide `pint --test` still reports 7 pre-existing style issues outside these changes.
- Caddy uses `tls internal` and serves `https://localhost` (see DECISIONS).
- `public/hot` is absent, so `public/build` assets are what the browser loads.

## Inquiry management (branch `inquiry-management`)
- `inquiries.show` and `inquiries.status.update` authorize through `InquiryPolicy` via `$this->authorize('view'|'update', $inquiry)`; `App\Http\Controllers\Controller` now uses `AuthorizesRequests` (the previous empty base caused `Call to undefined method App\Http\Controllers\InquiryController::authorize()`).
- List scoping stays in `Inquiry::viewableBy()` and the `whereViewableBy()` query scope; `InquiryController@index` delegates to it, so the list contains exactly the inquiries the policy allows to be viewed.
- `test_inquiry_list_scope_agrees_with_policy_view` proves the list scope and policy agree for super_admin, company, professional, customer and verifier; all existing denial tests still pass and none were removed or weakened.
- Removed the unrequested `database/seeders/TownSeeder.php` and its `DatabaseSeeder` reference; `DatabaseSeederTest` predates this branch (`faa67b2`).
- `app/Models/User.php` is unchanged from `main`: the `booted()` hook only fills a null `role_id` with Customer; registration assigns the role explicitly.
- Full suite: 157 passed (518 assertions); `pint --test` passes 109 files.

## Inquiry email notifications (branch `inquiry-notifications`)
- Status controls on `inquiries.show` are now real forms: `method="POST"`, `@csrf`, `@method('PATCH')`, a hidden `status` field and a submit button. The previous `method="PATCH"` form made browsers fall back to GET, which produced `The GET method is not supported for route inquiries/{id}/status`.
- Valid transitions live in `InquiryStatus::allowedTransitions()` and both the show page and `updateStatus` use them; the page offers only the transitions available from the current status.
- Added the queued `App\Notifications\InquiryReceived` (mail only, `ShouldQueue`, `afterCommit`) and `App\Services\InquiryNotifier`. Recipient precedence is the professional's user email for professional inquiries, then the listing company email; a missing email logs a warning and skips silently.
- The controller wraps the notifier call in `try/catch (Throwable)` and logs a warning, so a mail or queue failure never breaks the inquiry submission.
- Spam protection: `throttle:5,1` on `inquiries.store` with a friendly `resources/views/errors/429.blade.php`, and a hidden `website` honeypot that silently discards filled submissions before validation.
- `docker-compose.yml` and `docker-compose.prod.yml` now run a `queue` service (`php artisan queue:work --tries=3 --max-time=3600`, `restart: unless-stopped`) with a worker-specific healthcheck. Prod `QUEUE_CONNECTION` changed from `redis` (no redis service) to `database`.
- README documents the mail/queue environment variables, the worker command and a free-tier Brevo SMTP option without secrets.
- Bug 2 proof: a real HTTP inquiry queued one job; the compose `queue` service delivered it, and `storage/logs/laravel.log` shows `To: hello@acme-realty.test`, the listing title, type, first name, message excerpt and the `inquiries.show` button, with zero occurrences of the submitter's phone or email.
- Full suite: 167 passed (590 assertions); `pint --test` passes 112 files; Node 22 build passes (CSS 39.55 kB, JS 0.00 kB from an empty `app.js`); both compose files validate.

### Handoff notes (inquiry-notifications)
- `docker-compose.yml` runs `queue` alongside `app`, `db` and `caddy`; the queue container is healthy and processes jobs automatically.
- Local mail stays `MAIL_MAILER=log`; switch to SMTP through environment variables only.
- Prod still sets `CACHE_STORE=redis` and `SESSION_DRIVER=redis` with no redis service (pre-existing, outside this item); only `QUEUE_CONNECTION` was moved to `database` because the worker needs a real backend.
- Tests use `Notification::fake()`; the full suite and Pint were run after the final code change.

## Professional consultations (branch `professional-consultations`)
- Added a nullable unique `professionals.slug`, backfilled from each user's name with collision suffixes, and auto-generated on `Professional::creating`. Public professional routes bind `{professional:slug}`; null or unknown slugs 404 instead of exposing ids.
- `ProfessionalController@index/show` lists only professionals with `credentials_verified_at`, supports `?type=`, and links the company and credentials. The profile shows a 44px "Request consultation" action and never renders the professional's email or phone.
- `InquiryController@createForProfessional/storeForProfessional` reuse the honeypot, shared validation and `InquiryNotifier`; `type` is forced to `consultation`, `listing_id` null, `professional_id` from the route and `user_id` only from the authenticated user. The listing form keeps its type selector; both forms share `inquiries/partials/fields.blade.php`.
- `InquiryReceived` prints `Professional: <name>` for professional inquiries instead of a listing line, and still excludes the submitter's phone and email.
- Public nav/footer link to "Partners"; company profiles list their verified professionals only. `DemoSeeder` seeds Ada Okafor (verified lawyer at Acme Realty), Tunde Bello (verified architect) and Ngozi Eze (unverified engineer).
- Tests: `ProfessionalProfileTest`, `ProfessionalInquiryTest` and `ProfessionalSlugMigrationTest`; full suite 191 passed (713 assertions), `pint --test` passes 117 files, Node 22 build clean.
- `migrate:fresh --seed` and a path-scoped rollback/re-migrate were run on the dev database; the migration removed and re-added `slug` and regenerated all three seeded slugs.
- Targeted rollback note: `migrate:rollback --path=<single migration>` still lists the other migrations in the same batch as "Migration not found"; they stay applied and only the targeted migration rolls back.

### Handoff notes (professional-consultations)
- All requested behaviour is implemented and verified on this branch; nothing was started beyond the current focus.
- The only WhatsApp action on a professional profile uses the platform number from `config('services.whatsapp.number')`; users without a configured number see only the consultation action.
- The existing `DatabaseMigrationsTest` (`--step 8`) now also exercises the slug migration down/up and still passes. 

## Pre-launch hardening stage 2 (branch `prelaunch-app-fixes`)
- Environment-guarded demo seeders: `DatabaseSeeder` seeds demo users/professionals/listings only in the `local` or `testing` environments; other environments run `RoleSeeder` only. Added `php artisan app:create-admin` (interactive or `--force`, refuses non-interactive use without `--force`).
- Email verification: `App\Models\User` implements `MustVerifyEmail`; `/profile` protected with `auth, verified` (dashboard and inquiries already were). Unverified users redirect to the verification notice; new registrations send the verification notification via the `Registered` event.
- Password policy: `AppServiceProvider` sets `Password::defaults()` (min 10, letters, mixed case, numbers, `uncompromised()` only in production).
- Error pages and crawling: styled `errors/403|404|419|500|503` views, server errors standalone HTML, `noindex, nofollow` in app/guest layouts, `GET /robots.txt` and `GET /sitemap.xml` (cached 1 hour).
- Legal pages and consent: `/privacy`, `/terms`, `/complaints` with draft notices, footer links, inquiry consent lines linking to `/privacy`.
- Environment hygiene: `.env.example` (`APP_NAME="Verifyhaus Afrika"`, placeholder `MAIL_FROM_ADDRESS`, http-friendly defaults), added `.env.production.example`, added `php artisan prelaunch:check`.
- Full suite: 226 passed (816 assertions); Pint passes 125 files; Node 22 build passes; `git fsck --full --no-reflogs` clean (no errors); `git status --short` empty.
- Commits on `prelaunch-app-fixes`: `f9c208f`, `db85ed1`, `5b89dab`, `635747d`, `5ff3fd1`, `3601814`.
- Handoff: infrastructure items stay open (proxy topology, trusted proxies, deploy workflow, backups, 2FA, retention); owner decisions required before launch. Stop here and wait for decisions.

## Email verification auto-detection (branch `verify-email-polling`)
- Updated AGENTS.md reporting rules: all npm/node commands run in Docker (`node:22-alpine3.24`) with `--network host` on ECONNRESET; `SESSION_SECURE_COOKIE` belongs in `.env.production.example` only. Commit `042b18e`.
- Removed `SESSION_SECURE_COOKIE=true` from `.env.example` (and local `.env`) so it works over `http://localhost:8000`; kept it in `.env.production.example`. Commit `472f814`.
- `resources/views/livewire/pages/auth/verify-email.blade.php`: the page wrapper now has `wire:poll.5s.visible="checkVerified"`. `checkVerified()` re-reads the authenticated user with `fresh()` and redirects to the dashboard via `redirectIntended()` only once `hasVerifiedEmail()` is true; the same guard runs on mount so an already-verified visitor is redirected immediately. The status line uses `aria-live="polite"` and changes from "Waiting for confirmation..." to "Email verified, redirecting..." just before the redirect.
- Resend and log out are unchanged; the poll only reads state — the signed link still does the verifying. `verification.verify` keeps its `throttle:6,1` middleware.
- Tests added to `EmailVerificationTest`: unverified poll does not redirect; database-verified user poll redirects; verified visitor redirected (Volt and HTTP); rendered page contains the wire:poll attribute and status region; resend sends the notification; signed-link throttle is unchanged.
- Full suite: 233 passed (833 assertions); Pint passes 125 files; Node 22 build via Docker command passes; `git fsck --full --no-reflogs` clean; `git status --short` empty.
- Handoff: implementation and checks complete; no further items started. Stop and wait for review.

## Verification resend throttle (branch `verify-resend-throttle`)
- `sendVerification()` in `resources/views/livewire/pages/auth/verify-email.blade.php` now uses `RateLimiter` with a per-user key (`verify-email-resend:<user id>`), allowing 3 sends per 600 seconds. The signed verification link still does the verifying and its `throttle:6,1` route middleware is unchanged.
- When the limit is hit, no notification is sent; a warning explains "Too many verification emails. Please try again in <seconds> seconds." A successful resend shows the short "Verification link sent." confirmation via a rendered alert.
- The resend button carries `wire:loading.attr="disabled"` so it cannot be clicked again while the Livewire request runs. Polling and verified-user redirects are untouched.
- Tests added to `EmailVerificationTest`: first three resends send, fourth is limited and shows the message, the limit is isolated per user, and it resets after 11 minutes via `travel()`. Existing verification/polling tests still pass.
- Full suite: 237 passed (845 assertions); Pint passes 125 files; `git fsck --full --no-reflogs` clean; `git status --short` empty.
- Handoff: implementation and checks complete; no further items started. Stop and wait for review.

## Production infrastructure, checkpoint A (branch `infra-prod`)
- One multi-stage `Dockerfile`, two production images: `app` (PHP-FPM as `www-data`, `composer install --no-dev --optimize-autoloader` in a build stage, no `.env` in the image) and `caddy` (`public/` baked in, no shared volume). `docker-compose.prod.yml` runs app, queue, db and caddy from those two images; only Caddy publishes 80/443 and no Redis is used (sessions, cache and queue are on Postgres).
- Networks and proxies: the compose network is fixed at `172.28.0.0/24` and Caddy is pinned to `172.28.0.10`; `TRUSTED_PROXIES=172.28.0.10` names that single address instead of the whole Docker range.
- Live measurement through `php_fastcgi` (throwaway `public/ip.php` copied into the running containers, never committed):
  - through Caddy: `REMOTE_ADDR=172.28.0.1`, `X-Forwarded-For=172.28.0.1`, `request_ip=172.28.0.1` (172.28.0.1 is the Docker gateway that Caddy sees as the client for a host request);
  - client sends `X-Forwarded-For: 203.0.113.9` to Caddy: the header is replaced, all three values stay `172.28.0.1`;
  - untrusted FastCGI proxy at `172.28.0.5` forwarding `X-Forwarded-For: 203.0.113.9`: `REMOTE_ADDR=127.0.0.1`, `request_ip=127.0.0.1`, so the spoofed header is ignored;
  - the host cannot reach the app container at all (`localhost:9000` refused, `172.28.0.3:9000` times out), so FPM has no public HTTP path.
- `TrustedProxiesTest` covers the wildcard/empty guard, a spoofed header from an untrusted address, a real header from a trusted address, and per-client inquiry throttle buckets. `prelaunch:check` now also fails on `TRUSTED_PROXIES=*`/empty, a missing `APP_KEY`, and `MAIL_MAILER` of `log`/`array`.
- Found only by running the real stack, all fixed: an empty `listen.allowed_clients` in the FPM pool dropped every FastCGI connection; the `@php path *.php` Caddy matcher never reached Laravel (404 on `/` and `/up`); `apk del .build-deps` also removed the runtime libraries so `intl`, `gd`, `zip` and `pdo_pgsql` failed to load; `intl` needs `-j1`; `opcache` is already compiled into the official image.
- Checks: 244 passed (861 assertions), Pint 126 files, `docker compose -f docker-compose.prod.yml config --quiet` exit 0, `git fsck --full --no-reflogs` reports dangling objects only, `git status --short` empty.
- Handoff: checkpoint A committed and pushed (`10e95e8`, `38e88ad`, `b2b8179`). Checkpoint B (deploy workflow) not started; the rehearsal stack was taken down, the dev stack is running.

## Production infrastructure, checkpoint A.1 (branch `infra-prod`)
- Caddy now sets security headers (`Strict-Transport-Security max-age=31536000`, `X-Content-Type-Options nosniff`, `Referrer-Policy strict-origin-when-cross-origin`, `X-Frame-Options SAMEORIGIN`, minimal `Permissions-Policy`), removes `Server`/`X-Powered-By`/`Via` headers, denies dotfiles and non-webroot paths (`/.env`, `/.git/config`, `/storage/*`, `/vendor/*` all return 404) and caps bodies at 2 MB.
- Hashed `/build/*` assets get `Cache-Control: public, max-age=31536000, immutable`; all other responses get `no-store`; Laravel's own `no-cache, private` duplicate header was fixed with the `>` overwrite operator.
- Production `LOG_CHANNEL=stderr` (also set in the prod compose), so `docker compose logs` carries it and no log volume is needed.
- Live proofs: `/` and `/build/assets/app-D9Uh1YX6.css` HEAD returns the expected headers; denied paths all 404; a 3 MB POST returns HTTP 413; HSTS and `max_size` confirmed via `caddy adapt`.
- Inquiry submitted end to end (seeded demo listing, throttled POST): inquiry row written, notification processed by the queue, zero personal-data markers in `docker compose logs` app/queue at `LOG_LEVEL=warning`.
- Throttle proof with two source addresses on the Docker network: client `172.28.0.20` → 404,404,404,404,404,429; client `172.28.0.21` → 404,404 (own bucket).
- Images contain no `.env` and no `.git`; app runs as `www-data` and can write `storage/` and `bootstrap/cache/`; compose reads `APP_KEY`/`DB_PASSWORD` from env_file with no defaults.
- Handoff: A.1 hardening done, checks green, next is checkpoint B (deploy workflow).

## Brand and UI shell, tasks 1-3 (branch `brand-ui`)
- Task 1 (brand rename): `APP_NAME="Verifyhaus Afrika"` in `.env.example` and `.env.production.example` (local `.env` updated likewise); README, `docs/ai/PRELAUNCH.md` and this file updated; the prelaunch check production config now uses `Verifyhaus Afrika`. Views, mail, meta/OG tags and titles already sourced the name from `config('app.name')`, so no hardcoded brand strings remain in tracked files; kept identifiers (`busy-hub` paths, `busyhub` user/database, `@busyhub.test` seeded emails).
- Task 2 (unified navbar): one `<x-site-nav>` anonymous Blade component renders the fixed header used by `layouts/public`, `layouts/app` and `layouts/guest`; shared links for everyone (Listings, Partners, Fees, How verification works, Blog), auth-aware Dashboard (verified only) + Inquiries (allowed roles), avatar menu with logout via the new `layout.logout` Volt component mounted only in the `@auth` branch; the public auto-hide header (toggle + `public-nav-autohide`) was removed; later refinements (compressed mobile spacing, in-line login actions, outside-click menu collapse, logo → home) applied after the task-2 checkpoint.
- Task 3 (avatar, footer, pages): `<x-avatar>` component added (deterministic palette colour from the user id, uppercase initial from name with email fallback, `aria-hidden` letter + `role="img"`/`aria-label` label, contrast ≥ 4.5:1 against white verified in `AvatarTest`); one `<x-site-footer>` component renders the footer with `mt-auto` and the copyright line last, used by all three layouts; new public pages `/about`, `/mission`, `/vision`, `/services`, `/contact`, `/feedback` added with the draft notice; sitemap updated; `CONTACT_EMAIL` added to config/services, `.env.example` and `.env.production.example`.

Checkpoint 1 (tasks 1-2) checks: full suite 249 passed (877 assertions), Pint PASS 127 files, Node 22 build OK, `docker compose -f docker-compose.prod.yml config --quiet` exit 0, `git fsck --full --no-reflogs` exit 0, `git status --short` empty; task 1 commit `0f8476d`, task 2 commit `aefd96f`.

Checkpoint 2 (task 3) checks: full suite **263 passed (959 assertions)** (0 failed; +14 tests: AvatarTest 5, SiteFooterTest 3, StaticContentTest +6 new pages), Pint PASS 129 files, Node 22 build OK, `docker compose -f docker-compose.prod.yml config --quiet` exit 0, `git fsck --full --no-reflogs` exit 0, `git status --short` empty.

- Handoff: task 3 implementation complete; checkpoint 2 checks passed; commit task-3 pieces, push, then stop for the browser check.
