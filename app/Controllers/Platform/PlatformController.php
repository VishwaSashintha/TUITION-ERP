<?php

declare(strict_types=1);

namespace App\Controllers\Platform;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Tenant;
use App\Services\AuditService;

/**
 * TuitionPro ERP — Developer / System Super Administrator Controller
 *
 * Per requirements:
 * - Developer-controlled system administrator for setup & maintenance
 * - Manages the installation's Institute Admin account (creation / recovery)
 * - Monitors system health, database integrity, and audit logs
 * - No SaaS multi-tenant self-service onboarding or runtime tenant switching
 */
class PlatformController extends Controller
{
    private Tenant $tenantModel;
    private AuditService $audit;
    private Database $db;

    public function __construct()
    {
        $this->tenantModel = new Tenant();
        $this->audit       = new AuditService();
        $this->db          = Database::getInstance();
    }

    /**
     * System Administration Dashboard & Health Monitor.
     */
    public function dashboard(Request $request): void
    {
        $code = strtolower((string) env('INSTITUTE_CODE', 'apex'));

        // 1. Active Institute Profile
        $activeInstitute = $this->db->queryOne(
            "SELECT t.*, 
                    (SELECT COUNT(*) FROM `students` s WHERE s.tenant_id = t.id AND s.deleted_at IS NULL) AS student_count,
                    (SELECT COUNT(*) FROM `teachers` tc WHERE tc.tenant_id = t.id AND tc.deleted_at IS NULL) AS teacher_count,
                    (SELECT COUNT(*) FROM `users` u WHERE u.tenant_id = t.id AND u.deleted_at IS NULL) AS user_count,
                    (SELECT COUNT(*) FROM `branches` b WHERE b.tenant_id = t.id AND b.deleted_at IS NULL) AS branch_count
             FROM `tenants` t 
             WHERE t.`code` = :code AND t.`deleted_at` IS NULL LIMIT 1",
            ['code' => $code]
        );

        if (!$activeInstitute) {
            // Fallback to first tenant
            $activeInstitute = $this->db->queryOne(
                "SELECT t.*, 
                        (SELECT COUNT(*) FROM `students` s WHERE s.tenant_id = t.id AND s.deleted_at IS NULL) AS student_count,
                        (SELECT COUNT(*) FROM `teachers` tc WHERE tc.tenant_id = t.id AND tc.deleted_at IS NULL) AS teacher_count,
                        (SELECT COUNT(*) FROM `users` u WHERE u.tenant_id = t.id AND u.deleted_at IS NULL) AS user_count,
                        (SELECT COUNT(*) FROM `branches` b WHERE b.tenant_id = t.id AND b.deleted_at IS NULL) AS branch_count
                 FROM `tenants` t 
                 WHERE t.`deleted_at` IS NULL ORDER BY t.`id` ASC LIMIT 1"
            );
        }

        // 2. System & Platform-Wide Statistics
        $totalTenants = (int) $this->db->queryOne("SELECT COUNT(*) AS c FROM `tenants` WHERE `deleted_at` IS NULL")['c'];
        $activeTenants = (int) $this->db->queryOne("SELECT COUNT(*) AS c FROM `tenants` WHERE `status` = 'active' AND `deleted_at` IS NULL")['c'];
        $inactiveTenants = max(0, $totalTenants - $activeTenants);
        $totalPlatformUsers = (int) $this->db->queryOne("SELECT COUNT(*) AS c FROM `users` WHERE `deleted_at` IS NULL")['c'];
        $totalAllStudents = (int) $this->db->queryOne("SELECT COUNT(*) AS c FROM `students` WHERE `deleted_at` IS NULL")['c'];
        $totalAllTeachers = (int) $this->db->queryOne("SELECT COUNT(*) AS c FROM `teachers` WHERE `deleted_at` IS NULL")['c'];

        $totalTables = (int) $this->db->queryOne("SELECT COUNT(*) AS c FROM information_schema.tables WHERE table_schema = DATABASE()")['c'];
        $activeTokens = (int) $this->db->queryOne("SELECT COUNT(*) AS c FROM `refresh_tokens` WHERE `revoked_at` IS NULL AND `expires_at` > NOW()")['c'];
        $totalAudits = (int) $this->db->queryOne("SELECT COUNT(*) AS c FROM `audit_logs`")['c'];

        // 3. System Environment Info
        $systemInfo = [
            'php_version'    => PHP_VERSION,
            'mysql_version'  => $this->db->getConnection()->getAttribute(\PDO::ATTR_SERVER_VERSION),
            'server_os'      => PHP_OS_FAMILY,
            'memory_usage'   => round(memory_get_usage(true) / (1024 * 1024), 2) . ' MB',
            'memory_peak'    => round(memory_get_peak_usage(true) / (1024 * 1024), 2) . ' MB',
            'jwt_auth'       => 'HS256 HMAC-SHA256 (Active)',
            'storage_status' => is_writable(STORAGE_PATH) ? 'Writable' : 'Read-Only',
        ];

        // 4. Recent Audit Logs
        $recentAudits = $this->db->query(
            "SELECT a.*, COALESCE(u.name, 'System') AS user_name 
             FROM `audit_logs` a 
             LEFT JOIN `users` u ON a.user_id = u.id 
             ORDER BY a.`id` DESC LIMIT 10"
        );

        $this->render('platform/dashboard', [
            'title'              => 'System Administration Overview',
            'activeMenu'         => 'platform_dashboard',
            'activeInstitute'    => $activeInstitute,
            'totalTenants'       => $totalTenants,
            'activeTenants'      => $activeTenants,
            'inactiveTenants'    => $inactiveTenants,
            'totalPlatformUsers' => $totalPlatformUsers,
            'totalAllStudents'   => $totalAllStudents,
            'totalAllTeachers'   => $totalAllTeachers,
            'totalTables'        => $totalTables,
            'activeTokens'       => $activeTokens,
            'totalAudits'        => $totalAudits,
            'systemInfo'         => $systemInfo,
            'recentAudits'       => $recentAudits,
        ]);
    }

