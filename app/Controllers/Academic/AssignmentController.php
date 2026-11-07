<?php

declare(strict_types=1);

namespace App\Controllers\Academic;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Assignment;
use App\Services\AuditService;

class AssignmentController extends Controller
{
    private Assignment $assignmentModel;
    private Database $db;
    private AuditService $audit;

    public function __construct()
    {
        $this->assignmentModel = new Assignment();
        $this->db              = Database::getInstance();
        $this->audit           = new AuditService();
    }

    public function index(Request $request): void
    {
        $tenantId = current_tenant_id();
        $batchId  = (int) $request->query('batch_id', 0);
        $search   = trim((string) $request->query('search', ''));

        $sql = "SELECT a.*, b.name AS batch_name, s.name AS subject_name, t.full_name AS teacher_name,
                       (SELECT COUNT(*) FROM `enrollments` e WHERE e.batch_id = a.batch_id AND e.status = 'active') AS total_students
                FROM `assignments` a
                JOIN `batches` b ON a.batch_id = b.id
                JOIN `subjects` s ON a.subject_id = s.id
                LEFT JOIN `teachers` t ON a.teacher_id = t.id
                WHERE a.`tenant_id` = :tid";
        $params = ['tid' => $tenantId];

        if ($batchId > 0) {
            $sql .= " AND a.`batch_id` = :bid";
            $params['bid'] = $batchId;
        }

        if (!empty($search)) {
            $sql .= " AND (a.`title` LIKE :s1 OR a.`instructions` LIKE :s2)";
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
        }

        $sql .= " ORDER BY a.`due_date` DESC, a.`id` DESC";
        $assignments = $this->db->query($sql, $params);

        $batches = $this->db->query("SELECT id, name FROM `batches` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL", ['tid' => $tenantId]);

        $this->render('academic/assignments/index', [
            'title'       => 'Assignments & Homework',
            'activeMenu'  => 'assignments',
            'assignments' => $assignments,
            'batches'     => $batches,
            'filters'     => [
                'batch_id' => $batchId,
                'search'   => $search,
            ],
        ]);
    }

    public function create(Request $request): void
    {
        $tenantId = current_tenant_id();
        $batches  = $this->db->query("SELECT id, name, subject_id, teacher_id FROM `batches` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL", ['tid' => $tenantId]);
        $subjects = $this->db->query("SELECT id, name FROM `subjects` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL", ['tid' => $tenantId]);
        $teachers = $this->db->query("SELECT id, full_name FROM `teachers` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL", ['tid' => $tenantId]);

        $this->render('academic/assignments/create', [
            'title'      => 'Create New Assignment',
            'activeMenu' => 'assignments',
            'batches'    => $batches,
            'subjects'   => $subjects,
            'teachers'   => $teachers,
        ]);
    }

    public function store(Request $request): void
    {
        $rules = [
            'title'        => 'required|min:3|max:200',
            'batch_id'     => 'required|numeric',
            'due_date'     => 'required',
            'instructions' => 'required|min:5',
            'max_marks'    => 'required|numeric',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        $tenantId     = current_tenant_id();
        $batchId      = (int) $request->input('batch_id');
        $title        = trim((string) $request->input('title'));
        $instructions = trim((string) $request->input('instructions'));
        $dueDate      = (string) $request->input('due_date');
        $maxMarks     = (float) $request->input('max_marks', 100);

        $batch = $this->db->queryOne("SELECT subject_id, teacher_id FROM `batches` WHERE `id` = :id AND `tenant_id` = :tid", ['id' => $batchId, 'tid' => $tenantId]);
        if (!$batch) {
            $this->back('error', 'Selected class batch not found.');
            return;
        }

        $subjectId = (int) $batch['subject_id'];
        $teacherId = !empty($request->input('teacher_id')) ? (int) $request->input('teacher_id') : ((int) ($batch['teacher_id'] ?? 1));

        $attachmentPath = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['attachment'];
            $maxBytes = (int) env('UPLOAD_MAX_SIZE_MB', 10) * 1024 * 1024;
            if ($file['size'] > $maxBytes) {
                $this->back('error', 'Uploaded attachment exceeds the 10 MB limit.');
                return;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $safeName = 'assignment_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destination = STORAGE_PATH . '/uploads/assignments/' . $safeName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $attachmentPath = 'storage/uploads/assignments/' . $safeName;
            }
        }

        $id = $this->assignmentModel->create([
            'tenant_id'       => $tenantId,
            'batch_id'        => $batchId,
            'subject_id'      => $subjectId,
            'teacher_id'      => $teacherId,
            'title'           => $title,
            'instructions'    => $instructions,
            'due_date'        => $dueDate,
            'max_marks'       => $maxMarks,
            'attachment_path' => $attachmentPath,
        ]);

        $this->audit->log('assignment_created', 'assignments', $id, "Assignment '{$title}' created for batch #{$batchId}");

        $this->redirect(url('academic/assignments'), 'success', 'Assignment created and published to class batch successfully.');
    }

