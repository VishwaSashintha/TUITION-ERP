# TuitionPro ERP — System Architecture Specification

**System Version:** 2.0-Commercial  
**Architecture Pattern:** Modular Monolith (MVC)  
**Primary Language:** PHP 8.2+ (Strict Types)  
**Database:** MySQL 8.0+ / MariaDB 10.4+ (InnoDB Engine)  

---

## 1. High-Level Architecture Overview

TuitionPro ERP is architected as a high-performance **Modular Monolith**. It avoids unnecessary microservice complexity while maintaining strict domain boundaries, reusable service layers, and explicit multi-tenancy.

```
+-------------------------------------------------------------------------------+
|                               HTTP / WEB CLIENTS                              |
|           Browser (Admin, Staff, Teachers, Students, Parents) / Mobile        |
+-------------------------------------------------------------------------------+
                                       |
                                       v
+-------------------------------------------------------------------------------+
|                       WEB SERVER & ROUTING ENGINE (PHP)                       |
|           public/index.php -> Router -> Middleware Pipeline                   |
|     (AuthMiddleware, RoleMiddleware, TenantMiddleware, CsrfMiddleware)        |
+-------------------------------------------------------------------------------+
                                       |
        +------------------------------+------------------------------+
        |                              |                              |
        v                              v                              v
+---------------+              +---------------+              +---------------+
|   INSTITUTE   |              |  FRONT-DESK   |              |  PORTALS &    |
|   EXECUTIVE   |              |   & CASHIER   |              |   ACADEMIC    |
|  CONTROLLERS  |              |  CONTROLLERS  |              |  CONTROLLERS  |
+---------------+              +---------------+              +---------------+
        |                              |                              |
        +------------------------------+------------------------------+
                                       |
                                       v
+-------------------------------------------------------------------------------+
|                                BUSINESS SERVICES                              |
|   BillingService | TenantManager | AuditService | ReportService | JWTService  |
+-------------------------------------------------------------------------------+
                                       |
                                       v
+-------------------------------------------------------------------------------+
|                             DATA ACCESS / MODELS                              |
|   Student | Teacher | Invoice | Payment | JournalEntry | Batch | Attendance   |
+-------------------------------------------------------------------------------+
                                       |
                                       v
+-------------------------------------------------------------------------------+
|                       DATABASE LAYER (MySQL / MariaDB)                        |
|       InnoDB | Foreign Keys | Tenant Scoping (tenant_id) | Double-Entry GL     |
+-------------------------------------------------------------------------------+
```

---

## 2. Dual Deployment Architecture

TuitionPro ERP natively supports two distinct deployment modes from the same codebase:

### 2.1 Mode A: Standalone Installation (Single Institute)
- Designed for independent tuition institutes, schools, or single academies.
- Operates under a dedicated database and dedicated `.env` configuration.
- The default tenant is initialized via `config/institute.php` (`INSTITUTE_CODE=apex`).
- Platform management routes (`/platform/*`) can be disabled or restricted to the system administrator.
- Institute branding, contact details, currency, and timezone are loaded from `.env`.

### 2.2 Mode B: Multi-Institute Platform (Education Groups)
- Designed for education groups, franchise networks, or SaaS commercial operations.
- The `tenants` table centrally maintains institutes, subscription plans, student limits, and feature flags.
- Super Administrators log in via `/platform/dashboard` to create, monitor, and suspend institutes.
- Each institute's users are scoped by `tenant_id` at runtime.
- Multi-branch hierarchy allows institutes to operate multiple regional branches under one organization.

---

## 3. Directory Layout & Structure

```
TuitionPro ERP/
├── app/
│   ├── Controllers/          # HTTP request handlers grouped by domain
│   │   ├── Academic/         # Subjects, Batches, Materials, Assignments
│   │   ├── Attendance/       # Attendance register & QR scanner
│   │   ├── Auth/             # Login, Logout, Password reset, JWT auth
│   │   ├── Communication/    # Announcements & In-App notifications
│   │   ├── Examinations/     # Exams, marks entry, and result publishing
│   │   ├── Expenses/         # Expenses, Cash & Bank, Profit & Loss, Trial Balance
│   │   ├── Fees/             # Invoicing, Collections, Receipts, Outstanding
│   │   ├── Institute/        # Executive, Receptionist, & Teacher Dashboards
│   │   ├── Platform/         # Super Admin Platform & Tenant Management
│   │   ├── Portals/          # Student & Parent dedicated portal controllers
│   │   ├── Reports/          # Centralized reporting engine
│   │   ├── Settings/         # Branches, users, settings, and database backups
│   │   ├── Students/         # Student onboarding, directory, ID cards
│   │   ├── TeacherPayments/  # Teacher compensation calculations & disbursements
│   │   ├── Teachers/         # Teacher profiles & directory
│   │   └── Timetable/        # Timetable management & session generator
│   ├── Core/                 # Core framework (Router, Request, Response, Database)
│   ├── Helpers/              # Global utility functions (auth_user, e, url, format_currency)
│   ├── Middleware/           # Security filters (Auth, Role, CSRF, JWT, Tenant)
│   ├── Models/               # Data models with automatic tenant scoping
│   └── Services/             # Core business logic engines (Billing, Audit, Tenant)
├── config/                   # System configuration files (app, database, institute, jwt)
├── database/
│   ├── migrations/           # Versioned SQL migrations (001 to 004)
│   └── seeds/                # Initial seed data for standard roles and demo institutes
├── docs/                     # Technical documentation pack
├── public/                   # Public web root (index.php, CSS, JS, images, uploads)
│   └── assets/
│       ├── css/              # Main stylesheet (style.css) with approved 6-color palette
│       └── js/               # Client-side scripts (app.js, qr-scanner.js)
├── resources/
│   └── views/                # Modular PHP view templates
│       ├── layouts/          # Base layouts (app.php, auth.php)
│       └── ...               # Domain view subdirectories
├── storage/                  # Logs, database backup dumps, and session files
├── tests/                    # Automated test suites (JWT, Core, Workflows, Routes, RBAC)
├── .env.example              # Environment configuration template
├── README.md                 # Public documentation & user guide
└── task.md                   # Master implementation & development tracker
```

---

## 4. Key Architectural Patterns & Decisions

1. **Strict Server-Side Authorization:** All business logic, permission checks, and data queries enforce security on the server. UI visibility is never treated as a security control.
2. **Immutable Financial Records:** Payments and posted journal entries cannot be deleted or mutated. Corrections must be handled via reversal entries or authorized refund records.
3. **Double-Entry Equilibrium:** All journal entries must verify $\sum \text{Debits} = \sum \text{Credits}$. Unbalanced transactions trigger an immediate rollback and exception.
4. **Tenant Scoping:** All database queries implicitly include `WHERE tenant_id = :tenant_id` to guarantee absolute data isolation.
5. **Session & JWT Dual-Stack:** Standard web browser access uses secure HTTP-only cookies; mobile apps and external API clients utilize stateless JWT tokens with refresh rotation.

