INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `is_system`, `created_at`, `updated_at`)
VALUES
    (11, 'cashier', 'Cashier', 'Counter Cashier & Receipts Clerk', 1, NOW(), NOW()),
    (12, 'admissions_officer', 'Admissions Officer', 'Student Admissions & Enquiries', 1, NOW(), NOW()),
    (13, 'academic_coordinator', 'Academic Coordinator', 'Timetable, Classes & Exam Coordination', 1, NOW(), NOW()),
    (14, 'hr_manager', 'HR Manager', 'Teacher Profiles & Compensation Management', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `display_name` = VALUES(`display_name`), `description` = VALUES(`description`);
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 11, id FROM `permissions` WHERE `name` IN (
    'fees.view', 'fees.collect_payment', 'fees.create_invoice'
);
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 12, id FROM `permissions` WHERE `name` IN (
    'students.view', 'students.create', 'students.edit',
    'fees.view',
    'reports.academic'
);
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 13, id FROM `permissions` WHERE `name` IN (
    'students.view',
    'attendance.view', 'attendance.record', 'attendance.correct',
    'academic.manage', 'timetable.manage', 'teachers.manage',
    'reports.academic'
);
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 14, id FROM `permissions` WHERE `name` IN (
    'teachers.manage',
    'teacher_payments.view', 'teacher_payments.manage',
    'reports.academic'
);
