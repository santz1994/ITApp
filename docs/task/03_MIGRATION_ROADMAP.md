# Migration Roadmap - Monolith ke Microservices

**Proyek:** ITQuty System Migration  
**Duration:** 12 Bulan (Dapat disesuaikan: 6-18 bulan)  
**Strategy:** Strangler Fig Pattern (Gradual Migration)  
**Tanggal:** 18 Desember 2025

---

## 🎯 Migration Philosophy

```
"We don't rewrite, we strangle"

Principle:
- Migrate gradually, not big bang
- Keep old system running during migration
- Test extensively before switching
- Always have rollback plan
- Measure everything
```

---

## 📅 Complete 12-Month Roadmap

### 🔧 MONTH 1-2: Foundation & Preparation

#### Week 1-2: Team Setup & Training
```yaml
Activities:
  ✓ Form migration team
  ✓ Assign roles & responsibilities
  ✓ Docker training untuk team
  ✓ Microservices architecture training
  ✓ Setup development environments
  ✓ Create Slack channel untuk coordination
  
Deliverables:
  - Team roster dengan roles
  - Training completion certificates
  - Development environment ready per developer
  - Communication channels active
  
Team Size:
  - 1 Tech Lead
  - 2 Backend Developers
  - 1 Frontend Developer
  - 1 DevOps Engineer
  - 1 QA Engineer
  
Budget:
  - Training: $2,000
  - Tooling licenses: $1,000
  - Infrastructure setup: $500
```

#### Week 3-4: Infrastructure Setup
```yaml
Activities:
  ✓ Setup Docker environment
  ✓ Install & configure Portainer
  ✓ Setup Git repository structure
  ✓ Create CI/CD pipeline skeleton
  ✓ Setup monitoring tools (Prometheus + Grafana)
  ✓ Configure logging (ELK stack)
  ✓ Setup staging environment
  
Deliverables:
  - Docker Compose file untuk development
  - Git mono-repo atau multi-repo structure
  - Jenkins/GitLab CI basic pipeline
  - Monitoring dashboard template
  - Logging aggregation working
  
Infrastructure:
  Development:
    - Local Docker Desktop per developer
  Staging:
    - 1 VM: 4 CPU, 16GB RAM, 100GB SSD
  Production (prepare):
    - 2 VMs: 8 CPU, 32GB RAM, 500GB SSD
```

#### Week 5-6: API Gateway Setup
```yaml
Activities:
  ✓ Choose API Gateway (Kong/NGINX/Laravel Gateway)
  ✓ Implement routing rules
  ✓ Setup JWT authentication
  ✓ Configure rate limiting
  ✓ Implement CORS handling
  ✓ Create API documentation (Swagger)
  
Deliverables:
  - API Gateway container running
  - Authentication working end-to-end
  - API documentation published
  - Rate limiting tested (100 req/min per user)
  - CORS configured untuk frontend
  
Testing:
  - Load test: 500 concurrent users
  - Response time: < 50ms for routing
  - 99.9% uptime during testing
```

#### Week 7-8: Database Strategy Implementation
```yaml
Activities:
  ✓ Analyze current database schema
  ✓ Create database views per service
  ✓ Setup MySQL replication (Master-Slave)
  ✓ Implement automated backup scripts
  ✓ Test backup restoration
  ✓ Create data validation scripts
  
Deliverables:
  - Database view definitions
  - Replication working dengan < 1s lag
  - Daily backups automated
  - Restore procedure documented & tested
  - Data validation SQL scripts
  
Backup Strategy:
  - Daily full backup (2 AM)
  - Hourly incremental backup
  - 30 days retention local
  - 90 days retention cloud
```

---

### ⚡ MONTH 3-4: First Service Extraction

#### Week 9-10: Auth Service (First Microservice)
```yaml
Why Auth First:
  ✓ Foundation untuk all other services
  ✓ Isolated functionality (easier)
  ✓ Small code base
  ✓ High value (security improvement)

Activities:
  Day 1-2:
    - Create auth-service Laravel project
    - Setup Dockerfile
    - Configure database connection
    - Implement JWT token generation
  
  Day 3-5:
    - Migrate authentication logic
    - Implement /login endpoint
    - Implement /logout endpoint
    - Implement /refresh-token endpoint
    - Implement /me endpoint
  
  Day 6-8:
    - Integration tests
    - API Gateway routing setup
    - Token validation middleware
    - Documentation
  
  Day 9-10:
    - Deploy to staging
    - End-to-end testing
    - Performance testing
    - Security audit

Deliverables:
  - auth-service container
  - 5 API endpoints working
  - 90% test coverage
  - API documentation
  - Performance: < 100ms response time

Migration Path:
  Old: monolith/auth/login → New: API Gateway → Auth Service
  Parallel running: Both working simultaneously
  Gradual switch: 10% → 50% → 100% traffic
```

