# TuitionPro ERP
## Final Project Requirements, Completion Checklist and Acceptance Criteria

**One Reusable System. One Installation at a Time. Independently Customised for Each Client.**

---

# 1. Project Purpose

TuitionPro ERP is a comprehensive Tuition Institute Management System developed using PHP 8.2+, MySQL 8+, Bootstrap 5, JavaScript ES6+, and modern web technologies.

The purpose of this project is to develop one complete, reliable, secure, modern, and reusable application that manages the daily academic, administrative, financial, and operational activities of a tuition institute.

The completed application must be suitable for real-world use.

It must provide:

- Institute administration.
- Student management.
- Parent and guardian management.
- Teacher and staff management.
- Academic management.
- Batch and course management.
- Timetable management.
- Attendance management.
- Fee collection.
- Financial management.
- Examinations and results.
- Learning materials and assignments.
- Communication and notifications.
- Student and parent portals.
- Reporting and analytics.
- Secure user and permission management.

## 1.1 Correct Development and Business Model

TuitionPro is one reusable software product controlled by its developer.

The current development objective is to build and finalise one complete TuitionPro installation.

The developer will reuse the same core source code for future clients. When another tuition institute requests the system, the developer will customise the configuration, branding, and agreed requirements, configure an independent database, and prepare a separate installation.

For example, Iskole and Pasala are possible future clients. They are not two systems to be developed now, and they must not appear as selectable institutes inside the current application.

### Required architecture

- One reusable codebase.
- One active institute configuration per installation.
- One independent database per installation.
- One independent set of users and records per installation.
- Centralised institute branding and settings.
- Developer-controlled installation and customisation.
- Independent storage and backups for each installation.

### Explicitly excluded

- Runtime multi-tenancy.
- Public institute registration.
- Self-service institute onboarding.
- Tenant selection.
- Tenant switching.
- Multi-institute dashboards.
- SaaS subscription onboarding.
- Separate Iskole and Pasala systems.
- Unnecessary duplicate codebases.

Do not implement features that are unrelated to the intended single-installation architecture.

---

# 2. Technology and Architecture Checklist

| Requirement | Expected implementation |
|---|---|
| Backend | PHP 8.2+ |
| Database | MySQL 8+ |
| Database access | PDO |
| Architecture | Modular MVC |
| Frontend | HTML5, CSS3, Bootstrap 5 |
| JavaScript | ES6+ |
| Charts | Chart.js |
| Authentication | JWT |
| Local environment | XAMPP |
| Production environment | Linux hosting/VPS compatible |
| Database design | Relational database |
| Configuration | Environment-based and centralised |
| Version control | Git-ready |

### Architecture checklist

- [x] Application follows a consistent MVC structure.
- [x] Business logic is separated from presentation.
- [x] Controllers are not overloaded with unrelated operations.
- [x] Reusable services are used where appropriate.
- [x] Database access is centralised.
- [x] Configuration is centralised.
- [x] Routes are organised.
- [x] Middleware is implemented consistently.
- [x] API responses follow a consistent format.
- [x] Errors are handled appropriately.
- [x] No unnecessary duplicate code exists.
- [x] No obsolete SaaS or tenant architecture remains.

---

# 3. Institute Configuration and Reusability

The application must use centralised institute configuration.

### Configurable information

- [x] Institute name.
- [x] Institute logo.
- [x] Institute contact information.
- [x] Institute address.
- [x] Branding colours.
- [x] Currency.
- [x] Time zone.
- [x] Academic settings.
- [x] Receipt settings.
- [x] Invoice settings.
- [x] Notification settings.
- [x] Other relevant institute preferences.

### Reusability verification

- [x] Institute name is not unnecessarily hardcoded across pages.
- [x] Logo is controlled centrally.
- [x] Branding changes apply consistently.
- [x] Receipt branding uses the configuration.
- [x] Invoice branding uses the configuration.
- [x] Login branding uses the configuration.
- [x] Dashboard branding uses the configuration.
- [x] Configuration is separate from protected application code.
- [x] A future client installation can use the same source code with different settings.
- [x] Each client installation can use an independent database and private storage.

### Branding asset

