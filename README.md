# TuitionPro ERP — Education Management & Enterprise Resource Planning System

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat&logo=php&logoColor=white)](https://php.net)
[![Database](https://img.shields.io/badge/Database-MySQL%208.0%20%7C%20MariaDB-4479A1?style=flat&logo=mysql&logoColor=white)](https://mysql.com)
[![Architecture](https://img.shields.io/badge/Architecture-Modular%20Monolith-orange)](https://github.com/VishwaSashintha/TUITION-ERP)
[![Security](https://img.shields.io/badge/Security-HS256%20JWT%20%2B%20RBAC-green)](https://github.com/VishwaSashintha/TUITION-ERP)
[![Tests Passing](https://img.shields.io/badge/Tests-149%2F149%20Passing%20(100%25)-success)](https://github.com/VishwaSashintha/TUITION-ERP)
[![License](https://img.shields.io/badge/License-Commercial%20%2F%20Proprietary-blue)](https://github.com/VishwaSashintha/TUITION-ERP)

TuitionPro ERP is a commercial-grade, multi-tenant and standalone Education Management and Enterprise Resource Planning platform engineered for tuition institutes, private schools, tuition centres, and large multi-branch educational organizations.

Built in pure PHP 8.2+ and MySQL/MariaDB with zero heavy framework overhead, TuitionPro delivers enterprise-scale performance, strict double-entry financial integrity, comprehensive role-based access control (RBAC), and user-specific dashboard experiences.

---

## 1. Core Architecture & Deployment Modes

TuitionPro is **ONE reusable software product** that seamlessly supports two deployment models on the exact same architecture:

### 1.1 Mode A: Standalone Installation (Single Institute)
- One installation serving one customer institute (e.g. Apex Higher Education Institute, Iskole, Pasala).
- Dedicated local database and `.env` institute profile (`INSTITUTE_NAME`, `INSTITUTE_CODE`, `INSTITUTE_CURRENCY`, `INSTITUTE_LOGO`).
- Local branch campuses managed under a unified single-institute tenancy.

### 1.2 Mode B: Multi-Institute Platform (Education Groups & SaaS)
- One centrally managed installation serving multiple independent institutes and education groups.
- Centrally administered via the Platform Super Admin Dashboard (`/platform/dashboard`).
- Dynamic tenant provisioning, subscription tier limits (student/user caps), and automated institute onboarding.
- Strict database-level tenant scoping (`tenant_id`) ensuring 100% multi-tenant data isolation.

---

## 2. Official Visual Identity & Design System

TuitionPro adheres to a visual design language engineered for readability and fast operations:

### 2.1 The Approved 6-Color Palette
| Purpose | Color | Hex Code | Usage |
|---|---|:---:|---|
| **Primary Dark** | Black | `#0C0C0C` | Main text, high-contrast headings, dark navigation accents |
| **Primary Brand** | Green | `#4BA95F` | Primary action buttons, active states, financial inflow/receipts |
| **Secondary** | Light Blue | `#77DDFA` | Informational cards, timetable sessions, student metric pills |
| **Accent** | Orange | `#FEA339` | Warnings, pending fee notices, upcoming session indicators |
| **Accent** | Purple | `#9985FB` | Batch badges, course indicators, secondary action accents |
| **Accent** | Coral | `#FF908D` | Urgent alerts, overdue balances, expense tags, error badges |

### 2.2 Typography Hierarchy
- **Roboto:** Used for all base interface text, navigation links, form fields, table contents, and descriptions.
- **Poppins SemiBold 700:** Used exclusively for high-impact numeric values, KPI statistic counters, financial balances, and currency figures.

---

## 3. Comprehensive Feature Matrix

### 3.1 Student Lifecycle & Admissions
- **Prospective Student Enquiries:** Lead capture, contact interaction logs, follow-up scheduling, and direct conversion to student admissions (`/students/enquiries`).
- **Onboarding Workflow:** Full student registration with automatic unique admission number generation (`APX-YYYY-XXXX`).
- **Student Directory:** Searchable directory with real-time filters by branch, grade, and active/inactive status (`/students`).
- **Guardian Association:** Multi-guardian relationship management with primary contacts, emergency phones, and occupations.
- **Digital Student ID Cards:** Formatted printable student ID cards featuring embedded dynamic QR codes for rapid scanner check-in (`/students/{id}/id-card`).
- **Student History:** Consolidated profile displaying enrolled batches, attendance percentage, fee invoice history, and staff notes.

### 3.2 Academic Management & Timetable
- **Master Catalogue:** Subjects, courses, and educational programmes with active status controls (`/academic/subjects`).
- **Batch Cohorts:** Class batch creation with teacher assignment, monthly fee structures, and capacity limits (`/academic/batches`).
- **Timetable Scheduling:** Weekly timetable configuration with conflict prevention (`/timetable`).
- **Session Generator:** Date-specific session scheduler with classroom room numbers and assigned lecturers (`/timetable/sessions`).

### 3.3 Rapid Attendance & Scanner Engine
- **Session Register:** Manual roll-call attendance register with status options (Present, Absent, Late, Excused) (`/attendance/take`).
- **Camera QR Scanner:** Interactive real-time HTML5 camera scanner with instant audio-visual confirmation (`/attendance/qr-scanner`).
- **Attendance History & Audit:** Searchable logs by date and student with controlled correction tracking (`/attendance/history`).

### 3.4 Invoicing & Financial Management
- **Invoicing Engine:** Batch and ad-hoc invoice creation with automated due date tracking (`/fees/invoices/create`).
- **Partial Payments:** Incremental payment recording with real-time outstanding balance calculation (`/fees/collect-payment`).
- **Overpayment Defense:** Server-side validation blocking allocations exceeding outstanding invoice amounts.
- **Printable Receipts:** Formatted A4/thermal printable payment receipts with institute branding (`/fees/receipt/{id}`).
- **Defaulters Tracking:** Dedicated aged defaulters report with direct guardian contact information (`/fees/outstanding`).
- **Controlled Refunds:** Formal refund requests with general ledger adjustment entries.

### 3.5 Double-Entry General Ledger Accounting
- **Standard Chart of Accounts:** 5-tier account hierarchy (1000 Assets, 2000 Liabilities, 3000 Equity, 4000 Income, 5000 Expenses).
- **Balanced Journal Posting:** Strict server-side invariant requiring $\sum \text{Debits} = \sum \text{Credits}$ on every transaction.
- **Trial Balance:** Live ledger verification confirming exact equilibrium across all ledger accounts (`/accounting/trial-balance`).
- **Profit & Loss Statement:** Real-time income statement calculating net operating surplus (`/accounting/profit-loss`).
- **Balance Sheet:** Real-time Statement of Financial Position verifying $\text{Assets} = \text{Liabilities} + \text{Equity}$ (`/accounting/balance-sheet`).
- **Cash & Bank Accounts:** Multi-account tracking of physical cash counters and institutional bank balances (`/accounting/cash-bank`).
- **Expense Categorization:** Direct recording of operational overheads with receipt attachments (`/expenses`).

### 3.6 Teacher Compensation & HR
- **Compensation Formula Engine:** Automated calculation based on % of fee collection, hourly teaching rates, or flat monthly stipends (`/teacher-payments/calculate`).
- **Payment Disbursements:** Voucher generation and settlement tracking (`/teacher-payments`).
- **Printable Statements:** Detailed compensation statements for individual lecturers (`/teacher-payments/{id}/statement`).

### 3.7 Examinations & Performance
- **Examination Setup:** Exam creation by batch, subject, maximum marks, and date (`/examinations`).
- **Marks Entry:** Batch-wide grade book entry with automatic percentage calculation (`/examinations/marks-entry`).
- **Results Publishing:** Publish results with automated grade assignments (A, B, C, S, F) and batch ranking (`/examinations/results`).

### 3.8 Coursework & Learning Materials (LMS)
- **Document Handouts:** Teacher upload and distribution of lesson notes and PDF study packs (`/academic/materials`).
- **Homework Assignments:** Coursework creation with submission deadlines and download briefs (`/academic/assignments`).
- **Student Submissions & Grading:** Online solution submission with teacher grading and feedback workflow (`/academic/assignments/submit`, `/academic/assignments/grade`).

### 3.9 Portals & Stakeholder Experience
- **Student Portal (`/portal/student`):** Personal dashboard displaying timetable schedules, attendance %, exam results, fee balances, and study materials.
- **Parent Portal (`/portal/parent`):** Multi-child switcher, attendance turnout %, fee balances, and child exam report cards.
- **Anti-IDOR Defense:** Server-side ownership verification preventing parents from accessing unauthorized student records.

### 3.10 Centralized Reporting Centre
- **Consolidated Navigation Hub:** Dedicated report centre at `/reports`.
- **7 Analytical Reports:** Attendance Reports, Fee Collections, Outstanding Balances, Teacher Payments, Operational Expenses, and Student Performance.

---

## 4. User Roles & Role-Based Dashboards

TuitionPro ERP implements a 14-role access hierarchy with **strict server-side data isolation**:

| Role Slug | Display Name | Access Scope & Dedicated Experience |
|---|---|---|
| `super_admin` | Platform Super Administrator | Dedicated Platform Dashboard (`/platform/dashboard`), tenant lifecycle, platform health |
| `support_admin` | Platform Support Administrator | Monitored tenant diagnostics and support administration |
| `institute_owner` | Institute Owner / Director | Executive Dashboard with full financials, general ledger, and staff management |
| `institute_admin` | Institute Administrator | Operational Dashboard with academics, student admissions, and daily billing |
| `branch_manager` | Branch Manager | Operations restricted strictly to assigned branch campus |
| `accountant` | Accountant | Executive Financial Dashboard, General Ledger, Trial Balance, P&L, Teacher Payroll |
| `receptionist` | Receptionist | **Dedicated Front-Desk Dashboard** (zero GL/payroll/profit exposure; HTTP 403 on accounting routes) |
| `cashier` | Cashier | Counter fee collections, invoice receipt generation, daily cash settlement |
| `admissions_officer` | Admissions Officer | Student inquiries, registrations, batch enrollment, document verification |
| `academic_coordinator`| Academic Coordinator | Timetable, class sessions, teacher assignments, and examination coordination |
| `hr_manager` | HR Manager | Teacher profiles, workload monitoring, and teacher payment processing |
| `teacher` | Teacher | **Dedicated Academic Dashboard** (assigned classes/students only; HTTP 403 on fees/accounting) |
| `student` | Student | Dedicated Student Portal (`/portal/student`) |
| `parent` | Parent / Guardian | Dedicated Parent Portal (`/portal/parent`) with anti-IDOR child switcher |

---

## 5. Security Architecture & Threat Defense

1. **Authentication:** RFC 7519 HS256 JSON Web Tokens with single-use rotating refresh tokens and database persistence (`refresh_tokens`).
2. **Brute-Force Lockout:** Automatic IP and account rate-limiting after 5 consecutive failed login attempts.
3. **Strict Route Protection:** `RoleMiddleware` blocks unpermitted route access on the server and returns `HTTP 403 Forbidden`.
4. **Anti-CSRF:** Synchronizer token verification on all state-changing `POST`, `PUT`, and `DELETE` requests.
5. **Anti-XSS:** 100% output escaping through the global `e()` helper.
6. **SQL Injection Defense:** 100% prepared PDO statements with parameterized input bindings.
7. **Anti-IDOR:** Server-side resource ownership validation across all student, parent, and invoice endpoints.
8. **Audit Trail:** Immutable logging of all sensitive security and financial events via `AuditService`.

---

## 6. Installation & Quick Start

### 6.1 Prerequisites
- PHP 8.2 or higher (`pdo_mysql`, `mbstring`, `openssl`, `json`, `gd`, `curl` extensions)
- MySQL 8.0+ or MariaDB 10.4+
- Apache 2.4+ with `mod_rewrite` enabled, or Nginx 1.20+

### 6.2 Setup Instructions
```bash
# 1. Clone the repository
git clone https://github.com/VishwaSashintha/TUITION-ERP.git
cd TUITION-ERP

# 2. Configure environment
cp .env.example .env
# Edit .env with your database credentials and institute profile

# 3. Create the database
mysql -u root -p -e "CREATE DATABASE tuitionpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Run versioned migrations
mysql -u root -p tuitionpro < database/migrations/001_initial_schema.sql
mysql -u root -p tuitionpro < database/migrations/002_jwt_auth_schema.sql
mysql -u root -p tuitionpro < database/migrations/003_seed_role_permissions.sql
mysql -u root -p tuitionpro < database/migrations/004_seed_additional_roles.sql

# 5. Start development server
php -S localhost:8080 -t public
```

---

## 7. Default Credentials (Verification Accounts)

| Role | Tenant Code | Email / Username | Password | Dashboard URL |
|---|:---:|---|---|---|
| **Platform Super Admin** | `apex` | `superadmin@tuitionpro.lk` | `SuperAdmin@2026` | `/platform/dashboard` |
| **Institute Owner** | `apex` | `owner@apex.lk` | `Password@123` | `/dashboard` (Executive) |
| **Receptionist** | `apex` | `receptionist@apex.lk` | `Password@123` | `/dashboard` (Front-Desk) |
| **Teacher** | `apex` | `teacher@apex.lk` | `Password@123` | `/dashboard` (Academic) |
| **Student** | `apex` | `student@apex.lk` | `Password@123` | `/portal/student` |
| **Parent** | `apex` | `parent@apex.lk` | `Password@123` | `/portal/parent` |

---

## 8. Automated Test Suite Verification

TuitionPro ERP features an automated test harness with **149 out of 149 tests passing (100% pass rate)**:

```bash
# Run all test suites
php tests/test_jwt_auth.php
php tests/test_backend_core.php
php tests/test_e2e_workflows.php
php tests/test_http_routes.php
php tests/test_role_dashboards.php
```

| Suite | Focus Area | Result |
|---|---|:---:|
| `test_jwt_auth.php` | JWT signing, tamper rejection, token rotation, replay defense, lockout | **16 / 16 PASS** |
| `test_backend_core.php` | Multi-tenant isolation, fee invoicing, double-entry balance, trial balance | **16 / 16 PASS** |
| `test_e2e_workflows.php` | Student admission, batch enrollment, session, QR attendance, payments | **28 / 28 PASS** |
| `test_http_routes.php` | 62 public & authenticated routes return HTTP 200/302 | **62 / 62 PASS** |
| `test_role_dashboards.php` | Role dashboard dispatch, front-desk/teacher 403 blocks, Anti-IDOR | **27 / 27 PASS** |
| **Total** | **Comprehensive System Verification** | **149 / 149 PASS (100%)** |

---

## 9. Comprehensive Documentation Index

For detailed technical specifications, consult the documents in the `docs/` directory:
- [`docs/PRODUCT_AUDIT.md`](docs/PRODUCT_AUDIT.md): Comprehensive system audit and verified component status.
- [`docs/FEATURE_GAP_ANALYSIS.md`](docs/FEATURE_GAP_ANALYSIS.md): Evaluated capabilities and integration boundaries.
- [`docs/SYSTEM_ARCHITECTURE.md`](docs/SYSTEM_ARCHITECTURE.md): Modular Monolith architecture, layout, and diagrams.
- [`docs/DATABASE_ARCHITECTURE.md`](docs/DATABASE_ARCHITECTURE.md): Relational schema, 59 tables, and indexes.
- [`docs/RBAC_PERMISSION_MATRIX.md`](docs/RBAC_PERMISSION_MATRIX.md): 14 roles, 22 permissions, and security matrix.
- [`docs/API_SPECIFICATION.md`](docs/API_SPECIFICATION.md): REST API endpoints and JWT payload specifications.
- [`docs/SECURITY_REQUIREMENTS.md`](docs/SECURITY_REQUIREMENTS.md): Cryptography, OWASP defenses, and audit logging.
- [`docs/INSTALLATION.md`](docs/INSTALLATION.md): Complete standalone deployment guide.
- [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md): Multi-institute commercial platform deployment.
- [`docs/BACKUP_AND_RESTORE.md`](docs/BACKUP_AND_RESTORE.md): Database backup, verification, and disaster recovery.
- [`docs/TESTING.md`](docs/TESTING.md): Test harness execution instructions and assertion criteria.
- [`task.md`](task.md): Master implementation tracker and phase checklist.

---

## 10. License & Intellectual Property

Copyright &copy; 2026 TuitionPro ERP. All rights reserved. Commercial & proprietary software.
