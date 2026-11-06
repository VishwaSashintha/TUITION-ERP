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

    /**
     * Display live institute dashboard.
     */
    public function index(Request $request): void
    {
        $tenantId = current_tenant_id();

        // 1. KPI Counts
        $studentCount = (int) $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM `students` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL",
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

        // 2. Today's Sessions & Attendance
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

        // 3. Financial Metrics
        $financials = $this->billing->getFinancialKPIs();

        // 4. Recent Payments
        $recentPayments = $this->db->query(
            "SELECT p.*, s.admission_number, s.full_name AS student_name, u.name AS cashier_name 
             FROM `payments` p 
             JOIN `students` s ON p.student_id = s.id 
             LEFT JOIN `users` u ON p.collected_by = u.id 
             WHERE p.`tenant_id` = :tid 
             ORDER BY p.`id` DESC LIMIT 5",
            ['tid' => $tenantId]
        );

        // 5. Recent Admissions
        $recentAdmissions = $this->db->query(
            "SELECT s.*, br.name AS branch_name 
             FROM `students` s 
             LEFT JOIN `branches` br ON s.branch_id = br.id 
             WHERE s.`tenant_id` = :tid AND s.`deleted_at` IS NULL 
             ORDER BY s.`id` DESC LIMIT 5",
            ['tid' => $tenantId]
        );

        $this->render('institute/dashboard', [
            'title'            => 'Institute Dashboard',
            'activeMenu'       => 'dashboard',
            'studentCount'     => $studentCount,
            'teacherCount'     => $teacherCount,
            'batchCount'       => $batchCount,
            'todaySessions'    => $todaySessions,
            'attendanceStats'  => $attendanceStats,
            'financials'       => $financials,
            'recentPayments'   => $recentPayments,
            'recentAdmissions' => $recentAdmissions,
        ]);
    }
}

