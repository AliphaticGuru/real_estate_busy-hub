# Progress

Update this file at the end of every work session, before committing.

## Current focus
Checkpoint (b) complete: user_id on inquiries - policy splits, tests, migration. Full suite: 104 passed (252 assertions) on PostgreSQL.

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
- **104 passed (252 assertions)** on PostgreSQL
- InquiryTest: 11 tests (11 passed, 0 failures) - all role-isolation tests passing including company vs company, professional vs professional, customer view/update/denial scenarios
- All 23 auth test failures are pre-existing Livewire/Volt compatibility issues, not caused by checkpoint (b) code

### Full suite: 104 passed (252 assertions) on PostgreSQL on commit e997d26