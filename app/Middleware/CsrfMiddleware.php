<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * TuitionPro ERP — CSRF Protection Middleware
 *
 * Validates CSRF tokens on all state-changing HTTP requests (POST, PUT, DELETE, PATCH).
 */
class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next, ?string $param = null): mixed
    {
        // Safe read-only HTTP methods do not require CSRF token
        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next();
        }

        $session = app()->getSession();
        $token = $request->input('_csrf_token')
            ?? $request->header('X-CSRF-TOKEN')
            ?? $request->header('X-XSRF-TOKEN');

        if (!$session->validateCsrfToken($token)) {
            app()->getLogger()->warning('CSRF validation failed', [
                'uri'    => $request->getUri(),
                'method' => $request->getMethod(),
                'ip'     => $request->ip(),
            ]);

            if ($request->isAjax()) {
                Response::json(['error' => 'Invalid or expired CSRF token. Please refresh the page.'], 419);
                return null;
            }

            flash('error', 'Security check failed. Please submit the form again.');
            $referer = $_SERVER['HTTP_REFERER'] ?? url();
            Response::redirect($referer);
            return null;
        }

        return $next();
    }
}

