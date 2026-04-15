# Arsitektur Detail Microservices - ITQuty System

**Proyek:** ITQuty Asset & Ticket Management System  
**Arsitektur:** Microservices with API Gateway Pattern  
**Tanggal:** 18 Desember 2025

---

## 🏗️ Overview Arsitektur

Arsitektur microservices ITQuty dirancang dengan prinsip:
- **Domain-Driven Design (DDD)**: Setiap service represent bounded context
- **API-First**: Semua komunikasi via REST API
- **Event-Driven**: Async communication via message queue
- **Containerized**: Semua services dalam Docker containers
- **Polyglot Persistence**: Setiap service bisa punya database sendiri

---

## 🎨 High-Level Architecture Diagram

```
┌────────────────────────────────────────────────────────────┐
│                    EXTERNAL LAYER                           │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │   Web    │  │  Mobile  │  │ Desktop  │  │  Public  │   │
│  │   App    │  │   App    │  │   App    │  │   API    │   │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘   │
└───────┼────────────┼─────────────┼─────────────┼──────────┘
        │            │             │             │
        └────────────┴─────────────┴─────────────┘
                          │
        ┌─────────────────┴──────────────────┐
        │       API GATEWAY (Port 8000)       │
        │  - Kong / NGINX / Laravel Gateway   │
        │  - JWT Authentication               │
        │  - Rate Limiting (100 req/min)      │
        │  - Request Routing                  │
        │  - API Composition                  │
        │  - CORS Handling                    │
        └─────────────────┬──────────────────┘
                          │
        ┌─────────────────┴──────────────────┐
        │         SERVICE MESH (Optional)     │
        │  - Service Discovery (Consul)       │
        │  - Load Balancing                   │
        │  - Circuit Breaker                  │
        │  - Retry Logic                      │
        └─────────────────┬──────────────────┘
                          │
        ┌─────────────────┴──────────────────────────┐
        │          MICROSERVICES LAYER                │
        └──────────────────────────────────────────────┘
```

---

## 🔐 1. Authentication Service (Port 8001)

### Tanggung Jawab
- User authentication (login/logout)
- JWT token generation & validation
- Password reset
- Session management
- OAuth integration (future)

### Tech Stack
```yaml
Framework: Laravel 10.x
Database: MySQL (users table)
Cache: Redis (sessions, tokens)
Queue: Redis (email notifications)
```

### API Endpoints
```
POST   /api/v1/auth/login           - User login
POST   /api/v1/auth/logout          - User logout
POST   /api/v1/auth/refresh         - Refresh JWT token
POST   /api/v1/auth/register        - User registration
POST   /api/v1/auth/forgot-password - Send reset link
POST   /api/v1/auth/reset-password  - Reset password
GET    /api/v1/auth/me              - Get current user
```

### Database Schema
```sql
-- Dedicated auth_db
CREATE DATABASE auth_db;

USE auth_db;

-- Users table (replicated from main DB)
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    username VARCHAR(255) UNIQUE,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    email_verified_at TIMESTAMP,
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
);

-- JWT tokens blacklist
CREATE TABLE jwt_blacklist (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    token_hash VARCHAR(64) UNIQUE,
    user_id BIGINT,
    expires_at TIMESTAMP,
    created_at TIMESTAMP,
    INDEX idx_token (token_hash),
    INDEX idx_expires (expires_at)
);

-- Login history
CREATE TABLE login_history (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status ENUM('success', 'failed'),
    created_at TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);
```

### Docker Configuration
```dockerfile
# Dockerfile
FROM php:8.2-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql bcmath
RUN apk add --no-cache redis

COPY . /var/www/auth-service
WORKDIR /var/www/auth-service

CMD ["php-fpm"]
```

---

## 👤 2. User Service (Port 8002)

### Tanggung Jawab
- User profile management
- Role & permission management (Spatie)
- User CRUD operations
- User search & filtering
- Admin online status

### Tech Stack
```yaml
Framework: Laravel 10.x
Database: MySQL (users, roles, permissions)
Cache: Redis (user profiles)
Search: Elasticsearch (optional)
```

### API Endpoints
```
GET    /api/v1/users                - List users
POST   /api/v1/users                - Create user
GET    /api/v1/users/{id}           - Get user detail
PUT    /api/v1/users/{id}           - Update user
DELETE /api/v1/users/{id}           - Delete user
GET    /api/v1/users/{id}/roles     - Get user roles
POST   /api/v1/users/{id}/roles     - Assign roles
GET    /api/v1/users/{id}/permissions - Get permissions
POST   /api/v1/users/search         - Search users
GET    /api/v1/users/online         - Get online admins
```

