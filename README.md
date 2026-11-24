# Busy Hub

Busy Hub is a verified real estate and construction hub for Nigeria, starting in Abuja. Phase 1 covers the verified property marketplace, company and professional profiles, consultation and inspection requests, the fees, verification-process and blog pages, the per-listing verification workflow, and role-specific dashboards. Phase 1 is implemented and covered by the test suite; payments, chat, rentals and a mobile app are not included.

## Stack

- PHP 8.5 and Laravel 13
- Livewire 4 with Volt, and Alpine.js
- Tailwind CSS 4, built with Vite and Node 22
- PostgreSQL 15
- Docker Compose and Caddy 2
- PHPUnit 12 and Laravel Pint
- GitHub Actions for CI and deployment

## Local setup (WSL)

Prerequisites: Docker Desktop with WSL 2 integration, Git, and ports 80, 443 and 8000 free. Keep the checkout inside the WSL filesystem, not under `/mnt/c`.

Clone the repository and create the environment file:

```bash
git clone <repository-url> busy-hub
cd busy-hub
cp .env.example .env
```

The Compose file supplies the PostgreSQL connection settings to the app container, so the `.env` database values are not used locally.

Start the containers, then install PHP dependencies. The app image does not bundle Composer, so dependencies are installed through the official Composer container. The app is stopped first so it does not read `vendor/` while Composer rewrites it:

```bash
docker compose up -d --build
docker compose stop app
docker run --rm -v "$PWD:/app" -w /app composer:2 install --no-interaction --no-progress
docker compose up -d
```

Generate the application key(first run only):

```bash
docker compose exec -T app php artisan key:generate
```

Create the isolated test database once. The command is safe to rerun:

```bash
docker compose exec -T db psql -U busyhub -d postgres \
  -tc "SELECT 1 FROM pg_database WHERE datname = 'busy_hub_testing'" \
  | grep -q 1 \
  || docker compose exec -T db psql -U busyhub -d postgres \
       -c "CREATE DATABASE busy_hub_testing OWNER busyhub"
```

Run migrations and seed the demo data. Run the seed once on a fresh database: the demo listings use fixed slugs, so a second seed fails the unique slug constraint.

```bash
docker compose exec -T app php artisan migrate
docker compose exec -T app php artisan db:seed
```

Build the front-end assets with Node 22:

```bash
docker run --rm -v "$PWD:/app" -w /app node:22-alpine sh -lc "npm ci && npm run build"
```

Open <https://localhost> through Caddy and accept the locally generated certificate, or open <http://localhost:8000> to reach the app container directly.

## Demo accounts

`php artisan db:seed` runs `RoleSeeder`, `UserSeeder` and `DemoSeeder`, which creates one account per role plus a demo company and demo listings. Every seeded account uses the password `password`.

| Role | Email |
|---|---|
| super_admin | admin@busyhub.test |
| company | company@busyhub.test |
| professional | professional@busyhub.test |
| verifier | verifier@busyhub.test |
| customer | customer@busyhub.test |

## Development

Run the test suite through the app container so it uses the isolated `busy_hub_testing` database:

```bash
docker compose exec -T app php artisan test
```

Check or apply code style with Pint:

```bash
docker compose exec -T app ./vendor/bin/pint --test
docker compose exec -T app ./vendor/bin/pint
```

Rebuild assets after CSS or JavaScript changes:

```bash
docker run --rm -v "$PWD:/app" -w /app node:22-alpine npm run build
```

Run `npm ci` in the same Node container after `package-lock.json` changes.

### Troubleshooting

If `npm ci` fails with `ECONNRESET`, run the same command with host networking:

```bash
docker run --rm --network host -v "$PWD:/app" -w /app node:22-alpine sh -lc "npm ci && npm run build"
```

Always run tests with `docker compose exec -T app php artisan test`. Running `php artisan test` on the host bypasses the container database settings, falls back to SQLite, and does not use the isolated test database.

## Project structure

- `app/Http/Controllers` — public, verification and dashboard controllers.
- `app/Dashboard/DashboardData.php` — role-aware dashboard queries and recent rows.
- `app/Models`, `app/Enums`, `app/Policies` — data model, backed enums and authorization.
- `resources/views` — Blade pages, layouts, components and Livewire views.
- `routes/web.php` and `routes/auth.php` — public, authenticated, verification and auth routes.
- `database/migrations`, `database/factories`, `database/seeders` — schema, test data and demo data.
- `tests` — feature and policy coverage.
- `docs/ai` — project scope, progress and decisions.

Start with `AGENTS.md`, then `docs/ai/PROJECT.md` for scope and architecture rules, `docs/ai/PROGRESS.md` for current status, and `docs/ai/DECISIONS.md` for architectural decisions and their reasons.

## Environment variables

Copy `.env.example` and fill in values locally. Never commit `.env`.

