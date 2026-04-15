# 🚀 Quick Reference - ITQuty Microservices Development

**Print this or keep open while coding!**

---

## 📋 Essential Commands

### Docker Commands
```bash
# Start all services
docker compose up -d

# Stop all services
docker compose down

# View logs (all)
docker compose logs -f

# View logs (specific service)
docker compose logs -f auth-service

# Rebuild service
docker compose up -d --build auth-service

# Enter service container
docker compose exec auth-service bash

# Clean restart (removes volumes)
docker compose down -v && docker compose up -d
```

### Laravel Commands (Inside Service Container)
```bash
# Migrations
php artisan migrate
php artisan migrate:fresh --seed
php artisan migrate:rollback

# Testing
php artisan test
php artisan test --filter=TicketTest
php artisan test --coverage

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Queue
php artisan queue:work
php artisan queue:failed
php artisan queue:retry all
```

### Git Commands
```bash
# Feature branch workflow
git checkout -b feature/ticket-service
git add .
git commit -m "feat(ticket): add create ticket endpoint"
git push origin feature/ticket-service

# Update from main
git checkout main
git pull origin main
git checkout feature/ticket-service
git rebase main
```

---

## 🏗️ Project Structure Quick Reference

```
itquty-microservices/
├── .github/
│   └── copilot-instructions.md        ← Copilot instructions
├── docs/task/
│   ├── 09_CUSTOM_ROADMAP...md         ← YOUR roadmap
│   └── 02_ARSITEKTUR...md             ← Architecture
├── services/
│   ├── auth-service/                  ← Port 8001
│   ├── user-service/                  ← Port 8002
│   ├── ticket-service/                ← Port 8004 ⭐ Priority #1
│   └── meeting-room-service/          ← Port 8007 ⭐ Priority #3
├── api-gateway/                       ← Port 8000
├── frontend/
│   ├── web-app/                       ← React
│   └── admin-panel/                   ← React Admin
├── shared/                            ← Shared code
├── infrastructure/                    ← Docker configs
└── docker-compose.yml                 ← Main orchestration
```

---

## 🎯 Service Development Checklist

### Starting New Service:

- [ ] Create service folder: `services/[service-name]`
- [ ] Initialize Laravel: `composer create-project laravel/laravel [service-name]`
- [ ] Install dependencies:
  ```bash
  composer require tymon/jwt-auth
  composer require spatie/laravel-permission
  ```
- [ ] Copy `.env.example` → `.env`
- [ ] Configure database connection (shared DB)
- [ ] Create Dockerfile
- [ ] Add to docker-compose.yml
- [ ] Create migrations
- [ ] Create models with relationships
- [ ] Create controllers (thin, delegate to services)
- [ ] Create service classes (business logic)
- [ ] Create repositories (data access)
- [ ] Create Form Requests (validation)
- [ ] Create API Resources (responses)
- [ ] Add audit logging trait
- [ ] Write unit tests (target 80%+)
- [ ] Write feature tests (API endpoints)
- [ ] Document API (Postman + README)
- [ ] Test locally
- [ ] Code review
- [ ] Merge to main

---

## 💻 Code Templates

### Controller Template
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    public function __construct(
        private TicketService $ticketService
    ) {}

    public function store(CreateTicketRequest $request): JsonResponse
    {
        $ticket = $this->ticketService->create($request->validated());
        
        return response()->json([
            'success' => true,
            'data' => new TicketResource($ticket),
            'message' => 'Ticket created successfully'
        ], 201);
    }
}
```

### Service Template
```php
<?php

namespace App\Services;

use App\Models\Ticket;
use App\Repositories\TicketRepository;
use App\Events\TicketCreated;

class TicketService
{
    public function __construct(
        private TicketRepository $repository
    ) {}

    public function create(array $data): Ticket
    {
        $ticket = $this->repository->create($data);
        
        // Audit logging (automatic via trait)
        // Event dispatch
        event(new TicketCreated($ticket));
        
        return $ticket;
    }
}
```

### Repository Template
```php
<?php

namespace App\Repositories;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

class TicketRepository
{
    public function create(array $data): Ticket
    {
        return Ticket::create($data);
    }
    
    public function findWithRelations(int $id): ?Ticket
    {
        return Ticket::with(['user', 'priority', 'status'])->find($id);
    }
    