### Database Schema
```sql
-- Dedicated user_db
CREATE DATABASE user_db;

USE user_db;

-- Complete users table
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255),
    phone VARCHAR(50),
    department VARCHAR(100),
    division_id BIGINT,
    location_id BIGINT,
    avatar VARCHAR(255),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    INDEX idx_department (department),
    INDEX idx_division (division_id),
    INDEX idx_status (status)
);

-- Roles (Spatie)
CREATE TABLE roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Permissions (Spatie)
CREATE TABLE permissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Model has roles
CREATE TABLE model_has_roles (
    role_id BIGINT,
    model_type VARCHAR(255),
    model_id BIGINT,
    PRIMARY KEY (role_id, model_id, model_type),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Model has permissions
CREATE TABLE model_has_permissions (
    permission_id BIGINT,
    model_type VARCHAR(255),
    model_id BIGINT,
    PRIMARY KEY (permission_id, model_id, model_type),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- Role has permissions
CREATE TABLE role_has_permissions (
    permission_id BIGINT,
    role_id BIGINT,
    PRIMARY KEY (permission_id, role_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

-- Admin online status
CREATE TABLE admin_online_status (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    is_online BOOLEAN DEFAULT false,
    last_seen_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_online (is_online)
);
```

---

## 🖥️ 3. Asset Service (Port 8003)

### Tanggung Jawab
- Asset management (CRUD)
- Asset assignment
- Asset maintenance scheduling
- QR code generation
- Asset lifecycle tracking
- Asset movement tracking

### Tech Stack
```yaml
Framework: Laravel 10.x
Database: MySQL (assets, asset_models, asset_types, etc)
File Storage: MinIO / AWS S3 (images, QR codes)
Queue: RabbitMQ (email notifications)
Cache: Redis (asset lists)
```

### API Endpoints
```
GET    /api/v1/assets               - List assets
POST   /api/v1/assets               - Create asset
GET    /api/v1/assets/{id}          - Get asset detail
PUT    /api/v1/assets/{id}          - Update asset
DELETE /api/v1/assets/{id}          - Delete asset
POST   /api/v1/assets/{id}/assign   - Assign asset to user
POST   /api/v1/assets/{id}/maintenance - Schedule maintenance
GET    /api/v1/assets/{id}/qrcode   - Generate QR code
GET    /api/v1/assets/{id}/history  - Get lifecycle history
POST   /api/v1/assets/{id}/move     - Record asset movement
GET    /api/v1/assets/models        - List asset models
GET    /api/v1/assets/types         - List asset types
POST   /api/v1/assets/bulk-import   - Bulk import assets
GET    /api/v1/assets/export        - Export assets to Excel
```

### Database Schema
```sql
-- Dedicated asset_db
CREATE DATABASE asset_db;

USE asset_db;

-- Assets table
CREATE TABLE assets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    asset_tag VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    asset_model_id BIGINT,
    asset_type_id BIGINT,
    serial_number VARCHAR(255),
    purchase_date DATE,
    purchase_cost DECIMAL(15,2),
    supplier_id BIGINT,
    warranty_months INT,
    warranty_type_id BIGINT,
    status_id BIGINT,
    location_id BIGINT,
    assigned_to BIGINT,  -- user_id
    assigned_at TIMESTAMP,
    department VARCHAR(100),
    notes TEXT,
    qr_code_path VARCHAR(255),
    image_path VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    INDEX idx_asset_tag (asset_tag),
    INDEX idx_status (status_id),
    INDEX idx_location (location_id),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_model (asset_model_id)
);

-- Asset models
CREATE TABLE asset_models (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    manufacturer_id BIGINT,
    model_number VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Asset types
CREATE TABLE asset_types (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Asset maintenance logs
CREATE TABLE asset_maintenance_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    asset_id BIGINT,
    maintenance_type ENUM('preventive', 'corrective', 'upgrade'),
    scheduled_date DATE,
    completed_date DATE,
    performed_by BIGINT,
    description TEXT,
    cost DECIMAL(15,2),
    notes TEXT,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    INDEX idx_asset_id (asset_id),
    INDEX idx_scheduled_date (scheduled_date)
);

-- Asset lifecycle events
CREATE TABLE asset_lifecycle_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    asset_id BIGINT,
    event_type ENUM('created', 'assigned', 'returned', 'maintenance', 'repaired', 'upgraded', 'disposed'),
    event_date TIMESTAMP,
    user_id BIGINT,
    description TEXT,
    metadata JSON,
    created_at TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    INDEX idx_asset_id (asset_id),
    INDEX idx_event_type (event_type)
);

-- Asset movements
CREATE TABLE movements (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    asset_id BIGINT,
    from_location_id BIGINT,
    to_location_id BIGINT,
    moved_by BIGINT,
    moved_at TIMESTAMP,
    reason TEXT,
    created_at TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    INDEX idx_asset_id (asset_id),
    INDEX idx_moved_at (moved_at)
);

-- Asset requests
CREATE TABLE asset_requests (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    requester_id BIGINT,
    asset_type_id BIGINT,
    quantity INT DEFAULT 1,
    reason TEXT,
    priority ENUM('low', 'medium', 'high', 'urgent'),
    status ENUM('pending', 'approved', 'rejected', 'fulfilled'),
    approved_by BIGINT,
    approved_at TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_requester (requester_id),
    INDEX idx_status (status)
);
```

