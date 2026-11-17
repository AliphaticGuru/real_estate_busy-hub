# Decisions (append-only)

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

## Data layer (current stage)
- Single database scoped by company_id; no multi-tenancy package.
- Prices are stored as integers in kobo, never floats.
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

## Hosting
- Hosting: free tier for demo, then one VPS before onboarding real companies.
