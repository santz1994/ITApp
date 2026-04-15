# Local Deployment Guide - Microservices Development Environment

**Proyek:** ITQuty Microservices  
**Target:** Setup development environment di komputer lokal  
**Platform:** Windows / Mac / Linux  
**Tanggal:** 18 Desember 2025

---

## 🎯 Overview

Dokumen ini menjelaskan **aplikasi apa saja yang dibutuhkan** untuk running microservices ITQuty di komputer lokal, termasuk:
1. Software requirements
2. Installation steps
3. Configuration
4. Development workflow

---

## 💻 Software Requirements

### 1. Core Requirements (WAJIB)

#### A. Docker Desktop ⭐ PALING PENTING
```yaml
Software: Docker Desktop
Version: 4.25+ (Latest)
Download: https://www.docker.com/products/docker-desktop/
OS Support:
  - Windows: Windows 10/11 Pro, Enterprise, atau Education (64-bit)
  - Mac: macOS 11+
  - Linux: Ubuntu 20.04+, Fedora, Debian

Fungsi:
  - Menjalankan semua microservices dalam containers
  - Mengisolasi setiap service
  - Easy start/stop services
  - Consistent environment (dev = staging = production)

Requirement:
  - RAM: Minimum 8GB (recommended 16GB)
  - Disk: 50GB free space
  - CPU: 4 cores minimum

Installation:
  Windows/Mac: Install dari website (GUI installer)
  Linux: 
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
```

#### B. Docker Compose (Included di Docker Desktop)
```yaml
Software: Docker Compose
Version: 2.20+ (bundled dengan Docker Desktop)
Fungsi:
  - Orchestrate multiple containers
  - Start all services dengan 1 command
  - Manage service dependencies
  - Network configuration

Verification:
  docker compose version
  # Output: Docker Compose version v2.23.0
```

#### C. Git
```yaml
Software: Git
Version: 2.40+
Download: https://git-scm.com/downloads

Fungsi:
  - Version control
  - Clone repositories
  - Branch management
  - Collaboration

Installation:
  Windows: Download installer dari website
  Mac: brew install git
  Linux: sudo apt install git
```

#### D. Visual Studio Code (Recommended IDE)
```yaml
Software: VS Code
Version: Latest
Download: https://code.visualstudio.com/

Recommended Extensions:
  - Docker (microsoft.docker)
  - PHP Intelephense (bmewburn.vscode-intelephense-client)
  - Laravel Extension Pack (onecentlin.laravel-extension-pack)
  - REST Client (humao.rest-client)
  - Thunder Client (rangav.vscode-thunder-client)
  - GitLens (eamodio.gitlens)

Alternative IDEs:
  - PHPStorm (paid, excellent Laravel support)
  - Sublime Text
  - Vim/Neovim
```

### 2. Optional but Recommended

#### E. Portainer (Docker GUI Management)
```yaml
Software: Portainer Community Edition
Version: Latest
Installation:
  docker volume create portainer_data
  docker run -d -p 9000:9000 \
    --name portainer --restart=always \
    -v /var/run/docker.sock:/var/run/docker.sock \
    -v portainer_data:/data \
    portainer/portainer-ce:latest

Access: http://localhost:9000

Fungsi:
  - Visual Docker management
  - View logs dengan GUI
  - Manage containers, images, volumes
  - Monitor resource usage
  - Easy untuk non-technical users
```

#### F. Postman / Insomnia (API Testing)
```yaml
Software: Postman
Version: Latest
Download: https://www.postman.com/downloads/

Alternative: Insomnia
Download: https://insomnia.rest/download

Fungsi:
  - Test API endpoints
  - Create API collections
  - Save requests
  - Environment variables
  - Automated testing
```

#### G. DBeaver / TablePlus (Database GUI)
```yaml
Software: DBeaver Community
Version: Latest
Download: https://dbeaver.io/download/

Alternative: TablePlus (Mac/Windows)
Download: https://tableplus.com/

Fungsi:
  - View database tables
  - Run SQL queries
  - Export data
  - Database design
  - Connection management
```