---

## 🎫 4. Ticket Service (Port 8004)

### Tanggung Jawab
- Support ticket management
- Ticket assignment & escalation
- SLA tracking
- Comment & history
- Canned responses
- Priority management

### Tech Stack
```yaml
Framework: Laravel 10.x
Database: MySQL (tickets, comments, history)
Queue: RabbitMQ (notifications, SLA alerts)
Cache: Redis (ticket counts, stats)
Search: Elasticsearch (ticket search)
```

### API Endpoints
```
GET    /api/v1/tickets              - List tickets
POST   /api/v1/tickets              - Create ticket
GET    /api/v1/tickets/{id}         - Get ticket detail
PUT    /api/v1/tickets/{id}         - Update ticket
DELETE /api/v1/tickets/{id}         - Delete ticket
POST   /api/v1/tickets/{id}/assign  - Assign ticket
POST   /api/v1/tickets/{id}/comment - Add comment
GET    /api/v1/tickets/{id}/history - Get history
POST   /api/v1/tickets/{id}/close   - Close ticket
POST   /api/v1/tickets/{id}/reopen  - Reopen ticket
GET    /api/v1/tickets/priorities   - List priorities
GET    /api/v1/tickets/statuses     - List statuses
GET    /api/v1/tickets/types        - List types
GET    /api/v1/tickets/canned-responses - Get canned responses
GET    /api/v1/tickets/sla-status   - Get SLA compliance
```

