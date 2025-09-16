# 🔐 Login Credentials for KHH Insurance Agent Portal

## Test User Credentials

### 👨‍💼 Main Agent Account
- **Agent Code:** `AGT12345`
- **Email:** `agt12345@khholdings.com`
- **Password:** `password123`
- **MLM Level:** 1 (Top Level)
- **Wallet Balance:** RM 1,500.00
- **Total Commission Earned:** RM 2,500.00

### 👨‍💼 Second Level Agent
- **Agent Code:** `AGT67890`
- **Email:** `agt67890@khholdings.com`
- **Password:** `password123`
- **MLM Level:** 2
- **Wallet Balance:** RM 800.00
- **Total Commission Earned:** RM 1,200.00
- **Referrer:** AGT12345

### 👤 Test Client
- **Email:** `client1@example.com`
- **Password:** `password123`
- **Type:** Client (Not Agent)
- **Referrer:** AGT12345

## 🌐 Application URLs

### Frontend
- **Development URL:** http://localhost:3000
- **Login Page:** http://localhost:3000/login

### Backend API
- **API Base URL:** http://localhost:8000/api/v1
- **API Documentation:** http://localhost:8000/api/documentation
- **PHPMyAdmin:** http://localhost:8080 (root/root123)

## 🎯 Login Options

The login page supports **TWO methods**:

### Method 1: Agent Code Login
1. Select "Agent Code" tab
2. Enter: `12345` (the system will prefix with "AGT")
3. Password: `password123`

### Method 2: Email Login
1. Select "Email" tab
2. Enter: `agt12345@khholdings.com`
3. Password: `password123`

## 🔧 Environment Configuration

The system is configured for **Development Environment**:
- API URL: `http://localhost:8000/api/v1`
- Debug Mode: Enabled
- Analytics: Disabled
- Curlec Payment: Test Mode

## 🧪 Testing Checklist

### ✅ Completed
- [x] Backend API running (Docker containers)
- [x] Database seeded with test users
- [x] Frontend environment configured
- [x] API bridge service fixed
- [x] Login API tested with curl

### 🚦 Ready for Testing
- [ ] Frontend login functionality
- [ ] Dashboard with MLM statistics
- [ ] Agent wallet and transactions
- [ ] Profile page with network data
- [ ] Payment integration (Curlec)
- [ ] Client registration flow
- [ ] Commission calculation system

## 💳 Payment Testing

### Curlec Test Credentials
- **Key ID:** `rzp_test_VMjSJlnmnfhL42`
- **Key Secret:** `JE2yCp33iypo2kANXym1uRFY`
- **Mode:** Sandbox/Test

## 📊 System Features

### MLM Network Structure
```
AGT12345 (Level 1)
├── AGT67890 (Level 2) 
└── Client1 (Customer)
```

### Commission Rates
- **MediPlan Coop:** T1-T5 structure
- **Senior Care Gold 270:** Custom rates from user specs
- **Senior Care Diamond 370:** Custom rates from user specs

### Commission Flow
1. Client purchases insurance policy
2. Payment processed via Curlec
3. Commission automatically calculated
4. Funds disbursed to agent wallets
5. Network levels updated

## 🚨 Important Notes

- **Session Management:** JWT tokens with auto-refresh
- **Network Detection:** Automatic MLM level calculation
- **Commission Automation:** Real-time disbursement after successful payment
- **Data Validation:** Comprehensive form validation on all pages
- **Security:** CSRF protection and input sanitization

## 🔍 Debugging

If you encounter issues:
1. Check browser console for API errors
2. Verify Docker containers are running: `docker ps`
3. Check backend logs: `docker logs khi_backend`
4. Verify database connection via PHPMyAdmin
5. Test API endpoints directly with curl

---

**System Status:** ✅ Ready for testing  
**Last Updated:** September 16, 2025  
**Version:** Development Build
