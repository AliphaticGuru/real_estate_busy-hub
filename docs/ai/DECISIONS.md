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