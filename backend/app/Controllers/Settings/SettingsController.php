<?php

declare(strict_types=1);

namespace App\Controllers\Settings;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Models\Branch;
use App\Models\User;
use App\Services\AuditService;

class SettingsController extends Controller
{
    private Branch $branchModel;
    private User $userModel;
    private AuditService $audit;
    private Database $db;

    public function __construct()
    {
        $this->branchModel = new Branch();
        $this->userModel   = new User();
        $this->audit       = new AuditService();
        $this->db          = Database::getInstance();
    }

    public function index(Request $request): void
    {
        $tenantId = current_tenant_id();
        $tenant = $this->db->queryOne("SELECT * FROM `tenants` WHERE `id` = :tid", ['tid' => $tenantId]);

        $this->render('settings/index', [
            'title'      => 'Institute Settings',
            'activeMenu' => 'settings',
            'tenant'     => $tenant,
        ]);
    }

    public function updateSettings(Request $request): void
    {
        $tenantId = current_tenant_id();

        $name     = trim((string)$request->input('name'));
        $email    = trim((string)$request->input('email'));
        $phone    = $request->input('phone');
        $address  = $request->input('address');
        $timezone = $request->input('timezone', 'Asia/Colombo');

        $this->db->execute(
            "UPDATE `tenants` SET `name` = :name, `email` = :email, `phone` = :phone, `address` = :addr, `timezone` = :tz, `updated_at` = NOW() WHERE `id` = :id",
            [
                'name'  => $name,
                'email' => $email,
                'phone' => $phone,
                'addr'  => $address,
                'tz'    => $timezone,
                'id'    => $tenantId
            ]
        );

        $this->audit->log('settings_updated', 'tenants', $tenantId, 'Institute profile settings updated');

        $this->back('success', 'Institute settings saved successfully.');
    }

    public function branches(Request $request): void
    {
        $tenantId = current_tenant_id();

        $branches = $this->db->query(
            "SELECT b.*, 
                    (SELECT COUNT(*) FROM `students` s WHERE s.branch_id = b.id AND s.deleted_at IS NULL) AS student_count,
                    (SELECT COUNT(*) FROM `batches` bt WHERE bt.branch_id = b.id AND bt.deleted_at IS NULL) AS batch_count
             FROM `branches` b 
             WHERE b.`tenant_id` = :tid AND b.`deleted_at` IS NULL 
             ORDER BY b.`id` ASC, b.`name` ASC",
            ['tid' => $tenantId]
        );

        $this->render('settings/branches', [
            'title'      => 'Institute Branches',
            'activeMenu' => 'settings',
            'branches'   => $branches,
        ]);
    }

