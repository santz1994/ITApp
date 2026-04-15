# 🤖 Ready-to-Use Copilot Chat Prompts

**Copy-paste prompts untuk development ITQuty Microservices**

---

## 🏗️ Architecture & Planning Prompts

### 1. Service Design
```
I'm building [SERVICE_NAME] service for ITQuty microservices project. 
Reference: z:/htdocs/quty2/docs/task/02_ARSITEKTUR_DETAIL_MICROSERVICES.md

Requirements:
- Laravel 10+, PHP 8.1+
- Shared MySQL database (no separate DB yet)
- JWT authentication
- Audit logging required
- 80%+ test coverage

Please help me:
1. Design the database schema
2. List required models with relationships
3. Outline controller endpoints (RESTful)
4. Suggest service layer methods
5. Identify security considerations

Current phase: [Month X - Service Name]
```

### 2. API Endpoint Design
```
I need to design RESTful API endpoints for [FEATURE_NAME] in [SERVICE_NAME] service.

Requirements:
- Follow REST best practices
- Include authentication (JWT)
- Include authorization (RBAC with Spatie)
- Return standardized JSON responses
- Add audit logging
- Validate all inputs

Please provide:
1. HTTP method + endpoint URL
2. Request body schema
3. Response format (success + error)
4. Required permissions
5. Example curl commands

Example: POST /api/v1/tickets for creating a ticket
```

### 3. Database Migration
```
I need a Laravel migration for [TABLE_NAME] table in [SERVICE_NAME] service.

Requirements:
- Follow ITQuty naming conventions
- Include proper indexes
- Add foreign keys (shared DB)
- Support soft deletes
- Add audit columns (created_by, updated_by)

Table purpose: [DESCRIBE PURPOSE]

Fields needed:
- [field_name]: [type] - [description]
- [field_name]: [type] - [description]

Generate complete migration with up() and down() methods.
```

---

## 💻 Code Generation Prompts

### 4. Complete Controller
```
Generate Laravel controller for [RESOURCE_NAME] in [SERVICE_NAME] service.

Requirements:
- Thin controller (delegate to service layer)
- Use Form Requests for validation
- Return API Resources
- Follow PSR-12 standards
- Include PHPDoc comments
- Add try-catch error handling
- Standard JSON response format

Endpoints needed:
- index() - List all with filters
- show($id) - Get single resource
- store(Request) - Create new
- update(Request, $id) - Update existing
- destroy($id) - Soft delete

Reference: z:/htdocs/quty2/.github/copilot-instructions.md (Code Standards section)
```

### 5. Service Class
```
Generate service class [ServiceName]Service for [SERVICE_NAME] service.

Requirements:
- Business logic only (no HTTP, no DB direct access)
- Use dependency injection
- Use repository pattern for data access
- Dispatch domain events
- Include audit logging
- Handle transactions
- Add type hints
- PHPDoc comments

Methods needed:
- create(array $data): Model
- update(int $id, array $data): Model
- delete(int $id): bool
- [other methods as needed]

Include complete implementation with error handling.
```

### 6. Repository Class
```
Generate repository class [ModelName]Repository for [SERVICE_NAME] service.

Requirements:
- Data access layer only
- Use Eloquent Query Builder
- Include eager loading
- Support filtering, sorting, pagination
- Optimize queries (avoid N+1)
- Type hints required
- PHPDoc comments

Methods needed:
- create(array $data): Model
- findById(int $id, array $relations = []): ?Model
- update(int $id, array $data): Model
- delete(int $id): bool
- getAll(array $filters = []): Collection
- paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator

Include complete implementation.
```

### 7. Form Request Validation
```
Generate Laravel Form Request for [ACTION_NAME] in [SERVICE_NAME] service.

Requirements:
- Strong validation rules
- Custom error messages (user-friendly)
- Authorization check (authorize() method)
- Sanitize inputs
- Handle file uploads (if needed)

Fields to validate:
- [field_name]: [validation rules] - [description]
- [field_name]: [validation rules] - [description]

Include complete implementation with rules() and messages() methods.
```

### 8. API Resource
```
Generate Laravel API Resource for [MODEL_NAME] in [SERVICE_NAME] service.

Requirements:
- Transform model to JSON
- Include related models (eager loaded)
- Hide sensitive fields (passwords, tokens)
- Format dates consistently
- Add conditional fields based on permissions
- Include HATEOAS links (optional)

Model relationships:
- [relation_name]: [related_model]
- [relation_name]: [related_model]

Include complete toArray() implementation.
```

---

## 🧪 Testing Prompts

