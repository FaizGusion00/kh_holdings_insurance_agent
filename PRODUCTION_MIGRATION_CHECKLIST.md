# 📋 Production Migration Checklist
## KH Holdings Insurance Agent System

This checklist ensures all database migrations are applied correctly in production.

---

## ✅ Pre-Migration Checklist

### 1. Backup Current Database
```bash
# Create full database backup
mysqldump -u username -p kh_holdings_insurance > backup_before_migration_$(date +%Y%m%d_%H%M%S).sql

# Verify backup
ls -la backup_before_migration_*.sql
```

### 2. Check Current Migration Status
```bash
# Check which migrations have been run
php artisan migrate:status

# Expected output should show all migrations as "Ran"
```

### 3. Verify Environment
```bash
# Check environment is set to production
grep APP_ENV .env
# Should show: APP_ENV=production

# Check debug is disabled
grep APP_DEBUG .env
# Should show: APP_DEBUG=false
```

---

## 🗄️ Migration Execution

### 1. Run All Migrations
```bash
# Run migrations in production mode
php artisan migrate --force

# Expected output:
# INFO  Running migrations.
# 2025_09_17_145436_create_admins_table ................... DONE
# 2025_09_17_145436_create_insurance_plans_table .......... DONE
# 2025_09_17_145437_create_agent_wallets_table ............ DONE
# 2025_09_17_145437_create_commission_rates_table .......... DONE
# 2025_09_17_145437_create_commission_transactions_table .. DONE
# 2025_09_17_145437_create_gateway_records_table .......... DONE
# 2025_09_17_145437_create_hospitals_table ................ DONE
# 2025_09_17_145437_create_member_policies_table .......... DONE
# 2025_09_17_145437_create_payment_transactions_table ..... DONE
# 2025_09_17_145437_create_pending_payments_table .......... DONE
# 2025_09_17_145437_create_wallet_transactions_table ...... DONE
# 2025_09_17_145437_create_withdrawal_requests_table ...... DONE
# 2025_09_18_120000_update_commission_transactions_schema . DONE
# 2025_09_18_121500_update_users_add_profile_fields ....... DONE
# 2025_09_18_171041_add_medical_fields_to_users_table ..... DONE
# 2025_09_19_181659_add_remember_token_to_admins_table .... DONE
# 2025_09_20_121048_add_commitment_fee_to_insurance_plans_table DONE
# 2025_09_20_124256_add_missing_medical_columns_to_users_table DONE
# 2025_09_20_131849_create_network_levels_table ........... DONE
# 2025_09_20_202139_create_pending_registrations_table .... DONE
# 2025_09_21_063801_remove_customer_type_from_users_table . DONE
# 2025_09_21_132516_calculate_network_levels_for_all_agents DONE
```

### 2. Verify Migration Success
```bash
# Check migration status again
php artisan migrate:status

# All migrations should show "Ran" status
```

---

## 🌱 Database Seeding

### 1. Seed Initial Data
```bash
# Seed the database with initial data
php artisan db:seed --force

# Expected output:
# INFO  Seeding database.
# Calculating network levels for all users...
# Calculated network levels for AGT00001
# Calculated network levels for AGT00002
# Calculated network levels for AGT00003
# Calculated network levels for AGT00004
# Created 4 users with proper MLM hierarchy:
# 1. Main Agent (AGT00001) - Top Level
# 2. Level 2 Agent (AGT00002) - Referred by AGT00001
# 3. Level 3 Agent (AGT00003) - Referred by AGT00002
# 4. Sample Client (AGT00004) - Referred by AGT00003
# Network levels calculated for all agents!
```

### 2. Verify Seeded Data
```bash
# Check users were created
php artisan tinker --execute="echo 'Users: ' . \App\Models\User::count();"

# Check network levels were calculated
php artisan tinker --execute="echo 'Network levels: ' . \App\Models\NetworkLevel::count();"

# Check commission rates
php artisan tinker --execute="echo 'Commission rates: ' . \App\Models\CommissionRate::count();"

# Check insurance plans
php artisan tinker --execute="echo 'Insurance plans: ' . \App\Models\InsurancePlan::count();"
```

