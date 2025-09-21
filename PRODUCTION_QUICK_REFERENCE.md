# 🚀 Production Quick Reference Card
## KH Holdings Insurance Agent System

---

## ⚡ Essential Commands

### Initial Setup
```bash
# 1. Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# 2. Configure environment
cp .env.example .env
nano .env  # Configure database, JWT, Curlec settings

# 3. Setup database
php artisan migrate --force
php artisan db:seed --force

# 4. Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Start Services
```bash
# Backend (Laravel)
php artisan serve --host=0.0.0.0 --port=8000

# Frontend (Next.js)
npm run start

# Or with PM2
pm2 start npm --name "kh-holdings-frontend" -- run start
```

---

## 🔧 Maintenance Commands

### Network Levels
```bash
# Rebuild all network levels
php artisan network:rebuild --force

# Check network levels status
php artisan tinker --execute="echo 'Network levels: ' . \App\Models\NetworkLevel::count();"
```

### Database
```bash
# Check agent codes
php artisan tinker --execute="echo 'Agents: ' . \App\Models\User::whereNotNull('agent_code')->count();"

# Check commission rates
php artisan tinker --execute="echo 'Commission rates: ' . \App\Models\CommissionRate::count();"

# Backup database
mysqldump -u username -p database_name > backup.sql
```

### Application
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check application status
php artisan about
```

---

## 🚨 Emergency Fixes

### Network Levels Not Working
```bash
php artisan network:rebuild --force
```

### Agent Codes Missing
```bash
# Check if users have agent codes
php artisan tinker --execute="
\$users = \App\Models\User::whereNull('agent_code')->get();
echo 'Users without agent codes: ' . \$users->count();
"
```

### Commission Not Calculating
```bash
# Check commission rates exist
php artisan tinker --execute="
\$rates = \App\Models\CommissionRate::all();
echo 'Commission rates: ' . \$rates->count();
foreach(\$rates as \$rate) {
    echo \$rate->plan->name . ' Level ' . \$rate->level . ': ' . (\$rate->fixed_amount_cents ?? \$rate->rate_percent) . PHP_EOL;
}
"
```

### Payment Gateway Issues
```bash
# Check Curlec configuration
php artisan tinker --execute="
echo 'API Key: ' . (config('services.curlec.api_key') ? 'Set' : 'Not Set') . PHP_EOL;
echo 'API Secret: ' . (config('services.curlec.api_secret') ? 'Set' : 'Not Set') . PHP_EOL;
"
```

---

## 📊 Health Checks

### System Status
```bash
# Check services
sudo systemctl status nginx
sudo systemctl status php8.1-fpm
sudo systemctl status mysql

# Check disk space
df -h

# Check memory
free -h
```

### Application Health
```bash
# Test API
curl -X GET https://yourdomain.com/api/v1/health

# Test login
curl -X POST https://yourdomain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"agent@khholdings.com","password":"agent123"}'
```

### Database Health
```bash
# Check table sizes
mysql -u username -p -e "SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)' FROM information_schema.tables WHERE table_schema = 'kh_holdings_insurance' ORDER BY (data_length + index_length) DESC;"
```

---

## 🔐 Security Checklist

- [ ] SSL certificate installed and valid
- [ ] Environment file (.env) secured (600 permissions)
- [ ] Database credentials strong
- [ ] JWT secret generated
- [ ] Curlec API keys configured
- [ ] File permissions set correctly
- [ ] Firewall configured
- [ ] Regular security updates

---

## 📱 Default Login Credentials

### Admin Panel
- **URL**: https://yourdomain.com/admin
- **Email**: admin@khh.test
- **Password**: admin123

### Test Agents
- **Main Agent**: agent@khholdings.com / agent123
- **Level 2**: level2@khholdings.com / agent123
- **Level 3**: level3@khholdings.com / agent123
- **Sample Client**: client@example.com / client123

---

## 📞 Emergency Contacts

- **System Admin**: [Your Contact]
- **Database Admin**: [Your Contact]
- **Development Team**: [Your Contact]

---

## 🎯 Key URLs

- **Frontend**: https://yourdomain.com
- **Backend API**: https://yourdomain.com/api/
- **Admin Panel**: https://yourdomain.com/admin
- **Health Check**: https://yourdomain.com/api/v1/health

---

**💡 Tip**: Bookmark this reference card for quick access during production maintenance!
