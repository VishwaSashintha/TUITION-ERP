<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * TuitionPro ERP — Platform Administration Middleware
 *
 * Strictly confines /platform routes to Super Admins and Support Admins.
 */
class PlatformAdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next, ?string $param = null): mixed
    {
        $user = auth_user();

        if ($user === null) {
            if ($request->isAjax()) {
                Response::json(['error' => 'Unauthenticated'], 401);
                return null;
            }
            Response::redirect(url('login'));
            return null;
        }

        $platformRoles = ['super_admin', 'support_admin'];
        if (!in_array($user['role_name'] ?? '', $platformRoles, true)) {
            app()->getLogger()->warning('Non-platform user attempted platform administration access', [
                'user_id' => $user['id'],
                'uri'     => $request->getUri(),
            ]);

            if ($request->isAjax()) {
                Response::json(['error' => 'Forbidden: platform administration only.'], 403);
                return null;
            }

            flash('error', 'Access restricted to Platform Administrators.');
            Response::redirect(url('dashboard'));
            return null;
        }

        return $next();
    }
}

