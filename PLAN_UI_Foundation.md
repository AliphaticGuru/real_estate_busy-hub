# UI Foundation — Revised Plan (with all user fixes)

## 1. Tailwind v4 — design tokens in `@theme { }`
This repo uses Tailwind CSS v4 (`package.json: "tailwindcss": "^4.0.0"`). No project-level `tailwind.config.js`; tokens are defined in `resources/css/app.css` using the `@theme` directive with CSS custom properties.

**`resources/css/app.css`** (updated):
```
@import 'tailwindcss';

@theme {
  --color-brand-50: #eff6ff;
  --color-brand-100: #dbeafe;
  --color-brand-600: #2563eb;
  --color-brand-700: #1d4ed8;
  --color-status-success: #10b981;
  --color-status-warning: #f59e0b;
  --color-status-danger: #ef4444;
  --color-neutral-50: #f8fafc;
  --color-neutral-100: #f1f5f9;
  --color-neutral-600: #475569;
  --color-neutral-900: #0f172a;
  --radius-sm: 0.125rem;
  --radius-md: 0.375rem;
  --radius-lg: 0.5rem;
  --font-sans: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
}

@layer components {
  /* small shared classes only */
  .btn { @apply inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2; }
  .card { @apply bg-white shadow-sm rounded-lg border border-gray-200; }
  .badge { @apply inline-flex items-center rounded-full text-xs font-medium px-2.5 py-0.5; }
  .input { @apply block w-full px-3 py-2 rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500 disabled:opacity-50 disabled:pointer-events-none; }
  .alert { @apply p-3 rounded-md; }
  .page-header { @apply mb-4 text-2xl font-bold text-neutral-900; }
  .empty-state { @apply text-center py-8 text-neutral-500; }
}

@layer utilities {
  /* utilities that need @layer utilities for specificity */
  .bg-brand-100 { @apply bg-[var(--color-brand-100)]; }
  .bg-brand-600 { @apply bg-[var(--color-brand-600)]; }
  .text-brand-600 { @apply text-[var(--color-brand-600)]; }
  .focus-ring-brand-500 { @apply focus-visible:ring-brand-500; }
  .border-radius-sm { @apply rounded-[var(--radius-sm)]; }
  .border-radius-md { @apply rounded-[var(--radius-md)]; }
  .border-radius-lg { @apply rounded-[var(--radius-lg)]; }
  .font-sans { @apply font-[var(--font-sans)]; }
  .color-neutral-600 { @apply text-[var(--color-neutral-600)]; }
  .color-neutral-900 { @apply text-[var(--color-neutral-900)]; }
}
```

## 2. Routes + PROGRESS.md reconciliation

### Full `php artisan route:list --except-vendor` output
```
GET|HEAD   / ............................................................... 
GET|HEAD   blog .......................... blog.index › BlogController@index
GET|HEAD   blog/{slug} ..................... blog.show › BlogController@show
GET|HEAD   companies/{slug} ........ companies.show › CompanyController@show
GET|HEAD   dashboard ............................................. dashboard
GET|HEAD   fees ....................................................... fees
GET|HEAD   listings ............... listings.index › ListingController@index
POST       listings/{listing}/inquiries inquiries.store › InquiryController…
GET|HEAD   listings/{listing}/inquiries/create inquiries.create › InquiryCo…
GET|HEAD   listings/{slug} .......... listings.show › ListingController@show
GET|HEAD   profile ................................................. profile
GET|HEAD   verification ....................................... verification
GET|HEAD   verify .............. verify.index › VerificationController@index
GET|HEAD   verify-email/{id}/{hash} verification.verify › Auth\VerifyEmailC…
POST       verify/listings/{listing} verify.checks.update › VerificationCon…
```

### Current PROGRESS.md checklist (items 1–11)
All items are `[x]` (complete). The checklist items correspond to routes as follows:

