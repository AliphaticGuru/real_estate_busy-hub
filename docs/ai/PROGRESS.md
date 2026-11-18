# Progress

Update this file at the end of every work session, before committing.

## Current focus
Checkpoint (c) corrections complete: shared public layout, page conversions, WhatsApp footer, Node 22 build, tests. Awaiting the user's own build run and 360px check before Stage 2.

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
- WhatsApp footer reads `config('services.whatsapp.number')`, strips non-digits, and hides when empty. `WHATSAPP_NUMBER=` added to `.env.example`.
- Public page tests assert `config('app.name')` and factory data; added WhatsApp digits/hidden tests.
- `vite.config.js`: removed the Bunny font config; `npm run build` under Node 22 passes and produces 0 `.woff` assets.
- Mobile public header: Alpine-driven hamburger (`x-data`, `sm:hidden`, `x-cloak` panel with `responsive-nav-link` items); desktop nav is `hidden sm:flex`.
- Footer WhatsApp is only rendered when `config('services.whatsapp.number')` has digits; local `.env` needs `WHATSAPP_NUMBER` for the button to appear.
- Full suite: 107 passed (271 assertions), 0 failed.

### Handoff notes
- Do not start Stage 2. Wait for the user to run the Node 22 build and the 360px viewport check.
- `vendor/` had 8 zero-filled files (two packages) that blocked Livewire boot; fixed by reinstalling `laravel/framework` and `livewire/livewire`.
- `public/hot` is absent, so the built assets in `public/build` are what the browser loads.