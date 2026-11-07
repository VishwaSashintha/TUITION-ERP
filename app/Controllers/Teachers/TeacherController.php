<?php

declare(strict_types=1);

namespace App\Controllers\Teachers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AuditService;

class TeacherController extends Controller
{
    private Teacher $teacherModel;
    private User $userModel;
    private AuditService $audit;
    private Database $db;

    public function __construct()
    {
        $this->teacherModel = new Teacher();
        $this->userModel    = new User();
        $this->audit         = new AuditService();
        $this->db            = Database::getInstance();
    }

    public function index(Request $request): void
    {
        $tenantId = current_tenant_id();
        $teachers = $this->db->query(
            "SELECT t.*, u.username, br.name AS branch_name,
                    (SELECT COUNT(*) FROM `batches` b WHERE b.teacher_id = t.id AND b.status = 'active' AND b.deleted_at IS NULL) AS active_batches_count
             FROM `teachers` t 
             LEFT JOIN `users` u ON t.user_id = u.id 
             LEFT JOIN `branches` br ON t.branch_id = br.id 
             WHERE t.`tenant_id` = :tid AND t.`deleted_at` IS NULL 
             ORDER BY t.`id` DESC",
            ['tid' => $tenantId]
        );

        $this->render('teachers/index', [
            'title'      => 'Teacher Directory',
            'activeMenu' => 'teachers',
            'teachers'   => $teachers,
        ]);
    }

    public function create(Request $request): void
    {
        $tenantId = current_tenant_id();
        $branches = $this->db->query("SELECT id, name FROM `branches` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL", ['tid' => $tenantId]);

        $this->render('teachers/create', [
            'title'      => 'Register New Teacher',
            'activeMenu' => 'teachers',
            'branches'   => $branches,
        ]);
    }

    public function store(Request $request): void
    {
        $rules = [
            'first_name'         => 'required|min:2|max:100',
            'last_name'          => 'required|min:2|max:100',
            'phone'              => 'required|min:9|max:20',
            'email'              => 'required|email',
            'compensation_model' => 'required',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        try {
            $teacherId = $this->db->transaction(function () use ($request) {
                $tenantId = current_tenant_id();
                $tenant = $this->db->queryOne("SELECT code FROM `tenants` WHERE `id` = :tid", ['tid' => $tenantId]);
                $tCode = strtoupper($tenant['code'] ?? 'T');

                $last = $this->db->queryOne("SELECT employee_id FROM `teachers` WHERE `tenant_id` = :tid ORDER BY `id` DESC LIMIT 1", ['tid' => $tenantId]);
                $nextSeq = 1;
                if ($last) {
                    $parts = explode('-T', $last['employee_id']);
                    $nextSeq = ((int) end($parts)) + 1;
                }
                $empId = "{$tCode}-T" . str_pad((string)$nextSeq, 3, '0', STR_PAD_LEFT);

                $fullName = trim($request->input('first_name') . ' ' . $request->input('last_name'));

                //Teachers
                $roleTeacher = $this->db->queryOne("SELECT id FROM `roles` WHERE `name` = 'teacher'");
                $roleTeacherId = $roleTeacher ? (int)$roleTeacher['id'] : 8;

                $defaultPasswordHash = password_hash('Password@123', PASSWORD_BCRYPT);
                $userId = $this->userModel->create([
                    'tenant_id'     => $tenantId,
                    'branch_id'     => $request->input('branch_id') ?: null,
                    'role_id'       => $roleTeacherId,
                    'name'          => $fullName,
                    'username'      => strtolower(trim($request->input('first_name'))) . '.' . substr(uniqid(), -3),
                    'email'         => $request->input('email'),
                    'phone'         => $request->input('phone'),
                    'password_hash' => $defaultPasswordHash,
                    'status'        => 'active',
                ]);

                //Teachers
                $teacherId = $this->teacherModel->create([
                    'tenant_id'            => $tenantId,
                    'user_id'              => $userId,
                    'branch_id'            => $request->input('branch_id') ?: null,
                    'employee_id'          => $empId,
                    'first_name'           => $request->input('first_name'),
                    'last_name'            => $request->input('last_name'),
                    'full_name'            => $fullName,
                    'phone'                => $request->input('phone'),
                    'email'                => $request->input('email'),
                    'qualifications'       => $request->input('qualifications') ?: null,
                    'specialization'       => $request->input('specialization') ?: null,
                    'join_date'            => $request->input('join_date') ?: date('Y-m-d'),
                    'status'               => 'active',
                    'compensation_model'   => $request->input('compensation_model'),
                    'default_rate'         => (float) $request->input('default_rate', 0),
                    'default_percentage'   => (float) $request->input('default_percentage', 0),
                    'bank_account_details' => $request->input('bank_account_details') ?: null,
                ]);

                $this->audit->log('teacher_created', 'teachers', $teacherId, "Teacher {$fullName} registered with ID: {$empId}");

                return $teacherId;
            });

            $this->redirect(url("teachers/{$teacherId}"), 'success', 'Teacher registered successfully with login account.');
        } catch (\Throwable $e) {
            $this->back('error', 'Failed to register teacher: ' . $e->getMessage(), $request->all());
        }
    }

    public function show(Request $request, string $id = ''): void
    {
        $teacherId = (int) ($id !== '' ? $id : $request->query('id', 0));
        $teacher = $this->teacherModel->find($teacherId);
        if (!$teacher) {
            $this->redirect(url('teachers'), 'error', 'Teacher not found.');
            return;
        }

        $tenantId = current_tenant_id();

        $batches = $this->teacherModel->getAssignedBatches($teacherId);

        $rules = $this->db->query(
            "SELECT * FROM `teacher_payment_rules` WHERE `teacher_id` = :tid AND `tenant_id` = :tenant_id ORDER BY `id` DESC",
            ['tid' => $teacherId, 'tenant_id' => $tenantId]
        );

        $vouchers = $this->db->query(
            "SELECT * FROM `teacher_payments` WHERE `teacher_id` = :tid AND `tenant_id` = :tenant_id ORDER BY `id` DESC",
            ['tid' => $id, 'tenant_id' => $tenantId]
        );

        $this->render('teachers/show', [
            'title'      => $teacher['full_name'],
            'activeMenu' => 'teachers',
            'teacher'    => $teacher,
            'batches'    => $batches,
            'rules'      => $rules,
            'vouchers'   => $vouchers,
        ]);
    }
}

