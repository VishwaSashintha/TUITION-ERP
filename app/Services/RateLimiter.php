<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

//Auth
class RateLimiter
{
    private Database $db;
    private int $maxAttempts;
    private int $decaySeconds;

    public function __construct(int $maxAttempts = 5, int $decaySeconds = 300)
    {
        $this->db           = Database::getInstance();
        $this->maxAttempts  = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
    }

    //Auth
    public function isRateLimited(string $ip, string $identifier): bool
    {
        $cutoff = date('Y-m-d H:i:s', time() - $this->decaySeconds);
        $res = $this->db->queryOne(
            "SELECT COUNT(*) AS failed_count 
             FROM `login_attempts` 
             WHERE (`ip_address` = :ip OR `login_identifier` = :ident)
               AND `is_successful` = 0 
               AND `attempted_at` >= :cutoff",
            ['ip' => $ip, 'ident' => $identifier, 'cutoff' => $cutoff]
        );

        return ((int) ($res['failed_count'] ?? 0)) >= $this->maxAttempts;
    }

    //Auth
    public function recordAttempt(string $ip, string $identifier, bool $success): void
    {
        $this->db->execute(
            "INSERT INTO `login_attempts` (`ip_address`, `login_identifier`, `is_successful`, `attempted_at`)
             VALUES (:ip, :ident, :succ, NOW())",
            ['ip' => $ip, 'ident' => $identifier, 'succ' => $success ? 1 : 0]
        );
    }

    //Auth
    public function clear(string $ip, string $identifier): void
    {
        $this->db->execute(
            "DELETE FROM `login_attempts` WHERE `ip_address` = :ip OR `login_identifier` = :ident",
            ['ip' => $ip, 'ident' => $identifier]
        );
    }
}

