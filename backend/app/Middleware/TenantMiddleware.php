<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

class TenantMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Response $response, callable $next, ?string $param = null): mixed
    {
        $user = auth_user();

        if ($user && in_array($user['role_name'] ?? '', ['super_admin', 'support_admin'], true)) {
            return $next();
        }

        $tenantId = current_tenant_id();

        if (!$tenantId && $user && !empty($user['tenant_id'])) {
            $tenantId = (int) $user['tenant_id'];
            session(['tenant_id' => $tenantId]);
        }

        if (!$tenantId) {
            app()->getLogger()->warning('Access denied: missing tenant context', [
                'uri' => $request->getUri(),
                'user_id' => $user['id'] ?? null,
            ]);

            if ($request->isAjax()) {
                Response::json(['error' => 'No active institute context found.'], 403);
                return null;
            }

            flash('error', 'Unable to resolve institute context. Please sign in again.');
            Response::redirect(url('login'));
            return null;
        }

        return $next();
    }
}

