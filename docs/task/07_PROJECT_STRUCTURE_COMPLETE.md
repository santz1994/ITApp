# Project Structure & Files Organization - Complete Guide

**Proyek:** ITQuty Microservices Architecture  
**Purpose:** Complete file & folder structure reference  
**Tanggal:** 18 Desember 2025

---

## 🎯 Overview

Dokumen ini menjelaskan **complete structure** untuk project microservices ITQuty, termasuk:
- Monorepo vs Multi-repo strategy
- Complete folder structure
- File organization per service
- API service structure
- Configuration files
- Development tools setup

---

## 📁 Repository Strategy

### Option 1: Monorepo (Recommended) ⭐

```yaml
Strategy: Single repository untuk semua services

Advantages:
  ✓ Easier code sharing
  ✓ Atomic changes across services
  ✓ Simplified versioning
  ✓ Single CI/CD pipeline
  ✓ Better for small-medium teams

Disadvantages:
  ✗ Large repository size
  ✗ Longer clone times
  ✗ All services in one repo

Tools:
  - Nx Workspace (Node.js)
  - Lerna (Node.js)
  - Git submodules
  - Custom scripts
```

### Option 2: Multi-repo

```yaml
Strategy: Separate repository per service

Advantages:
  ✓ Service independence
  ✓ Smaller repositories
  ✓ Team autonomy
  ✓ Independent versioning

Disadvantages:
  ✗ Code duplication
  ✗ Complex dependency management
  ✗ Multiple CI/CD pipelines
  ✗ Harder to maintain consistency

Recommended for:
  - Large teams (100+ developers)
  - Services owned by different teams
  - Mature microservices (production-ready)
```

**Recommendation:** Start dengan **Monorepo**, migrate ke Multi-repo jika needed (Month 12+)

---

## 🗂️ Complete Monorepo Structure

