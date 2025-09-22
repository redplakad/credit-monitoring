# 🚀 Deployment Guide - Credit Monitoring App

## Production Environment Setup untuk `baturaja.com`

### 📋 Pre-requisites

1. **Server Requirements:**
   - PHP 8.1+ with extensions: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo
   - MySQL 8.0+ atau PostgreSQL 13+
   - Redis 6.0+ (untuk cache & queue)
   - Nginx atau Apache
   - SSL Certificate untuk HTTPS
   - Node.js 18+ (untuk build assets)

2. **Domain Setup:**
   - Domain: `baturaja.com`
   - Subdomain opsional: `www.baturaja.com`, `admin.baturaja.com`
   - SSL Certificate installed dan configured

### 🔧 Environment Configuration

#### 1. Copy dan Edit .env untuk Production

```bash
cp .env.production.example .env
```

#### 2. Key Configuration Changes

```bash
# Generate app key
php artisan key:generate

# Update database credentials
DB_DATABASE=credit_monitoring_prod
DB_USERNAME=your_db_username
DB_PASSWORD=your_secure_db_password

# Session configuration untuk production
SESSION_DOMAIN=.baturaja.com           # Dot prefix untuk subdomain support
SESSION_SECURE_COOKIE=true             # HTTPS only
SESSION_HTTP_ONLY=true                 # Security

# Sanctum stateful domains
SANCTUM_STATEFUL_DOMAINS=baturaja.com,www.baturaja.com,admin.baturaja.com

# Mail configuration
MAIL_FROM_ADDRESS=noreply@baturaja.com
MAIL_FROM_NAME="Credit Monitoring"
```

### 📦 Deployment Steps

#### 1. Clone Repository
```bash
git clone https://github.com/your-repo/credit-monitoring.git
cd credit-monitoring
```

#### 2. Install Dependencies
```bash
# PHP dependencies
composer install --optimize-autoloader --no-dev

# Node.js dependencies
npm ci --only=production
```

#### 3. Environment Setup
```bash
# Copy dan edit .env
cp .env.production.example .env
# Edit .env dengan values yang sesuai

# Generate app key
php artisan key:generate

# Link storage
php artisan storage:link
```

#### 4. Database Setup
```bash
# Run migrations
php artisan migrate --force

# Seed data (jika ada)
php artisan db:seed --force
```

#### 5. Build Assets
```bash
# Build untuk production
npm run build
```

#### 6. Cache Configuration
```bash
# Cache config, routes, views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate app key if not done
php artisan key:generate --force
```

#### 7. Set Permissions
```bash
# Set proper permissions
chown -R www-data:www-data /var/www/credit-monitoring
chmod -R 755 /var/www/credit-monitoring
chmod -R 775 /var/www/credit-monitoring/storage
chmod -R 775 /var/www/credit-monitoring/bootstrap/cache
```

### 🌐 Web Server Configuration

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name baturaja.com www.baturaja.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name baturaja.com www.baturaja.com;
    root /var/www/credit-monitoring/public;

    # SSL Configuration
    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Laravel Configuration
    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \\.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\\.(?!well-known).* {
        deny all;
    }

    # Asset Caching
    location ~* \\.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 🔄 Production Workflow

#### Deployment Script
```bash
#!/bin/bash
# deploy.sh

echo "🚀 Starting deployment..."

# Pull latest code
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm ci --only=production

# Build assets
npm run build

# Run migrations
php artisan migrate --force

# Clear dan cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl reload nginx
sudo systemctl restart php8.2-fpm

echo "✅ Deployment completed!"
```

#### Zero-Downtime Deployment dengan Octane
```bash
# Restart Octane gracefully
php artisan octane:reload
```

### 🔐 Security Checklist Production

- [ ] **HTTPS/SSL** configured dan enforced
- [ ] **SESSION_SECURE_COOKIE=true** di .env
- [ ] **SESSION_DOMAIN** set ke domain utama
- [ ] **SANCTUM_STATEFUL_DOMAINS** configured dengan semua domain
- [ ] **Database credentials** secure dan unique
- [ ] **APP_DEBUG=false** di production
- [ ] **File permissions** set correctly (755/775)
- [ ] **Web server** configured dengan security headers
- [ ] **Firewall** configured (hanya port 80, 443, 22)
- [ ] **Regular backups** setup untuk database
- [ ] **Error logging** configured
- [ ] **Rate limiting** enabled

### 🔍 Environment Differences

| Setting | Development | Production |
|---------|-------------|------------|
| APP_ENV | local | production |
| APP_DEBUG | true | false |
| APP_URL | http://localhost:8000 | https://baturaja.com |
| SESSION_DOMAIN | localhost | .baturaja.com |
| SESSION_SECURE_COOKIE | false | true |
| SANCTUM_STATEFUL_DOMAINS | localhost:3000,localhost:5173 | baturaja.com,www.baturaja.com |
| CACHE_STORE | file | redis |
| QUEUE_CONNECTION | sync | redis |
| LOG_LEVEL | debug | error |

### 🎯 Monitoring & Maintenance

#### Health Check Endpoints
- `GET /health` - Application health
- `GET /` - Main application

#### Log Monitoring
```bash
# Monitor application logs
tail -f storage/logs/laravel.log

# Monitor web server logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

#### Performance Monitoring
```bash
# Monitor system resources
htop
df -h
free -m

# Monitor database performance
mysql -e "SHOW PROCESSLIST;"
```

---

**📞 Support:** Untuk troubleshooting deployment, periksa log files dan pastikan semua environment variables sudah benar.