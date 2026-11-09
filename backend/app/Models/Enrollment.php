<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Enrollment extends Model
{
    protected string $table = 'enrollments';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = true;

    public function getBatchStudents(int $batchId): array
    {
        $tenantId = $this->getActiveTenantId();
        return $this->db->query(
            "SELECT s.*, e.enrollment_date, e.fee_override, e.status AS enrollment_status 
             FROM `enrollments` e 
             JOIN `students` s ON e.student_id = s.id 
             WHERE e.`batch_id` = :bid AND e.`tenant_id` = :tid AND e.`status` = 'active' AND e.`deleted_at` IS NULL AND s.`deleted_at` IS NULL 
             ORDER BY s.`admission_number` ASC",
            ['bid' => $batchId, 'tid' => $tenantId]
        );
    }
}

