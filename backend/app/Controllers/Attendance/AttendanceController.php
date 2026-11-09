<?php

declare(strict_types=1);

namespace App\Controllers\Attendance;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Services\AuditService;

class AttendanceController extends Controller
{
    private AttendanceRecord $attendanceModel;
    private ClassSession $sessionModel;
    private AuditService $audit;
    private Database $db;

    public function __construct()
    {
        $this->attendanceModel = new AttendanceRecord();
        $this->sessionModel    = new ClassSession();
        $this->audit           = new AuditService();
        $this->db              = Database::getInstance();
    }

    public function take(Request $request): void
    {
        $sessionId = (int) $request->query('session_id');
        $tenantId = current_tenant_id();

        if ($sessionId <= 0) {
            $today = date('Y-m-d');
            $session = $this->db->queryOne(
                "SELECT id FROM `class_sessions` WHERE `tenant_id` = :tid AND `session_date` = :date ORDER BY `start_time` ASC LIMIT 1",
                ['tid' => $tenantId, 'date' => $today]
            );

            if ($session) {
                $sessionId = (int) $session['id'];
            } else {
                $session = $this->db->queryOne(
                    "SELECT id FROM `class_sessions` WHERE `tenant_id` = :tid ORDER BY `session_date` DESC, `start_time` ASC LIMIT 1",
                    ['tid' => $tenantId]
                );
                if ($session) {
                    $sessionId = (int) $session['id'];
                }
            }

            if ($sessionId <= 0) {
                $this->redirect(url('timetable/sessions'), 'warning', 'Please select or generate a class session first to take attendance.');
                return;
            }
        }

        $session = $this->db->queryOne(
            "SELECT cs.*, b.name AS batch_name, s.name AS subject_name, t.full_name AS teacher_name, br.name AS branch_name
             FROM `class_sessions` cs
             JOIN `batches` b ON cs.batch_id = b.id
             JOIN `subjects` s ON b.subject_id = s.id
             JOIN `teachers` t ON cs.teacher_id = t.id
             JOIN `branches` br ON cs.branch_id = br.id
             WHERE cs.`id` = :id AND cs.`tenant_id` = :tid",
            ['id' => $sessionId, 'tid' => $tenantId]
        );

        if (!$session) {
            $this->redirect(url('timetable/sessions'), 'error', 'Class session not found.');
            return;
        }

        //Students
        $students = $this->db->query(
            "SELECT st.id, st.admission_number, st.full_name, st.phone, st.status AS student_status,
                    ar.id AS attendance_id, ar.status AS attendance_status, ar.check_in_time, ar.notes
             FROM `enrollments` e
             JOIN `students` st ON e.student_id = st.id
             LEFT JOIN `attendance_records` ar ON ar.student_id = st.id AND ar.class_session_id = :sid AND ar.tenant_id = :tid_ar
             WHERE e.batch_id = :bid AND e.tenant_id = :tid_e AND e.status = 'active' AND st.deleted_at IS NULL
             ORDER BY st.full_name ASC",
            [
                'sid'    => $sessionId,
                'tid_ar' => $tenantId,
                'bid'    => $session['batch_id'],
                'tid_e'  => $tenantId
            ]
        );

        $daySessions = $this->db->query(
            "SELECT cs.id, cs.start_time, cs.end_time, b.name AS batch_name
             FROM `class_sessions` cs
             JOIN `batches` b ON cs.batch_id = b.id
             WHERE cs.tenant_id = :tid AND cs.session_date = :date
             ORDER BY cs.start_time ASC",
            ['tid' => $tenantId, 'date' => $session['session_date']]
        );

        $this->render('attendance/take', [
            'title'       => 'Mark Attendance — ' . $session['batch_name'],
            'activeMenu'  => 'attendance',
            'session'     => $session,
            'students'    => $students,
            'daySessions' => $daySessions,
        ]);
    }

    public function save(Request $request): void
    {
        $sessionId = (int) $request->input('session_id');
        $tenantId  = current_tenant_id();
        $userId    = (int) auth_user()['id'];

        $session = $this->db->queryOne(
            "SELECT * FROM `class_sessions` WHERE `id` = :id AND `tenant_id` = :tid",
            ['id' => $sessionId, 'tid' => $tenantId]
        );

        if (!$session) {
            $this->back('error', 'Session not found.');
            return;
        }

        $statuses = (array) $request->input('attendance', []);
        $notes    = (array) $request->input('notes', []);

        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO `attendance_records` (`tenant_id`, `class_session_id`, `batch_id`, `student_id`, `status`, `check_in_time`, `recorded_by`, `notes`, `created_at`, `updated_at`)
             VALUES (:tid, :sid, :bid, :stuid, :status, :time, :uid, :note, NOW(), NOW())
             ON DUPLICATE KEY UPDATE `status` = VALUES(`status`), `check_in_time` = VALUES(`check_in_time`), `notes` = VALUES(`notes`), `updated_at` = NOW()"
        );

