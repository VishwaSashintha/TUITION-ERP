<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Request;

/**
 * TuitionPro ERP — Centralized Audit Logging Service
 *
 * Implements immutable auditing for security, administrative, and financial activities.
 */
class AuditService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Record an audit event into audit_logs.
     */
    public function log(
        string $action,
        string $entityType,
        ?int $entityId,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        $tenantId = current_tenant_id();
        $userId   = auth_id();

        $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        $sql = "INSERT INTO `audit_logs` 
                (`tenant_id`, `user_id`, `action`, `entity_type`, `entity_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) 
                VALUES (:tenant_id, :user_id, :action, :entity_type, :entity_id, :description, :old_values, :new_values, :ip_address, :user_agent, NOW())";

        $this->db->execute($sql, [
            'tenant_id'   => $tenantId,
            'user_id'     => $userId,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'description' => $description,
            'old_values'  => $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
            'new_values'  => $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : null,
            'ip_address'  => $ipAddress,
            'user_agent'  => $userAgent,
        ]);
    }
}

