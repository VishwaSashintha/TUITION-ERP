<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\JwtService;

//Auth
class AuthMiddleware implements MiddlewareInterface
{
    private JwtService $jwt;

    public function __construct()
    {
        $this->jwt = new JwtService();
    }

    //Auth
    public function handle(Request $request, Response $response, callable $next, ?string $param = null): mixed
    {
        $user = auth_user();

        if ($user === null) {
            $token = $this->extractToken($request);

            if ($token !== null) {
                $payload = $this->jwt->validateToken($token);

                if ($payload !== null) {
                    $this->establishUserContext($payload);
                    $user = auth_user();
                } else {
                    //Authentication
                    $refreshToken = $_COOKIE['tuitionpro_refresh'] ?? $request->header('X-Refresh-Token');
                    if ($refreshToken) {
                        $rotated = $this->jwt->rotateRefreshToken($refreshToken);
                        if ($rotated !== null) {
                            $this->setAuthCookies($rotated['access_token'], $rotated['refresh_token']);
                            $this->establishUserContext($rotated['user']);
                            $user = auth_user();
                        }
                    }
                }
            }
        }

        if ($user === null) {
            if ($request->isAjax() || str_contains($request->header('Accept') ?? '', 'application/json')) {
                Response::json(['error' => 'Unauthenticated', 'redirect' => url('login')], 401);
                return null;
            }

            session('intended_url', $request->getUri());
            flash('warning', 'Please sign in to access this page.');
            Response::redirect(url('login'));
            return null;
        }

        return $next();
    }

    //JWT
    private function extractToken(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return $_COOKIE['tuitionpro_jwt'] ?? null;
    }

    //Auth
    private function establishUserContext(array $payload): void
    {
        $userData = [
            'id'           => (int) ($payload['sub'] ?? $payload['id'] ?? 0),
            'name'         => $payload['name'] ?? '',
            'email'        => $payload['email'] ?? '',
            'role_name'    => $payload['role'] ?? ($payload['role_name'] ?? 'guest'),
            'role_display' => $payload['role_display'] ?? '',
            'tenant_id'    => $payload['tenant_id'] ?? null,
            'branch_id'    => $payload['branch_id'] ?? null,
            'is_platform'  => ($payload['user_type'] ?? '') === 'platform' || !empty($payload['is_platform']),
        ];

        session('user', $userData);
        if ($userData['tenant_id']) {
            session('tenant_id', (int) $userData['tenant_id']);
        }
        if ($userData['branch_id']) {
            session('branch_id', (int) $userData['branch_id']);
        }
    }

    //JWT
    private function setAuthCookies(string $accessToken, string $refreshToken): void
    {
        if (headers_sent()) {
            return;
        }

        $isSecure   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        $accessTtl  = (int) env('JWT_ACCESS_TTL', 900);
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
}
