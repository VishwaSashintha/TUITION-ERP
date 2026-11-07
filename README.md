# TuitionPro ERP

TuitionPro ERP is an education management and enterprise resource planning system designed for tuition institutes, private educational centres, and multi-branch educational organizations. It centralizes student management, academic operations, fee collection, accounting, teacher compensation, examinations, and reporting into a single web-based platform.

The system is built on pure PHP 8.2 without a third-party framework, uses MySQL as its database, and enforces role-based access control across 14 user roles.

---

## Table of Contents

- [Key Features](#key-features)
- [User Roles and Access Control](#user-roles-and-access-control)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Installation and Setup](#installation-and-setup)
- [Configuration](#configuration)
- [Security](#security)
- [Project Status](#project-status)
- [Future Improvements](#future-improvements)
- [Author](#author)

---

## Key Features

**Student Management**
- Prospective student enquiry tracking with lead source capture and follow-up interaction logging
- One-click conversion of an enquiry to an active student registration
- Student profiles with multi-guardian linking, document storage, and academic history
- Printable student ID cards with embedded QR codes
- Student directory with filtering and CSV export

**Academic Management**
- Subject and course catalogue management
- Batch cohort creation with teacher assignment, monthly fee structure, and capacity limits
- Weekly timetable scheduling with session-level planning and conflict prevention
- Student batch enrolment management

**Attendance**
- Session-level roll-call register with statuses: Present, Absent, Late, Excused
- Real-time HTML5 camera-based QR code scanner for attendance recording
- Attendance history with controlled correction and audit trail

**Fee Management and Invoicing**
- Invoice generation for batch-based and ad-hoc fees
- Partial payment recording with real-time outstanding balance tracking
- Overpayment prevention enforced at the server side
- Printable payment receipts with institute branding
- Aged defaulters report with guardian contact access
- Formal refund workflow with general ledger adjustment entries

**Double-Entry General Ledger Accounting**
- Standard five-tier Chart of Accounts: Assets, Liabilities, Equity, Income, Expenses
- Strict debit and credit balance enforced on every journal posting
- Trial Balance with real-time ledger equilibrium verification
- Profit and Loss Statement showing revenue, expenses, and net surplus
- Balance Sheet verifying Assets equal Liabilities plus Equity plus Net Surplus
- Cash and bank account tracking
- Expense recording with receipt attachment support

**Teacher and HR Management**
- Teacher profile management with availability tracking
- Compensation calculation using percentage of fee collection, hourly rate, or flat monthly stipend
- Payment disbursement recording and settlement tracking
- Printable individual teacher compensation statements

**Examinations and Learning Management**
- Exam creation by batch, subject, maximum marks, and date
- Batch-wide marks entry with automatic percentage and grade calculation
- Results publishing with grade rankings: A, B, C, S, F
- Lesson material upload and distribution
- Homework assignment creation with submission deadlines
- Online student submission portal with teacher grading and written feedback

**Student and Parent Portals**
- Student portal: personal timetable, attendance summary, exam results, fee balance, and study materials
- Parent portal: multi-child switcher, attendance summary, fee balance, and report cards
- Server-side resource ownership verification preventing unauthorized cross-student access

**Reports**
- Attendance reports
- Fee collection reports
- Outstanding balance reports
- Teacher payment reports
- Operational expense reports
- Student performance reports

**System Administration**
- Role and permission management
- Branch campus management
- User account management
- In-browser database backup creation, download, and restoration
- Announcement and notification management
- Platform Super Admin dashboard for multi-tenant operations

---

## User Roles and Access Control

TuitionPro ERP enforces a 14-role hierarchy. Each role receives a dedicated dashboard experience with server-side route-level permission enforcement. Unauthorized route access returns HTTP 403.

| Role | Access Scope |
|---|---|
| Platform Super Admin | Full platform administration, tenant provisioning, subscription management |
| Platform Support Admin | Tenant diagnostics and support operations |
| Institute Owner / Director | Executive dashboard with full financials, general ledger, and staff management |
| Institute Admin | Operational dashboard with academics, admissions, and billing |
| Branch Manager | Operations scoped to the assigned branch campus |
| Accountant | Financial dashboard with general ledger, trial balance, P&L, balance sheet, and payroll |
| Receptionist | Front-desk dashboard for enquiries, admissions, and attendance, with no access to accounting or payroll |
| Cashier | Fee collection, receipt generation, and daily cash settlement |
| Admissions Officer | Student enquiries, registrations, enrolments, and document verification |
| Academic Coordinator | Timetable management, session planning, teacher assignments, and examination coordination |
| HR Manager | Teacher profiles, workload monitoring, and compensation processing |
| Teacher | Academic dashboard scoped to assigned classes and students only |
| Student | Personal academic portal with own records only |
| Parent / Guardian | Multi-child portal with own children's records only |

---

## Technology Stack

| Layer | Details |
|---|---|
| Backend | PHP 8.2, custom MVC architecture, PDO, PSR-4 autoloading |
| Database | MySQL 8.0 or MariaDB 10.4, InnoDB, 61 relational tables |
| Authentication | HS256 JSON Web Tokens, rotating refresh tokens, database-persisted sessions |
| Frontend | PHP view templates, custom CSS, vanilla JavaScript |
| QR Code | HTML5 Camera API for scanning, endroid/qr-code for generation |
| PDF and Export | dompdf/dompdf for PDF generation, phpoffice/phpspreadsheet for Excel export |
| Email | phpmailer/phpmailer via SMTP |
| Environment | vlucas/phpdotenv |
| Testing | Native PHP test suites, PHPUnit 11 |
| Development Server | XAMPP, Apache 2.4, or the PHP built-in development server |

---

## Project Structure

```
TuitionPro ERP/
├── app/
│   ├── Controllers/        # HTTP request handlers organized by domain (20 controllers)
│   ├── Core/               # Custom MVC core: Router, Request, Response, Database, Application
│   ├── Helpers/            # Global utility functions: auth_user, e, url, format_currency
│   ├── Middleware/         # Request pipeline: Auth, Role, CSRF, JWT, Tenant
│   ├── Models/             # Database models with automatic tenant scoping (35 models)
│   └── Services/           # Business logic: BillingService, AccountingService, JwtService, AuditService, TenantManager
├── config/                 # Application, database, institute, and JWT configuration files
├── database/
│   ├── migrations/         # Five versioned SQL migration files (001 through 005)
│   └── seeds/              # Role, permission, and demonstration institute seed data
├── public/                 # Web root: index.php, CSS, JavaScript, and image assets
│   └── assets/
│       ├── css/style.css   # Main stylesheet
│       ├── js/app.js       # Client-side scripts including QR scanner
│       └── images/         # SVG logo files
├── resources/views/        # PHP view templates organized by module
├── routes/web.php          # All application route definitions
├── storage/                # Runtime logs, backups, and file uploads (excluded from version control)
├── tests/                  # Native PHP automated test suites
├── .env.example            # Environment configuration template
├── .gitignore              # Version control exclusion rules
└── README.md               # Project documentation
```

---

## Installation and Setup

### Requirements

- PHP 8.2 or higher with the following extensions enabled: `pdo_mysql`, `mbstring`, `openssl`, `json`, `gd`, `fileinfo`, `curl`
- MySQL 8.0 or MariaDB 10.4 or higher
- Apache 2.4 with `mod_rewrite` enabled, or Nginx 1.20, or the PHP built-in server for local development
- Composer 2.x

### Setup Steps

**1. Clone the repository**

```bash
git clone https://github.com/VishwaSashintha/TUITION-ERP.git
cd TUITION-ERP
```

**2. Install PHP dependencies**

```bash
composer install
```

**3. Configure the environment**

```bash
cp .env.example .env
```

Open `.env` and set your database credentials, institute profile, and JWT secret key.

**4. Create the database**

```bash
mysql -u root -p -e "CREATE DATABASE tuitionpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**5. Run the database migrations in order**

```bash
mysql -u root -p tuitionpro < database/migrations/001_initial_schema.sql
mysql -u root -p tuitionpro < database/migrations/002_jwt_auth_schema.sql
mysql -u root -p tuitionpro < database/migrations/003_seed_role_permissions.sql
mysql -u root -p tuitionpro < database/migrations/004_seed_additional_roles.sql
mysql -u root -p tuitionpro < database/migrations/005_student_enquiries_schema.sql
```

**6. Start the development server**

```bash
php -S localhost:8080 -t public
```

Open `http://localhost:8080` in your browser.

### Apache Virtual Host (Local or Production)

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

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.lk;
    root /var/www/TUITION-ERP/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }

    location ~ /\. {
        deny all;
    }
}
```

---

## Configuration

Copy `.env.example` to `.env` and update the following variables before starting the application.

```env
# Application
APP_NAME="TuitionPro ERP"
APP_ENV=local
APP_URL=http://localhost:8080
APP_SECRET_KEY=your-32-character-random-key

# Institute Profile (Standalone Mode)
INSTITUTE_NAME="Your Institute Name"
INSTITUTE_CODE=yourcode
INSTITUTE_CURRENCY=LKR
INSTITUTE_CURRENCY_SYMBOL="Rs. "

# JWT Authentication
JWT_SECRET=your-64-character-random-signing-key
JWT_ACCESS_TTL=900
JWT_REFRESH_TTL=604800

# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tuitionpro
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Mail (SMTP)
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS=noreply@yourdomain.lk

# SMS (optional)
SMS_DRIVER=none
SMS_API_KEY=
```

The `.env` file is excluded from version control by `.gitignore` and must never be committed to the repository.

### Default Evaluation Credentials

The seed data creates the following accounts for local evaluation:

| Role | Tenant Code | Email | Password |
|---|:---:|---|---|
| Platform Super Admin | apex | superadmin@tuitionpro.lk | SuperAdmin@2026 |
| Institute Owner | apex | owner@apex.lk | Password@123 |
| Receptionist | apex | receptionist@apex.lk | Password@123 |
| Teacher | apex | teacher@apex.lk | Password@123 |
| Student | apex | student@apex.lk | Password@123 |
| Parent | apex | parent@apex.lk | Password@123 |

Change all passwords before using the application in any non-development environment.

---

## Security

The following security controls are implemented in the application:

- **Authentication:** RFC 7519 HS256 JSON Web Tokens with single-use rotating refresh tokens and database persistence.
- **Brute-force protection:** Automatic lockout after five consecutive failed login attempts per account and IP address.
- **Route authorization:** Server-side `RoleMiddleware` enforces permitted roles on every protected route and returns HTTP 403 for unauthorized access.
- **CSRF protection:** Synchronizer token verification on all POST, PUT, and DELETE requests.
- **XSS prevention:** All output is escaped using the global `e()` helper function.
- **SQL injection prevention:** All database queries use PDO prepared statements with parameterized bindings.
- **IDOR prevention:** Server-side resource ownership validation on all student, parent, and invoice endpoints.
- **File upload security:** MIME type whitelisting, randomized storage filenames, and blocking of executable file extensions.
- **Audit logging:** Sensitive security and financial events are recorded via `AuditService`.
- **Multi-tenant isolation:** All database queries are scoped by `tenant_id` to prevent cross-tenant data access.

---

## Automated Tests

The project includes a native PHP test harness covering authentication security, business logic, end-to-end workflows, HTTP route coverage, and role-based access control.

```bash
php tests/test_jwt_auth.php
php tests/test_backend_core.php
php tests/test_e2e_workflows.php
php tests/test_http_routes.php
php tests/test_role_dashboards.php
```

| Suite | Coverage | Tests |
|---|---|:---:|
| test_jwt_auth.php | JWT signing, refresh rotation, replay defense, brute-force lockout | 16 |
| test_backend_core.php | Multi-tenant isolation, invoicing, double-entry accounting | 16 |
| test_e2e_workflows.php | Student admission, enrolment, attendance, and payment workflows | 28 |
| test_http_routes.php | HTTP status verification across 62 application routes | 62 |
| test_role_dashboards.php | Role dashboard dispatch, 403 enforcement, and anti-IDOR checks | 27 |
| Total | | 149 |

---

## Project Status

The core modules are implemented and all 149 automated tests pass. The application has not been validated for large-scale production deployment.

| Module | Status |
|---|---|
| Authentication and RBAC | Complete |
| Student Management and Enquiries | Complete |
| Academic Management and Timetable | Complete |
| Attendance (manual and QR camera) | Complete |
| Fee Invoicing and Payment Collection | Complete |
| Double-Entry General Ledger | Complete |
| Teacher Compensation | Complete |
| Examinations and Marks | Complete |
| Learning Materials and Assignments | Complete |
| Student and Parent Portals | Complete |
| Reports | Complete |
| Database Backups | Complete |
| Multi-Tenant Platform Dashboard | Complete |
| Email and SMS Delivery | Configuration ready, gateway integration not yet implemented |

---

## Future Improvements

- Mobile-responsive progressive web application
- Online payment gateway integration
- Push notification delivery
- Advanced analytics dashboards
- Bulk SMS dispatch via local gateway
- Single sign-on support

---

## Author

Vishwa Sashintha

GitHub: [github.com/VishwaSashintha](https://github.com/VishwaSashintha)

Repository: [github.com/VishwaSashintha/TUITION-ERP](https://github.com/VishwaSashintha/TUITION-ERP)
