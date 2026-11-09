# TuitionPro ERP — Backend Architecture

The backend of TuitionPro ERP is built on native PHP 8.2+ using a lightweight, custom MVC architecture with strict typing, prepared statements, and complete multi-tenant data isolation.

## Directory Structure

```
backend/
├── app/
│   ├── Controllers/        # Domain controllers (20 controllers across Academic, Fees, Attendance, etc.)
│   ├── Core/               # MVC Core: Application, Router, Request, Response, Database, View, Session, Logger
│   ├── Helpers/            # Helper functions: auth_user, url, asset, format_currency, etc.
│   ├── Middleware/         # Security pipeline: AuthMiddleware, RoleMiddleware, TenantMiddleware, CsrfMiddleware
│   ├── Models/             # 35 domain models with automatic tenant-scoped query filters
│   ├── Services/           # Business logic: BillingService, AccountingService, JwtService, TeacherPaymentService
│   └── Validators/         # Request input validation logic
├── config/                 # Application and institute configuration files
├── public/                 # HTTP server document root (index.php, .htaccess)
├── routes/                 # Route registrations (web.php)
├── storage/                # Runtime data: logs/, cache/, backups/, temp/, uploads/
└── tests/                  # 150 automated verification tests across 5 test suites
```

## Core Design Principles

1. **Custom MVC Engine:** Zero external framework overhead; optimized for rapid execution and low memory footprint.
2. **Multi-Tenant Data Isolation:** Every query and data mutation enforces `tenant_id` verification.
3. **Double-Entry General Ledger:** Enforces mathematical equilibrium (`Debits == Credits`) on all financial postings.
4. **JWT & Session Security:** Supports HS256 tokens with rotating refresh tokens and database revocation tracking.
5. **Automated Verification:** 150 automated tests verify core logic, E2E workflows, JWT auth, RBAC permissions, and HTTP routes.

## Running Tests

From the project root:
```cmd
scripts\test.bat
```
Or directly from `backend`:
```bash
php tests/test_backend_core.php
php tests/test_e2e_workflows.php
php tests/test_jwt_auth.php
php tests/test_role_dashboards.php
php tests/test_http_routes.php
```

