# Pre-launch audit — Stage 1 (audit only)

- Date: 2026-11-22
- Branch: `prelaunch-audit`
- Base: implementation on top of `cbe6c65` (`professional-consultations` build)
- Scope: read-only pre-launch audit. No application code was changed during this stage.

## Stage 2 fixes applied (branch `prelaunch-app-fixes`)

Application-level findings from this audit were fixed during the pre-launch hardening build. Each fixed item below is marked **FIXED** and references the implementing commit:

- Environment-guarded demo seeders and `php artisan app:create-admin`: **FIXED** — commit `f9c208f`.
- Email verification enforcement (`MustVerifyEmail`, `auth, verified`): **FIXED** — commit `db85ed1`.
- Password policy (`Password::defaults()` with uncompromised check in production): **FIXED** — commit `5b89dab`.
- Styled error pages, noindex guards, robots and sitemap: **FIXED** — commit `635747d`.
- Draft legal pages and inquiry consent: **FIXED** — commit `5ff3fd1`.
- Environment hygiene and `php artisan prelaunch:check`: **FIXED** — commit `3601814`.

Infrastructure findings that remain open and require owner/infrastructure decisions: proxy topology, trusted proxies, deploy workflow, backups, two-factor authentication and retention policy (see sections 2, 4, 5, 6 and 7).

## Verdict legend

- **OK** — verified acceptable during the audit.
- **FIX** — issue found; fix recommended or applied during stage 2 (see the stage 2 fix list above).
- **DECISION NEEDED** — product, legal, or security decision required before launch.

---

## 1. Dependencies

### Composer audit

- **Verdict: OK**
- Command evidence: `docker run --rm -u $(id -u):$(id -g) -e COMPOSER_HOME=/tmp/composer -v "$PWD":/app -w /app composer:latest audit --format=json` produced `advisories: []`, `abandoned: []`, exit code `0`.
- Raw output saved to `/tmp/opencode/composer-audit.json`.
- Command evidence (initial attempt): `docker compose exec -T app composer audit` failed because `composer` is not installed in the `busy-hub-app` image.
- Runtime context: the app image uses PHP 8.5 and Laravel Framework 13.43.0.

### npm audit

- **Verdict: OK**
- Commands (inside `node:22-alpine`, no `--force`, no fix):
  - `npm audit --json` → exit `0`, `vulnerabilities: {}`, totals all zero.
  - `npm audit --omit=dev --json` → exit `0`, totals all zero.
- Raw output saved to `/tmp/opencode/npm-audit.json` and `/tmp/opencode/npm-audit-prod.json`.
- Severity: no low, moderate, high, or critical findings.
- Production relevance: reported packages are build-time/dev tooling (Vite/Tailwind and related); production-only audit is also clean.

### Dependency verdict

- **Verdict: OK** — no actionable dependency findings requiring remediation in this stage.

---

## 2. Proxy, TLS, HTTPS, and request identity

### Caddy and production proxy topology

- **Verdict: FIXED** (checkpoint A; branch `infra-prod`)
- Commit: `10e95e8` (branch `infra-prod`).
- Files: `Caddyfile`, `docker-compose.prod.yml`, `bootstrap/app.php`.
- Two-image topology built and proven locally: multi-stage `Dockerfile` with `app` (php-fpm) and `caddy` (baked `public/`) images, only Caddy publishing 80/443, app/queue/db unexposed, healthchecks on all four services, fpm resource limits and Postgres 2 GB defaults.
- Fixed compose network `172.28.0.0/24`, Caddy pinned to `172.28.0.10`.
- `Caddyfile` uses `{$APP_URL:localhost}` and defaults to `tls internal`; production comments instruct replacing the TLS line with `tls {$TLS_ADMIN_EMAIL:admin@example.com}` and setting a real `APP_URL`.
- `docker-compose.prod.yml` publishes port `80` on both `app` and `caddy`; `caddy` also publishes `443`.
- The production `app` image runs `php-fpm` and exposes `9000`, but the Dockerfile CMD is `php-fpm`; health checks target `/up`; Caddy reverse proxies to `app:8000`. The dev compose instead overrides the app command to `php artisan serve --host=0.0.0.0 --port=8000`, which aligns with Caddy.
- Production config therefore has a likely broken or at least inconsistent proxy topology (app/Caddy port and process mismatch).