### Database Schema
```sql
-- Dedicated ticket_db
CREATE DATABASE ticket_db;

USE ticket_db;

-- Tickets table
CREATE TABLE tickets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    ticket_number VARCHAR(50) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    ticket_type_id BIGINT,
    priority_id BIGINT,
    status_id BIGINT,
    requester_id BIGINT NOT NULL,
    assigned_to BIGINT,
    department VARCHAR(100),
    location_id BIGINT,
    asset_id BIGINT,  -- related asset
    sla_policy_id BIGINT,
    due_date TIMESTAMP,
    resolved_at TIMESTAMP,
    closed_at TIMESTAMP,
    resolution_notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    INDEX idx_ticket_number (ticket_number),
    INDEX idx_requester (requester_id),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_status (status_id),
    INDEX idx_priority (priority_id),
    INDEX idx_created_at (created_at)
);

-- Ticket comments
CREATE TABLE ticket_comments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    ticket_id BIGINT,
    user_id BIGINT,
    comment TEXT NOT NULL,
    is_internal BOOLEAN DEFAULT false,
    attachments JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    INDEX idx_ticket_id (ticket_id)
);

-- Ticket history
CREATE TABLE ticket_history (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    ticket_id BIGINT,
    user_id BIGINT,
    action VARCHAR(100),
    field_name VARCHAR(100),
    old_value TEXT,
    new_value TEXT,
    created_at TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    INDEX idx_ticket_id (ticket_id)
);

-- Ticket priorities
CREATE TABLE tickets_priorities (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    color VARCHAR(20),
    response_time_hours INT,  -- SLA
    resolution_time_hours INT, -- SLA
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Ticket statuses
CREATE TABLE tickets_statuses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    color VARCHAR(20),
    is_closed BOOLEAN DEFAULT false,
    is_default BOOLEAN DEFAULT false,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Ticket types
CREATE TABLE tickets_types (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    default_priority_id BIGINT,
    default_sla_policy_id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Canned responses
CREATE TABLE tickets_canned_fields (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(100),
    usage_count INT DEFAULT 0,
    created_by BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_category (category)
);

-- SLA policies
CREATE TABLE sla_policies (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    priority_id BIGINT,
    response_time_hours INT,
    resolution_time_hours INT,
    business_hours_only BOOLEAN DEFAULT true,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## 📦 5. Inventory Service (Port 8005)

### Tanggung Jawab
- Spare parts management
- Stock tracking
- Storeroom management
- Low stock alerts
- Inventory movements

### API Endpoints
```
GET    /api/v1/inventory/spares     - List spare parts
POST   /api/v1/inventory/spares     - Create spare part
GET    /api/v1/inventory/spares/{id} - Get spare detail
PUT    /api/v1/inventory/spares/{id} - Update spare
DELETE /api/v1/inventory/spares/{id} - Delete spare
POST   /api/v1/inventory/spares/{id}/adjust - Adjust stock
GET    /api/v1/inventory/spares/low-stock - Get low stock items
GET    /api/v1/inventory/storerooms - List storerooms
POST   /api/v1/inventory/movements  - Record movement
GET    /api/v1/inventory/movements  - List movements
```

---

## 💰 6. Financial Service (Port 8006)

### Tanggung Jawab
- Budget management
- Invoice tracking
- Purchase order processing
- Financial reporting

### API Endpoints
```
GET    /api/v1/financial/budgets    - List budgets
POST   /api/v1/financial/budgets    - Create budget
GET    /api/v1/financial/invoices   - List invoices
POST   /api/v1/financial/invoices   - Create invoice
GET    /api/v1/financial/purchase-orders - List POs
POST   /api/v1/financial/purchase-orders - Create PO
GET    /api/v1/financial/reports/spending - Spending report
```

---

## 🏢 7. Meeting Room Service (Port 8007)

### Tanggung Jawab
- Room booking management
- Approval workflow
- Calendar integration
- Conflict detection

### API Endpoints
```
GET    /api/v1/meeting-rooms/bookings - List bookings
POST   /api/v1/meeting-rooms/bookings - Create booking
PUT    /api/v1/meeting-rooms/bookings/{id} - Update booking
DELETE /api/v1/meeting-rooms/bookings/{id} - Cancel booking
POST   /api/v1/meeting-rooms/bookings/{id}/approve - Approve
POST   /api/v1/meeting-rooms/bookings/{id}/reject - Reject
GET    /api/v1/meeting-rooms/availability - Check availability
GET    /api/v1/meeting-rooms/calendar - Get calendar view
```

---

## 📋 8. Master Data Service (Port 8008)

### Tanggung Jawab
- Locations, Divisions, Manufacturers
- Suppliers, Statuses, Warranty Types
- Import/Export functionality
- Conflict resolution

### API Endpoints
```
# Generic CRUD for all master data
GET    /api/v1/master/{entity}      - List records
POST   /api/v1/master/{entity}      - Create record
GET    /api/v1/master/{entity}/{id} - Get record
PUT    /api/v1/master/{entity}/{id} - Update record
DELETE /api/v1/master/{entity}/{id} - Delete record

# Import/Export
POST   /api/v1/master/import        - Import data
GET    /api/v1/master/export        - Export data
GET    /api/v1/master/conflicts     - List conflicts
POST   /api/v1/master/conflicts/{id}/resolve - Resolve conflict
```

---

## 📊 9. Reporting Service (Port 8009)

### Tanggung Jawab
- Dashboard data aggregation
- KPI calculations
- Report generation
- Data analytics

### API Endpoints
```
GET    /api/v1/reports/dashboard/director - Director dashboard
GET    /api/v1/reports/dashboard/management - Management dashboard
GET    /api/v1/reports/dashboard/sla - SLA dashboard
GET    /api/v1/reports/kpi - KPI metrics
GET    /api/v1/reports/assets - Asset reports
GET    /api/v1/reports/tickets - Ticket reports
GET    /api/v1/reports/activity-logs - Activity logs
POST   /api/v1/reports/generate - Generate custom report
```

---

## 🔔 10. Notification Service (Port 8010)

### Tanggung Jawab
- Email notifications
- Push notifications (future)
- SMS notifications (future)
- Notification history

### API Endpoints
```
POST   /api/v1/notifications/send   - Send notification
GET    /api/v1/notifications        - List notifications
GET    /api/v1/notifications/{id}   - Get notification
PUT    /api/v1/notifications/{id}/read - Mark as read
POST   /api/v1/notifications/preferences - Update preferences
```

---

## 🔄 Inter-Service Communication

### 1. Synchronous (REST API)
```yaml
Use Case: Real-time data needed
Example: 
  - API Gateway calls Asset Service untuk get asset detail
  - Ticket Service calls User Service untuk get user info
  
