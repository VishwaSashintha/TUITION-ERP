<?php

declare(strict_types=1);

namespace App\Controllers\Examinations;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Examination;
use App\Models\ExamMark;
use App\Services\AuditService;

class ExamController extends Controller
{
    private Examination $examModel;
    private ExamMark $markModel;
    private AuditService $audit;
    private Database $db;

    public function __construct()
    {
        $this->examModel = new Examination();
        $this->markModel = new ExamMark();
        $this->audit     = new AuditService();
        $this->db        = Database::getInstance();
    }

    public function index(Request $request): void
    {
        $tenantId = current_tenant_id();

        $exams = $this->db->query(
            "SELECT ex.*, b.name AS batch_name, s.name AS subject_name, br.name AS branch_name,
                    (SELECT COUNT(*) FROM `exam_marks` em WHERE em.examination_id = ex.id) AS graded_students_count,
                    (SELECT COUNT(*) FROM `enrollments` en WHERE en.batch_id = ex.batch_id AND en.status = 'active') AS total_students_count
             FROM `examinations` ex
             JOIN `batches` b ON ex.batch_id = b.id
             JOIN `subjects` s ON ex.subject_id = s.id
             JOIN `branches` br ON ex.branch_id = br.id
             WHERE ex.`tenant_id` = :tid AND ex.`deleted_at` IS NULL
             ORDER BY ex.`exam_date` DESC, ex.`id` DESC",
            ['tid' => $tenantId]
        );

        $batches = $this->db->query(
            "SELECT id, name, branch_id, subject_id FROM `batches` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL ORDER BY name ASC",
            ['tid' => $tenantId]
        );

        $this->render('examinations/index', [
            'title'      => 'Examinations & Student Performance',
            'activeMenu' => 'examinations',
            'exams'      => $exams,
            'batches'    => $batches,
        ]);
    }