### Trusted proxies and `request()->ip()`

- **Verdict: FIXED** (checkpoint A; branch `infra-prod`)
- Commit: `38e88ad` (branch `infra-prod`).
- File: `bootstrap/app.php` — env-driven `TRUSTED_PROXIES=172.28.0.10` through `$middleware->trustProxies(at: $proxies)`.
- Proven live: through Caddy `REMOTE_ADDR`/`X-Forwarded-For`/`request_ip` all equal the client address; a client-forged `X-Forwarded-For` through Caddy is overwritten; an untrusted FastCGI peer (`172.28.0.5`) sending `X-Forwarded-For: 203.0.113.9` had it ignored (`request_ip` kept the peer address).
- Proof via temporary probe script `/tmp/opencode/ip-probe.php` executed in the app container:
  - With forwarded header (`REMOTE_ADDR=172.18.0.9`, `X-Forwarded-For=203.0.113.10`) → `ip: 203.0.113.10`.
  - Direct request without header → `ip: 198.51.100.7`.
  - Direct request with spoofed forwarded header → `ip: 203.0.113.99`.
- Conclusion: `request()->ip()` honours `X-Forwarded-For` because all proxies are trusted; behind Caddy, the forwarded client IP is used for the request identity.
- Risk: trusting all proxies allows direct clients to spoof `X-Forwarded-For`; recommend restricting trusted proxies once the real production proxy topology is fixed.

### Production HTTPS and environment expectations

- **Verdict: FIX**
- `docker-compose.prod.yml` sets `APP_ENV=production` but does not set `APP_DEBUG`, `APP_URL`, `APP_NAME`, or mail/S3/session-secure values explicitly.
- `.env` currently has `APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=https://localhost`.
- `APP_DEBUG` must be `false` for production.
- Secure session cookie: `SESSION_SECURE_COOKIE` is not set; session uses `same_site` default `lax`, `http_only` default `true`, lifetime `120`. Production should set `SESSION_SECURE_COOKIE=true`.

## 2.1 Production HTTPS and environment expectations (updated)

- **Verdict: FIXED**
- Commit: `3601814` (stage 2, item 6).
- `.env.example` now sets `APP_NAME="Busy Hub"` and `MAIL_FROM_ADDRESS="no-reply@your-domain.example"`, and stays usable over `http://localhost:8000` (no `SESSION_SECURE_COOKIE`).
- Added `.env.production.example` with production-safe values: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain.example`, `LOG_CHANNEL=daily`, `LOG_DAILY_DAYS=14`, `LOG_LEVEL=warning`, `SESSION_DRIVER=database`, `SESSION_SECURE_COOKIE=true`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`, `MAIL_MAILER=smtp`.
- Added `php artisan prelaunch:check` (commit `3601814`) which exits non-zero in production when `APP_DEBUG` is true, `APP_URL` is localhost or `http`, `MAIL_MAILER` is `log` or `array`, the session cookie is not secure, `SESSION_DRIVER`/`CACHE_STORE` is redis without `REDIS_HOST`, or `APP_NAME` is the default `Laravel`.

---

## 3. Mail identity

- **Verdict: FIXED** (sender identity; production transport and credentials remain open)
- Commit: `3601814` (stage 2, item 6).
- Files: `.env.example`, `.env`, `config/mail.php`, `config/app.php`.
- `APP_NAME` is now `Busy Hub`; `MAIL_FROM_ADDRESS` is a clearly placeholder value; `MAIL_FROM_NAME` comes from `APP_NAME`.
- The rendered mail identity is therefore sourced from the application name and a placeholder sender.
- Production mail transport, domain and credentials remain open (see section 6 deploy notes).

---

## 4. Deploy workflow and release path

