<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\TeacherPaymentCalculation;
use App\Models\TeacherPayment;

class TeacherPaymentService
{
    private Database $db;
    private AuditService $audit;
    private TeacherPaymentCalculation $calcModel;
    private TeacherPayment $paymentModel;

    public function __construct()
    {
        $this->db           = Database::getInstance();
        $this->audit        = new AuditService();
        $this->calcModel    = new TeacherPaymentCalculation();
        $this->paymentModel = new TeacherPayment();
    }

    public function calculateCompensation(int $teacherId, int $periodId, ?int $batchId = null): array
    {
        $tenantId = current_tenant_id();

        //Teachers
        $teacher = $this->db->queryOne(
            "SELECT * FROM `teachers` WHERE `id` = :id AND `tenant_id` = :tid AND `deleted_at` IS NULL",
            ['id' => $teacherId, 'tid' => $tenantId]
        );
        if (!$teacher) {
            throw new \RuntimeException('Teacher not found.');
        }

        $period = $this->db->queryOne(
            "SELECT * FROM `teacher_payment_periods` WHERE `id` = :id AND `tenant_id` = :tid",
            ['id' => $periodId, 'tid' => $tenantId]
        );
        if (!$period) {
            throw new \RuntimeException('Payment period not found.');
        }

        //Teachers
        $rule = null;
        if ($batchId) {
            $rule = $this->db->queryOne(
                "SELECT * FROM `teacher_payment_rules` WHERE `tenant_id` = :tid AND `teacher_id` = :teacher_id AND `batch_id` = :batch_id AND `effective_from` <= :end_date AND (`effective_to` IS NULL OR `effective_to` >= :start_date) ORDER BY `id` DESC LIMIT 1",
                ['tid' => $tenantId, 'teacher_id' => $teacherId, 'batch_id' => $batchId, 'start_date' => $period['start_date'], 'end_date' => $period['end_date']]
            );
        }

        if (!$rule) {
            $rule = $this->db->queryOne(
                "SELECT * FROM `teacher_payment_rules` WHERE `tenant_id` = :tid AND `teacher_id` = :teacher_id AND `batch_id` IS NULL AND `effective_from` <= :end_date AND (`effective_to` IS NULL OR `effective_to` >= :start_date) ORDER BY `id` DESC LIMIT 1",
                ['tid' => $tenantId, 'teacher_id' => $teacherId, 'start_date' => $period['start_date'], 'end_date' => $period['end_date']]
            );
        }

        //Teachers
        $model      = $rule['model'] ?? $teacher['compensation_model'] ?? 'revenue_share';
        $rate       = (float) ($rule['rate'] ?? $teacher['default_rate'] ?? 0.00);
        $percentage = (float) ($rule['percentage'] ?? $teacher['default_percentage'] ?? 0.00);

        $sessionQuery = "SELECT COUNT(*) AS total_sessions FROM `class_sessions` WHERE `tenant_id` = :tid AND `teacher_id` = :teacher_id AND `status` = 'completed' AND `session_date` BETWEEN :start_date AND :end_date";
        $sessionParams = ['tid' => $tenantId, 'teacher_id' => $teacherId, 'start_date' => $period['start_date'], 'end_date' => $period['end_date']];
        if ($batchId) {
            $sessionQuery .= " AND `batch_id` = :batch_id";
            $sessionParams['batch_id'] = $batchId;
        }
        $totalSessions = (int) $this->db->queryOne($sessionQuery, $sessionParams)['total_sessions'];

        //Students
        $studentQuery = "SELECT COUNT(DISTINCT e.student_id) AS total_students FROM `enrollments` e JOIN `batches` b ON e.batch_id = b.id WHERE e.`tenant_id` = :tid AND b.`teacher_id` = :teacher_id AND e.`status` = 'active' AND e.`deleted_at` IS NULL";
        $studentParams = ['tid' => $tenantId, 'teacher_id' => $teacherId];
        if ($batchId) {
            $studentQuery .= " AND e.`batch_id` = :batch_id";
            $studentParams['batch_id'] = $batchId;
        }
        $totalStudents = (int) $this->db->queryOne($studentQuery, $studentParams)['total_students'];

        //Teachers
        $revenueQuery = "SELECT COALESCE(SUM(pa.amount), 0) AS total_revenue 
                         FROM `payment_allocations` pa 
                         JOIN `payments` p ON pa.payment_id = p.id 
                         JOIN `invoices` i ON pa.invoice_id = i.id 
                         JOIN `invoice_items` ii ON i.id = ii.invoice_id 
                         JOIN `batches` b ON ii.batch_id = b.id 
                         WHERE pa.`tenant_id` = :tid 
                         AND b.`teacher_id` = :teacher_id 
                         AND p.`status` = 'confirmed' 
                         AND p.`payment_date` BETWEEN :start_date AND :end_date";
        $revenueParams = ['tid' => $tenantId, 'teacher_id' => $teacherId, 'start_date' => $period['start_date'], 'end_date' => $period['end_date']];
        if ($batchId) {
            $revenueQuery .= " AND b.`id` = :batch_id";
            $revenueParams['batch_id'] = $batchId;
        }
        $eligibleRevenue = (float) $this->db->queryOne($revenueQuery, $revenueParams)['total_revenue'];

        //Calculation
        $calculatedAmount = 0.00;
        switch ($model) {
            case 'fixed_salary':
                $calculatedAmount = $rate;
                break;
            case 'per_session':
                $calculatedAmount = round($totalSessions * $rate, 2);
                break;
            case 'per_student':
                $calculatedAmount = round($totalStudents * $rate, 2);
                break;
            case 'revenue_share':
                $calculatedAmount = round($eligibleRevenue * ($percentage / 100), 2);
                break;
            case 'custom':
            default:
                $calculatedAmount = round($eligibleRevenue * ($percentage / 100) + ($totalSessions * $rate), 2);
                break;
        }

        return [
            'teacher_id'         => $teacherId,
            'period_id'          => $periodId,
            'batch_id'           => $batchId,
            'compensation_model' => $model,
            'rate'               => $rate,
            'percentage'         => $percentage,
            'total_sessions'     => $totalSessions,
            'total_students'     => $totalStudents,
            'eligible_revenue'   => $eligibleRevenue,
            'calculated_amount'  => $calculatedAmount,
            'adjustments'        => 0.00,
            'deductions'         => 0.00,
            'net_payable'        => $calculatedAmount,
        ];
    }