#### Week 11-12: User Service
```yaml
Activities:
  Day 1-3:
    - Extract User model & relationships
    - Migrate Spatie Permission tables
    - Setup Redis cache
    - Implement user CRUD endpoints
  
  Day 4-7:
    - Role & permission management endpoints
    - User search functionality
    - Profile management
    - Admin online status
  
  Day 8-10:
    - Integration with Auth Service
    - Testing with existing data
    - API Gateway routing
    - Documentation

Deliverables:
  - user-service container
  - 15+ API endpoints
  - Redis caching implemented
  - Integration tests passing
  
Dependencies:
  - Auth Service (for authentication)
  - Master Data Service (for locations, divisions)
```

#### Week 13-14: Notification Service
```yaml
Why Notification Next:
  ✓ Independent service (no dependencies)
  ✓ Async processing (good for microservices)
  ✓ Immediate value (better email handling)
  ✓ Learning opportunity for message queues

Activities:
  Day 1-3:
    - Setup RabbitMQ
    - Create notification-service
    - Implement email sending
    - Setup queue workers
  
  Day 4-7:
    - Migrate email templates
    - Implement notification history
    - User notification preferences
    - Push notification foundation (for mobile future)
  
  Day 8-10:
    - Integration testing
    - Load testing (1000 emails/min)
    - Monitoring & alerting
    - Documentation

Deliverables:
  - notification-service container
  - RabbitMQ configured
  - Email queue working
  - 99% delivery rate
  - < 5 second send time
```

#### Week 15-16: Testing & Optimization
```yaml
Activities:
  ✓ Full integration testing (3 services)
  ✓ Load testing
  ✓ Security penetration testing
  ✓ Performance optimization
  ✓ Documentation review
  ✓ Training untuk team (how to use new services)

Deliverables:
  - Test report
  - Performance benchmarks
  - Security audit report
  - Updated documentation
  - Team training completed

Success Criteria:
  - All 3 services passing tests
  - Response time < 200ms (p95)
  - Zero critical security issues
  - 100% test coverage for critical paths
```

---

### 🚀 MONTH 5-6: Core Business Services

#### Week 17-20: Asset Service (Complex, Core Business)
```yaml
Complexity: HIGH (banyak relationships)
Priority: CRITICAL

Phase 1 (Week 17-18): Basic CRUD
  Activities:
    - Extract Asset models (Asset, AssetModel, AssetType)
    - Migrate asset CRUD endpoints
    - Implement basic search
    - File upload untuk images & QR codes
    - Integration dengan MinIO/S3
  
  Deliverables:
    - Basic asset CRUD working
    - File storage implemented
    - QR code generation

Phase 2 (Week 19): Advanced Features
  Activities:
    - Asset assignment logic
    - Maintenance scheduling
    - Movement tracking
    - Lifecycle events
    - Asset requests
  
  Deliverables:
    - Advanced features working
    - Business logic preserved
    - Email notifications integrated

Phase 3 (Week 20): Testing & Optimization
  Activities:
    - Data migration validation
    - Performance testing
    - Load testing (10,000+ assets)
    - Parallel running with monolith
  
  Success Metrics:
    - Query time < 100ms for list
    - < 50ms for single asset
    - Support 100,000+ assets
    - Zero data loss during migration

Data Migration Strategy:
  1. Copy existing assets ke asset_db
  2. Setup two-way sync (CDC)
  3. Validate data integrity daily
  4. Switch reads to new service (week 19)
  5. Switch writes to new service (week 20)
  6. Monitor 1 week before decommission
```

