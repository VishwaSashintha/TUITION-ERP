# TuitionPro ERP — Automated Testing & Quality Assurance Guide

**Test Framework:** Native PHP CLI Automated Verification Suites  
**Total Tests:** 146  
**Pass Rate:** 100% (146 Passed, 0 Failed)  

---

## 1. Test Suite Overview

TuitionPro ERP includes a comprehensive automated test harness covering security, backend business logic, end-to-end user lifecycles, HTTP routing, and role-based dashboard RBAC defenses.

| Test File | Focus Area | Test Count | Status |
|---|---|:---:|:---:|
| `tests/test_jwt_auth.php` | Cryptographic JWT signing, token rotation, replay defense, lockout | 16 | **PASS (16/16)** |
| `tests/test_backend_core.php` | Multi-tenant isolation, invoicing, double-entry balance, trial balance | 16 | **PASS (16/16)** |
| `tests/test_e2e_workflows.php` | Student admission, batch enrollment, session, QR attendance, payments | 28 | **PASS (28/28)** |
| `tests/test_http_routes.php` | Complete route catalog response verification (HTTP 200/302) | 59 | **PASS (59/59)** |
| `tests/test_role_dashboards.php` | Role dashboard routing, Anti-IDOR, front-desk/teacher 403 blocks | 27 | **PASS (27/27)** |

---

## 2. Test Execution Instructions

### Prerequisites
Ensure the MySQL database daemon is running and the PHP development web server is listening on `http://localhost:8080`:
```bash
# Terminal 1: Run MySQL Daemon
mysqld --console

# Terminal 2: Start PHP Built-in Server
php -S localhost:8080 -t public
```

### 2.1 Run All Test Suites Sequentially
Execute the following one-line command to run all 146 automated tests:
```bash
php tests/test_jwt_auth.php && php tests/test_backend_core.php && php tests/test_e2e_workflows.php && php tests/test_http_routes.php && php tests/test_role_dashboards.php
```

---

## 3. Detailed Test Assertions

### 3.1 JWT Authentication Suite (`test_jwt_auth.php`)
- **Token Issuance:** Confirms access token and refresh token generation with valid claims.
- **Tampering Defense:** Modifies token payload and counterfeit signature; verifies strict rejection.
- **Algorithm Protection:** Injects `"alg": "none"`; verifies rejection.
- **Rotation & Replay:** Re-uses expired/revoked refresh tokens; verifies immediate revocation of token family.
- **Rate-Limiting:** Simulates 5 failed logins; verifies IP/account lockout.

### 3.2 Backend Core Suite (`test_backend_core.php`)
- **Multi-Tenant Data Isolation:** Cross-tenant reads return `null`; cross-tenant writes affect 0 rows.
- **Dynamic Fee Invoicing:** Partial payment updates invoice balance and status; over-allocation throws exception.
- **Teacher Compensation:** Verifies percentage and hourly revenue distribution formulas.
- **Double-Entry General Ledger:** Balanced journals post successfully; unbalanced journals ($\text{Debits} \neq \text{Credits}$) are rejected with `AccountingException`.
- **Trial Balance:** Verifies that institute total debits strictly equal total credits.

### 3.3 E2E Workflow Suite (`test_e2e_workflows.php`)
- **Lifecycle Execution:** Student registration $\rightarrow$ Batch enrollment $\rightarrow$ Schedule session $\rightarrow$ QR scan $\rightarrow$ Invoice generation $\rightarrow$ Partial & final payments $\rightarrow$ Teacher compensation $\rightarrow$ General Ledger posting.

### 3.4 HTTP Route Suite (`test_http_routes.php`)
- Validates 59 public and protected system routes across all functional modules.

### 3.5 Role Dashboards & RBAC Suite (`test_role_dashboards.php`)
- Confirms Super Admin, Institute Owner, Receptionist, Teacher, Student, and Parent dashboard dispatch.
- Confirms Receptionists receive front-desk view and receive `HTTP 403` on `/accounting/*` and `/teacher-payments`.
- Confirms Teachers receive academic view and receive `HTTP 403` on `/fees` and `/accounting/*`.
- Tests parent child switcher with invalid and cross-family IDs to verify Anti-IDOR defense.
- Verifies exact 6-color palette variables and Roboto / Poppins typography in CSS and views.
