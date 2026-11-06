<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Invoice extends Model
{
    protected string $table = 'invoices';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = true;

    /**
     * Find invoice with joined student, branch, and line items.
     */
    public function findWithDetails(int $id): ?array
    {
        $tenantId = $this->getActiveTenantId();
        $invoice = $this->db->queryOne(
            "SELECT i.*, s.admission_number, s.full_name AS student_name, s.phone AS student_phone, s.grade_level, br.name AS branch_name 
             FROM `invoices` i 
             JOIN `students` s ON i.student_id = s.id 
             JOIN `branches` br ON i.branch_id = br.id 
             WHERE i.`id` = :id AND i.`tenant_id` = :tid AND i.`deleted_at` IS NULL",
            ['id' => $id, 'tid' => $tenantId]
        );

        if (!$invoice) {
            return null;
        }

        $items = $this->db->query(
            "SELECT ii.*, b.name AS batch_name, ft.name AS fee_type_name 
             FROM `invoice_items` ii 
             LEFT JOIN `batches` b ON ii.batch_id = b.id 
             LEFT JOIN `fee_types` ft ON ii.fee_type_id = ft.id 
             WHERE ii.`invoice_id` = :inv_id AND ii.`tenant_id` = :tid",
            ['inv_id' => $id, 'tid' => $tenantId]
        );

        $invoice['items'] = $items;
        return $invoice;
    }

    /**
     * Generate the next sequential invoice number.
     * Format: {TENANT_CODE}-INV-{YYYYMM}-{XXXX}
     */
    public function generateNextNumber(): string
    {
        $tenantId = $this->getActiveTenantId();
        $tenant = $this->db->queryOne("SELECT code FROM `tenants` WHERE `id` = :tid", ['tid' => $tenantId]);
        $tCode = strtoupper($tenant['code'] ?? 'TP');
        $prefix = "{$tCode}-INV-" . date('Ym') . "-";

        $last = $this->db->queryOne(
            "SELECT invoice_number FROM `invoices` WHERE `tenant_id` = :tid AND `invoice_number` LIKE :pref ORDER BY `id` DESC LIMIT 1",
            ['tid' => $tenantId, 'pref' => "{$prefix}%"]
        );

        $nextSeq = 1;
        if ($last) {
            $parts = explode('-', $last['invoice_number']);
            $nextSeq = ((int) end($parts)) + 1;
        }

        return $prefix . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
    }
}