---

## 🚀 Complete Installation Guide

### Step 1: Install Docker Desktop

#### Windows 10/11:
```powershell
# 1. Download Docker Desktop
# https://desktop.docker.com/win/main/amd64/Docker%20Desktop%20Installer.exe

# 2. Run installer
# - Check "Use WSL 2 instead of Hyper-V"
# - Install

# 3. Reboot komputer

# 4. Start Docker Desktop
# - Icon muncul di system tray
# - Wait sampai "Docker Desktop is running"

# 5. Verify installation
docker --version
# Output: Docker version 24.0.7, build afdd53b

docker compose version
# Output: Docker Compose version v2.23.0

# 6. Test Docker
docker run hello-world
# Should download dan run test container
```

#### Mac:
```bash
# 1. Download Docker Desktop untuk Mac
# https://desktop.docker.com/mac/main/arm64/Docker.dmg (Apple Silicon)
# https://desktop.docker.com/mac/main/amd64/Docker.dmg (Intel)

# 2. Open .dmg file dan drag ke Applications

# 3. Open Docker dari Applications

# 4. Grant permissions when asked

# 5. Verify
docker --version
docker compose version

# 6. Test
docker run hello-world
```

#### Linux (Ubuntu/Debian):
```bash
# 1. Uninstall old versions
sudo apt-get remove docker docker-engine docker.io containerd runc

# 2. Update packages
sudo apt-get update

# 3. Install dependencies
sudo apt-get install \
    ca-certificates \
    curl \
    gnupg \
    lsb-release

# 4. Add Docker's official GPG key
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | \
  sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

# 5. Setup repository
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
  https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# 6. Install Docker Engine
sudo apt-get update
sudo apt-get install docker-ce docker-ce-cli containerd.io \
  docker-buildx-plugin docker-compose-plugin

# 7. Verify
sudo docker --version
sudo docker compose version

# 8. Add user to docker group (avoid sudo)
sudo usermod -aG docker $USER
# Logout dan login lagi

# 9. Test
docker run hello-world
```

### Step 2: Configure Docker Resources

```yaml
# Docker Desktop Settings → Resources

Recommended for Development:
  CPUs: 4 (or half of your total cores)
  Memory: 8 GB (or 50% of your RAM)
  Swap: 2 GB
  Disk image size: 60 GB

For Low-Spec Machines:
  CPUs: 2
  Memory: 4 GB
  Swap: 1 GB
  Disk: 40 GB
  
For High-Spec Machines:
  CPUs: 8+
  Memory: 16 GB+
  Swap: 4 GB
  Disk: 100 GB
```

### Step 3: Clone Repository & Setup Project

```bash
# 1. Create project directory
mkdir ~/projects
cd ~/projects

# 2. Clone ITQuty microservices
git clone https://github.com/santz1994/itquty-microservices.git
cd itquty-microservices

# 3. Project structure akan seperti ini:
# itquty-microservices/
# ├── docker-compose.yml
# ├── .env.example
# ├── api-gateway/
# ├── services/
# │   ├── auth-service/
# │   ├── asset-service/
# │   ├── ticket-service/
# │   ├── user-service/
# │   └── ...
# ├── frontend/
# │   ├── web-app/
# │   ├── mobile-app/
# │   └── desktop-app/
# └── docs/

# 4. Copy environment file
cp .env.example .env

# 5. Edit .env file
# Adjust settings sesuai kebutuhan lokal
nano .env  # atau code .env untuk VS Code
```

### Step 4: Start All Services

