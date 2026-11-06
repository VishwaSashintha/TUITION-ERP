-- TuitionPro ERP — Master Relational Database Schema
-- Multi-Tenant Tuition Institute Management System
-- Compatible with MySQL 8.0+ / MariaDB 10.5+

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- DOMAIN 1: PLATFORM, TENANTS & SUBSCRIPTIONS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `subscription_plans` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `billing_period` ENUM('monthly', 'annual') NOT NULL DEFAULT 'monthly',
    `student_limit` INT UNSIGNED NOT NULL DEFAULT 150,
    `branch_limit` INT UNSIGNED NOT NULL DEFAULT 1,
    `user_limit` INT UNSIGNED NOT NULL DEFAULT 5,
    `features_json` JSON NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tenants` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid` CHAR(36) NOT NULL UNIQUE,
    `name` VARCHAR(150) NOT NULL,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `address` TEXT NULL,
    `logo` VARCHAR(255) NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'LKR',
    `currency_symbol` VARCHAR(10) NOT NULL DEFAULT 'Rs.',
    `timezone` VARCHAR(50) NOT NULL DEFAULT 'Asia/Colombo',
    `status` ENUM('trial', 'active', 'past_due', 'suspended', 'expired', 'cancelled') NOT NULL DEFAULT 'trial',
    `subscription_plan_id` INT UNSIGNED NULL,
    `trial_ends_at` DATETIME NULL,
    `subscription_ends_at` DATETIME NULL,
    `modules_config` JSON NULL,
    `settings_json` JSON NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_tenant_code` (`code`),
    INDEX `idx_tenant_status` (`status`),
    CONSTRAINT `fk_tenants_plan` FOREIGN KEY (`subscription_plan_id`) REFERENCES `subscription_plans`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `plan_id` INT UNSIGNED NOT NULL,
    `status` ENUM('active', 'expired', 'cancelled') NOT NULL DEFAULT 'active',
    `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `starts_at` DATETIME NOT NULL,
    `ends_at` DATETIME NOT NULL,
    `auto_renew` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sub_tenant` (`tenant_id`),
    CONSTRAINT `fk_sub_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sub_plan` FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `platform_users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('super_admin', 'support_admin') NOT NULL DEFAULT 'super_admin',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `last_login_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- DOMAIN 2: INSTITUTE BRANCHES & ACADEMIC YEARS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `branches` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `code` VARCHAR(50) NOT NULL,
    `address` TEXT NULL,
    `phone` VARCHAR(30) NULL,
    `email` VARCHAR(150) NULL,
    `manager_name` VARCHAR(100) NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_branches_tenant` (`tenant_id`),
    UNIQUE KEY `uk_tenant_branch_code` (`tenant_id`, `code`),
    CONSTRAINT `fk_branches_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `academic_years` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_acad_tenant` (`tenant_id`),
    CONSTRAINT `fk_acad_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- DOMAIN 3: USERS, ROLES & PERMISSIONS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NULL, -- NULL indicates global system role
    `name` VARCHAR(50) NOT NULL,
    `display_name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `is_system` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_roles_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `module` VARCHAR(50) NOT NULL,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `display_name` VARCHAR(150) NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_permissions_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `branch_id` INT UNSIGNED NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(60) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `last_login_at` DATETIME NULL,
    `last_login_ip` VARCHAR(45) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_users_tenant` (`tenant_id`),
    INDEX `idx_users_branch` (`branch_id`),
    UNIQUE KEY `uk_tenant_username` (`tenant_id`, `username`),
    UNIQUE KEY `uk_tenant_email` (`tenant_id`, `email`),
    CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_users_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- DOMAIN 4: STUDENTS & GUARDIANS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `students` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `branch_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `admission_number` VARCHAR(50) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `full_name` VARCHAR(200) NOT NULL,
    `preferred_name` VARCHAR(100) NULL,
    `dob` DATE NULL,
    `gender` ENUM('male', 'female', 'other') NULL,
    `phone` VARCHAR(30) NULL,
    `email` VARCHAR(150) NULL,
    `address` TEXT NULL,
    `school` VARCHAR(150) NULL,
    `grade_level` VARCHAR(50) NULL,
    `photo` VARCHAR(255) NULL,
    `status` ENUM('active', 'inactive', 'suspended', 'graduated', 'withdrawn') NOT NULL DEFAULT 'active',
    `admission_date` DATE NOT NULL,
    `emergency_contact_name` VARCHAR(100) NULL,
    `emergency_contact_phone` VARCHAR(30) NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_students_tenant` (`tenant_id`),
    INDEX `idx_students_branch` (`branch_id`),
    INDEX `idx_students_status` (`status`),
    UNIQUE KEY `uk_tenant_adm_no` (`tenant_id`, `admission_number`),
    CONSTRAINT `fk_students_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_students_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`),
    CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `guardians` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `full_name` VARCHAR(200) NOT NULL,
    `relationship` VARCHAR(50) NOT NULL DEFAULT 'Parent',
    `phone` VARCHAR(30) NOT NULL,
    `email` VARCHAR(150) NULL,
    `occupation` VARCHAR(100) NULL,
    `address` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_guardians_tenant` (`tenant_id`),
    CONSTRAINT `fk_guardians_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_guardians_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `student_guardians` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `guardian_id` INT UNSIGNED NOT NULL,
    `relationship` VARCHAR(50) NOT NULL DEFAULT 'Parent',
    `is_primary` TINYINT(1) NOT NULL DEFAULT 1,
    `is_emergency` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sg_tenant` (`tenant_id`),
    UNIQUE KEY `uk_sg_student_guardian` (`student_id`, `guardian_id`),
    CONSTRAINT `fk_sg_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sg_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sg_guardian` FOREIGN KEY (`guardian_id`) REFERENCES `guardians`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- DOMAIN 5: TEACHERS & STAFF
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `teachers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `branch_id` INT UNSIGNED NULL,
    `employee_id` VARCHAR(50) NOT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `full_name` VARCHAR(200) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `address` TEXT NULL,
    `photo` VARCHAR(255) NULL,
    `qualifications` TEXT NULL,
    `specialization` VARCHAR(150) NULL,
    `join_date` DATE NOT NULL,
    `status` ENUM('active', 'inactive', 'on_leave') NOT NULL DEFAULT 'active',
    `compensation_model` ENUM('fixed_salary', 'per_session', 'per_student', 'revenue_share', 'custom') NOT NULL DEFAULT 'revenue_share',
    `default_rate` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `default_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `bank_account_details` TEXT NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_teachers_tenant` (`tenant_id`),
    UNIQUE KEY `uk_tenant_emp_id` (`tenant_id`, `employee_id`),
    CONSTRAINT `fk_teachers_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_teachers_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_teachers_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `teacher_availability` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `teacher_id` INT UNSIGNED NOT NULL,
    `day_of_week` TINYINT UNSIGNED NOT NULL, -- 0=Sunday, 1=Monday... 6=Saturday
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ta_tenant` (`tenant_id`),
    CONSTRAINT `fk_ta_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ta_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- DOMAIN 6: ACADEMIC (SUBJECTS, COURSES, BATCHES, ENROLLMENTS)
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `subjects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(50) NOT NULL,
    `grade_level` VARCHAR(50) NULL,
    `description` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_subjects_tenant` (`tenant_id`),
    UNIQUE KEY `uk_tenant_subject_code` (`tenant_id`, `code`),
    CONSTRAINT `fk_subjects_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `subject_id` INT UNSIGNED NOT NULL,
    `academic_year_id` INT UNSIGNED NULL,
    `name` VARCHAR(150) NOT NULL,
    `code` VARCHAR(50) NOT NULL,
    `description` TEXT NULL,
    `default_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_courses_tenant` (`tenant_id`),
    UNIQUE KEY `uk_tenant_course_code` (`tenant_id`, `code`),
    CONSTRAINT `fk_courses_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_courses_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`),
    CONSTRAINT `fk_courses_acad` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `batches` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `branch_id` INT UNSIGNED NOT NULL,
    `course_id` INT UNSIGNED NULL,
    `subject_id` INT UNSIGNED NOT NULL,
    `teacher_id` INT UNSIGNED NOT NULL,
    `academic_year_id` INT UNSIGNED NULL,
    `name` VARCHAR(150) NOT NULL,
    `code` VARCHAR(50) NOT NULL,
    `classroom` VARCHAR(100) NULL,
    `max_capacity` INT UNSIGNED NOT NULL DEFAULT 50,
    `monthly_fee` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `start_date` DATE NULL,
    `end_date` DATE NULL,
    `status` ENUM('active', 'completed', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_batches_tenant` (`tenant_id`),
    INDEX `idx_batches_branch` (`branch_id`),
    INDEX `idx_batches_teacher` (`teacher_id`),
    UNIQUE KEY `uk_tenant_batch_code` (`tenant_id`, `code`),
    CONSTRAINT `fk_batches_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_batches_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`),
    CONSTRAINT `fk_batches_course` FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_batches_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`),
    CONSTRAINT `fk_batches_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`),
    CONSTRAINT `fk_batches_acad` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `enrollments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `batch_id` INT UNSIGNED NOT NULL,
    `academic_year_id` INT UNSIGNED NULL,
    `enrollment_date` DATE NOT NULL,
    `fee_override` DECIMAL(12,2) NULL,
    `discount_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('active', 'suspended', 'completed', 'dropped') NOT NULL DEFAULT 'active',
    `notes` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_enr_tenant` (`tenant_id`),
    INDEX `idx_enr_student` (`student_id`),
    INDEX `idx_enr_batch` (`batch_id`),
    UNIQUE KEY `uk_tenant_student_batch` (`tenant_id`, `student_id`, `batch_id`),
    CONSTRAINT `fk_enr_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_enr_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_enr_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_enr_acad` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- DOMAIN 7: TIMETABLE & CLASS SESSIONS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `class_schedules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `branch_id` INT UNSIGNED NOT NULL,
    `batch_id` INT UNSIGNED NOT NULL,
    `teacher_id` INT UNSIGNED NOT NULL,
    `day_of_week` TINYINT UNSIGNED NOT NULL, -- 0=Sunday, 1=Monday... 6=Saturday
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `classroom` VARCHAR(100) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sched_tenant` (`tenant_id`),
    INDEX `idx_sched_batch` (`batch_id`),
    INDEX `idx_sched_teacher` (`teacher_id`),
    CONSTRAINT `fk_sched_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sched_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`),
    CONSTRAINT `fk_sched_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sched_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `class_sessions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `branch_id` INT UNSIGNED NOT NULL,
    `batch_id` INT UNSIGNED NOT NULL,
    `teacher_id` INT UNSIGNED NOT NULL,
    `schedule_id` INT UNSIGNED NULL,
    `session_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `topic` VARCHAR(255) NULL,
    `classroom` VARCHAR(100) NULL,
    `status` ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') NOT NULL DEFAULT 'scheduled',
    `cancellation_reason` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sess_tenant` (`tenant_id`),
    INDEX `idx_sess_batch_date` (`batch_id`, `session_date`),
    CONSTRAINT `fk_sess_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sess_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`),
    CONSTRAINT `fk_sess_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sess_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`),
    CONSTRAINT `fk_sess_sched` FOREIGN KEY (`schedule_id`) REFERENCES `class_schedules`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- DOMAIN 8: ATTENDANCE
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `attendance_records` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `class_session_id` INT UNSIGNED NOT NULL,
    `batch_id` INT UNSIGNED NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `status` ENUM('present', 'absent', 'late', 'excused') NOT NULL DEFAULT 'present',
    `check_in_time` TIME NULL,
    `recorded_by` INT UNSIGNED NULL,
    `notes` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_att_tenant` (`tenant_id`),
    INDEX `idx_att_session` (`class_session_id`),
    INDEX `idx_att_student` (`student_id`),
    UNIQUE KEY `uk_session_student` (`class_session_id`, `student_id`),
    CONSTRAINT `fk_att_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_att_session` FOREIGN KEY (`class_session_id`) REFERENCES `class_sessions`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_att_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_att_recorder` FOREIGN KEY (`recorded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `attendance_corrections` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `attendance_record_id` INT UNSIGNED NOT NULL,
    `previous_status` ENUM('present', 'absent', 'late', 'excused') NOT NULL,
    `new_status` ENUM('present', 'absent', 'late', 'excused') NOT NULL,
    `reason` VARCHAR(255) NOT NULL,
    `corrected_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ac_tenant` (`tenant_id`),
    CONSTRAINT `fk_ac_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ac_att` FOREIGN KEY (`attendance_record_id`) REFERENCES `attendance_records`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ac_user` FOREIGN KEY (`corrected_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- DOMAIN 9: FEES, INVOICES, PAYMENTS & ALLOCATIONS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `fee_types` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(50) NOT NULL,
    `is_recurring` TINYINT(1) NOT NULL DEFAULT 1,
    `default_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `description` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ft_tenant` (`tenant_id`),
    UNIQUE KEY `uk_tenant_fee_code` (`tenant_id`, `code`),
    CONSTRAINT `fk_ft_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `invoices` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `branch_id` INT UNSIGNED NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `invoice_number` VARCHAR(50) NOT NULL,
    `fee_period` VARCHAR(20) NOT NULL, -- e.g. '2026-11'
    `issue_date` DATE NOT NULL,
    `due_date` DATE NOT NULL,
    `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `balance_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('draft', 'issued', 'partially_paid', 'paid', 'overdue', 'cancelled') NOT NULL DEFAULT 'issued',
    `created_by` INT UNSIGNED NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_inv_tenant` (`tenant_id`),
    INDEX `idx_inv_student` (`student_id`),
    INDEX `idx_inv_status` (`status`),
    INDEX `idx_inv_period` (`fee_period`),
    UNIQUE KEY `uk_tenant_inv_no` (`tenant_id`, `invoice_number`),
    CONSTRAINT `fk_inv_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_inv_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`),
    CONSTRAINT `fk_inv_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`),
    CONSTRAINT `fk_inv_creator` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `invoice_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `invoice_id` INT UNSIGNED NOT NULL,
    `fee_type_id` INT UNSIGNED NULL,
    `batch_id` INT UNSIGNED NULL,
    `description` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `discount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `net_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ii_tenant` (`tenant_id`),
    INDEX `idx_ii_invoice` (`invoice_id`),
    CONSTRAINT `fk_ii_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ii_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ii_fee_type` FOREIGN KEY (`fee_type_id`) REFERENCES `fee_types`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ii_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `branch_id` INT UNSIGNED NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `payment_number` VARCHAR(50) NOT NULL,
    `payment_date` DATE NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `payment_method` ENUM('cash', 'bank_transfer', 'card', 'online', 'cheque') NOT NULL DEFAULT 'cash',
    `reference_number` VARCHAR(100) NULL,
    `receipt_number` VARCHAR(50) NOT NULL,
    `collected_by` INT UNSIGNED NULL,
    `notes` TEXT NULL,
    `status` ENUM('confirmed', 'refunded', 'reversed') NOT NULL DEFAULT 'confirmed',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pay_tenant` (`tenant_id`),
    INDEX `idx_pay_student` (`student_id`),
    INDEX `idx_pay_date` (`payment_date`),
    UNIQUE KEY `uk_tenant_pay_no` (`tenant_id`, `payment_number`),
    UNIQUE KEY `uk_tenant_receipt_no` (`tenant_id`, `receipt_number`),
    CONSTRAINT `fk_pay_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pay_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`),
    CONSTRAINT `fk_pay_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`),
    CONSTRAINT `fk_pay_collector` FOREIGN KEY (`collected_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payment_allocations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `payment_id` INT UNSIGNED NOT NULL,
    `invoice_id` INT UNSIGNED NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_pa_tenant` (`tenant_id`),
    INDEX `idx_pa_payment` (`payment_id`),
    INDEX `idx_pa_invoice` (`invoice_id`),
    CONSTRAINT `fk_pa_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pa_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pa_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `refunds` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `payment_id` INT UNSIGNED NOT NULL,
    `invoice_id` INT UNSIGNED NULL,
    `refund_number` VARCHAR(50) NOT NULL,
    `refund_date` DATE NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `reason` VARCHAR(255) NOT NULL,
    `authorized_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ref_tenant` (`tenant_id`),
    UNIQUE KEY `uk_tenant_refund_no` (`tenant_id`, `refund_number`),
    CONSTRAINT `fk_ref_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ref_pay` FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`),
    CONSTRAINT `fk_ref_inv` FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ref_authorizer` FOREIGN KEY (`authorized_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- DOMAIN 10: TEACHER PAYMENTS & COMPENSATION
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `teacher_payment_rules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `teacher_id` INT UNSIGNED NOT NULL,
    `batch_id` INT UNSIGNED NULL, -- NULL means default for all teacher's batches
    `model` ENUM('fixed_salary', 'per_session', 'per_student', 'revenue_share', 'custom') NOT NULL DEFAULT 'revenue_share',
    `rate` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `effective_from` DATE NOT NULL,
    `effective_to` DATE NULL,
    `notes` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tpr_tenant` (`tenant_id`),
    CONSTRAINT `fk_tpr_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tpr_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tpr_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `teacher_payment_periods` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL, -- e.g. 'November 2026'
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_closed` TINYINT(1) NOT NULL DEFAULT 0,
    `closed_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tpp_tenant` (`tenant_id`),
    CONSTRAINT `fk_tpp_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `teacher_payment_calculations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `teacher_id` INT UNSIGNED NOT NULL,
    `period_id` INT UNSIGNED NOT NULL,
    `batch_id` INT UNSIGNED NULL,
    `compensation_model` ENUM('fixed_salary', 'per_session', 'per_student', 'revenue_share', 'custom') NOT NULL,
    `total_sessions` INT UNSIGNED NOT NULL DEFAULT 0,
    `total_students` INT UNSIGNED NOT NULL DEFAULT 0,
    `eligible_revenue` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `calculated_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `adjustments` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `deductions` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `net_payable` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('draft', 'approved', 'rejected', 'paid') NOT NULL DEFAULT 'draft',
    `approved_by` INT UNSIGNED NULL,
    `approved_at` DATETIME NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_tpc_tenant` (`tenant_id`),
    CONSTRAINT `fk_tpc_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tpc_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`),
    CONSTRAINT `fk_tpc_period` FOREIGN KEY (`period_id`) REFERENCES `teacher_payment_periods`(`id`),
    CONSTRAINT `fk_tpc_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tpc_approver` FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `teacher_payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `teacher_id` INT UNSIGNED NOT NULL,
    `calculation_id` INT UNSIGNED NULL,
    `voucher_number` VARCHAR(50) NOT NULL,
    `payment_date` DATE NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `payment_method` ENUM('cash', 'bank_transfer', 'cheque') NOT NULL DEFAULT 'bank_transfer',
    `reference_number` VARCHAR(100) NULL,
    `paid_by` INT UNSIGNED NOT NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tp_tenant` (`tenant_id`),
    UNIQUE KEY `uk_tenant_voucher_no` (`tenant_id`, `voucher_number`),
    CONSTRAINT `fk_tp_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tp_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`),
    CONSTRAINT `fk_tp_calc` FOREIGN KEY (`calculation_id`) REFERENCES `teacher_payment_calculations`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_tp_payer` FOREIGN KEY (`paid_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- DOMAIN 11: EXPENSES, FINANCIAL ACCOUNTS & DOUBLE-ENTRY ACCOUNTING
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `expense_categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `code` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ec_tenant` (`tenant_id`),
    UNIQUE KEY `uk_tenant_exp_code` (`tenant_id`, `code`),
    CONSTRAINT `fk_ec_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `financial_accounts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `branch_id` INT UNSIGNED NULL,
    `account_name` VARCHAR(100) NOT NULL,
    `account_type` ENUM('cash', 'bank', 'savings', 'petty_cash') NOT NULL DEFAULT 'cash',
    `account_number` VARCHAR(50) NULL,
    `bank_name` VARCHAR(100) NULL,
    `current_balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_fa_tenant` (`tenant_id`),
    CONSTRAINT `fk_fa_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fa_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `expenses` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `branch_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    `expense_number` VARCHAR(50) NOT NULL,
    `expense_date` DATE NOT NULL,
    `payee` VARCHAR(150) NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `balance_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `payment_status` ENUM('unpaid', 'partially_paid', 'paid', 'cancelled') NOT NULL DEFAULT 'paid',
    `description` TEXT NOT NULL,
    `attachment` VARCHAR(255) NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_exp_tenant` (`tenant_id`),
    INDEX `idx_exp_date` (`expense_date`),
    UNIQUE KEY `uk_tenant_exp_no` (`tenant_id`, `expense_number`),
    CONSTRAINT `fk_exp_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_exp_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`),
    CONSTRAINT `fk_exp_cat` FOREIGN KEY (`category_id`) REFERENCES `expense_categories`(`id`),
    CONSTRAINT `fk_exp_creator` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `expense_payments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `expense_id` INT UNSIGNED NOT NULL,
    `account_id` INT UNSIGNED NULL,
    `payment_date` DATE NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `payment_method` ENUM('cash', 'bank_transfer', 'cheque', 'card') NOT NULL DEFAULT 'cash',
    `reference_number` VARCHAR(100) NULL,
    `recorded_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ep_tenant` (`tenant_id`),
    CONSTRAINT `fk_ep_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ep_exp` FOREIGN KEY (`expense_id`) REFERENCES `expenses`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ep_account` FOREIGN KEY (`account_id`) REFERENCES `financial_accounts`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ep_user` FOREIGN KEY (`recorded_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `account_transfers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `from_account_id` INT UNSIGNED NOT NULL,
    `to_account_id` INT UNSIGNED NOT NULL,
    `transfer_number` VARCHAR(50) NOT NULL,
    `transfer_date` DATE NOT NULL,
    `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `description` VARCHAR(255) NULL,
    `processed_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_at_tenant` (`tenant_id`),
    UNIQUE KEY `uk_tenant_trf_no` (`tenant_id`, `transfer_number`),
    CONSTRAINT `fk_at_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_at_from` FOREIGN KEY (`from_account_id`) REFERENCES `financial_accounts`(`id`),
    CONSTRAINT `fk_at_to` FOREIGN KEY (`to_account_id`) REFERENCES `financial_accounts`(`id`),
    CONSTRAINT `fk_at_user` FOREIGN KEY (`processed_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chart_of_accounts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `account_code` VARCHAR(20) NOT NULL,
    `account_name` VARCHAR(100) NOT NULL,
    `account_type` ENUM('asset', 'liability', 'equity', 'revenue', 'expense') NOT NULL,
    `normal_balance` ENUM('debit', 'credit') NOT NULL,
    `is_system` TINYINT(1) NOT NULL DEFAULT 1,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_coa_tenant` (`tenant_id`),
    UNIQUE KEY `uk_tenant_acc_code` (`tenant_id`, `account_code`),
    CONSTRAINT `fk_coa_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `accounting_periods` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `period_name` VARCHAR(50) NOT NULL, -- e.g. '2026-11'
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_closed` TINYINT(1) NOT NULL DEFAULT 0,
    `closed_at` DATETIME NULL,
    `closed_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ap_tenant` (`tenant_id`),
    UNIQUE KEY `uk_tenant_period` (`tenant_id`, `period_name`),
    CONSTRAINT `fk_ap_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ap_closer` FOREIGN KEY (`closed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `journal_entries` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `branch_id` INT UNSIGNED NULL,
    `journal_number` VARCHAR(50) NOT NULL,
    `entry_date` DATE NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `source_module` VARCHAR(50) NOT NULL, -- 'fee_invoice', 'payment', 'expense', 'teacher_payment', 'transfer', 'manual'
    `source_id` INT UNSIGNED NULL,
    `status` ENUM('posted', 'reversed') NOT NULL DEFAULT 'posted',
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_je_tenant` (`tenant_id`),
    INDEX `idx_je_date` (`entry_date`),
    UNIQUE KEY `uk_tenant_journal_no` (`tenant_id`, `journal_number`),
    CONSTRAINT `fk_je_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_je_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_je_creator` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `journal_lines` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `journal_entry_id` INT UNSIGNED NOT NULL,
    `account_id` INT UNSIGNED NOT NULL,
    `line_description` VARCHAR(255) NULL,
    `debit` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `credit` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_jl_tenant` (`tenant_id`),
    INDEX `idx_jl_entry` (`journal_entry_id`),
    INDEX `idx_jl_account` (`account_id`),
    CONSTRAINT `fk_jl_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_jl_entry` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_jl_account` FOREIGN KEY (`account_id`) REFERENCES `chart_of_accounts`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- DOMAIN 12: EXAMINATIONS & STUDENT PERFORMANCE
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `grading_rules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `grade_name` VARCHAR(10) NOT NULL, -- e.g. 'A', 'B', 'C', 'S', 'F'
    `min_percentage` DECIMAL(5,2) NOT NULL,
    `max_percentage` DECIMAL(5,2) NOT NULL,
    `grade_point` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    `description` VARCHAR(100) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_gr_tenant` (`tenant_id`),
    CONSTRAINT `fk_gr_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `examinations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `branch_id` INT UNSIGNED NOT NULL,
    `batch_id` INT UNSIGNED NOT NULL,
    `subject_id` INT UNSIGNED NOT NULL,
    `academic_year_id` INT UNSIGNED NULL,
    `name` VARCHAR(150) NOT NULL,
    `exam_date` DATE NOT NULL,
    `max_marks` DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    `pass_mark` DECIMAL(5,2) NOT NULL DEFAULT 40.00,
    `exam_type` ENUM('monthly', 'term', 'model', 'quiz', 'final') NOT NULL DEFAULT 'monthly',
    `status` ENUM('scheduled', 'completed', 'published') NOT NULL DEFAULT 'scheduled',
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME NULL,
    INDEX `idx_exam_tenant` (`tenant_id`),
    INDEX `idx_exam_batch` (`batch_id`),
    CONSTRAINT `fk_exam_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_exam_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`),
    CONSTRAINT `fk_exam_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_exam_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`),
    CONSTRAINT `fk_exam_acad` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_exam_creator` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `exam_marks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `examination_id` INT UNSIGNED NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `marks_obtained` DECIMAL(5,2) NULL,
    `percentage` DECIMAL(5,2) NULL,
    `grade` VARCHAR(10) NULL,
    `rank_in_batch` INT UNSIGNED NULL,
    `is_absent` TINYINT(1) NOT NULL DEFAULT 0,
    `status` ENUM('pass', 'fail', 'absent') NOT NULL DEFAULT 'pass',
    `remarks` VARCHAR(255) NULL,
    `entered_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_em_tenant` (`tenant_id`),
    INDEX `idx_em_exam` (`examination_id`),
    INDEX `idx_em_student` (`student_id`),
    UNIQUE KEY `uk_exam_student` (`examination_id`, `student_id`),
    CONSTRAINT `fk_em_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_em_exam` FOREIGN KEY (`examination_id`) REFERENCES `examinations`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_em_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_em_user` FOREIGN KEY (`entered_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- DOMAIN 13: COMMUNICATION, LEARNING MATERIALS & ASSIGNMENTS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `announcements` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `branch_id` INT UNSIGNED NULL,
    `batch_id` INT UNSIGNED NULL,
    `title` VARCHAR(200) NOT NULL,
    `content` TEXT NOT NULL,
    `target_audience` ENUM('all', 'students', 'teachers', 'parents') NOT NULL DEFAULT 'all',
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `published_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ann_tenant` (`tenant_id`),
    CONSTRAINT `fk_ann_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ann_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ann_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_ann_creator` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('info', 'success', 'warning', 'danger', 'fee', 'attendance') NOT NULL DEFAULT 'info',
    `link_url` VARCHAR(255) NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `read_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notif_tenant` (`tenant_id`),
    INDEX `idx_notif_user` (`user_id`, `is_read`),
    CONSTRAINT `fk_notif_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `learning_materials` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `batch_id` INT UNSIGNED NOT NULL,
    `subject_id` INT UNSIGNED NOT NULL,
    `teacher_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `material_type` ENUM('document', 'pdf', 'image', 'video_link', 'external_link') NOT NULL DEFAULT 'pdf',
    `file_path` VARCHAR(255) NULL,
    `file_size` INT UNSIGNED NULL,
    `external_link` VARCHAR(255) NULL,
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_lm_tenant` (`tenant_id`),
    INDEX `idx_lm_batch` (`batch_id`),
    CONSTRAINT `fk_lm_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lm_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_lm_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`),
    CONSTRAINT `fk_lm_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `assignments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `batch_id` INT UNSIGNED NOT NULL,
    `subject_id` INT UNSIGNED NOT NULL,
    `teacher_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `instructions` TEXT NOT NULL,
    `due_date` DATETIME NOT NULL,
    `max_marks` DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    `attachment_path` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_asgn_tenant` (`tenant_id`),
    INDEX `idx_asgn_batch` (`batch_id`),
    CONSTRAINT `fk_asgn_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_asgn_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_asgn_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`),
    CONSTRAINT `fk_asgn_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `student_submissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `assignment_id` INT UNSIGNED NOT NULL,
    `student_id` INT UNSIGNED NOT NULL,
    `file_path` VARCHAR(255) NULL,
    `submission_text` TEXT NULL,
    `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `marks_awarded` DECIMAL(5,2) NULL,
    `feedback` TEXT NULL,
    `graded_by` INT UNSIGNED NULL,
    `graded_at` DATETIME NULL,
    INDEX `idx_subm_tenant` (`tenant_id`),
    UNIQUE KEY `uk_assignment_student` (`assignment_id`, `student_id`),
    CONSTRAINT `fk_subm_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_subm_asgn` FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_subm_student` FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_subm_grader` FOREIGN KEY (`graded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- DOMAIN 14: AUDIT LOGS, SETTINGS & BACKUPS
-- -----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NULL,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(50) NOT NULL, -- e.g. 'login', 'create_student', 'record_payment', 'attendance_override'
    `entity_type` VARCHAR(50) NOT NULL, -- e.g. 'students', 'payments', 'users'
    `entity_id` INT UNSIGNED NULL,
    `description` VARCHAR(255) NOT NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_tenant` (`tenant_id`),
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_action` (`action`),
    INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NOT NULL,
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_set_tenant` (`tenant_id`),
    UNIQUE KEY `uk_tenant_setting` (`tenant_id`, `setting_key`),
    CONSTRAINT `fk_set_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `backup_records` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tenant_id` INT UNSIGNED NULL, -- NULL indicates full platform backup
    `filename` VARCHAR(255) NOT NULL,
    `file_size` BIGINT UNSIGNED NOT NULL,
    `backup_type` ENUM('database', 'files', 'full') NOT NULL DEFAULT 'database',
    `status` ENUM('completed', 'failed') NOT NULL DEFAULT 'completed',
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_bak_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

