<?php

declare(strict_types=1);

namespace App\Controllers\Students;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Services\AuditService;

class EnquiryController extends Controller
{
    private Database $db;
    private AuditService $audit;

    public function __construct()
    {
        $this->db    = Database::getInstance();
        $this->audit = new AuditService();
    }

    public function index(Request $request): void
    {
        $tenantId = current_tenant_id();
        $term     = trim((string) $request->query('term', ''));
        $status   = trim((string) $request->query('status', ''));
        $source   = trim((string) $request->query('source', ''));
        $branchId = $request->query('branch_id') ? (int) $request->query('branch_id') : null;

        $where = ["e.`tenant_id` = :tid"];
        $params = ['tid' => $tenantId];

        if ($term !== '') {
            $where[] = "(e.`first_name` LIKE :term OR e.`last_name` LIKE :term OR e.`phone` LIKE :term OR e.`course_interest` LIKE :term)";
            $params['term'] = "%{$term}%";
        }
        if ($status !== '') {
            $where[] = "e.`status` = :status";
            $params['status'] = $status;
        }
        if ($source !== '') {
            $where[] = "e.`source` = :source";
            $params['source'] = $source;
        }
        if ($branchId !== null) {
            $where[] = "e.`branch_id` = :bid";
            $params['bid'] = $branchId;
        }

        $whereSql = implode(' AND ', $where);

        $enquiries = $this->db->query(
            "SELECT e.*, b.name AS branch_name, u.name AS assigned_to_name, s.admission_number AS converted_adm_no
             FROM `student_enquiries` e
             LEFT JOIN `branches` b ON e.branch_id = b.id
             LEFT JOIN `users` u ON e.assigned_to = u.id
             LEFT JOIN `students` s ON e.converted_student_id = s.id
             WHERE {$whereSql}
             ORDER BY e.`created_at` DESC",
            $params
        );

        $counts = $this->db->queryOne(
            "SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN `status` = 'new' THEN 1 ELSE 0 END) AS count_new,
                SUM(CASE WHEN `status` = 'follow_up' THEN 1 ELSE 0 END) AS count_follow_up,
                SUM(CASE WHEN `status` = 'admitted' THEN 1 ELSE 0 END) AS count_admitted,
                SUM(CASE WHEN `next_followup_date` = CURDATE() AND `status` NOT IN ('admitted', 'closed', 'lost') THEN 1 ELSE 0 END) AS due_today
             FROM `student_enquiries` WHERE `tenant_id` = :tid",
            ['tid' => $tenantId]
        );

        $branches = $this->db->query("SELECT id, name FROM `branches` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL", ['tid' => $tenantId]);

        $this->render('students/enquiries/index', [
            'title'      => 'Admissions & Student Enquiries',
            'activeMenu' => 'students',
            'enquiries'  => $enquiries,
            'counts'     => $counts,
            'branches'   => $branches,
            'filters'    => [
                'term'      => $term,
                'status'    => $status,
                'source'    => $source,
                'branch_id' => $branchId,
            ],
        ]);
    }

    public function create(Request $request): void
    {
        $tenantId = current_tenant_id();
        $branches = $this->db->query("SELECT id, name FROM `branches` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL", ['tid' => $tenantId]);
        $staff    = $this->db->query("SELECT u.id, u.name, r.display_name AS role_name FROM `users` u JOIN `roles` r ON u.role_id = r.id WHERE u.`tenant_id` = :tid AND u.`status` = 'active'", ['tid' => $tenantId]);

        $this->render('students/enquiries/create', [
            'title'      => 'Register New Student Enquiry',
            'activeMenu' => 'students',
            'branches'   => $branches,
            'staff'      => $staff,
        ]);
    }

    public function store(Request $request): void
    {
        $tenantId = current_tenant_id();

        $firstName      = trim((string) $request->input('first_name'));
        $lastName       = trim((string) $request->input('last_name'));
        $phone          = trim((string) $request->input('phone'));
        $email          = trim((string) $request->input('email')) ?: null;
        $grade          = trim((string) $request->input('grade')) ?: null;
        $courseInterest = trim((string) $request->input('course_interest')) ?: null;
        $source         = trim((string) $request->input('source', 'walk_in'));
        $branchId       = $request->input('branch_id') ? (int) $request->input('branch_id') : null;
        $followupDate   = trim((string) $request->input('next_followup_date')) ?: null;
        $notes          = trim((string) $request->input('notes')) ?: null;

        if ($firstName === '' || $lastName === '' || $phone === '') {
            $this->back('error', 'First name, last name, and phone number are required.', $request->all());
            return;
        }

        $enquiryId = $this->db->insert('student_enquiries', [
            'tenant_id'          => $tenantId,
            'branch_id'          => $branchId,
            'first_name'         => $firstName,
            'last_name'          => $lastName,
            'phone'              => $phone,
            'email'              => $email,
            'grade'              => $grade,
            'course_interest'    => $courseInterest,
            'source'             => $source,
            'status'             => 'new',
            'next_followup_date' => $followupDate,
            'notes'              => $notes,
            'created_at'         => date('Y-m-d H:i:s'),
        ]);

        $this->audit->log('enquiry.created', 'student_enquiries', $enquiryId, [
            'name'  => "{$firstName} {$lastName}",
            'phone' => $phone,
            'course'=> $courseInterest,
        ]);

        $this->redirect(url('students/enquiries'), 'success', 'Student enquiry registered successfully.');
    }