```bash
# 1. Build all Docker images (first time only, ~10-20 menit)
docker compose build

# 2. Start all services
docker compose up -d

# Output:
# [+] Running 15/15
#  ✔ Network itquty_network         Created
#  ✔ Container itquty-mysql         Started
#  ✔ Container itquty-redis         Started
#  ✔ Container itquty-rabbitmq      Started
#  ✔ Container itquty-auth-service  Started
#  ✔ Container itquty-user-service  Started
#  ✔ Container itquty-asset-service Started
#  ✔ Container itquty-ticket-service Started
#  ✔ Container itquty-api-gateway   Started
#  ... (more services)

# 3. Check status
docker compose ps

# Output:
# NAME                        STATUS    PORTS
# itquty-api-gateway          Up        0.0.0.0:8000->8000/tcp
# itquty-auth-service         Up        8001/tcp
# itquty-asset-service        Up        8003/tcp
# itquty-ticket-service       Up        8004/tcp
# itquty-mysql                Up        0.0.0.0:3306->3306/tcp
# itquty-redis                Up        6379/tcp
# itquty-rabbitmq             Up        0.0.0.0:5672->5672/tcp, 0.0.0.0:15672->15672/tcp

# 4. View logs
docker compose logs -f

# View logs dari specific service
docker compose logs -f asset-service

# 5. Access applications
# API Gateway: http://localhost:8000
# Web App: http://localhost:3000
# Portainer: http://localhost:9000
# RabbitMQ Management: http://localhost:15672 (guest/guest)
```

---

## 📁 Complete Docker Compose Configuration

