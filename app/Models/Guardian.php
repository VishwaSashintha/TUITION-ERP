<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Guardian extends Model
{
    protected string $table = 'guardians';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = true;

    public function getLinkedStudents(int $guardianId): array
    {
        $tenantId = $this->getActiveTenantId();
        return $this->db->query(
            "SELECT s.*, sg.relationship, sg.is_primary, b.name AS branch_name 
             FROM `students` s 
             JOIN `student_guardians` sg ON s.id = sg.student_id 
             LEFT JOIN `branches` b ON s.branch_id = b.id 
             WHERE sg.`guardian_id` = :gid AND s.`tenant_id` = :tid AND s.`deleted_at` IS NULL",
            ['gid' => $guardianId, 'tid' => $tenantId]
        );
    }
}

