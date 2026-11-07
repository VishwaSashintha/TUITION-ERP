# TuitionPro ERP — Database Architecture Specification

**Database Engine:** MySQL 8.0+ / MariaDB 10.4+  
**Storage Engine:** InnoDB  
**Character Set:** `utf8mb4`  
**Collation:** `utf8mb4_unicode_ci`  
**Total Tables:** 59  

---

## 1. Relational Entity Overview

The TuitionPro database architecture enforces referential integrity through foreign keys, cascade safety rules, and tenant scoping. All monetary amounts use `DECIMAL(12, 2)` to eliminate floating-point rounding inaccuracies.

```
                              +--------------------+
                              |      tenants       |
                              +--------------------+
                                        | 1
                                        |
                 +----------------------+----------------------+
                 | *                                           | *
        +-----------------+                           +-----------------+
        |    branches     |                           |      users      |
        +-----------------+                           +-----------------+
                 | 1                                           | 1
                 |                                             |
                 +----------------------+                      |
                 | *                    | *                    | *
        +-----------------+    +-----------------+    +-----------------+
        |    students     |    |    teachers     |    |   audit_logs    |
        +-----------------+    +-----------------+    +-----------------+
                 | 1                    | 1
                 |                      |
                 +----------+-----------+
                 | *        | *
        +-----------------+ |  +-----------------+
        |   enrollments   | |  |     batches     |
        +-----------------+ |  +-----------------+
                 |          |           | 1
                 |          |           | *
                 |          |  +-----------------+
                 |          |  | class_sessions  |
                 |          |  +-----------------+
                 |          |           | 1
                 |          |           | *
                 |          +--------->| attendance_     |
                 |                     | records         |
                 |                     +-----------------+
                 |
                 +----------------------+
                 | 1                    | 1
                 v *                    v *
        +-----------------+    +-----------------+
        |    invoices     |    |    payments     |
        +-----------------+    +-----------------+
                 | 1                    | 1
                 | *                    | *
        +-----------------+    +-----------------+
        |  invoice_items  |    | payment_        |
        |                 |    | allocations     |
        +-----------------+    +-----------------+
                                        | 1
                                        v 1
                               +-----------------+
                               | journal_entries |
                               +-----------------+
                                        | 1
                                        v *
                               +-----------------+
                               |  journal_lines  |
                               +-----------------+
```

---

## 2. Table Categorization

### 2.1 Multi-Tenancy & Platform
- `tenants`: Primary tenant registry (institute code, name, domain, status, subscription limits).
- `subscription_plans`: Plan tiers (Starter, Professional, Enterprise, student/user caps).
- `subscriptions`: Active subscription cycles and billing records.
- `branches`: Physical branch campuses under each tenant.
- `system_settings`: Key-value configuration store per tenant.

### 2.2 Identity, RBAC & Security
- `users`: User identity accounts (email, password hash, tenant ID, status).
- `roles`: 14 system and custom roles (`super_admin` to `parent`).
- `permissions`: 22 granular capability tokens (e.g., `students.create`, `fees.collect_payment`).
- `role_permissions`: Role-to-permission mapping table.
- `refresh_tokens`: Persisted JWT refresh tokens for token rotation and instant revocation.
- `login_attempts`: IP and username rate-limiting counters for brute-force lockout.
- `audit_logs`: Detailed tamper-evident event log (actor, action, entity, IP, timestamp).

### 2.3 Students & Guardians
- `students`: Core student records (admission number, names, DOB, contact, branch, status).
- `guardians`: Parent/guardian contact directory (NIC, phone, relationship).
- `student_guardians`: Many-to-many relationship linking students to authorized guardians.
- `student_documents`: Secure uploaded student certificates and identity files.
- `student_notes`: Staff notes and administrative remarks regarding students.

### 2.4 Academics & Timetable
- `academic_years`: Academic calendar periods.
- `subjects`: Master subject catalogue.
- `courses`: Multi-subject or certified educational programmes.
- `batches`: Class cohorts (subject, teacher, schedule, monthly fee, capacity).
- `enrollments`: Student-to-batch registrations with enrollment status.
- `class_schedules`: Recurring weekly timetable slots.
- `class_sessions`: Concrete calendar sessions with date, time, room, and status.

### 2.5 Attendance
- `attendance_records`: Check-in records (student, session, status: present, late, absent, excused).
- `attendance_corrections`: Audit trail of manual attendance status overrides.

### 2.6 Fees & Financial Invoicing
- `fee_types`: Catalog of fees (Tuition, Admission, Facility, Lab, Exam).
- `invoices`: Fee invoices with total amount, paid amount, balance, and status.
- `invoice_items`: Detailed line items per invoice.
- `payments`: Cash, card, and bank receipts collected from students.
- `payment_allocations`: Mapping connecting payments to specific invoices.
- `refunds`: Formally approved refund records with reversal links.

### 2.7 Double-Entry General Ledger
- `chart_of_accounts`: Hierarchical ledger accounts (1000 Assets, 2000 Liabilities, 3000 Equity, 4000 Income, 5000 Expenses).
- `financial_accounts`: Specific physical bank and petty cash accounts with current balance.
- `journal_entries`: Transaction vouchers with entry number, date, and tenant ID.
- `journal_lines`: Individual debit and credit legs (`entry_id`, `account_id`, `type`, `amount`).
- `accounting_periods`: Fiscal months/years with open/closed status flags.

### 2.8 Teacher Management & Compensation
- `teachers`: Teacher profiles, educational qualifications, and default pay terms.
- `teacher_availability`: Weekly availability calendar for scheduling.
- `teacher_payment_rules`: Compensation formula definitions (revenue %, hourly, flat rate).
- `teacher_payment_periods`: Monthly settlement cycles.
- `teacher_payment_calculations`: Computed compensation breakdowns per batch.
- `teacher_payments`: Actual disbursement records with payment voucher references.

### 2.9 Examinations & LMS
- `examinations`: Scheduled exams with batch, subject, date, and maximum marks.
- `grading_rules`: Grade boundaries (A: 75+, B: 65+, C: 50+, S: 35+, F: <35).
- `exam_marks`: Individual student marks, calculated grade, and remarks.
- `learning_materials`: Study notes, PDF handouts, and uploaded revision materials.
- `assignments`: Homework tasks with submission deadlines.
- `student_submissions`: Student uploaded coursework assignments and teacher feedback.

### 2.10 Operational Expenses & System Backups
- `expense_categories`: Categorization (Rent, Utilities, Marketing, Printing, Salaries).
- `expenses`: Incurred expense records.
- `expense_payments`: Bank/cash payment disbursements for expenses.
- `announcements`: Role-targeted institute-wide broadcast notices.
- `notifications`: User-specific in-app notification alerts.
- `backup_records`: Automated database SQL dump archives with file size and hash.

---

## 3. Database Indexes & Performance Optimization

All foreign keys carry explicit B-tree indexes:
- `idx_students_tenant_branch`: `(tenant_id, branch_id)`
- `idx_students_admission`: `(tenant_id, admission_number)` UNIQUE
- `idx_attendance_session_student`: `(session_id, student_id)` UNIQUE
- `idx_invoices_tenant_student`: `(tenant_id, student_id, status)`
- `idx_journal_lines_entry`: `(journal_entry_id, account_id)`
- `idx_refresh_tokens_hash`: `(token_hash)` UNIQUE

ACID compliance is guaranteed across billing, invoice allocation, and journal posting using `PDO::beginTransaction()` and `PDO::commit()`.