    public function store(Request $request): void
    {
        $rules = [
            'name'      => 'required',
            'batch_id'  => 'required|numeric',
            'exam_date' => 'required',
            'max_marks' => 'required|numeric',
            'pass_mark' => 'required|numeric',
            'exam_type' => 'required',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        $tenantId = current_tenant_id();
        $batchId  = (int) $request->input('batch_id');

        $batch = $this->db->queryOne(
            "SELECT branch_id, subject_id FROM `batches` WHERE `id` = :id AND `tenant_id` = :tid",
            ['id' => $batchId, 'tid' => $tenantId]
        );

        if (!$batch) {
            $this->back('error', 'Selected batch not found.');
            return;
        }

        try {
            $id = $this->examModel->create([
                'tenant_id'  => $tenantId,
                'branch_id'  => $batch['branch_id'],
                'batch_id'   => $batchId,
                'subject_id' => $batch['subject_id'],
                'name'       => trim((string)$request->input('name')),
                'exam_date'  => (string)$request->input('exam_date'),
                'max_marks'  => (float)$request->input('max_marks'),
                'pass_mark'  => (float)$request->input('pass_mark'),
                'exam_type'  => (string)$request->input('exam_type'),
                'status'     => 'scheduled',
                'created_by' => auth_id() ?? 1,
            ]);

            $this->audit->log('exam_created', 'examinations', $id, "Examination '{$request->input('name')}' created");
            $this->redirect(url('examinations'), 'success', 'Examination scheduled successfully.');
        } catch (\Throwable $e) {
            $this->back('error', 'Failed to schedule exam: ' . $e->getMessage());
        }
    }

    public function marksEntry(Request $request, string $id = ''): void
    {
        $examId   = (int) ($id !== '' ? $id : $request->query('id', 0));
        $tenantId = current_tenant_id();

        if ($examId <= 0) {
            $latest = $this->db->queryOne("SELECT id FROM `examinations` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL ORDER BY `id` DESC LIMIT 1", ['tid' => $tenantId]);
            if ($latest) {
                $examId = (int) $latest['id'];
            }
        }

        $exam = $this->db->queryOne(
            "SELECT ex.*, b.name AS batch_name, s.name AS subject_name, br.name AS branch_name
             FROM `examinations` ex
             JOIN `batches` b ON ex.batch_id = b.id
             JOIN `subjects` s ON ex.subject_id = s.id
             JOIN `branches` br ON ex.branch_id = br.id
             WHERE ex.`id` = :id AND ex.`tenant_id` = :tid AND ex.`deleted_at` IS NULL",
            ['id' => $examId, 'tid' => $tenantId]
        );

        if (!$exam) {
            $this->redirect(url('examinations'), 'error', 'Examination not found.');
            return;
        }

        //Students
        $students = $this->db->query(
            "SELECT st.id AS student_id, st.admission_number, st.full_name,
                    em.id AS mark_id, em.marks_obtained, em.percentage, em.grade, em.rank_in_batch, em.is_absent, em.remarks
             FROM `enrollments` en
             JOIN `students` st ON en.student_id = st.id
             LEFT JOIN `exam_marks` em ON em.student_id = st.id AND em.examination_id = :eid AND em.tenant_id = :tid_em
             WHERE en.`batch_id` = :bid AND en.`tenant_id` = :tid_en AND en.`status` = 'active' AND st.`deleted_at` IS NULL
             ORDER BY st.full_name ASC",
            [
                'eid'    => $examId,
                'tid_em' => $tenantId,
                'bid'    => $exam['batch_id'],
                'tid_en' => $tenantId
            ]
        );

        $this->render('examinations/marks_entry', [
            'title'      => 'Enter Marks — ' . $exam['name'],
            'activeMenu' => 'examinations',
            'exam'       => $exam,
            'students'   => $students,
        ]);
    }

    public function saveMarks(Request $request): void
    {
        $examId   = (int) $request->input('examination_id');
        $tenantId = current_tenant_id();
        $userId   = auth_id() ?? 1;

        $exam = $this->db->queryOne(
            "SELECT * FROM `examinations` WHERE `id` = :id AND `tenant_id` = :tid",
            ['id' => $examId, 'tid' => $tenantId]
        );

        if (!$exam) {
            $this->back('error', 'Exam not found.');
            return;
        }

        $marksInput   = (array) $request->input('marks', []);
        $absentsInput = (array) $request->input('absent', []);
        $remarksInput = (array) $request->input('remarks', []);

        $maxMarks = (float) $exam['max_marks'];
        $passMark = (float) $exam['pass_mark'];

        $this->db->transaction(function () use ($examId, $tenantId, $userId, $marksInput, $absentsInput, $remarksInput, $maxMarks, $passMark) {
            $scores = [];

            //Students
            foreach ($marksInput as $studentId => $markVal) {
                $studentId = (int) $studentId;
                $isAbsent  = isset($absentsInput[$studentId]) && $absentsInput[$studentId] == '1';
                $remarks   = $remarksInput[$studentId] ?? null;

                if ($isAbsent) {
                    $marksObtained = 0.00;
                    $percentage    = 0.00;
                    $grade         = 'AB';
                    $status        = 'absent';
                } else {
                    $marksObtained = round((float)$markVal, 2);
                    $percentage    = $maxMarks > 0 ? round(($marksObtained / $maxMarks) * 100, 2) : 0.00;

                    $grade = match(true) {
                        $percentage >= 75.0 => 'A',
                        $percentage >= 65.0 => 'B',
                        $percentage >= 55.0 => 'C',
                        $percentage >= 40.0 => 'S',
                        default             => 'F'
                    };

                    $status = ($marksObtained >= $passMark) ? 'pass' : 'fail';
                    $scores[$studentId] = $marksObtained;
                }

                $stmt = $this->db->getConnection()->prepare(
                    "INSERT INTO `exam_marks` (`tenant_id`, `examination_id`, `student_id`, `marks_obtained`, `percentage`, `grade`, `is_absent`, `status`, `remarks`, `entered_by`, `created_at`, `updated_at`)
                     VALUES (:tid, :eid, :stuid, :marks, :pct, :grade, :abs, :st, :rem, :uid, NOW(), NOW())
                     ON DUPLICATE KEY UPDATE `marks_obtained` = VALUES(`marks_obtained`), `percentage` = VALUES(`percentage`), `grade` = VALUES(`grade`), `is_absent` = VALUES(`is_absent`), `status` = VALUES(`status`), `remarks` = VALUES(`remarks`), `entered_by` = VALUES(`entered_by`), `updated_at` = NOW()"
                );

                $stmt->execute([
                    'tid'   => $tenantId,
                    'eid'   => $examId,
                    'stuid' => $studentId,
                    'marks' => $isAbsent ? null : $marksObtained,
                    'pct'   => $percentage,
                    'grade' => $grade,
                    'abs'   => $isAbsent ? 1 : 0,
                    'st'    => $status,
                    'rem'   => $remarks ? (string)$remarks : null,
                    'uid'   => $userId,
                ]);
            }

            //Calculation
            arsort($scores);
            $currentRank = 1;
            $prevScore   = null;
            $skipCount   = 0;

            $rankStmt = $this->db->getConnection()->prepare(
                "UPDATE `exam_marks` SET `rank_in_batch` = :rnk WHERE `examination_id` = :eid AND `student_id` = :stuid AND `tenant_id` = :tid"
            );

            foreach ($scores as $sId => $score) {
                if ($prevScore !== null && $score === $prevScore) {
                    $rankStmt->execute(['rnk' => $currentRank, 'eid' => $examId, 'stuid' => $sId, 'tid' => $tenantId]);
                    $skipCount++;
                } else {
                    $currentRank += $skipCount;
                    $skipCount = 1;
                    $rankStmt->execute(['rnk' => $currentRank, 'eid' => $examId, 'stuid' => $sId, 'tid' => $tenantId]);
                    $prevScore = $score;
                }
            }

            $this->db->execute(
                "UPDATE `examinations` SET `status` = 'completed' WHERE `id` = :id AND `tenant_id` = :tid",
                ['id' => $examId, 'tid' => $tenantId]
            );

            $this->audit->log('exam_marks_saved', 'examinations', $examId, "Marks and rankings updated for exam #{$examId}");
        });

        $this->redirect(url("examinations/results?id={$examId}"), 'success', 'Marks saved and student rankings computed successfully.');
    }

    public function results(Request $request, string $id = ''): void
    {
        $examId   = (int) ($id !== '' ? $id : $request->query('id', 0));
        $tenantId = current_tenant_id();

        if ($examId <= 0) {
            $latest = $this->db->queryOne("SELECT id FROM `examinations` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL ORDER BY `id` DESC LIMIT 1", ['tid' => $tenantId]);
            if ($latest) {
                $examId = (int) $latest['id'];
            }
        }

        $exam = $this->db->queryOne(
            "SELECT ex.*, b.name AS batch_name, s.name AS subject_name, br.name AS branch_name
             FROM `examinations` ex
             JOIN `batches` b ON ex.batch_id = b.id
             JOIN `subjects` s ON ex.subject_id = s.id
             JOIN `branches` br ON ex.branch_id = br.id
             WHERE ex.`id` = :id AND ex.`tenant_id` = :tid AND ex.`deleted_at` IS NULL",
            ['id' => $examId, 'tid' => $tenantId]
        );

        if (!$exam) {
            $this->redirect(url('examinations'), 'error', 'Examination not found.');
            return;
        }

        $results = $this->db->query(
            "SELECT em.*, st.full_name AS student_name, st.admission_number, st.phone
             FROM `exam_marks` em
             JOIN `students` st ON em.student_id = st.id
             WHERE em.`examination_id` = :eid AND em.`tenant_id` = :tid
             ORDER BY CASE WHEN em.is_absent = 1 THEN 1 ELSE 0 END ASC, em.rank_in_batch ASC, em.marks_obtained DESC",
            ['eid' => $examId, 'tid' => $tenantId]
        );

        $summary = $this->db->queryOne(
            "SELECT 
                COUNT(*) AS total_candidates,
                SUM(CASE WHEN is_absent = 0 THEN 1 ELSE 0 END) AS present_count,
                SUM(CASE WHEN is_absent = 1 THEN 1 ELSE 0 END) AS absent_count,
                SUM(CASE WHEN status = 'pass' THEN 1 ELSE 0 END) AS pass_count,
                SUM(CASE WHEN status = 'fail' THEN 1 ELSE 0 END) AS fail_count,
                AVG(CASE WHEN is_absent = 0 THEN marks_obtained ELSE NULL END) AS average_marks,
                MAX(CASE WHEN is_absent = 0 THEN marks_obtained ELSE NULL END) AS highest_marks,
                MIN(CASE WHEN is_absent = 0 THEN marks_obtained ELSE NULL END) AS lowest_marks,
                SUM(CASE WHEN grade = 'A' THEN 1 ELSE 0 END) AS grade_a,
                SUM(CASE WHEN grade = 'B' THEN 1 ELSE 0 END) AS grade_b,
                SUM(CASE WHEN grade = 'C' THEN 1 ELSE 0 END) AS grade_c,
                SUM(CASE WHEN grade = 'S' THEN 1 ELSE 0 END) AS grade_s,
                SUM(CASE WHEN grade = 'F' THEN 1 ELSE 0 END) AS grade_f
             FROM `exam_marks`
             WHERE `examination_id` = :eid AND `tenant_id` = :tid",
            ['eid' => $examId, 'tid' => $tenantId]
        );

        $this->render('examinations/results', [
            'title'      => 'Results — ' . $exam['name'],
            'activeMenu' => 'examinations',
            'exam'       => $exam,
            'results'    => $results,
            'summary'    => $summary,
        ]);
    }

    public function publish(Request $request): void
    {
        $examId   = (int) $request->input('id');
        $tenantId = current_tenant_id();

        $this->db->execute(
            "UPDATE `examinations` SET `status` = 'published' WHERE `id` = :id AND `tenant_id` = :tid",
            ['id' => $examId, 'tid' => $tenantId]
        );

        $this->audit->log('exam_published', 'examinations', $examId, "Results for exam #{$examId} published to student & parent portals");

        $this->redirect(url("examinations/results?id={$examId}"), 'success', 'Results published successfully to Student and Parent portals.');
    }
}

