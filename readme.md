# QutyApp — Integrated Management System

Booking fasilitas (ruang rapat, kendaraan) dan manajemen inventaris (ATK & sparepart) untuk PT Quty Karunia.

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

The React dev server proxies API calls to `http://localhost:8000` (configured in `frontend/package.json`).

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
React + Redux Toolkit (frontend with local-first architecture goal).  
Docker Compose for local development.
