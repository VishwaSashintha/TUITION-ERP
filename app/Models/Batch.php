<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Batch extends Model
{
    protected string $table = 'batches';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = true;

    /**
     * Get batch with joined subject, course, teacher, and branch information.
     */
    public function findWithDetails(int $id): ?array
    {
        $tenantId = $this->getActiveTenantId();
        return $this->db->queryOne(
            "SELECT b.*, s.name AS subject_name, s.grade_level, c.name AS course_name, t.full_name AS teacher_name, br.name AS branch_name,
                    (SELECT COUNT(*) FROM `enrollments` e WHERE e.batch_id = b.id AND e.status = 'active' AND e.deleted_at IS NULL) AS enrolled_students_count
             FROM `batches` b 
             JOIN `subjects` s ON b.subject_id = s.id 
             JOIN `teachers` t ON b.teacher_id = t.id 
             LEFT JOIN `courses` c ON b.course_id = c.id 
             LEFT JOIN `branches` br ON b.branch_id = br.id 
             WHERE b.`id` = :id AND b.`tenant_id` = :tid AND b.`deleted_at` IS NULL",
            ['id' => $id, 'tid' => $tenantId]
        );
    }

    /**
     * List all batches with active enrollment count and capacity check.
     */
    public function allWithDetails(?int $branchId = null): array
    {
        $tenantId = $this->getActiveTenantId();
        $conditions = ["b.`tenant_id` = :tid", "b.`deleted_at` IS NULL"];
        $params = ['tid' => $tenantId];

        if ($branchId) {
            $conditions[] = "b.`branch_id` = :bid";
            $params['bid'] = $branchId;
        }

        $where = implode(' AND ', $conditions);
        return $this->db->query(
            "SELECT b.*, s.name AS subject_name, s.grade_level, t.full_name AS teacher_name, br.name AS branch_name,
                    (SELECT COUNT(*) FROM `enrollments` e WHERE e.batch_id = b.id AND e.status = 'active' AND e.deleted_at IS NULL) AS enrolled_count
             FROM `batches` b 
             JOIN `subjects` s ON b.subject_id = s.id 
             JOIN `teachers` t ON b.teacher_id = t.id 
             LEFT JOIN `branches` br ON b.branch_id = br.id 
             WHERE {$where} 
             ORDER BY b.`id` DESC",
            $params
        );
    }
}