    public function show(Request $request, string $id = ''): void
    {
        $tenantId  = current_tenant_id();
        $enquiryId = (int) ($id ?: $request->query('id', 0));

        $enquiry = $this->db->queryOne(
            "SELECT e.*, b.name AS branch_name, u.name AS assigned_to_name, s.admission_number, s.id AS student_pk
             FROM `student_enquiries` e
             LEFT JOIN `branches` b ON e.branch_id = b.id
             LEFT JOIN `users` u ON e.assigned_to = u.id
             LEFT JOIN `students` s ON e.converted_student_id = s.id
             WHERE e.`id` = :id AND e.`tenant_id` = :tid",
            ['id' => $enquiryId, 'tid' => $tenantId]
        );

        if (!$enquiry) {
            $this->redirect(url('students/enquiries'), 'error', 'Enquiry record not found.');
            return;
        }

        $followups = $this->db->query(
            "SELECT f.*, u.name AS contacted_by_name
             FROM `enquiry_followups` f
             JOIN `users` u ON f.contacted_by = u.id
             WHERE f.`enquiry_id` = :eid AND f.`tenant_id` = :tid
             ORDER BY f.`contact_date` DESC",
            ['eid' => $enquiryId, 'tid' => $tenantId]
        );

        $this->render('students/enquiries/show', [
            'title'      => "Enquiry #{$enquiryId} — {$enquiry['first_name']} {$enquiry['last_name']}",
            'activeMenu' => 'students',
            'enquiry'    => $enquiry,
            'followups'  => $followups,
        ]);
    }

    public function storeFollowup(Request $request): void
    {
        $tenantId  = current_tenant_id();
        $enquiryId = (int) $request->input('enquiry_id');
        $channel   = trim((string) $request->input('channel', 'call'));
        $outcome   = trim((string) $request->input('outcome', 'neutral'));
        $notes     = trim((string) $request->input('response_notes'));
        $nextDate  = trim((string) $request->input('next_followup_date')) ?: null;
        $newStatus = trim((string) $request->input('new_status', 'follow_up'));

        if ($notes === '') {
            $this->back('error', 'Please provide notes describing the follow-up outcome.', $request->all());
            return;
        }

        $user = auth_user();
        $userId = $user['id'] ?? 1;

        $this->db->insert('enquiry_followups', [
            'tenant_id'      => $tenantId,
            'enquiry_id'     => $enquiryId,
            'contacted_by'   => $userId,
            'contact_date'   => date('Y-m-d H:i:s'),
            'channel'        => $channel,
            'response_notes' => $notes,
            'outcome'        => $outcome,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->db->execute(
            "UPDATE `student_enquiries` SET `status` = :st, `next_followup_date` = :nd, `updated_at` = NOW() WHERE `id` = :id AND `tenant_id` = :tid",
            [
                'st'  => $newStatus,
                'nd'  => $nextDate,
                'id'  => $enquiryId,
                'tid' => $tenantId,
            ]
        );

        $this->audit->log('enquiry.followup', 'student_enquiries', $enquiryId, [
            'channel' => $channel,
            'outcome' => $outcome,
            'status'  => $newStatus,
        ]);

        $this->redirect(url("students/enquiries/{$enquiryId}"), 'success', 'Follow-up interaction recorded successfully.');
    }

    public function convert(Request $request): void
    {
        $tenantId  = current_tenant_id();
        $enquiryId = (int) $request->input('enquiry_id');

        $enquiry = $this->db->queryOne(
            "SELECT * FROM `student_enquiries` WHERE `id` = :id AND `tenant_id` = :tid",
            ['id' => $enquiryId, 'tid' => $tenantId]
        );

        if (!$enquiry) {
            $this->redirect(url('students/enquiries'), 'error', 'Enquiry not found.');
            return;
        }

        if ($enquiry['status'] === 'admitted' && $enquiry['converted_student_id']) {
            $this->redirect(url("students/{$enquiry['converted_student_id']}"), 'info', 'Enquiry already admitted as student.');
            return;
        }

        $year   = date('Y');
        $random = mt_rand(1000, 9999);
        $admNo  = "APX-{$year}-{$random}";

        $branchId = $enquiry['branch_id'] ?: 1;

        $studentId = $this->db->insert('students', [
            'tenant_id'        => $tenantId,
            'branch_id'        => $branchId,
            'admission_number' => $admNo,
            'first_name'       => $enquiry['first_name'],
            'last_name'        => $enquiry['last_name'],
            'full_name'        => "{$enquiry['first_name']} {$enquiry['last_name']}",
            'email'            => $enquiry['email'],
            'phone'            => $enquiry['phone'],
            'gender'           => 'other',
            'admission_date'   => date('Y-m-d'),
            'status'           => 'active',
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        $this->db->execute(
            "UPDATE `student_enquiries` SET `status` = 'admitted', `converted_student_id` = :sid, `updated_at` = NOW() WHERE `id` = :eid AND `tenant_id` = :tid",
            ['sid' => $studentId, 'eid' => $enquiryId, 'tid' => $tenantId]
        );

        $this->audit->log('enquiry.converted_to_student', 'students', $studentId, [
            'enquiry_id'       => $enquiryId,
            'admission_number' => $admNo,
        ]);

        $this->redirect(url("students/{$studentId}"), 'success', "Enquiry successfully converted to student! Admission Number: {$admNo}");
    }
}