#### Week 21-24: Ticket Service (Complex, High Volume)
```yaml
Complexity: HIGH (banyak features, high traffic)
Priority: CRITICAL

Phase 1 (Week 21-22): Core Ticketing
  Activities:
    - Extract Ticket models & relationships
    - Migrate ticket CRUD
    - Comments functionality
    - Ticket history tracking
    - Status management
  
  Deliverables:
    - Ticket CRUD complete
    - Comment system working
    - History audit trail

Phase 2 (Week 23): Advanced Features
  Activities:
    - SLA policy implementation
    - Priority management
    - Canned responses
    - Ticket assignment logic
    - Escalation rules
  
  Deliverables:
    - SLA tracking working
    - Automated workflows
    - Email notifications

Phase 3 (Week 24): Testing & Performance
  Activities:
    - Data migration (potentially 100K+ tickets)
    - Performance optimization
    - Load testing (500 concurrent users)
    - Search optimization (Elasticsearch?)
  
  Success Metrics:
    - List tickets < 200ms
    - Create ticket < 100ms
    - Search tickets < 500ms
    - Support 1M+ tickets
    - 99.9% uptime

Migration Challenges:
  - Large data volume
  - High traffic service
  - Complex relationships
  
  Mitigation:
    - Migrate in batches (1000 tickets/batch)
    - Read from old DB during migration
    - Switch gradually (canary deployment)
```

---

### 📦 MONTH 7-8: Supporting Services

#### Week 25-26: Master Data Service
```yaml
Scope:
  - Locations, Divisions, Departments
  - Manufacturers, Suppliers
  - Asset Types, Warranty Types
  - Statuses, Priorities
  - All dropdown/reference data

Activities:
  Week 25:
    - Extract all master data tables
    - Generic CRUD endpoints
    - Import/Export functionality
    - Conflict resolution
  
  Week 26:
    - Data validation
    - Bulk operations
    - API for other services
    - Caching strategy (rarely changes)

Deliverables:
  - master-data-service
  - Generic API for all entities
  - Import/Export working
  - 99% Redis cache hit rate
```

#### Week 27-28: Inventory Service (Spares)
```yaml
Activities:
  - Extract spare parts management
  - Stock tracking
  - Storeroom management
  - Low stock alerts
  - Movement history

Deliverables:
  - inventory-service
  - Real-time stock levels
  - Alert system for low stock
  - Integration with Asset Service
```

#### Week 29-30: Financial Service
```yaml
Activities:
  - Budget management
  - Invoice tracking
  - Purchase orders
  - Financial reports
  - Approval workflows

Deliverables:
  - financial-service
  - Budget tracking
  - Invoice management
  - Financial reports
```

#### Week 31-32: Meeting Room Service
```yaml
Activities:
  - Room booking system
  - Calendar integration
  - Approval workflow
  - Conflict detection
  - Email notifications

Deliverables:
  - meeting-room-service
  - Booking system
  - Calendar view
  - Approval workflow
```

---

### 📊 MONTH 9-10: Reporting & Optimization

#### Week 33-35: Reporting Service
```yaml
Complexity: HIGH (aggregates data from all services)

Activities:
  Week 33:
    - Data aggregation from all services
    - Dashboard APIs
    - KPI calculations
    - Real-time metrics
  
  Week 34:
    - Historical data analysis
    - Custom report builder
    - Scheduled reports
    - Export functionality
  
  Week 35:
    - Performance optimization
    - Caching strategy
    - Data warehouse consideration
    - Chart data APIs

Deliverables:
  - reporting-service
  - Dashboard endpoints
  - KPI APIs
  - Report generation
  - Data export

Considerations:
  - May need separate read database
  - Consider CQRS pattern
  - Implement query optimization
  - Use materialized views
```

#### Week 36-38: System-Wide Optimization
```yaml
Activities:
  - Performance profiling all services
  - Database query optimization
  - Cache implementation review
  - API response time optimization
  - Resource usage optimization
  - Load balancing setup

Deliverables:
  - Performance report
  - Optimization recommendations implemented
  - Load balancer configured
  - Auto-scaling rules

Target Improvements:
  - 30% reduction in response time
  - 40% reduction in database load
  - 50% improvement in throughput
  - 20% reduction in resource usage
```

#### Week 39-40: Security Hardening
```yaml
Activities:
  - Security audit all services
  - Implement service-to-service auth
  - API rate limiting refinement
  - Input validation review
  - SQL injection prevention
  - XSS prevention
  - CSRF protection
  - Penetration testing

Deliverables:
  - Security audit report
  - Vulnerabilities fixed
  - Security best practices documented
  - Penetration test results
```

---

### 🎨 MONTH 11-12: Frontend Development & Finalization