        $currentTime = date('H:i:s');
        $markedCount = 0;

        foreach ($statuses as $studentId => $status) {
            $studentId = (int) $studentId;
            $note = $notes[$studentId] ?? null;

            $stmt->execute([
                'tid'    => $tenantId,
                'sid'    => $sessionId,
                'bid'    => $session['batch_id'],
                'stuid'  => $studentId,
                'status' => in_array($status, ['present', 'absent', 'late', 'excused'], true) ? $status : 'present',
                'time'   => in_array($status, ['present', 'late'], true) ? $currentTime : null,
                'uid'    => $userId,
                'note'   => $note ? (string)$note : null,
            ]);
            $markedCount++;
        }

        $this->db->execute(
            "UPDATE `class_sessions` SET `status` = 'completed' WHERE `id` = :id AND `tenant_id` = :tid",
            ['id' => $sessionId, 'tid' => $tenantId]
        );

        $this->audit->log('attendance_marked', 'class_sessions', $sessionId, "Attendance recorded for {$markedCount} students");

        $this->redirect(url("attendance/take?session_id={$sessionId}"), 'success', "Attendance saved successfully for {$markedCount} students.");
    }

    public function qrScanner(Request $request): void
    {
        $tenantId = current_tenant_id();
        $sessionId = (int) $request->query('session_id');

        $today = date('Y-m-d');
        $sessions = $this->db->query(
            "SELECT cs.id, cs.start_time, cs.end_time, b.name AS batch_name, s.name AS subject_name, t.full_name AS teacher_name
             FROM `class_sessions` cs
             JOIN `batches` b ON cs.batch_id = b.id
             JOIN `subjects` s ON b.subject_id = s.id
             JOIN `teachers` t ON cs.teacher_id = t.id
             WHERE cs.tenant_id = :tid AND cs.session_date = :date
             ORDER BY cs.start_time ASC",
            ['tid' => $tenantId, 'date' => $today]
        );

        $this->render('attendance/qr_scanner', [
            'title'      => 'QR Attendance Scanner',
            'activeMenu' => 'attendance',
            'sessions'   => $sessions,
            'sessionId'  => $sessionId,
        ]);
    }

    public function scanQr(Request $request): void
    {
        $tenantId = current_tenant_id();
        $userId   = (int) auth_user()['id'];

        $sessionId = (int) $request->input('session_id');
        $code      = trim((string) $request->input('admission_number'));

        if ($sessionId <= 0 || empty($code)) {
            $this->json(['success' => false, 'message' => 'Session and Student Code are required.'], 400);
            return;
        }

        $session = $this->db->queryOne(
            "SELECT cs.*, b.name AS batch_name FROM `class_sessions` cs JOIN `batches` b ON cs.batch_id = b.id WHERE cs.`id` = :id AND cs.`tenant_id` = :tid",
            ['id' => $sessionId, 'tid' => $tenantId]
        );

        if (!$session) {
            $this->json(['success' => false, 'message' => 'Class session not found.'], 404);
            return;
        }

        //Students
        $student = $this->db->queryOne(
            "SELECT s.* FROM `students` s WHERE (s.`admission_number` = :code OR s.`id` = :code_id) AND s.`tenant_id` = :tid AND s.`deleted_at` IS NULL",
            ['code' => $code, 'code_id' => is_numeric($code) ? (int)$code : 0, 'tid' => $tenantId]
        );

        if (!$student) {
            $this->json(['success' => false, 'message' => "Student not found with code {$code}."], 404);
            return;
        }

        $enrollment = $this->db->queryOne(
            "SELECT id FROM `enrollments` WHERE `student_id` = :stuid AND `batch_id` = :bid AND `tenant_id` = :tid AND `status` = 'active'",
            ['stuid' => $student['id'], 'bid' => $session['batch_id'], 'tid' => $tenantId]
        );

        $isEnrolled = (bool) $enrollment;

        //Attendance
        $existing = $this->db->queryOne(
            "SELECT id, status, check_in_time FROM `attendance_records` WHERE `class_session_id` = :sid AND `student_id` = :stuid AND `tenant_id` = :tid",
            ['sid' => $sessionId, 'stuid' => $student['id'], 'tid' => $tenantId]
        );

        $currentTime = date('H:i:s');
        if ($existing) {
            $this->json([
                'success' => true,
                'already_marked' => true,
                'student' => [
                    'id' => $student['id'],
                    'name' => $student['full_name'],
                    'admission_number' => $student['admission_number'],
                    'check_in_time' => $existing['check_in_time'],
                    'status' => $existing['status'],
                    'batch' => $session['batch_name'],
                ],
                'message' => "Student {$student['full_name']} was already marked as {$existing['status']} at {$existing['check_in_time']}."
            ]);
            return;
        }

        $this->db->execute(
            "INSERT INTO `attendance_records` (`tenant_id`, `class_session_id`, `batch_id`, `student_id`, `status`, `check_in_time`, `recorded_by`, `notes`, `created_at`, `updated_at`)
             VALUES (:tid, :sid, :bid, :stuid, 'present', :time, :uid, :note, NOW(), NOW())",
            [
                'tid'   => $tenantId,
                'sid'   => $sessionId,
                'bid'   => $session['batch_id'],
                'stuid' => $student['id'],
                'time'  => $currentTime,
                'uid'   => $userId,
                'note'  => $isEnrolled ? 'QR Scanned' : 'QR Scanned (Unenrolled Visitor)',
            ]
        );

        $this->json([
            'success' => true,
            'already_marked' => false,
            'student' => [
                'id' => $student['id'],
                'name' => $student['full_name'],
                'admission_number' => $student['admission_number'],
                'check_in_time' => $currentTime,
                'status' => 'present',
                'batch' => $session['batch_name'],
                'warning' => !$isEnrolled ? 'Warning: Student is not officially enrolled in this batch!' : null,
            ],
            'message' => "Successfully marked PRESENT for {$student['full_name']} at {$currentTime}."
        ]);
    }

    public function history(Request $request): void
    {
        $tenantId  = current_tenant_id();
        $batchId   = (int) $request->query('batch_id', 0);
        $status    = $request->query('status', '');
        $startDate = $request->query('start_date', date('Y-m-01'));
        $endDate   = $request->query('end_date', date('Y-m-d'));

        $sql = "SELECT ar.*, st.full_name AS student_name, st.admission_number, b.name AS batch_name, cs.session_date, cs.start_time
                FROM `attendance_records` ar
                JOIN `students` st ON ar.student_id = st.id
                JOIN `batches` b ON ar.batch_id = b.id
                JOIN `class_sessions` cs ON ar.class_session_id = cs.id
                WHERE ar.`tenant_id` = :tid AND cs.`session_date` BETWEEN :start AND :end";
        $params = [
            'tid'   => $tenantId,
            'start' => $startDate,
            'end'   => $endDate,
        ];

        if ($batchId > 0) {
            $sql .= " AND ar.`batch_id` = :bid";
            $params['bid'] = $batchId;
        }

        if (!empty($status)) {
            $sql .= " AND ar.`status` = :st";
            $params['st'] = $status;
        }

        $sql .= " ORDER BY cs.`session_date` DESC, ar.`check_in_time` DESC LIMIT 200";

        $records = $this->db->query($sql, $params);
        $batches = $this->db->query("SELECT id, name FROM `batches` WHERE `tenant_id` = :tid AND `status` = 'active'", ['tid' => $tenantId]);

        //Calculation
        $summary = $this->db->queryOne(
            "SELECT 
                COUNT(*) AS total_count,
                SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) AS late_count,
                SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) AS absent_count,
                SUM(CASE WHEN ar.status = 'excused' THEN 1 ELSE 0 END) AS excused_count
             FROM `attendance_records` ar
             JOIN `class_sessions` cs ON ar.class_session_id = cs.id
             WHERE ar.`tenant_id` = :tid AND cs.`session_date` BETWEEN :start AND :end",
            ['tid' => $tenantId, 'start' => $startDate, 'end' => $endDate]
        );

        $this->render('attendance/history', [
            'title'      => 'Attendance Records & History',
            'activeMenu' => 'attendance',
            'records'    => $records,
            'batches'    => $batches,
            'summary'    => $summary,
            'filters'    => [
                'batch_id'   => $batchId,
                'status'     => $status,
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ]
        ]);
    }

    public function correct(Request $request): void
    {
        $id = (int) $request->input('id');
        $newStatus = (string) $request->input('status');
        $reason = trim((string) $request->input('reason'));
        $tenantId = current_tenant_id();

        if (empty($reason)) {
            $this->back('error', 'A justification reason is strictly required when correcting attendance.');
            return;
        }

        $record = $this->db->queryOne(
            "SELECT ar.*, st.full_name FROM `attendance_records` ar JOIN `students` st ON ar.student_id = st.id WHERE ar.`id` = :id AND ar.`tenant_id` = :tid",
            ['id' => $id, 'tid' => $tenantId]
        );

        if (!$record) {
            $this->back('error', 'Attendance record not found.');
            return;
        }

        $this->db->execute(
            "UPDATE `attendance_records` SET `status` = :st, `notes` = CONCAT(IFNULL(`notes`, ''), ' [Corrected from ', :old_st, ' by Staff: ', :reason, ']'), `updated_at` = NOW() WHERE `id` = :id AND `tenant_id` = :tid",
            [
                'st'     => $newStatus,
                'old_st' => $record['status'],
                'reason' => $reason,
                'id'     => $id,
                'tid'    => $tenantId
            ]
        );

        $this->audit->log('attendance_corrected', 'attendance_records', $id, "Changed {$record['full_name']} from {$record['status']} to {$newStatus}. Reason: {$reason}");

        $this->back('success', "Attendance record successfully corrected to {$newStatus}.");
    }
}

