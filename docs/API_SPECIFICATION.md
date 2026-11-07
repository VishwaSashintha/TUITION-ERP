# TuitionPro ERP — API Specification

**Specification Version:** 2.0-REST  
**Authentication:** JSON Web Token (JWT) Bearer Token (HS256) & Session Cookies  
**Base URL:** `http://localhost:8080/api` or `https://tuitionpro.lk/api`  
**Content-Type:** `application/json`  

---

## 1. Authentication Endpoints

### 1.1 Issue JWT Access & Refresh Token
- **Endpoint:** `POST /api/auth/token`
- **Headers:** `Content-Type: application/json`
- **Payload:**
```json
{
  "email": "owner@apex.lk",
  "password": "Password@123",
  "tenant_code": "apex"
}
```
- **Response (200 OK):**
```json
{
  "status": "success",
  "data": {
    "access_token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "token_type": "Bearer",
    "expires_in": 900,
    "refresh_token": "def50200...",
    "user": {
      "id": 2,
      "name": "Kamal Perera",
      "email": "owner@apex.lk",
      "role": "institute_owner"
    }
  }
}
```

### 1.2 Rotate Refresh Token
- **Endpoint:** `POST /api/auth/refresh`
- **Payload:**
```json
{
  "refresh_token": "def50200..."
}
```
- **Response (200 OK):** Returns a newly issued access token and rotated refresh token; revokes the previous token in `refresh_tokens`.

### 1.3 Revoke Token / Logout
- **Endpoint:** `POST /api/auth/revoke`
- **Headers:** `Authorization: Bearer <access_token>`
- **Response (200 OK):**
```json
{
  "status": "success",
  "message": "Token revoked successfully."
}
```

---

## 2. Student Management Endpoints

### 2.1 List Students (Paginated)
- **Endpoint:** `GET /api/students`
- **Headers:** `Authorization: Bearer <access_token>`
- **Query Parameters:** `page=1`, `term=Nimal`, `branch_id=1`, `grade=12`
- **Response (200 OK):**
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "last_page": 4,
    "total": 52,
    "students": [
      {
        "id": 14,
        "admission_number": "APX-2026-0014",
        "full_name": "Kasun Kalhara",
        "grade": "12",
        "branch_name": "Colombo Campus",
        "status": "active"
      }
    ]
  }
}
```

### 2.2 Register Student
- **Endpoint:** `POST /api/students`
- **Headers:** `Authorization: Bearer <access_token>`
- **Payload:**
```json
{
  "first_name": "Dilini",
  "last_name": "Fonseka",
  "admission_date": "2026-11-01",
  "branch_id": 1,
  "gender": "female",
  "guardian_name": "Sunil Fonseka",
  "guardian_phone": "0771234567",
  "guardian_relation": "Father"
}
```
- **Response (201 Created):** Returns created student details with assigned admission number.

---

## 3. Attendance Endpoints

### 3.1 QR Scanner Check-In
- **Endpoint:** `POST /api/attendance/scan-qr`
- **Headers:** `Authorization: Bearer <access_token>`
- **Payload:**
```json
{
  "qr_payload": "TUITIONPRO:APX:APX-2026-0014",
  "session_id": 5
}
```
- **Response (200 OK):**
```json
{
  "status": "success",
  "message": "Attendance marked as present.",
  "student": {
    "admission_number": "APX-2026-0014",
    "name": "Kasun Kalhara"
  },
  "timestamp": "2026-11-07 14:30:00"
}
```

---

## 4. Fee & Invoicing Endpoints

### 4.1 Collect Payment
- **Endpoint:** `POST /api/fees/collect-payment`
- **Headers:** `Authorization: Bearer <access_token>`
- **Payload:**
```json
{
  "invoice_id": 12,
  "amount": 4000.00,
  "payment_method": "cash",
  "remarks": "November tuition installment"
}
```
- **Response (200 OK):**
```json
{
  "status": "success",
  "message": "Payment recorded successfully.",
  "receipt_number": "REC-APX-0012",
  "invoice_balance": 1000.00,
  "invoice_status": "partially_paid"
}
```

---

## 5. Security & Error Response Format

All error responses strictly adhere to uniform JSON payloads:
```json
{
  "status": "error",
  "error_code": "FORBIDDEN",
  "message": "Insufficient role permissions to access this endpoint."
}
```
Standard HTTP status codes utilized:
- `400 Bad Request`: Validation failure.
- `401 Unauthorized`: Missing or invalid JWT token.
- `403 Forbidden`: Insufficient role permissions or cross-tenant attempt.
- `404 Not Found`: Resource does not exist within active tenant.
- `429 Too Many Requests`: Rate-limiting triggered (e.g. brute-force defense).
- `500 Internal Server Error`: Unhandled server exception.

