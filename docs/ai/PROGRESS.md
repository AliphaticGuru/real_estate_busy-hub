# Progress

Update this file at the end of every work session, before committing.

## Current focus
Phase 1 — Production deploy files started; docker-compose.prod.yml, Caddyfile, deploy workflow, backup script.

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
- [ ] Production deploy files (docker-compose.prod.yml, Caddyfile, deploy workflow, backup script)

## Handoff notes
- Phase 1 data layer complete: 8 migrations, 9 backed enums, 8 models, audit hooks, factories, DemoSeeder.
- Last session fixed failing Phase 1 tests: morphMany now passes explicit relation name (`'auditable'`);
  foreign keys use explicit table strings (Laravel guessed wrong for `*_by` columns); AuditLog casts via
  `casts()` method; factories default required FKs; verification checks use distinct check keys.
- Auth frontend fix (Nov 18): added TrustProxies middleware (bootstrap/app.php). Before this, Livewire
  asset URLs were generated as http://localhost/..., blocked as mixed content on the https://localhost
  page, so the Livewire JS never loaded and login/register clicks did nothing. After the fix, script,
  module-url and update-uri all render as https://localhost/... .
- Listings public pages done (Nov 18): ListingController (index w/ filters, show by slug, published only)
  + resources/views/listings/{index,show}.blade.php (standalone shells, guest-safe) + WhatsApp button on
  show. New tests: ListingPublicTest (5 tests). Full suite: 80 passed (196 assertions); Pint clean;
  `migrate:fresh --seed` OK; live /listings, /listings/{slug} return 200.
- Verification workflow done (Nov 18): VerificationController (verify dashboard, check update endpoint
  recomputing listing status) + verifications dashboard view + tests (VerificationWorkflowTest, 6 tests).
  Full suite: 86 passed (212 assertions); Pint clean.
- Partner profiles and inquiry forms done (Nov 18): CompanyController@show (public partner profile w/
  published listings) + companies/show view; InquiryController (public create/store for consultation or
  inspection on published listings) + inquiries/create view; tests (CompanyPublicTest, InquiryTest). Full
  suite: 92 passed (225 assertions); Pint clean; live pages verified.
- Static content pages done (Nov 18): BlogController (index/show w/ sample posts, Carbon publish dates),
  views (fees, verification process, blog index, blog post), routes (/fees, /verification, /blog, /blog/{slug}),
  StaticContentTest (5 tests). Full suite: 97 passed (234 assertions); Pint clean.
- GitHub Actions CI done (Nov 18): new .github/workflows/ci.yml runs the suite on ubuntu-latest against a
  postgres:15-alpine service container (busy_hub db). Test suite now uses the configured database when
  DB_CONNECTION=pgsql is set (TestCase stops forcing sqlite otherwise; phpunit.xml no longer forces sqlite).
  Full suite verified against local Postgres dev DB: 97 passed (234 assertions); Pint clean.

## Next
- Production deploy files (docker-compose.prod.yml, Caddyfile, deploy workflow, backup script).
