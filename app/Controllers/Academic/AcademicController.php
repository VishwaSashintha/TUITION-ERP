<?php

declare(strict_types=1);

namespace App\Controllers\Academic;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Subject;
use App\Models\Batch;
use App\Models\Enrollment;
use App\Services\AuditService;

class AcademicController extends Controller
{
    private Subject $subjectModel;
    private Batch $batchModel;
    private Enrollment $enrollmentModel;
    private AuditService $audit;
    private Database $db;

    public function __construct()
    {
        $this->subjectModel    = new Subject();
        $this->batchModel      = new Batch();
        $this->enrollmentModel = new Enrollment();
        $this->audit           = new AuditService();
        $this->db              = Database::getInstance();
    }

    /**
     * Subject management view.
     */
    public function subjects(Request $request): void
    {
        $tenantId = current_tenant_id();
        $subjects = $this->db->query(
            "SELECT s.*, 
                    (SELECT COUNT(*) FROM `batches` b WHERE b.subject_id = s.id AND b.status = 'active' AND b.deleted_at IS NULL) AS batches_count 
             FROM `subjects` s 
             WHERE s.`tenant_id` = :tid AND s.`deleted_at` IS NULL 
             ORDER BY s.`id` DESC",
            ['tid' => $tenantId]
        );

        $this->render('academic/subjects', [
            'title'      => 'Academic Subjects',
            'activeMenu' => 'subjects',
            'subjects'   => $subjects,
        ]);
    }