    public function show(Request $request, string $id = ''): void
    {
        $assignmentId = (int) ($id !== '' ? $id : $request->query('id', 0));
        $tenantId     = current_tenant_id();

        $assignment = $this->db->queryOne(
            "SELECT a.*, b.name AS batch_name, s.name AS subject_name, t.full_name AS teacher_name
             FROM `assignments` a
             JOIN `batches` b ON a.batch_id = b.id
             JOIN `subjects` s ON a.subject_id = s.id
             LEFT JOIN `teachers` t ON a.teacher_id = t.id
             WHERE a.`id` = :id AND a.`tenant_id` = :tid",
            ['id' => $assignmentId, 'tid' => $tenantId]
        );

        if (!$assignment) {
            $this->redirect(url('academic/assignments'), 'error', 'Assignment not found.');
            return;
        }

        $students = $this->db->query(
            "SELECT st.id, st.admission_number, st.full_name, st.phone
             FROM `enrollments` e
             JOIN `students` st ON e.student_id = st.id
             WHERE e.`batch_id` = :bid AND e.`tenant_id` = :tid AND e.`status` = 'active' AND st.`deleted_at` IS NULL
             ORDER BY st.full_name ASC",
            ['bid' => $assignment['batch_id'], 'tid' => $tenantId]
        );

        $submissions = $this->db->query(
            "SELECT sub.*, st.full_name AS student_name, st.admission_number, u.name AS graded_by_name
             FROM `student_submissions` sub
             JOIN `students` st ON sub.student_id = st.id
             LEFT JOIN `users` u ON sub.graded_by = u.id
             WHERE sub.`assignment_id` = :aid AND sub.`tenant_id` = :tid
             ORDER BY sub.`submitted_at` DESC",
            ['aid' => $assignmentId, 'tid' => $tenantId]
        );

        $this->render('academic/assignments/show', [
            'title'       => $assignment['title'],
            'activeMenu'  => 'assignments',
            'assignment'  => $assignment,
            'students'    => $students,
            'submissions' => $submissions,
        ]);
    }

    public function submit(Request $request): void
    {
        $tenantId     = current_tenant_id();
        $assignmentId = (int) $request->input('assignment_id');
        $studentId    = (int) $request->input('student_id');
        $solutionText = trim((string) $request->input('submission_text'));

        $user = auth_user();
        if ($user && ($user['role_name'] ?? '') === 'student') {
            $stu = $this->db->queryOne("SELECT id FROM `students` WHERE `email` = :em AND `tenant_id` = :tid", [
                'em'  => $user['email'] ?? '',
                'tid' => $tenantId,
            ]);
            if ($stu) {
                $studentId = (int) $stu['id'];
            }
        }

        if ($studentId <= 0 || $assignmentId <= 0) {
            $this->back('error', 'Valid student and assignment are required for submission.');
            return;
        }

        $existing = $this->db->queryOne(
            "SELECT id FROM `student_submissions` WHERE `assignment_id` = :aid AND `student_id` = :sid AND `tenant_id` = :tid",
            ['aid' => $assignmentId, 'sid' => $studentId, 'tid' => $tenantId]
        );

        if ($existing) {
            $this->db->execute(
                "UPDATE `student_submissions` SET `submission_text` = :txt, `submitted_at` = NOW() WHERE `id` = :id AND `tenant_id` = :tid",
                ['txt' => $solutionText, 'id' => $existing['id'], 'tid' => $tenantId]
            );
        } else {
            $this->db->insert('student_submissions', [
                'tenant_id'       => $tenantId,
                'assignment_id'   => $assignmentId,
                'student_id'      => $studentId,
                'submission_text' => $solutionText,
                'submitted_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        $this->redirect(url("academic/assignments/{$assignmentId}"), 'success', 'Coursework solution submitted successfully!');
    }

    public function grade(Request $request): void
    {
        $tenantId     = current_tenant_id();
        $submissionId = (int) $request->input('submission_id');
        $assignmentId = (int) $request->input('assignment_id');
        $marks        = (float) $request->input('marks_awarded', 0.0);
        $feedback     = trim((string) $request->input('feedback'));

        $user = auth_user();
        $userId = $user['id'] ?? 1;

        $this->db->execute(
            "UPDATE `student_submissions` SET `marks_awarded` = :m, `feedback` = :fb, `graded_by` = :uid, `graded_at` = NOW() WHERE `id` = :id AND `tenant_id` = :tid",
            [
                'm'   => $marks,
                'fb'  => $feedback,
                'uid' => $userId,
                'id'  => $submissionId,
                'tid' => $tenantId,
            ]
        );

        $this->redirect(url("academic/assignments/{$assignmentId}"), 'success', 'Submission graded successfully with feedback saved.');
    }

    public function download(Request $request, string $id = ''): void
    {
        $assignmentId = (int) ($id !== '' ? $id : $request->query('id', 0));
        $tenantId     = current_tenant_id();

        $assignment = $this->db->queryOne(
            "SELECT * FROM `assignments` WHERE `id` = :id AND `tenant_id` = :tid",
            ['id' => $assignmentId, 'tid' => $tenantId]
        );

        if (!$assignment || empty($assignment['attachment_path'])) {
            $this->redirect(url('academic/assignments'), 'error', 'Attachment file not found.');
            return;
        }

        $fullPath = ROOT_PATH . '/' . ltrim($assignment['attachment_path'], '/');
        if (!file_exists($fullPath)) {
            $this->redirect(url('academic/assignments'), 'error', 'Attachment file is missing from storage.');
            return;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fullPath) . '"');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
}