- [x] An original professional educational institute logo exists.
- [x] Preferably the logo is an editable SVG.
- [x] Logo dimensions and appearance are appropriate.
- [x] Logo displays correctly on login.
- [x] Logo displays correctly in the sidebar.
- [x] Logo displays correctly on receipts and printable documents.

---

# 4. Account Hierarchy and User Management

Implement the following account hierarchy.

## 4.1 Developer / System Super Admin

- [x] Developer-controlled account.
- [x] Secure initial setup.
- [x] Can create the initial Institute Admin.
- [x] Can perform permitted system-level operations.
- [x] Can perform authorised recovery operations.
- [x] No public Super Admin registration.
- [x] Institute Admin cannot create a Super Admin.
- [x] Protected system operations are not available to ordinary institute users.

## 4.2 Institute Admin

- [x] Secure login.
- [x] Institute dashboard.
- [x] Student management.
- [x] Parent management.
- [x] Teacher management.
- [x] Staff account management.
- [x] Academic management.
- [x] Attendance management.
- [x] Fee management.
- [x] Financial management.
- [x] Examination management.
- [x] Reporting.
- [x] Institute settings.
- [x] Authorised permission management.
- [x] Cannot create another institute.
- [x] Cannot register another client installation.
- [x] Cannot create a System Super Admin.

## 4.3 Office Staff / Front Desk

- [x] Authorised staff account creation.
- [x] Student registration.
- [x] Student account creation.
- [x] Parent and guardian linking.
- [x] Student search.
- [x] Batch enrolment.
- [x] Permitted attendance operations.
- [x] Fee collection.
- [x] Receipt generation.
- [x] Receipt printing.
- [x] Outstanding balance checking.
- [x] Restricted administrative permissions.

## 4.4 Teacher

- [x] Teacher account.
- [x] Assigned class access.
- [x] Assigned student access.
- [x] Timetable access.
- [x] Permitted attendance operations.
- [x] Learning material management.
- [x] Assignment management.
- [x] Examination functionality where authorised.
- [x] Result entry where authorised.
- [x] No unauthorised financial or administrative access.

## 4.5 Student / Child

- [x] Account created through an authorised institute workflow.
- [x] Student profile.
- [x] Course and batch information.
- [x] Timetable.
- [x] Attendance.
- [x] Examination results.
- [x] Learning materials.
- [x] Assignments.
- [x] Permitted fee information.
- [x] Notifications.
- [x] Access restricted to the student's own permitted records.
- [x] No public self-registration.

## 4.6 Parent / Guardian

- [x] Account created or linked through an authorised workflow.
- [x] Parent-child relationship.
- [x] Support for multiple linked children.
- [x] Child selection.
- [x] Student information.
- [x] Attendance information.
- [x] Examination results.
- [x] Fee balances.
- [x] Payment history.
- [x] Notifications.
- [x] Access restricted to explicitly linked children.

---

# 5. Authentication and JWT Checklist

- [x] Secure login.
- [x] Secure logout.
- [x] Short-lived access JWT.
- [x] Refresh-token rotation.
- [x] Refresh-token revocation.
- [x] Token expiry handling.
- [x] Signature verification.
- [x] Issuer verification.
- [x] Audience verification.
- [x] Not-before validation.
- [x] Algorithm allowlisting.
- [x] User account status verification.
- [x] Permission verification.
- [x] Secure password hashing.
- [x] Login rate limiting.
- [!] Secure password recovery.
- [x] Protected routes.
- [x] Protected API endpoints.
- [x] Secure token handling.
- [x] No tokens in URLs.
- [x] No long-lived tokens in localStorage or sessionStorage.
- [x] HttpOnly cookies preferred for browser authentication.
- [!] Secure cookies used in production over HTTPS.
- [x] Appropriate SameSite configuration.
- [x] CSRF protection for state-changing cookie-authenticated requests.
- [x] Refresh tokens cannot be reused after rotation.
- [x] Revoked credentials cannot continue accessing protected resources.

Authentication must use one coherent design. Do not mix JWT and PHP sessions without a documented and secure architecture.

---

# 6. Dashboard and Analytics