    public function saveAndApproveCalculation(array $calcData, bool $autoApprove = true): int
    {
        return $this->db->transaction(function () use ($calcData, $autoApprove) {
            $calcData['tenant_id'] = current_tenant_id();
            if ($autoApprove) {
                $calcData['status']      = 'approved';
                $calcData['approved_by'] = auth_id();
                $calcData['approved_at'] = date('Y-m-d H:i:s');
            } else {
                $calcData['status'] = 'draft';
            }

            $id = $this->calcModel->create($calcData);
            $this->audit->log('teacher_calc_created', 'teacher_payment_calculations', $id, "Teacher calculation #{$id} saved. Amount: Rs. {$calcData['net_payable']}");
            return $id;
        });
    }

    public function disbursePayment(
        int $teacherId,
        int $calculationId,
        float $amount,
        string $paymentMethod = 'bank_transfer',
        ?string $referenceNumber = null,
        ?string $notes = null
    ): int {
        return $this->db->transaction(function () use ($teacherId, $calculationId, $amount, $paymentMethod, $referenceNumber, $notes) {
            $tenantId = current_tenant_id();
            $amount   = round($amount, 2);

            $tenant = $this->db->queryOne("SELECT code FROM `tenants` WHERE `id` = :tid", ['tid' => $tenantId]);
            $tCode  = strtoupper($tenant['code'] ?? 'TP');
            $voucherNumber = "{$tCode}-TPV-" . date('Ym') . "-" . substr(uniqid(), -4);

            $paymentId = $this->paymentModel->create([
                'tenant_id'        => $tenantId,
                'teacher_id'       => $teacherId,
                'calculation_id'   => $calculationId,
                'voucher_number'   => $voucherNumber,
                'payment_date'     => date('Y-m-d'),
                'amount'           => $amount,
                'payment_method'   => $paymentMethod,
                'reference_number' => $referenceNumber,
                'paid_by'          => auth_id(),
                'notes'            => $notes,
            ]);

            //Calculation
            $this->db->execute(
                "UPDATE `teacher_payment_calculations` SET `status` = 'paid' WHERE `id` = :id AND `tenant_id` = :tid",
                ['id' => $calculationId, 'tid' => $tenantId]
            );

            $account = $this->db->queryOne(
                "SELECT id, current_balance FROM `financial_accounts` WHERE `tenant_id` = :tid AND `account_type` = 'bank' AND `is_active` = 1 LIMIT 1",
                ['tid' => $tenantId]
            );
            if ($account) {
                $newBal = max(0.00, round((float)$account['current_balance'] - $amount, 2));
                $this->db->execute("UPDATE `financial_accounts` SET `current_balance` = :bal WHERE `id` = :id", ['bal' => $newBal, 'id' => $account['id']]);
            }

            $this->audit->log('teacher_paid', 'teacher_payments', $paymentId, "Teacher payment voucher {$voucherNumber} disbursed for Rs. {$amount}");

            return $paymentId;
        });
    }
}