```
itquty-microservices/                    # Root directory
├── .git/                                # Git repository
├── .github/                             # GitHub Actions workflows
│   └── workflows/
│       ├── ci.yml                       # Continuous Integration
│       ├── cd-staging.yml               # Deploy to staging
│       ├── cd-production.yml            # Deploy to production
│       └── test.yml                     # Run tests
│
├── docs/                                # Project documentation
│   ├── architecture/
│   │   ├── adr/                         # Architecture Decision Records
│   │   │   ├── 001-microservices-architecture.md
│   │   │   ├── 002-database-per-service.md
│   │   │   └── 003-api-gateway-choice.md
│   │   ├── diagrams/
│   │   │   ├── system-architecture.png
│   │   │   ├── service-dependencies.png
│   │   │   └── data-flow.png
│   │   └── README.md
│   ├── api/                             # API documentation
│   │   ├── openapi.yaml                 # OpenAPI 3.0 spec
│   │   ├── postman-collection.json
│   │   └── README.md
│   ├── deployment/
│   │   ├── kubernetes/                  # K8s deployment guides
│   │   ├── docker-compose/              # Docker Compose guides
│   │   └── aws/                         # AWS deployment
│   ├── development/
│   │   ├── setup-guide.md
│   │   ├── coding-standards.md
│   │   ├── git-workflow.md
│   │   └── debugging.md
│   └── README.md                        # Main documentation index
│
├── scripts/                             # Utility scripts
│   ├── setup/
│   │   ├── install-dependencies.sh
│   │   ├── init-databases.sh
│   │   └── generate-keys.sh
│   ├── development/
│   │   ├── start-all-services.sh
│   │   ├── stop-all-services.sh
│   │   ├── rebuild-service.sh
│   │   └── logs.sh
│   ├── testing/
│   │   ├── run-tests.sh
│   │   ├── run-integration-tests.sh
│   │   └── load-test.sh
│   ├── deployment/
│   │   ├── deploy-staging.sh
│   │   ├── deploy-production.sh
│   │   └── rollback.sh
│   ├── database/
│   │   ├── backup-all.sh
│   │   ├── restore-backup.sh
│   │   ├── migrate-all.sh
│   │   └── seed-all.sh
│   └── monitoring/
│       ├── check-health.sh
│       ├── check-logs.sh
│       └── alert-test.sh
│
├── shared/                              # Shared code across services
│   ├── types/                           # TypeScript type definitions
│   │   ├── Asset.ts
│   │   ├── Ticket.ts
│   │   ├── User.ts
│   │   ├── ApiResponse.ts
│   │   └── index.ts
│   ├── constants/                       # Shared constants
│   │   ├── apiEndpoints.ts
│   │   ├── statusCodes.ts
│   │   ├── errorCodes.ts
│   │   └── index.ts
│   ├── utils/                           # Shared utilities
│   │   ├── formatters.ts
│   │   ├── validators.ts
│   │   ├── date-helpers.ts
│   │   └── index.ts
│   ├── interfaces/                      # Shared interfaces (PHP)
│   │   ├── ApiClientInterface.php
│   │   ├── RepositoryInterface.php
│   │   └── ServiceInterface.php
│   ├── traits/                          # Shared traits (PHP)
│   │   ├── HasUuid.php
│   │   ├── Searchable.php
│   │   └── Auditable.php
│   └── README.md
│
├── infrastructure/                      # Infrastructure as Code
│   ├── docker/
│   │   ├── php/
│   │   │   ├── Dockerfile
│   │   │   ├── php.ini
│   │   │   └── opcache.ini
│   │   ├── nginx/
│   │   │   ├── Dockerfile
│   │   │   ├── nginx.conf
│   │   │   └── default.conf
│   │   ├── mysql/
│   │   │   ├── Dockerfile
│   │   │   ├── my.cnf
│   │   │   └── init.sql
│   │   └── redis/
│   │       └── redis.conf
│   ├── kubernetes/                      # K8s manifests
│   │   ├── base/
│   │   │   ├── namespace.yaml
│   │   │   ├── configmap.yaml
│   │   │   └── secrets.yaml
│   │   ├── services/
│   │   │   ├── auth-service.yaml
│   │   │   ├── asset-service.yaml
│   │   │   └── ...
│   │   ├── ingress/
│   │   │   └── ingress.yaml
│   │   └── monitoring/
│   │       ├── prometheus.yaml
│   │       └── grafana.yaml
│   ├── terraform/                       # Terraform IaC
│   │   ├── modules/
│   │   ├── environments/
│   │   │   ├── dev/
│   │   │   ├── staging/
│   │   │   └── production/
│   │   └── main.tf
│   └── ansible/                         # Ansible playbooks
│       ├── playbooks/
│       ├── roles/
│       └── inventory/
│
├── api-gateway/                         # API Gateway service
│   ├── src/
│   │   ├── controllers/
│   │   │   ├── AuthController.ts
│   │   │   └── ProxyController.ts
│   │   ├── middleware/
│   │   │   ├── authMiddleware.ts
│   │   │   ├── rateLimitMiddleware.ts
│   │   │   ├── corsMiddleware.ts
│   │   │   └── loggingMiddleware.ts
│   │   ├── routes/
│   │   │   ├── index.ts
│   │   │   ├── authRoutes.ts
│   │   │   ├── assetRoutes.ts
│   │   │   └── ticketRoutes.ts
│   │   ├── services/
│   │   │   ├── AuthService.ts
│   │   │   └── ServiceRegistry.ts
│   │   ├── utils/
│   │   │   ├── jwt.ts
│   │   │   ├── logger.ts
│   │   │   └── httpClient.ts
│   │   ├── config/
│   │   │   ├── services.ts
│   │   │   └── gateway.ts
│   │   └── app.ts
│   ├── tests/
│   │   ├── unit/
│   │   └── integration/
│   ├── Dockerfile
│   ├── package.json
│   ├── tsconfig.json
│   ├── .env.example
│   └── README.md
│
├── services/                            # Microservices directory
│   │
│   ├── auth-service/                    # Auth Service (Port 8001)
│   │   ├── app/
│   │   │   ├── Console/
│   │   │   │   └── Commands/
│   │   │   ├── Exceptions/
│   │   │   │   ├── Handler.php
│   │   │   │   └── InvalidCredentialsException.php
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── PasswordResetController.php
│   │   │   │   │   └── TokenController.php
│   │   │   │   ├── Middleware/
│   │   │   │   │   ├── Authenticate.php
│   │   │   │   │   └── ThrottleRequests.php
│   │   │   │   ├── Requests/
│   │   │   │   │   ├── LoginRequest.php
│   │   │   │   │   ├── RegisterRequest.php
│   │   │   │   │   └── ResetPasswordRequest.php
│   │   │   │   └── Resources/
│   │   │   │       ├── UserResource.php
│   │   │   │       └── TokenResource.php
│   │   │   ├── Models/
│   │   │   │   ├── User.php
│   │   │   │   ├── JwtBlacklist.php
│   │   │   │   └── LoginHistory.php
│   │   │   ├── Services/
│   │   │   │   ├── AuthService.php
│   │   │   │   ├── JwtService.php
│   │   │   │   └── PasswordService.php
│   │   │   ├── Repositories/
│   │   │   │   ├── UserRepository.php
│   │   │   │   └── TokenRepository.php
│   │   │   └── Events/
│   │   │       ├── UserLoggedIn.php
│   │   │       └── UserLoggedOut.php
│   │   ├── bootstrap/
│   │   ├── config/
│   │   │   ├── app.php
│   │   │   ├── auth.php
│   │   │   ├── database.php
│   │   │   └── jwt.php
│   │   ├── database/
│   │   │   ├── migrations/
│   │   │   │   ├── 2025_01_01_000001_create_users_table.php
│   │   │   │   ├── 2025_01_01_000002_create_jwt_blacklist_table.php
│   │   │   │   └── 2025_01_01_000003_create_login_history_table.php
│   │   │   ├── seeders/
│   │   │   │   └── DatabaseSeeder.php
│   │   │   └── factories/
│   │   │       └── UserFactory.php
│   │   ├── routes/
│   │   │   ├── api.php
│   │   │   └── web.php
│   │   ├── storage/
│   │   ├── tests/
│   │   │   ├── Unit/
│   │   │   │   ├── AuthServiceTest.php
│   │   │   │   └── JwtServiceTest.php
│   │   │   ├── Feature/
│   │   │   │   ├── LoginTest.php
│   │   │   │   ├── LogoutTest.php
│   │   │   │   └── TokenRefreshTest.php
│   │   │   └── TestCase.php
│   │   ├── .env.example
│   │   ├── .env.testing
│   │   ├── artisan
│   │   ├── composer.json
│   │   ├── phpunit.xml
│   │   ├── Dockerfile
│   │   ├── docker-compose.yml
│   │   └── README.md
│   │
│   ├── user-service/                    # User Service (Port 8002)
│   │   ├── app/
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   │   ├── UserController.php
│   │   │   │   │   ├── RoleController.php
│   │   │   │   │   └── PermissionController.php
│   │   │   │   ├── Requests/
│   │   │   │   │   ├── CreateUserRequest.php
│   │   │   │   │   ├── UpdateUserRequest.php
│   │   │   │   │   └── AssignRoleRequest.php
│   │   │   │   └── Resources/
│   │   │   │       ├── UserResource.php
│   │   │   │       ├── UserCollection.php
│   │   │   │       └── RoleResource.php
│   │   │   ├── Models/
│   │   │   │   ├── User.php
│   │   │   │   ├── Role.php
│   │   │   │   ├── Permission.php
│   │   │   │   └── AdminOnlineStatus.php
│   │   │   ├── Services/
│   │   │   │   ├── UserService.php
│   │   │   │   ├── RoleService.php
│   │   │   │   └── PermissionService.php
│   │   │   └── Repositories/
│   │   │       ├── UserRepository.php
│   │   │       └── RoleRepository.php
│   │   ├── database/
│   │   │   └── migrations/
│   │   │       ├── 2025_01_01_000001_create_users_table.php
│   │   │       ├── 2025_01_01_000002_create_roles_table.php
│   │   │       ├── 2025_01_01_000003_create_permissions_table.php
│   │   │       └── 2025_01_01_000004_create_role_permission_tables.php
│   │   ├── routes/
│   │   │   └── api.php
│   │   ├── tests/
│   │   ├── Dockerfile
│   │   ├── composer.json
│   │   └── README.md
│   │
│   ├── asset-service/                   # Asset Service (Port 8003) ⭐ CORE
│   │   ├── app/
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   │   ├── AssetController.php
│   │   │   │   │   ├── AssetModelController.php
│   │   │   │   │   ├── AssetTypeController.php
│   │   │   │   │   ├── MaintenanceController.php
│   │   │   │   │   ├── MovementController.php
│   │   │   │   │   ├── QRCodeController.php
│   │   │   │   │   └── RequestController.php
│   │   │   │   ├── Requests/
│   │   │   │   │   ├── CreateAssetRequest.php
│   │   │   │   │   ├── UpdateAssetRequest.php
│   │   │   │   │   ├── AssignAssetRequest.php
│   │   │   │   │   └── ScheduleMaintenanceRequest.php
│   │   │   │   └── Resources/
│   │   │   │       ├── AssetResource.php
│   │   │   │       ├── AssetCollection.php
│   │   │   │       ├── AssetDetailResource.php
│   │   │   │       └── MaintenanceResource.php
│   │   │   ├── Models/
│   │   │   │   ├── Asset.php
│   │   │   │   ├── AssetModel.php
│   │   │   │   ├── AssetType.php
│   │   │   │   ├── AssetMaintenanceLog.php
│   │   │   │   ├── AssetLifecycleEvent.php
│   │   │   │   ├── AssetRequest.php
│   │   │   │   ├── Movement.php
│   │   │   │   └── Pcspec.php
│   │   │   ├── Services/
│   │   │   │   ├── AssetService.php
│   │   │   │   ├── AssignmentService.php
│   │   │   │   ├── MaintenanceService.php
│   │   │   │   ├── QRCodeService.php
│   │   │   │   └── MovementService.php
│   │   │   ├── Repositories/
│   │   │   │   ├── AssetRepository.php
│   │   │   │   ├── MaintenanceRepository.php
│   │   │   │   └── MovementRepository.php
│   │   │   ├── Events/
│   │   │   │   ├── AssetCreated.php
│   │   │   │   ├── AssetAssigned.php
│   │   │   │   ├── AssetReturned.php
│   │   │   │   ├── MaintenanceScheduled.php
│   │   │   │   └── AssetMoved.php
│   │   │   ├── Listeners/
│   │   │   │   ├── SendAssetAssignmentNotification.php
│   │   │   │   ├── LogAssetLifecycleEvent.php
│   │   │   │   └── UpdateAssetStatus.php
│   │   │   └── Jobs/
│   │   │       ├── GenerateQRCode.php
│   │   │       ├── SendMaintenanceReminder.php
│   │   │       └── ExportAssetsToExcel.php
│   │   ├── database/
│   │   │   ├── migrations/
│   │   │   │   ├── 2025_01_01_000001_create_assets_table.php
│   │   │   │   ├── 2025_01_01_000002_create_asset_models_table.php
│   │   │   │   ├── 2025_01_01_000003_create_asset_types_table.php
│   │   │   │   ├── 2025_01_01_000004_create_maintenance_logs_table.php
│   │   │   │   ├── 2025_01_01_000005_create_lifecycle_events_table.php
│   │   │   │   ├── 2025_01_01_000006_create_movements_table.php
│   │   │   │   └── 2025_01_01_000007_create_asset_requests_table.php
│   │   │   ├── seeders/
│   │   │   │   ├── AssetSeeder.php
│   │   │   │   └── AssetTypeSeeder.php
│   │   │   └── factories/
│   │   │       └── AssetFactory.php
│   │   ├── routes/
│   │   │   └── api.php
│   │   ├── storage/
│   │   │   ├── app/
│   │   │   │   ├── public/
│   │   │   │   │   ├── assets/              # Asset images
│   │   │   │   │   ├── qrcodes/             # Generated QR codes
│   │   │   │   │   └── documents/           # Asset documents
│   │   │   └── logs/
│   │   ├── tests/
│   │   │   ├── Unit/
│   │   │   │   ├── AssetServiceTest.php
│   │   │   │   └── QRCodeServiceTest.php
│   │   │   └── Feature/
│   │   │       ├── AssetCrudTest.php
│   │   │       ├── AssetAssignmentTest.php
│   │   │       └── MaintenanceTest.php
│   │   ├── Dockerfile
│   │   ├── composer.json
│   │   └── README.md
│   │
│   ├── ticket-service/                  # Ticket Service (Port 8004) ⭐ CORE
│   │   ├── app/
│   │   │   ├── Http/
│   │   │   │   ├── Controllers/
│   │   │   │   │   ├── TicketController.php
│   │   │   │   │   ├── CommentController.php
│   │   │   │   │   ├── PriorityController.php
│   │   │   │   │   ├── StatusController.php
│   │   │   │   │   ├── TypeController.php
│   │   │   │   │   ├── SLAController.php
│   │   │   │   │   └── CannedResponseController.php
│   │   │   │   └── Resources/
│   │   │   │       ├── TicketResource.php
│   │   │   │       ├── TicketDetailResource.php
│   │   │   │       └── CommentResource.php
│   │   │   ├── Models/
│   │   │   │   ├── Ticket.php
│   │   │   │   ├── TicketComment.php
│   │   │   │   ├── TicketHistory.php
│   │   │   │   ├── TicketsPriority.php
│   │   │   │   ├── TicketsStatus.php
│   │   │   │   ├── TicketsType.php
│   │   │   │   ├── TicketsCannedField.php
│   │   │   │   └── SlaPolicy.php
│   │   │   ├── Services/
│   │   │   │   ├── TicketService.php
│   │   │   │   ├── CommentService.php
│   │   │   │   ├── SLAService.php
│   │   │   │   └── WorkflowService.php
│   │   │   └── Events/
│   │   │       ├── TicketCreated.php
│   │   │       ├── TicketAssigned.php
│   │   │       ├── TicketStatusChanged.php
│   │   │       └── CommentAdded.php
│   │   ├── database/
│   │   │   └── migrations/
│   │   ├── routes/
│   │   ├── tests/
│   │   └── README.md
│   │
│   ├── inventory-service/               # Inventory Service (Port 8005)
│   ├── financial-service/               # Financial Service (Port 8006)
│   ├── meeting-room-service/            # Meeting Room Service (Port 8007)
│   ├── master-data-service/             # Master Data Service (Port 8008)
│   ├── reporting-service/               # Reporting Service (Port 8009)
│   └── notification-service/            # Notification Service (Port 8010)
│
├── frontend/                            # Frontend applications
│   │
│   ├── web-app/                         # React Web Application
│   │   ├── public/
│   │   │   ├── index.html
│   │   │   ├── favicon.ico
│   │   │   └── manifest.json
│   │   ├── src/
│   │   │   ├── api/                     # API clients
│   │   │   │   ├── authApi.ts
│   │   │   │   ├── assetApi.ts
│   │   │   │   ├── ticketApi.ts
│   │   │   │   └── index.ts
│   │   │   ├── components/              # Reusable components
│   │   │   │   ├── common/
│   │   │   │   │   ├── Button/
│   │   │   │   │   │   ├── Button.tsx
│   │   │   │   │   │   ├── Button.test.tsx
│   │   │   │   │   │   └── Button.module.css
│   │   │   │   │   ├── Input/
│   │   │   │   │   ├── Modal/
│   │   │   │   │   ├── Table/
│   │   │   │   │   └── index.ts
│   │   │   │   ├── layout/
│   │   │   │   │   ├── Header.tsx
│   │   │   │   │   ├── Sidebar.tsx
│   │   │   │   │   ├── Footer.tsx
│   │   │   │   │   └── DashboardLayout.tsx
│   │   │   │   └── features/
│   │   │   │       ├── AssetCard.tsx
│   │   │   │       ├── TicketList.tsx
│   │   │   │       └── QRScanner.tsx
│   │   │   ├── features/                # Feature modules
│   │   │   │   ├── auth/
│   │   │   │   │   ├── components/
│   │   │   │   │   ├── pages/
│   │   │   │   │   │   ├── Login.tsx
│   │   │   │   │   │   └── ForgotPassword.tsx
│   │   │   │   │   ├── hooks/
│   │   │   │   │   │   └── useAuth.ts
│   │   │   │   │   └── authSlice.ts
│   │   │   │   ├── assets/
│   │   │   │   │   ├── components/
│   │   │   │   │   ├── pages/
│   │   │   │   │   │   ├── AssetList.tsx
│   │   │   │   │   │   ├── AssetDetail.tsx
│   │   │   │   │   │   └── AssetForm.tsx
│   │   │   │   │   └── assetSlice.ts
│   │   │   │   ├── tickets/
│   │   │   │   │   ├── components/
│   │   │   │   │   ├── pages/
│   │   │   │   │   │   ├── TicketList.tsx
│   │   │   │   │   │   ├── TicketDetail.tsx
│   │   │   │   │   │   └── CreateTicket.tsx
│   │   │   │   │   └── ticketSlice.ts
│   │   │   │   └── dashboard/
│   │   │   │       └── pages/
│   │   │   │           ├── DirectorDashboard.tsx
│   │   │   │           └── ManagementDashboard.tsx
│   │   │   ├── hooks/                   # Custom hooks
│   │   │   │   ├── useAuth.ts
│   │   │   │   ├── usePermissions.ts
│   │   │   │   ├── useDebounce.ts
│   │   │   │   └── usePagination.ts
│   │   │   ├── store/                   # Redux store
│   │   │   │   ├── index.ts
│   │   │   │   └── rootReducer.ts
│   │   │   ├── types/                   # TypeScript types
│   │   │   │   ├── Asset.ts
│   │   │   │   ├── Ticket.ts
│   │   │   │   ├── User.ts
│   │   │   │   └── index.ts
│   │   │   ├── utils/                   # Utility functions
│   │   │   │   ├── formatters.ts
│   │   │   │   ├── validators.ts
│   │   │   │   └── constants.ts
│   │   │   ├── styles/                  # Global styles
│   │   │   │   ├── global.css
│   │   │   │   ├── variables.css
│   │   │   │   └── themes/
│   │   │   ├── routes/                  # Route definitions
│   │   │   │   ├── index.tsx
│   │   │   │   ├── ProtectedRoute.tsx
│   │   │   │   └── PublicRoute.tsx
│   │   │   ├── App.tsx
│   │   │   ├── main.tsx
│   │   │   └── vite-env.d.ts
│   │   ├── .env.example
│   │   ├── .env.development
│   │   ├── .env.production
│   │   ├── package.json
│   │   ├── tsconfig.json
│   │   ├── vite.config.ts
│   │   ├── vitest.config.ts
│   │   ├── Dockerfile
│   │   ├── nginx.conf
│   │   └── README.md
│   │
│   ├── mobile-app/                      # Flutter Mobile App
│   │   ├── android/
│   │   ├── ios/
│   │   ├── lib/
│   │   │   ├── main.dart
│   │   │   ├── app.dart
│   │   │   ├── core/
│   │   │   │   ├── api/
│   │   │   │   │   ├── api_client.dart
│   │   │   │   │   └── endpoints.dart
│   │   │   │   ├── models/
│   │   │   │   │   ├── asset.dart
│   │   │   │   │   ├── ticket.dart
│   │   │   │   │   └── user.dart
│   │   │   │   └── providers/
│   │   │   │       ├── auth_provider.dart
│   │   │   │       └── asset_provider.dart
│   │   │   ├── features/
│   │   │   │   ├── auth/
│   │   │   │   │   ├── presentation/
│   │   │   │   │   ├── data/
│   │   │   │   │   └── domain/
│   │   │   │   ├── assets/
│   │   │   │   │   ├── presentation/
│   │   │   │   │   │   ├── screens/
│   │   │   │   │   │   │   ├── asset_list_screen.dart
│   │   │   │   │   │   │   ├── asset_detail_screen.dart
│   │   │   │   │   │   │   └── scan_qr_screen.dart
│   │   │   │   │   │   └── widgets/
│   │   │   │   │   ├── data/
│   │   │   │   │   └── domain/
│   │   │   │   └── tickets/
│   │   │   ├── shared/
│   │   │   │   ├── widgets/
│   │   │   │   └── utils/
│   │   │   └── config/
│   │   │       ├── routes.dart
│   │   │       └── theme.dart
│   │   ├── test/
│   │   ├── pubspec.yaml
│   │   └── README.md
│   │
│   ├── desktop-app/                     # Electron Desktop App
│   │   ├── electron/
│   │   │   ├── main.ts
│   │   │   ├── preload.ts
│   │   │   └── ipc/
│   │   ├── src/                         # Reuse from web-app
│   │   ├── build/
│   │   ├── package.json
│   │   ├── electron-builder.json
│   │   └── README.md
│   │
│   └── admin-panel/                     # Admin Panel (React Admin)
│       ├── src/
│       │   ├── resources/
│       │   │   ├── users.tsx
│       │   │   ├── assets.tsx
│       │   │   └── tickets.tsx
│       │   ├── Dashboard.tsx
│       │   └── App.tsx
│       ├── package.json
│       └── README.md
│
├── tests/                               # Integration & E2E tests
│   ├── integration/
│   │   ├── auth-user-integration.test.ts
│   │   ├── asset-ticket-integration.test.ts
│   │   └── end-to-end-flow.test.ts
│   ├── e2e/
│   │   ├── user-journey-1.test.ts
│   │   └── user-journey-2.test.ts
│   ├── load/
│   │   ├── k6/
│   │   │   ├── load-test-assets.js
│   │   │   └── load-test-tickets.js
│   │   └── artillery/
│   │       └── scenarios.yml
│   └── contract/
│       ├── consumer/
│       └── provider/
│
├── monitoring/                          # Monitoring & observability
│   ├── prometheus/
│   │   ├── prometheus.yml
│   │   └── alerts.yml
│   ├── grafana/
│   │   ├── dashboards/
│   │   │   ├── system-overview.json
│   │   │   ├── service-metrics.json
│   │   │   └── business-metrics.json
│   │   └── datasources/
│   │       └── prometheus.yml
│   ├── elk/
│   │   ├── logstash/
│   │   │   └── logstash.conf
│   │   ├── elasticsearch/
│   │   │   └── elasticsearch.yml
│   │   └── kibana/
│   │       └── kibana.yml
│   └── jaeger/
│       └── jaeger.yml
│
├── .env.example                         # Environment template
├── .env.development
├── .env.staging
├── .env.production
├── .gitignore
├── .dockerignore
├── docker-compose.yml                   # Main Docker Compose
├── docker-compose.dev.yml               # Development override
├── docker-compose.prod.yml              # Production override
├── Makefile                             # Common commands
├── package.json                         # Root package.json (for shared deps)
├── lerna.json                           # Lerna config (if using)
├── nx.json                              # Nx config (if using)
├── README.md                            # Main README
├── CONTRIBUTING.md                      # Contribution guidelines
├── LICENSE
└── CHANGELOG.md                         # Version history
```