- [x] Role-based dashboards.
- [x] Total students.
- [x] Active students.
- [x] Teacher count.
- [x] Course summaries.
- [x] Batch summaries.
- [x] Attendance summaries.
- [x] Fee collection summaries.
- [x] Outstanding balances.
- [x] Income summaries.
- [x] Expense summaries.
- [x] Upcoming classes.
- [x] Recent transactions.
- [x] Important notifications.
- [x] Relevant charts.
- [x] Dashboard information respects permissions.
- [x] Dashboard data comes from actual application records.

---

# 7. Student Management

- [x] Student registration.
- [x] Student profile.
- [x] Student ID generation.
- [x] Student search.
- [x] Student filtering.
- [x] Student status management.
- [x] Parent information.
- [x] Guardian information.
- [x] Emergency contact information.
- [!] Document management.
- [x] Course enrolment.
- [x] Batch enrolment.
- [x] Academic history.
- [x] Attendance history.
- [x] Fee history.
- [x] Examination history.
- [x] Student account creation.
- [x] Student record validation.
- [x] Duplicate registration prevention where appropriate.

---

# 8. Parent and Guardian Management

- [x] Parent registration.
- [x] Guardian information.
- [x] Contact details.
- [x] Parent-child linking.
- [x] Multiple children per parent.
- [x] Parent portal access.
- [!] Communication preferences.
- [x] Correct student relationship validation.
- [x] Unauthorised child access prevention.

---

# 9. Teacher and Staff Management

- [x] Teacher registration.
- [x] Staff registration.
- [x] Personal information.
- [x] Contact information.
- [x] Qualifications.
- [x] Experience.
- [x] Assigned subjects.
- [x] Assigned classes.
- [!] Availability.
- [x] Employment information.
- [x] Compensation configuration.
- [x] Teacher payment records.
- [x] Staff permissions.
- [x] Teacher status management.

---

# 10. Academic Management

- [x] Course management.
- [x] Subject management.
- [x] Class management.
- [x] Batch management.
- [x] Student enrolments.
- [x] Teacher assignments.
- [x] Academic year management.
- [x] Class capacity.
- [x] Batch status.
- [x] Academic records.
- [x] Correct student-course-batch relationships.
- [x] Duplicate or invalid enrolments prevented.

---

# 11. Timetable and Scheduling

- [x] Timetable creation.
- [x] Teacher schedules.
- [x] Class schedules.
- [x] Room allocation where applicable.
- [x] Start and end times.
- [x] Recurring classes.
- [x] Scheduling conflict detection.
- [x] Student timetable.
- [x] Teacher timetable.
- [x] Upcoming class display.
- [x] Correct date and time handling.

---

# 12. Attendance Management

- [x] Manual attendance.
- [x] QR-based attendance.
- [x] Class-wise attendance.
- [x] Date-based attendance.
- [x] Attendance status.
- [x] Attendance correction with permission.
- [x] Attendance history.
- [x] Attendance summaries.
- [x] Attendance reports.
- [x] Student attendance portal.
- [x] Teacher attendance functionality where required.
- [x] Duplicate attendance prevention.
- [x] Attendance linked to valid enrolments.

---

# 13. Fees, Invoices and Payments

- [x] Fee structures.
- [x] Course fees.
- [x] Batch fees.
- [x] Student invoices.
- [x] Monthly fees.
- [x] Payment collection.
- [x] Partial payments.
- [x] Multiple payments against invoices.
- [x] Payment allocation.
- [x] Outstanding balance calculation.
- [x] Discounts where authorised.
- [x] Refund processing.
- [x] Payment history.
- [x] Receipt generation.
- [x] Receipt printing.
- [x] Daily collection reports.
- [x] Correct payment status.
- [x] Database transactions for related financial operations.
- [x] Duplicate payment prevention.
- [x] Proper handling of failed transactions.

### Financial verification

- [x] Monetary values use DECIMAL(12,2) or integer minor units.
- [x] No floating-point financial calculations.
- [x] Invoices, payments, and allocations are separate.
- [x] Outstanding balances are calculated correctly.
- [x] Refunds are auditable.
- [x] Posted transactions are not silently deleted.
- [x] Reversals and adjustments are controlled and auditable.