#### Week 41-44: Web Application (React)
```yaml
Activities:
  Week 41-42:
    - Setup React project
    - Implement authentication
    - Asset management UI
    - Ticket management UI
  
  Week 43:
    - Dashboard implementation
    - Reports UI
    - All remaining features
  
  Week 44:
    - Testing & bug fixes
    - Performance optimization
    - Responsive design
    - Accessibility compliance

Deliverables:
  - Complete web application
  - 100% feature parity dengan monolith
  - Responsive design
  - 90%+ test coverage
```

#### Week 45-46: Mobile App Foundation (Flutter)
```yaml
Scope: MVP (60% features)

Activities:
  - Setup Flutter project
  - Authentication
  - View assets (read-only)
  - Create/view tickets
  - QR code scanning
  - Basic dashboard

Deliverables:
  - Mobile app MVP
  - iOS & Android builds
  - Core features working
  - Ready for beta testing
```

#### Week 47-48: Final Integration & Testing
```yaml
Activities:
  Week 47:
    - Full system integration testing
    - User acceptance testing (UAT)
    - Performance testing (production load)
    - Security final review
    - Documentation review & update
  
  Week 48:
    - Bug fixes from UAT
    - Performance tuning
    - Training untuk end users
    - Deployment preparation
    - Rollback plan finalization

Deliverables:
  - UAT sign-off
  - Zero critical bugs
  - Training materials
  - Deployment runbook
  - Rollback procedures

Go-Live Checklist:
  □ All services tested & approved
  □ Database migration validated
  □ Backup & restore tested
  □ Monitoring & alerting active
  □ Team trained
  □ Users trained
  □ Support plan ready
  □ Rollback plan tested
```

---

## 🎯 Go-Live Strategy (End of Month 12)

### Pre-Production Checklist

```yaml
Infrastructure:
  □ Production environment ready
  □ All services deployed
  □ Database replication active
  □ Backup automation running
  □ Monitoring & alerting configured
  □ SSL certificates installed
  □ Domain DNS configured
  □ Load balancer configured

Application:
  □ All features tested
  □ Performance benchmarks met
  □ Security audit passed
  □ No critical bugs
  □ Documentation complete
  □ API documentation published

Data:
  □ Data migration completed
  □ Data validation 100% passed
  □ Backup taken before cutover
  □ Sync between old & new systems working

Team:
  □ Development team ready
  □ Support team trained
  □ End users trained
  □ On-call schedule defined
  □ Communication plan ready
```

### Go-Live Phases

#### Phase 1: Soft Launch (Day 1-3)
```yaml
Participants:
  - IT team only (10-20 users)
  - Beta testers (5-10 power users)

Activities:
  - Monitor system closely
  - Fix critical bugs immediately
  - Collect feedback
  - Adjust configuration

Success Criteria:
  - System stable for 3 days
  - No data loss
  - < 5 critical bugs
  - Response time acceptable
```

#### Phase 2: Limited Rollout (Day 4-7)
```yaml
Participants:
  - IT department (all)
  - Management team
  - ~50 users total

Activities:
  - Monitor usage patterns
  - Address issues proactively
  - Continue bug fixes
  - Gather feedback

Success Criteria:
  - System stable under load
  - User feedback positive (>80%)
  - Performance meets targets
```

#### Phase 3: Full Rollout (Day 8-14)
```yaml
Participants:
  - All users (200-500 users)

Activities:
  - Open to all users
  - 24/7 support available
  - Monitor everything
  - Quick response to issues

Success Criteria:
  - 99% uptime
  - < 0.1% error rate
  - Users adapted to new system
  - Old system can be decommissioned
```

#### Phase 4: Stabilization (Week 3-4)
```yaml
Activities:
  - Monitor system health
  - Optimize performance
  - Address minor issues
  - Collect improvement suggestions
  - Document lessons learned

Deliverables:
  - Post-launch report
  - Lessons learned document
  - Improvement backlog
  - Updated documentation
```

---

## 📊 Success Metrics & KPIs

### Technical Metrics

```yaml
Performance:
  ✓ API response time: < 200ms (p95)
  ✓ Database query time: < 50ms (p95)
  ✓ Page load time: < 2 seconds
  ✓ Time to First Byte: < 100ms

Reliability:
  ✓ Uptime: 99.9% (8.76 hours/year downtime)
  ✓ Error rate: < 0.1%
  ✓ Mean Time to Recovery: < 15 minutes

Scalability:
  ✓ Support 500 concurrent users
  ✓ Handle 2000 requests/second
  ✓ Database: 1M+ records per table
  ✓ Storage: Unlimited (cloud-based)

Security:
  ✓ Zero critical vulnerabilities
  ✓ 100% traffic encrypted (HTTPS)
  ✓ Audit log 100% coverage
  ✓ RBAC properly implemented
```

