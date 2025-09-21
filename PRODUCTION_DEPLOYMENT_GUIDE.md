# 🚀 Production Deployment Guide
## KH Holdings Insurance Agent System

This guide provides step-by-step instructions for deploying the KH Holdings Insurance Agent system to production with all MLM network features working correctly.

---

## 📋 Pre-Deployment Checklist

### ✅ System Requirements
- **PHP**: 8.1 or higher
- **MySQL**: 8.0 or higher
- **Composer**: Latest version
- **Node.js**: 18.x or higher
- **NPM**: Latest version
- **Web Server**: Nginx or Apache
- **SSL Certificate**: Required for production

### ✅ Environment Setup
- [ ] Production server configured
- [ ] Domain name pointed to server
- [ ] SSL certificate installed
- [ ] Database server ready
- [ ] File permissions set correctly

---

## 🔧 Step 1: Server Preparation

### 1.1 Update System Packages
```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade -y

# CentOS/RHEL
sudo yum update -y
```

### 1.2 Install Required Software
```bash
# Install PHP 8.1+ with extensions
sudo apt install php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-bcmath php8.1-gd -y

# Install MySQL
sudo apt install mysql-server -y

# Install Nginx
sudo apt install nginx -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 18.x
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y
```

### 1.3 Configure MySQL
```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE kh_holdings_insurance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'kh_holdings_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';
GRANT ALL PRIVILEGES ON kh_holdings_insurance.* TO 'kh_holdings_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 📁 Step 2: Application Deployment

### 2.1 Clone Repository
```bash
# Navigate to web directory
cd /var/www

# Clone the repository
sudo git clone https://github.com/your-repo/kh_holdings_insurance_agent.git
sudo chown -R www-data:www-data kh_holdings_insurance_agent
cd kh_holdings_insurance_agent
```

### 2.2 Backend Setup
```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
nano .env
```

### 2.3 Environment Configuration (.env)
```env
APP_NAME="KH Holdings Insurance Agent"
APP_ENV=production
APP_KEY=base64:your_generated_key_here
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kh_holdings_insurance
DB_USERNAME=kh_holdings_user
DB_PASSWORD=your_secure_password_here

# JWT Configuration
JWT_SECRET=your_jwt_secret_here
JWT_ALGO=HS256

# Curlec Payment Gateway
CURLEC_API_KEY=your_curlec_api_key
CURLEC_API_SECRET=your_curlec_api_secret
CURLEC_WEBHOOK_SECRET=your_webhook_secret

# Mail Configuration (for production)
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="KH Holdings Insurance"
```

### 2.4 Frontend Setup
```bash
# Install Node.js dependencies
npm install

# Build for production
npm run build
```

---

## 🗄️ Step 3: Database Setup

### 3.1 Run Migrations
```bash
# Run all migrations (this will create tables and calculate network levels)
php artisan migrate --force

# Verify migration status
php artisan migrate:status
```

### 3.2 Seed Initial Data
```bash
# Seed the database with initial data
php artisan db:seed --force

# This will create:
# - Admin user (admin@khh.test / admin123)
# - Sample agents with proper MLM hierarchy
# - Insurance plans and commission rates
# - Hospitals and clinics
# - Network levels for all agents
```

### 3.3 Verify Database Setup
```bash
# Check if network levels were calculated
php artisan tinker --execute="echo 'Network levels count: ' . \App\Models\NetworkLevel::count();"

# Check agent codes
php artisan tinker --execute="echo 'Agents: ' . \App\Models\User::whereNotNull('agent_code')->count();"
```

---

## ⚙️ Step 4: Application Configuration

### 4.1 Set File Permissions
```bash
# Set correct permissions
sudo chown -R www-data:www-data /var/www/kh_holdings_insurance_agent
sudo chmod -R 755 /var/www/kh_holdings_insurance_agent
sudo chmod -R 775 /var/www/kh_holdings_insurance_agent/storage
sudo chmod -R 775 /var/www/kh_holdings_insurance_agent/bootstrap/cache
```

### 4.2 Optimize Application
```bash
# Clear and cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### 4.3 Configure Queue (Optional but Recommended)
```bash
# Install Supervisor for queue processing
sudo apt install supervisor -y

# Create queue worker configuration
sudo nano /etc/supervisor/conf.d/kh-holdings-worker.conf
```

```ini
[program:kh-holdings-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/kh_holdings_insurance_agent/backend/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/kh_holdings_insurance_agent/backend/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
# Start supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start kh-holdings-worker:*
```

---

## 🌐 Step 5: Web Server Configuration

### 5.1 Nginx Configuration
```bash
# Create Nginx configuration
sudo nano /etc/nginx/sites-available/kh-holdings
```

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/kh_holdings_insurance_agent;
    index index.html index.php;

    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Frontend (Next.js)
    location / {
        try_files $uri $uri/ @nextjs;
    }

    location @nextjs {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    # Backend API
    location /api/ {
        alias /var/www/kh_holdings_insurance_agent/backend/public/;
        try_files $uri $uri/ @backend;
    }

    location @backend {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME /var/www/kh_holdings_insurance_agent/backend/public/index.php;
        include fastcgi_params;
    }

    # Admin Panel
    location /admin {
        alias /var/www/kh_holdings_insurance_agent/backend/public;
        try_files $uri $uri/ @admin;
    }

    location @admin {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME /var/www/kh_holdings_insurance_agent/backend/public/index.php;
        include fastcgi_params;
    }

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript;
}
```

### 5.2 Enable Site
```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/kh-holdings /etc/nginx/sites-enabled/

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
```

---

## 🚀 Step 6: Start Services

### 6.1 Start Backend Services
```bash
# Start PHP-FPM
sudo systemctl start php8.1-fpm
sudo systemctl enable php8.1-fpm

