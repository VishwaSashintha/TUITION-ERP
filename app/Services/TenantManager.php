<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Session;

class TenantManager
{
    private Database $db;
    private AuditService $audit;

    public function __construct()
    {
        $this->db    = Database::getInstance();
        $this->audit = new AuditService();
    }

    public function getActiveTenant(): ?array
    {
        $tenantId = current_tenant_id();
        if (!$tenantId) {
            return null;
        }

        return $this->db->queryOne(
            "SELECT t.*, p.name AS plan_name, p.code AS plan_code, p.student_limit, p.branch_limit, p.user_limit, p.features_json 
             FROM `tenants` t 
             LEFT JOIN `subscription_plans` p ON t.subscription_plan_id = p.id 
             WHERE t.`id` = :id AND t.`deleted_at` IS NULL",
            ['id' => $tenantId]
        );
    }

    public function setActiveTenant(int $tenantId): void
    {
        session(['tenant_id' => $tenantId]);
    }

    public function isTenantActive(): bool
    {
        $tenant = $this->getActiveTenant();
        if (!$tenant) {
            return false;
        }

        if (in_array($tenant['status'], ['active', 'trial'], true)) {
            return true;
        }

        return false;
    }

    public function canAddStudent(): bool
    {
        $tenant = $this->getActiveTenant();
        if (!$tenant) {
            return true;
        }

        $limit = isset($tenant['student_limit']) ? (int) $tenant['student_limit'] : 0;
        if ($limit <= 0) {
            return true;
        }

        $currentCount = (int) $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM `students` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL",
            ['tid' => $tenant['id']]
        )['total'];

