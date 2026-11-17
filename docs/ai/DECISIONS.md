# Decisions (append-only)

- Roles: single backed-enum `role` column on users plus `company_id`; no Spatie package for now.
- Policies: one policy class per model; Gate::define only for non-model abilities.
- Auth scope: starter kit + roles + seeders + tests first; policies added with each model.
- Hosting: free tier for demo, then one VPS before onboarding real companies.
