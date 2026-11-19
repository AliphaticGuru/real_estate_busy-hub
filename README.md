# Busy Hub

Busy Hub is a verified real estate and construction marketplace for Nigeria, starting in Abuja. It combines verified property listings, legal due diligence and construction/architectural consultancy. The construction consultancy arm is Phase 2; Phase 1 covers the verified marketplace, partner profiles and the consultation/inspection flow.

## Phase 1 scope

- Verified property listings with filters, a detail page and WhatsApp contact.
- Company and professional partner profiles.
- Consultation and inspection request forms.
- Fees, verification-process and blog pages.
- The verification workflow: per-listing checklist, statuses, verifier records and an audit log.
- Role-specific dashboards for super admin, verifier, company, professional and customer.

Phase 1 does not include in-app chat, payments, rentals or a mobile app.

## Stack

- PHP 8.5 and Laravel 13
- Livewire 4 with Volt, and Alpine.js
- Tailwind CSS 4, built with Vite and Node 22
- PostgreSQL 15
- Docker Compose and Caddy 2
- PHPUnit 12 and Laravel Pint
- GitHub Actions for CI and deployment

## Local setup (WSL)

Prerequisites: Docker Desktop with WSL 2 integration enabled, Git, and ports 80, 443 and 8000 free. Run the commands from a checkout inside the WSL filesystem, not under `/mnt/c`.

Clone the repository and create the environment file:

```bash
git clone <repository-url> busy-hub
cd busy-hub
cp .env.example .env
```

Set `APP_URL=https://localhost` in `.env` if you want generated URLs to use the Caddy entry point. The Compose file supplies the PostgreSQL connection settings to the app container.

Start the containers and install PHP dependencies. The app image does not bundle Composer, so dependencies are installed through the official Composer container:

```bash
docker compose up -d --build
docker run --rm -v "$PWD:/app" -w /app composer:2 install --no-interaction --no-progress
docker compose up -d
```

The second `docker compose up -d` ensures the app container is running after `vendor/` exists.

Generate the application key, run migrations and seed the demo data:

```bash
docker compose exec -T app php artisan key:generate
docker compose exec -T app php artisan migrate
docker compose exec -T app php artisan db:seed
```

Build the front-end assets with Node 22:

```bash
docker run --rm -v "$PWD:/app" -w /app node:22-alpine sh -lc "npm ci && npm run build"
```

Open <https://localhost> through Caddy (accept the locally generated certificate) or <http://localhost:8000> to reach the app directly.

### Create the test database once

Tests are forced onto an isolated `busy_hub_testing` database. Create it the first time you set up the project:

```bash
docker compose exec -T db psql -U busyhub -d postgres \
  -tc "SELECT 1 FROM pg_database WHERE datname = 'busy_hub_testing'" \
  | grep -q 1 \
  || docker compose exec -T db psql -U busyhub -d postgres \
       -c "CREATE DATABASE busy_hub_testing OWNER busyhub"
```

The command is safe to rerun: it creates the database only when it does not already exist.

## Demo accounts

`php artisan db:seed` runs `RoleSeeder`, `UserSeeder` and `DemoSeeder`. It creates one account per role, plus a demo company and listings. The password for every seeded account is `password`.

| Role | Email |
|---|---|
| super_admin | admin@busyhub.test |
| company | company@busyhub.test |
| professional | professional@busyhub.test |
| verifier | verifier@busyhub.test |
| customer | customer@busyhub.test |

## Development

Run the test suite:

```bash
docker compose exec -T app php artisan test
```

Run Pint before committing, or use `--test` to check without rewriting files:

```bash
docker compose exec -T app ./vendor/bin/pint
docker compose exec -T app ./vendor/bin/pint --test
```

Rebuild assets after CSS or JavaScript changes:

```bash
docker run --rm -v "$PWD:/app" -w /app node:22-alpine npm run build
```

Run `npm ci` inside the same Node container after `package-lock.json` changes.

## Project structure

- `app/Http/Controllers` — public, verification and dashboard controllers.
- `app/Dashboard/DashboardData.php` — role-aware dashboard queries and recent rows.
- `app/Models`, `app/Enums`, `app/Policies` — data model, backed enums and authorization.
- `resources/views` — Blade pages, layouts, components and Livewire views.
- `routes/web.php` and `routes/auth.php` — public, authenticated, verification and auth routes.
- `database/migrations`, `database/factories`, `database/seeders` — schema and demo data.
- `tests` — feature, unit and policy coverage.
- `docs/ai` — project scope, progress and decisions.

Start with `AGENTS.md`, then `docs/ai/PROJECT.md` for scope and architecture rules, `docs/ai/PROGRESS.md` for current status, and `docs/ai/DECISIONS.md` for architectural decisions and their reasons.

## Environment variables

Copy `.env.example` and fill in values locally. Never commit `.env`.

- `WHATSAPP_NUMBER` — fallback WhatsApp number for listing contact links. A company's own number takes precedence.
- `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION` — S3-compatible credentials.
- `AWS_BUCKET` — target bucket for uploads.
- `AWS_USE_PATH_STYLE_ENDPOINT` — set to `true` for path-style S3 endpoints.
- `config/filesystems.php` also reads `AWS_ENDPOINT` and `AWS_URL`, which are useful when pointing the S3 disk at Cloudflare R2 or another non-AWS provider.

Secrets belong in environment variables or the deployment platform, never in the repository.

## Deployment

The intended path is a free-tier demo first, then a single VPS running Docker Compose and Caddy. Later phases may move to AWS, GCP or Kubernetes.

- `docker-compose.prod.yml` defines the production app, PostgreSQL and Caddy services.
- `.github/workflows/ci.yml` runs the test suite against a PostgreSQL service container.
- `.github/workflows/deploy.yml` runs on pushes to `main` and deploys over SSH using repository secrets.
- `backup.sh` creates compressed `pg_dump` backups and keeps the last seven days.
- `Caddyfile` uses `tls internal` for local development; the production TLS configuration replaces that line.

Keep production secrets in the deployment environment or GitHub repository secrets, and never commit `.env`.