- **Verdict: FIXED** (checkpoint B, branch `infra-prod`)
- Commits: `2fa6082` (initial dry-run-first workflow), `571e9ea` (ssh/sha-validation/smoke-rollback hardening).
- Files: `.github/workflows/deploy.yml`, `scripts/deploy.sh`, `README.md`.
- Manual `workflow_dispatch` only (dry_run default true, sha input); build+push both images to GHCR with SHA tag + `:latest`; deploy job with `environment: production`, concurrency, minimal permissions; plain `ssh` as dedicated non-root user with hardened key handling; SHA validated against `^[0-9a-f]{40}$` twice (workflow + deploy.sh); deploy SHA must be `origin/main` head and CI green before the server step; pre-deploy pg_dump before `migrate --force`; config/route/view cache, queue restart, `up -d --wait`, smoke checks with retries, rollback restores previous SHA for both images.
- Workflow steps observed:
  - Checkout, setup PHP 8.5, composer install, copy `.env.example` to `.env`, generate key, run migrations, build Vite.
  - SSH sync step has address/username/key but no remote `script` content.
  - Subsequent deployment commands run in the GitHub runner context rather than executing the intended server-side sequence.
- Missing production actions:
  - No `php artisan config:cache`, `route:cache`, or `view:cache`.
  - No queue restart step.
  - No Docker image build/push in workflow.
  - No rollback path.
  - Migrations are run, but the workflow topology around deployment is incomplete.

---

## 5. Backups

- **Verdict: IN PROGRESS** (checkpoint C, branch `infra-prod`)
- Commits: `backup.sh` rewritten (pg_dump in db container -> gzip -> gpg recipient -> timestamped local dump, local retention 7 days, S3 upload via env), `restore.sh --confirm` added (restore into new disposable database, drop after), `deploy/busy-hub-backup.{service,timer}` added (not installed).
- Local rehearsal executed: backup -> restore into disposable database -> row counts compared -> stack down/up -> counts unchanged (users/listings/inquiries stable).
- Behaviour: creates `pg_dump` gzip backup in `./backups`, keeps last seven days; no encryption and no off-server copy observed.
- Frequency: no cron/schedule entry found in repository.
- Restore: no restore procedure or executed rehearsal found.
- Recommendation: rehearse restore against a disposable database/server copy, not the dev database.

---

## 6. Authentication hardening

### Login throttling

- **Verdict: OK**
- File: `app/Livewire/Forms/LoginForm.php`.
- Uses `RateLimiter`, throttle key combining email and request IP, attempts limit of `5`, lockout event and validation throttle response.

### Password rules

- **Verdict: FIXED**
- Commit: `5b89dab` (stage 2, item 3).
- Files: `app/Providers/AppServiceProvider.php`, `resources/views/livewire/pages/auth/register.blade.php`, password update form, reset form, vendor default.
- `AppServiceProvider` now sets `Password::defaults()` to minimum length `10` with letters, mixed case and numbers; the `uncompromised()` check is applied only when the app runs in production.
- Weak passwords are rejected at registration and password update; strong passwords are accepted.
- Follow-up: existing demo accounts keep the shared password and are outside the new policy until reset.

### Email verification enforcement

- **Verdict: FIXED**
- Commit: `db85ed1` (stage 2, item 2).
- Files: `routes/web.php`, `app/Models/User.php`, auth verification controller.
- `App\Models\User` now implements `Illuminate\Contracts\Auth\MustVerifyEmail`.
- Protected routes use the `auth, verified` middleware; `/profile` is protected with `auth, verified` (dashboard and inquiries already were).
- Unverified users are redirected to the verification notice; verified users proceed.
- Demo accounts keep `email_verified_at` set (see `f9c208f`).

### Session and account security

- **Verdict: FIX**
- Files: `config/session.php`, `docker-compose.prod.yml`, `.env`.
- Session lifetime `120` minutes, no expire-on-close, secure cookie unset, same-site default `lax`.
- Production compose sets Redis session/cache stores without a Redis service.

### Two-factor authentication

- **Verdict: DECISION NEEDED**
- No two-factor implementation was found.
- Whether `super_admin` and `verifier` require 2FA before launch is an open product/security decision.

### Seeded demo accounts

