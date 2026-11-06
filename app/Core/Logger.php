<?php

declare(strict_types=1);

namespace App\Core;

/**
 * TuitionPro ERP — Centralized Daily File Logger
 *
 * Supports PSR-3 style levels: DEBUG, INFO, WARNING, ERROR, CRITICAL.
 * Log entries are written to daily rotating files in storage/logs/.
 */
class Logger
{
    private string $logDirectory;

    public function __construct(string $logDirectory)
    {
        $this->logDirectory = rtrim($logDirectory, '/\\');
        if (!is_dir($this->logDirectory)) {
            mkdir($this->logDirectory, 0755, true);
        }
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $timezone  = new \DateTimeZone(env('APP_TIMEZONE', 'Asia/Colombo'));
        $timestamp = (new \DateTime('now', $timezone))->format('Y-m-d H:i:s.v');
        $date      = (new \DateTime('now', $timezone))->format('Y-m-d');

        $tenantId = current_tenant_id() ?? 'PLATFORM';
        $userId   = auth_id() ?? 'GUEST';

        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        $line = sprintf("[%s] [%s] [Tenant:%s] [User:%s] %s%s%s", $timestamp, strtoupper($level), $tenantId, $userId, $message, $contextStr, PHP_EOL);

        $file = $this->logDirectory . DIRECTORY_SEPARATOR . $date . '.log';
        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}

