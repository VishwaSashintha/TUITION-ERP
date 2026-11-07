-- TuitionPro ERP: Seed Default Role Permissions
-- Inserts role-permission matrix ensuring strict RBAC enforcement

-- Clear existing role permissions safely
DELETE FROM `role_permissions`;

-- 1. institute_owner (Role 3): All permissions (IDs 1 to 22)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, id FROM `permissions`;

-- 2. institute_admin (Role 4): Operations, Students, Fees, Attendance, Academics, Teachers, Expenses, Reports, Settings
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 4, id FROM `permissions` WHERE `name` IN (
    'students.view', 'students.create', 'students.edit', 'students.delete',
    'fees.view', 'fees.create_invoice', 'fees.collect_payment', 'fees.refund',
    'attendance.view', 'attendance.record', 'attendance.correct',
    'academic.manage', 'timetable.manage', 'teachers.manage',
    'expenses.manage', 'accounts.view',
    'reports.academic', 'reports.financial',
    'settings.manage'
);

-- 3. branch_manager (Role 5): Branch-level student ops, fees collection, attendance, academics
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 5, id FROM `permissions` WHERE `name` IN (
    'students.view', 'students.create', 'students.edit',
    'fees.view', 'fees.collect_payment',
    'attendance.view', 'attendance.record',
    'academic.manage', 'timetable.manage',
    'reports.academic'
);

-- 4. accountant (Role 6): Full Financials, Invoices, Collections, Refunds, Teacher Payments, Expenses, Accounts, Journals, Financial Reports
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 6, id FROM `permissions` WHERE `name` IN (
    'fees.view', 'fees.create_invoice', 'fees.collect_payment', 'fees.refund',
    'teacher_payments.view', 'teacher_payments.manage',
    'expenses.manage', 'accounts.view', 'journals.post',
    'reports.financial'
);

-- 5. receptionist (Role 7): Front-Desk Operations ONLY (Students view/create/edit, Fees view/collect, Attendance view/record)
-- STRICTLY NO: accounts.view, journals.post, expenses.manage, teacher_payments.view/manage, reports.financial, settings.manage
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 7, id FROM `permissions` WHERE `name` IN (
    'students.view', 'students.create', 'students.edit',
    'fees.view', 'fees.collect_payment',
    'attendance.view', 'attendance.record'
);

-- 6. teacher (Role 8): Academic & Classroom duties only
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 8, id FROM `permissions` WHERE `name` IN (
    'attendance.view', 'attendance.record',
    'academic.manage'
);

