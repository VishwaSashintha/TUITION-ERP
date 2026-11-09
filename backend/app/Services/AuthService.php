<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

//Auth
class AuthService
{
    private Database $db;
    private AuditService $audit;
    private JwtService $jwt;
    private RateLimiter $limiter;

    public function __construct()
    {
        $this->db      = Database::getInstance();
        $this->audit   = new AuditService();
        $this->jwt     = new JwtService();
        $this->limiter = new RateLimiter();
    }

    //Auth
    public function attempt(string $login, string $password, ?string $tenantCode = null): ?array
    {
        $login = trim($login);
        $ip    = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        if ($this->limiter->isRateLimited($ip, $login)) {
            throw new \RuntimeException('Too many failed login attempts. Please wait 5 minutes before trying again.');
        }

        $platformUser = $this->db->queryOne(
            "SELECT * FROM `platform_users` WHERE (`email` = :login) AND `status` = 'active' LIMIT 1",
            ['login' => $login]
        );

        if ($platformUser && password_verify($password, $platformUser['password_hash'])) {
            $this->limiter->recordAttempt($ip, $login, true);
            $this->loginPlatformUser($platformUser);
            return $platformUser;
        }

        $sql = "SELECT u.*, r.name AS role_name, r.display_name AS role_display, t.name AS tenant_name, t.code AS tenant_code, t.status AS tenant_status, b.name AS branch_name 
                FROM `users` u 
                JOIN `roles` r ON u.role_id = r.id 
                JOIN `tenants` t ON u.tenant_id = t.id 
                LEFT JOIN `branches` b ON u.branch_id = b.id 
                WHERE (u.`email` = :login OR u.`username` = :login2) 
                AND u.`deleted_at` IS NULL 
                AND t.`deleted_at` IS NULL";

        $params = ['login' => $login, 'login2' => $login];

        $effectiveCode = ($tenantCode !== null && trim($tenantCode) !== '') 
            ? strtolower(trim($tenantCode)) 
            : strtolower((string) env('INSTITUTE_CODE', 'apex'));

        if ($effectiveCode !== '') {
            $sql .= " AND t.`code` = :tcode";
            $params['tcode'] = $effectiveCode;
        }

        $sql .= " LIMIT 1";

        $user = $this->db->queryOne($sql, $params);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->limiter->recordAttempt($ip, $login, false);
            return null;
        }

        if ($user['status'] !== 'active') {
            throw new \RuntimeException('Your account has been deactivated. Please contact your institute administrator.');
        }

        if (!in_array($user['tenant_status'], ['active', 'trial'], true)) {
            throw new \RuntimeException("Your institute's subscription is {$user['tenant_status']}. Please contact the administrator.");
        }

        $this->limiter->recordAttempt($ip, $login, true);
        $this->loginInstituteUser($user);
        return $user;
    }

    //Auth
    private function loginPlatformUser(array &$user): void
    {
        $userData = [
            'id'          => (int) $user['id'],
            'name'        => $user['name'],
            'email'       => $user['email'],
            'role_name'   => $user['role'],
            'role_display'=> $user['role'] === 'super_admin' ? 'Super Administrator' : 'Support Desk',
            'is_platform' => true,
            'tenant_id'   => null,
            'branch_id'   => null,
        ];

        //JWT
        $accessToken  = $this->jwt->generateAccessToken($userData);
        $refreshToken = $this->jwt->generateRefreshToken($userData);

        $user['access_token']  = $accessToken;
        $user['refresh_token'] = $refreshToken;
        $userData['access_token']  = $accessToken;
        $userData['refresh_token'] = $refreshToken;

        $this->setAuthCookies($accessToken, $refreshToken);

        $session = app()->getSession();
        $session->regenerate(true);
        $session->set('user', $userData);
        $session->remove('tenant_id');
        $session->remove('branch_id');

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $this->db->execute(
            "UPDATE `platform_users` SET `last_login_at` = NOW() WHERE `id` = :id",
            ['id' => $user['id']]
        );

        $this->audit->log('platform_login', 'platform_users', (int)$user['id'], "Platform administrator {$user['name']} signed in via JWT.");
    }

    //Auth
    private function loginInstituteUser(array &$user): void
    {
        $userData = [
            'id'           => (int) $user['id'],
            'tenant_id'    => (int) $user['tenant_id'],
            'branch_id'    => $user['branch_id'] ? (int) $user['branch_id'] : null,
            'name'         => $user['name'],
            'username'     => $user['username'],
            'email'        => $user['email'],
            'role_id'      => (int) $user['role_id'],
            'role_name'    => $user['role_name'],
            'role_display' => $user['role_display'],
            'tenant_name'  => $user['tenant_name'],
            'tenant_code'  => $user['tenant_code'],
            'branch_name'  => $user['branch_name'] ?? 'All Branches',
            'is_platform'  => false,
        ];

        //JWT
        $accessToken  = $this->jwt->generateAccessToken($userData);
        $refreshToken = $this->jwt->generateRefreshToken($userData);

        $user['access_token']  = $accessToken;
        $user['refresh_token'] = $refreshToken;
        $userData['access_token']  = $accessToken;
        $userData['refresh_token'] = $refreshToken;

        $this->setAuthCookies($accessToken, $refreshToken);

        $session = app()->getSession();
        $session->regenerate(true);
        $session->set('user', $userData);
        $session->set('tenant_id', (int) $user['tenant_id']);
        if ($user['branch_id']) {
            $session->set('branch_id', (int) $user['branch_id']);
        }

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $this->db->execute(
            "UPDATE `users` SET `last_login_at` = NOW(), `last_login_ip` = :ip WHERE `id` = :id",
            ['id' => $user['id'], 'ip' => $ip]
        );

        $this->audit->log('user_login', 'users', (int)$user['id'], "User {$user['name']} ({$user['role_display']}) signed in via JWT.");
    }

    //Auth
    public function logout(): void
    {
        $user = auth_user();
        if ($user) {
            $this->audit->log('user_logout', 'users', (int)$user['id'], "User {$user['name']} signed out.");
        }

        //Authentication
        $refreshToken = $_COOKIE['tuitionpro_refresh'] ?? null;
        if ($refreshToken) {
            $this->jwt->revokeRefreshToken($refreshToken);
        }

        $this->clearAuthCookies();
        app()->getSession()->destroy();
    }

    //JWT
    private function setAuthCookies(string $accessToken, string $refreshToken): void
    {
        if (headers_sent()) {
            return;
        }

        $isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        $accessTtl = (int) env('JWT_ACCESS_TTL', 900);
        $refreshTtl = (int) env('JWT_REFRESH_TTL', 604800);

        setcookie('tuitionpro_jwt', $accessToken, [
            'expires'  => time() + $accessTtl,
            'path'     => '/',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        setcookie('tuitionpro_refresh', $refreshToken, [
            'expires'  => time() + $refreshTtl,
            'path'     => '/',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    //JWT
    private function clearAuthCookies(): void
    {
        if (headers_sent()) {
            return;
        }

        $isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');

        setcookie('tuitionpro_jwt', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        setcookie('tuitionpro_refresh', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => $isSecure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    //JWT
    public function getJwtService(): JwtService
    {
        return $this->jwt;
    }
}
