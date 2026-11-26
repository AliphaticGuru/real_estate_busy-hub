## Inquiry-customer linking (phase progress)
- Inquiries table has `listing_id`, `professional_id`, and newly added `user_id` (nullable, indexed, nullOnDelete).
- Customers submit inquiries that are traced to their user account; `InquiryController@store` sets `user_id` only when the visitor is authenticated; guests still submit inquiries with `user_id` null.
- `InquiryPolicy`: super_admin sees all; company users see only inquiries for their own company's listings; professionals only their own (`professional_id`); customers only their own (`user_id`); `update` excludes customers.
- Migration: `php artisan make:migration add_user_id_to_inquiries_table` — `user_id`: foreignId('user_id')->nullable()->index()->nullOnDelete().
- Tests: guest inquiry works (user_id null), authenticated inquiry stores user_id, customer A cannot see customer B's inquiries (verified by `Inquiry::where('user_id', $userA->id)->get()`).
- Full suite: 126 passed (397 assertions).

## CI (phase progress)
- GitHub Actions workflow runs the test suite on ubuntu-latest against a `postgres:15-alpine` service container.
- Test suite uses the configured database when `DB_CONNECTION=pgsql` is set (CI / local Postgres); defaults to
  in-memory SQLite otherwise.

## Test database isolation
- Tests use `busy_hub_testing`, forced in `tests/TestCase.php` and `phpunit.xml`; CI uses the same name.
- Reason: tests previously ran against the dev database and wiped it.

## Public layout (checkpoint c)
- Public pages use the anonymous `<x-public-layout>` component, which includes `layouts/public.blade.php`; `@livewireStyles`/`@livewireScripts` live only in that layout so they cannot be duplicated.
- The public footer is brand/tagline only. The footer WhatsApp button was removed on request.
- Listing pages keep their own pre-filled WhatsApp link, preferring the company number and falling back to `config('services.whatsapp.number')`.
- Reason: one layout prevents divergent headers/footers, and an empty config must not render a broken `wa.me/` link.

## Mobile public navigation (checkpoint c fix)
- The public header uses Livewire's bundled Alpine (`x-data="{ open: false }"`) for the mobile menu; no extra Alpine dependency is added.
- The menu panel uses `x-show` + `x-cloak` with a global `[x-cloak] { display: none !important; }` rule to prevent a flash before Alpine starts.
- Reason: keeps JS minimal and server-rendered content intact at 360px.

## Floating/sticky public header (checkpoint c fix)
- The public header is `fixed` and visible by default; the page wrapper gets `pt-16` so content is not covered.
- A user toggle (pin button) enables auto-hide and stores the preference in `localStorage` (`public-nav-autohide`). When enabled and the page is not at the top, the header slides out after 2250ms without scrolling and returns on the next scroll.
- At the top of the page (`scrollY === 0`) the header always stays visible.
- The mobile menu panel is `absolute top-full` inside the fixed header, so opening it overlays the page instead of pushing content down.
- Requested by the user: yes — an always-visible nav with an optional auto-hide after 2000–2500ms without scrolling; it was not introduced unasked.

## Authenticated UI (stage 2)
- The brand colour lives in `resources/css/app.css` under `@theme` (`--color-brand-*`); auth/profile focus rings and primary buttons use `brand-500`/`brand-600`.
- The five placeholder components (`input`, `button`, `card`, `alert`, `page-header`) were rebuilt with explicit props instead of the undefined `$attrs`/`$variant-class`; inputs and buttons enforce a 44px minimum tap target.
- The authenticated nav and the app/guest layouts reuse the public design tokens (white surface, `border-gray-100`, `config('app.name')`, shared footer) and keep one `@livewireStyles`/`@livewireScripts` pair per layout.
- Volt PHP blocks, validation and redirects were left untouched; only markup and classes changed.
- Reason: consistent chrome across guest, auth and public pages, WCAG-sized controls, and no duplicated asset injection.