| PROGRESS.md item | Corresponding route(s) | Status |
|---|---|---|
| 14 — Listings: public list with filters, detail page, WhatsApp button | `GET /listings`, `GET /listings/{slug}` | ✅ Built & tested |
| 16 — Verification workflow: verifier dashboard, checklist, badge display | `GET /verify`, `POST /verify/listings/{listing}` | ✅ Built & tested |
| 16 — Partner profiles and inquiry forms | `GET /companies/{slug}`, `GET/POST /listings/{listing}/inquiries/create`, `inquiries.store` | ✅ Built & tested |
| 17 — Static/content pages: fees, verification process, blog | `GET /fees`, `GET /verification`, `GET /blog`, `GET /blog/{slug}` | ✅ Built & tested |
| 10 — GitHub Actions CI | `.github/workflows/ci.yml` | ✅ Built & tested |
| 11 — Production deploy files | `docker-compose.prod.yml`, `.github/workflows/deploy.yml`, `backup.sh` | ✅ Built & tested |
| 4 — Auth and roles | Breeze/Volt auth, role enum, policies, seeders | ✅ Built & tested |
| 13 — Dashboard (role-specific) | `GET /dashboard` | ⚠️ UI Foundation dashboards (not yet ticked) |
| 15 — (gap) No item for dashboard UI | — | — |
| 1 — Laravel scaffolded | — | ✅ |
| 2 — Git initialised | — | ✅ |
| 3 — Docker setup | — | ✅ |
| 5 — Database schema and models | — | ✅ |
| 6 — Listings public pages | — | ✅ |
| 7 — Verification workflow | — | ✅ |
| 8 — Partner profiles/inquiry forms | — | ✅ |
| 9 — Static/content pages | — | ✅ |
| 12 — (not used) | — | — |

**Features built but not ticked in PROGRESS.md**: The UI Foundation dashboards (role-specific content under `/dashboard` for super_admin, verifier, company, professional, customer). These are the next item to tick.

**Do NOT map `/dashboard` to partner profiles or inquiry forms** — those are separate features already complete (items 16). The dashboard is a new UI Foundation element.

### Reconciled route → PROGRESS.md mapping (corrected):
- `GET /` → static content pages (item 17, to be replaced by landing page)
- `GET /listings` / `GET /listings/{slug}` → item 14 (listings, ✅)
- `GET /companies/{slug}` → item 16 partner profiles (✅)
- `GET/POST /listings/{listing}/inquiries` → item 16 inquiry forms (✅)
- `GET /dashboard` → UI Foundation dashboards (not yet ticked, new item)
- `GET /fees`, `GET /verification`, `GET /blog`, `GET /blog/{slug}` → item 17 (✅)
- Auth routes → items 4, 13 (✅)

## 3. user_id on inquiries — separate step
**Done before the customer dashboard card.** Requirements:

- **New migration**: `php artisan make:migration add_user_id_to_inquiries_table`
  - `user_id`: foreignId('user_id')->nullable()->index()->nullOnDelete()
- **InquiryController@store**: set `user_id` only when the user is authenticated; guests still allowed (no `user_id` set).
- **InquiryPolicy.update/resolve**: include check for `inquiry->user_id === $user->id` for customer-owned inquiries.
- **Tests** (add to `tests/Feature/InquiryTest.php` or new file):
  - `test_guest_can_submit_inquiry` — guests can still submit (no `user_id` required)
  - `test_authenticated_can_submit_inquiry` — authenticated user's `user_id` is set
  - `test_customer_see_only_own_inquiries` — customer queries `Inquiry::where('user_id', $user->id)`
- **Log in DECISIONS.md**: new decision entry for inquiry-customer linking.

## 4. Placeholders
No `opacity-50 text-gray-300`. Use a ** visible "Placeholder" badge component** with:
- Readable text (e.g., `"Partner placeholder"` in normal text color, not faded)
- Dashed border: `border-dashed border-2 border-gray-300`
- Background: `bg-gray-50`
- Padding and reasonable spacing
- Example: `<span class="badge badge-placeholder">Partner placeholder</span>` with `.badge-placeholder { @apply rounded-full px-2 py-1 text-sm border-2 border-dashed border-gray-300 bg-gray-50 text-neutral-700; }`

## 5. WhatsApp
- Read from `config/services.php`: env var `WHATSAPP_NUMBER` (e.g., `234800000000`).
- **Strip non-digits** before building the link: `preg_replace('/\D/', '', config('services.whatsapp.number'))`.
- **Hide the button when empty**: `@if (config('services.whatsapp.number')) … @endif`.
- **Link**: `https://wa.me/{{ strip_non_digits } }?text={{ rawurlencode('Hello, I have a question') }}`.
- The WhatsApp button on listing pages uses this same config.

