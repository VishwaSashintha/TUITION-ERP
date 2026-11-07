<?php

declare(strict_types=1);

namespace App\Controllers\Reports;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Services\BillingService;
use App\Services\AccountingService;

class ReportController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(Request $request, Response $response): void
    {
        $tenantId = current_tenant_id();

        $kpis = [
            'total_students'  => (int)($this->db->queryOne(
                "SELECT COUNT(*) AS c FROM `students` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL",
                ['tid' => $tenantId]
            )['c'] ?? 0),
            'total_collected' => (float)($this->db->queryOne(
                "SELECT COALESCE(SUM(amount),0) AS c FROM `payments` WHERE `tenant_id` = :tid AND `status` = 'confirmed'",
                ['tid' => $tenantId]
            )['c'] ?? 0),
            'outstanding' => (float)($this->db->queryOne(
                "SELECT COALESCE(SUM(balance_amount),0) AS c FROM `invoices` WHERE `tenant_id` = :tid AND `status` IN ('issued','partially_paid','overdue') AND `deleted_at` IS NULL",
                ['tid' => $tenantId]
            )['c'] ?? 0),
            'total_expenses' => (float)($this->db->queryOne(
                "SELECT COALESCE(SUM(amount),0) AS c FROM `expenses` WHERE `tenant_id` = :tid AND `payment_status` != 'cancelled' AND `deleted_at` IS NULL",
                ['tid' => $tenantId]
            )['c'] ?? 0),
        ];

        $this->view('reports/index', compact('kpis'));
    }

    public function attendance(Request $request, Response $response): void
    {
        $tenantId = current_tenant_id();

        $batchId   = (int)($request->get('batch_id', 0));
        $month     = $request->get('month', date('Y-m'));
        $branchId  = (int)($request->get('branch_id', 0));

        [$yearStr, $monthStr] = explode('-', $month . '-01');
        $startDate = "$yearStr-$monthStr-01";
        $endDate   = date('Y-m-t', strtotime($startDate));

        //Query
        $batches = $this->db->query(
            "SELECT id, name FROM `batches` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL ORDER BY name",
            ['tid' => $tenantId]
        );

        //Query
        $branches = $this->db->query(
            "SELECT id, name FROM `branches` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL ORDER BY name",
            ['tid' => $tenantId]
        );

        //Attendance
        $params = ['tid' => $tenantId, 'start' => $startDate, 'end' => $endDate];
        $batchFilter = '';
        if ($batchId > 0) {
            $batchFilter = ' AND ar.batch_id = :bid';
            $params['bid'] = $batchId;
        }

        $rows = $this->db->query(
            "SELECT s.admission_number, s.full_name,
                    COUNT(ar.id) AS total_sessions,
                    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) AS present_count,
                    SUM(CASE WHEN ar.status = 'absent'  THEN 1 ELSE 0 END) AS absent_count,
                    SUM(CASE WHEN ar.status = 'late'    THEN 1 ELSE 0 END) AS late_count,
                    ROUND(SUM(CASE WHEN ar.status IN ('present','late') THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(ar.id),0), 1) AS attendance_pct
             FROM `attendance_records` ar
             JOIN `students` s ON ar.student_id = s.id
             WHERE ar.`tenant_id` = :tid
               AND ar.`created_at` BETWEEN :start AND :end
               {$batchFilter}
             GROUP BY s.id, s.admission_number, s.full_name
             ORDER BY attendance_pct ASC, s.full_name ASC",
            $params
        );

        $totalSessions = array_sum(array_column($rows, 'total_sessions'));
        $totalPresent  = array_sum(array_column($rows, 'present_count'));
        $overallPct    = $totalSessions > 0 ? round($totalPresent * 100.0 / $totalSessions, 1) : 0;

        $this->view('reports/attendance', compact(
            'rows', 'batches', 'branches', 'batchId', 'branchId', 'month',
            'startDate', 'endDate', 'totalSessions', 'totalPresent', 'overallPct'
        ));
    }

    public function feeCollection(Request $request, Response $response): void
    {
        $tenantId  = current_tenant_id();
        $month     = $request->get('month', date('Y-m'));
        $branchId  = (int)($request->get('branch_id', 0));

        [$yearStr, $monthStr] = explode('-', $month . '-01');
        $startDate = "$yearStr-$monthStr-01";
        $endDate   = date('Y-m-t', strtotime($startDate));

        $branches = $this->db->query(
            "SELECT id, name FROM `branches` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL ORDER BY name",
            ['tid' => $tenantId]
        );

        $branchFilter = '';
        $params = ['tid' => $tenantId, 'start' => $startDate, 'end' => $endDate];
        if ($branchId > 0) {
            $branchFilter = ' AND p.branch_id = :bid';
            $params['bid'] = $branchId;
        }

        $dailyRows = $this->db->query(
            "SELECT DATE(p.payment_date) AS pay_date,
                    COUNT(p.id) AS payment_count,
                    SUM(p.amount) AS daily_total,
                    SUM(CASE WHEN p.payment_method = 'cash'          THEN p.amount ELSE 0 END) AS cash_total,
                    SUM(CASE WHEN p.payment_method = 'bank_transfer' THEN p.amount ELSE 0 END) AS bank_total,
                    SUM(CASE WHEN p.payment_method = 'cheque'        THEN p.amount ELSE 0 END) AS cheque_total
             FROM `payments` p
             WHERE p.`tenant_id` = :tid AND p.`status` = 'confirmed'
               AND p.`payment_date` BETWEEN :start AND :end
               {$branchFilter}
             GROUP BY DATE(p.payment_date)
             ORDER BY pay_date ASC",
            $params
        );

        //Students
        $studentRows = $this->db->query(
            "SELECT s.admission_number, s.full_name,
                    SUM(p.amount) AS paid_total,
                    p.payment_method,
                    COUNT(p.id) AS payment_count
             FROM `payments` p
             JOIN `students` s ON p.student_id = s.id
             WHERE p.`tenant_id` = :tid AND p.`status` = 'confirmed'
               AND p.`payment_date` BETWEEN :start AND :end
               {$branchFilter}
             GROUP BY s.id, s.admission_number, s.full_name, p.payment_method
             ORDER BY paid_total DESC",
            $params
        );

        $grandTotal = array_sum(array_column($dailyRows, 'daily_total'));
        $cashTotal  = array_sum(array_column($dailyRows, 'cash_total'));
        $bankTotal  = array_sum(array_column($dailyRows, 'bank_total'));

        $this->view('reports/fee_collection', compact(
            'dailyRows', 'studentRows', 'branches', 'branchId', 'month',
            'grandTotal', 'cashTotal', 'bankTotal', 'startDate', 'endDate'
        ));
    }

    public function outstanding(Request $request, Response $response): void
    {
        $tenantId = current_tenant_id();
        $branchId = (int)($request->get('branch_id', 0));
        $status   = $request->get('status', 'all');

        $branches = $this->db->query(
            "SELECT id, name FROM `branches` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL ORDER BY name",
            ['tid' => $tenantId]
        );

        $params = ['tid' => $tenantId];
        $filters = ["inv.`deleted_at` IS NULL", "inv.`tenant_id` = :tid"];

        if ($status === 'overdue') {
            $filters[] = "inv.`status` IN ('overdue','issued','partially_paid') AND inv.`due_date` < CURDATE()";
        } elseif ($status !== 'all') {
            $filters[] = "inv.`status` = :status";
            $params['status'] = $status;
        } else {
            $filters[] = "inv.`status` IN ('issued','partially_paid','overdue')";
        }

        if ($branchId > 0) {
            $filters[] = 'inv.`branch_id` = :bid';
            $params['bid'] = $branchId;
        }

        $where = implode(' AND ', $filters);

        $rows = $this->db->query(
            "SELECT s.admission_number, s.full_name, s.phone,
                    inv.invoice_number, inv.fee_period, inv.issue_date, inv.due_date,
                    inv.total_amount, inv.paid_amount, inv.balance_amount, inv.status,
                    DATEDIFF(CURDATE(), inv.due_date) AS days_overdue
             FROM `invoices` inv
             JOIN `students` s ON inv.student_id = s.id
             WHERE {$where}
             ORDER BY days_overdue DESC, inv.balance_amount DESC",
            $params
        );

        $totalOutstanding = array_sum(array_column($rows, 'balance_amount'));
        $overdueCount     = count(array_filter($rows, fn($r) => $r['days_overdue'] > 0));

        $this->view('reports/outstanding', compact(
            'rows', 'branches', 'branchId', 'status',
            'totalOutstanding', 'overdueCount'
        ));
    }

    public function teacherPayments(Request $request, Response $response): void
    {
        $tenantId = current_tenant_id();
        $month    = $request->get('month', date('Y-m'));

        [$yearStr, $monthStr] = explode('-', $month . '-01');
        $startDate = "$yearStr-$monthStr-01";
        $endDate   = date('Y-m-t', strtotime($startDate));

        $rows = $this->db->query(
            "SELECT t.full_name AS teacher_name, t.employee_id,
                    t.compensation_model,
                    COALESCE(SUM(tp.amount), 0) AS total_paid,
                    COUNT(tp.id) AS payment_count
             FROM `teachers` t
             LEFT JOIN `teacher_payments` tp ON t.id = tp.teacher_id
               AND tp.`tenant_id` = :tid
               AND tp.`payment_date` BETWEEN :start AND :end
             WHERE t.`tenant_id` = :tid2 AND t.`deleted_at` IS NULL AND t.`status` = 'active'
             GROUP BY t.id, t.full_name, t.employee_id, t.compensation_model
             ORDER BY total_paid DESC",
            ['tid' => $tenantId, 'start' => $startDate, 'end' => $endDate, 'tid2' => $tenantId]
        );

        $grandTotal = array_sum(array_column($rows, 'total_paid'));

        $this->view('reports/teacher_payments', compact('rows', 'month', 'grandTotal', 'startDate', 'endDate'));
    }

    public function expenses(Request $request, Response $response): void
    {
        $tenantId = current_tenant_id();
        $month    = $request->get('month', date('Y-m'));
        $branchId = (int)($request->get('branch_id', 0));

        [$yearStr, $monthStr] = explode('-', $month . '-01');
        $startDate = "$yearStr-$monthStr-01";
        $endDate   = date('Y-m-t', strtotime($startDate));

        $branches = $this->db->query(
            "SELECT id, name FROM `branches` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL ORDER BY name",
            ['tid' => $tenantId]
        );

        $params = ['tid' => $tenantId, 'start' => $startDate, 'end' => $endDate];
        $branchFilter = '';
        if ($branchId > 0) {
            $branchFilter = ' AND e.branch_id = :bid';
            $params['bid'] = $branchId;
        }

        $byCat = $this->db->query(
            "SELECT ec.name AS category, SUM(e.amount) AS total, COUNT(e.id) AS count
             FROM `expenses` e
             JOIN `expense_categories` ec ON e.category_id = ec.id
             WHERE e.`tenant_id` = :tid
               AND e.`expense_date` BETWEEN :start AND :end
               AND e.`payment_status` != 'cancelled'
               AND e.`deleted_at` IS NULL
               {$branchFilter}
             GROUP BY ec.id, ec.name
             ORDER BY total DESC",
            $params
        );

        $rows = $this->db->query(
            "SELECT e.expense_number, e.expense_date, ec.name AS category,
                    e.payee, e.description, e.amount, e.payment_status
             FROM `expenses` e
             JOIN `expense_categories` ec ON e.category_id = ec.id
             WHERE e.`tenant_id` = :tid
               AND e.`expense_date` BETWEEN :start AND :end
               AND e.`deleted_at` IS NULL
               {$branchFilter}
             ORDER BY e.expense_date DESC",
            $params
        );

        $grandTotal = array_sum(array_column($byCat, 'total'));

        $this->view('reports/expenses', compact(
            'byCat', 'rows', 'branches', 'branchId', 'month',
            'grandTotal', 'startDate', 'endDate'
        ));
    }

    public function studentPerformance(Request $request, Response $response): void
    {
        $tenantId = current_tenant_id();
        $batchId  = (int)($request->get('batch_id', 0));

        $batches = $this->db->query(
            "SELECT id, name FROM `batches` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL ORDER BY name",
            ['tid' => $tenantId]
        );

        $rows = [];
        if ($batchId > 0) {
            $rows = $this->db->query(
                "SELECT s.admission_number, s.full_name,
                        COUNT(DISTINCT em.examination_id) AS exams_taken,
                        ROUND(AVG(em.percentage), 1) AS avg_percentage,
                        MAX(em.percentage) AS best_score,
                        MIN(em.percentage) AS worst_score,
                        SUM(CASE WHEN em.status = 'pass' THEN 1 ELSE 0 END) AS pass_count,
                        SUM(CASE WHEN em.status = 'fail' THEN 1 ELSE 0 END) AS fail_count
                 FROM `exam_marks` em
                 JOIN `examinations` ex ON em.examination_id = ex.id
                 JOIN `students` s ON em.student_id = s.id
                 WHERE em.`tenant_id` = :tid AND ex.`batch_id` = :bid
                 GROUP BY s.id, s.admission_number, s.full_name
                 ORDER BY avg_percentage DESC",
                ['tid' => $tenantId, 'bid' => $batchId]
            );
        }

        $this->view('reports/student_performance', compact('batches', 'batchId', 'rows'));
    }
}