### Business Metrics

```yaml
Development:
  ✓ New feature delivery: 2x faster
  ✓ Bug fix time: 50% reduction
  ✓ Deployment frequency: Daily vs monthly
  ✓ Lead time: < 1 day

User Experience:
  ✓ User satisfaction: > 85%
  ✓ Task completion time: 30% reduction
  ✓ Training time: 50% reduction
  ✓ Support tickets: 40% reduction

Cost:
  ✓ Infrastructure cost: Track monthly
  ✓ Development cost: Compare velocity
  ✓ Maintenance cost: 30% reduction target
  ✓ TCO: Positive ROI in 24 months
```

---

## ⚠️ Risk Management

### High-Risk Areas

```yaml
Risk 1: Data Loss During Migration
  Probability: Low (with proper planning)
  Impact: CRITICAL
  Mitigation:
    - Multiple backups before migration
    - Two-way sync during parallel running
    - Extensive validation
    - Quick rollback capability
  
Risk 2: Performance Degradation
  Probability: Medium
  Impact: HIGH
  Mitigation:
    - Load testing before launch
    - Performance monitoring
    - Auto-scaling configured
    - Optimization sprint before launch

Risk 3: Service Downtime
  Probability: Medium
  Impact: HIGH
  Mitigation:
    - Health checks for all services
    - Auto-restart on failure
    - Redundancy (multiple instances)
    - Quick rollback to monolith

Risk 4: Team Learning Curve
  Probability: High
  Impact: MEDIUM
  Mitigation:
    - Early training
    - Dedicated learning time
    - Pair programming
    - External consultants if needed

Risk 5: Budget Overrun
  Probability: Medium
  Impact: MEDIUM
  Mitigation:
    - Monthly budget reviews
    - Prioritize features
    - Use open-source tools
    - Cloud cost monitoring
```

### Rollback Plan

```yaml
Scenario: Critical issue in production

Step 1: Stop the Bleeding (5 minutes)
  - Switch API Gateway to route to old monolith
  - All traffic back to known-good system
  - Users experience minimal disruption

Step 2: Assess Damage (15 minutes)
  - Check data integrity
  - Identify root cause
  - Estimate fix time

Step 3: Communicate (Immediate)
  - Alert stakeholders
  - Update status page
  - Inform users

Step 4: Fix or Stay on Old System
  If fix < 2 hours:
    - Fix in microservices
    - Test thoroughly
    - Gradual switch back
  
  If fix > 2 hours:
    - Stay on monolith
    - Schedule fix for maintenance window
    - Post-mortem analysis

Step 5: Post-Mortem (Within 24 hours)
  - Document what happened
  - Identify prevention measures
  - Update runbooks
  - Share learnings with team
```

---

## 💰 Budget Estimation

### Infrastructure Costs (Monthly)

```yaml
Development:
  - Docker Desktop licenses: $0 (free tier OK)
  - Cloud storage (backups): $50
  - Monitoring tools: $0 (Prometheus/Grafana free)
  - Development VMs: $100
  Total: $150/month

Staging:
  - VM (4 CPU, 16GB RAM): $200
  - Database: Included
  - Storage: $50
  - Network: $20
  Total: $270/month

Production:
  - VMs (2x 8 CPU, 32GB RAM): $800
  - Database (managed): $300
  - Redis (managed): $100
  - RabbitMQ: $100
  - Load Balancer: $50
  - Storage (1TB): $100
  - Backup storage: $50
  - CDN: $50
  - Monitoring (paid tier): $100
  Total: $1,650/month

Annual Infrastructure: ~$2,500 (dev) + $24,000 (prod) = $26,500
```

### One-Time Costs

```yaml
Training & Licenses:
  - Team training: $5,000
  - Software licenses: $2,000
  - Consultant fees (if needed): $10,000
  Total: $17,000

Migration:
  - Data migration tools: $1,000
  - Testing tools: $2,000
  - Security audit: $5,000
  Total: $8,000

Total One-Time: $25,000
```

### Personnel Costs (12 months)

