-- TuitionPro ERP: Student Enquiries and Follow-Up Lifecycle Schema
-- Migration 005: Creates student_enquiries and enquiry_followups tables

CREATE TABLE IF NOT EXISTS `student_enquiries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `branch_id` INT UNSIGNED DEFAULT NULL,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `grade` VARCHAR(20) DEFAULT NULL,
    `course_interest` VARCHAR(150) DEFAULT NULL,
    `source` ENUM('walk_in', 'phone', 'website', 'social_media', 'referral', 'advertisement', 'other') DEFAULT 'walk_in',
    `status` ENUM('new', 'contacted', 'follow_up', 'admitted', 'closed', 'lost') DEFAULT 'new',
    `assigned_to` INT UNSIGNED DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `next_followup_date` DATE DEFAULT NULL,
    `converted_student_id` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_enquiries_tenant_status` (`tenant_id`, `status`),
    INDEX `idx_enquiries_followup` (`tenant_id`, `next_followup_date`),
    CONSTRAINT `fk_enquiries_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_enquiries_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_enquiries_assigned` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_enquiries_converted` FOREIGN KEY (`converted_student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `enquiry_followups` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `enquiry_id` INT UNSIGNED NOT NULL,
    `contacted_by` INT UNSIGNED NOT NULL,
    `contact_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `channel` ENUM('call', 'whatsapp', 'email', 'in_person', 'sms') DEFAULT 'call',
    `response_notes` TEXT NOT NULL,
    `outcome` ENUM('positive', 'neutral', 'unreachable', 'not_interested', 'scheduled_visit') DEFAULT 'neutral',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_followups_enquiry` (`tenant_id`, `enquiry_id`),
    CONSTRAINT `fk_followups_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_followups_enquiry` FOREIGN KEY (`enquiry_id`) REFERENCES `student_enquiries` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_followups_contacted` FOREIGN KEY (`contacted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed demonstration inquiries for active tenant (Tenant 1: Apex)
INSERT IGNORE INTO `student_enquiries` (`id`, `tenant_id`, `branch_id`, `first_name`, `last_name`, `phone`, `email`, `grade`, `course_interest`, `source`, `status`, `notes`, `next_followup_date`, `created_at`)
VALUES
    (1, 1, 1, 'Roshan', 'Jayasinghe', '0779911223', 'roshan.j@gmail.com', '12', 'A/L Combined Mathematics', 'walk_in', 'follow_up', 'Interested in weekday revision batch. Requested timetable.', CURDATE() + INTERVAL 1 DAY, NOW()),
    (2, 1, 1, 'Sanduni', 'Tharaka', '0714455667', 'sanduni.t@yahoo.com', '13', 'A/L Physics & Chemistry', 'social_media', 'new', 'Inquired via Facebook page regarding physics theory sessions.', CURDATE(), NOW()),
    (3, 1, 1, 'Kavindu', 'Perera', '0761122334', 'kavindu.p@gmail.com', '11', 'O/L Mathematics English Medium', 'referral', 'contacted', 'Spoke to mother. Arranged campus visit.', CURDATE() + INTERVAL 3 DAY, NOW());
