<?php

declare(strict_types=1);

namespace App\Controllers\Timetable;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\ClassSchedule;
use App\Models\ClassSession;
use App\Services\AuditService;

class TimetableController extends Controller
{
    private ClassSchedule $scheduleModel;
    private ClassSession $sessionModel;
    private AuditService $audit;
    private Database $db;

    public function __construct()
    {
        $this->scheduleModel = new ClassSchedule();
        $this->sessionModel  = new ClassSession();
        $this->audit         = new AuditService();
        $this->db            = Database::getInstance();
    }

    /**
     * Weekly timetable view.
     */
    public function index(Request $request): void
    {
        $tenantId = current_tenant_id();

        $schedules = $this->db->query(
            "SELECT cs.*, b.name AS batch_name, s.name AS subject_name, t.full_name AS teacher_name, br.name AS branch_name 
             FROM `class_schedules` cs 
             JOIN `batches` b ON cs.batch_id = b.id 
             JOIN `subjects` s ON b.subject_id = s.id 
             JOIN `teachers` t ON cs.teacher_id = t.id 
             JOIN `branches` br ON cs.branch_id = br.id 
             WHERE cs.`tenant_id` = :tid AND cs.`is_active` = 1 
             ORDER BY cs.`day_of_week` ASC, cs.`start_time` ASC",
            ['tid' => $tenantId]
        );

        // Group by day of week (0=Sunday ... 6=Saturday)
        $byDay = array_fill(0, 7, []);
        foreach ($schedules as $s) {
            $byDay[(int)$s['day_of_week']][] = $s;
        }

        $batches  = $this->db->query("SELECT id, name, branch_id, teacher_id FROM `batches` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL", ['tid' => $tenantId]);
        $teachers = $this->db->query("SELECT id, full_name FROM `teachers` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL", ['tid' => $tenantId]);
        $branches = $this->db->query("SELECT id, name FROM `branches` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL", ['tid' => $tenantId]);

        $this->render('timetable/index', [
            'title'      => 'Institute Timetable',
            'activeMenu' => 'timetable',
            'byDay'      => $byDay,
            'batches'    => $batches,
            'teachers'   => $teachers,
            'branches'   => $branches,
        ]);
    }

