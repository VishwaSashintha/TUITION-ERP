<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Teacher extends Model
{
    protected string $table = 'teachers';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = true;

    /**
     * Get batches assigned to a teacher.
     */
    public function getAssignedBatches(int $teacherId): array
    {
        $tenantId = $this->getActiveTenantId();
        return $this->db->query(
            "SELECT b.*, s.name AS subject_name, c.name AS course_name, br.name AS branch_name 
             FROM `batches` b 
             JOIN `subjects` s ON b.subject_id = s.id 
             LEFT JOIN `courses` c ON b.course_id = c.id 
             LEFT JOIN `branches` br ON b.branch_id = br.id 
             WHERE b.`teacher_id` = :tid AND b.`tenant_id` = :tenant_id AND b.`deleted_at` IS NULL",
            ['tid' => $teacherId, 'tenant_id' => $tenantId]
        );
    }
}

