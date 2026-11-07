<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Session;

if (!function_exists('env')) {
    /**
     * Get an environment variable with optional default.
     */
    function env(string $key, mixed $default = null): mixed
    {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($val === false || $val === null) {
            return $default;
        }
        return match (strtolower((string) $val)) {
            'true', '(true)'   => true,
            'false', '(false)' => false,
            'empty', '(empty)' => '',
            'null', '(null)'   => null,
            default            => $val,
        };
    }
}

if (!function_exists('e')) {
    /**
     * Context-aware HTML escaping to prevent XSS.
     */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('app')) {
    /**
     * Get the Application singleton instance.
     */
    function app(): Application
    {
        return Application::getInstance();
    }
}

if (!function_exists('session')) {
    /**
     * Get the Session instance or a specific session key.
     */
    function session(?string $key = null, mixed $default = null): mixed
    {
        $session = app()->getSession();
        if ($key === null) {
            return $session;
        }
        return $session->get($key, $default);
    }
}

if (!function_exists('flash')) {
    /**
     * Set or get flash message.
     */
    function flash(string $key, mixed $value = null): mixed
    {
        $session = app()->getSession();
        if ($value !== null) {
            $session->setFlash($key, $value);
            return null;
        }
        return $session->getFlash($key);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get the current CSRF token.
     */
    function csrf_token(): string
    {
        return app()->getSession()->getCsrfToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Output a hidden CSRF token input field.
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Retrieve old input flashed to session.
     */
    function old(string $key, mixed $default = ''): mixed
    {
        $old = session('__old_input', []);
        return $old[$key] ?? $default;
    }
}

if (!function_exists('url')) {
    /**
     * Generate an application URL dynamically matching the active scheme, host, port, and subfolder.
     */
    function url(string $path = ''): string
    {
        $path = ltrim($path, '/');

        if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== '') {
            $isHttps = (isset($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) === 'on')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
            $scheme = $isHttps ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'];

            $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
            $baseDir    = rtrim(dirname($scriptName), '/');
            if ($baseDir === '/' || $baseDir === '\\' || $baseDir === '.') {
                $baseDir = '';
            }

            $base = "{$scheme}://{$host}{$baseDir}";
        } else {
            $base = rtrim(env('APP_URL', 'http://localhost:8080'), '/');
        }

        return $path === '' ? $base : $base . '/' . $path;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate URL for a static asset.
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format an amount in Sri Lankan Rupees (LKR).
     */
    function format_currency(float|int|string|null $amount, string $symbol = 'Rs. '): string
    {
        $val = (float) ($amount ?? 0);
        return $symbol . number_format($val, 2, '.', ',');
    }
}

if (!function_exists('format_date')) {
    /**
     * Format a date string in Asian/Colombo timezone.
     */
    function format_date(?string $date, string $format = 'd M Y'): string
    {
        if (empty($date)) {
            return '—';
        }
        try {
            $dt = new \DateTime($date, new \DateTimeZone(env('APP_TIMEZONE', 'Asia/Colombo')));
            return $dt->format($format);
        } catch (\Exception) {
            return (string) $date;
        }
    }
}

if (!function_exists('format_datetime')) {
    /**
     * Format a datetime string in Asian/Colombo timezone.
     */
    function format_datetime(?string $datetime, string $format = 'd M Y, h:i A'): string
    {
        return format_date($datetime, $format);
    }
}

if (!function_exists('auth_user')) {
    /**
     * Get the authenticated user record from session.
     */
    function auth_user(): ?array
    {
        return session('user');
    }
}

if (!function_exists('auth_id')) {
    /**
     * Get the authenticated user ID.
     */
    function auth_id(): ?int
    {
        $user = auth_user();
        return $user ? (int) $user['id'] : null;
    }
}

if (!function_exists('auth_check')) {
    /**
     * Check if a user is currently authenticated.
     */
    function auth_check(): bool
    {
        return auth_user() !== null;
    }
}

if (!function_exists('auth_role')) {
    /**
     * Get the authenticated user's role identifier.
     */
    function auth_role(): ?string
    {
        $user = auth_user();
        return $user['role_name'] ?? null;
    }
}

if (!function_exists('has_role')) {
    /**
     * Determine if current user matches one or more allowed roles.
     */
    function has_role(array|string $roles): bool
    {
        $currentRole = auth_role();
        if ($currentRole === null) {
            return false;
        }
        if ($currentRole === 'super_admin') {
            return true;
        }
        $allowed = is_array($roles) ? $roles : array_map('trim', explode(',', $roles));
        return in_array($currentRole, $allowed, true);
    }
}

if (!function_exists('has_permission')) {
    /**
     * Determine if current user has a specific permission.
     */
    function has_permission(string $permission): bool
    {
        $service = new \App\Services\PermissionService();
        return $service->hasPermission($permission);
    }
}

if (!function_exists('current_tenant_id')) {
    /**
     * Get the active tenant ID from session context.
     */
    function current_tenant_id(): ?int
    {
        return session('tenant_id') ? (int) session('tenant_id') : null;
    }
}

if (!function_exists('current_branch_id')) {
    /**
     * Get the active branch ID from session context.
     */
    function current_branch_id(): ?int
    {
        return session('branch_id') ? (int) session('branch_id') : null;
    }
}

if (!function_exists('institute_name')) {
    // Branding
    function institute_name(): string
    {
        $user = auth_user();
        if (!empty($user['tenant_name'])) {
            return (string) $user['tenant_name'];
        }
        return (string) env('INSTITUTE_NAME', 'Iskole Higher Education Institute');
    }
}

if (!function_exists('institute_logo')) {
    // Branding
    function institute_logo(): string
    {
        return asset('images/logo.svg');
    }
}

if (!function_exists('institute_logo_white')) {
    // Branding
    function institute_logo_white(): string
    {
        return asset('images/logo-white.svg');
    }
}

if (!function_exists('institute_logo_mark')) {
    // Branding
    function institute_logo_mark(): string
    {
        return asset('images/logo-mark.svg');
    }
}

if (!function_exists('institute_currency')) {
    // Branding
    function institute_currency(): string
    {
        return (string) env('INSTITUTE_CURRENCY', 'LKR');
    }
}

if (!function_exists('institute_currency_symbol')) {
    // Branding
    function institute_currency_symbol(): string
    {
        return (string) env('INSTITUTE_CURRENCY_SYMBOL', 'Rs. ');
    }
}

