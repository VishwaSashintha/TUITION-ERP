<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Refund;

class BillingService
{
    private Database $db;
    private AuditService $audit;
    private Invoice $invoiceModel;
    private Payment $paymentModel;

    public function __construct()
    {
        $this->db           = Database::getInstance();
        $this->audit        = new AuditService();
        $this->invoiceModel = new Invoice();
        $this->paymentModel = new Payment();
    }

    public function createInvoice(int $studentId, string $feePeriod, string $dueDate, array $items, ?string $notes = null): int
    {
        return $this->db->transaction(function () use ($studentId, $feePeriod, $dueDate, $items, $notes) {
            $tenantId = current_tenant_id();

            //Students
            $student = $this->db->queryOne(
                "SELECT id, branch_id FROM `students` WHERE `id` = :id AND `tenant_id` = :tid",
                ['id' => $studentId, 'tid' => $tenantId]
            );
            if (!$student) {
                throw new \RuntimeException('Student not found within the active institute.');
            }

            $subtotal       = 0.00;
            $discountAmount = 0.00;
            $totalAmount    = 0.00;

            foreach ($items as $item) {
                $amt  = round((float) ($item['amount'] ?? 0), 2);
                $disc = round((float) ($item['discount'] ?? 0), 2);
                $net  = max(0.00, $amt - $disc);

                $subtotal       += $amt;
                $discountAmount += $disc;
                $totalAmount    += $net;
            }

            $invoiceNumber = $this->invoiceModel->generateNextNumber();

            $invoiceId = $this->invoiceModel->create([
                'tenant_id'       => $tenantId,
                'branch_id'       => $student['branch_id'],
                'student_id'      => $studentId,
                'invoice_number'  => $invoiceNumber,
                'fee_period'      => $feePeriod,
                'issue_date'      => date('Y-m-d'),
                'due_date'        => $dueDate,
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'total_amount'    => $totalAmount,
                'paid_amount'     => 0.00,
                'balance_amount'  => $totalAmount,
                'status'          => 'issued',
                'created_by'      => auth_id(),
                'notes'           => $notes,
            ]);

            $iiStmt = $this->db->getConnection()->prepare(
                "INSERT INTO `invoice_items` (`tenant_id`, `invoice_id`, `fee_type_id`, `batch_id`, `description`, `amount`, `discount`, `net_amount`, `created_at`) 
                 VALUES (:tid, :inv_id, :ft_id, :b_id, :desc, :amt, :disc, :net, NOW())"
            );

            foreach ($items as $item) {
                $amt  = round((float) ($item['amount'] ?? 0), 2);
                $disc = round((float) ($item['discount'] ?? 0), 2);
                $net  = max(0.00, $amt - $disc);

                $iiStmt->execute([
                    'tid'    => $tenantId,
                    'inv_id' => $invoiceId,
                    'ft_id'  => $item['fee_type_id'] ?? null,
                    'b_id'   => $item['batch_id'] ?? null,
                    'desc'   => $item['description'],
                    'amt'    => $amt,
                    'disc'   => $disc,
                    'net'    => $net,
                ]);
            }

            $this->audit->log('invoice_created', 'invoices', $invoiceId, "Invoice {$invoiceNumber} created for Rs. {$totalAmount}");

            return $invoiceId;
        });
    }

