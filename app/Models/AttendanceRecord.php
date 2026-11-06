<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class AttendanceRecord extends Model
{
    protected string $table = 'attendance_records';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;

    /**
     * Get attendance summary for a student.
     */
    public function getStudentSummary(int $studentId): array
    {
        $tenantId = $this->getActiveTenantId();
        $totalSessions = (int) $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM `attendance_records` WHERE `student_id` = :sid AND `tenant_id` = :tid",
            ['sid' => $studentId, 'tid' => $tenantId]
        )['total'];

        $presentSessions = (int) $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM `attendance_records` WHERE `student_id` = :sid AND `tenant_id` = :tid AND `status` IN ('present', 'late')",
            ['sid' => $studentId, 'tid' => $tenantId]
        )['total'];

        $percentage = $totalSessions > 0 ? round(($presentSessions / $totalSessions) * 100, 1) : 0.0;

        return [
            'total'      => $totalSessions,
            'present'    => $presentSessions,
            'percentage' => $percentage,
        ];
    }
}

