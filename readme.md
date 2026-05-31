# QutyApp — Integrated Management System

Booking fasilitas (ruang rapat, kendaraan) dan manajemen inventaris (ATK & sparepart) untuk PT Quty Karunia.

IMPORTANT: This repository enforces a split architecture — Backend MUST be Laravel (API) and Frontend MUST be a React SPA. The legacy Blade views are being migrated to React; do not add new Blade-based UI.

## Stack

| Layer         | Technology                         |
|---------------|------------------------------------|
| Frontend Web  | React 18, Redux Toolkit, Axios     |
| Frontend Mobile | React Native (planned)           |
| Backend       | Laravel 10 (PHP 8.1+)             |
| Database      | MySQL 8                             |
| Cache / Queue | Redis 7                             |
| Container     | Docker Compose                      |

## Quick Start (Docker)

```bash
# 1. Clone & enter the project
git clone https://github.com/santz1994/ITApp.git
cd ITApp

# 2. Copy environment file
cp .env.example .env

# 3. Start containers (PHP-FPM, Nginx, MySQL, Redis)
docker compose up -d

# 4. Install PHP dependencies
docker compose exec app composer install

# 5. Generate app key
docker compose exec app php artisan key:generate

# 6. Run migrations & seed
docker compose exec app php artisan migrate --seed

# 7. Open in browser
# http://localhost:8000
```

### Frontend (React)

```bash
cd frontend
npm install        # or: docker compose run --rm node npm install
npm start          # dev server on http://localhost:3000
```

Notes for developers:
- The React dev server proxies API calls to `http://localhost:8000` (see `frontend/package.json`).
- Production build: set `PUBLIC_URL=/react` before building the frontend so assets resolve under `/react`.
  - PowerShell example: `$env:PUBLIC_URL = '/react'; npm --prefix frontend run build`
  - After build copy `frontend/build` → `public/react` (or use a build script that does this).
  - Ensure Laravel serves the SPA (repo contains a fallback route in `routes/web.php`).

Migration policy:
- Migrate all Blade views to React before removing the Blade files. A backup branch `backup/blades-2026-05-31` exists as a snapshot. Follow module-by-module migration and smoke-tests before deleting blades.

## Project Structure

```
app/
  Http/Controllers/   — Thin controllers (delegate to Services)
  Services/           — Business logic
  Repositories/       — Database access layer
routes/
  modules/            — Per-module route files (admin, meeting-rooms, vehicles, inventory, approvals, profile)
resources/views/      — Blade templates (transitional, migrating to React)
frontend/             — React SPA source (Redux Toolkit, Axios, Router v6)
docker/               — Docker build context (PHP, Nginx)
```

## Modules

- **Meeting Room Booking** — room scheduling with conflict prevention & LCD dashboard
- **Vehicle Booking** — vehicle reservation with approval workflow
- **Inventory Management** — ATK & sparepart stock tracking with low-stock alerts
- **Multi-tier Approval** — configurable approval rules engine
- **User & Role Management** — Spatie-based RBAC with database-driven permissions
- **Notifications** — email & push notifications for booking/status changes
- **Audit Logs** — full CUD operation tracking

## Testing

```bash
# PHPUnit (backend)
docker compose exec app php artisan test

# Jest (frontend)
cd frontend && npm test
```

## Architecture

Controller → Service → Repository pattern (Laravel backend).  
React + Redux Toolkit (frontend SPA).  
DO NOT add server-rendered Blade pages for new features — implement UI in React and expose APIs from Laravel.  
Docker Compose for local development.