    public function storeBranch(Request $request): void
    {
        $rules = [
            'name' => 'required',
            'code' => 'required',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        $tenantId = current_tenant_id();

        try {
            $id = $this->branchModel->create([
                'tenant_id' => $tenantId,
                'name'      => trim((string)$request->input('name')),
                'code'      => strtoupper(trim((string)$request->input('code'))),
                'phone'     => $request->input('phone'),
                'email'     => $request->input('email'),
                'address'   => $request->input('address'),
            ]);

            $this->audit->log('branch_created', 'branches', $id, "Branch '{$request->input('name')}' created");
            $this->redirect(url('settings/branches'), 'success', 'New branch added successfully.');
        } catch (\Throwable $e) {
            $this->back('error', 'Failed to add branch: ' . $e->getMessage());
        }
    }

    public function users(Request $request): void
    {
        $tenantId = current_tenant_id();

        $users = $this->db->query(
            "SELECT u.*, r.name AS role_name, r.display_name AS role_display, br.name AS branch_name
             FROM `users` u
             JOIN `roles` r ON u.role_id = r.id
             LEFT JOIN `branches` br ON u.branch_id = br.id
             WHERE u.`tenant_id` = :tid AND u.`deleted_at` IS NULL
             ORDER BY u.`id` ASC",
            ['tid' => $tenantId]
        );

        $roles    = $this->db->query("SELECT id, name, display_name FROM `roles` WHERE `name` NOT IN ('super_admin', 'support_admin') ORDER BY id ASC");
        $branches = $this->db->query("SELECT id, name FROM `branches` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL", ['tid' => $tenantId]);

        $this->render('settings/users', [
            'title'      => 'Staff & User Accounts',
            'activeMenu' => 'settings',
            'users'      => $users,
            'roles'      => $roles,
            'branches'   => $branches,
        ]);
    }

    public function storeUser(Request $request): void
    {
        $rules = [
            'name'     => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:6',
            'role_id'  => 'required|numeric',
        ];

        $errors = $this->validate($rules, $request->all());
        if (!empty($errors)) {
            $this->back('error', reset($errors), $request->all());
            return;
        }

        $roleId = (int) $request->input('role_id');
        $targetRole = $this->db->queryOne("SELECT id, name FROM `roles` WHERE `id` = :id", ['id' => $roleId]);
        if (!$targetRole || in_array($targetRole['name'], ['super_admin', 'support_admin'], true)) {
            $this->back('error', 'Unauthorized role assignment. Platform administrative roles cannot be assigned.', $request->all());
            return;
        }

        $tenantId = current_tenant_id();
        $email    = trim((string)$request->input('email'));

        $existing = $this->db->queryOne("SELECT id FROM `users` WHERE `email` = :email AND `tenant_id` = :tid", ['email' => $email, 'tid' => $tenantId]);
        if ($existing) {
            $this->back('error', 'A user with this email address already exists in this institute.');
            return;
        }

        try {
            $id = $this->userModel->create([
                'tenant_id' => $tenantId,
                'branch_id' => $request->input('branch_id') ? (int)$request->input('branch_id') : null,
                'role_id'   => (int)$request->input('role_id'),
                'name'      => trim((string)$request->input('name')),
                'email'     => $email,
                'password'  => password_hash((string)$request->input('password'), PASSWORD_BCRYPT),
                'phone'     => $request->input('phone'),
                'status'    => 'active',
            ]);

            $this->audit->log('user_created', 'users', $id, "Staff account for '{$request->input('name')}' created");
            $this->redirect(url('settings/users'), 'success', 'Staff account created successfully.');
        } catch (\Throwable $e) {
            $this->back('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function backups(Request $request): void
    {
        $backupDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/backups' : dirname(__DIR__, 3) . '/backend/storage/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $files = glob($backupDir . '/*.sql');
        $backups = [];
        foreach ($files as $f) {
            $backups[] = [
                'filename' => basename($f),
                'size'     => round(filesize($f) / 1024, 2) . ' KB',
                'date'     => date('Y-m-d H:i:s', filemtime($f)),
            ];
        }
        usort($backups, fn($a, $b) => strcmp($b['date'], $a['date']));

        $this->render('settings/backups', [
            'title'      => 'Database Backups & Restore',
            'activeMenu' => 'settings',
            'backups'    => $backups,
        ]);
    }

    public function createBackup(Request $request): void
    {
        $backupDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/backups' : dirname(__DIR__, 3) . '/backend/storage/backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'tuitionpro_backup_' . date('Ymd_His') . '.sql';
        $filepath = $backupDir . '/' . $filename;

        $dumpCmd = sprintf(
            '"C:\xampp\mysql\bin\mysqldump.exe" -u root tuitionpro > "%s"',
            $filepath
        );

        exec($dumpCmd, $output, $code);

        if ($code === 0 && file_exists($filepath) && filesize($filepath) > 0) {
            $this->audit->log('backup_created', 'system', 0, "Database backup created: {$filename}");
            $this->redirect(url('settings/backups'), 'success', "Backup archive '{$filename}' generated successfully.");
        } else {
            $tables = $this->db->query("SHOW TABLES");
            $sqlDump = "-- TuitionPro ERP Database Backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

            foreach ($tables as $tRow) {
                $table = reset($tRow);
                $createRes = $this->db->queryOne("SHOW CREATE TABLE `{$table}`");
                $sqlDump .= "\n\n" . $createRes['Create Table'] . ";\n\n";

                $rows = $this->db->query("SELECT * FROM `{$table}`");
                foreach ($rows as $row) {
                    $vals = array_map(function ($val) {
                        return $val === null ? 'NULL' : Database::getInstance()->getConnection()->quote((string)$val);
                    }, array_values($row));
                    $sqlDump .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $vals) . ");\n";
                }
            }

            file_put_contents($filepath, $sqlDump);
            $this->audit->log('backup_created', 'system', 0, "PHP Database backup created: {$filename}");
            $this->redirect(url('settings/backups'), 'success', "Backup archive '{$filename}' generated successfully.");
        }
    }

    public function downloadBackup(Request $request): void
    {
        $filename  = basename((string)$request->query('file'));
        $backupDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/backups' : dirname(__DIR__, 3) . '/backend/storage/backups';
        $filepath  = $backupDir . '/' . $filename;

        if (empty($filename) || !file_exists($filepath) || !str_ends_with($filename, '.sql')) {
            $this->redirect(url('settings/backups'), 'error', 'Invalid or missing backup file.');
            return;
        }

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    public function restoreBackup(Request $request): void
    {
        $filename  = basename((string)$request->input('filename'));
        $backupDir = defined('STORAGE_PATH') ? STORAGE_PATH . '/backups' : dirname(__DIR__, 3) . '/backend/storage/backups';
        $filepath  = $backupDir . '/' . $filename;

        if (empty($filename) || !file_exists($filepath) || !str_ends_with($filename, '.sql')) {
            $this->back('error', 'Target backup archive not found.');
            return;
        }

        try {
            $sql = file_get_contents($filepath);
            if (empty($sql)) {
                $this->back('error', 'Backup archive is empty.');
                return;
            }

            $pdo = $this->db->getConnection();
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $pdo->exec($sql);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

            $this->audit->log('database_restored', 'system', 0, "Database restored from archive: {$filename}");
            $this->redirect(url('settings/backups'), 'success', "Database successfully restored from '{$filename}'.");
        } catch (\Throwable $e) {
            $this->back('error', 'Restore failed: ' . $e->getMessage());
        }
    }
}

