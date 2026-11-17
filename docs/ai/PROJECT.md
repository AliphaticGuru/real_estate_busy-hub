# Project Overview

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
  per-listing checklist; record verifier and timestamp; audit log.
