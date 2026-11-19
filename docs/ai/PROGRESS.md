# Progress

Update this file at the end of every work session, before committing.

## Current focus
UI foundation: landing page and role dashboards (README last). Stage 2 role dashboards are implemented and all checks pass; the README is rewritten with verified setup and deployment notes. Awaiting user review.

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

### Handoff notes
- Stage 2 dashboards are complete and awaiting review; the README setup, test, asset and deployment instructions were rewritten and verified.
- Full suite: 126 passed (397 assertions); Node 22 build clean with 0 `.woff` assets.
- Pint passes for all Stage 2 files; repository-wide `pint --test` still reports 9 pre-existing style issues outside this docs change.
- Caddy uses `tls internal` and serves `https://localhost` (see DECISIONS).
- `public/hot` is absent, so `public/build` assets are what the browser loads.
