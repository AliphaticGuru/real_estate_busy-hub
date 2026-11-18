# Decisions (append-only)
Update this file to match the next unchecked task in PROGRESS.md

Current stage: Database schema and models (auth and roles are done and tested).

## Auth and roles (done)
- Roles: single backed-enum `role` column on users plus `company_id`; no Spatie package for now.
- Policies: one policy class per model; Gate::define only for non-model abilities.
- Auth scope: starter kit + roles + seeders + tests first; policies added with each model.

## Authorization
- Policies: one policy class per model; Gate::define only for non-model abilities
  (e.g. access-verifier-dashboard).
- Company users can only manage their own company's records; cross-company access must fail and be tested.
- Only verifier or super_admin can change a listing's verification status.
- Customers can only see published listings; inquiries are visible only to the target
  company/professional and super_admin.

## Auth frontend behind Caddy (done, Nov 18)
- Added TrustProxies middleware (bootstrap/app.php) so Laravel trusts Caddy's proxy headers; asset and
  Livewire endpoint URLs now render as https://localhost/... instead of http://localhost/... (mixed content
  blocked the Livewire script on the https login page, so login/register clicks silently did nothing).

## Data layer (current stage, done)
- Single database scoped by company_id; no multi-tenancy package.
- Prices are stored as integers in kobo, never floats.

## Phase 1 data layer completion (2026-11-17)
- Schema implemented: companies, professionals, listings, listing_images, verification_checks,
  audit_logs, inquiries + users.company_id.
- Laravel's `foreignId()->constrained()` guessed wrong foreign tables for `*_by` columns
  (e.g. `created_bies`); all FKs now pass explicit table names.
- Laravel framework here requires an explicit morph relation name for `morphMany`
  (e.g. `$this->morphMany(AuditLog::class, 'auditable')`); relations use `auditable_type`/`auditable_id`.
- Audit log is append-only via overridden save/delete; status-change hooks on Listing/VerificationCheck
  write audit entries with jsonb old/new values.
- DemoSeeder creates Acme Realty with one verified/published, one draft/pending, one in_review/published listing.
- Statuses and types are backed PHP enums cast on the models (listing status, verification
  status, check status, professional type, inquiry type and status).
- Verification checklist keys are a PHP enum (e.g. title_document, survey_plan,
  ownership_proof, site_inspection, no_encumbrance) so the checklist stays consistent across code and tests.
- Listing images store paths only; files live in S3-compatible storage (Cloudflare R2), not the container.
- audit_logs is append-only (no update or delete through the model) and records verification
  status changes and verification check updates, using jsonb for old and new values.
- Every foreign key gets a deliberate on-delete rule and an index.
- Migrations must run fresh and roll back cleanly on PostgreSQL.
- Phase 1 only: no chat, payments, or app features in the schema.

## Process
- Plan first, implement after approval; one PROGRESS.md item at a time.
- Tests must pass before an item is marked done.

## Public listings (phase progress)
- Public listing pages (`/listings`, `/listings/{slug}`) are guest-accessible and only serve listings
  whose status is `published`; drafts are not publicly accessible.

## Verification workflow (phase progress)
- Verifier dashboard (`/verify`) is auth-gated and limited to super_admin/verifier roles; customers get 403.
- Verification checks are submitted via `POST /verify/listings/{listing}` (`check_key` + `status`);
  submitting a check recomputes the listing status: all checks passed -> `verified`, any failed ->
  `rejected`, otherwise remains pending/in-review.

## Partner profiles and inquiry forms (phase progress)
- Public partner (company) profile at `/companies/{slug}` lists the company's published listings.
- Public inquiry form at `GET/POST /listings/{listing}/inquiries` (consultation | inspection) stores an
  inquiry against a published listing; invalid submissions are rejected with validation errors.

## Static content pages (phase progress)
- Public static pages: `/fees`, `/verification`, `/blog`, `/blog/{slug}` (guest-accessible).
- Blog posts are hardcoded sample posts in BlogController; dates use Carbon instances.
- Fees page content is currently static demo pricing (no content/fee admin yet).

## Hosting
- Hosting: free tier for demo, then one VPS before onboarding real companies.