        return $currentCount < $limit;
    }

    public function canAddBranch(): bool
    {
        $tenant = $this->getActiveTenant();
        if (!$tenant) {
            return true;
        }

        $limit = isset($tenant['branch_limit']) ? (int) $tenant['branch_limit'] : 0;
        if ($limit <= 0) {
            return true;
        }

        $currentCount = (int) $this->db->queryOne(
            "SELECT COUNT(*) AS total FROM `branches` WHERE `tenant_id` = :tid AND `deleted_at` IS NULL",
            ['tid' => $tenant['id']]
        )['total'];

        return $currentCount < $limit;
    }

    public function registerInstitute(array $data): int
    {
        return $this->db->transaction(function () use ($data) {
            $planCode = $data['plan_code'] ?? 'starter';
            $plan = $this->db->queryOne("SELECT id, student_limit, branch_limit FROM `subscription_plans` WHERE `code` = :code", ['code' => $planCode]);
            $planId = $plan ? (int)$plan['id'] : null;

            $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );

            $tenantId = $this->db->insert(
                "INSERT INTO `tenants` 
                (`uuid`, `name`, `code`, `email`, `phone`, `address`, `currency`, `currency_symbol`, `timezone`, `status`, `subscription_plan_id`, `trial_ends_at`, `subscription_ends_at`, `created_at`) 
                VALUES (:uuid, :name, :code, :email, :phone, :address, 'LKR', 'Rs.', 'Asia/Colombo', 'trial', :plan_id, DATE_ADD(NOW(), INTERVAL 14 DAY), DATE_ADD(NOW(), INTERVAL 365 DAY), NOW())",
                [
                    'uuid'    => $uuid,
                    'name'    => $data['institute_name'],
                    'code'    => strtolower(trim($data['institute_code'])),
                    'email'   => $data['email'],
                    'phone'   => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'plan_id' => $planId,
                ]
            );

            $branchId = $this->db->insert(
                "INSERT INTO `branches` (`tenant_id`, `name`, `code`, `address`, `phone`, `email`, `status`, `created_at`) 
                 VALUES (:tid, :name, 'MAIN', :address, :phone, :email, 'active', NOW())",
                [
                    'tid'     => $tenantId,
                    'name'    => $data['institute_name'] . ' - Main Branch',
                    'address' => $data['address'] ?? null,
                    'phone'   => $data['phone'] ?? null,
                    'email'   => $data['email'],
                ]
            );

            $currentYear = date('Y');
            $this->db->insert(
                "INSERT INTO `academic_years` (`tenant_id`, `name`, `start_date`, `end_date`, `is_active`, `created_at`) 
                 VALUES (:tid, :name, :start_date, :end_date, 1, NOW())",
                [
                    'tid'        => $tenantId,
                    'name'       => "{$currentYear} Academic Year",
                    'start_date' => "{$currentYear}-01-01",
                    'end_date'   => "{$currentYear}-12-31",
                ]
            );

            $roleOwner = $this->db->queryOne("SELECT id FROM `roles` WHERE `name` = 'institute_owner'");
            $roleOwnerId = $roleOwner ? (int)$roleOwner['id'] : 3;

            $ownerPasswordHash = password_hash($data['password'], PASSWORD_BCRYPT);
            $userId = $this->db->insert(
                "INSERT INTO `users` (`tenant_id`, `branch_id`, `role_id`, `name`, `username`, `email`, `phone`, `password_hash`, `status`, `created_at`) 
                 VALUES (:tid, :bid, :rid, :name, :username, :email, :phone, :pwd, 'active', NOW())",
                [
                    'tid'      => $tenantId,
                    'bid'      => $branchId,
                    'rid'      => $roleOwnerId,
                    'name'     => $data['owner_name'],
                    'username' => strtolower(trim($data['username'])),
                    'email'    => $data['email'],
                    'phone'    => $data['phone'] ?? null,
                    'pwd'      => $ownerPasswordHash,
                ]
            );

            $defaultCoa = [
                ['1010', 'Cash in Hand', 'asset', 'debit'],
                ['1020', 'Main Operating Bank Account', 'asset', 'debit'],
                ['1100', 'Accounts Receivable (Student Fees)', 'asset', 'debit'],
                ['2010', 'Accounts Payable (Expenses)', 'liability', 'credit'],
                ['2020', 'Teacher Compensation Payable', 'liability', 'credit'],
                ['3010', 'Owner Capital', 'equity', 'credit'],
                ['4010', 'Tuition Fee Revenue', 'revenue', 'credit'],
                ['4020', 'Admission Fee Revenue', 'revenue', 'credit'],
                ['5010', 'Teacher Compensation Expense', 'expense', 'debit'],
                ['5020', 'Classroom Rent Expense', 'expense', 'debit'],
                ['5030', 'Electricity & Water Utilities', 'expense', 'debit'],
                ['5040', 'Printing & Stationery Expense', 'expense', 'debit'],
            ];

            foreach ($defaultCoa as $item) {
                $this->db->insert(
                    "INSERT INTO `chart_of_accounts` (`tenant_id`, `account_code`, `account_name`, `account_type`, `normal_balance`, `is_system`, `is_active`, `created_at`) 
                     VALUES (:tid, :code, :name, :type, :balance, 1, 1, NOW())",
                    [
                        'tid'     => $tenantId,
                        'code'    => $item[0],
                        'name'    => $item[1],
                        'type'    => $item[2],
                        'balance' => $item[3],
                    ]
                );
            }

            $this->db->insert(
                "INSERT INTO `financial_accounts` (`tenant_id`, `branch_id`, `account_name`, `account_type`, `account_number`, `current_balance`, `is_active`, `created_at`) 
                 VALUES (:tid, :bid, 'Cash in Hand (Reception)', 'cash', 'CASH-01', 0.00, 1, NOW())",
                ['tid' => $tenantId, 'bid' => $branchId]
            );

            $this->db->insert(
                "INSERT INTO `fee_types` (`tenant_id`, `code`, `name`, `is_recurring`, `default_amount`, `description`, `created_at`) 
                 VALUES (:tid, 'TUIT_MONTHLY', 'Monthly Tuition Fee', 1, 3500.00, 'Standard monthly class fee', NOW())",
                ['tid' => $tenantId]
            );

            $this->audit->log('institute_registered', 'tenants', $tenantId, "New tuition institute registered: {$data['institute_name']}");

            return $tenantId;
        });
    }
}

