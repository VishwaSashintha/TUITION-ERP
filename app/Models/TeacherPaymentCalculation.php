<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class TeacherPaymentCalculation extends Model
{
    protected string $table = 'teacher_payment_calculations';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;

    /**
     * Get calculation with teacher, period, and batch info.
     */
    public function findWithDetails(int $id): ?array
    {
        $tenantId = $this->getActiveTenantId();
        return $this->db->queryOne(
            "SELECT tpc.*, t.full_name AS teacher_name, t.employee_id, tpp.name AS period_name, b.name AS batch_name, u.name AS approver_name 
             FROM `teacher_payment_calculations` tpc 
             JOIN `teachers` t ON tpc.teacher_id = t.id 
             JOIN `teacher_payment_periods` tpp ON tpc.period_id = tpp.id 
             LEFT JOIN `batches` b ON tpc.batch_id = b.id 
             LEFT JOIN `users` u ON tpc.approved_by = u.id 
             WHERE tpc.`id` = :id AND tpc.`tenant_id` = :tid",
            ['id' => $id, 'tid' => $tenantId]
        );
    }
}