- `WHATSAPP_NUMBER` — fallback WhatsApp number for listing contact links. A company's own WhatsApp number takes precedence.
- `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET` — S3-compatible storage credentials and bucket.
- `AWS_USE_PATH_STYLE_ENDPOINT` — set to `true` for path-style S3 endpoints.
- `config/filesystems.php` also reads `AWS_ENDPOINT` and `AWS_URL`; add them to `.env` when pointing the S3 disk at Cloudflare R2 or another non-AWS provider.
- `TRUSTED_PROXIES` — comma-separated IPs/CIDRs allowed to set `X-Forwarded-*` headers. In production this is the pinned address of the Caddy container (`172.28.0.10` in `docker-compose.prod.yml`), so a client that sends its own `X-Forwarded-For` cannot change the IP Laravel records or throttles by. If a CDN or load balancer is added in front of Caddy, add its ranges here **and** to Caddy's `trusted_proxies`, or client IPs will be wrong.

Secrets belong in environment variables or the deployment platform, never in the repository.

### Email and queues

Inquiry notifications are sent through Laravel's mail and queue system. Mail settings come from environment variables only:

- `MAIL_MAILER` — transport. `.env.example` uses `log`, which writes the rendered email to `storage/logs/laravel.log` for local development.
- `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` — SMTP connection details when `MAIL_MAILER=smtp`.
- `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` — sender identity. `MAIL_FROM_NAME` defaults to `APP_NAME`.

`QUEUE_CONNECTION=database` is the default and needs no external service; the `jobs` and `failed_jobs` tables already exist in `database/migrations`.

Run a worker locally with:

```sh
php artisan queue:work --tries=3 --max-time=3600
```

`docker-compose.yml` and `docker-compose.prod.yml` both define a `queue` service that runs that command with `restart: unless-stopped`, so notifications are delivered without a manual worker.

For a free-tier SMTP provider, [Brevo](https://www.brevo.com/) offers a free plan with a daily sending limit. Set `MAIL_MAILER=smtp`, `MAIL_HOST=smtp-relay.brevo.com`, `MAIL_PORT=587`, and put the SMTP login and key in `MAIL_USERNAME` and `MAIL_PASSWORD`. Keep the credentials in the deployment environment, never in the repository.

Resend can be used over SMTP without the PHP SDK: set `MAIL_HOST=smtp.resend.com`, `MAIL_PORT=587`, `MAIL_USERNAME=resend`, and `MAIL_PASSWORD` to your Resend API key (`RESEND_API_KEY` in the production env example). No Composer package is needed.

## Deployment

The intended path is a free-tier demo first, then a single VPS running Docker Compose and Caddy. Later phases may move to AWS, GCP or Kubernetes.

### Production deploy workflow (`.github/workflows/deploy.yml`)

- Trigger: **manual only** (`workflow_dispatch`) during the build/dry-run phase.
  - Inputs: `dry_run` (default `true`) — when `true`, the workflow builds and pushes the images to GHCR but never connects to a server; when `false` it deploys.
  - `sha` (optional) — commit SHA to build and deploy; defaults to the commit of the dispatch.
- The workflow builds the `app` and `caddy` images from the same commit, both tagged with the commit SHA plus `:latest`, and pushes both to GHCR.
- On deploy it logs into GHCR, then over SSH as a dedicated non-root deploy user runs `scripts/deploy.sh <sha>` on the server (plain `ssh`, `set -euo pipefail`, never echoes secret values).
- The deploy job runs with `environment: production`, a concurrency group (`deploy-production`) and minimal permissions (`contents: read`, `packages: write`).
- Automatic triggering from `main`/CI (`workflow_run` or `push`) is deliberately not enabled in this phase; enabling it once the first server exists is a Stage 2c decision.

### One-time first-server bootstrap (not executed yet)

1. Create a dedicated non-root deploy user with Docker group membership on the VPS.
2. Install Docker Engine and the Compose plugin on the VPS.
3. `docker login` on `ghcr.io` with a read-only personal access token as that user.
4. Place `docker-compose.prod.yml` and a production `.env` in `/var/www/busy-hub`.
5. Run the first deploy (`workflow_dispatch` with `dry_run: false`), which migrates and starts the stack.
6. Seed only the roles: `php artisan db:seed --class=RoleSeeder --force`.
7. Create the first super admin: `php artisan app:create-admin --force`.

### Manual rollback

A deploy records the previous release in `/var/www/busy-hub/deploy-state/previous-sha` and, if any deploy step fails, restores the previous release for **both** images (`app` and `caddy`) with the same SHA tag.

For a manual rollback to a known SHA:

```sh
IMAGE_TAG=<previous-sha> docker compose -f docker-compose.prod.yml --env-file .env up -d
docker compose -f docker-compose.prod.yml --env-file .env pull
```

### Migration compatibility

Migrations must be **backward compatible** with the previous release: rollback does not revert the database, so a new release must keep old code paths working while the database catches up.

### Production secrets and variables (names only)

- GitHub repository secrets: `PROD_SERVER_IP`, `DEPLOY_SSH_KEY` (dedicated deploy user key), plus the GitHub token usage for GHCR (`GITHUB_TOKEN` is provided by the workflow).
- Deployment environment variables (from the server `.env`): `APP_KEY`, `DB_PASSWORD`, `POSTGRES_*`, `TRUSTED_PROXIES`, `MAIL_*`, `AWS_*`.

Keep production secrets in the deployment environment or GitHub repository secrets, and never commit `.env`.
