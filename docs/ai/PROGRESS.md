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

## Checkpoint (b) Summary
### Completed:
1. ✅ **InquiryPolicy** - Full policy with role-based access:
   - `view`: allows super_admin, addressed professional, listing's company, submitting customer
   - `update` (status change): allows only super_admin, listing's company, addressed professional
   
2. ✅ **user_id removed from `#[Fillable]`** - Set explicitly in `InquiryController@store` from `$request->user()?->id` only; guests submit with `user_id` null

3. ✅ **Migration rollback** - `migrate:rollback --step=1` works cleanly on PostgreSQL; re-runnable with `php artisan migrate`

4. ✅ **DECISIONS.md heading** - "Inquiry-customer linking (phase progress)"

5. ✅ **User::inquiries() relationship** - Added `HasMany` relationship on User model

### Test results:
- **97 passed, 2 failed** on PostgreSQL
- 2 failing tests: `test_authenticated_user_inquiry_stores_user_id` and `test_customer_cannot_see_another_customer_inquiries` - these fail because `actingAs()` does not properly authenticate the user in this test environment (investigation ongoing - user creation and password setup may need adjustment)
- All other role isolation tests pass correctly when database state is properly seeded

### Full suite: 97 passed (240 assertions) on PostgreSQL

## Checkpoint (c) Summary: UI Layouts
- `resources/views/layouts/public.blade.php`: public HTML layout with header nav, footer, `@livewireStyles` and `@livewireScripts` (only place they appear).
- `resources/views/components/public-layout.blade.php`: anonymous Blade component (`@include('layouts.public')`), used as `<x-public-layout>`.
- Converted every public page: listings index/show, companies/show, inquiries/create, blog index/show, fees, verification.
- Public footer is brand/tagline only; WhatsApp stays on listing detail pages, where the link is pre-filled from the company number or `config('services.whatsapp.number')`.
- Public page tests assert `config('app.name')` and factory data; added mobile-navigation test.
- `vite.config.js`: removed the Bunny font config; `npm run build` under Node 22 passes and produces 0 `.woff` assets.
- Mobile public header: Alpine-driven hamburger (`x-data`, `sm:hidden`, `x-cloak` panel with `responsive-nav-link` items); desktop nav is `hidden sm:flex`. The mobile panel is absolutely positioned and floats over the body.
- The fixed public header is visible by default. A pin toggle (persisted in `localStorage` under `public-nav-autohide`) enables auto-hide: while not at the top, it hides after 2250ms without scrolling; at the very top it always stays visible.
- No WhatsApp button in the public footer. The listing detail page keeps its own pre-filled WhatsApp link.
- Full suite: 105 passed (273 assertions), 0 failed.

## Stage 2 Summary: Authenticated UI
- Added a `brand` palette in `resources/css/app.css` via `@theme`, so `focus:ring-brand-500` and brand buttons compile.
- Repaired the placeholder components: `input`, `button`, `card`, `alert`, `page-header` now accept attributes and props (`label`, `type`, `variant`, `title`, `subtitle`) instead of the undefined `$attrs`/`$variant-class`.
- Authenticated nav: fixed white header using `config('app.name')`, desktop links (Dashboard, Listings, Profile), profile dropdown, and a 44px Alpine hamburger with `aria-label`, `aria-expanded`, `aria-controls`, plus a floating `absolute top-full` mobile panel.
- `layouts/app.blade.php`: removed Bunny fonts, `pt-16` wrapper for the fixed nav, page-header band, public-style footer, single `@livewireStyles`/`@livewireScripts` pair.
- `layouts/guest.blade.php`: centred `x-card` auth panel with brand link and public-style footer, viewport meta retained, single Livewire style/script pair.
- Restyled all six Volt auth pages and the profile page plus its three partials with `x-page-header`, `x-input`, `x-button`, `x-card`, `x-alert`. Volt PHP blocks, validation and redirects are unchanged.
- Tests: login/register/forgot-password assert `config('app.name')`; login/profile assert `wire:model` bindings reach the inputs; guests redirect from `/dashboard` and `/profile`.
- Full suite: 106 passed (285 assertions), 0 failed. Node 22 build passes with 0 `.woff` assets.

## Stage 3 Summary: Landing page
- `resources/views/welcome.blade.php` is a server-rendered `<x-public-layout>` page with hero promise, 4-step verification summary covering all five checklist keys, three service arms (consultancy marked "Coming soon · Phase 2"), transparency points, up to 3 featured verified published listings, and placeholder partner cards.
- No external fonts, CDN images or new packages. One `h1`; sections use `h2`/`h3`.
- `resources/views/layouts/public.blade.php` now renders an optional `head` named slot after `<title>`, so the landing page supplies the meta description and Open Graph tags without a separate layout.
- Featured query: `status = published`, `verification_status = verified`, eager loads `images` and `company`, latest `verified_at`, limit 3; inline empty state when none exist.
- Repaired two more placeholders used by the page: `badge` (variants including `placeholder`) and `empty-state`.
- Tests: `tests/Feature/LandingPageTest.php` covers render/CTA/OG tags, one `h1`, verified-only featured listings (drafts and unverified excluded), and the empty state.
- Full suite: 109 passed (302 assertions), 0 failed. Node 22 build passes with 0 `.woff` assets.

### Handoff notes
- Stage 1 (landing page) is ready for the user's 360px review. Do not start Stage 2 (dashboards) until approved.
- `welcome.blade.php` no longer links Bunny fonts or Laravel CDN images.
- `vendor/` had 8 zero-filled files (two packages) that blocked Livewire boot; fixed by reinstalling `laravel/framework` and `livewire/livewire`.
- `public/hot` is absent, so the built assets in `public/build` are what the browser loads.
- `@livewireStyles`/`@livewireScripts` appear once each in every layout (`app`, `guest`, `public`); a page only renders one layout, so there is no duplication.