```yaml
Assuming existing team (no new hires):
  - Opportunity cost (could work on features instead)
  - Estimate: 50% of team time for 12 months
  
If hiring contractors:
  - Backend Dev: $80/hour x 1000 hours = $80,000
  - Frontend Dev: $70/hour x 800 hours = $56,000
  - DevOps: $90/hour x 600 hours = $54,000
  - Total: $190,000
```

### Total Budget Range

```yaml
Conservative (existing team):
  - Infrastructure: $26,500
  - One-time: $25,000
  - Total: $51,500

Aggressive (with contractors):
  - Infrastructure: $26,500
  - One-time: $25,000
  - Personnel: $190,000
  - Total: $241,500

Recommended (hybrid):
  - Infrastructure: $26,500
  - One-time: $25,000
  - 1 contractor (DevOps): $54,000
  - Total: $105,500
```

---

## ✅ Post-Migration (Month 13+)

### Continuous Improvement

```yaml
Month 13-15: Stability & Optimization
  - Monitor performance
  - Optimize slow queries
  - Add missing features
  - Improve documentation

Month 16-18: Mobile App Complete
  - Finish mobile app (100% features)
  - Offline sync
  - Push notifications
  - Beta release → Production

Month 19-21: Desktop App
  - Electron app development
  - Native features
  - Auto-update mechanism
  - Beta release → Production

Month 22-24: Advanced Features
  - AI/ML for asset prediction
  - Advanced analytics
  - GraphQL API
  - Real-time collaboration
```

---

## 📚 Documentation Deliverables

### For Developers
```
1. Architecture Decision Records (ADRs)
2. API Documentation (Swagger)
3. Service README files
4. Database schema documentation
5. Deployment runbooks
6. Troubleshooting guides
7. Code standards & conventions
```

### For Operations
```
1. Infrastructure setup guide
2. Monitoring & alerting guide
3. Backup & restore procedures
4. Disaster recovery plan
5. Security procedures
6. Scaling guide
7. On-call playbook
```

### For Users
```
1. User manual (updated)
2. Video tutorials
3. FAQ
4. Release notes
5. Training materials
```

---

## 🎓 Lessons Learned Template

```yaml
After each major milestone, document:

What Went Well:
  - List successes
  - What to repeat

What Didn't Go Well:
  - List problems
  - Root causes

What We Learned:
  - Key insights
  - Process improvements

Action Items:
  - Concrete next steps
  - Assigned owners
  - Due dates
```

---

## ✅ Final Summary

**Migration Path:**
```
Month 1-2:  Foundation (Infrastructure, Training)
Month 3-4:  First Services (Auth, User, Notification)
Month 5-6:  Core Services (Asset, Ticket)
Month 7-8:  Support Services (Master Data, Inventory, Financial, Meeting Room)
Month 9-10: Reporting & Optimization
Month 11-12: Frontend & Go-Live
```

**Critical Success Factors:**
1. ✅ Strong team commitment
2. ✅ Executive support & budget
3. ✅ Gradual migration (no big bang)
4. ✅ Extensive testing
5. ✅ Good communication
6. ✅ Monitoring & observability
7. ✅ Always have rollback plan

**Expected Outcomes:**
- ✅ Scalable system (10x current capacity)
- ✅ Faster development (2x velocity)
- ✅ Better reliability (99.9% uptime)
- ✅ Modern architecture (ready for future)
- ✅ Multi-platform (web, mobile, desktop)
- ✅ Happy developers (modern tech stack)
- ✅ Happy users (better UX)

---

**Document Status:** Complete Migration Roadmap  
**Next Steps:** Team review → Budget approval → Kickoff  
**Contact:** Tech Lead untuk questions

---

**All Documents in This Series:**
1. [01_ANALISIS_KELAYAKAN_MICROSERVICES.md](./01_ANALISIS_KELAYAKAN_MICROSERVICES.md)
2. [02_ARSITEKTUR_DETAIL_MICROSERVICES.md](./02_ARSITEKTUR_DETAIL_MICROSERVICES.md)
3. [03_MIGRATION_ROADMAP.md](./03_MIGRATION_ROADMAP.md) ← You are here
4. [04_DATABASE_STRATEGY.md](./04_DATABASE_STRATEGY.md)
5. [05_LOCAL_DEPLOYMENT_GUIDE.md](./05_LOCAL_DEPLOYMENT_GUIDE.md)
6. [06_FRONTEND_MOBILE_DESKTOP.md](./06_FRONTEND_MOBILE_DESKTOP.md)
