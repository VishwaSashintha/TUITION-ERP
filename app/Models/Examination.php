<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Examination extends Model
{
    protected string $table = 'examinations';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = true;

    public function findWithMarks(int $id): ?array
    {
        $tenantId = $this->getActiveTenantId();
        $exam = $this->db->queryOne(
            "SELECT e.*, b.name AS batch_name, s.name AS subject_name, br.name AS branch_name 
             FROM `examinations` e 
             JOIN `batches` b ON e.batch_id = b.id 
             JOIN `subjects` s ON e.subject_id = s.id 
             JOIN `branches` br ON e.branch_id = br.id 
             WHERE e.`id` = :id AND e.`tenant_id` = :tid AND e.`deleted_at` IS NULL",
            ['id' => $id, 'tid' => $tenantId]
        );

        if (!$exam) {
            return null;
        }

        $marks = $this->db->query(
            "SELECT em.*, st.admission_number, st.full_name AS student_name 
             FROM `exam_marks` em 
             JOIN `students` st ON em.student_id = st.id 
             WHERE em.`examination_id` = :eid AND em.`tenant_id` = :tid 
             ORDER BY em.`marks_obtained` DESC",
            ['eid' => $id, 'tid' => $tenantId]
        );

        $exam['marks'] = $marks;
        return $exam;
    }
}

