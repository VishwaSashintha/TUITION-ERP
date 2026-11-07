<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ClassSchedule extends Model
{
    protected string $table = 'class_schedules';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = false;

    public function detectConflicts(
        int $teacherId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?string $classroom = null,
        ?int $excludeId = null
    ): array {
        $tenantId = $this->getActiveTenantId();
        $conflicts = [];

        //Conflict
        $sql = "SELECT cs.*, b.name AS batch_name, t.full_name AS teacher_name 
                FROM `class_schedules` cs 
                JOIN `batches` b ON cs.batch_id = b.id 
                JOIN `teachers` t ON cs.teacher_id = t.id 
                WHERE cs.`tenant_id` = :tid 
                AND cs.`teacher_id` = :teacher_id 
                AND cs.`day_of_week` = :dow 
                AND cs.`is_active` = 1 
                AND (cs.`start_time` < :end_time AND cs.`end_time` > :start_time)";
        
        $params = [
            'tid'        => $tenantId,
            'teacher_id' => $teacherId,
            'dow'        => $dayOfWeek,
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ];

        if ($excludeId) {
            $sql .= " AND cs.`id` != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $teacherClash = $this->db->queryOne($sql, $params);
        if ($teacherClash) {
            $conflicts[] = "Teacher {$teacherClash['teacher_name']} has a schedule clash with batch '{$teacherClash['batch_name']}' ({$teacherClash['start_time']} - {$teacherClash['end_time']}).";
        }

        if (!empty($classroom)) {
            $sqlRoom = "SELECT cs.*, b.name AS batch_name 
                        FROM `class_schedules` cs 
                        JOIN `batches` b ON cs.batch_id = b.id 
                        WHERE cs.`tenant_id` = :tid 
                        AND cs.`classroom` = :room 
                        AND cs.`day_of_week` = :dow 
                        AND cs.`is_active` = 1 
                        AND (cs.`start_time` < :end_time AND cs.`end_time` > :start_time)";
            $roomParams = [
                'tid'        => $tenantId,
                'room'       => $classroom,
                'dow'        => $dayOfWeek,
                'start_time' => $startTime,
                'end_time'   => $endTime,
            ];
            if ($excludeId) {
                $sqlRoom .= " AND cs.`id` != :exclude_id";
                $roomParams['exclude_id'] = $excludeId;
            }
            $roomClash = $this->db->queryOne($sqlRoom, $roomParams);
            if ($roomClash) {
                $conflicts[] = "Classroom '{$classroom}' is already occupied by batch '{$roomClash['batch_name']}' ({$roomClash['start_time']} - {$roomClash['end_time']}).";
            }
        }

        return $conflicts;
    }
}

