# KH Holdings Insurance Agent API Documentation

This document describes available API endpoints, request/response formats, authentication, common flows, and troubleshooting notes.

## Base URL

- Development: `http://localhost:8000/api`

All endpoints return JSON.

## Authentication

- Scheme: Bearer token (Laravel Sanctum)
- Obtain token via login and include header: `Authorization: Bearer <token>`

### POST /auth/login

Request body:
```
{
  "agent_code": "AGT00000",
  "password": "demo123"
}
```

Responses:
- 200 OK
```
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { /* User object */ },
    "token": "<token>",
    "token_type": "Bearer"
  }
}
```
- 401/422 with `message` and optional `errors` map

### POST /auth/logout
Header: `Authorization: Bearer <token>`

## Profile

### GET /profile
Returns current user and profile completeness

### PUT /profile
Update profile fields
```
{
  "name": "...",
  "current_password": "...",
  "address": "...",
  "city": "...",
  "state": "...",
  "postal_code": "..."
}
```

### PUT /profile/change-password
```
{
  "current_password": "old",
  "new_password": "newPass123",
  "new_password_confirmation": "newPass123"
}
```

### PUT /profile/change-phone
```
{
  "current_phone": "+60123456700",
  "new_phone": "+60123456701",
  "tac_code": "123456"
}
```

### PUT /profile/bank-info
```
{
  "current_password": "...",
  "bank_name": "Maybank",
  "bank_account_number": "1234567890",
  "bank_account_owner": "John Doe"
}
```

## TAC

### POST /tac/send
```
{
  "phone_number": "+60123456700",
  "purpose": "change_phone"
}
```

### POST /tac/verify
```
{
  "phone_number": "+60123456700",
  "tac_code": "123456",
  "purpose": "change_phone"
}
```

## Dashboard

### GET /dashboard
Returns stats, recent activities, and performance data.

## Members

- Auth required for all

### GET /members
Query params: `page`, `search`, `status`

### POST /members
```
{
  "name": "...",
  "nric": "...",
  "phone": "...",
  "email": "...",
  "address": "...",
  "date_of_birth": "YYYY-MM-DD",
  "gender": "male|female|other",
  "occupation": "...",
  "relationship_with_agent": "...",
  "emergency_contact_name": "...",
  "emergency_contact_phone": "...",
  "emergency_contact_relationship": "..."
}
```

### PUT /members/{id}
Partial update of member

### DELETE /members/{id}
Remove member

## Payments

### GET /payments
Summary stats, recent payments, active mandates

### GET /payments/history
Query params: `page`, `month`, `year`, `status`, `type`

### GET /payments/mandates
List mandates

### POST /payments/process
```
{
  "member_id": 1,
  "policy_id": 10, // optional
  "amount": 100.00,
  "payment_type": "one_time",
  "payment_method": "card",
  "description": "..."
}
```

### POST /payments/setup-mandate
```
{
  "member_id": 1,
  "policy_id": 10, // optional
  "mandate_type": "recurring",
  "frequency": "monthly",
  "amount": 50.00,
  "start_date": "YYYY-MM-DD",
  "end_date": "YYYY-MM-DD", // optional
  "bank_account": "...",
  "bank_name": "..."
}
```

## Healthcare

### GET /healthcare
All facilities

### GET /healthcare/hospitals
Hospitals only

### GET /healthcare/clinics
Clinics only

### GET /healthcare/search?q=...
Search facilities

## Records

### GET /records/sharing
Monthly performance and case counts

### GET /records/performance
Performance data

## How to test APIs in the browser

- For GET endpoints, paste the URL in the browser with proper query params and the `Authorization` header through a browser extension like "ModHeader" or by using a REST client (Insomnia/Postman).
- For authenticated requests, first login using curl/Postman to get a token, then add `Authorization: Bearer <token>` for subsequent calls.

### Quick curl examples

```
# Login
curl -s http://localhost:8000/api/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"agent_code":"AGT00000","password":"demo123"}'

# Get profile
curl -s http://localhost:8000/api/profile \
  -H "Authorization: Bearer <TOKEN>"

# Members
curl -s 'http://localhost:8000/api/members?page=1&search=' \
  -H "Authorization: Bearer <TOKEN>"
```

## Error format

```
{
  "success": false,
  "message": "Validation failed",
  "errors": { "field": ["message"] }
}
```

## Notes
- Login uses `agent_code` only (format `AGT00001`).
- Use phpMyAdmin at `http://localhost:8080` to browse DB (host: db, user: khh_user, password: khh_secure_password_2024).