## Landing page (stage 3, stage 1)
- `layouts/public.blade.php` renders an optional `head` named slot after `<title>`; the landing page uses it for the meta description and Open Graph tags rather than adding a one-off layout.
- Featured listings are queried in the view with explicit `status = published` and `verification_status = verified` filters, eager loading `images` and `company`, ordered by `verified_at` and limited to three.
- Partner profiles are generic placeholder cards marked with the repaired `badge` component (`variant="placeholder"`); no names, testimonials or statistics are invented.
- Only the `/` route renders the landing page; no sitemap route is added in this stage.
- Reason: server-rendered SEO content with no new dependencies, and honest placeholder content until partners are onboarded.

## Caddy dev configuration fix
- `local_certs` is a Caddy global option and cannot sit inside a site block, so the site uses `tls internal` instead; that is also the line to replace with a production `tls` address.
- Caddyfile environment placeholders use `{$APP_URL:localhost}` syntax; shell-style `${APP_URL:localhost}` is not substituted and makes config adaptation fail.
- Reason: the old Caddyfile could not adapt, which left the Caddy container in a restart loop after `docker compose up`.

## Role dashboards (stage 2)
- One `/dashboard` route uses `DashboardController` and `App\Dashboard\DashboardData`; the service switches on the user's role, returns counts and recent rows, and aborts 403 when no role is assigned.
- Queries are scoped explicitly in the service: company via `listing.company_id`, professional via `professional_id`, customer via `user_id`; the Blade view contains no queries.
- A company user without a company gets zeroed counts, every list has an empty state, and only existing route names are linked; unfinished actions are disabled "Coming soon" items.
- Reason: role-aware data without relying on policies to filter result sets, and a view that cannot accidentally widen a scope.

## Slug binding for public listing routes (inquiry-flow fix)
- `listings.show`, `inquiries.create` and `inquiries.store` use explicit `{listing:slug}` route binding instead of a global `getRouteKeyName()`.
- Reason: URLs stay slug-based, inquiry pages no longer pass slugs into an `id` lookup (which caused a PostgreSQL bigint error), and `verify/checks.update` keeps its existing id binding.
- `x-button` renders an anchor when an `href` prop is supplied, so link-style actions keep the same 44px tap target as submit buttons.

## Listing detail actions (listing-buttons-readme)
- Published listing pages show "Inquire" and "WhatsApp" below the company name in one left-aligned `flex flex-wrap` row; `grow` lets the actions share a line at narrow widths or fill the row when they wrap.
- The Inquire link keeps `aria-label="Request an inspection or consultation"` so the short visible label retains the descriptive accessible name.
- Reason: both actions stay visible and tappable at 360px without adding JavaScript, while screen readers keep the full intent.

## Customer role assignment fix (inquiry-management)
- The `User` model keeps a default Customer role (`booted` creating hook) so plain factory users start as customers; other roles are assigned deliberately via `forRole()` or the registration flow.
- The register Volt component now calls `$user->assignRole(RoleName::Customer)` after creating the user, so registration assigns the customer role explicitly instead of relying on the model default.
- Factories, seeders and tests set roles deliberately via `forRole()` or explicit `assignRole()` (e.g. `User::factory()->professional()`, `UserSeeder`, `RoleTest`, `AuthenticationTest`, `RegistrationTest`).
- Reason: removes the "professional treated as customer" ambiguity by making the customer role an explicit default for new users while registration remains the explicit source of the customer role; factories and tests no longer inherit implicit defaults.

## Inquiry status management (inquiry-management)
- `InquiryController@updateStatus` returns 422 for tampered status values not in `InquiryStatus` cases, and redirects back with an error flash for disallowed/stale transitions.
- Success redirects to `inquiries.show` with a success flash; the status badge variant lives on the enum (`InquiryStatus::badgeVariant()`).
- Reason: tampered payloads fail fast with 422, stale transitions bounce back with a readable error via the shared `x-alert` component, and valid transitions flow to the show page.

