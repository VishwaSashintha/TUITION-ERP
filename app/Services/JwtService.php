<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

//JWT
class JwtService
{
    private string $secret;
    private string $issuer;
    private string $audience;
    private int $accessTtl;
    private int $refreshTtl;
    private Database $db;

    public function __construct()
    {
        $this->secret     = (string) env('JWT_SECRET', env('APP_SECRET_KEY', 'tuitionpro-secret-key-2026-secure-jwt-token'));
        $this->issuer     = (string) env('JWT_ISSUER', 'tuitionpro-erp');
        $this->audience   = (string) env('JWT_AUDIENCE', 'tuitionpro-users');
        $this->accessTtl  = (int) env('JWT_ACCESS_TTL', 900); // 15 mins
        $this->refreshTtl = (int) env('JWT_REFRESH_TTL', 604800); // 7 days
        $this->db         = Database::getInstance();
    }

    //JWT
    public function generateAccessToken(array $user): string
    {
        $now = time();
        $isPlatform = !empty($user['is_platform']);

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $payload = [
            'iss'         => $this->issuer,
            'aud'         => $this->audience,
            'iat'         => $now,
            'nbf'         => $now,
            'exp'         => $now + $this->accessTtl,
            'jti'         => bin2hex(random_bytes(16)),
            'sub'         => (int) $user['id'],
            'user_type'   => $isPlatform ? 'platform' : 'institute',
            'role'        => $user['role_name'] ?? ($user['role'] ?? 'guest'),
            'role_display'=> $user['role_display'] ?? '',
            'name'        => $user['name'] ?? '',
            'email'       => $user['email'] ?? '',
            'tenant_id'   => $user['tenant_id'] ?? null,
            'branch_id'   => $user['branch_id'] ?? null,
        ];

        return $this->encode($header, $payload);
    }

    //JWT
    public function generateRefreshToken(array $user): string
    {
        $now = time();
        $isPlatform = !empty($user['is_platform']);
        $tokenId = bin2hex(random_bytes(16));

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $payload = [
            'iss'       => $this->issuer,
            'aud'       => $this->audience,
            'iat'       => $now,
            'nbf'       => $now,
            'exp'       => $now + $this->refreshTtl,
            'jti'       => $tokenId,
            'sub'       => (int) $user['id'],
            'user_type' => $isPlatform ? 'platform' : 'institute',
            'type'      => 'refresh',
        ];

        $tokenString = $this->encode($header, $payload);
        $tokenHash   = hash('sha256', $tokenString);

        //Database
        $this->db->execute(
            "INSERT INTO `refresh_tokens` (`user_id`, `user_type`, `token_id`, `token_hash`, `expires_at`, `created_at`)
             VALUES (:uid, :utype, :tid, :thash, :exp, NOW())",
            [
                'uid'   => (int) $user['id'],
                'utype' => $isPlatform ? 'platform' : 'institute',
                'tid'   => $tokenId,
                'thash' => $tokenHash,
                'exp'   => date('Y-m-d H:i:s', $now + $this->refreshTtl),
            ]
        );

        return $tokenString;
    }

    //JWT
    public function validateToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerB64, $payloadB64, $sigB64] = $parts;

        $header = json_decode($this->base64UrlDecode($headerB64), true);
        if (!is_array($header) || ($header['alg'] ?? '') !== 'HS256' || ($header['typ'] ?? '') !== 'JWT') {
            return null;
        }

        $expectedSig = $this->sign("$headerB64.$payloadB64");
        $actualSig   = $this->base64UrlDecode($sigB64);

        if (!hash_equals($expectedSig, $actualSig)) {
            return null;
        }

        $payload = json_decode($this->base64UrlDecode($payloadB64), true);
        if (!is_array($payload)) {
            return null;
        }

        $now = time();
        if (isset($payload['exp']) && $payload['exp'] < $now) {
            return null;
        }
        if (isset($payload['nbf']) && $payload['nbf'] > $now) {
            return null;
        }
        if (isset($payload['iss']) && $payload['iss'] !== $this->issuer) {
            return null;
        }
        if (isset($payload['aud']) && $payload['aud'] !== $this->audience) {
            return null;
        }

        return $payload;
    }

    //JWT
    public function rotateRefreshToken(string $tokenString): ?array
    {
        $payload = $this->validateToken($tokenString);
        if (!$payload || ($payload['type'] ?? '') !== 'refresh') {
            return null;
        }

        $tokenHash = hash('sha256', $tokenString);
        $record = $this->db->queryOne(
            "SELECT * FROM `refresh_tokens` WHERE `token_hash` = :thash LIMIT 1",
            ['thash' => $tokenHash]
        );

        if (!$record) {
            return null;
        }

        //Validation
        if ($record['revoked_at'] !== null) {
            $this->db->execute(
                "UPDATE `refresh_tokens` SET `revoked_at` = NOW() WHERE `user_id` = :uid AND `user_type` = :utype",
                ['uid' => $record['user_id'], 'utype' => $record['user_type']]
            );
            return null;
        }

        if (strtotime($record['expires_at']) < time()) {
            return null;
        }

        //Authentication
        $this->db->execute(
            "UPDATE `refresh_tokens` SET `revoked_at` = NOW() WHERE `id` = :id",
            ['id' => $record['id']]
        );

        $user = null;
        if ($record['user_type'] === 'platform') {
            $user = $this->db->queryOne(
                "SELECT * FROM `platform_users` WHERE `id` = :id AND `status` = 'active' LIMIT 1",
                ['id' => $record['user_id']]
            );
            if ($user) {
                $user['is_platform']  = true;
                $user['role_name']    = $user['role'];
                $user['role_display'] = $user['role'] === 'super_admin' ? 'Super Administrator' : 'Support Desk';
            }
        } else {
            $user = $this->db->queryOne(
                "SELECT u.*, r.name AS role_name, r.display_name AS role_display, t.name AS tenant_name, t.code AS tenant_code, b.name AS branch_name
                 FROM `users` u
                 JOIN `roles` r ON u.role_id = r.id
                 JOIN `tenants` t ON u.tenant_id = t.id
                 LEFT JOIN `branches` b ON u.branch_id = b.id
                 WHERE u.`id` = :id AND u.`status` = 'active' AND u.`deleted_at` IS NULL LIMIT 1",
                ['id' => $record['user_id']]
            );
            if ($user) {
                $user['is_platform'] = false;
            }
        }

        if (!$user) {
            return null;
        }

        $newAccess  = $this->generateAccessToken($user);
        $newRefresh = $this->generateRefreshToken($user);

        return [
            'access_token'  => $newAccess,
            'refresh_token' => $newRefresh,
            'user'          => $user,
        ];
    }

    //JWT
    public function revokeRefreshToken(string $tokenString): void
    {
        $tokenHash = hash('sha256', $tokenString);
        $this->db->execute(
            "UPDATE `refresh_tokens` SET `revoked_at` = NOW() WHERE `token_hash` = :thash",
            ['thash' => $tokenHash]
        );
    }

    //JWT
    private function encode(array $header, array $payload): string
    {
        $headerB64  = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $payloadB64 = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $sig        = $this->sign("$headerB64.$payloadB64");
        $sigB64     = $this->base64UrlEncode($sig);

        return "$headerB64.$payloadB64.$sigB64";
    }

    //JWT
    private function sign(string $data): string
    {
        return hash_hmac('sha256', $data, $this->secret, true);
    }

    //JWT
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    //JWT
    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

