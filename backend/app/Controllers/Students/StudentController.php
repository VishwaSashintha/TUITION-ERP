<?php

declare(strict_types=1);

namespace App\Controllers\Students;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Student;
use App\Models\Guardian;
use App\Services\TenantManager;
use App\Services\AuditService;

class StudentController extends Controller
{
    private Student $studentModel;
    private Guardian $guardianModel;
    private TenantManager $tenantManager;
    private AuditService $audit;
    private Database $db;

    public function __construct()
    {
        $this->studentModel   = new Student();
        $this->guardianModel  = new Guardian();
        $this->tenantManager  = new TenantManager();
        $this->audit          = new AuditService();
        $this->db             = Database::getInstance();
    }

    public function index(Request $request): void
    {
        $term     = $request->query('term');
        $branchId = $request->query('branch_id') ? (int) $request->query('branch_id') : null;
        $grade    = $request->query('grade');
        $status   = $request->query('status');
        $page     = (int) $request->query('page', 1);

        $result   = $this->studentModel->search($term, $branchId, $grade, $status, $page, 15);
        $branches = $this->db->query("SELECT id, name FROM `branches` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL", ['tid' => current_tenant_id()]);

        $this->render('students/index', [
            'title'      => 'Student Directory',
            'activeMenu' => 'students',
            'students'   => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['current_page'],
            'lastPage'   => $result['last_page'],
            'branches'   => $branches,
            'filters'    => [
                'term'      => $term,
                'branch_id' => $branchId,
                'grade'     => $grade,
                'status'    => $status,
            ],
        ]);
    }

    public function create(Request $request): void
    {
        //Students
        if (!$this->tenantManager->canAddStudent()) {
            $this->redirect(url('students'), 'error', 'Subscription limit reached: Please upgrade your subscription plan to add more students.');
            return;
        }

        $tenantId = current_tenant_id();
        $branches = $this->db->query("SELECT id, name FROM `branches` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL", ['tid' => $tenantId]);
        $batches  = $this->db->query("SELECT b.id, b.name, b.monthly_fee, s.name AS subject_name FROM `batches` b JOIN `subjects` s ON b.subject_id = s.id WHERE b.`tenant_id` = :tid AND b.`status` = 'active' AND b.`deleted_at` IS NULL", ['tid' => $tenantId]);

        $this->render('students/create', [
            'title'      => 'New Student Admission',
            'activeMenu' => 'students',
            'branches'   => $branches,
            'batches'    => $batches,
        ]);
    }

