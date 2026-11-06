<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = true;

    /**
     * Find user with role and branch details.
     */
    public function findWithDetails(int $id): ?array
    {
        $tenantId = $this->getActiveTenantId();
        return $this->db->queryOne(
            "SELECT u.*, r.name AS role_name, r.display_name AS role_display, b.name AS branch_name 
             FROM `users` u 
             JOIN `roles` r ON u.role_id = r.id 
             LEFT JOIN `branches` b ON u.branch_id = b.id 
             WHERE u.`id` = :id AND u.`tenant_id` = :tid AND u.`deleted_at` IS NULL",
            ['id' => $id, 'tid' => $tenantId]
        );
    }
}

