# Progress

Update this file at the end of every work session, before committing.

## Current focus
UI foundation: landing page and role dashboards (README last). Stage 1 landing page is implemented and all checks pass; awaiting the user's 360px review before Stage 2. Dashboards not started.

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
- [ ] UI foundation: landing page and role dashboards (README last)

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

### Handoff notes
- Stage 1 landing page awaits the user's 360px review; do not start dashboards.
- Full suite: 109 passed (302 assertions); Node 22 build clean with 0 `.woff` assets.
- Caddy uses `tls internal` and serves `https://localhost` (see DECISIONS).
- `public/hot` is absent, so `public/build` assets are what the browser loads.
