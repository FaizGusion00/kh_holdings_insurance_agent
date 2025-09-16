# 🧪 Testing Guide - KHH Insurance Agent Portal

## 🎯 System Status: ✅ READY FOR TESTING

All TypeScript errors have been fixed, backend API is running, test users are created, and the frontend is successfully configured.

## 🚀 Quick Start Testing

### 1. Access the Application
- **Frontend URL:** http://localhost:3000
- **Auto-redirect to login:** Yes ✅

### 2. Login Test
- **Agent Code:** `AGT12345` or `AGT67890`
- **Email:** `agt12345@khholdings.com` or `agt67890@khholdings.com`  
- **Password:** `password123`

### 3. Expected Flow
1. Homepage redirects to `/login`
2. Login with credentials
3. Successful authentication → JWT token stored
4. Redirect to `/dashboard`
5. Access all protected pages

## 📋 Complete Testing Checklist

### 🔐 Authentication & Session Management
- [ ] **Login Page Load** - Verify login form displays correctly
- [ ] **Agent Code Login** - Test with AGT12345 format
- [ ] **Email Login** - Test with email format
- [ ] **Invalid Credentials** - Test error handling
- [ ] **JWT Token Storage** - Verify token persistence
- [ ] **Auto-redirect** - Unauthenticated users redirect to login
- [ ] **Session Timeout** - Test token expiration handling
- [ ] **Logout** - Test logout functionality

### 📊 Dashboard Functionality
- [ ] **Dashboard Load** - Main dashboard displays
- [ ] **Statistics Display** - Total members, new members, etc.
- [ ] **Recent Activities** - Activity feed loads
- [ ] **Performance Metrics** - Charts and graphs render
- [ ] **Member Count** - Network member statistics
- [ ] **Quick Actions** - Navigation buttons work

### 💰 Agent Wallet
- [ ] **Wallet Balance** - Current balance displays (RM 1,500.00)
- [ ] **Transaction History** - Commission records
- [ ] **Withdrawal Requests** - Request withdrawal form
- [ ] **Commission Details** - Detailed commission breakdown
- [ ] **Payment Methods** - Bank information display

### 👤 Profile Management
- [ ] **User Information** - Profile data displays correctly
- [ ] **Edit Profile** - Update personal information
- [ ] **Change Password** - Password update functionality
- [ ] **Bank Information** - Bank details form
- [ ] **MLM Network** - Network tree visualization
- [ ] **Referral Links** - Generate referral links

### 🏥 Healthcare Facilities
- [ ] **Hospitals List** - Display hospital directory
- [ ] **Clinics List** - Display clinic directory
- [ ] **Search Function** - Search by name/location
- [ ] **Facility Details** - Detailed facility information

### 🎯 MLM Network & Commissions
- [ ] **Network Levels** - T1-T5 structure display
- [ ] **Referral Tree** - Visual network representation
- [ ] **Commission Rates** - Correct rates per plan
- [ ] **Downline Members** - List of referred users
- [ ] **Commission History** - Historical commission data

### 👥 Client Management
- [ ] **Add New Client** - Client registration form
- [ ] **Client List** - Display all clients
- [ ] **Client Details** - Individual client information
- [ ] **Policy Management** - Client insurance policies

### 💳 Payment Integration (Curlec)
- [ ] **Payment Form** - Insurance payment page
- [ ] **Curlec Integration** - Payment gateway loads
- [ ] **Test Payments** - Process test transactions
- [ ] **Payment Success** - Success page handling
- [ ] **Payment Failure** - Error handling
- [ ] **Commission Disbursement** - Auto-commission after payment

### 📱 Responsive Design
- [ ] **Mobile View** - All pages responsive
- [ ] **Tablet View** - Medium screen optimization
- [ ] **Desktop View** - Large screen layout
- [ ] **Navigation** - Mobile navigation menu

### 🔔 Notifications
- [ ] **Notification Bell** - Notification indicator
- [ ] **Notification List** - List of notifications
- [ ] **Mark as Read** - Mark notifications read
- [ ] **Delete Notifications** - Remove notifications

## 🐛 Known Issues & Fixes Applied

### ✅ Fixed Issues
- [x] TypeScript compilation errors
- [x] API response format compatibility  
- [x] Missing interface properties
- [x] Authentication flow
- [x] Environment configuration
- [x] Docker containers setup

### 🔍 Areas to Watch
- **Payment Gateway:** Test mode enabled
- **Commission Calculation:** Verify accuracy
- **Network Levels:** Check MLM structure
- **Database Connections:** Monitor performance

## 📊 Test Data Available

### User Accounts
- **AGT12345:** Main agent, Level 1, RM 1,500 balance
- **AGT67890:** Sub-agent, Level 2, RM 800 balance  
- **Client1:** Test client under AGT12345

### Insurance Plans
- **MediPlan Coop:** T1-T5 commission structure
- **Senior Care Gold 270:** Custom commission rates
- **Senior Care Diamond 370:** Custom commission rates

## 🚨 Critical Test Scenarios

### 1. End-to-End Registration & Payment Flow
1. Agent logs in → Dashboard
2. Add new client → Client form
3. Select insurance plan → Payment
4. Process payment → Success
5. Verify commission disbursement
6. Check agent wallet balance increase

### 2. MLM Network Commission Test
1. Create client under AGT12345
2. Process insurance payment
3. Verify commission calculation
4. Check AGT12345 wallet increase
5. Verify network level updates

### 3. Multi-Level Commission Distribution
1. Create structure: AGT12345 → AGT67890 → Client
2. Client purchases insurance
3. Verify commission split between agents
4. Check both agent wallets

## 📞 Support & Debugging

### Browser Console
- Check for JavaScript errors
- Monitor API calls in Network tab
- Verify JWT token in localStorage

### Backend Logs
```bash
docker logs khi_backend
```

### Database Access
- **PHPMyAdmin:** http://localhost:8080
- **Username:** root  
- **Password:** root123

### API Testing
```bash
# Test login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "agt12345@khholdings.com", "password": "password123"}'
```

---

## ✅ Final Status

**System Status:** 🟢 Ready for comprehensive testing  
**Backend API:** 🟢 Running (Docker)  
**Frontend App:** 🟢 Running (Next.js)  
**Database:** 🟢 Connected (MySQL)  
**Test Data:** 🟢 Seeded  
**TypeScript:** 🟢 No errors  
**Environment:** 🟢 Development mode  

**Ready to test all features!** 🚀
