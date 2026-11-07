<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Expense extends Model
{
    protected string $table = 'expenses';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = true;

    public function findWithDetails(int $id): ?array
    {
        $tenantId = $this->getActiveTenantId();
        return $this->db->queryOne(
            "SELECT e.*, ec.name AS category_name, ec.code AS category_code, br.name AS branch_name, u.name AS creator_name 
             FROM `expenses` e 
             JOIN `expense_categories` ec ON e.category_id = ec.id 
             JOIN `branches` br ON e.branch_id = br.id 
             JOIN `users` u ON e.created_by = u.id 
             WHERE e.`id` = :id AND e.`tenant_id` = :tid AND e.`deleted_at` IS NULL",
            ['id' => $id, 'tid' => $tenantId]
        );
    }
}

