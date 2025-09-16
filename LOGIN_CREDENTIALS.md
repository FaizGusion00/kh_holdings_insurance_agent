# Login Credentials for KHH Insurance Agent System

## System Overview
The system now implements a proper MLM hierarchy with the correct payment flow:
- **Level 1 Agent**: No referrer_code (top level)
- **Level 2 Agent**: referrer_code points to Level 1 agent_code  
- **Clients**: mlm_level = 0, referred by agents

## Current Login Credentials

### Agent Login (Level 1 - Top Agent)
- **Agent Code**: `AGT12345`
- **Email**: `agt12345@khholdings.com`
- **Password**: `password123`
- **Type**: Agent (Level 1)
- **Referrer**: None (Top level)
- **Status**: Active

### Agent Login (Level 2 - Sub Agent)  
- **Agent Code**: `AGT00001`
- **Email**: `agt00001@khholdings.com`
- **Password**: `password123` 
- **Type**: Agent (Level 2)
- **Referrer**: AGT12345
- **Status**: Active

### Client Login
- **Email**: `test@gmail.com`
- **Password**: `password123`
- **Type**: Client (Level 0)
- **Referrer**: AGT12345
- **Status**: Active

## Login Methods
The system supports two login methods:
1. **Agent Code**: Use agent code (e.g., AGT12345) + password
2. **Email**: Use email address + password

## New Payment Flow
1. **Registration**: Click "Submit Registration" → Creates pending registration (NO users created yet)
2. **Payment**: Proceed to payment → Complete payment via Curlec/Razorpay
3. **Success**: After successful payment → Users are created in database + Commissions auto-distributed

## Commission Structure
- When a client payment is successful, commissions are automatically distributed up the MLM hierarchy
- Level 1 gets commission from direct clients
- Level 2 gets commission from Level 1's clients  
- And so on up to 5 levels

## Testing the New Flow
1. Login with AGT12345
2. Go to Profile → Medical Insurance tab
3. Click "Add Client" 
4. Fill multiple client details
5. Click "Submit Registration" (creates pending registration)
6. Click "Proceed to Payment" 
7. Complete payment (can use test mode)
8. Check that:
   - New users are created in database
   - Commissions are added to agent wallets
   - MLM hierarchy is respected