## Inquiry authorization via InquiryPolicy (inquiry-management)
- `App\Http\Controllers\Controller` uses `Illuminate\Foundation\Auth\Access\AuthorizesRequests`; this Laravel version has no `Illuminate\Foundation\Http\Controllers\Controller` base, and the empty app base made `authorize()` undefined.
- `inquiries.show` and `inquiries.status.update` authorize through `InquiryPolicy` (`view` / `update`); `Inquiry::viewableBy()` and the `whereViewableBy()` scope are kept for list scoping only.
- Reason: a single authorization path (policy) for single inquiries and a single scoping path (model scope) for lists, with a test proving they agree.
- Removed `database/seeders/TownSeeder.php` (unrequested demo data) and its `DatabaseSeeder` reference; `CompanySeeder` remains.

## Inquiry email notifications (inquiry-notifications)
- `App\Notifications\InquiryReceived` is mail-only, implements `ShouldQueue`, and sets `afterCommit` in the constructor; `App\Services\InquiryNotifier` resolves the recipient and sends it on demand.
- Recipient precedence: `professional_id` set → that professional's user email; otherwise the listing company email. A missing email logs a warning and skips; the controller also catches `Throwable` so mail failures never break the submission.
- `InquiryStatus::allowedTransitions()` is the single source of truth for the status form and `updateStatus`; the show page renders one POST form (`@csrf` + `@method('PATCH')`) per valid transition. Reason: the HTML form method must be POST, and duplicating the transition map in the view would let the UI and controller drift.
- Spam protection is `throttle:5,1` on `inquiries.store` plus a hidden `website` honeypot checked before validation. Reason: cheap, stateless protection for guests without adding a CAPTCHA dependency.
- Queue uses the database driver. Both compose files add a `queue` service running `php artisan queue:work --tries=3 --max-time=3600` with `restart: unless-stopped`; prod `QUEUE_CONNECTION` is now `database` because no redis service exists. Reason: the existing migrations already create `jobs`/`failed_jobs`, so no schema change was needed.
- The queue service overrides the image healthcheck with `pgrep -f 'queue:work'`; the inherited PHP-FPM check would mark it unhealthy even while the worker ran.

## Mail and queue environment configuration
- Mail settings come only from `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME`; local development uses `MAIL_MAILER=log`, and the README documents a free-tier Brevo SMTP option without secrets.
- Reason: keeps credentials out of the repository and lets production switch transports without code changes.

## Professional consultations (professional-consultations)
- `professionals.slug` is nullable and unique; the migration backfills from `users.name` with numeric suffixes, and `Professional::creating` fills it for new rows. All public professional routes use `{professional:slug}`, so numeric ids are never exposed; null or unknown slugs return 404.
- The public directory and profile filter on `credentials_verified_at is not null`; the same guard protects the consultation form and both store actions, so unverified professionals cannot receive requests even by direct URL.
- Professional inquiries reuse the listing inquiry path: shared honeypot, shared field validation and `InquiryNotifier`, with `type` forced to `consultation`, `listing_id` null, `professional_id` from the route and `user_id` only from the authenticated user. The professional form omits the listing form's type selector.
- `InquiryReceived` shows `Professional: <name>` when `professional_id` is set and keeps the listing line otherwise. Reason: one email template with clear recipient context and no submitter contact details.
- `DemoSeeder` seeds two verified professionals of different types plus one unverified engineer, with one attached to Acme Realty, so both the visible and 404 paths are demonstrable locally.
- No `ProfessionalPolicy` was added: the pages are public, and inquiry access already goes through `InquiryPolicy` / `Inquiry::whereViewableBy()`.