- **Verdict: FIXED**
- Commit: `f9c208f` (stage 2, item 1).
- Files: `database/seeders/DatabaseSeeder.php`, `UserSeeder.php`, `DemoSeeder.php`, `AppCreateAdminCommand`.
- The default database seeder now seeds demo users/professionals/listings only in the `local` or `testing` environments; other environments run `RoleSeeder` only.
- Added `php artisan app:create-admin` to create a super admin interactively or with `--force`.
- Follow-up: production deployment still needs an admin account via the command.

---

## 7. Data protection

- **Verdict: FIX (retention/legal pages)**
- Data collected by inquiries: name, phone, email, message, preferred date.
- Inquiry access control:
  - Super admin: full.
  - Company: own listing inquiries.
  - Professional: own professional inquiries.
  - Customer: own inquiries.
  - Verifier: denied in inquiry controller scoping.
- Retention: no deletion/purge policy or scheduled cleanup found — **OPEN**.
- Pre-launch legal pages added during stage 2: privacy policy, terms of service and complaints process with clearly marked draft notices (`5ff3fd1`).
- Nigeria Data Protection Act: noted as a legal-advice topic only; no legal conclusion added.

---

## 8. SEO and error handling

- **Verdict: FIXED**
- Commit: `635747d` (stage 2, item 4).
- Files: `routes/web.php`, `app/Http/Controllers/SitemapController.php`, `resources/views/errors/*`, layouts, `public/robots.txt`.
- Styled error views added for 403, 404, 419, 500 and 503; server errors render standalone HTML without database, session or Vite manifest dependencies.
- `noindex, nofollow` meta added to the app and guest layouts.
- `GET /robots.txt` and `GET /sitemap.xml` added; the sitemap contains static public pages, published verified listings, active companies, verified professionals and published blog posts and is cached for an hour.
- Static `public/robots.txt` was replaced by a route-based robots.txt that lists the sitemap URL from `APP_URL`.

---

## 9. File uploads and storage

- **Verdict: OK (for current phase) with operational caveats**
- Files: `app/Models/ListingImage.php`, listing image migration, listing views, storage config.
- Listing images store paths only; no upload endpoint or storage write path was found in app code.
- S3/R2 settings exist in `config/filesystems.php` and `.env.example` (`AWS_*`, path-style endpoint guidance), but uploads are not yet implemented.

---

## 10. Logging

- **Verdict: FIX**
- File: `storage/logs/laravel.log`.
- Current log contains inquiry notification content and mail metadata.
- Email/address patterns were scanned; recipient addresses and inquiry content appear in logs.
- Secret-pattern scan found no application-key/database/mail/AWS secret matches in the scanned log output.
- Log rotation: configured default stack/single and `LOG_STACK=single`; no active `daily` rotation setting was used. Rotation should be hardened before production.

---

## Summary

- No blocking dependency vulnerabilities were found.
- Most critical pre-launch gaps are deployment/proxy correctness, production mail identity, deploy workflow completeness, logging rotation, SEO/legal pages, and authentication enforcement/2FA decisions.
- The next recommended steps depend on decisions from the owner; nothing in application code has been changed during the audit.

---

## Stage 2c: first server (checklist, not executed)

- [ ] From a remote client, verify `request()->ip()` is the real public address, over IPv4 **and** IPv6.
- [ ] Confirm the inquiry throttle buckets per client: two different source addresses each get 5 attempts before a 429.
- [ ] If a CDN or load balancer is added in front of Caddy, add its ranges to Caddy's `trusted_proxies` and to `TRUSTED_PROXIES`; otherwise every client shares one IP and one throttle bucket.

### Not yet testable without a server or bucket (Stage 2c, unchecked)

- [ ] S3 upload with write-only bucket credentials (`backup.sh` `S3_BUCKET` path) — not executed against a real bucket.
- [ ] Remote client IP verification (real public IP over IPv4 and IPv6) — blocked until the first server exists.
- [ ] First certificate issuance for a real public `APP_URL` via Caddy ACME — blocked until the first server exists.

## Post-launch backlog (not executed)

- [ ] Content-Security-Policy: add a production `Content-Security-Policy` header; Livewire/Alpine scripts, Vite build assets and inline styles must be accounted for (nonce or hash based approach) before first public traffic.