    public function store(Request $request): void
    {
        if (!$this->tenantManager->canAddStudent()) {
            $this->back('error', 'Student limit reached for current subscription plan.', $request->all());
            return;
        }

        $rules = [
            'first_name'    => 'required|min:2|max:100',
            'last_name'     => 'required|min:2|max:100',
            'branch_id'     => 'required|numeric',
            'admission_date'=> 'required',
            'guardian_name' => 'required|min:3|max:150',
            'guardian_phone'=> 'required|min:9|max:20',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        try {
            $studentId = $this->db->transaction(function () use ($request) {
                $tenantId = current_tenant_id();

                $admNo = $request->input('admission_number');
                if (empty($admNo)) {
                    $tenant = $this->db->queryOne("SELECT code FROM `tenants` WHERE `id` = :tid", ['tid' => $tenantId]);
                    $tCode = strtoupper($tenant['code'] ?? 'STU');
                    $last = $this->db->queryOne("SELECT admission_number FROM `students` WHERE `tenant_id` = :tid ORDER BY `id` DESC LIMIT 1", ['tid' => $tenantId]);
                    $nextSeq = 1;
                    if ($last) {
                        $parts = explode('-', $last['admission_number']);
                        $nextSeq = ((int) end($parts)) + 1;
                    }
                    $admNo = "{$tCode}-" . date('Y') . "-" . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
                }

                $fullName = trim($request->input('first_name') . ' ' . $request->input('last_name'));

                //Students
                $studentId = $this->studentModel->create([
                    'tenant_id'              => $tenantId,
                    'branch_id'              => (int) $request->input('branch_id'),
                    'admission_number'       => $admNo,
                    'first_name'             => $request->input('first_name'),
                    'last_name'              => $request->input('last_name'),
                    'full_name'              => $fullName,
                    'preferred_name'         => $request->input('preferred_name') ?: $request->input('first_name'),
                    'dob'                    => $request->input('dob') ?: null,
                    'gender'                 => $request->input('gender') ?: 'other',
                    'phone'                  => $request->input('phone') ?: null,
                    'email'                  => $request->input('email') ?: null,
                    'address'                => $request->input('address') ?: null,
                    'school'                 => $request->input('school') ?: null,
                    'grade_level'            => $request->input('grade_level') ?: null,
                    'status'                 => 'active',
                    'admission_date'         => $request->input('admission_date'),
                    'emergency_contact_name' => $request->input('guardian_name'),
                    'emergency_contact_phone'=> $request->input('guardian_phone'),
                    'notes'                  => $request->input('notes') ?: null,
                ]);

                $gParts = explode(' ', (string) $request->input('guardian_name'));
                $guardianId = $this->guardianModel->create([
                    'tenant_id'    => $tenantId,
                    'first_name'   => $gParts[0],
                    'last_name'    => $gParts[1] ?? '',
                    'full_name'    => $request->input('guardian_name'),
                    'relationship' => $request->input('guardian_relationship') ?: 'Parent',
                    'phone'        => $request->input('guardian_phone'),
                    'email'        => $request->input('guardian_email') ?: null,
                    'occupation'   => $request->input('guardian_occupation') ?: null,
                ]);

                //Students
                $this->db->execute(
                    "INSERT INTO `student_guardians` (`tenant_id`, `student_id`, `guardian_id`, `relationship`, `is_primary`, `is_emergency`) 
                     VALUES (:tid, :sid, :gid, :rel, 1, 1)",
                    [
                        'tid' => $tenantId,
                        'sid' => $studentId,
                        'gid' => $guardianId,
                        'rel' => $request->input('guardian_relationship') ?: 'Parent',
                    ]
                );

                $batchId = $request->input('batch_id');
                if (!empty($batchId)) {
                    $acadYear = $this->db->queryOne("SELECT id FROM `academic_years` WHERE `tenant_id` = :tid AND `is_active` = 1 LIMIT 1", ['tid' => $tenantId]);
                    $this->db->execute(
                        "INSERT INTO `enrollments` (`tenant_id`, `student_id`, `batch_id`, `academic_year_id`, `enrollment_date`, `status`) 
                         VALUES (:tid, :sid, :bid, :ayid, :date, 'active')",
                        [
                            'tid'  => $tenantId,
                            'sid'  => $studentId,
                            'bid'  => (int)$batchId,
                            'ayid' => $acadYear ? (int)$acadYear['id'] : null,
                            'date' => $request->input('admission_date'),
                        ]
                    );
                }

                $this->audit->log('student_admitted', 'students', $studentId, "Student {$fullName} admitted with Admission No: {$admNo}");

                return $studentId;
            });

            $this->redirect(url("students/show?id={$studentId}"), 'success', 'Student admitted successfully!');
        } catch (\Throwable $e) {
            $this->back('error', 'Failed to admit student: ' . $e->getMessage(), $request->all());
        }
    }

    public function show(Request $request, string $id = ''): void
    {
        $studentId = (int) ($id !== '' ? $id : $request->query('id', 0));
        $student = $this->studentModel->find($studentId);

        if (!$student) {
            $this->redirect(url('students'), 'error', 'Student not found.');
            return;
        }

        $tenantId = current_tenant_id();

        //Guardians
        $guardians = $this->guardianModel->getLinkedStudents($studentId);

        $enrollments = $this->db->query(
            "SELECT e.*, b.name AS batch_name, b.monthly_fee, s.name AS subject_name, t.full_name AS teacher_name 
             FROM `enrollments` e 
             JOIN `batches` b ON e.batch_id = b.id 
             JOIN `subjects` s ON b.subject_id = s.id 
             JOIN `teachers` t ON b.teacher_id = t.id 
             WHERE e.`student_id` = :sid AND e.`tenant_id` = :tid AND e.`deleted_at` IS NULL",
            ['sid' => $studentId, 'tid' => $tenantId]
        );

        //Invoices
        $invoices = $this->db->query(
            "SELECT * FROM `invoices` WHERE `student_id` = :sid AND `tenant_id` = :tid AND `deleted_at` IS NULL ORDER BY `id` DESC",
            ['sid' => $studentId, 'tid' => $tenantId]
        );

        $payments = $this->db->query(
            "SELECT * FROM `payments` WHERE `student_id` = :sid AND `tenant_id` = :tid ORDER BY `id` DESC",
            ['sid' => $studentId, 'tid' => $tenantId]
        );

        //Attendance
        $attSummary = (new \App\Models\AttendanceRecord())->getStudentSummary($studentId);

        $examMarks = $this->db->query(
            "SELECT em.*, e.name AS exam_name, e.max_marks, s.name AS subject_name 
             FROM `exam_marks` em 
             JOIN `examinations` e ON em.examination_id = e.id 
             JOIN `subjects` s ON e.subject_id = s.id 
             WHERE em.`student_id` = :sid AND em.`tenant_id` = :tid ORDER BY e.`exam_date` DESC",
            ['sid' => $studentId, 'tid' => $tenantId]
        );

        $this->render('students/show', [
            'title'       => $student['full_name'],
            'activeMenu'  => 'students',
            'student'     => $student,
            'guardians'   => $guardians,
            'enrollments' => $enrollments,
            'invoices'    => $invoices,
            'payments'    => $payments,
            'attSummary'  => $attSummary,
            'examMarks'   => $examMarks,
        ]);
    }

    public function idCard(Request $request, string $id): void
    {
        $student = $this->studentModel->find((int)$id);
        if (!$student) {
            $this->redirect(url('students'), 'error', 'Student not found.');
            return;
        }

        $tenant = $this->db->queryOne("SELECT * FROM `tenants` WHERE `id` = :tid", ['tid' => current_tenant_id()]);
        $branch = $this->db->queryOne("SELECT * FROM `branches` WHERE `id` = :bid", ['bid' => $student['branch_id']]);

        require VIEW_PATH . '/students/id_card.php';
    }

    public function exportCsv(Request $request): void
    {
        $tenantId = current_tenant_id();
        $students = $this->db->query(
            "SELECT s.admission_number, s.full_name, s.gender, s.phone, s.school, s.grade_level, s.status, s.admission_date, b.name AS branch_name 
             FROM `students` s 
             LEFT JOIN `branches` b ON s.branch_id = b.id 
             WHERE s.`tenant_id` = :tid AND s.`deleted_at` IS NULL 
             ORDER BY s.`id` DESC",
            ['tid' => $tenantId]
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="students_export_' . date('Ymd_His') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Admission No', 'Full Name', 'Gender', 'Phone', 'School', 'Grade', 'Status', 'Admission Date', 'Branch']);

        foreach ($students as $row) {
            fputcsv($output, [
                $row['admission_number'],
                $row['full_name'],
                ucfirst($row['gender'] ?? ''),
                $row['phone'],
                $row['school'],
                $row['grade_level'],
                ucfirst($row['status']),
                $row['admission_date'],
                $row['branch_name'],
            ]);
        }

        fclose($output);
        exit;
    }
}

