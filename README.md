<div align="center">

<img src="public/assets/images/logo.svg" alt="TuitionPro ERP" height="80" />

# TuitionPro ERP

**A Comprehensive Education Management & Enterprise Resource Planning System**

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Tests](https://img.shields.io/badge/Tests-149%2F149%20Passing-brightgreen?style=flat-square&logo=checkmarx&logoColor=white)](https://github.com/VishwaSashintha/TUITION-ERP)
[![License](https://img.shields.io/badge/License-Proprietary-red?style=flat-square)](https://github.com/VishwaSashintha/TUITION-ERP)

A production-grade, multi-tenant education ERP built for tuition institutes, private centres, and multi-branch educational organizations. Built on pure PHP 8.2+ with zero framework dependency.

[📋 Features](#-features) · [👥 User Roles](#-user-roles) · [🛠 Tech Stack](#-tech-stack) · [🚀 Installation](#-installation) · [🧪 Tests](#-automated-tests) · [🔐 Security](#-security)

</div>

---

## 📌 Overview

**TuitionPro ERP** centralizes every operational aspect of an educational institute — from student admissions and batch scheduling to fee collection, double-entry accounting, teacher compensation, and parent portals — into a single cohesive platform.

**Designed for:**
- 🏫 Private tuition centres and tuition classes
- 📚 Multi-subject educational institutes
- 🏢 Multi-branch education groups
- 👨‍🏫 Individual tutors managing organized class operations

**Key strengths:**
- 14-role RBAC with strict server-side access enforcement
- Double-entry general ledger with real-time Balance Sheet and P&L
- Multi-tenant architecture supporting both standalone and SaaS deployment
- Role-specific dashboards — every user sees only what they need
- 149/149 automated tests passing

---

## 🚀 Deployment Modes

TuitionPro ERP runs on a single codebase across two deployment configurations:

| Mode | Description |
|---|---|
| **Standalone** | One installation per institute. Dedicated database, local branch campus management. Ideal for independent tuition centres. |
| **Multi-Institute Platform** | One centrally hosted installation serving multiple independent institutes. Managed via Platform Super Admin Dashboard. Strict `tenant_id` database-level isolation. |

---

## ✨ Features

### 🎓 Student Management
- Prospective student enquiry tracking with lead source capture (Walk-in, Phone, Social Media, Website, Referral)
- Follow-up interaction timeline with channel logging (Call, WhatsApp, Email, In-Person)
- One-click enquiry-to-admission conversion with auto-generated admission numbers (`APX-YYYY-XXXX`)
- Student profiles with multi-guardian linking, emergency contacts, and document storage
- Printable digital student ID cards with embedded QR codes
- Student CSV export and advanced directory filtering

### 📅 Academic Management
- Subject and course catalogue management
- Batch cohort creation with teacher assignment, monthly fee structures, and capacity limits
- Weekly timetable scheduling with conflict prevention
- Date-specific session planning with room and lecturer assignment
- Student batch enrolment management

### ✅ Attendance
- Session-level roll-call register (Present / Absent / Late / Excused)
- **Real-time HTML5 camera QR scanner** with instant audio-visual confirmation
- Attendance history with controlled correction audit trail
- Attendance reports by student, batch, and date range

### 💰 Fee Management & Invoicing
- Flexible invoice generation (batch-based and ad-hoc)
- Partial payment recording with real-time outstanding balance tracking
- Server-side overpayment prevention
- Printable A4/thermal-formatted payment receipts with institute branding
- Aged defaulters report with direct guardian contact access
- Formal refund workflow with general ledger adjustment entries

### 📒 Double-Entry General Ledger Accounting
- Standard 5-tier Chart of Accounts (1000 Assets · 2000 Liabilities · 3000 Equity · 4000 Income · 5000 Expenses)
- Strict debit = credit invariant enforced on every journal posting
- **Trial Balance** — real-time ledger equilibrium verification
- **Profit & Loss Statement** — gross revenue, expenses, net surplus
- **Balance Sheet** — Assets = Liabilities + Equity + Net Surplus
- Multi-account cash and bank balance tracking
- Expense recording with receipt attachment support

### 👨‍🏫 Teacher & HR Management
- Teacher profile directory with availability tracking
- Compensation formula engine: percentage of fee collection, hourly rate, or flat monthly stipend
- Payment disbursement vouchers with settlement tracking
- Printable individual teacher compensation statements

### 📝 Examinations & LMS
- Exam creation by batch, subject, max marks, and date
- Batch-wide marks entry with automatic percentage calculation
- Results publishing with auto-graded rankings (A / B / C / S / F)
- Lesson material uploads and distribution (PDF study packs)
- Homework assignment creation with submission deadlines
- Online student submission portal with teacher grading and feedback workflow

### 👨‍👩‍👧 Student & Parent Portals
- **Student Portal** — personal timetable, attendance %, exam results, fee balance, study materials
- **Parent Portal** — multi-child switcher, attendance summary, fee dues, exam report cards
- Server-side anti-IDOR protection — parents cannot access unauthorized student records

### 📊 Reports Centre
- Attendance Reports
- Fee Collection Reports
- Outstanding Balance Reports
- Teacher Payment Reports
- Operational Expense Reports
- Student Performance Reports
- Consolidated navigation hub at `/reports`

### ⚙️ System & Administration
- Role and permission management
- Branch campus management
- User account management
- In-browser database backup creation, download, and restoration
- Announcement and notification management
- Platform Super Admin dashboard for multi-tenant operations

---

## 👥 User Roles

TuitionPro ERP enforces a **14-role access hierarchy** with dedicated dashboard experiences per role:

| Role | Access Scope |
|---|---|
| **Platform Super Admin** | Full platform administration, tenant lifecycle, subscription management |
| **Platform Support Admin** | Monitored tenant diagnostics and support |
| **Institute Owner / Director** | Executive dashboard — full financials, GL, staff management |
| **Institute Admin** | Operational dashboard — academics, admissions, daily billing |
| **Branch Manager** | Operations scoped strictly to assigned branch campus |
| **Accountant** | Financial dashboard — GL, Trial Balance, P&L, Balance Sheet, payroll |
| **Receptionist** | Front-desk dashboard — enquiries, admissions, attendance (zero GL/payroll exposure) |
| **Cashier** | Counter fee collection, receipt generation, daily cash settlement |
| **Admissions Officer** | Enquiry management, student registrations, enrolment, document verification |
| **Academic Coordinator** | Timetable, sessions, teacher assignments, examination coordination |
| **HR Manager** | Teacher profiles, workload monitoring, compensation processing |
| **Teacher** | Academic dashboard — assigned classes and students only |
| **Student** | Personal academic portal — own records only |
| **Parent / Guardian** | Multi-child portal — own children's records only |

---

## 🔐 Security

| Control | Implementation |
|---|---|
| **Authentication** | RFC 7519 HS256 JSON Web Tokens · single-use rotating refresh tokens · database persistence |
| **Brute-Force Protection** | Automatic lockout after 5 failed login attempts (IP + account) |
| **Route Authorization** | Server-side `RoleMiddleware` returning `HTTP 403` on unauthorized access |
| **CSRF Protection** | Synchronizer token on all `POST`, `PUT`, `DELETE` requests |
| **XSS Prevention** | 100% output escaping via global `e()` helper |
| **SQL Injection Defense** | 100% parameterized PDO prepared statements |
| **IDOR Prevention** | Server-side resource ownership validation on all student, parent, and invoice endpoints |
| **File Upload Security** | Whitelisted MIME types · randomized filenames · executable extension blocking |
| **Audit Trail** | Immutable event log for all sensitive security and financial actions via `AuditService` |
| **Multi-Tenant Isolation** | All queries scoped by `tenant_id` at the database level |

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP 8.2+ · Custom MVC (no framework) · PDO · PSR-4 Autoloading |
| **Database** | MySQL 8.0+ / MariaDB 10.4+ · InnoDB · 61 tables · ACID transactions |
| **Authentication** | HS256 JWT · Refresh token rotation · Session management |
| **Frontend** | PHP view templates · Custom CSS (6-colour design system) · Vanilla JS |
| **QR Scanning** | HTML5 Camera API · `endroid/qr-code` |
| **PDF & Export** | `dompdf/dompdf` · `phpoffice/phpspreadsheet` |
| **Email** | `phpmailer/phpmailer` · SMTP |
| **Environment** | `vlucas/phpdotenv` |
| **Testing** | Native PHP test harness · PHPUnit 11 |
| **Dev Environment** | XAMPP · Apache 2.4+ · PHP built-in server |

---

## 🏗 Architecture

```
┌─────────────────────────────────────────────┐
│          Client Browsers & Devices          │
│   Admin · Teacher · Student · Parent        │
└───────────────────┬─────────────────────────┘
                    │ HTTP
┌───────────────────▼─────────────────────────┐
│          public/index.php (Front Controller) │
└───────────────────┬─────────────────────────┘
                    │
┌───────────────────▼─────────────────────────┐
│   Middleware Pipeline                        │
│   AuthMiddleware · RoleMiddleware            │
│   TenantMiddleware · CsrfMiddleware          │
└───────────────────┬─────────────────────────┘
                    │
┌───────────────────▼─────────────────────────┐
│   Domain Controllers (20 controllers)        │
│   Students · Fees · Academic · Attendance    │
│   Exams · Teachers · Reports · Portals · GL  │
└───────────────────┬─────────────────────────┘
                    │
┌───────────────────▼─────────────────────────┐
│   Business Services                          │
│   BillingService · AccountingService         │
│   JwtService · AuditService · TenantManager  │
└───────────────────┬─────────────────────────┘
                    │
┌───────────────────▼─────────────────────────┐
│   MySQL 8.0+ / MariaDB                       │
│   InnoDB · 61 Tables · tenant_id isolation   │
└─────────────────────────────────────────────┘
```

---

## 📁 Project Structure

```
TuitionPro ERP/
├── app/
│   ├── Controllers/        # HTTP request handlers (20 controllers, grouped by domain)
│   ├── Core/               # Custom MVC core — Router, Request, Response, Database, App
│   ├── Helpers/            # Global utility functions (auth_user, e, url, format_currency)
│   ├── Middleware/         # Security pipeline — Auth, Role, CSRF, JWT, Tenant
│   ├── Models/             # Data access models with automatic tenant scoping (35 models)
│   └── Services/           # Business logic — Billing, Accounting, Tenant, Audit, JWT
├── config/                 # App, database, institute, and JWT configuration files
├── database/
│   ├── migrations/         # 5 versioned SQL migration files (001–005)
│   └── seeds/              # Role, permission, and demo institute seed data
├── public/                 # Web root — index.php, CSS, JS, SVG assets
│   └── assets/
│       ├── css/style.css   # Main stylesheet (6-colour design system)
│       ├── js/app.js       # Client-side interactions and QR scanner
│       └── images/         # SVG logo assets
├── resources/views/        # PHP view templates (grouped by module)
├── routes/web.php          # All application route definitions
├── storage/                # Logs, backups, and secure file uploads (git-ignored)
├── tests/                  # Native PHP automated test suites (5 suites, 149 tests)
├── .env.example            # Environment configuration template
├── .gitignore              # Protects credentials, logs, uploads, and vendor/
└── README.md               # This file
```

---

## 🚀 Installation

### Prerequisites

- PHP **8.2+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `json`, `gd`, `fileinfo`, `curl`
- MySQL **8.0+** or MariaDB **10.4+**
- Apache 2.4+ with `mod_rewrite` enabled — or Nginx 1.20+ — or PHP built-in server for development
- Composer 2.x

### Quick Start (XAMPP / Local Development)

```bash
# 1. Clone the repository
git clone https://github.com/VishwaSashintha/TUITION-ERP.git
cd TUITION-ERP

# 2. Install PHP dependencies
composer install

# 3. Configure environment
cp .env.example .env
# Open .env and set your database credentials and institute profile

# 4. Create database
mysql -u root -p -e "CREATE DATABASE tuitionpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 5. Run migrations in order
mysql -u root -p tuitionpro < database/migrations/001_initial_schema.sql
mysql -u root -p tuitionpro < database/migrations/002_jwt_auth_schema.sql
mysql -u root -p tuitionpro < database/migrations/003_seed_role_permissions.sql
mysql -u root -p tuitionpro < database/migrations/004_seed_additional_roles.sql
mysql -u root -p tuitionpro < database/migrations/005_student_enquiries_schema.sql

# 6. Start development server
php -S localhost:8080 -t public
```

Open **http://localhost:8080** in your browser.

### Apache VirtualHost

```apache
<VirtualHost *:80>
    ServerName yourinstitute.local
    DocumentRoot /path/to/TUITION-ERP/public

    <Directory /path/to/TUITION-ERP/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Nginx (Multi-Institute / Production)

```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.lk *.yourdomain.lk;

    root /var/www/TUITION-ERP/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }

    location ~ /\. { deny all; }
}
```

---

## ⚙️ Configuration

Copy `.env.example` to `.env` and set the following variables:

```env
# Application
APP_NAME="TuitionPro ERP"
APP_ENV=production
APP_URL=https://yourdomain.lk
APP_SECRET_KEY=your-32-character-random-secret-key

# Institute Profile (Standalone Mode)
INSTITUTE_NAME="Your Institute Name"
INSTITUTE_CODE=yourcode
INSTITUTE_CURRENCY=LKR
INSTITUTE_CURRENCY_SYMBOL="Rs. "

# JWT Authentication
JWT_SECRET=your-64-character-random-signing-key
JWT_ACCESS_TTL=900         # 15 minutes
JWT_REFRESH_TTL=604800     # 7 days

# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tuitionpro
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Mail (SMTP)
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-smtp-user
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS=noreply@yourdomain.lk
```

> **Never commit your `.env` file.** It is excluded by `.gitignore`.

---

## 🧪 Automated Tests

TuitionPro ERP includes a comprehensive native PHP test harness:

```bash
php tests/test_jwt_auth.php        # JWT security tests
php tests/test_backend_core.php    # Core business logic tests
php tests/test_e2e_workflows.php   # End-to-end workflow tests
php tests/test_http_routes.php     # HTTP route coverage tests
php tests/test_role_dashboards.php # RBAC and dashboard tests
```

| Suite | Focus | Result |
|---|---|:---:|
| `test_jwt_auth.php` | JWT signing, tamper rejection, refresh rotation, replay defense, brute-force lockout | **16 / 16** ✅ |
| `test_backend_core.php` | Multi-tenant isolation, invoicing, double-entry balance, trial balance | **16 / 16** ✅ |
| `test_e2e_workflows.php` | Student admission → enrolment → session → QR attendance → payment | **28 / 28** ✅ |
| `test_http_routes.php` | 62 application routes returning correct HTTP status | **62 / 62** ✅ |
| `test_role_dashboards.php` | Role dashboard dispatch, 403 blocks, anti-IDOR ownership checks | **27 / 27** ✅ |
| **Total** | | **149 / 149** ✅ |

---

## 🔑 Evaluation Credentials

| Role | Tenant | Email | Password |
|---|:---:|---|---|
| Platform Super Admin | `apex` | `superadmin@tuitionpro.lk` | `SuperAdmin@2026` |
| Institute Owner | `apex` | `owner@apex.lk` | `Password@123` |
| Receptionist | `apex` | `receptionist@apex.lk` | `Password@123` |
| Teacher | `apex` | `teacher@apex.lk` | `Password@123` |
| Student | `apex` | `student@apex.lk` | `Password@123` |
| Parent | `apex` | `parent@apex.lk` | `Password@123` |

---

## 🗄 Database

61 InnoDB tables across the following domains:

| Domain | Tables |
|---|---|
| **Platform & Tenancy** | `tenants`, `subscription_plans`, `subscriptions`, `branches`, `system_settings` |
| **Identity & Security** | `users`, `roles`, `permissions`, `role_permissions`, `refresh_tokens`, `login_attempts`, `audit_logs` |
| **Admissions & Students** | `student_enquiries`, `enquiry_followups`, `students`, `guardians`, `student_guardians`, `student_documents`, `student_notes` |
| **Academics & Timetable** | `academic_years`, `subjects`, `courses`, `batches`, `enrollments`, `class_schedules`, `class_sessions` |
| **Attendance** | `attendance_records`, `attendance_corrections` |
| **Billing & Invoices** | `fee_types`, `invoices`, `invoice_items`, `payments`, `payment_allocations`, `refunds` |
| **General Ledger** | `chart_of_accounts`, `financial_accounts`, `journal_entries`, `journal_lines`, `accounting_periods` |
| **Teacher & HR** | `teachers`, `teacher_availability`, `teacher_payment_rules`, `teacher_payment_periods`, `teacher_payment_calculations`, `teacher_payments` |
| **Examinations & LMS** | `examinations`, `grading_rules`, `exam_marks`, `learning_materials`, `assignments`, `student_submissions` |
| **Operations** | `expense_categories`, `expenses`, `expense_payments`, `announcements`, `notifications`, `backup_records` |

---

## 📈 Project Status

> **Development Stage** — Core modules implemented and passing automated tests.
> Not yet validated for production deployment at scale.

| Module | Status |
|---|:---:|
| Authentication & RBAC | ✅ Complete |
| Student Management & Enquiries | ✅ Complete |
| Academic Management & Timetable | ✅ Complete |
| Attendance (Manual + QR Camera) | ✅ Complete |
| Fee Invoicing & Payment Collection | ✅ Complete |
| Double-Entry General Ledger | ✅ Complete |
| Teacher Compensation | ✅ Complete |
| Examinations & Marks | ✅ Complete |
| LMS — Materials & Assignments | ✅ Complete |
| Student & Parent Portals | ✅ Complete |
| Reports Centre | ✅ Complete |
| System Backups | ✅ Complete |
| Multi-Tenant Platform Dashboard | ✅ Complete |
| Email / SMS Notification Delivery | ⚙️ Config-ready (driver integration pending) |

---

## 🔭 Planned Improvements

- Mobile-responsive progressive web application (PWA)
- Online payment gateway integration (Stripe / PayHere)
- Push notification delivery
- Advanced analytics and reporting dashboards
- Batch SMS dispatch via local gateway
- SAML / OAuth2 SSO support

---

## 👨‍💻 Author

**Developer:** Vishwa Sashintha

**GitHub:** [github.com/VishwaSashintha](https://github.com/VishwaSashintha)

**Repository:** [github.com/VishwaSashintha/TUITION-ERP](https://github.com/VishwaSashintha/TUITION-ERP)

---

## 📄 License

Copyright © 2026 TuitionPro ERP. All rights reserved.

This software is proprietary and commercially licensed. Unauthorized copying, distribution, or modification is prohibited.