# Start MySQL
sudo systemctl start mysql
sudo systemctl enable mysql
```

### 6.2 Start Frontend Services
```bash
# Start Next.js in production mode
cd /var/www/kh_holdings_insurance_agent
npm run start &

# Or use PM2 for process management
npm install -g pm2
pm2 start npm --name "kh-holdings-frontend" -- run start
pm2 save
pm2 startup
```

---

## ✅ Step 7: Verification & Testing

### 7.1 Test Backend API
```bash
# Test API endpoints
curl -X GET https://yourdomain.com/api/v1/health
curl -X POST https://yourdomain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"agent@khholdings.com","password":"agent123"}'
```

### 7.2 Test Frontend
```bash
# Check if frontend is accessible
curl -I https://yourdomain.com
```

### 7.3 Test Admin Panel
```bash
# Access admin panel
# URL: https://yourdomain.com/admin
# Login: admin@khh.test / admin123
```

### 7.4 Verify Network Levels
```bash
# Check if network levels are working
php artisan tinker --execute="
\$agents = ['AGT00001', 'AGT00002', 'AGT00003', 'AGT00004'];
foreach(\$agents as \$code) {
    \$user = \App\Models\User::where('agent_code', \$code)->first();
    if(\$user) {
        \$service = new \App\Services\NetworkLevelService();
        \$members = \$service->getNetworkMembers(\$code, null);
        \$downlines = \$members->where('level', '>', 1);
        echo \$code . ': ' . \$downlines->count() . ' downlines' . PHP_EOL;
    }
}
"
```

---

## 🔧 Step 8: Production Maintenance

### 8.1 Set Up Log Rotation
```bash
# Configure log rotation
sudo nano /etc/logrotate.d/kh-holdings
```

```
/var/www/kh_holdings_insurance_agent/backend/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
    postrotate
        sudo systemctl reload php8.1-fpm
    endscript
}
```

### 8.2 Set Up Database Backups
```bash
# Create backup script
sudo nano /usr/local/bin/backup-kh-holdings.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/kh-holdings"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="kh_holdings_insurance"
DB_USER="kh_holdings_user"
DB_PASS="your_secure_password_here"

mkdir -p $BACKUP_DIR

# Create database backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/db_backup_$DATE.sql

# Keep only last 7 days of backups
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +7 -delete

echo "Backup completed: db_backup_$DATE.sql.gz"
```

```bash
# Make script executable
sudo chmod +x /usr/local/bin/backup-kh-holdings.sh

# Add to crontab for daily backups
sudo crontab -e
# Add this line:
# 0 2 * * * /usr/local/bin/backup-kh-holdings.sh
```

### 8.3 Monitor System Health
```bash
# Check service status
sudo systemctl status nginx
sudo systemctl status php8.1-fpm
sudo systemctl status mysql

# Check disk space
df -h

# Check memory usage
free -h

# Check application logs
tail -f /var/www/kh_holdings_insurance_agent/backend/storage/logs/laravel.log
```

---

## 🚨 Troubleshooting

### Common Issues & Solutions

#### 1. Network Levels Not Working
```bash
# Rebuild network levels
php artisan network:rebuild --force
```

#### 2. Agent Codes Not Generating
```bash
# Check if users table has agent_code column
php artisan tinker --execute="echo 'Agent codes count: ' . \App\Models\User::whereNotNull('agent_code')->count();"
```

#### 3. Commission Not Calculating
```bash
# Check commission rates
php artisan tinker --execute="echo 'Commission rates: ' . \App\Models\CommissionRate::count();"
```

#### 4. Payment Gateway Issues
```bash
# Check Curlec configuration
php artisan tinker --execute="echo 'Curlec API Key: ' . (config('services.curlec.api_key') ? 'Set' : 'Not Set');"
```

#### 5. Frontend Not Loading
```bash
# Check if Next.js is running
ps aux | grep next
# Restart if needed
pm2 restart kh-holdings-frontend
```

---

## 📞 Support & Maintenance

### Regular Maintenance Tasks
- **Daily**: Check application logs for errors
- **Weekly**: Verify database backups
- **Monthly**: Update system packages
- **Quarterly**: Review and optimize database performance

### Emergency Contacts
- **System Administrator**: [Your Contact]
- **Database Administrator**: [Your Contact]
- **Development Team**: [Your Contact]

### Useful Commands
```bash
# Restart all services
sudo systemctl restart nginx php8.1-fpm mysql

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild network levels
php artisan network:rebuild --force

# Check application status
php artisan about
```

---

## ✅ Final Checklist

- [ ] All services running (Nginx, PHP-FPM, MySQL)
- [ ] SSL certificate installed and working
- [ ] Frontend accessible at https://yourdomain.com
- [ ] Backend API responding at https://yourdomain.com/api/
- [ ] Admin panel accessible at https://yourdomain.com/admin
- [ ] Database seeded with initial data
- [ ] Network levels calculated for all agents
- [ ] Payment gateway configured
- [ ] Log rotation configured
- [ ] Database backups scheduled
- [ ] Monitoring in place

---

**🎉 Congratulations! Your KH Holdings Insurance Agent system is now live in production!**

For any issues or questions, refer to the troubleshooting section or contact the development team.
