DELETE FROM `role_permissions`;
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, id FROM `permissions`;
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
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 5, id FROM `permissions` WHERE `name` IN (
    'students.view', 'students.create', 'students.edit',
    'fees.view', 'fees.collect_payment',
    'attendance.view', 'attendance.record',
    'academic.manage', 'timetable.manage',
    'reports.academic'
);
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 6, id FROM `permissions` WHERE `name` IN (
    'fees.view', 'fees.create_invoice', 'fees.collect_payment', 'fees.refund',
    'teacher_payments.view', 'teacher_payments.manage',
    'expenses.manage', 'accounts.view', 'journals.post',
    'reports.financial'
);
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 7, id FROM `permissions` WHERE `name` IN (
    'students.view', 'students.create', 'students.edit',
    'fees.view', 'fees.collect_payment',
    'attendance.view', 'attendance.record'
);
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 8, id FROM `permissions` WHERE `name` IN (
    'attendance.view', 'attendance.record',
    'academic.manage'
);