    public function recordPayment(
        int $studentId,
        float $amount,
        string $paymentMethod,
        array $allocations,
        ?string $referenceNumber = null,
        ?string $notes = null
    ): int {
        return $this->db->transaction(function () use ($studentId, $amount, $paymentMethod, $allocations, $referenceNumber, $notes) {
            $tenantId = current_tenant_id();
            $amount   = round($amount, 2);

            if ($amount <= 0.00) {
                throw new \RuntimeException('Payment amount must be greater than zero.');
            }

            $allocatedTotal = 0.00;
            foreach ($allocations as $alloc) {
                $allocatedTotal += round((float) ($alloc['amount'] ?? 0), 2);
            }

            if (round($allocatedTotal, 2) > $amount) {
                throw new \RuntimeException(
                    sprintf('Allocated amount (Rs. %.2f) cannot exceed collected payment amount (Rs. %.2f).', $allocatedTotal, $amount)
                );
            }

            //Students
            $student = $this->db->queryOne(
                "SELECT id, branch_id FROM `students` WHERE `id` = :id AND `tenant_id` = :tid",
                ['id' => $studentId, 'tid' => $tenantId]
            );
            if (!$student) {
                throw new \RuntimeException('Student not found within the active institute.');
            }

            $numbers = $this->paymentModel->generateNextNumbers();
            $paymentNumber = $numbers['payment_number'];
            $receiptNumber = $numbers['receipt_number'];

            $paymentId = $this->paymentModel->create([
                'tenant_id'        => $tenantId,
                'branch_id'        => $student['branch_id'],
                'student_id'       => $studentId,
                'payment_number'   => $paymentNumber,
                'payment_date'     => date('Y-m-d'),
                'amount'           => $amount,
                'payment_method'   => $paymentMethod,
                'reference_number' => $referenceNumber,
                'receipt_number'   => $receiptNumber,
                'collected_by'     => auth_id(),
                'notes'            => $notes,
                'status'           => 'confirmed',
            ]);

            $paStmt = $this->db->getConnection()->prepare(
                "INSERT INTO `payment_allocations` (`tenant_id`, `payment_id`, `invoice_id`, `amount`, `created_at`) 
                 VALUES (:tid, :pid, :inv_id, :amt, NOW())"
            );

            foreach ($allocations as $alloc) {
                $invId    = (int) $alloc['invoice_id'];
                $allocAmt = round((float) ($alloc['amount'] ?? 0), 2);

                if ($allocAmt <= 0.00) {
                    continue;
                }

                $invoice = $this->invoiceModel->find($invId);
                if (!$invoice) {
                    throw new \RuntimeException("Invoice #{$invId} not found.");
                }

                $balance = round((float) $invoice['balance_amount'], 2);
                if ($allocAmt > $balance) {
                    throw new \RuntimeException(
                        sprintf("Allocation of Rs. %.2f exceeds invoice %s remaining balance of Rs. %.2f.", $allocAmt, $invoice['invoice_number'], $balance)
                    );
                }

                $paStmt->execute([
                    'tid'    => $tenantId,
                    'pid'    => $paymentId,
                    'inv_id' => $invId,
                    'amt'    => $allocAmt,
                ]);

                $newPaid    = round((float)$invoice['paid_amount'] + $allocAmt, 2);
                $newBalance = max(0.00, round((float)$invoice['total_amount'] - $newPaid, 2));
                $newStatus  = ($newBalance <= 0.00) ? 'paid' : 'partially_paid';

                $this->invoiceModel->update($invId, [
                    'paid_amount'    => $newPaid,
                    'balance_amount' => $newBalance,
                    'status'         => $newStatus,
                ]);
            }

            $cashAccount = $this->db->queryOne(
                "SELECT id, current_balance FROM `financial_accounts` WHERE `tenant_id` = :tid AND `account_type` = 'cash' AND `is_active` = 1 LIMIT 1",
                ['tid' => $tenantId]
            );
            if ($cashAccount) {
                $newAccBal = round((float)$cashAccount['current_balance'] + $amount, 2);
                $this->db->execute(
                    "UPDATE `financial_accounts` SET `current_balance` = :bal WHERE `id` = :id",
                    ['bal' => $newAccBal, 'id' => $cashAccount['id']]
                );
            }

            $this->audit->log('payment_recorded', 'payments', $paymentId, "Payment {$paymentNumber} (Receipt: {$receiptNumber}) recorded for Rs. {$amount}");

            return $paymentId;
        });
    }

    public function refundPayment(int $paymentId, float $refundAmount, string $reason): int
    {
        return $this->processRefund($paymentId, $refundAmount, $reason);
    }