---

## 📝 Key Files Explained

### Root Level Files

#### docker-compose.yml
```yaml
# Main Docker Compose file
version: '3.8'

services:
  # Infrastructure
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - itquty-network

  redis:
    image: redis:7-alpine
    networks:
      - itquty-network

  rabbitmq:
    image: rabbitmq:3-management-alpine
    ports:
      - "5672:5672"
      - "15672:15672"
    networks:
      - itquty-network

  # API Gateway
  api-gateway:
    build: ./api-gateway
    ports:
      - "8000:8000"
    environment:
      - AUTH_SERVICE_URL=http://auth-service:8001
      - ASSET_SERVICE_URL=http://asset-service:8003
    depends_on:
      - auth-service
      - asset-service
    networks:
      - itquty-network

  # Services
  auth-service:
    build: ./services/auth-service
    environment:
      - DB_HOST=mysql
      - REDIS_HOST=redis
    networks:
      - itquty-network

  # ... other services

volumes:
  mysql_data:
  redis_data:

networks:
  itquty-network:
    driver: bridge
```

#### Makefile
```makefile
# Common commands for easy management

.PHONY: help install start stop restart logs clean test

help:
	@echo "Available commands:"
	@echo "  make install   - Install all dependencies"
	@echo "  make start     - Start all services"
	@echo "  make stop      - Stop all services"
	@echo "  make restart   - Restart all services"
	@echo "  make logs      - View logs"
	@echo "  make test      - Run tests"
	@echo "  make clean     - Clean up"

install:
	@echo "Installing dependencies..."
	@cd api-gateway && npm install
	@cd frontend/web-app && npm install
	@cd services/auth-service && composer install
	@cd services/asset-service && composer install
	@echo "Done!"

start:
	@echo "Starting all services..."
	@docker compose up -d
	@echo "Services started!"
	@echo "API Gateway: http://localhost:8000"
	@echo "Web App: http://localhost:3000"

stop:
	@echo "Stopping all services..."
	@docker compose down
	@echo "Services stopped!"

restart:
	@make stop
	@make start

logs:
	@docker compose logs -f

logs-service:
	@docker compose logs -f $(SERVICE)

test:
	@echo "Running tests..."
	@docker compose exec auth-service php artisan test
	@docker compose exec asset-service php artisan test
	@cd frontend/web-app && npm test
	@echo "Tests completed!"

clean:
	@echo "Cleaning up..."
	@docker compose down -v
	@echo "Cleaned!"

migrate:
	@docker compose exec auth-service php artisan migrate
	@docker compose exec asset-service php artisan migrate
	@docker compose exec ticket-service php artisan migrate

seed:
	@docker compose exec auth-service php artisan db:seed
	@docker compose exec asset-service php artisan db:seed
```

