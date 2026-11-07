<?php

declare(strict_types=1);

namespace App\Controllers\Institute;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Services\BillingService;

class DashboardController extends Controller
{
    private Database $db;
    private BillingService $billing;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->billing = new BillingService();
    }

    public function index(Request $request): void
    {
        $role = auth_role() ?? 'guest';

        if (in_array($role, ['super_admin', 'support_admin'], true)) {
            $this->redirect(url('platform/dashboard'));
            return;
        }

        //Students
        if ($role === 'student') {
            $this->redirect(url('portal/student'));
            return;
        }

        if ($role === 'parent') {
            $this->redirect(url('portal/parent'));
            return;
        }

        if (in_array($role, ['receptionist', 'cashier', 'admissions_officer'], true)) {
            $this->receptionistDashboard($request);
            return;
        }

        //Teachers
        if (in_array($role, ['teacher', 'academic_coordinator'], true)) {
            $this->teacherDashboard($request);
            return;
        }

        $this->adminDashboard($request);
    }

    public function adminDashboard(Request $request): void
    {
        $tenantId = current_tenant_id();

        $studentCount = (int) $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM `students` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL",
            ['tid' => $tenantId]
        )['total'];

        $newStudentsThisMonth = (int) $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM `students` WHERE `tenant_id` = :tid AND `created_at` >= DATE_FORMAT(NOW(), '%Y-%m-01') AND `deleted_at` IS NULL",
            ['tid' => $tenantId]
        )['total'];

        $teacherCount = (int) $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM `teachers` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL",
            ['tid' => $tenantId]
        )['total'];

        $batchCount = (int) $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM `batches` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL",
            ['tid' => $tenantId]
        )['total'];

        //Attendance
        $today = date('Y-m-d');
        $todaySessions = $this->db->query(
            "SELECT cs.*, b.name AS batch_name, s.name AS subject_name, t.full_name AS teacher_name 
             FROM `class_sessions` cs 
             JOIN `batches` b ON cs.batch_id = b.id 
             JOIN `subjects` s ON b.subject_id = s.id 
             JOIN `teachers` t ON cs.teacher_id = t.id 
             WHERE cs.`tenant_id` = :tid AND cs.`session_date` = :today 
             ORDER BY cs.`start_time` ASC",
            ['tid' => $tenantId, 'today' => $today]
        );

        $attendanceStats = $this->db->queryOne(
            "SELECT 
                COUNT(*) AS total_marked,
                SUM(CASE WHEN `status` = 'present' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN `status` = 'late' THEN 1 ELSE 0 END) AS late_count,
                SUM(CASE WHEN `status` = 'absent' THEN 1 ELSE 0 END) AS absent_count 
             FROM `attendance_records` 
             WHERE `tenant_id` = :tid AND DATE(`created_at`) = :today",
            ['tid' => $tenantId, 'today' => $today]
        );

        $totalMarked = (int)($attendanceStats['total_marked'] ?? 0);
        $presentCount = (int)($attendanceStats['present_count'] ?? 0);
        $attendanceRate = $totalMarked > 0 ? round(($presentCount / $totalMarked) * 100, 1) : 100.0;

        $financials = $this->billing->getFinancialKPIs();

        $bankBalanceRow = $this->db->queryOne(
            "SELECT COALESCE(SUM(current_balance), 0) AS total_bank FROM `financial_accounts` WHERE `tenant_id` = :tid AND `is_active` = 1",
            ['tid' => $tenantId]
        );
        $bankBalance = (float)($bankBalanceRow['total_bank'] ?? 0);

        $recentPayments = $this->db->query(
            "SELECT p.*, s.admission_number, s.full_name AS student_name, u.name AS cashier_name 
             FROM `payments` p 
             JOIN `students` s ON p.student_id = s.id 
             LEFT JOIN `users` u ON p.collected_by = u.id 
             WHERE p.`tenant_id` = :tid 
             ORDER BY p.`id` DESC LIMIT 6",
            ['tid' => $tenantId]
        );

        $recentAdmissions = $this->db->query(
            "SELECT s.*, br.name AS branch_name 
             FROM `students` s 
             LEFT JOIN `branches` br ON s.branch_id = br.id 
             WHERE s.`tenant_id` = :tid AND s.`deleted_at` IS NULL 
             ORDER BY s.`id` DESC LIMIT 6",
            ['tid' => $tenantId]
        );

        $defaulters = $this->db->query(
            "SELECT i.*, s.full_name AS student_name, s.admission_number, s.phone AS student_phone
             FROM `invoices` i
             JOIN `students` s ON i.student_id = s.id
             WHERE i.`tenant_id` = :tid AND i.`status` IN ('issued', 'partially_paid') AND i.`deleted_at` IS NULL
             ORDER BY i.`balance_amount` DESC LIMIT 5",
            ['tid' => $tenantId]
        );

        $recentActivities = $this->db->query(
            "SELECT al.*, COALESCE(u.name, 'Staff') AS user_name 
             FROM `audit_logs` al 
             LEFT JOIN `users` u ON al.user_id = u.id 
             WHERE al.`tenant_id` = :tid 
             ORDER BY al.`id` DESC LIMIT 6",
            ['tid' => $tenantId]
        );

        $this->render('institute/dashboard', [
            'title'                => 'Executive Dashboard',
            'activeMenu'           => 'dashboard',
            'studentCount'         => $studentCount,
            'newStudentsThisMonth' => $newStudentsThisMonth,
            'teacherCount'         => $teacherCount,
            'batchCount'           => $batchCount,
            'todaySessions'        => $todaySessions,
            'attendanceStats'      => $attendanceStats,
            'attendanceRate'       => $attendanceRate,
            'financials'           => $financials,
            'bankBalance'          => $bankBalance,
            'recentPayments'       => $recentPayments,
            'recentAdmissions'     => $recentAdmissions,
            'defaulters'           => $defaulters,
            'recentActivities'     => $recentActivities,
        ]);
    }

    public function receptionistDashboard(Request $request): void
    {
        $tenantId = current_tenant_id();
        $today = date('Y-m-d');

        //Students
        $studentCount = (int) $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM `students` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL",
            ['tid' => $tenantId]
        )['total'];

        $todayAdmissionsCount = (int) $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM `students` WHERE `tenant_id` = :tid AND DATE(`created_at`) = :today AND `deleted_at` IS NULL",
            ['tid' => $tenantId, 'today' => $today]
        )['total'];

        //Attendance
        $attendanceStats = $this->db->queryOne(
            "SELECT 
                COUNT(*) AS total_marked,
                SUM(CASE WHEN `status` = 'present' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN `status` = 'late' THEN 1 ELSE 0 END) AS late_count,
                SUM(CASE WHEN `status` = 'absent' THEN 1 ELSE 0 END) AS absent_count 
             FROM `attendance_records` 
             WHERE `tenant_id` = :tid AND DATE(`created_at`) = :today",
            ['tid' => $tenantId, 'today' => $today]
        );

        $totalMarked = (int)($attendanceStats['total_marked'] ?? 0);
        $presentCount = (int)($attendanceStats['present_count'] ?? 0);
        $attendanceRate = $totalMarked > 0 ? round(($presentCount / $totalMarked) * 100, 1) : 100.0;

        //Students
        $pendingFees = $this->db->queryOne(
            "SELECT COUNT(*) AS pending_count, COALESCE(SUM(balance_amount), 0) AS pending_total 
             FROM `invoices` 
             WHERE `tenant_id` = :tid AND `status` IN ('issued', 'partially_paid') AND `deleted_at` IS NULL",
            ['tid' => $tenantId]
        );

        $todayCollections = (float) ($this->db->queryOne(
            "SELECT COALESCE(SUM(amount), 0) AS total_collected 
             FROM `payments` 
             WHERE `tenant_id` = :tid AND `payment_date` = :today",
            ['tid' => $tenantId, 'today' => $today]
        )['total_collected'] ?? 0);

        $todaySessions = $this->db->query(
            "SELECT cs.*, b.name AS batch_name, s.name AS subject_name, t.full_name AS teacher_name 
             FROM `class_sessions` cs 
             JOIN `batches` b ON cs.batch_id = b.id 
             JOIN `subjects` s ON b.subject_id = s.id 
             JOIN `teachers` t ON cs.teacher_id = t.id 
             WHERE cs.`tenant_id` = :tid AND cs.`session_date` = :today 
             ORDER BY cs.`start_time` ASC",
            ['tid' => $tenantId, 'today' => $today]
        );

        //Students
        $recentAdmissions = $this->db->query(
            "SELECT s.*, br.name AS branch_name 
             FROM `students` s 
             LEFT JOIN `branches` br ON s.branch_id = br.id 
             WHERE s.`tenant_id` = :tid AND s.`deleted_at` IS NULL 
             ORDER BY s.`id` DESC LIMIT 6",
            ['tid' => $tenantId]
        );

        $recentPayments = $this->db->query(
            "SELECT p.*, s.admission_number, s.full_name AS student_name, u.name AS cashier_name 
             FROM `payments` p 
             JOIN `students` s ON p.student_id = s.id 
             LEFT JOIN `users` u ON p.collected_by = u.id 
             WHERE p.`tenant_id` = :tid 
             ORDER BY p.`id` DESC LIMIT 6",
            ['tid' => $tenantId]
        );

        //Students
        $followUpStudents = $this->db->query(
            "SELECT i.id AS invoice_id, i.invoice_number, i.balance_amount, i.due_date,
                    s.id AS student_id, s.full_name AS student_name, s.admission_number, s.phone
             FROM `invoices` i
             JOIN `students` s ON i.student_id = s.id
             WHERE i.`tenant_id` = :tid AND i.`status` IN ('issued', 'partially_paid') AND i.`deleted_at` IS NULL
             ORDER BY i.`due_date` ASC LIMIT 6",
            ['tid' => $tenantId]
        );

        //Students
        $enquiryStats = $this->db->queryOne(
            "SELECT 
                COUNT(*) AS total_enquiries,
                SUM(CASE WHEN `status` = 'new' THEN 1 ELSE 0 END) AS new_enquiries,
                SUM(CASE WHEN `next_followup_date` = :today AND `status` NOT IN ('admitted', 'closed', 'lost') THEN 1 ELSE 0 END) AS followups_due_today
             FROM `student_enquiries` 
             WHERE `tenant_id` = :tid",
            ['tid' => $tenantId, 'today' => $today]
        );

        $announcements = $this->db->query(
            "SELECT * FROM `announcements` 
             WHERE `tenant_id` = :tid AND `is_published` = 1 
             ORDER BY `published_at` DESC LIMIT 4",
            ['tid' => $tenantId]
        );

        $this->render('institute/receptionist_dashboard', [
            'title'                => 'Front-Desk Reception Dashboard',
            'activeMenu'           => 'dashboard',
            'studentCount'         => $studentCount,
            'todayAdmissionsCount' => $todayAdmissionsCount,
            'attendanceStats'      => $attendanceStats,
            'attendanceRate'       => $attendanceRate,
            'pendingFees'          => $pendingFees,
            'todayCollections'     => $todayCollections,
            'todaySessions'        => $todaySessions,
            'recentAdmissions'     => $recentAdmissions,
            'recentPayments'       => $recentPayments,
            'followUpStudents'     => $followUpStudents,
            'enquiryStats'         => $enquiryStats,
            'announcements'        => $announcements,
        ]);
    }

    public function teacherDashboard(Request $request): void
    {
        $tenantId = current_tenant_id();
        $user = auth_user();
        $today = date('Y-m-d');

        //Teachers
        $teacher = $this->db->queryOne(
            "SELECT * FROM `teachers` 
             WHERE `tenant_id` = :tid AND (`user_id` = :uid OR `email` = :email) AND `deleted_at` IS NULL LIMIT 1",
            ['tid' => $tenantId, 'uid' => $user['id'], 'email' => $user['email']]
        );

        if (!$teacher) {
            $teacher = $this->db->queryOne(
                "SELECT * FROM `teachers` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL ORDER BY `id` ASC LIMIT 1",
                ['tid' => $tenantId]
            );
        }

        $teacherId = $teacher ? (int) $teacher['id'] : 0;

        $myBatches = $this->db->query(
            "SELECT b.*, s.name AS subject_name, 
                    (SELECT COUNT(*) FROM `enrollments` e WHERE e.batch_id = b.id AND e.status = 'active') AS enrolled_count
             FROM `batches` b 
             JOIN `subjects` s ON b.subject_id = s.id 
             WHERE b.`tenant_id` = :tid AND b.`teacher_id` = :teacher_id AND b.`status` = 'active' AND b.`deleted_at` IS NULL",
            ['tid' => $tenantId, 'teacher_id' => $teacherId]
        );

        //Students
        $myStudentCount = (int) $this->db->queryOne(
            "SELECT COUNT(DISTINCT e.student_id) AS c 
             FROM `enrollments` e 
             JOIN `batches` b ON e.batch_id = b.id 
             WHERE b.`tenant_id` = :tid AND b.`teacher_id` = :teacher_id AND e.`status` = 'active'",
            ['tid' => $tenantId, 'teacher_id' => $teacherId]
        )['c'];

        //Teachers
        $todaySessions = $this->db->query(
            "SELECT cs.*, b.name AS batch_name, s.name AS subject_name,
                    (SELECT COUNT(*) FROM `attendance_records` ar WHERE ar.class_session_id = cs.id) AS attendance_count
             FROM `class_sessions` cs 
             JOIN `batches` b ON cs.batch_id = b.id 
             JOIN `subjects` s ON b.subject_id = s.id 
             WHERE cs.`tenant_id` = :tid AND cs.`teacher_id` = :teacher_id AND cs.`session_date` = :today 
             ORDER BY cs.`start_time` ASC",
            ['tid' => $tenantId, 'teacher_id' => $teacherId, 'today' => $today]
        );

        //Attendance
        $pendingAttendance = $this->db->query(
            "SELECT cs.*, b.name AS batch_name, s.name AS subject_name
             FROM `class_sessions` cs
             JOIN `batches` b ON cs.batch_id = b.id
             JOIN `subjects` s ON b.subject_id = s.id
             WHERE cs.`tenant_id` = :tid AND cs.`teacher_id` = :teacher_id 
             AND cs.`session_date` <= :today 
             AND (SELECT COUNT(*) FROM `attendance_records` ar WHERE ar.class_session_id = cs.id) = 0
             ORDER BY cs.`session_date` DESC, cs.`start_time` ASC LIMIT 5",
            ['tid' => $tenantId, 'teacher_id' => $teacherId, 'today' => $today]
        );

        $assignments = $this->db->query(
            "SELECT a.*, b.name AS batch_name, s.name AS subject_name,
                    (SELECT COUNT(*) FROM `student_submissions` ss WHERE ss.assignment_id = a.id) AS submission_count
             FROM `assignments` a
             JOIN `batches` b ON a.batch_id = b.id
             JOIN `subjects` s ON a.subject_id = s.id
             WHERE a.`tenant_id` = :tid AND (a.`teacher_id` = :teacher_id OR b.`teacher_id` = :teacher_id2)
             ORDER BY a.`due_date` DESC LIMIT 6",
            ['tid' => $tenantId, 'teacher_id' => $teacherId, 'teacher_id2' => $teacherId]
        );

        $examinations = $this->db->query(
            "SELECT ex.*, b.name AS batch_name, s.name AS subject_name,
                    (SELECT COUNT(*) FROM `exam_marks` em WHERE em.examination_id = ex.id) AS marked_count
             FROM `examinations` ex
             JOIN `batches` b ON ex.batch_id = b.id
             JOIN `subjects` s ON ex.subject_id = s.id
             WHERE ex.`tenant_id` = :tid AND (b.`teacher_id` = :teacher_id OR s.id IN (SELECT subject_id FROM `batches` WHERE teacher_id = :teacher_id2))
             ORDER BY ex.`exam_date` DESC LIMIT 5",
            ['tid' => $tenantId, 'teacher_id' => $teacherId, 'teacher_id2' => $teacherId]
        );

        $announcements = $this->db->query(
            "SELECT * FROM `announcements` 
             WHERE `tenant_id` = :tid AND `target_audience` IN ('all', 'teachers') AND `is_published` = 1 
             ORDER BY `published_at` DESC LIMIT 4",
            ['tid' => $tenantId]
        );

        $this->render('institute/teacher_dashboard', [
            'title'             => 'Teacher Academic Dashboard',
            'activeMenu'        => 'dashboard',
            'teacher'           => $teacher,
            'myBatches'         => $myBatches,
            'myStudentCount'    => $myStudentCount,
            'todaySessions'     => $todaySessions,
            'pendingAttendance' => $pendingAttendance,
            'assignments'       => $assignments,
            'examinations'      => $examinations,
            'announcements'     => $announcements,
        ]);
    }
}
