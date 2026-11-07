# TuitionPro ERP — Security Requirements & Defense Architecture

**Classification:** Enterprise Web Application Security  
**Standards Compliance:** OWASP Top 10 (2021/2025 Mitigation Baseline)  
**Authentication Standard:** RFC 7519 (JSON Web Tokens) with HS256 Signing  

---

## 1. Authentication & Session Defense

### 1.1 Password Hashing & Storage
- Passwords are encrypted using PHP's native `password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12])`.
- Plain-text passwords are never logged, cached, or persisted.

### 1.2 Brute-Force & Credential Stuffing Defense
- Dynamic tracking via the `login_attempts` table.
- Consecutive failed attempts from an IP address or against a user account trigger automatic rate-limiting after 5 failures.
- Rate-limiting lockout enforces an exponential cooldown or administrative unlock.

### 1.3 JWT Cryptographic Integrity
- **Algorithm Manipulation Defense:** Tokens specifying `"alg": "none"` or unapproved asymmetric keys are rejected immediately.
- **Payload Verification:** Signature verification is performed prior to decoding claims (`iss`, `aud`, `exp`, `sub`, `role`).
- **Refresh Token Rotation (RTR):** Refresh tokens can only be used once. Attempted re-use of an already revoked token triggers an automatic replay attack defense, immediately revoking all child tokens in the session family.
- **Explicit Revocation:** User logout invalidates the stored token record in `refresh_tokens`.

---

## 2. Authorization & Data Isolation

### 2.1 Server-Side Role Enforcement
- Authorization decisions are executed strictly in PHP middleware (`RoleMiddleware.php`) and controllers.
- Client-side DOM manipulation or hiding of CSS elements does not confer access to protected API endpoints or views.
- Unpermitted role requests consistently return `HTTP 403 Forbidden`.

### 2.2 Multi-Tenant Data Isolation
- Every relational query joins against `tenant_id = :tenant_id` resolved from the authenticated session context.
- Cross-tenant requests (e.g. modifying student `#105` belonging to Tenant 2 while authenticated under Tenant 1) yield 0 modified rows and log a security warning.

### 2.3 Anti-IDOR Defense (Insecure Direct Object Reference)
- Resource lookups (such as `/students/{id}`, `/fees/invoice/{id}`, `/portal/parent?child_id={id}`) verify explicit ownership:
  - Students must belong to the active tenant.
  - Invoices must belong to students of the active tenant.
  - Parents can only view children with active linkages in `student_guardians`.

---

## 3. Web Vulnerability Countermeasures

### 3.1 SQL Injection (SQLi)
- 100% of SQL database operations use parameterized queries through PDO prepared statements (`$stmt->execute(['param' => $value])`).
- Direct string concatenation into SQL statements is prohibited.

### 3.2 Cross-Site Scripting (XSS)
- All dynamic user data rendered into HTML views is sanitized using the global `e()` helper:
  ```php
  function e(?string $value): string {
      return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
  ```
- Output context is strictly HTML-escaped.

### 3.3 Cross-Site Request Forgery (CSRF)
- Synchronizer token pattern implemented for all state-changing HTTP requests (`POST`, `PUT`, `DELETE`).
- CSRF token is verified in `CsrfMiddleware.php`. Mismatches abort with `HTTP 419 Page Expired / Invalid CSRF Token`.

### 3.4 File Upload Security
- Uploaded student attachments and learning materials are validated against a strict MIME whitelist (e.g., `image/jpeg`, `image/png`, `application/pdf`).
- Executable extensions (`.php`, `.phtml`, `.exe`, `.sh`, `.bat`) are blocked.
- Uploaded files are renamed using cryptographically secure hashes (`bin2hex(random_bytes(16))`) and stored outside public web access where practical.

---

## 4. Audit Logging & Non-Repudiation

The `AuditService` logs all critical security and financial actions into the `audit_logs` table:
- User login / logout / failed authentication
- Student admission / profile updates / deletions
- Fee collections / refund approvals / journal postings
- Attendance overrides and grade publications

Each audit entry captures: `tenant_id`, `user_id`, `event_type`, `resource_type`, `resource_id`, `ip_address`, `user_agent`, and `timestamp`. Audit records cannot be altered or deleted by application users.
