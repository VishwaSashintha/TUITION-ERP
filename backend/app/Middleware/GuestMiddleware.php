<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class GuestMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next, ?string $param = null): mixed
    {
        $user = auth_user();

        if ($user !== null) {
            $role = $user['role_name'] ?? 'student';
            $redirectUrl = match ($role) {
                'super_admin', 'support_admin' => url('platform/dashboard'),
                'student'                       => url('portal/student/dashboard'),
                'parent'                        => url('portal/parent/dashboard'),
                default                         => url('dashboard'),
            };

            Response::redirect($redirectUrl);
            return null;
        }

        return $next();
    }
}