    public function processRefund(int $paymentId, float $refundAmount, string $reason): int
    {
        return $this->db->transaction(function () use ($paymentId, $refundAmount, $reason) {
            $tenantId     = current_tenant_id();
            $refundAmount = round($refundAmount, 2);

            $payment = $this->paymentModel->find($paymentId);
            if (!$payment) {
                throw new \RuntimeException('Payment record not found.');
            }

            if ($payment['status'] === 'refunded') {
                throw new \RuntimeException('This payment has already been refunded.');
            }

            $paidAmount = round((float) $payment['amount'], 2);
            if ($refundAmount > $paidAmount) {
                throw new \RuntimeException("Refund amount (Rs. {$refundAmount}) cannot exceed paid amount (Rs. {$paidAmount}).");
            }

            $tenant = $this->db->queryOne("SELECT code FROM `tenants` WHERE `id` = :tid", ['tid' => $tenantId]);
            $tCode  = strtoupper($tenant['code'] ?? 'TP');
            $refundNumber = "{$tCode}-REF-" . date('Ym') . "-" . substr(uniqid(), -4);

            $allocations = $this->db->query(
                "SELECT * FROM `payment_allocations` WHERE `payment_id` = :pid AND `tenant_id` = :tid",
                ['pid' => $paymentId, 'tid' => $tenantId]
            );

            foreach ($allocations as $alloc) {
                $inv = $this->invoiceModel->find((int)$alloc['invoice_id']);
                if ($inv) {
                    $revertedPaid = max(0.00, round((float)$inv['paid_amount'] - (float)$alloc['amount'], 2));
                    $revertedBal  = round((float)$inv['total_amount'] - $revertedPaid, 2);
                    $revertedStatus = ($revertedBal >= (float)$inv['total_amount']) ? 'issued' : 'partially_paid';

                    $this->invoiceModel->update((int)$inv['id'], [
                        'paid_amount'    => $revertedPaid,
                        'balance_amount' => $revertedBal,
                        'status'         => $revertedStatus,
                    ]);
                }
            }

            $refundModel = new Refund();
            $refundId = $refundModel->create([
                'tenant_id'     => $tenantId,
                'payment_id'    => $paymentId,
                'refund_number' => $refundNumber,
                'refund_date'   => date('Y-m-d'),
                'amount'        => $refundAmount,
                'reason'        => $reason,
                'authorized_by' => auth_id() ?? 1,
            ]);

            $this->db->execute(
                "UPDATE `payments` SET `status` = 'refunded' WHERE `id` = :id AND `tenant_id` = :tid",
                ['id' => $paymentId, 'tid' => $tenantId]
            );

            $cashAccount = $this->db->queryOne(
                "SELECT id, current_balance FROM `financial_accounts` WHERE `tenant_id` = :tid AND `account_type` = 'cash' AND `is_active` = 1 LIMIT 1",
                ['tid' => $tenantId]
            );
            if ($cashAccount) {
                $newAccBal = max(0.00, round((float)$cashAccount['current_balance'] - $refundAmount, 2));
                $this->db->execute(
                    "UPDATE `financial_accounts` SET `current_balance` = :bal WHERE `id` = :id",
                    ['bal' => $newAccBal, 'id' => $cashAccount['id']]
                );
            }

            $this->audit->log('payment_refunded', 'refunds', $refundId, "Refund {$refundNumber} of Rs. {$refundAmount} processed for Payment #{$paymentId}");

            return $refundId;
        });
    }

    public function getFinancialKPIs(): array
    {
        $tenantId = current_tenant_id();

        $billedRow = $this->db->queryOne(
            "SELECT COALESCE(SUM(total_amount), 0) AS total FROM `invoices` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL AND `status` != 'cancelled'",
            ['tid' => $tenantId]
        );

        $collectedRow = $this->db->queryOne(
            "SELECT COALESCE(SUM(amount), 0) AS total FROM `payments` WHERE `tenant_id` = :tid AND `status` = 'confirmed'",
            ['tid' => $tenantId]
        );

        $outstandingRow = $this->db->queryOne(
            "SELECT COALESCE(SUM(balance_amount), 0) AS total FROM `invoices` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL AND `status` IN ('issued', 'partially_paid', 'overdue')",
            ['tid' => $tenantId]
        );

        $expenseRow = $this->db->queryOne(
            "SELECT COALESCE(SUM(amount), 0) AS total FROM `expenses` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL AND `payment_status` != 'cancelled'",
            ['tid' => $tenantId]
        );

        return [
            'total_billed'      => (float) $billedRow['total'],
            'total_collected'   => (float) $collectedRow['total'],
            'total_outstanding' => (float) $outstandingRow['total'],
            'total_expenses'    => (float) $expenseRow['total'],
            'net_profit'        => round((float) $collectedRow['total'] - (float) $expenseRow['total'], 2),
        ];
    }
}