### 9. Feature Test
```
Generate PHPUnit feature test for [ENDPOINT_NAME] in [SERVICE_NAME] service.

Requirements:
- Test all HTTP methods
- Test authentication (JWT)
- Test authorization (permissions)
- Test validation rules
- Test success responses
- Test error responses
- Use factories for data
- Database transactions

Endpoint: [HTTP_METHOD] /api/v1/[endpoint]

Test cases needed:
- Happy path (success)
- Unauthenticated (401)
- Unauthorized (403)
- Invalid input (422)
- Not found (404)

Reference format from: z:/htdocs/quty2/.github/copilot-instructions.md
```

### 10. Unit Test
```
Generate PHPUnit unit test for [CLASS_NAME] class in [SERVICE_NAME] service.

Requirements:
- Test public methods only
- Mock dependencies
- Test happy paths
- Test error cases
- Test edge cases
- 100% method coverage
- Clear test names (test_method_scenario_expectedBehavior)

Class: [ClassName]
Methods to test:
- [method_name]: [description of what it does]
- [method_name]: [description of what it does]

Include complete test implementation with setUp() and tearDown().
```

---

## 🔒 Security Prompts

### 11. Audit Logging Implementation
```
I need to implement audit logging for [MODEL_NAME] in [SERVICE_NAME] service.

Requirements (Compliance: ISO 27001 + GDPR + SOC 2):
- Log all CREATE, UPDATE, DELETE operations
- Capture: user_id, action, resource, old_values, new_values, ip_address, user_agent, timestamp
- Cannot be deleted (compliance)
- Store for minimum 1 year
- Support querying for audit reports

Please provide:
1. Auditable trait implementation
2. AuditLog model
3. Migration for audit_logs table
4. How to use in models
5. Query examples for reports
```

### 12. JWT Authentication Setup
```
I need to set up JWT authentication for [SERVICE_NAME] service.

Requirements:
- Use tymon/jwt-auth package
- Access token: 60 minutes
- Refresh token: 14 days
- Token blacklist on logout
- Rate limiting: 5 login attempts/min
- Account lockout: 10 failed attempts = 15 min lockout

Please provide:
1. Installation steps
2. Configuration (config/jwt.php)
3. AuthController implementation (login, logout, refresh, me)
4. Middleware setup
5. Token blacklist implementation
6. Rate limiting implementation
```

### 13. RBAC Implementation
```
I need to implement Role-Based Access Control for [SERVICE_NAME] service.

Requirements:
- Use Spatie Laravel Permission package
- Roles: Super Admin, Admin, Manager, User, Technician
- Permissions: [service].create, [service].read, [service].update, [service].delete
- Check permissions in controllers (middleware)
- Check permissions in service layer
- Audit all role/permission changes

Please provide:
1. Installation steps
2. Roles and permissions seeder
3. Middleware implementation
4. Permission check examples in controllers
5. How to assign roles to users
6. How to check permissions in Blade/React
```

---

## 📦 Docker & Infrastructure Prompts

### 14. Dockerfile for Laravel Service
```
Generate Dockerfile for [SERVICE_NAME] service (Laravel 10).

Requirements:
- PHP 8.1+ with required extensions
- Composer installed
- Production-ready (optimized)
- Multi-stage build (optional)
- Non-root user
- Health check endpoint

Include:
1. Complete Dockerfile
2. .dockerignore
3. docker-compose.yml entry for this service
4. Environment variables needed
```

### 15. Docker Compose Setup
```
Generate docker-compose.yml for ITQuty microservices project.

Services needed:
- MySQL 8.0 (shared database)
- Redis (caching)
- RabbitMQ (message queue)
- API Gateway (Node.js or Laravel)
- Auth Service (Laravel)
- User Service (Laravel)
- [other services as needed]

Requirements:
- Networks for service communication
- Volumes for data persistence
- Environment variables
- Port mappings
- Health checks
- Restart policies

Reference architecture: z:/htdocs/quty2/docs/task/02_ARSITEKTUR_DETAIL_MICROSERVICES.md
```

---

## 🎨 Frontend Prompts

### 16. React Component
```
Generate React component for [COMPONENT_NAME] in ITQuty web app.

Requirements:
- TypeScript
- Functional component (hooks)
- Material-UI (MUI) components
- Responsive design
- Form validation (React Hook Form + Yup)
- API integration (RTK Query)
- Error handling
- Loading states

Component purpose: [DESCRIBE PURPOSE]

Props needed:
- [prop_name]: [type] - [description]
- [prop_name]: [type] - [description]

Include complete implementation with TypeScript interfaces.
```

