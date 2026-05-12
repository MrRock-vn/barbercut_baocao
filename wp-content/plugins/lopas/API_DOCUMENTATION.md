# LOPAS REST API Documentation

## Overview

The LOPAS REST API provides endpoints for mobile apps and external integrations to interact with the booking system.

**Base URL**: `https://yoursite.com/wp-json/lopas/v1`

**API Version**: 1.0.0

## Authentication

### JWT Token Authentication

The API uses JWT (JSON Web Tokens) for authentication.

#### Login
```
POST /auth/login
Content-Type: application/json

{
  "username": "user@example.com",
  "password": "password123"
}

Response:
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "username": "user@example.com",
    "email": "user@example.com",
    "name": "John Doe"
  }
}
```

#### Using Token
Include the token in the Authorization header:
```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

#### Logout
```
POST /auth/logout
Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "Logged out successfully"
}
```

#### Get Current User
```
GET /auth/me
Authorization: Bearer {token}

Response:
{
  "success": true,
  "user": {
    "id": 1,
    "username": "user@example.com",
    "email": "user@example.com",
    "name": "John Doe",
    "roles": ["customer"]
  }
}
```

#### Refresh Token
```
POST /auth/refresh
Authorization: Bearer {token}

Response:
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

## Endpoints

### Salons

#### Get All Salons
```
GET /salons?page=1&per_page=20&status=active

Response:
{
  "success": true,
  "data": {
    "items": [...],
    "total": 50,
    "page": 1,
    "per_page": 20,
    "total_pages": 3
  }
}
```

#### Get Single Salon
```
GET /salons/{id}

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Angel Barber Shop",
    "description": "Professional barber services",
    "address": "123 Main St",
    "phone": "0123456789",
    "email": "salon@example.com",
    "opening_time": "08:00:00",
    "closing_time": "18:00:00",
    "status": "active"
  }
}
```

#### Create Salon (Admin Only)
```
POST /salons
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "New Salon",
  "description": "Description",
  "address": "Address",
  "phone": "Phone",
  "email": "Email",
  "opening_time": "08:00:00",
  "closing_time": "18:00:00",
  "status": "active"
}
```

#### Update Salon (Admin Only)
```
PUT /salons/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Updated Name",
  "status": "active"
}
```

#### Delete Salon (Admin Only)
```
DELETE /salons/{id}
Authorization: Bearer {token}
```

### Services

#### Get All Services
```
GET /services?page=1&per_page=20&status=active&category=hair

Response:
{
  "success": true,
  "data": {
    "items": [...],
    "total": 100,
    "page": 1,
    "per_page": 20,
    "total_pages": 5
  }
}
```

#### Get Single Service
```
GET /services/{id}
```

#### Get Services by Salon
```
GET /salons/{salon_id}/services?page=1&per_page=20
```

#### Create Service (Admin Only)
```
POST /services
Authorization: Bearer {token}
Content-Type: application/json

{
  "salon_id": 1,
  "name": "Haircut",
  "description": "Professional haircut",
  "category": "hair",
  "price": 50000,
  "duration": 30,
  "status": "active"
}
```

#### Update Service (Admin Only)
```
PUT /services/{id}
Authorization: Bearer {token}
```

#### Delete Service (Admin Only)
```
DELETE /services/{id}
Authorization: Bearer {token}
```

### Bookings

#### Get User Bookings
```
GET /bookings?page=1&per_page=20&status=pending
Authorization: Bearer {token}

Response:
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "booking_code": "BK001",
        "user_id": 1,
        "salon_id": 1,
        "service_id": 1,
        "booking_date": "2026-05-15",
        "booking_time": "10:00:00",
        "status": "pending",
        "notes": "Notes",
        "created_at": "2026-05-09 10:00:00"
      }
    ],
    "total": 5,
    "page": 1,
    "per_page": 20,
    "total_pages": 1
  }
}
```

#### Get Single Booking
```
GET /bookings/{id}
Authorization: Bearer {token}
```

#### Create Booking
```
POST /bookings
Authorization: Bearer {token}
Content-Type: application/json

{
  "salon_id": 1,
  "service_id": 1,
  "booking_date": "2026-05-15",
  "booking_time": "10:00:00",
  "notes": "Optional notes"
}

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "booking_code": "BK001",
    "status": "pending"
  }
}
```

#### Update Booking
```
PUT /bookings/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "booking_date": "2026-05-16",
  "booking_time": "11:00:00"
}
```

