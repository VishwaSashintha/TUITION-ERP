<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    private bool $started = false;

    public function start(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return;
        }

        $lifetime = (int) env('SESSION_LIFETIME', 120) * 60;
        $secure   = filter_var(env('SESSION_SECURE_COOKIE', 'false'), FILTER_VALIDATE_BOOLEAN)
                    || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
        $sameSite = (string) env('SESSION_SAME_SITE', 'Lax');

        if (!headers_sent()) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_httponly', '1');

            session_set_cookie_params([
                'lifetime' => $lifetime,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $secure,
                'httponly' => true,
                'samesite' => $sameSite,
            ]);

            session_name('tuitionpro_session');
        }

        if (!headers_sent()) {
            session_start();
        } elseif (session_status() !== PHP_SESSION_ACTIVE) {
            //CLI
            if (!isset($_SESSION)) {
                $_SESSION = [];
            }
        }

        $this->started = true;
        $this->ageFlash();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function regenerate(bool $deleteOldSession = true): void
    {
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_regenerate_id($deleteOldSession);
        }
    }

    public function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            session_destroy();
            $this->started = false;
        }
    }

    public function setFlash(string $key, mixed $value): void
    {
        $_SESSION['__flash']['new'][$key] = $value;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        return $_SESSION['__flash']['old'][$key] ?? $default;
    }

    private function ageFlash(): void
    {
        $_SESSION['__flash']['old'] = $_SESSION['__flash']['new'] ?? [];
        $_SESSION['__flash']['new'] = [];
    }

    public function getCsrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public function validateCsrfToken(?string $token): bool
    {
        $stored = $_SESSION['_csrf_token'] ?? null;
        if (!$stored || !$token) {
            return false;
        }
        return hash_equals($stored, $token);
    }
}