---

## 🔧 Service-Level File Structure

### Laravel Service Standard Structure

```
service-name/
├── app/
│   ├── Console/
│   │   └── Commands/          # Artisan commands
│   ├── Exceptions/
│   │   └── Handler.php        # Exception handling
│   ├── Http/
│   │   ├── Controllers/       # API controllers
│   │   ├── Middleware/        # Custom middleware
│   │   ├── Requests/          # Form requests
│   │   └── Resources/         # API resources
│   ├── Models/                # Eloquent models
│   ├── Services/              # Business logic
│   ├── Repositories/          # Data access layer
│   ├── Events/                # Domain events
│   ├── Listeners/             # Event listeners
│   └── Jobs/                  # Queue jobs
├── bootstrap/
├── config/                    # Configuration files
├── database/
│   ├── migrations/            # Database migrations
│   ├── seeders/               # Database seeders
│   └── factories/             # Model factories
├── routes/
│   └── api.php                # API routes
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/
│   ├── Unit/                  # Unit tests
│   ├── Feature/               # Feature tests
│   └── TestCase.php
├── .env.example
├── artisan
├── composer.json
├── phpunit.xml
├── Dockerfile
└── README.md
```

---

## 🌐 API Structure Standards

### RESTful API Endpoint Patterns

```
Service: Auth Service (http://localhost:8001)
├── POST   /api/v1/auth/login
├── POST   /api/v1/auth/logout
├── POST   /api/v1/auth/refresh
├── POST   /api/v1/auth/register
├── POST   /api/v1/auth/forgot-password
├── POST   /api/v1/auth/reset-password
└── GET    /api/v1/auth/me

Service: User Service (http://localhost:8002)
├── GET    /api/v1/users
├── POST   /api/v1/users
├── GET    /api/v1/users/{id}
├── PUT    /api/v1/users/{id}
├── DELETE /api/v1/users/{id}
├── GET    /api/v1/users/{id}/roles
├── POST   /api/v1/users/{id}/roles
├── DELETE /api/v1/users/{id}/roles/{roleId}
└── POST   /api/v1/users/search

Service: Asset Service (http://localhost:8003)
├── GET    /api/v1/assets
├── POST   /api/v1/assets
├── GET    /api/v1/assets/{id}
├── PUT    /api/v1/assets/{id}
├── DELETE /api/v1/assets/{id}
├── POST   /api/v1/assets/{id}/assign
├── POST   /api/v1/assets/{id}/return
├── POST   /api/v1/assets/{id}/maintenance
├── GET    /api/v1/assets/{id}/qrcode
├── GET    /api/v1/assets/{id}/history
├── POST   /api/v1/assets/{id}/move
├── GET    /api/v1/assets/models
├── POST   /api/v1/assets/models
├── GET    /api/v1/assets/types
└── POST   /api/v1/assets/bulk-import

Service: Ticket Service (http://localhost:8004)
├── GET    /api/v1/tickets
├── POST   /api/v1/tickets
├── GET    /api/v1/tickets/{id}
├── PUT    /api/v1/tickets/{id}
├── DELETE /api/v1/tickets/{id}
├── POST   /api/v1/tickets/{id}/assign
├── POST   /api/v1/tickets/{id}/comment
├── GET    /api/v1/tickets/{id}/comments
├── GET    /api/v1/tickets/{id}/history
├── POST   /api/v1/tickets/{id}/close
├── POST   /api/v1/tickets/{id}/reopen
├── GET    /api/v1/tickets/priorities
├── GET    /api/v1/tickets/statuses
└── GET    /api/v1/tickets/types
```

