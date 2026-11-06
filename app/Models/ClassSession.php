<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ClassSession extends Model
{
    protected string $table = 'class_sessions';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;

    /**
     * Get class session with batch, teacher, and subject details.
     */
    public function findWithDetails(int $id): ?array
    {
        $tenantId = $this->getActiveTenantId();
        return $this->db->queryOne(
            "SELECT cs.*, b.name AS batch_name, s.name AS subject_name, t.full_name AS teacher_name, br.name AS branch_name 
             FROM `class_sessions` cs 
             JOIN `batches` b ON cs.batch_id = b.id 
             JOIN `subjects` s ON b.subject_id = s.id 
             JOIN `teachers` t ON cs.teacher_id = t.id 
             JOIN `branches` br ON cs.branch_id = br.id 
             WHERE cs.`id` = :id AND cs.`tenant_id` = :tid",
            ['id' => $id, 'tid' => $tenantId]
        );
    }
}