```yaml
# File: docker-compose.yml

version: '3.8'

services:
  # ============================================
  # INFRASTRUCTURE SERVICES
  # ============================================
  
  mysql:
    image: mysql:8.0
    container_name: itquty-mysql
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-secret}
      MYSQL_DATABASE: ${DB_DATABASE:-itquty_db}
      MYSQL_USER: ${DB_USERNAME:-itquty_user}
      MYSQL_PASSWORD: ${DB_PASSWORD:-itquty_pass}
    ports:
      - "${DB_PORT:-3306}:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/conf.d:/etc/mysql/conf.d
      - ./docker/mysql/init:/docker-entrypoint-initdb.d
    networks:
      - itquty-network
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 5

  redis:
    image: redis:7-alpine
    container_name: itquty-redis
    ports:
      - "${REDIS_PORT:-6379}:6379"
    volumes:
      - redis_data:/data
    networks:
      - itquty-network
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 3s
      retries: 5

  rabbitmq:
    image: rabbitmq:3-management-alpine
    container_name: itquty-rabbitmq
    environment:
      RABBITMQ_DEFAULT_USER: ${RABBITMQ_USER:-guest}
      RABBITMQ_DEFAULT_PASS: ${RABBITMQ_PASS:-guest}
    ports:
      - "${RABBITMQ_PORT:-5672}:5672"
      - "${RABBITMQ_MANAGEMENT_PORT:-15672}:15672"
    volumes:
      - rabbitmq_data:/var/lib/rabbitmq
    networks:
      - itquty-network
    healthcheck:
      test: ["CMD", "rabbitmq-diagnostics", "ping"]
      interval: 30s
      timeout: 10s
      retries: 5

  # ============================================
  # MICROSERVICES
  # ============================================

  api-gateway:
    build:
      context: ./api-gateway
      dockerfile: Dockerfile
    container_name: itquty-api-gateway
    ports:
      - "${API_GATEWAY_PORT:-8000}:8000"
    environment:
      - AUTH_SERVICE_URL=http://auth-service:8001
      - ASSET_SERVICE_URL=http://asset-service:8003
      - TICKET_SERVICE_URL=http://ticket-service:8004
      - USER_SERVICE_URL=http://user-service:8002
    networks:
      - itquty-network
    depends_on:
      - auth-service
      - asset-service
      - ticket-service
      - user-service

  auth-service:
    build:
      context: ./services/auth-service
      dockerfile: Dockerfile
    container_name: itquty-auth-service
    environment:
      - DB_HOST=mysql
      - DB_DATABASE=auth_db
      - REDIS_HOST=redis
    volumes:
      - ./services/auth-service:/var/www/html
    networks:
      - itquty-network
    depends_on:
      mysql:
        condition: service_healthy
      redis:
        condition: service_healthy

  user-service:
    build:
      context: ./services/user-service
      dockerfile: Dockerfile
    container_name: itquty-user-service
    environment:
      - DB_HOST=mysql
      - DB_DATABASE=user_db
      - REDIS_HOST=redis
    volumes:
      - ./services/user-service:/var/www/html
    networks:
      - itquty-network
    depends_on:
      mysql:
        condition: service_healthy
      redis:
        condition: service_healthy

  asset-service:
    build:
      context: ./services/asset-service
      dockerfile: Dockerfile
    container_name: itquty-asset-service
    environment:
      - DB_HOST=mysql
      - DB_DATABASE=asset_db
      - REDIS_HOST=redis
      - RABBITMQ_HOST=rabbitmq
    volumes:
      - ./services/asset-service:/var/www/html
      - asset_uploads:/var/www/html/storage/app/public
    networks:
      - itquty-network
    depends_on:
      mysql:
        condition: service_healthy
      redis:
        condition: service_healthy
      rabbitmq:
        condition: service_healthy

  ticket-service:
    build:
      context: ./services/ticket-service
      dockerfile: Dockerfile
    container_name: itquty-ticket-service
    environment:
      - DB_HOST=mysql
      - DB_DATABASE=ticket_db
      - REDIS_HOST=redis
      - RABBITMQ_HOST=rabbitmq
    volumes:
      - ./services/ticket-service:/var/www/html
    networks:
      - itquty-network
    depends_on:
      mysql:
        condition: service_healthy
      redis:
        condition: service_healthy
      rabbitmq:
        condition: service_healthy

  # ============================================
  # FRONTEND APPLICATIONS
  # ============================================

  web-app:
    build:
      context: ./frontend/web-app
      dockerfile: Dockerfile
    container_name: itquty-web-app
    ports:
      - "${WEB_APP_PORT:-3000}:3000"
    environment:
      - REACT_APP_API_URL=http://localhost:8000/api/v1
    volumes:
      - ./frontend/web-app:/app
      - /app/node_modules
    networks:
      - itquty-network

  # ============================================
  # MONITORING & MANAGEMENT
  # ============================================

  portainer:
    image: portainer/portainer-ce:latest
    container_name: itquty-portainer
    ports:
      - "9000:9000"
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
      - portainer_data:/data
    networks:
      - itquty-network
    restart: always

volumes:
  mysql_data:
  redis_data:
  rabbitmq_data:
  asset_uploads:
  portainer_data:

networks:
  itquty-network:
    driver: bridge
```

---

## 🛠️ Development Workflow

### Daily Development Routine

```bash
# Morning: Start all services
cd ~/projects/itquty-microservices
docker compose up -d

# Check if all services running
docker compose ps

# View logs (optional)
docker compose logs -f asset-service

# Start coding...
# Edit files di ./services/asset-service/
# Changes otomatis ter-reflect (hot reload)

# Run database migrations
docker compose exec asset-service php artisan migrate

# Run tests
docker compose exec asset-service php artisan test

# Evening: Stop services (optional, bisa dibiarkan jalan)
docker compose stop

# Next day: Restart
docker compose start

# Weekly: Update images
docker compose pull
docker compose build --no-cache
```

### Working with Specific Service

```bash
# Enter service container
docker compose exec asset-service bash

# Inside container:
php artisan tinker
php artisan migrate
php artisan db:seed
composer install
php artisan cache:clear

# Exit container
exit

# View service logs
docker compose logs -f asset-service

# Restart specific service
docker compose restart asset-service

# Rebuild specific service
docker compose build asset-service
docker compose up -d asset-service
```

### Database Operations

