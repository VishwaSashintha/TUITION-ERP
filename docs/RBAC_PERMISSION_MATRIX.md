# TuitionPro ERP — Role-Based Access Control (RBAC) Matrix

**Architecture:** Fine-Grained Role-Based Access Control with Tenant Scoping  
**Total Supported Roles:** 14  
**Total Permissions:** 22 Granular System Tokens  

---

## 1. Supported User Roles

| ID | Role Slug | Display Name | Primary Scope |
|---|---|---|---|
| 1 | `super_admin` | Platform Super Administrator | Global multi-tenant administration, plans, platform health |
| 2 | `support_admin` | Platform Support Administrator | Technical diagnostics, monitored tenant support |
| 3 | `institute_owner` | Institute Owner / Executive | Universal authority over active institute and all branches |
| 4 | `institute_admin` | Institute Administrator | Day-to-day operations, academic management, general finance |
| 5 | `branch_manager` | Branch Manager | Operations restricted strictly to assigned branch campus |
| 6 | `accountant` | Accountant | General Ledger, trial balance, P&L, teacher compensation, expenses |
| 7 | `receptionist` | Receptionist | Front-desk admissions, student search, cash counter collections |
| 8 | `teacher` | Teacher | Assigned batches, personal timetable, attendance, marks entry |
| 9 | `student` | Student | Enrolled batches, personal timetable, fee receipts, exam marks |
| 10 | `parent` | Parent / Guardian | Linked children's attendance, fees, results, announcements |
| 11 | `cashier` | Cashier | Counter fee collections, payment receipts, invoice viewing |
| 12 | `admissions_officer` | Admissions Officer | Student inquiries, registration, batch enrollment |
| 13 | `academic_coordinator`| Academic Coordinator | Timetable, class sessions, teacher assignments, exams |
| 14 | `hr_manager` | HR Manager | Teacher profiles, workload monitoring, salary calculations |

---

## 2. Granular Permissions Definition

```
students.view           View student directory and profiles
students.create         Register new students
students.edit           Modify student details
students.delete         Soft-delete student records

fees.view               View fee structures and invoice listings
fees.create_invoice     Generate ad-hoc or batch invoices
fees.collect_payment    Process payments and issue receipts
fees.refund             Approve and process payment refunds

attendance.view         View attendance history and rosters
attendance.record       Mark daily attendance registers
attendance.correct      Perform authorized attendance adjustments

academic.manage         Create and modify courses, subjects, batches
timetable.manage        Configure schedules and generate class sessions
teachers.manage         Manage teacher profiles and assignments

teacher_payments.view   View teacher payment records and calculations
teacher_payments.manage Calculate and disburse teacher compensations

expenses.manage         Record and categorize institute expenses
accounts.view           Inspect Chart of Accounts, Bank & Cash balances
journals.post           Create double-entry general ledger journal entries

reports.academic        Generate attendance, batch, and exam reports
reports.financial       Generate Trial Balance, Profit & Loss, Fee reports

settings.manage         Configure institute branches, users, and backups
```

---

## 3. Comprehensive Role Permission Matrix

| Permission | Owner | Admin | Branch Mgr | Accountant | Receptionist | Cashier | Teacher | Student / Parent |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| `students.view` | [x] | [x] | [x] | [ ] | [x] | [ ] | [x] (Assigned) | [x] (Self/Child) |
| `students.create` | [x] | [x] | [x] | [ ] | [x] | [ ] | [ ] | [ ] |
| `students.edit` | [x] | [x] | [x] | [ ] | [x] | [ ] | [ ] | [ ] |
| `students.delete` | [x] | [x] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |
| `fees.view` | [x] | [x] | [x] | [x] | [x] | [x] | [ ] | [x] (Own dues) |
| `fees.create_invoice` | [x] | [x] | [ ] | [x] | [ ] | [ ] | [ ] | [ ] |
| `fees.collect_payment`| [x] | [x] | [x] | [x] | [x] | [x] | [ ] | [ ] |
| `fees.refund` | [x] | [x] | [ ] | [x] | [ ] | [ ] | [ ] | [ ] |
| `attendance.view` | [x] | [x] | [x] | [ ] | [x] | [ ] | [x] | [x] (Self/Child) |
| `attendance.record` | [x] | [x] | [x] | [ ] | [x] | [ ] | [x] | [ ] |
| `attendance.correct` | [x] | [x] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |
| `academic.manage` | [x] | [x] | [x] | [ ] | [ ] | [ ] | [ ] | [ ] |
| `timetable.manage` | [x] | [x] | [x] | [ ] | [ ] | [ ] | [ ] | [ ] |
| `teachers.manage` | [x] | [x] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |
| `teacher_payments.view`|[x] | [ ] | [ ] | [x] | [ ] | [ ] | [ ] | [ ] |
| `teacher_payments.manage`|[x]| [ ] | [ ] | [x] | [ ] | [ ] | [ ] | [ ] |
| `expenses.manage` | [x] | [x] | [ ] | [x] | [ ] | [ ] | [ ] | [ ] |
| `accounts.view` | [x] | [x] | [ ] | [x] | [ ] | [ ] | [ ] | [ ] |
| `journals.post` | [x] | [ ] | [ ] | [x] | [ ] | [ ] | [ ] | [ ] |
| `reports.academic` | [x] | [x] | [x] | [ ] | [ ] | [ ] | [x] (Assigned) | [ ] |
| `reports.financial` | [x] | [x] | [ ] | [x] | [ ] | [ ] | [ ] | [ ] |
| `settings.manage` | [x] | [x] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] |

---

## 4. Server-Side Enforcement Rules

1. **Defense in Depth:** Even if a menu item is made visible on the client, `RoleMiddleware` intercepts the HTTP route and aborts execution with `HTTP 403 Forbidden` if the user's role is not authorized.
2. **Dashboard Query Segregation:** `DashboardController` branches into dedicated methods (`adminDashboard`, `receptionistDashboard`, `teacherDashboard`) so that unpermitted queries (e.g. `financial_accounts` current balance, profit/loss totals) are **never executed** for front-desk and academic roles.
3. **Anti-IDOR Policy:** Resource controllers verify that the requested model belongs to the active `tenant_id` and that parents are explicitly linked to the requested student in `student_guardians`.
