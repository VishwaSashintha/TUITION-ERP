<?php

declare(strict_types=1);

namespace App\Controllers\TeacherPayments;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Services\AuditService;
use App\Services\TeacherPaymentService;

class TeacherPaymentController extends Controller
{
    private TeacherPaymentService $paymentService;
    private AuditService $audit;
    private Database $db;

    public function __construct()
    {
        $this->paymentService = new TeacherPaymentService();
        $this->audit          = new AuditService();
        $this->db             = Database::getInstance();
    }

    /**
     * Dashboard & list of teacher payments and calculations.
     */
    public function index(Request $request): void
    {
        $tenantId = current_tenant_id();

        $periods = $this->db->query(
            "SELECT * FROM `teacher_payment_periods` WHERE `tenant_id` = :tid ORDER BY `start_date` DESC",
            ['tid' => $tenantId]
        );

        $calculations = $this->db->query(
            "SELECT tpc.*, t.full_name AS teacher_name, t.phone AS teacher_phone, tpp.name AS period_name, b.name AS batch_name
             FROM `teacher_payment_calculations` tpc
             JOIN `teachers` t ON tpc.teacher_id = t.id
             JOIN `teacher_payment_periods` tpp ON tpc.period_id = tpp.id
             LEFT JOIN `batches` b ON tpc.batch_id = b.id
             WHERE tpc.`tenant_id` = :tid
             ORDER BY tpc.`id` DESC LIMIT 100",
            ['tid' => $tenantId]
        );

        $vouchers = $this->db->query(
            "SELECT tp.*, t.full_name AS teacher_name, tpc.compensation_model, u.name AS paid_by_name
             FROM `teacher_payments` tp
             JOIN `teachers` t ON tp.teacher_id = t.id
             LEFT JOIN `teacher_payment_calculations` tpc ON tp.calculation_id = tpc.id
             LEFT JOIN `users` u ON tp.paid_by = u.id
             WHERE tp.`tenant_id` = :tid
             ORDER BY tp.`id` DESC LIMIT 50",
            ['tid' => $tenantId]
        );

        // Summary KPI
        $summary = $this->db->queryOne(
            "SELECT 
                COALESCE(SUM(amount), 0.00) AS total_disbursed,
                COUNT(*) AS total_vouchers
             FROM `teacher_payments`
             WHERE `tenant_id` = :tid",
            ['tid' => $tenantId]
        );

        $this->render('teacher_payments/index', [
            'title'        => 'Teacher Payroll & Compensations',
            'activeMenu'   => 'teacher_payments',
            'periods'      => $periods,
            'calculations' => $calculations,
            'vouchers'     => $vouchers,
            'summary'      => $summary,
        ]);
    }

    /**
     * Calculation preview generator page.
     */
    public function calculate(Request $request): void
    {
        $tenantId = current_tenant_id();
        $periodId = (int) $request->query('period_id');

        $periods = $this->db->query(
            "SELECT * FROM `teacher_payment_periods` WHERE `tenant_id` = :tid AND `is_closed` = 0 ORDER BY `start_date` DESC",
            ['tid' => $tenantId]
        );

        if ($periodId <= 0 && !empty($periods)) {
            $periodId = (int) $periods[0]['id'];
        }

        $teachers = $this->db->query(
            "SELECT * FROM `teachers` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL ORDER BY full_name ASC",
            ['tid' => $tenantId]
        );

        $calculatedList = [];
        if ($periodId > 0) {
            foreach ($teachers as $t) {
                try {
                    $calc = $this->paymentService->calculateCompensation((int)$t['id'], $periodId);
                    $calc['teacher_name'] = $t['full_name'];
                    $calculatedList[] = $calc;
                } catch (\Throwable $e) {
                    // skip or log
                }
            }
        }

        $this->render('teacher_payments/calculate', [
            'title'          => 'Generate Teacher Payroll',
            'activeMenu'     => 'teacher_payments',
            'periods'        => $periods,
            'periodId'       => $periodId,
            'calculatedList' => $calculatedList,
        ]);
    }