## 6. Public layout — Alpine duplication
Livewire bundles Alpine. Do **not** load a second copy. Confirmation:
- `resources/views/layouts/app.blade.php` uses `<livewire:layout.navigation />` — Livewire injects Alpine.
- `resources/views/layouts/public.blade.php` (NEW) will also use Livewire-free Alpine only if needed, or rely on Livewire's global Alpine.
- Both public and app layouts share the same design tokens and components; no duplicate Alpine JS.

## Work order (updated)
1. **Design tokens** in `resources/css/app.css` — `@theme { }` block with `--color-brand-*`, `--color-status-*`, `--radius-*`, `--font-sans`; `@layer components` for small classes; `@layer utilities` for derived utilities.
2. **Reusable Blade components** (all under `resources/views/components/`): button (variants with brand colors), card, badge (verified/pending/in_review/rejected + placeholder), input with focus ring (brand-500), alert (success/warning/danger), page-header, empty-state (dashed border + Placeholder badge).
3. **Layouts**: create `resources/views/layouts/public.blade.php` (header with Alpine mobile menu [via Livewire], footer); keep `resources/views/layouts/guest.blade.php` for auth forms; keep `resources/views/layouts/app.blade.php` for authenticated pages; ensure all three share tokens/components.
4. **Auth pages** (login, register, profile): restyled with new components + brand tokens; Alpine not duplicated.
5. **Landing page** (`welcome.blade.php`): hero with two CTAs, verification steps (3–4 short lines), three service arms (placeholders clearly marked), partner section (placeholders), SEO tags, WhatsApp link from `config('services.whatsapp.number')`, footers with placeholders.
6. **Dashboards** (role-specific, scoped queries, done as separate step): super_admin (companies count, listings by verification status, new inquiries); verifier (listings awaiting review = pending + in_review count); company (own listings by status, own verification status, inquiries for company); professional (own inquiries); customer (their submitted inquiries + browse listings link). All queries scoped by role and `company_id` where applicable. Empty states with "coming soon" links. **user_id on inquiries migration + policy + tests done just before this step.**
7. **README.md** — full rewrite (last).

## Tests (PostgreSQL, 97 passed)
- Landing page returns 200 and contains the app name from `config('app.name')`
- Each role sees its own dashboard content, and cannot see another company's numbers (cross-company check)
- Guests are redirected from the dashboard
- Existing auth tests still pass
- Full suite: 97 passed (234 assertions)

## Constraints
- Tailwind v4: tokens in `@theme { }`, utilities via `@layer utilities`, components via `@layer components` only for small shared classes
- No external font CDNs. System font stack (`--font-sans`).
- No new npm packages without asking. Use only what's in `package.json`.
- Mobile-first: test at 360px width up. Tap targets ≥44px. Readable font sizes. No horizontal scrolling.
- Accessibility: semantic HTML, visible focus states (brand-500 focus ring), sufficient contrast, labels on all inputs.
- No Phase 2 or Phase 3 features.

## Files to create/modify
- `resources/css/app.css` — `@theme { }` block + `@layer components` + `@layer utilities`
- `resources/views/components/` — button, card, badge, input, alert, page-header, empty-state (Placeholder badge)
- `resources/views/layouts/public.blade.php` — NEW
- `resources/views/layouts/guest.blade.php` — restyle (auth forms)
- `resources/views/layouts/app.blade.php` — ensure token consistency
- `database/migrations/add_user_id_to_inquiries_table.php` — new migration (separate commit)
- `app/Http/Controllers/InquiryController.php` — set user_id when authenticated
- `app/Policies/InquiryPolicy.php` — update resolve method
- `tests/Feature/InquiryTest.php` — add guest/authenticated/customer tests
- `config/services.php` — add `whatsapp => ['number' => '']`
- `README.md` — full rewrite
- `docs/ai/PROGRESS.md` — tick UI Foundation item, update current focus, handoff notes
- `docs/ai/DECISIONS.md` — append design token decisions, component conventions, inquiry-customer linking decision