```bash
# Connect to MySQL
docker compose exec mysql mysql -u root -p

# Or use GUI tool (DBeaver):
# Host: localhost
# Port: 3306
# Username: root
# Password: secret
# Database: itquty_db

# Export database
docker compose exec mysql mysqldump -u root -p itquty_db > backup.sql

# Import database
docker compose exec -T mysql mysql -u root -p itquty_db < backup.sql

# Run migrations for all services
docker compose exec auth-service php artisan migrate
docker compose exec asset-service php artisan migrate
docker compose exec ticket-service php artisan migrate
```

### API Testing

```bash
# Using cURL
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}'

# Save token
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGc..."

# Test protected endpoint
curl -X GET http://localhost:8000/api/v1/assets \
  -H "Authorization: Bearer $TOKEN"

# Or use Postman/Insomnia for GUI
```

---

## 🐛 Troubleshooting Common Issues

### Issue 1: Docker Desktop Not Starting (Windows)

```powershell
# Problem: WSL 2 installation incomplete
# Solution:
wsl --install
wsl --set-default-version 2
# Restart computer

# Verify WSL
wsl -l -v
# Should show WSL version 2
```

### Issue 2: Port Already in Use

```bash
# Problem: Port 8000 already used by XAMPP/other app
# Solution 1: Stop XAMPP
Z:\apache_stop.bat
Z:\mysql_stop.bat

# Solution 2: Change port di .env
API_GATEWAY_PORT=8080

# Restart
docker compose up -d
```

### Issue 3: Containers Keep Restarting

```bash
# Check logs
docker compose logs mysql

# Common causes:
# 1. Insufficient memory
#    → Increase Docker memory di Settings

# 2. Database init error
#    → Remove volume dan recreate
docker compose down -v
docker compose up -d

# 3. Port conflict
#    → Change ports di .env
```

### Issue 4: Slow Performance

```bash
# Solutions:
# 1. Increase Docker resources (RAM, CPU)
# 2. Use Docker volumes instead of bind mounts
# 3. Enable file sharing optimization
# 4. Close other applications
# 5. Use SSD instead of HDD

# Windows specific: Enable WSL 2 backend
# Settings → General → Use WSL 2 based engine
```

### Issue 5: Cannot Connect to Service

```bash
# Check service health
docker compose ps

# Check network
docker network inspect itquty-microservices_itquty-network

# Test connectivity
docker compose exec api-gateway ping mysql
docker compose exec api-gateway curl http://asset-service:8003/health

# Check firewall
# Windows: Allow Docker in Windows Firewall
# Mac: System Preferences → Security → Allow Docker
```

---

## 📊 Resource Monitoring

### Using Portainer (Recommended)

```yaml
Access: http://localhost:9000

Features:
- Visual container list
- Real-time resource usage graphs
- Log viewer dengan search
- Terminal access ke containers
- Volume management
- Network inspection
```

### Using Docker Commands

```bash
# View resource usage
docker stats

# Output:
# CONTAINER           CPU %   MEM USAGE / LIMIT     MEM %   NET I/O
# itquty-api-gateway  2.5%    150MiB / 8GiB        1.9%    1.2MB/850KB
# itquty-mysql        5.3%    450MiB / 8GiB        5.6%    5MB/2MB
# itquty-redis        0.5%    10MiB / 8GiB         0.1%    100KB/50KB

# View disk usage
docker system df

# Clean up unused resources
docker system prune -a
```

---

## 💡 Tips & Best Practices

### 1. Resource Management
```bash
# Stop unused services untuk save resources
docker compose stop notification-service reporting-service

# Start only when needed
docker compose start notification-service

# Scale services
docker compose up -d --scale asset-service=3
```

### 2. Development Efficiency
```bash
# Use aliases untuk commands yang sering dipakai
# File: ~/.bashrc or ~/.zshrc

alias dc='docker compose'
alias dcu='docker compose up -d'
alias dcd='docker compose down'
alias dcl='docker compose logs -f'
alias dcp='docker compose ps'
alias dcr='docker compose restart'

# Usage:
dcu  # instead of docker compose up -d
dcl asset-service  # view logs
```