    /**
     * Save and approve calculated compensation.
     */
    public function saveCalculation(Request $request): void
    {
        $teacherId = (int) $request->input('teacher_id');
        $periodId  = (int) $request->input('period_id');

        try {
            $calcData = $this->paymentService->calculateCompensation($teacherId, $periodId);
            $calcData['adjustments'] = (float) $request->input('adjustments', 0.00);
            $calcData['deductions']  = (float) $request->input('deductions', 0.00);
            $calcData['net_payable'] = round($calcData['calculated_amount'] + $calcData['adjustments'] - $calcData['deductions'], 2);

            $id = $this->paymentService->saveAndApproveCalculation($calcData, true);
            $this->redirect(url("teacher-payments/statement?id={$id}"), 'success', 'Teacher compensation calculation approved.');
        } catch (\Throwable $e) {
            $this->back('error', 'Failed to approve calculation: ' . $e->getMessage());
        }
    }

    /**
     * Payslip & Statement view.
     */
    public function statement(Request $request): void
    {
        $id = (int) $request->query('id');
        $tenantId = current_tenant_id();

        $calc = $this->db->queryOne(
            "SELECT tpc.*, t.full_name AS teacher_name, t.phone, t.email, t.bank_name, t.bank_account_name, t.bank_account_number, tpp.name AS period_name, tpp.start_date, tpp.end_date, b.name AS batch_name
             FROM `teacher_payment_calculations` tpc
             JOIN `teachers` t ON tpc.teacher_id = t.id
             JOIN `teacher_payment_periods` tpp ON tpc.period_id = tpp.id
             LEFT JOIN `batches` b ON tpc.batch_id = b.id
             WHERE tpc.`id` = :id AND tpc.`tenant_id` = :tid",
            ['id' => $id, 'tid' => $tenantId]
        );

        if (!$calc) {
            $this->redirect(url('teacher-payments'), 'error', 'Calculation not found.');
            return;
        }

        $voucher = $this->db->queryOne(
            "SELECT * FROM `teacher_payments` WHERE `calculation_id` = :cid AND `tenant_id` = :tid",
            ['cid' => $id, 'tid' => $tenantId]
        );

        $tenant = $this->db->queryOne("SELECT name, email, phone FROM `tenants` WHERE `id` = :tid", ['tid' => $tenantId]);

        $this->render('teacher_payments/statement', [
            'title'      => 'Payslip — ' . $calc['teacher_name'],
            'activeMenu' => 'teacher_payments',
            'calc'       => $calc,
            'voucher'    => $voucher,
            'tenant'     => $tenant,
        ]);
    }

    /**
     * Disburse payment voucher.
     */
    public function disburse(Request $request): void
    {
        $calcId        = (int) $request->input('calculation_id');
        $amount        = (float) $request->input('amount');
        $paymentMethod = (string) $request->input('payment_method', 'bank_transfer');
        $reference     = $request->input('reference_number');
        $notes         = $request->input('notes');
        $tenantId      = current_tenant_id();

        $calc = $this->db->queryOne(
            "SELECT * FROM `teacher_payment_calculations` WHERE `id` = :id AND `tenant_id` = :tid",
            ['id' => $calcId, 'tid' => $tenantId]
        );

        if (!$calc) {
            $this->back('error', 'Calculation not found.');
            return;
        }

        try {
            $paymentId = $this->paymentService->disbursePayment(
                (int) $calc['teacher_id'],
                $calcId,
                $amount,
                $paymentMethod,
                $reference,
                $notes
            );

            $this->redirect(url("teacher-payments/statement?id={$calcId}"), 'success', 'Payment voucher successfully disbursed.');
        } catch (\Throwable $e) {
            $this->back('error', 'Disbursement failed: ' . $e->getMessage());
        }
    }
}

