# Pre-launch audit — Stage 1 (audit only)

- Date: 2026-11-22
- Branch: `prelaunch-audit`
- Base: implementation on top of `cbe6c65` (`professional-consultations` build)
- Scope: read-only pre-launch audit. No application code was changed during this stage.

## Verdict legend

- **OK** — verified acceptable during the audit.
- **FIX** — issue found; fix recommended. Not applied during this stage.
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

- **Verdict: FIX**
- Files: `Caddyfile`, `docker-compose.prod.yml`, `bootstrap/app.php`.
- `Caddyfile` uses `{$APP_URL:localhost}` and defaults to `tls internal`; production comments instruct replacing the TLS line with `tls {$TLS_ADMIN_EMAIL:admin@example.com}` and setting a real `APP_URL`.
- `docker-compose.prod.yml` publishes port `80` on both `app` and `caddy`; `caddy` also publishes `443`.
- The production `app` image runs `php-fpm` and exposes `9000`, but the Dockerfile CMD is `php-fpm`; health checks target `/up`; Caddy reverse proxies to `app:8000`. The dev compose instead overrides the app command to `php artisan serve --host=0.0.0.0 --port=8000`, which aligns with Caddy.
- Production config therefore has a likely broken or at least inconsistent proxy topology (app/Caddy port and process mismatch).

### Trusted proxies and `request()->ip()`

- **Verdict: FIX (trust-proxy scope)**
- File: `bootstrap/app.php` — `$middleware->trustProxies(at: '*');`.
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

---

## 3. Mail identity

- **Verdict: FIX**
- Locations and defaults:
  - `.env.example`: `APP_NAME=Laravel`, `APP_URL=http://localhost:8000`, `MAIL_MAILER=log`, `MAIL_FROM_ADDRESS="hello@example.com"`, `MAIL_FROM_NAME="${APP_NAME}"`.
  - `.env`: `APP_NAME=Laravel`, `APP_URL=https://localhost`, `MAIL_MAILER=log`, `MAIL_FROM_ADDRESS="hello@example.com"`, `MAIL_FROM_NAME="${APP_NAME}"`.
  - `config/app.php`: defaults `Laravel` and `http://localhost`.
  - `config/mail.php`: from address default `hello@example.com`, from name default `Laravel`, SMTP local domain from `APP_URL`.
- Rendered mail in current logs uses `Laravel` as the application/greeting/signature name; sender identity is generic.
- Production mail identity is not launch-ready without explicit sender, domain, transport, and credentials.

---

## 4. Deploy workflow and release path

- **Verdict: FIX**
- Files: `.github/workflows/deploy.yml`.
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

- **Verdict: FIX**
- File: `backup.sh`.
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

- **Verdict: FIX**
- Files: `resources/views/livewire/pages/auth/register.blade.php`, password update form, reset form, vendor default.
- Uses `Password::defaults()`; vendor default is min length `8` and does not add mixed-case/letter/number/symbol strength by default.
- No application-level custom `Password::defaults()` was found.

### Email verification enforcement

- **Verdict: FIX**
- Files: `routes/web.php`, `app/Models/User.php`, auth verification controller.
- Protected inquiry/dashboard routes use `auth, verified`.
- However, `App\Models\User` does not implement `Illuminate\Contracts\Auth\MustVerifyEmail`, so Laravel’s email-verification enforcement middleware is not effective.
- Verification controller checks `hasVerifiedEmail()`, but unverified users are not structurally blocked by the model/route contract.

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

- **Verdict: DECISION NEEDED**
- Files: `database/seeders/DatabaseSeeder.php`, `UserSeeder.php`, `DemoSeeder.php`.
- The default database seeder creates demo users/professionals.
- Production deploy workflow does not run the seeder, but the default seeder is not environment-guarded.

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
- Retention: no deletion/purge policy or scheduled cleanup found.
- Pre-launch legal pages required but absent: privacy policy, terms of service, complaints process.
- Nigeria Data Protection Act: noted as a legal-advice topic only; no legal conclusion added.

---

## 8. SEO and error handling

- **Verdict: FIX**
- Files: `public/robots.txt`, sitemap references, error views, layouts.
- `public/robots.txt` exists and allows all (`User-agent: * Disallow:`).
- No sitemap file/route found.
- Only `resources/views/errors/429.blade.php` exists and uses public layout.
- No published error views found for 404, 403, 419, or 500.
- No `noindex` output found in authenticated layouts.

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
