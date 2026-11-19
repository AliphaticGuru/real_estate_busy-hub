## Inquiry-customer linking (phase progress)
- Inquiries table has `listing_id`, `professional_id`, and newly added `user_id` (nullable, indexed, nullOnDelete).
- Customers submit inquiries that are traced to their user account; `InquiryController@store` sets `user_id` only when the visitor is authenticated; guests still submit inquiries with `user_id` null.
- `InquiryPolicy.resolve` includes `inquiry->user_id === $user->id` so customers can only see their own inquiries; super_admin and company users retain cross-company access per existing rules.
- Migration: `php artisan make:migration add_user_id_to_inquiries_table` — `user_id`: foreignId('user_id')->nullable()->index()->nullOnDelete().
- Tests: guest inquiry works (user_id null), authenticated inquiry stores user_id, customer A cannot see customer B's inquiries (verified by `Inquiry::where('user_id', $userA->id)->get()`).
- Full suite: 99 passed (240 assertions) on PostgreSQL.

## CI (phase progress)
- GitHub Actions workflow runs the test suite on ubuntu-latest against a `postgres:15-alpine` service container.
- Test suite uses the configured database when `DB_CONNECTION=pgsql` is set (CI / local Postgres); defaults to
  in-memory SQLite otherwise.
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
- The public header is `fixed` and translated off-screen at `scrollY <= 8`; it slides in with a 300ms transition once the page scrolls.
- The mobile menu panel is `absolute top-full` inside the fixed header, so opening it overlays the page instead of pushing content down.
- Reason: keeps the navbar available while reading without causing layout shift.