    /**
     * Institute Admin Account & Installation Configuration Management.
     */
    public function tenants(Request $request): void
    {
        $code = strtolower((string) env('INSTITUTE_CODE', 'apex'));

        $institute = $this->db->queryOne(
            "SELECT * FROM `tenants` WHERE `code` = :code AND `deleted_at` IS NULL LIMIT 1",
            ['code' => $code]
        );

        if (!$institute) {
            $institute = $this->db->queryOne("SELECT * FROM `tenants` WHERE `deleted_at` IS NULL ORDER BY `id` ASC LIMIT 1");
        }

        $admins = [];
        if ($institute) {
            $admins = $this->db->query(
                "SELECT u.*, r.display_name AS role_display 
                 FROM `users` u 
                 JOIN `roles` r ON u.role_id = r.id 
                 WHERE u.`tenant_id` = :tid AND r.`name` IN ('institute_owner', 'institute_admin') AND u.`deleted_at` IS NULL 
                 ORDER BY u.`id` ASC",
                ['tid' => $institute['id']]
            );
        }

        $this->render('platform/tenants', [
            'title'      => 'Institute Admin & Installation Setup',
            'activeMenu' => 'platform_tenants',
            'institute'  => $institute,
            'admins'     => $admins,
        ]);
    }

    /**
     * Create or Assign an Institute Admin Account.
     */
    public function storeTenant(Request $request): void
    {
        $rules = [
            'name'     => 'required|min:3',
            'email'    => 'required|email',
            'password' => 'required|min:8',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        $code = strtolower((string) env('INSTITUTE_CODE', 'apex'));
        $institute = $this->db->queryOne("SELECT id FROM `tenants` WHERE `code` = :code LIMIT 1", ['code' => $code]);
        if (!$institute) {
            $institute = $this->db->queryOne("SELECT id FROM `tenants` ORDER BY id ASC LIMIT 1");
        }
        $tenantId = (int) $institute['id'];

        $email = trim((string)$request->input('email'));

        $existing = $this->db->queryOne("SELECT id FROM `users` WHERE `email` = :email AND `deleted_at` IS NULL", ['email' => $email]);
        if ($existing) {
            $this->back('error', "User account with email '{$email}' already exists.");
            return;
        }

        // Get institute_admin role id
        $adminRole = $this->db->queryOne("SELECT id FROM `roles` WHERE `name` = 'institute_admin' LIMIT 1");
        $roleId = $adminRole ? (int) $adminRole['id'] : 2;

        // Get main branch
        $mainBranch = $this->db->queryOne("SELECT id FROM `branches` WHERE `tenant_id` = :tid ORDER BY is_main DESC, id ASC LIMIT 1", ['tid' => $tenantId]);
        $branchId = $mainBranch ? (int) $mainBranch['id'] : null;

        $userId = $this->db->insert('users', [
            'tenant_id'     => $tenantId,
            'branch_id'     => $branchId,
            'role_id'       => $roleId,
            'name'          => trim((string)$request->input('name')),
            'username'      => explode('@', $email)[0],
            'email'         => $email,
            'phone'         => $request->input('phone'),
            'password_hash' => password_hash((string)$request->input('password'), PASSWORD_BCRYPT),
            'status'        => 'active',
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->audit->log('institute_admin_created', 'users', $userId, "Super Admin created Institute Admin account {$email}");

        $this->back('success', "Institute Admin account for '{$email}' created successfully.");
    }

    /**
     * Reset Institute Admin Password.
     */
    public function resetAdminPassword(Request $request): void
    {
        $userId   = (int) $request->input('user_id');
        $password = (string) $request->input('new_password');

        if (strlen($password) < 8) {
            $this->back('error', 'New password must be at least 8 characters.');
            return;
        }

        // Verify user is an institute admin
        $user = $this->db->queryOne(
            "SELECT u.*, r.name AS role_name 
             FROM `users` u 
             JOIN `roles` r ON u.role_id = r.id 
             WHERE u.id = :id AND u.deleted_at IS NULL",
            ['id' => $userId]
        );

        if (!$user || !in_array($user['role_name'], ['institute_owner', 'institute_admin'], true)) {
            $this->back('error', 'Target user is not an Institute Administrator.');
            return;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->db->execute(
            "UPDATE `users` SET `password_hash` = :hash, `updated_at` = NOW() WHERE `id` = :id",
            ['hash' => $hash, 'id' => $userId]
        );

        // Revoke active refresh tokens for this user for security
        $this->db->execute(
            "UPDATE `refresh_tokens` SET `revoked_at` = NOW() WHERE `user_id` = :uid AND `user_type` = 'institute'",
            ['uid' => $userId]
        );

        $this->audit->log('institute_admin_password_reset', 'users', $userId, "Super Admin reset password for Institute Admin {$user['email']}");

        $this->back('success', "Password successfully reset for {$user['name']} ({$user['email']}).");
    }

    /**
     * Update Institute Profile Configuration.
     */
    public function updateInstitute(Request $request): void
    {
        $instituteId = (int) $request->input('institute_id');
        $name        = trim((string)$request->input('name'));
        $email       = trim((string)$request->input('email'));
        $phone       = trim((string)$request->input('phone'));
        $address     = trim((string)$request->input('address'));

        if ($name === '' || $email === '') {
            $this->back('error', 'Institute name and email cannot be empty.');
            return;
        }

        $this->db->execute(
            "UPDATE `tenants` SET `name` = :name, `email` = :email, `phone` = :phone, `address` = :addr, `updated_at` = NOW() WHERE `id` = :id",
            ['name' => $name, 'email' => $email, 'phone' => $phone, 'addr' => $address, 'id' => $instituteId]
        );

        $this->audit->log('institute_profile_updated', 'tenants', $instituteId, "Super Admin updated installation profile for {$name}");

        $this->back('success', 'Institute profile and installation configuration updated successfully.');
    }
}
