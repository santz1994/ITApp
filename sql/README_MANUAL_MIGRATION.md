Manual migration helper — Adding `supabase_id` column and marking Laravel migration applied

Use this if your local environment can't reach the Supabase Postgres instance.

Files added:
- `sql/2025_10_20_add_supabase_id.sql` — SQL to run in Supabase SQL editor.

Option A — Run migrations locally (if DB is reachable):
1) Ensure your `.env` contains the correct `DATABASE_URL` and `DB_*` values pointing to your Supabase DB.
2) From project root run:

```powershell
cd "d:/Project/ITQuty/quty2"
php artisan config:clear
php artisan migrate --force
```

Option B — Run SQL manually in Supabase (recommended if local DNS/firewall blocks):
1) Open the Supabase dashboard and go to SQL Editor.
2) Create a new query and paste the full contents of `sql/2025_10_20_add_supabase_id.sql`.
3) Run the query. It will:
   - add `supabase_id` column to `public.users` (if missing)
   - create a unique index on `supabase_id` (if missing)
   - ensure `public.migrations` exists and insert a row so Laravel considers the migration applied.

4) Back locally, clear the config and run `php artisan migrate:status` to confirm Laravel thinks the migration is applied:

```powershell
php artisan config:clear
php artisan migrate:status
```

Notes and cautions:
- The SQL creates a basic `migrations` table if it doesn't exist. If your Supabase DB already contains a Laravel `migrations` table with a different schema, review the SQL before running.
- The inserted migration name is `2025_10_20_120000_add_supabase_id_to_users_table`. If your migration file uses a different name, update the SQL accordingly.
- Keep your `SUPABASE_SERVICE_ROLE_KEY` and DB password secret. Do not commit these to public repos.

If you want, I can also:
- Try running `php artisan migrate` again from here after you confirm the host is reachable.
- Switch the middleware to only allow existing users instead of auto-creating them.
