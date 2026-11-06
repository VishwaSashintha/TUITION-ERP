<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Student extends Model
{
    protected string $table = 'students';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = true;

    /**
     * Search students across multiple criteria with pagination.
     */
    public function search(
        ?string $term = null,
        ?int $branchId = null,
        ?string $grade = null,
        ?string $status = null,
        int $page = 1,
        int $perPage = 20
    ): array {
        $tenantId = $this->getActiveTenantId();
        $conditions = ["s.`tenant_id` = :tid", "s.`deleted_at` IS NULL"];
        $params = ['tid' => $tenantId];

        if (!empty($term)) {
            $conditions[] = "(s.`first_name` LIKE :term OR s.`last_name` LIKE :term OR s.`full_name` LIKE :term OR s.`admission_number` LIKE :term OR s.`phone` LIKE :term)";
            $params['term'] = "%{$term}%";
        }

        if ($branchId) {
            $conditions[] = "s.`branch_id` = :bid";
            $params['bid'] = $branchId;
        }

        if (!empty($grade)) {
            $conditions[] = "s.`grade_level` = :grade";
            $params['grade'] = $grade;
        }

        if (!empty($status)) {
            $conditions[] = "s.`status` = :st";
            $params['st'] = $status;
        }

        $whereClause = implode(' AND ', $conditions);
        $countRow = $this->db->queryOne("SELECT COUNT(*) AS total FROM `students` s WHERE {$whereClause}", $params);
        $total = (int) ($countRow['total'] ?? 0);

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT s.*, b.name AS branch_name 
                FROM `students` s 
                LEFT JOIN `branches` b ON s.branch_id = b.id 
                WHERE {$whereClause} 
                ORDER BY s.`id` DESC 
                LIMIT {$perPage} OFFSET {$offset}";

        $rows = $this->db->query($sql, $params);

        return [
            'data'         => $rows,
            'total'        => $total,
            'current_page' => $page,
            'per_page'     => $perPage,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }
}

