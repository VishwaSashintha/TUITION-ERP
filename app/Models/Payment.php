<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Payment extends Model
{
    protected string $table = 'payments';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false; // Financial payments are immutable (never deleted)

    /**
     * Find payment with joined student, receipt, and allocations.
     */
    public function findWithDetails(int $id): ?array
    {
        $tenantId = $this->getActiveTenantId();
        $payment = $this->db->queryOne(
            "SELECT p.*, s.admission_number, s.full_name AS student_name, s.phone AS student_phone, br.name AS branch_name, u.name AS collector_name 
             FROM `payments` p 
             JOIN `students` s ON p.student_id = s.id 
             JOIN `branches` br ON p.branch_id = br.id 
             LEFT JOIN `users` u ON p.collected_by = u.id 
             WHERE p.`id` = :id AND p.`tenant_id` = :tid",
            ['id' => $id, 'tid' => $tenantId]
        );

        if (!$payment) {
            return null;
        }

        $allocations = $this->db->query(
            "SELECT pa.*, i.invoice_number, i.fee_period, i.total_amount, i.balance_amount 
             FROM `payment_allocations` pa 
             JOIN `invoices` i ON pa.invoice_id = i.id 
             WHERE pa.`payment_id` = :pid AND pa.`tenant_id` = :tid",
            ['pid' => $id, 'tid' => $tenantId]
        );

        $payment['allocations'] = $allocations;
        return $payment;
    }

    /**
     * Generate next sequential payment number and receipt number.
     */
    public function generateNextNumbers(): array
    {
        $tenantId = $this->getActiveTenantId();
        $tenant = $this->db->queryOne("SELECT code FROM `tenants` WHERE `id` = :tid", ['tid' => $tenantId]);
        $tCode = strtoupper($tenant['code'] ?? 'TP');

        $payPrefix = "{$tCode}-PAY-";
        $recPrefix = "{$tCode}-REC-" . date('Ym') . "-";

        $lastPay = $this->db->queryOne(
            "SELECT payment_number FROM `payments` WHERE `tenant_id` = :tid ORDER BY `id` DESC LIMIT 1",
            ['tid' => $tenantId]
        );
        $nextPaySeq = 1;
        if ($lastPay) {
            $parts = explode('-', $lastPay['payment_number']);
            $nextPaySeq = ((int) end($parts)) + 1;
        }

        $lastRec = $this->db->queryOne(
            "SELECT receipt_number FROM `payments` WHERE `tenant_id` = :tid AND `receipt_number` LIKE :pref ORDER BY `id` DESC LIMIT 1",
            ['tid' => $tenantId, 'pref' => "{$recPrefix}%"]
        );
        $nextRecSeq = 1;
        if ($lastRec) {
            $parts = explode('-', $lastRec['receipt_number']);
            $nextRecSeq = ((int) end($parts)) + 1;
        }

        return [
            'payment_number' => $payPrefix . str_pad((string)$nextPaySeq, 4, '0', STR_PAD_LEFT),
            'receipt_number' => $recPrefix . str_pad((string)$nextRecSeq, 4, '0', STR_PAD_LEFT),
        ];
    }
}

