<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class RoleMiddleware implements MiddlewareInterface
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

        if (($user['role_name'] ?? '') === 'super_admin') {
            return $next();
        }

        if ($param !== null) {
            $allowedRoles = array_map('trim', explode(',', $param));
            $userRole     = $user['role_name'] ?? '';

            if (!in_array($userRole, $allowedRoles, true)) {
                app()->getLogger()->warning('Unauthorized role access attempt', [
                    'user_id'      => $user['id'],
                    'user_role'    => $userRole,
                    'required'     => $allowedRoles,
                    'uri'          => $request->getUri(),
                ]);

                $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
                if ($request->isAjax() || str_contains($accept, 'application/json')) {
                    Response::json(['error' => 'Forbidden: insufficient role permissions.'], 403);
                    return null;
                }

                http_response_code(403);
                $viewPath = VIEW_PATH . '/errors/403.php';
                if (file_exists($viewPath)) {
                    require $viewPath;
                    return null;
                }

                flash('error', 'You do not have permission to access this module.');
                Response::redirect(url('dashboard'));
                return null;
            }
        }

        return $next();
    }
}

