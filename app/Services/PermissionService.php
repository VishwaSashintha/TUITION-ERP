<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * TuitionPro ERP — Role & Permission Verification Service
 */
class PermissionService
{
    private Database $db;
    private static array $cache = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Check if a given user role possesses a specific permission.
     */
    public function hasPermission(string $permissionName, ?int $roleId = null): bool
    {
        $user = auth_user();
        if (!$user) {
            return false;
        }

        // Platform Super Admin and Institute Owner have full access
        if (in_array($user['role_name'] ?? '', ['super_admin', 'institute_owner'], true)) {
            return true;
        }

        $effectiveRoleId = $roleId ?? ($user['role_id'] ?? null);
        if (!$effectiveRoleId) {
            return false;
        }

        $cacheKey = "role_{$effectiveRoleId}_{$permissionName}";
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $row = $this->db->queryOne(
            "SELECT 1 FROM `role_permissions` rp 
             JOIN `permissions` p ON rp.permission_id = p.id 
             WHERE rp.`role_id` = :rid AND p.`name` = :pname LIMIT 1",
            ['rid' => $effectiveRoleId, 'pname' => $permissionName]
        );

        $hasAccess = ($row !== null);
        self::$cache[$cacheKey] = $hasAccess;

        return $hasAccess;
    }
}