### 17. Redux Slice
```
Generate Redux Toolkit slice for [FEATURE_NAME] in ITQuty web app.

Requirements:
- TypeScript
- RTK Query for API calls
- Optimistic updates
- Error handling
- Loading states
- Selectors

API endpoints:
- GET /api/v1/[resource]
- POST /api/v1/[resource]
- PUT /api/v1/[resource]/:id
- DELETE /api/v1/[resource]/:id

Include:
1. API slice with RTK Query
2. Feature slice with reducers
3. Selectors
4. TypeScript types
```

---

## 🐛 Debugging & Troubleshooting Prompts

### 18. Debug Existing Code
```
I'm getting [ERROR_MESSAGE] in [FILE_NAME] at line [LINE_NUMBER].

Context:
- Service: [SERVICE_NAME]
- Function: [FUNCTION_NAME]
- Expected behavior: [WHAT SHOULD HAPPEN]
- Actual behavior: [WHAT IS HAPPENING]

Code snippet:
[PASTE CODE HERE]

Error details:
[PASTE ERROR DETAILS]

Please help me:
1. Identify the root cause
2. Explain why it's happening
3. Provide solution with code
4. Suggest how to prevent this in future
```

### 19. Optimize Performance
```
I have performance issue in [SERVICE_NAME] service.

Problem:
- Endpoint: [ENDPOINT]
- Current response time: [X] ms
- Target response time: <500ms
- Database queries: [N+1 issue / slow query / etc]

Code:
[PASTE CODE]

Please help me:
1. Identify performance bottlenecks
2. Suggest optimizations
3. Provide optimized code
4. Explain the improvements
```

---

## 📝 Documentation Prompts

### 20. API Documentation
```
Generate OpenAPI 3.0 specification for [ENDPOINT] in [SERVICE_NAME] service.

Endpoint: [HTTP_METHOD] /api/v1/[endpoint]

Requirements:
- Complete request/response schemas
- Authentication requirements
- Error responses (400, 401, 403, 404, 422, 500)
- Example requests (curl)
- Example responses (JSON)

Include complete OpenAPI YAML specification.
```

### 21. README for Service
```
Generate comprehensive README.md for [SERVICE_NAME] service.

Include:
- Service description and purpose
- Technology stack
- Setup instructions (local development)
- Environment variables
- Database migrations
- API endpoints list
- Testing instructions
- Docker commands
- Troubleshooting common issues
- Contributing guidelines

Reference structure: z:/htdocs/quty2/docs/task/07_PROJECT_STRUCTURE_COMPLETE.md
```

---

## 🚀 Deployment Prompts

### 22. Database Migration Script
```
I need to migrate data from monolith to [SERVICE_NAME] service.

Source:
- Database: itquty_monolith
- Tables: [table1, table2, table3]
- Records: ~[NUMBER] records

Target:
- Database: itquty_microservices (shared)
- Tables: [new_table1, new_table2, new_table3]

Requirements:
- Zero downtime
- Data validation
- Rollback plan
- Progress tracking
- Error handling

Reference strategy: z:/htdocs/quty2/docs/task/04_DATABASE_STRATEGY.md

Generate:
1. Migration script (Laravel Artisan command or SQL)
2. Validation queries
3. Rollback procedure
```

---

## 💡 Usage Tips

**How to Use These Prompts:**

1. **Copy prompt template** dari section yang sesuai
2. **Replace placeholders** [SERVICE_NAME], [ENDPOINT], etc dengan actual values
3. **Paste into Copilot Chat** (Ctrl+Shift+I in VS Code)
4. **Wait for response** and review code
5. **Test generated code** before committing
6. **Iterate if needed** - ask follow-up questions

**Pro Tips:**

- 📚 Always reference documentation files when available
- 🎯 Be specific about requirements
- 🔍 Include context (service name, phase, constraints)
- ✅ Ask for tests along with implementation
- 📝 Request documentation updates
- 🔄 Iterate with follow-ups: "Add error handling", "Include audit logging", etc

**Example Workflow:**

```
1. You: [Use prompt #4 - Complete Controller]
2. Copilot: [Generates controller code]
3. You: "Now generate Form Request validation for this controller"
4. Copilot: [Generates Form Request]
5. You: "Generate feature tests for all these endpoints"
6. Copilot: [Generates tests]
7. You: "Update API documentation with these new endpoints"
8. Copilot: [Updates docs]
```

---

**Happy Coding with AI! 🤖🚀**

*Last Updated: December 18, 2025*