## Pre-launch hardening decisions (prelaunch-app-fixes)
- `App\Models\User` implements `Illuminate\Contracts\Auth\MustVerifyEmail`, and protected routes use the `auth, verified` middleware; unverified users are redirected to the verification notice and new registrations send the verification notification through the `Registered` event.
- Reason: email verification is a launch prerequisite, and the framework middleware is the canonical enforcement point.
- Password defaults come from `AppServiceProvider` (`Password::defaults()`) with min length 10, letters, mixed case, numbers and the uncompromised check only in production.
- Reason: one policy source for registration, password update and reset, with production-only extra strength.
- Demo seeders run only in the `local` or `testing` environments, and super admin creation is available via `php artisan app:create-admin` with `--force`.
- Reason: demo data must never reach a production database.

## Email verification polling (verify-email-polling)
- The verification notice page polls every 5 seconds while visible via `wire:poll.5s.visible="checkVerified"`; the poll re-reads the authenticated user with `fresh()` and redirects only when `hasVerifiedEmail()` is true, and already-verified users are redirected on mount.
- Reason: a signed link may be opened on another device or tab, so the original page must notice verification without custom JavaScript, while the signed link remains the only thing that verifies state.
- All npm/node commands run in Docker with Node 22, and `SESSION_SECURE_COOKIE` stays in `.env.production.example` only.
- Reason: host Node is 18 (the Vite build fails there), and `.env.example` must remain usable over `http://localhost:8000`.

## Verification resend throttle (verify-resend-throttle)
- Verification email resends are limited to 3 per 10 minutes per user via Laravel's `RateLimiter` keyed as `verify-email-resend:<user id>` inside the Volt action, with a friendly remaining-seconds message when limited and no notification sent.
- Reason: an unauthenticated-style burst from one account must not be able to send unlimited mail, while other users stay unaffected; the limit resets with time.
- The resend button uses `wire:loading.attr="disabled"` and a short sent confirmation.
- Reason: prevents duplicate in-flight clicks and gives immediate feedback without custom JavaScript.

## Production infrastructure, checkpoint A (infra-prod)
- Two images from one multi-stage Dockerfile: `app` (PHP-FPM) and `caddy` (web tier). Both are tagged with the same commit SHA at deploy time.
- Reason: one build definition, and a rollback restores a matching pair of immutable images.
- The Caddy image contains the built `public/` directory instead of sharing a volume with the app container.
- Reason: no volume to go stale or to mix files between releases; static assets and PHP code always come from the same build.
- The compose network is pinned to `172.28.0.0/24` and Caddy to `172.28.0.10`; `TRUSTED_PROXIES` names only that address (not `172.16.0.0/12`, not `*`).
- Reason: it is the narrowest list that still survives container restarts, and app/queue/db publish no ports, so FPM is reachable only from that network.
- `docker/php/www.conf` leaves `listen.allowed_clients` unset.
- Reason: an empty value is an empty allow-list, and FPM dropped every connection from Caddy ("There are no allowed addresses").
- `Caddyfile` uses `php_fastcgi app:9000` with no `*.php` matcher and sets `root /var/www/html/public` inside it.
- Reason: php_fastcgi must rewrite unknown paths to `index.php`; with a `*.php` matcher, `/` and `/up` were served by `file_server` and returned 404.
- The base image installs the runtime libraries (`libzip`, `libpng`, `libjpeg-turbo`, `libwebp`, `freetype`, `icu-libs`, `libpq`) as world packages and asserts the extensions load at build time.
- Reason: `apk del .build-deps` also removed libraries that only `-dev` packages had pulled in, so the `.so` files failed to load at runtime.
- `intl` is built with `-j1`, and `opcache` is not recompiled (already part of the official image).
- Reason: the C++ intl build raced with `make -j install` ("cp: can't stat 'modules/*'"); opcache is compiled in.
- The Caddy healthcheck fetches `https://localhost/up` through Caddy to Laravel.
- Reason: a running Caddy with broken FastCGI still passes a process-level check, which is exactly the failure the rehearsal hit.
- `php_fastcgi` passes the client address to FPM as `REMOTE_ADDR`, taken from the connection Caddy accepted, not from a request header.
- Reason: `request()->ip()` is already the client address, so the normal path does not depend on `X-Forwarded-For` at all; Caddy also overwrites any client-supplied `X-Forwarded-For`, so a forged header never reaches Laravel from outside.
- `TRUSTED_PROXIES=172.28.0.10` is therefore defence in depth, not the mechanism that makes the client IP correct: a FastCGI peer that is not Caddy (measured with a throwaway proxy at `172.28.0.5`) sends `X-Forwarded-For: 203.0.113.9` and Laravel keeps the peer address as `request()->ip()`.
- A CDN or load balancer in front of Caddy changes this: Caddy would then see the CDN and pass the CDN address as `REMOTE_ADDR`, so its ranges must be added to Caddy's `trusted_proxies` **and** to `TRUSTED_PROXIES`, or every client shares one IP and one throttle bucket.

