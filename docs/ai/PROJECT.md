# Project Overview

Verified real estate and construction hub for Nigeria, starting in Abuja.
Three arms: verified property marketplace, legal due diligence, construction/architectural consultancy.
Rentals are a later phase.

Live status lives in docs/ai/PROGRESS.md. Decisions and their reasons live in docs/ai/DECISIONS.md.
If this file and the code disagree, the code wins: fix this file.

## Stack
Laravel, Livewire, Alpine.js, Tailwind, PostgreSQL, Docker Compose, GitHub Actions.
Deploy: free-tier demo first, then a single VPS (Docker Compose + Caddy). Later AWS/GCP/Kubernetes.

## Phase 1 scope (only this)
Verified listings with filters, partner profiles, consultation/inspection requests,
fees and verification-process pages, blog. No chat, no payments, no app.

## Roles
| Role | Can do |
|---|---|
| super_admin | Everything; manages companies, professionals, fees and content |
| verifier | Reviews listings, completes the verification checklist, sets verification status |
| company | Manages own company profile and own listings only; sees inquiries addressed to the company |
| professional | Manages own profile (lawyer, architect, engineer, surveyor, builder); sees own inquiries |
| customer | Browses published listings, sends consultation and inspection requests |

Roles are a backed enum on users. Company staff also have a company_id.

## Data model (Phase 1)
- companies: profile, contact and WhatsApp number, status (pending, active, suspended)
- professionals: linked to a user, optional company, type, registration body and number, credentials verification
- listings: owned by a company, price in kobo (integer), status (draft, published, archived),
  verification_status (pending, in_review, verified, rejected), verifier and timestamp
- listing_images: paths only, files stored in S3-compatible storage
- verification_checks: per-listing checklist (title_document, survey_plan, ownership_proof,
  site_inspection, no_encumbrance), each pending, passed or failed, with notes and checker
- audit_logs: append-only record of verification changes (jsonb old and new values)
- inquiries: consultation or inspection requests, linked to a listing or professional, status (new, contacted, closed)

## Architecture rules
- Stateless containers; uploads go to S3-compatible storage (Cloudflare R2).
- Secrets via environment variables only; never commit .env.
- Single database, scoped by company_id; no multi-tenancy package.
- Authorization via per-model policy classes; Gates only for non-model abilities.
- Every new model gets a policy, a factory, and tests that include cross-company access attempts.
- Statuses and types are backed PHP enums, cast on the models.
- Mobile-first, light, server-rendered pages. WhatsApp (wa.me) button on listings and service pages.
- Verified-listing workflow is the core feature: pending, in_review, verified, rejected;
  per-listing checklist; record verifier and timestamp; audit log.

## Conventions
- Local development runs in Docker Compose inside WSL; the repo lives in the WSL filesystem.
- Tests run on SQLite, PostgreSQL for production.
- One PROGRESS.md item at a time; plan first, implement after approval; tests pass before marking done.
- Do not add Phase 2 or Phase 3 features (construction consultancy packages, rentals, payments, chat) until asked.

## Out of scope for now
Mobile app, in-app chat, document upload and tracking, diaspora dashboards, Paystack payments,
rental management, Kubernetes.



<!-- # Project Overview

Verified real estate and construction hub for Nigeria, starting in Abuja.
Three arms: verified property marketplace, legal due diligence, construction/architectural consultancy.
Rentals are a later phase.

## Stack
Laravel, Livewire, Alpine.js, Tailwind, PostgreSQL, Docker Compose, GitHub Actions.
Deploy: free-tier demo first, then a single VPS (Docker Compose + Caddy). Later AWS/GCP/Kubernetes.

## Phase 1 scope (only this)
Verified listings with filters, partner profiles, consultation/inspection requests,
fees and verification-process pages, blog. No chat, no payments, no app.

## Architecture rules
- Stateless containers; uploads go to S3-compatible storage (Cloudflare R2).
- Secrets via environment variables only; never commit .env.
- Single database, scoped by company_id; no multi-tenancy package.
- Roles: super_admin, company, professional, verifier, customer (backed enum on users).
- Authorization via per-model policy classes; Gates only for non-model abilities.
- Mobile-first, light, server-rendered pages. WhatsApp (wa.me) button on listings and service pages.
- Verified-listing workflow is the core feature: pending, in_review, verified, rejected;
  per-listing checklist; record verifier and timestamp; audit log. -->