Protocol: HTTP/HTTPS REST
Format: JSON
Timeout: 5 seconds
Retry: 3 times dengan exponential backoff
```

### 2. Asynchronous (Message Queue)
```yaml
Use Case: Background processing, eventual consistency
Example:
  - Asset assigned → send notification
  - Ticket created → update analytics
  - Maintenance scheduled → send reminder
  
Message Broker: RabbitMQ
Exchange Type: Topic exchange
Queue: Durable, with dead letter queue
Retry: Automatic dengan RabbitMQ retry plugin
```

### 3. Event-Driven Architecture
```yaml
Pattern: Event Sourcing + CQRS (optional)
Events:
  - AssetAssigned
  - TicketCreated
  - MaintenanceScheduled
  - UserRoleChanged
  
Event Store: Event log dalam database
Event Bus: RabbitMQ dengan topic exchange
```

---

## 🗄️ Data Management Strategy

### Option 1: Shared Database (Phase 1)
```
Pros:
✓ Easiest migration dari monolith
✓ ACID transactions tetap work
✓ No data duplication
✓ Existing queries masih work

Cons:
✗ Tight coupling
✗ Single point of failure
✗ Sulit scale independent

Recommendation: Gunakan untuk fase awal (3-6 bulan)
```

### Option 2: Database Per Service (Phase 2)
```
Pros:
✓ True service independence
✓ Can scale databases independently
✓ Technology flexibility (MySQL, PostgreSQL, MongoDB)
✓ No schema conflicts

Cons:
✗ Data duplication
✗ Eventual consistency
✗ Complex joins across services
✗ Need data synchronization

Recommendation: Target architecture jangka panjang
```

### Option 3: Hybrid Approach (Recommended)
```
Phase 1 (Month 1-6):
- Shared database dengan views per service
- Start extracting services dengan read-only access

Phase 2 (Month 7-12):
- Critical services dapat database sendiri
- Implement event-driven sync
- Keep master data shared

Phase 3 (Month 13+):
- Complete database separation
- Implement data replication
- Event sourcing untuk audit trail
```

---

## 🔒 Security Architecture

### 1. Authentication Flow
```
User → API Gateway → Auth Service
                      ↓
                 Generate JWT
                      ↓
                 Return Token
                      ↓
User → API Gateway (dengan token) → Validate Token
                                     ↓
                              Route ke Service
```

### 2. JWT Token Structure
```json
{
  "sub": "user_id",
  "username": "john.doe",
  "email": "john@example.com",
  "roles": ["admin", "user"],
  "permissions": ["view_assets", "edit_tickets"],
  "iat": 1702900000,
  "exp": 1702903600
}
```

### 3. Service-to-Service Authentication
```yaml
Option 1: Service Token
  - Each service has dedicated token
  - Rotate every 30 days
  - Store dalam environment variables

Option 2: mTLS (Mutual TLS)
  - Certificate-based authentication
  - More secure
  - Higher complexity

Recommendation: Start dengan Service Token, migrate ke mTLS
```

---

## 📈 Monitoring & Observability

### 1. Logging Stack
```yaml
Application Logs:
  - Format: JSON
  - Level: DEBUG, INFO, WARNING, ERROR, CRITICAL
  - Storage: Elasticsearch
  
Log Aggregation:
  - Logstash untuk parsing
  - Elasticsearch untuk storage
  - Kibana untuk visualization
  
Retention:
  - Hot: 7 days (fast SSD)
  - Warm: 30 days (slower storage)
  - Cold: 1 year (archive)