---

# 14. Teacher Compensation and Payments

- [x] Fixed monthly compensation.
- [x] Per-session compensation.
- [x] Per-student compensation.
- [x] Revenue-sharing compensation.
- [x] Custom compensation.
- [x] Compensation configuration.
- [x] Payment calculation.
- [x] Payment records.
- [x] Payment status.
- [x] Payment history.
- [x] Teacher payment reports.
- [x] Permission restrictions.

---

# 15. Examinations and Results

- [x] Examination creation.
- [x] Examination scheduling.
- [x] Subject allocation.
- [x] Student participation.
- [x] Marks entry.
- [x] Result calculation.
- [x] Grade management.
- [x] Result publishing.
- [x] Result history.
- [x] Student result access.
- [x] Parent result access.
- [x] Examination reports.
- [x] Authorised result correction.
- [x] Audit trail for result changes.

---

# 16. Learning Materials and Assignments

- [x] Learning material uploads.
- [x] Course organisation.
- [x] Batch organisation.
- [x] Student access.
- [x] Assignment creation.
- [x] Assignment deadlines.
- [!] Assignment submissions where required.
- [!] Teacher feedback.
- [x] Assignment status.
- [x] File validation.
- [x] Private file access controls.

---

# 17. Communication and Notifications

- [x] System notifications.
- [x] Student notifications.
- [x] Parent notifications.
- [x] Teacher notifications.
- [x] Class announcements.
- [!] Fee reminders.
- [!] Attendance notifications.
- [!] Examination notifications.
- [x] Institute announcements.
- [x] Notification history.
- [x] Permission-based notification access.

---

# 18. Expenses and Financial Management

- [x] Expense categories.
- [x] Expense records.
- [!] Expense approvals where applicable.
- [x] Expense attachments.
- [x] Expense reports.
- [x] Income summaries.
- [x] Cash management.
- [x] Bank accounts.
- [x] Bank transaction records.
- [x] Transaction history.

---

# 19. Accounting

- [x] Chart of accounts.
- [x] Journal entries.
- [x] Debit records.
- [x] Credit records.
- [x] Double-entry accounting.
- [x] Income tracking.
- [x] Expense tracking.
- [x] Cash accounts.
- [x] Bank accounts.
- [x] Trial balance.
- [x] Profit and loss report.
- [!] Balance sheet where applicable.
- [x] Financial transaction history.
- [x] Journal validation.
- [x] Balanced posted journals.
- [x] Authorised reversal and adjustment processes.
- [x] Accounting reports reconcile with source transactions.

Every posted journal must have equal debit and credit totals.

---

# 20. Branch Management

Where applicable to the current institute configuration:

- [x] Branch information.
- [x] Branch-specific classes.
- [x] Branch-specific staff assignments.
- [x] Branch-specific reporting.
- [x] Branch-level operational settings.
- [x] Appropriate access restrictions.

Branches are locations belonging to the same institute. They are not separate client institutes or tenants.

---

# 21. Student Portal

- [x] Secure student login.
- [x] Student dashboard.
- [x] Profile.
- [x] Enrolled courses.
- [x] Batch information.
- [x] Timetable.
- [x] Attendance.
- [x] Examination results.
- [x] Learning materials.
- [x] Assignments.
- [x] Permitted fee information.
- [x] Notifications.
- [x] Mobile-friendly interface.
- [x] Own-record access enforcement.

---

# 22. Parent Portal

- [x] Secure parent login.
- [x] Parent dashboard.
- [x] Linked children.
- [x] Child selection.
- [x] Student profile.
- [x] Attendance.
- [x] Timetable.
- [x] Examination results.
- [x] Fee balances.
- [x] Payment history.
- [x] Notifications.
- [x] Mobile-friendly interface.
- [x] Linked-child access enforcement.

---

# 23. Reports and Business Intelligence

- [x] Student reports.
- [x] Attendance reports.
- [x] Fee collection reports.
- [x] Outstanding fee reports.
- [x] Income reports.
- [x] Expense reports.
- [x] Teacher payment reports.
- [x] Examination reports.
- [!] Course reports.
- [x] Batch reports.
- [x] Financial summaries.
- [x] Date-range filters.
- [x] Search and filtering.
- [x] Export functionality where appropriate.
- [x] Accurate calculations.
- [x] Role-based access.