### API Route File Example (Laravel)

```php
// File: services/asset-service/routes/api.php

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetModelController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\QRCodeController;

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    
    // Asset CRUD
    Route::apiResource('assets', AssetController::class);
    
    // Asset Operations
    Route::post('assets/{asset}/assign', [AssetController::class, 'assign']);
    Route::post('assets/{asset}/return', [AssetController::class, 'return']);
    Route::post('assets/{asset}/move', [AssetController::class, 'move']);
    
    // Maintenance
    Route::post('assets/{asset}/maintenance', [MaintenanceController::class, 'schedule']);
    Route::get('assets/{asset}/maintenance', [MaintenanceController::class, 'index']);
    
    // QR Code
    Route::get('assets/{asset}/qrcode', [QRCodeController::class, 'generate']);
    
    // History
    Route::get('assets/{asset}/history', [AssetController::class, 'history']);
    
    // Asset Models
    Route::apiResource('assets/models', AssetModelController::class);
    
    // Bulk Operations
    Route::post('assets/bulk-import', [AssetController::class, 'bulkImport']);
    Route::get('assets/export', [AssetController::class, 'export']);
});

// Public routes (no auth)
Route::prefix('v1')->group(function () {
    Route::get('health', function () {
        return response()->json(['status' => 'ok']);
    });
});
```

