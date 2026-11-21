# Progress

Update this file at the end of every work session, before committing.

## Current focus
Inquiry email notifications: queued recipient mail with spam protection (branch `inquiry-notifications`). Implementation and tests are complete and awaiting review.

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