---

## 🔍 Post-Migration Verification

### 1. Test Database Connections
```bash
# Test database connection
php artisan tinker --execute="echo 'DB Connection: ' . (\DB::connection()->getPdo() ? 'OK' : 'FAILED');"
```

### 2. Test Network Levels
```bash
# Test network levels for each agent
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

# Expected output:
# AGT00001: 3 downlines
# AGT00002: 2 downlines
# AGT00003: 1 downlines
# AGT00004: 0 downlines
```

### 3. Test Agent Code Generation
```bash
# Test agent code generation
php artisan tinker --execute="
\$user = new \App\Models\User();
\$user->name = 'Test User';
\$user->email = 'test@example.com';
\$user->password = bcrypt('password');
\$user->agent_code = 'AGT' . str_pad((string) (\App\Models\User::whereNotNull('agent_code')->count() + 1), 5, '0', STR_PAD_LEFT);
echo 'Next agent code would be: ' . \$user->agent_code;
"

# Expected output: Next agent code would be: AGT00005
```

### 4. Test Commission Calculation
```bash
# Test commission calculation
php artisan tinker --execute="
\$user = \App\Models\User::where('agent_code', 'AGT00001')->first();
if(\$user) {
    \$controller = new \App\Http\Controllers\MlmController();
    \$response = \$controller->getCommissionSummary();
    echo 'Commission API Response: ' . json_encode(\$response->getData(), JSON_PRETTY_PRINT);
}
"
```

---

## 🚨 Troubleshooting

### If Migrations Fail

#### 1. Check Database Permissions
```bash
# Verify database user has all privileges
mysql -u username -p -e "SHOW GRANTS FOR 'kh_holdings_user'@'localhost';"
```

#### 2. Check Table Existence
```bash
# List all tables
mysql -u username -p -e "USE kh_holdings_insurance; SHOW TABLES;"
```

#### 3. Check Migration Logs
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check specific migration errors
grep -i "migration" storage/logs/laravel.log
```

### If Network Levels Don't Calculate

#### 1. Manual Network Level Rebuild
```bash
# Rebuild network levels manually
php artisan network:rebuild --force
```

#### 2. Check Network Level Service
```bash
# Test network level service directly
php artisan tinker --execute="
\$service = new \App\Services\NetworkLevelService();
\$result = \$service->calculateNetworkLevelsForAgent('AGT00001');
echo 'Result: ' . (\$result ? 'Success' : 'Failed');
"
```

### If Seeding Fails

#### 1. Check for Duplicate Data
```bash
# Check for existing users
php artisan tinker --execute="echo 'Existing users: ' . \App\Models\User::count();"
```

#### 2. Clear and Re-seed
```bash
# Clear specific tables (BE CAREFUL!)
php artisan tinker --execute="
\App\Models\User::truncate();
\App\Models\NetworkLevel::truncate();
\App\Models\AgentWallet::truncate();
echo 'Tables cleared';
"

# Re-run seeding
php artisan db:seed --force
```

---

## ✅ Final Verification Checklist

- [ ] All migrations completed successfully
- [ ] Database seeded with initial data
- [ ] Network levels calculated for all agents
- [ ] Agent codes generating correctly (AGT + 5 digits)
- [ ] Commission rates configured
- [ ] Insurance plans created
- [ ] Admin user created
- [ ] Test agents can see their downlines
- [ ] API endpoints responding correctly
- [ ] Frontend loading properly
- [ ] Admin panel accessible

---

## 📞 Support

If you encounter any issues during migration:

1. **Check the logs**: `tail -f storage/logs/laravel.log`
2. **Verify database**: Check table structure and data
3. **Test individual components**: Use tinker commands above
4. **Contact support**: [Your Contact Information]

---

**🎉 Migration Complete!** Your KH Holdings Insurance Agent system is now ready for production use.