## Production hardening, checkpoint A.2
- Docker log driver for every production service: `json-file`, `max-size: 10m`, `max-file: 5`.
- Reason: container stdout is already how logs are collected (`LOG_CHANNEL=stderr`); the rotation caps disk use while keeping them greppable with `docker compose logs`.
- HSTS `max-age=604800` (one week), no `includeSubDomains`, no `preload`.
- Reason: one week is the standard safe default to recover from a bad cert/key while limiting exposure; raise it after launch stability is confirmed.
- Cache-Control `no-store` on all responses except the hashed `/build/*` assets (`public, max-age=31536000, immutable`).
- Reason: authenticated pages, forms and personalized pages are cacheable by path alone is not enough to be safe; no-store-everywhere keeps the privacy posture simple and lets a future split (no-store for authed/form, no-cache for public) happen without a second image build.

## Unified navigation (brand task, U1 task 2)
- One `<x-site-nav>` anonymous Blade component renders the fixed header used by the public, app and guest layouts; shared links for everyone (Listings, Partners, Fees, How verification works, Blog), auth-aware Dashboard (verified only) + Inquiries (allowed roles), avatar menu with logout via the new `layout.logout` Volt component mounted only inside the authenticated branch; the public auto-hide header and its `public-nav-autohide` key were removed.
- Reason: one navbar for every page, plain server-side Blade with Alpine for the mobile menu, and no Livewire payload for guests; logout reuses the existing `App\Livewire\Actions\Logout` action.

## Brand task U1 task 3 decisions (avatar, footer, new public pages)
- `<x-avatar>` computes its background colour deterministically from the user id hash (`crc32b` % 10 palette) and never stores it; accessible name falls back to the email initial when the name is empty.
- Reason: avatar colours must stay stable per user without a column, and the fallback keeps the accessible label meaningful for name-less accounts.
- Palette colours were checked with real WCAG contrast ratios (white text) and all pass ≥ 4.5:1; the avatar palette keeps white text readable.
- Reason: brand task requires ≥ 4.5:1 contrast for every avatar colour against white, measured with the WCAG relative-luminance formula in `AvatarTest`.
- The unified footer is a single `<x-site-footer>` component with `mt-auto` on a flex column min-h-screen wrapper, copyright line last; new public pages (about, mission, vision, services, contact, feedback) are draft content with a visible "Draft: pending review" notice, and `/contact` reads `CONTACT_EMAIL` plus `services.whatsapp.number` with "Contact details coming soon" when both are empty.
- Reason: Phase 1 content is still under review; drafts must not ship as final copy. The unified footer now groups info links into labelled **Support** (About, Contact, Feedback) and **Company** (Mission, Vision, Blog, Services, Partners) groups, rendered inline (row) on mobile through `xl`; the links inside each group are stacked (block) below `xl` and inline from `xl` up (`xl:flex-row xl:gap-4`); group elements remain block-level columns and links go inline from the `sm` breakpoint up.