    /**
     * Store new subject.
     */
    public function storeSubject(Request $request): void
    {
        $rules = [
            'name' => 'required|min:2|max:100',
            'code' => 'required|min:2|max:30',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        try {
            $tenantId = current_tenant_id();
            $code = strtoupper(trim((string)$request->input('code')));

            // Duplicate code check within tenant
            $exists = $this->db->queryOne("SELECT id FROM `subjects` WHERE `tenant_id` = :tid AND `code` = :code", ['tid' => $tenantId, 'code' => $code]);
            if ($exists) {
                $this->back('error', "Subject code '{$code}' already exists.", $request->all());
                return;
            }

            $id = $this->subjectModel->create([
                'tenant_id'   => $tenantId,
                'name'        => $request->input('name'),
                'code'        => $code,
                'grade_level' => $request->input('grade_level') ?: null,
                'description' => $request->input('description') ?: null,
                'is_active'   => 1,
            ]);

            $this->audit->log('subject_created', 'subjects', $id, "Subject {$request->input('name')} created.");
            $this->redirect(url('academic/subjects'), 'success', 'Subject created successfully.');
        } catch (\Throwable $e) {
            $this->back('error', 'Failed to create subject: ' . $e->getMessage(), $request->all());
        }
    }

    /**
     * Batches list with seat capacity indicators.
     */
    public function batches(Request $request): void
    {
        $batches = $this->batchModel->allWithDetails();

        $this->render('academic/batches', [
            'title'      => 'Classes & Batches',
            'activeMenu' => 'batches',
            'batches'    => $batches,
        ]);
    }

    /**
     * Create batch form.
     */
    public function createBatch(Request $request): void
    {
        $tenantId = current_tenant_id();
        $subjects = $this->db->query("SELECT id, name, grade_level FROM `subjects` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL", ['tid' => $tenantId]);
        $teachers = $this->db->query("SELECT id, full_name FROM `teachers` WHERE `tenant_id` = :tid AND `status` = 'active' AND `deleted_at` IS NULL", ['tid' => $tenantId]);
        $branches = $this->db->query("SELECT id, name FROM `branches` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL", ['tid' => $tenantId]);

        $this->render('academic/batch_create', [
            'title'      => 'Create New Class Batch',
            'activeMenu' => 'batches',
            'subjects'   => $subjects,
            'teachers'   => $teachers,
            'branches'   => $branches,
        ]);
    }

    /**
     * Store new batch.
     */
    public function storeBatch(Request $request): void
    {
        $rules = [
            'name'         => 'required|min:3|max:150',
            'code'         => 'required|min:2|max:30',
            'subject_id'   => 'required|numeric',
            'teacher_id'   => 'required|numeric',
            'branch_id'    => 'required|numeric',
            'monthly_fee'  => 'required|numeric',
            'max_capacity' => 'required|numeric',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        try {
            $tenantId = current_tenant_id();
            $acadYear = $this->db->queryOne("SELECT id FROM `academic_years` WHERE `tenant_id` = :tid AND `is_active` = 1 LIMIT 1", ['tid' => $tenantId]);

            $id = $this->batchModel->create([
                'tenant_id'        => $tenantId,
                'branch_id'        => (int) $request->input('branch_id'),
                'subject_id'       => (int) $request->input('subject_id'),
                'teacher_id'       => (int) $request->input('teacher_id'),
                'academic_year_id' => $acadYear ? (int)$acadYear['id'] : null,
                'name'             => $request->input('name'),
                'code'             => strtoupper(trim((string)$request->input('code'))),
                'classroom'        => $request->input('classroom') ?: null,
                'max_capacity'     => (int) $request->input('max_capacity'),
                'monthly_fee'      => (float) $request->input('monthly_fee'),
                'start_date'       => $request->input('start_date') ?: date('Y-m-d'),
                'end_date'         => $request->input('end_date') ?: null,
                'status'           => 'active',
            ]);

            $this->audit->log('batch_created', 'batches', $id, "Batch {$request->input('name')} created.");
            $this->redirect(url("academic/batches/{$id}"), 'success', 'Class batch created successfully.');
        } catch (\Throwable $e) {
            $this->back('error', 'Failed to create batch: ' . $e->getMessage(), $request->all());
        }
    }

    public function showBatch(Request $request, string $id = ''): void
    {
        $this->batchShow($request, $id);
    }

    /**
     * Show batch details and student roster with capacity controls.
     */
    public function batchShow(Request $request, string $id = ''): void
    {
        $batchId = (int) ($id !== '' ? $id : $request->query('id', 0));
        $batch = $this->batchModel->findWithDetails($batchId);

        if (!$batch) {
            $this->redirect(url('academic/batches'), 'error', 'Batch not found.');
            return;
        }

        $tenantId = current_tenant_id();
        $students = $this->enrollmentModel->getBatchStudents($batchId);

        // Fetch students not yet enrolled in this batch for the quick-enroll modal
        $availableStudents = $this->db->query(
            "SELECT s.id, s.admission_number, s.full_name, s.grade_level 
             FROM `students` s 
             WHERE s.`tenant_id` = :tid 
             AND s.`status` = 'active' 
             AND s.`deleted_at` IS NULL 
             AND s.`id` NOT IN (SELECT student_id FROM `enrollments` WHERE batch_id = :bid AND status = 'active' AND deleted_at IS NULL)
             ORDER BY s.`full_name` ASC",
            ['tid' => $tenantId, 'bid' => $batchId]
        );

        $this->render('academic/batch_show', [
            'title'             => $batch['name'],
            'activeMenu'        => 'batches',
            'batch'             => $batch,
            'students'          => $students,
            'availableStudents' => $availableStudents,
        ]);
    }

    /**
     * Enroll student into batch (enforcing maximum batch capacity).
     */
    public function enrollStudent(Request $request): void
    {
        $batchId   = (int) $request->input('batch_id');
        $studentId = (int) $request->input('student_id');

        if (!$batchId || !$studentId) {
            $this->back('error', 'Invalid batch or student selection.');
            return;
        }

        $batch = $this->batchModel->findWithDetails($batchId);
        if (!$batch) {
            $this->back('error', 'Batch not found.');
            return;
        }

        // Capacity check
        $enrolledCount = (int) $batch['enrolled_students_count'];
        $maxCapacity   = (int) $batch['max_capacity'];

        if ($enrolledCount >= $maxCapacity) {
            $this->back('error', "Batch capacity reached ({$enrolledCount}/{$maxCapacity} seats). Over-enrollment is restricted.");
            return;
        }

        try {
            $tenantId = current_tenant_id();
            $acadYear = $this->db->queryOne("SELECT id FROM `academic_years` WHERE `tenant_id` = :tid AND `is_active` = 1 LIMIT 1", ['tid' => $tenantId]);

            $this->db->execute(
                "INSERT INTO `enrollments` (`tenant_id`, `student_id`, `batch_id`, `academic_year_id`, `enrollment_date`, `status`) 
                 VALUES (:tid, :sid, :bid, :ayid, NOW(), 'active')",
                [
                    'tid'  => $tenantId,
                    'sid'  => $studentId,
                    'bid'  => $batchId,
                    'ayid' => $acadYear ? (int)$acadYear['id'] : null,
                ]
            );

            $this->audit->log('student_enrolled', 'enrollments', $batchId, "Student #{$studentId} enrolled in Batch #{$batchId}");
            $this->redirect(url("academic/batches/{$batchId}"), 'success', 'Student enrolled into batch successfully.');
        } catch (\Throwable $e) {
            $this->back('error', 'Enrollment failed: ' . $e->getMessage());
        }
    }
}