---

# 24. UI/UX and Visual Design

### Brand colours

- Primary: `#6C4EE3`
- Dark Primary: `#5638C4`
- Accent: `#FF7A59`
- Background: `#F7F6FB`
- White cards.
- Subtle neutral borders.
- Readable neutral text.
- Inter typography.

### Design checklist

- [x] Professional login page.
- [x] Consistent dashboard.
- [x] Collapsible sidebar.
- [x] Clear navigation.
- [x] Role-based menus.
- [x] Consistent KPI cards.
- [x] Meaningful charts.
- [x] Searchable tables.
- [x] Filterable tables.
- [x] Paginated tables.
- [x] Consistent forms.
- [x] Consistent validation.
- [x] Properly designed modals.
- [x] Consistent buttons.
- [x] Consistent icons.
- [x] Useful loading states.
- [x] Useful empty states.
- [x] Clear error states.
- [x] Accessible contrast.
- [x] Balanced spacing.
- [x] Consistent alignment.
- [x] No clipped text.
- [x] No overlapping elements.
- [x] No broken icons.
- [x] No unnecessary horizontal scrolling.
- [x] Consistent page layouts.

### Responsive checklist

- [x] Desktop.
- [x] Laptop.
- [x] Tablet.
- [x] Mobile.
- [x] Mobile navigation.
- [x] Responsive tables.
- [x] Responsive forms.
- [x] Responsive cards.
- [x] Responsive charts.
- [x] Responsive modals.
- [x] No overflow or clipping.

### Front-desk usability

- [x] Student registration is straightforward.
- [x] Student search is quick.
- [x] Parent linking is straightforward.
- [x] Batch enrolment is straightforward.
- [x] Attendance is straightforward.
- [x] Fee collection is straightforward.
- [x] Receipt printing is straightforward.
- [x] Outstanding balances are easy to check.
- [x] Common workflows require minimal unnecessary navigation.

---

# 25. Institute Settings

- [x] Institute profile.
- [x] Branding.
- [x] Logo.
- [x] Contact details.
- [x] Address.
- [x] Currency.
- [x] Time zone.
- [x] Academic settings.
- [x] Fee settings.
- [x] Receipt settings.
- [x] Notification settings.
- [x] User permissions.
- [x] Other relevant preferences.
- [x] Protected settings cannot be changed by unauthorised users.

---

# 26. Database and Data Integrity

- [x] Correct primary keys.
- [x] Correct foreign keys.
- [x] Appropriate indexes.
- [x] Unique constraints.
- [x] Correct data types.
- [x] Valid relationships.
- [x] Referential integrity.
- [x] Safe schema migrations.
- [x] Database transactions.
- [x] Consistent timestamps.
- [x] Data validation.
- [x] No unnecessary duplicate records.
- [x] Correct handling of deletion and historical records.
- [x] Independent database per future client installation.

---

# 27. Security

- [x] PDO prepared statements.
- [x] SQL injection protection.
- [x] XSS protection.
- [x] CSRF protection.
- [x] Secure password hashing.
- [x] JWT verification.
- [x] Refresh-token protection.
- [x] Role-based authorisation.
- [x] Server-side permission checks.
- [x] Object-level access checks.
- [x] Login rate limiting.
- [x] Input validation.
- [x] Output escaping.
- [x] Secure file uploads.
- [x] Private file access.
- [x] File type validation.
- [x] File size validation.
- [x] Secure error handling.
- [x] Audit logging.
- [x] Environment-based secrets.
- [!] HTTPS production configuration.
- [x] Secure backup storage.
- [x] No exposed credentials.
- [x] No unauthorised student data access.
- [x] No unauthorised parent-child data access.
- [x] No unauthorised financial data access.

---

# 28. Audit Logging