    /**
     * Store new recurring class schedule with conflict detection.
     */
    public function storeSchedule(Request $request): void
    {
        $rules = [
            'batch_id'    => 'required|numeric',
            'day_of_week' => 'required|numeric',
            'start_time'  => 'required',
            'end_time'    => 'required',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        $batchId   = (int) $request->input('batch_id');
        $dayOfWeek = (int) $request->input('day_of_week');
        $startTime = (string) $request->input('start_time');
        $endTime   = (string) $request->input('end_time');
        $classroom = $request->input('classroom');

        $tenantId = current_tenant_id();
        $batch = $this->db->queryOne("SELECT branch_id, teacher_id, name FROM `batches` WHERE `id` = :id AND `tenant_id` = :tid", ['id' => $batchId, 'tid' => $tenantId]);
        if (!$batch) {
            $this->back('error', 'Batch not found.');
            return;
        }

        $teacherId = (int) $batch['teacher_id'];
        $branchId  = (int) $batch['branch_id'];

        // Run schedule conflict detection
        $conflicts = $this->scheduleModel->detectConflicts($teacherId, $dayOfWeek, $startTime, $endTime, $classroom);
        if (!empty($conflicts)) {
            $this->back('error', 'Schedule conflict: ' . implode(' ', $conflicts), $request->all());
            return;
        }

        try {
            $id = $this->scheduleModel->create([
                'tenant_id'   => $tenantId,
                'branch_id'   => $branchId,
                'batch_id'    => $batchId,
                'teacher_id'  => $teacherId,
                'day_of_week' => $dayOfWeek,
                'start_time'  => $startTime,
                'end_time'    => $endTime,
                'classroom'   => $classroom ?: null,
                'is_active'   => 1,
            ]);

            $this->audit->log('schedule_created', 'class_schedules', $id, "Schedule slot created for {$batch['name']}");
            $this->redirect(url('timetable'), 'success', 'Class schedule slot added with zero conflicts.');
        } catch (\Throwable $e) {
            $this->back('error', 'Failed to save schedule: ' . $e->getMessage(), $request->all());
        }
    }

    /**
     * List date-specific class sessions.
     */
    public function sessions(Request $request): void
    {
        $tenantId = current_tenant_id();
        $date = $request->query('date', date('Y-m-d'));

        $sessions = $this->db->query(
            "SELECT cs.*, b.name AS batch_name, s.name AS subject_name, t.full_name AS teacher_name,
                    (SELECT COUNT(*) FROM `attendance_records` ar WHERE ar.class_session_id = cs.id) AS attendance_count
             FROM `class_sessions` cs 
             JOIN `batches` b ON cs.batch_id = b.id 
             JOIN `subjects` s ON b.subject_id = s.id 
             JOIN `teachers` t ON cs.teacher_id = t.id 
             WHERE cs.`tenant_id` = :tid AND cs.`session_date` = :date 
             ORDER BY cs.`start_time` ASC",
            ['tid' => $tenantId, 'date' => $date]
        );

        $this->render('timetable/sessions', [
            'title'      => 'Class Sessions',
            'activeMenu' => 'timetable',
            'sessions'   => $sessions,
            'date'       => $date,
        ]);
    }

    /**
     * Generate class sessions for the current month from active schedules.
     */
    public function generateSessions(Request $request): void
    {
        $tenantId = current_tenant_id();
        $yearMonth = $request->input('year_month', date('Y-m'));

        $schedules = $this->db->query(
            "SELECT * FROM `class_schedules` WHERE `tenant_id` = :tid AND `is_active` = 1",
            ['tid' => $tenantId]
        );

        if (empty($schedules)) {
            $this->back('error', 'No active recurring schedules found to generate sessions from.');
            return;
        }

        $startDate = new \DateTime("{$yearMonth}-01");
        $endDate   = (clone $startDate)->modify('last day of this month');

        $createdCount = 0;
        $insertStmt = $this->db->getConnection()->prepare(
            "INSERT IGNORE INTO `class_sessions` (`tenant_id`, `branch_id`, `batch_id`, `teacher_id`, `schedule_id`, `session_date`, `start_time`, `end_time`, `classroom`, `status`, `created_at`) 
             VALUES (:tid, :bid, :batch_id, :tid_t, :sid, :sdate, :stime, :etime, :room, 'scheduled', NOW())"
        );

        for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {
            $dow = (int) $date->format('w'); // 0=Sunday ... 6=Saturday
            $currentDateStr = $date->format('Y-m-d');

            foreach ($schedules as $sched) {
                if ((int)$sched['day_of_week'] === $dow) {
                    // Check if already generated
                    $exists = $this->db->queryOne(
                        "SELECT id FROM `class_sessions` WHERE `tenant_id` = :tid AND `batch_id` = :bid AND `session_date` = :sdate AND `start_time` = :stime",
                        ['tid' => $tenantId, 'bid' => $sched['batch_id'], 'sdate' => $currentDateStr, 'stime' => $sched['start_time']]
                    );

                    if (!$exists) {
                        $insertStmt->execute([
                            'tid'      => $tenantId,
                            'bid'      => $sched['branch_id'],
                            'batch_id' => $sched['batch_id'],
                            'tid_t'    => $sched['teacher_id'],
                            'sid'      => $sched['id'],
                            'sdate'    => $currentDateStr,
                            'stime'    => $sched['start_time'],
                            'etime'    => $sched['end_time'],
                            'room'     => $sched['classroom'],
                        ]);
                        $createdCount++;
                    }
                }
            }
        }

        $this->redirect(url("timetable/sessions?date={$yearMonth}-01"), 'success', "Generated {$createdCount} class sessions for {$yearMonth}.");
    }
}

