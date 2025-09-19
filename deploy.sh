#!/bin/bash

# KH Holdings Insurance Agent - Production Deployment Script
# This script prepares and deploys the application for production

set -e  # Exit on any error

echo "🚀 Starting KH Holdings Insurance Agent Production Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "package.json" ] || [ ! -f "backend/artisan" ]; then
    print_error "Please run this script from the project root directory"
    exit 1
fi

print_status "Setting up production environment..."

# 1. Install/Update Dependencies
print_status "Installing Node.js dependencies..."
npm ci --production=false

print_status "Installing PHP dependencies..."
cd backend
composer install --no-dev --optimize-autoloader

# 2. Environment Setup
print_status "Setting up environment configuration..."
if [ ! -f ".env" ]; then
    if [ -f ".env.production" ]; then
        cp .env.production .env
        print_warning "Copied .env.production to .env. Please update with your production values!"
    else
        print_error ".env.production template not found. Please create it first."
        exit 1
    fi
fi

# 3. Generate Application Key
print_status "Generating application key..."
php artisan key:generate --force

# 4. Generate JWT Secret
print_status "Generating JWT secret..."
php artisan jwt:secret --force

# 5. Database Setup
print_status "Setting up database..."
print_warning "Make sure your database is running and .env is configured correctly!"

# Run migrations
print_status "Running database migrations..."
php artisan migrate --force

# Seed the database
print_status "Seeding database with initial data..."
php artisan db:seed --force

# 6. Cache Optimization
print_status "Optimizing application for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Storage Setup
print_status "Setting up storage links..."
php artisan storage:link

# 8. Build Frontend
print_status "Building frontend for production..."
cd ..
npm run build

# 9. Set Permissions
print_status "Setting proper file permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 10. Create Production Ready Files
print_status "Creating production configuration files..."

# Create nginx configuration
cat > nginx.conf << 'EOF'
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Create systemd service for queue workers
cat > kh-insurance-queue.service << 'EOF'
[Unit]
Description=KH Holdings Insurance Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/html/backend/artisan queue:work --sleep=3 --tries=3 --max-time=3600
WorkingDirectory=/var/www/html/backend

[Install]
WantedBy=multi-user.target
EOF

# Create cron job for scheduled tasks
cat > crontab << 'EOF'
# KH Holdings Insurance Agent Cron Jobs
* * * * * cd /var/www/html/backend && php artisan schedule:run >> /dev/null 2>&1
EOF

print_status "✅ Production deployment completed successfully!"
print_status ""
print_status "📋 Next Steps:"
print_status "1. Update .env with your production values"
print_status "2. Configure your web server (nginx.conf provided)"
print_status "3. Set up SSL certificate"
print_status "4. Configure queue worker service (kh-insurance-queue.service provided)"
print_status "5. Set up cron jobs (crontab provided)"
print_status "6. Test the application"
print_status ""
print_status "🔧 Useful Commands:"
print_status "  - View logs: tail -f backend/storage/logs/laravel.log"
print_status "  - Clear cache: cd backend && php artisan cache:clear"
print_status "  - Queue status: cd backend && php artisan queue:work --once"
print_status ""
print_warning "Remember to:"
print_warning "  - Update CURLEC payment gateway keys for production"
print_warning "  - Configure proper database credentials"
print_warning "  - Set up proper file permissions"
print_warning "  - Configure backup strategy"