```

### 2. Metrics Collection
```yaml
Metrics:
  - Request rate (requests per second)
  - Response time (p50, p95, p99)
  - Error rate (%)
  - CPU usage (%)
  - Memory usage (%)
  - Database connections
  - Queue depth
  
Tools:
  - Prometheus untuk collection
  - Grafana untuk visualization
  - Alert Manager untuk alerts
```

### 3. Distributed Tracing
```yaml
Tool: Jaeger
Trace:
  - API Gateway → Asset Service → Database
  - Show latency per service
  - Identify bottlenecks
  
Sample Rate:
  - Production: 10% of requests
  - Development: 100% of requests
```

---

## 🚀 Deployment Strategy

### Docker Compose (Development & Small Production)
```yaml
version: '3.8'

services:
  # API Gateway
  api-gateway:
    image: itquty/api-gateway:latest
    ports:
      - "8000:8000"
    networks:
      - itquty-network
    depends_on:
      - auth-service
      - asset-service
      - ticket-service

  # Auth Service
  auth-service:
    image: itquty/auth-service:latest
    ports:
      - "8001:8001"
    environment:
      - DB_HOST=mysql-auth
      - REDIS_HOST=redis
    networks:
      - itquty-network
    depends_on:
      - mysql-auth
      - redis

  # Asset Service
  asset-service:
    image: itquty/asset-service:latest
    ports:
      - "8003:8003"
    environment:
      - DB_HOST=mysql-asset
      - REDIS_HOST=redis
      - RABBITMQ_HOST=rabbitmq
    networks:
      - itquty-network

  # Shared Services
  mysql-auth:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: auth_db
    volumes:
      - mysql-auth-data:/var/lib/mysql
    networks:
      - itquty-network

  redis:
    image: redis:7-alpine
    networks:
      - itquty-network

  rabbitmq:
    image: rabbitmq:3-management
    ports:
      - "5672:5672"
      - "15672:15672"
    networks:
      - itquty-network

volumes:
  mysql-auth-data:

networks:
  itquty-network:
    driver: bridge
```

---

## 📝 API Standards

### REST API Design
```yaml
Versioning: /api/v1/, /api/v2/
HTTP Methods:
  GET: Retrieve data
  POST: Create data
  PUT: Update entire resource
  PATCH: Update partial resource
  DELETE: Delete resource

Status Codes:
  200: OK
  201: Created
  204: No Content
  400: Bad Request
  401: Unauthorized
  403: Forbidden
  404: Not Found
  422: Validation Error
  500: Internal Server Error

Response Format:
  Success:
    {
      "success": true,
      "data": {...},
      "message": "Operation successful"
    }
  
  Error:
    {
      "success": false,
      "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid",
        "details": {
          "email": ["The email field is required"]
        }
      }
    }

Pagination:
  {
    "data": [...],
    "meta": {
      "current_page": 1,
      "per_page": 15,
      "total": 150,
      "last_page": 10
    },
    "links": {
      "first": "...",
      "last": "...",
      "next": "...",
      "prev": null
    }
  }
```

---

## 🎯 Performance Targets

### Response Time SLA
```yaml
API Gateway:
  - p50: < 50ms
  - p95: < 200ms
  - p99: < 500ms

Services:
  - p50: < 100ms
  - p95: < 300ms
  - p99: < 1000ms

Database Queries:
  - p50: < 10ms
  - p95: < 50ms
  - p99: < 200ms
```

### Throughput Targets
```yaml
Low Load (< 50 users):
  - 100 requests/second total

Medium Load (50-200 users):
  - 500 requests/second total

High Load (200+ users):
  - 2000 requests/second total
```

### Availability Target
```yaml
SLA: 99.9% uptime
  = Maximum 8.76 hours downtime per year
  = Maximum 43.8 minutes per month
  = Maximum 10.1 minutes per week
```

---

## 📚 Next Steps

1. ✅ **Review Architecture** - Team review & approval
2. 📋 **Create POC** - Build 1-2 services sebagai proof of concept
3. 🔧 **Setup Infrastructure** - Docker, CI/CD, monitoring
4. 🚀 **Start Migration** - Follow migration roadmap
5. 📱 **Build Frontend** - Web, mobile, desktop apps

---

**Document Status:** Draft for Review  
**Next Document:** [03_MIGRATION_ROADMAP.md](./03_MIGRATION_ROADMAP.md)  
**Related:** [04_DATABASE_STRATEGY.md](./04_DATABASE_STRATEGY.md)