### 3. Data Persistence
```yaml
# Important: Use named volumes untuk data persistence
# Volumes akan persist even jika container dihapus

volumes:
  mysql_data:  # ← This persists database data
  redis_data:  # ← This persists cache data
```

### 4. Environment Variables
```bash
# Use .env file untuk configuration
# NEVER commit .env ke git

# File: .env
API_GATEWAY_PORT=8000
DB_ROOT_PASSWORD=super_secret_password
REDIS_PASSWORD=redis_secret

# Load .env
docker compose --env-file .env up -d
```

### 5. Backup Strategy
```bash
# Daily: Backup volumes
docker run --rm -v itquty-microservices_mysql_data:/data \
  -v $(pwd)/backups:/backup \
  alpine tar czf /backup/mysql_backup_$(date +%Y%m%d).tar.gz -C /data .

# Restore from backup
docker run --rm -v itquty-microservices_mysql_data:/data \
  -v $(pwd)/backups:/backup \
  alpine tar xzf /backup/mysql_backup_20251218.tar.gz -C /data
```

---

## 🎯 Minimum vs Recommended Specs

### Minimum (Basic Development)
```yaml
Hardware:
  CPU: Intel i3 dual-core atau equivalent
  RAM: 8GB
  Disk: 50GB HDD free space
  
Software:
  - Docker Desktop
  - VS Code
  - Git
  
Limitations:
  - Bisa run 3-5 services sekaligus
  - Performance moderate
  - Some features mungkin slow
```

### Recommended (Smooth Development)
```yaml
Hardware:
  CPU: Intel i5/i7 quad-core atau equivalent
  RAM: 16GB
  Disk: 100GB SSD free space
  
Software:
  - Docker Desktop
  - VS Code dengan extensions
  - Git
  - Portainer
  - DBeaver
  - Postman
  
Benefits:
  - Run all services sekaligus
  - Fast performance
  - Multiple projects
  - Smooth development experience
```

### Ideal (Professional Development)
```yaml
Hardware:
  CPU: Intel i7/i9 atau AMD Ryzen 7/9 (8+ cores)
  RAM: 32GB
  Disk: 500GB NVMe SSD
  
Software: All recommended + bonus tools
  
Benefits:
  - Run production-like environment
  - Multiple projects simultaneously
  - Kubernetes local cluster
  - Load testing capabilities
```

---

## 📚 Next Steps

Setelah setup development environment:

1. ✅ **Test semua endpoints** dengan Postman
2. 📖 **Read API documentation** di [06_FRONTEND_MOBILE_DESKTOP.md](./06_FRONTEND_MOBILE_DESKTOP.md)
3. 🔧 **Setup CI/CD** di [07_DEVOPS_INFRASTRUCTURE.md](./07_DEVOPS_INFRASTRUCTURE.md)
4. 🚀 **Start development** pada service pilihan
5. 📝 **Follow coding standards** di documentation

---

**Summary:**
- **Core Tools:** Docker Desktop (WAJIB) + Git + VS Code
- **Optional:** Portainer (GUI) + Postman (API) + DBeaver (DB)
- **Minimum RAM:** 8GB (recommended 16GB)
- **Disk Space:** 50GB minimum
- **Time to Setup:** 1-2 hours untuk first time
- **Daily Workflow:** `docker compose up -d` → Code → Test → `docker compose stop`

**Contacts untuk Support:**
- Documentation: `./docs/`
- Issues: GitHub Issues
- Community: Slack channel

---

**Next Document:** [06_FRONTEND_MOBILE_DESKTOP.md](./06_FRONTEND_MOBILE_DESKTOP.md)  
**Related:** [02_ARSITEKTUR_DETAIL_MICROSERVICES.md](./02_ARSITEKTUR_DETAIL_MICROSERVICES.md)
