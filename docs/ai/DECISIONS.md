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