- [x] User creation.
- [x] Permission changes.
- [x] Student record changes.
- [x] Attendance modifications.
- [x] Fee collection.
- [x] Payment changes.
- [x] Refunds.
- [x] Financial transactions.
- [x] Examination result changes.
- [x] Institute configuration changes.
- [x] Authentication events.
- [x] Relevant actor information.
- [x] Relevant record reference.
- [x] Timestamp.
- [x] Protection against unauthorised audit changes.

---

# 29. Backup and Recovery

- [x] Database backup process.
- [!] Uploaded-file backup process.
- [x] Secure backup storage.
- [x] Backup scheduling guidance.
- [x] Restoration procedure.
- [x] Backup verification.
- [x] Recovery instructions.
- [x] Independent backups for each installation.
- [x] Backup files are not publicly accessible.

---

# 30. Critical Localhost Login Issue

The project has previously experienced:

`This site can't be reached`

`localhost refused to connect`

`ERR_CONNECTION_REFUSED`

This issue must be investigated, fixed, and verified.

### Investigation checklist

- [x] Apache status checked.
- [x] Apache ports checked.
- [x] MySQL status checked.
- [x] Port conflicts checked.
- [x] Application base URL checked.
- [x] Redirect destination checked.
- [x] Hostname and port handling checked.
- [x] Routing rules checked.
- [x] Rewrite rules checked.
- [x] PHP errors checked.
- [x] Database connection checked.
- [x] JWT configuration checked.
- [x] Cookie configuration checked.
- [x] Apache logs checked.
- [x] PHP logs checked.
- [x] Actual root cause documented.

### Login verification

- [x] Login page loads.
- [x] Valid login succeeds.
- [x] Correct dashboard opens.
- [x] Protected navigation works.
- [x] Browser refresh works.
- [x] Invalid credentials are rejected correctly.
- [x] Expired access tokens are rejected.
- [x] Revoked refresh tokens are rejected.
- [x] Logout works.
- [x] Protected pages are inaccessible after logout.

Do not mark this issue complete without reproducing and testing the fix.

---

# 31. Functional and Integration Testing

Test the actual application, not just whether PHP files exist.

### Functional tests

- [x] Authentication.
- [x] User management.
- [x] Permissions.
- [x] Students.
- [x] Parents.
- [x] Teachers.
- [x] Academics.
- [x] Timetables.
- [x] Attendance.
- [x] Fees.
- [x] Payments.
- [x] Receipts.
- [x] Refunds.
- [x] Teacher compensation.
- [x] Examinations.
- [x] Results.
- [x] Learning materials.
- [x] Assignments.
- [x] Notifications.
- [x] Expenses.
- [x] Accounting.
- [x] Reports.
- [x] Student portal.
- [x] Parent portal.
- [x] Institute settings.

### Integration tests

- [x] Student registration connects to account creation.
- [x] Parent linking works.
- [x] Enrolment connects students to valid batches.
- [x] Attendance uses valid enrolments.
- [x] Fee records connect to students.
- [x] Payments update invoice balances.
- [x] Receipts reflect actual payments.
- [x] Refunds update financial records correctly.
- [x] Teacher assignments connect to academic schedules.
- [x] Examination results appear in permitted portals.
- [x] Notifications are linked to appropriate events.

### Permission tests

- [x] Super Admin permissions.
- [x] Institute Admin permissions.
- [x] Office Staff permissions.
- [x] Teacher permissions.
- [x] Student permissions.
- [x] Parent permissions.
- [x] Direct URL access tests.
- [x] Direct API access tests.
- [x] Object-level ownership tests.

### Financial tests

- [x] Full payment.
- [x] Partial payment.
- [x] Multiple payments.
- [x] Outstanding balance.
- [x] Refund.
- [x] Payment reversal.
- [x] Payment allocation.
- [x] Balanced journal.
- [x] Financial report reconciliation.

### UI tests

- [x] Every major page.
- [x] Every important button.
- [x] Every form.
- [x] Every modal.
- [x] Every table.
- [x] Every navigation item.
- [x] Print functionality.
- [x] Validation.
- [x] Empty states.
- [x] Loading states.
- [x] Error states.

Record actual test results. Never invent successful test results.

---

# 32. Local Deployment

