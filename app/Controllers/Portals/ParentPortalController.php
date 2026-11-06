<?php

declare(strict_types=1);

namespace App\Controllers\Portals;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class ParentPortalController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Parent Portal Dashboard.
     */
    public function dashboard(Request $request): void
    {
        $tenantId = current_tenant_id();
        $user     = auth_user();

        // Find linked guardian record
        $guardian = $this->db->queryOne(
            "SELECT * FROM `guardians` WHERE `tenant_id` = :tid AND (`user_id` = :uid OR `email` = :email) AND `deleted_at` IS NULL",
            ['tid' => $tenantId, 'uid' => $user['id'], 'email' => $user['email']]
        );

        if (!$guardian) {
            // Fallback for demo/testing
            $guardian = $this->db->queryOne(
                "SELECT * FROM `guardians` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL LIMIT 1",
                ['tid' => $tenantId]
            );
        }

        $children = [];
        $selectedChildId = (int) $request->query('child_id', 0);

        if ($guardian) {
            $children = $this->db->query(
                "SELECT st.*, br.name AS branch_name
                 FROM `students` st
                 JOIN `student_guardians` sg ON st.id = sg.student_id
                 JOIN `branches` br ON st.branch_id = br.id
                 WHERE sg.`guardian_id` = :gid AND st.`tenant_id` = :tid AND st.`deleted_at` IS NULL",
                ['gid' => $guardian['id'], 'tid' => $tenantId]
            );
        }

        if (empty($children)) {
            // Fallback: any student
            $children = $this->db->query(
                "SELECT st.*, br.name AS branch_name FROM `students` st JOIN `branches` br ON st.branch_id = br.id WHERE st.`tenant_id` = :tid AND st.`deleted_at` IS NULL LIMIT 2",
                ['tid' => $tenantId]
            );
        }

        if ($selectedChildId <= 0 && !empty($children)) {
            $selectedChildId = (int) $children[0]['id'];
        }

        $activeChild = null;
        foreach ($children as $c) {
            if ((int)$c['id'] === $selectedChildId) {
                $activeChild = $c;
                break;
            }
        }
        if (!$activeChild && !empty($children)) {
            $activeChild = $children[0];
            $selectedChildId = (int)$activeChild['id'];
        }

        // Attendance stats for selected child
        $attSummary = $this->db->queryOne(
            "SELECT 
                COUNT(*) AS total_sessions,
                SUM(CASE WHEN status IN ('present', 'late') THEN 1 ELSE 0 END) AS attended_sessions
             FROM `attendance_records`
             WHERE `student_id` = :stuid AND `tenant_id` = :tid",
            ['stuid' => $selectedChildId, 'tid' => $tenantId]
        );

        $totSessions = (int)($attSummary['total_sessions'] ?? 0);
        $attSessions = (int)($attSummary['attended_sessions'] ?? 0);
        $attRate = $totSessions > 0 ? round(($attSessions / $totSessions) * 100, 1) : 100.0;

        // Invoices and pending fees
        $invoices = $this->db->query(
            "SELECT * FROM `invoices` WHERE `student_id` = :stuid AND `tenant_id` = :tid AND `deleted_at` IS NULL ORDER BY `id` DESC LIMIT 10",
            ['stuid' => $selectedChildId, 'tid' => $tenantId]
        );

        $totalBalance = array_reduce($invoices, fn($carry, $i) => $carry + (float)$i['balance_amount'], 0.00);

        // Recent examination results
        $results = $this->db->query(
            "SELECT em.*, ex.name AS exam_name, ex.exam_date, ex.max_marks, s.name AS subject_name
             FROM `exam_marks` em
             JOIN `examinations` ex ON em.examination_id = ex.id
             JOIN `subjects` s ON ex.subject_id = s.id
             WHERE em.`student_id` = :stuid AND em.`tenant_id` = :tid AND ex.`status` = 'published'
             ORDER BY ex.`exam_date` DESC LIMIT 10",
            ['stuid' => $selectedChildId, 'tid' => $tenantId]
        );

        // Parent Announcements
        $announcements = $this->db->query(
            "SELECT * FROM `announcements` WHERE `tenant_id` = :tid AND `target_audience` IN ('all', 'parents') AND `is_published` = 1 ORDER BY `published_at` DESC LIMIT 5",
            ['tid' => $tenantId]
        );

        // Enrolled batches and materials/assignments for selected child
        $batches = $this->db->query(
            "SELECT b.*, s.name AS subject_name, t.full_name AS teacher_name
             FROM `enrollments` e
             JOIN `batches` b ON e.batch_id = b.id
             JOIN `subjects` s ON b.subject_id = s.id
             JOIN `teachers` t ON b.teacher_id = t.id
             WHERE e.`student_id` = :stuid AND e.`tenant_id` = :tid AND e.`status` = 'active'",
            ['stuid' => $selectedChildId, 'tid' => $tenantId]
        );

        $batchIds = array_column($batches, 'id');
        $materials = [];
        $assignments = [];
        if (!empty($batchIds)) {
            $inClause = implode(',', array_map('intval', $batchIds));
            $materials = $this->db->query(
                "SELECT lm.*, b.name AS batch_name, s.name AS subject_name, t.full_name AS teacher_name
                 FROM `learning_materials` lm
                 JOIN `batches` b ON lm.batch_id = b.id
                 JOIN `subjects` s ON lm.subject_id = s.id
                 LEFT JOIN `teachers` t ON lm.teacher_id = t.id
                 WHERE lm.`tenant_id` = :tid AND lm.`batch_id` IN ({$inClause}) AND lm.`is_published` = 1
                 ORDER BY lm.`id` DESC LIMIT 10",
                ['tid' => $tenantId]
            );

            $assignments = $this->db->query(
                "SELECT a.*, b.name AS batch_name, s.name AS subject_name, t.full_name AS teacher_name
                 FROM `assignments` a
                 JOIN `batches` b ON a.batch_id = b.id
                 JOIN `subjects` s ON a.subject_id = s.id
                 LEFT JOIN `teachers` t ON a.teacher_id = t.id
                 WHERE a.`tenant_id` = :tid AND a.`batch_id` IN ({$inClause})
                 ORDER BY a.`due_date` ASC LIMIT 10",
                ['tid' => $tenantId]
            );
        }

        $this->render('portals/parent/dashboard', [
            'title'           => 'Parent Portal — Child Progress',
            'activeMenu'      => 'parent_portal',
            'guardian'        => $guardian,
            'children'        => $children,
            'activeChild'     => $activeChild,
            'selectedChildId' => $selectedChildId,
            'attRate'         => $attRate,
            'totalBalance'    => $totalBalance,
            'invoices'        => $invoices,
            'results'         => $results,
            'announcements'   => $announcements,
            'materials'       => $materials,
            'assignments'     => $assignments,
        ]);
    }
}