    public function getAll(array $filters = []): Collection
    {
        return Ticket::query()
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status_id', $status))
            ->when($filters['priority'] ?? null, fn($q, $priority) => $q->where('priority_id', $priority))
            ->get();
    }
}
```

### Test Template
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class TicketTest extends TestCase
{
    public function test_createTicket_withValidData_returnsTicket(): void
    {
        Sanctum::actingAs(User::factory()->create());
        
        $response = $this->postJson('/api/v1/tickets', [
            'title' => 'Test Ticket',
            'description' => 'Test Description',
            'priority_id' => 1
        ]);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'title', 'description'],
                'message'
            ]);
            
        $this->assertDatabaseHas('tickets', [
            'title' => 'Test Ticket'
        ]);
    }
}
```

---

## 🔒 Security Checklist (Every Feature)

- [ ] JWT authentication required?
- [ ] Permission check added?
- [ ] Input validation implemented?
- [ ] SQL injection prevented? (use Eloquent/Query Builder)
- [ ] XSS prevented? (escape output)
- [ ] Audit log created? (CUD operations)
- [ ] Rate limiting applied?
- [ ] HTTPS/TLS enforced?
- [ ] Sensitive data encrypted?
- [ ] Error messages don't leak info?

---

## 🧪 Testing Checklist

- [ ] Unit tests written (service layer)?
- [ ] Feature tests written (API endpoints)?
- [ ] Happy path tested?
- [ ] Error cases tested?
- [ ] Validation tested?
- [ ] Authentication tested (401)?
- [ ] Authorization tested (403)?
- [ ] Edge cases tested?
- [ ] Coverage ≥80%?
- [ ] All tests passing?

---

## 📝 Documentation Checklist

- [ ] README.md updated?
- [ ] API endpoints documented?
- [ ] Request/response examples added?
- [ ] Environment variables documented?
- [ ] Setup instructions clear?
- [ ] PHPDoc added to public methods?
- [ ] Complex logic commented?
- [ ] OpenAPI spec updated?

---

## 🚨 Common Mistakes to Avoid

1. ❌ **Fat Controllers** → ✅ Move logic to services
2. ❌ **No Validation** → ✅ Use Form Requests
3. ❌ **Direct Model Access** → ✅ Use repositories
4. ❌ **No Tests** → ✅ Write tests first (TDD)
5. ❌ **No Audit Logs** → ✅ Log all CUD operations
6. ❌ **Hardcoded Values** → ✅ Use config files
7. ❌ **N+1 Queries** → ✅ Use eager loading
8. ❌ **No Error Handling** → ✅ Try-catch + proper responses
9. ❌ **Poor Commit Messages** → ✅ Follow convention
10. ❌ **Skip Documentation** → ✅ Document as you code

---

## 🎯 Current Phase Quick Info

**Phase:** Month 1-2 (Preparation)  
**Next Service:** Auth Service (Month 3)  
**Priority Services:** Ticket → User → Meeting Room  
**Database:** Shared MySQL (all services)  
**Budget:** $2,800 total  
**Timeline:** 18 months  

---

## 📞 Quick Help

**Stuck?** Reference these docs:
- Custom Roadmap: `09_CUSTOM_ROADMAP_BASED_ON_QUESTIONNAIRE.md`
- Architecture: `02_ARSITEKTUR_DETAIL_MICROSERVICES.md`
- Database: `04_DATABASE_STRATEGY.md`
- Deployment: `05_LOCAL_DEPLOYMENT_GUIDE.md`

**Copilot Instructions:** `.github/copilot-instructions.md`

---

## 🔗 Useful URLs (Local)

```
API Gateway:        http://localhost:8000
Auth Service:       http://localhost:8001
User Service:       http://localhost:8002
Ticket Service:     http://localhost:8004
Meeting Room:       http://localhost:8007

PHPMyAdmin:         http://localhost:8080
RabbitMQ UI:        http://localhost:15672
Prometheus:         http://localhost:9090
Grafana:            http://localhost:3001

Web App:            http://localhost:3000
Admin Panel:        http://localhost:3002
```

---

## 💡 Pro Tips

1. **Always check Copilot instructions** before asking for code
2. **One service at a time** - don't jump ahead
3. **Test as you go** - don't accumulate technical debt
4. **Document immediately** - don't postpone
5. **Commit often** - small, atomic commits
6. **Review your own code** - pretend you're the reviewer
7. **Follow the roadmap** - resist scope creep
8. **Ask Copilot for tests** - leverage AI for test generation
9. **Use repositories** - keeps controllers clean
10. **KISS principle** - Keep It Simple, Stupid!

---

**Happy Coding! 🚀**

*Last Updated: December 18, 2025*