#### Cancel Booking
```
POST /bookings/{id}/cancel
Authorization: Bearer {token}

Response:
{
  "success": true,
  "message": "Booking cancelled successfully"
}
```

#### Get Available Slots
```
GET /bookings/available-slots?salon_id=1&date=2026-05-15

Response:
{
  "success": true,
  "data": {
    "salon_id": 1,
    "date": "2026-05-15",
    "available_slots": [
      "08:00:00",
      "08:30:00",
      "09:00:00",
      ...
    ]
  }
}
```

### Payments

#### Get User Payments
```
GET /payments?page=1&per_page=20&status=success
Authorization: Bearer {token}

Response:
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "transaction_code": "TXN001",
        "order_id": 1,
        "amount": 50000,
        "payment_method": "vnpay",
        "status": "success",
        "created_at": "2026-05-09 10:00:00"
      }
    ],
    "total": 10,
    "page": 1,
    "per_page": 20,
    "total_pages": 1
  }
}
```

#### Get Single Payment
```
GET /payments/{id}
Authorization: Bearer {token}
```

#### Create Payment
```
POST /payments
Authorization: Bearer {token}
Content-Type: application/json

{
  "order_id": 1,
  "payment_method": "vnpay"
}

Response:
{
  "success": true,
  "data": {
    "payment": {
      "id": 1,
      "transaction_code": "TXN001",
      "status": "pending"
    },
    "payment_url": "https://pay.vnpayment.vn/..."
  }
}
```

#### Get Payment Methods
```
GET /payment-methods

Response:
{
  "success": true,
  "data": [
    {
      "id": "cod",
      "name": "Cash on Delivery",
      "description": "Pay when service is completed",
      "enabled": true
    },
    {
      "id": "vnpay",
      "name": "VNPay",
      "description": "Pay online using VNPay",
      "enabled": true
    }
  ]
}
```

## Error Responses

### 400 Bad Request
```json
{
  "code": "error_code",
  "message": "Error message",
  "data": {
    "status": 400
  }
}
```

### 401 Unauthorized
```json
{
  "code": "unauthorized",
  "message": "Invalid or expired token",
  "data": {
    "status": 401
  }
}
```

### 403 Forbidden
```json
{
  "code": "forbidden",
  "message": "You don't have permission",
  "data": {
    "status": 403
  }
}
```

### 404 Not Found
```json
{
  "code": "not_found",
  "message": "Resource not found",
  "data": {
    "status": 404
  }
}
```

## Rate Limiting

API requests are limited to:
- 100 requests per minute for authenticated users
- 20 requests per minute for unauthenticated users

Rate limit headers:
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1620000000
```

## Pagination

All list endpoints support pagination:
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 20, max: 100)

Response includes:
- `items` - Array of items
- `total` - Total number of items
- `page` - Current page
- `per_page` - Items per page
- `total_pages` - Total number of pages

## Filtering

### Salons
- `status` - Filter by status (active, inactive)

### Services
- `status` - Filter by status (active, inactive)
- `category` - Filter by category

### Bookings
- `status` - Filter by status (pending, confirmed, completed, cancelled)

### Payments
- `status` - Filter by status (pending, success, failed)

## Examples

### JavaScript/Fetch
```javascript
// Login
const response = await fetch('https://yoursite.com/wp-json/lopas/v1/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    username: 'user@example.com',
    password: 'password123'
  })
});

const data = await response.json();
const token = data.token;

// Get bookings
const bookingsResponse = await fetch('https://yoursite.com/wp-json/lopas/v1/bookings', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const bookings = await bookingsResponse.json();
```

### cURL
```bash
# Login
curl -X POST https://yoursite.com/wp-json/lopas/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"user@example.com","password":"password123"}'

# Get bookings
curl -X GET https://yoursite.com/wp-json/lopas/v1/bookings \
  -H "Authorization: Bearer {token}"
```

### Python
```python
import requests

# Login
response = requests.post('https://yoursite.com/wp-json/lopas/v1/auth/login', json={
    'username': 'user@example.com',
    'password': 'password123'
})

token = response.json()['token']

# Get bookings
headers = {'Authorization': f'Bearer {token}'}
bookings = requests.get('https://yoursite.com/wp-json/lopas/v1/bookings', headers=headers)
```

## Support

For API support:
- Check this documentation
- Review error messages
- Check WordPress error logs

---

**Version**: 1.0.0  
**Last Updated**: May 9, 2026
