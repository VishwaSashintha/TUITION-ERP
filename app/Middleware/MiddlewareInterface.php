<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

interface MiddlewareInterface
{
    /**
     * Handle an incoming HTTP request.
     *
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @param string|null $param Optional parameter passed via route definition (e.g. 'role:admin')
     * @return mixed
     */
    public function handle(Request $request, Response $response, callable $next, ?string $param = null): mixed;
}

