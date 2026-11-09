<?php

declare(strict_types=1);

namespace App\Core;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected bool $tenantScoped = true;
    protected bool $supportsSoftDeletes = true;
    protected bool $hasUpdatedAt = true;

    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function getActiveTenantId(): int
    {
        $tenantId = current_tenant_id();
        if ($tenantId === null) {
            throw new \RuntimeException("Tenant isolation error: No active tenant context found for tenant-scoped table '{$this->table}'.");
        }
        return $tenantId;
    }

    public function find(int|string $id): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $params = ['id' => $id];

        if ($this->tenantScoped) {
            $sql .= " AND `tenant_id` = :tenant_id";
            $params['tenant_id'] = $this->getActiveTenantId();
        }

        if ($this->supportsSoftDeletes) {
            $sql .= " AND `deleted_at` IS NULL";
        }

        $sql .= " LIMIT 1";

        return $this->db->queryOne($sql, $params);
    }

    public function all(
        array $where = [],
        string $orderBy = 'id DESC',
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $conditions = [];
        $params = [];

        if ($this->tenantScoped) {
            $conditions[] = "`tenant_id` = :tenant_id";
            $params['tenant_id'] = $this->getActiveTenantId();
        }

        if ($this->supportsSoftDeletes) {
            $conditions[] = "`deleted_at` IS NULL";
        }

        foreach ($where as $col => $val) {
            $paramKey = 'w_' . str_replace('.', '_', $col);
            $conditions[] = "`{$col}` = :{$paramKey}";
            $params[$paramKey] = $val;
        }

        $sql = "SELECT * FROM `{$this->table}`";
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        if (!empty($orderBy)) {
            //Sanitize
            $cleanOrder = preg_replace('/[^a-zA-Z0-9_, ]/', '', $orderBy);
            $sql .= " ORDER BY {$cleanOrder}";
        }

        if ($limit !== null) {
            $sql .= " LIMIT " . (int) $limit;
            if ($offset !== null) {
                $sql .= " OFFSET " . (int) $offset;
            }
        }

        return $this->db->query($sql, $params);
    }

    public function first(array $where = [], string $orderBy = 'id ASC'): ?array
    {
        $results = $this->all($where, $orderBy, 1);
        return $results[0] ?? null;
    }

    public function count(array $where = []): int
    {
        $conditions = [];
        $params = [];

        if ($this->tenantScoped) {
            $conditions[] = "`tenant_id` = :tenant_id";
            $params['tenant_id'] = $this->getActiveTenantId();
        }

        if ($this->supportsSoftDeletes) {
            $conditions[] = "`deleted_at` IS NULL";
        }

        foreach ($where as $col => $val) {
            $paramKey = 'c_' . str_replace('.', '_', $col);
            $conditions[] = "`{$col}` = :{$paramKey}";
            $params[$paramKey] = $val;
        }

        $sql = "SELECT COUNT(*) as `total` FROM `{$this->table}`";
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $row = $this->db->queryOne($sql, $params);
        return (int) ($row['total'] ?? 0);
    }

    public function create(array $data): int
    {
        if ($this->tenantScoped && !isset($data['tenant_id'])) {
            $data['tenant_id'] = $this->getActiveTenantId();
        }

        $now = date('Y-m-d H:i:s');
        if (!isset($data['created_at'])) {
            $data['created_at'] = $now;
        }
        if ($this->hasUpdatedAt && !isset($data['updated_at'])) {
            $data['updated_at'] = $now;
        }

        $columns = array_keys($data);
        $escapedColumns = array_map(fn($c) => "`{$c}`", $columns);
        $placeholders = array_map(fn($c) => ":{$c}", $columns);

        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            $this->table,
            implode(', ', $escapedColumns),
            implode(', ', $placeholders)
        );

        return $this->db->insert($sql, $data);
    }

    public function update(int|string $id, array $data): int
    {
        unset($data['id'], $data['tenant_id'], $data['created_at']);
        $data['updated_at'] = date('Y-m-d H:i:s');

        $sets = [];
        $params = ['pk_id' => $id];

        foreach ($data as $col => $val) {
            $sets[] = "`{$col}` = :set_{$col}";
            $params["set_{$col}"] = $val;
        }

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets);
        $sql .= " WHERE `{$this->primaryKey}` = :pk_id";

        if ($this->tenantScoped) {
            $sql .= " AND `tenant_id` = :tenant_id";
            $params['tenant_id'] = $this->getActiveTenantId();
        }

        if ($this->supportsSoftDeletes) {
            $sql .= " AND `deleted_at` IS NULL";
        }

        return $this->db->execute($sql, $params);
    }

    public function delete(int|string $id, bool $forceHardDelete = false): int
    {
        $params = ['pk_id' => $id];

        if ($this->supportsSoftDeletes && !$forceHardDelete) {
            $sql = "UPDATE `{$this->table}` SET `deleted_at` = :now WHERE `{$this->primaryKey}` = :pk_id";
            $params['now'] = date('Y-m-d H:i:s');
        } else {
            $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :pk_id";
        }

        if ($this->tenantScoped) {
            $sql .= " AND `tenant_id` = :tenant_id";
            $params['tenant_id'] = $this->getActiveTenantId();
        }

        return $this->db->execute($sql, $params);
    }
}