---

## 📦 Configuration Management

### Environment Variables Structure

```bash
# File: .env.example

# Application
APP_NAME=ITQuty-Microservices
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# API Gateway
API_GATEWAY_PORT=8000
API_GATEWAY_HOST=localhost

# Services URLs
AUTH_SERVICE_URL=http://localhost:8001
USER_SERVICE_URL=http://localhost:8002
ASSET_SERVICE_URL=http://localhost:8003
TICKET_SERVICE_URL=http://localhost:8004
INVENTORY_SERVICE_URL=http://localhost:8005
FINANCIAL_SERVICE_URL=http://localhost:8006
MEETING_ROOM_SERVICE_URL=http://localhost:8007
MASTER_DATA_SERVICE_URL=http://localhost:8008
REPORTING_SERVICE_URL=http://localhost:8009
NOTIFICATION_SERVICE_URL=http://localhost:8010

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=itquty_db
DB_USERNAME=root
DB_PASSWORD=secret

# Redis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null

# RabbitMQ
RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/

# MinIO / S3
MINIO_ENDPOINT=http://minio:9000
MINIO_ACCESS_KEY=minioadmin
MINIO_SECRET_KEY=minioadmin
MINIO_BUCKET=itquty-assets

# JWT
JWT_SECRET=your-secret-key-change-this
JWT_TTL=60
JWT_REFRESH_TTL=20160

# Email
MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@itquty.com
MAIL_FROM_NAME="${APP_NAME}"

# Monitoring
PROMETHEUS_ENABLED=true
GRAFANA_ENABLED=true

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

---

## 🎯 Summary

### Key Takeaways:

1. **Monorepo Structure** (Recommended)
   - All services dalam 1 repository
   - Easier management untuk small-medium team
   - Shared code di `/shared` folder

2. **Service Structure**
   - Standard Laravel structure per service
   - Clear separation: Controllers, Services, Repositories
   - Event-driven architecture support

3. **API Standards**
   - RESTful patterns
   - Versioning: `/api/v1/`
   - Consistent endpoint naming

4. **Configuration**
   - Environment variables per environment
   - Centralized configuration
   - Docker Compose orchestration

5. **Development Tools**
   - Makefile untuk common commands
   - Docker untuk consistent environment
   - Automated testing structure

---

## 📚 Related Documents

- [02_ARSITEKTUR_DETAIL_MICROSERVICES.md](./02_ARSITEKTUR_DETAIL_MICROSERVICES.md) - Architecture details
- [05_LOCAL_DEPLOYMENT_GUIDE.md](./05_LOCAL_DEPLOYMENT_GUIDE.md) - Setup guide
- [03_MIGRATION_ROADMAP.md](./03_MIGRATION_ROADMAP.md) - Migration timeline

---

**Document Status:** Complete  
**Last Updated:** December 18, 2025  
**Next:** Setup development environment
