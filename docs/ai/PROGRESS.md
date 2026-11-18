# Progress

Update this file at the end of every work session, before committing.

## Current focus
Phase 1 data layer implemented and tested; moving on to next checklist item when ready.

## Checklist
- [x] Laravel scaffolded
- [x] Git initialised and first commit
- [x] Docker setup (Dockerfile, dev compose, /up health check)
- [x] Auth and roles (starter kit, role enum, seeders, tests)
- [x] Database schema and models (companies, professionals, listings, verification checks, audit log, inquiries)
- [ ] Listings: public list with filters, detail page, WhatsApp button
- [ ] Verification workflow: verifier dashboard, checklist, badge display
- [ ] Partner profiles and inquiry forms
- [ ] Static/content pages: fees, verification process, blog
- [ ] GitHub Actions CI (tests against Postgres service container)
- [ ] Production deploy files (docker-compose.prod.yml, Caddyfile, deploy workflow, backup script)

## Handoff notes
- Phase 1 data layer complete: 8 migrations, 9 backed enums, 8 models, audit hooks, factories, DemoSeeder.
- Last session fixed failing Phase 1 tests: morphMany now passes explicit relation name (`'auditable'`);
  foreign keys use explicit table strings (Laravel guessed wrong for `*_by` columns); AuditLog casts via
  `casts()` method; factories default required FKs; verification checks use distinct check keys.
- Full suite: 75 passed (183 assertions), Pint clean, `migrate:fresh --seed` OK on dev Postgres.

## Next
- Listings: public list with filters, detail page, WhatsApp button.