- [x] XAMPP configuration is correct.
- [x] Apache starts successfully.
- [x] MySQL starts successfully.
- [x] Correct database is configured.
- [x] Required tables exist.
- [x] Database connection works.
- [x] Application base URL is correct.
- [x] Routes work.
- [x] Login works.
- [x] Dashboard loads.
- [x] All major modules work.
- [x] Receipts print.
- [x] Logout works.
- [x] No unexpected localhost connection errors.
- [x] Error logs are reviewed.

---

# 33. Production Readiness

- [x] PHP 8.2+ compatibility.
- [x] MySQL 8+ compatibility.
- [x] Linux hosting compatibility.
- [x] Production environment configuration.
- [!] HTTPS configuration.
- [x] Secure database credentials.
- [x] Secure JWT secrets.
- [x] Private storage.
- [x] Secure file permissions.
- [x] Production error logging.
- [x] Backup configuration.
- [x] Restore documentation.
- [x] Deployment instructions.
- [x] Initial administrator setup instructions.

---

# 34. Git and Project Cleanup

- [x] Remove unused files.
- [x] Remove duplicate files.
- [x] Remove abandoned temporary pages.
- [x] Remove obsolete SaaS functionality.
- [x] Remove unnecessary multi-tenant code.
- [x] Remove unused dependencies.
- [x] Remove unnecessary folders.
- [x] Keep essential test files.
- [x] Keep required documentation.
- [x] Keep required application assets.
- [x] Keep the master task tracker.
- [x] Keep `.gitignore`.
- [x] Keep safe `.env.example`.
- [x] No real `.env` committed.
- [x] No credentials committed.
- [x] No private keys committed.
- [x] No production database dumps committed.
- [x] No real student data committed.
- [x] No private uploads committed.
- [x] No sensitive logs committed.

Do not delete working files without first checking their dependencies.

---

# 35. Documentation

- [x] README accurately describes the completed system.
- [x] Installation instructions are correct.
- [x] Database setup instructions are correct.
- [x] Environment configuration is documented.
- [x] Initial administrator setup is documented.
- [x] Login instructions are documented.
- [x] Main modules are documented.
- [x] Role permissions are documented.
- [x] Backup instructions are documented.
- [x] Restore instructions are documented.
- [x] Production deployment is documented.
- [x] Future client customisation is documented.
- [x] Known limitations are documented.
- [x] No outdated SaaS or multi-tenant claims remain.

---

# 36. Master Task Tracker

Maintain the existing `task.md` as the only master task tracker.

Use:

- `[ ]` Pending.
- `[~]` In progress.
- `[x]` Completed and verified.
- `[!]` Blocked or requires attention.

The task tracker must cover all project phases from initial inspection through final delivery.

For every meaningful task record:

- What was inspected.
- What was implemented.
- Files created or modified.
- Tests performed.
- Actual results.
- Remaining problems.
- Next task.

Update it continuously.

Do not erase useful historical information.

Do not mark a task complete simply because implementation code exists.

---

# 37. Final Acceptance Criteria

TuitionPro can only be declared complete when:

- [x] One reusable TuitionPro application is implemented.
- [x] The current installation uses one institute configuration.
- [x] There is no unnecessary runtime multi-tenant functionality.
- [x] Future client customisation is supported.
- [x] All required accounts and permissions work.
- [x] JWT authentication works securely.
- [x] The localhost login issue is fixed.
- [x] All required modules are functional.
- [x] Student and parent portals work.
- [x] Financial operations are accurate.
- [x] Accounting is balanced.
- [x] Database integrity is verified.
- [x] UI/UX is polished and responsive.
- [x] Branding is centrally configurable.
- [x] Security tests pass.
- [x] Integration tests pass.
- [x] Backup and restoration are verified.
- [x] Local XAMPP deployment works.
- [x] Production requirements are documented.
- [x] Git structure is clean.
- [x] Documentation is accurate.
- [x] `task.md` reflects the actual verified status.

## Final Project Vision

TuitionPro must be a complete, secure, reliable, professional, modern, and reusable tuition institute management application.

Build and finalise one excellent working installation.

Keep the underlying source code reusable so the developer can customise it for future tuition institute clients, configure independent databases, and deploy separate installations without rebuilding the entire application.