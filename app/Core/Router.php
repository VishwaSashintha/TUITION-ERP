<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $groupStack = [];
    private Request $request;
    private Response $response;

    public function __construct()
    {
        $this->request  = new Request();
        $this->response = new Response();
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function get(string $path, array|callable|string $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable|string $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array|callable|string $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array|callable|string $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    private function addRoute(string $method, string $path, array|callable|string $handler, array $middleware): self
    {
        $prefix = '';
        $groupMiddleware = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $gm = is_array($group['middleware']) ? $group['middleware'] : [$group['middleware']];
                $groupMiddleware = array_merge($groupMiddleware, $gm);
            }
        }

        $fullPath = '/' . trim($prefix . '/' . trim($path, '/'), '/');
        if ($fullPath === '') {
            $fullPath = '/';
        }

        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $fullPath);
        $regex   = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method'     => $method,
            'path'       => $fullPath,
            'regex'      => $regex,
            'handler'    => $handler,
            'middleware' => array_merge($groupMiddleware, $middleware),
        ];

        return $this;
    }

    public function dispatch(): void
    {
        $requestMethod = $this->request->getMethod();
        $requestPath   = $this->request->getPath();

        $allowedMethods = [];
        $matchedRoute   = null;
        $params         = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $requestPath, $matches)) {
                if ($route['method'] === $requestMethod) {
                    $matchedRoute = $route;
                    foreach ($matches as $k => $v) {
                        if (is_string($k)) {
                            $params[$k] = $v;
                        }
                    }
                    break;
                } else {
                    $allowedMethods[] = $route['method'];
                }
            }
        }

        if ($matchedRoute === null) {
            if (!empty($allowedMethods)) {
                $this->handleMethodNotAllowed(array_unique($allowedMethods));
            } else {
                $this->handleNotFound();
            }
            return;
        }

        $this->runMiddlewarePipeline($matchedRoute['middleware'], function () use ($matchedRoute, $params) {
            $this->executeHandler($matchedRoute['handler'], $params);
        });
    }

    private function runMiddlewarePipeline(array $middlewareList, callable $destination): void
    {
        $middlewareMap = [
            'auth'     => \App\Middleware\AuthMiddleware::class,
            'guest'    => \App\Middleware\GuestMiddleware::class,
            'csrf'     => \App\Middleware\CsrfMiddleware::class,
            'tenant'   => \App\Middleware\TenantMiddleware::class,
            'role'     => \App\Middleware\RoleMiddleware::class,
            'platform' => \App\Middleware\PlatformAdminMiddleware::class,
        ];

        $pipeline = array_reduce(
            array_reverse($middlewareList),
            function ($next, $middlewareItem) use ($middlewareMap) {
                return function () use ($next, $middlewareItem, $middlewareMap) {
                    $parts = explode(':', $middlewareItem, 2);
                    $alias = $parts[0];
                    $param = $parts[1] ?? null;

                    if (!isset($middlewareMap[$alias])) {
                        return $next();
                    }

                    $class = $middlewareMap[$alias];
                    $instance = new $class();
                    return $instance->handle($this->request, $this->response, $next, $param);
                };
            },
            $destination
        );

        $pipeline();
    }

    private function executeHandler(array|callable|string $handler, array $params): void
    {
        if (is_callable($handler)) {
            $result = call_user_func_array($handler, array_merge([$this->request, $this->response], $params));
            if ($result instanceof Response) {
                $result->send();
            }
            return;
        }

        if (is_string($handler) && str_contains($handler, '@')) {
            $parts = explode('@', $handler);
            $controllerClass = $parts[0];
            $method = $parts[1];
        } elseif (is_array($handler)) {
            $controllerClass = $handler[0];
            $method = $handler[1];
        } else {
            throw new \RuntimeException('Invalid route handler specified.');
        }

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller class '{$controllerClass}' not found.");
        }

        $controller = new $controllerClass();
        if (!method_exists($controller, $method)) {
            throw new \RuntimeException("Method '{$method}' does not exist on controller '{$controllerClass}'.");
        }

        $refMethod = new \ReflectionMethod($controller, $method);
        $methodParams = $refMethod->getParameters();
        $args = [];
        $remainingParams = $params;

        foreach ($methodParams as $p) {
            $type = $p->getType();
            $typeName = $type instanceof \ReflectionNamedType ? $type->getName() : '';
            $paramName = $p->getName();

            if ($typeName === Request::class || is_a($typeName, Request::class, true) || $paramName === 'request') {
                $args[] = $this->request;
            } elseif ($typeName === Response::class || is_a($typeName, Response::class, true) || $paramName === 'response') {
                $args[] = $this->response;
            } elseif (array_key_exists($paramName, $remainingParams)) {
                $args[] = $remainingParams[$paramName];
                unset($remainingParams[$paramName]);
            } elseif (!empty($remainingParams)) {
                $args[] = array_shift($remainingParams);
            } elseif ($p->isDefaultValueAvailable()) {
                $args[] = $p->getDefaultValue();
            } else {
                $args[] = null;
            }
        }

        $result = $refMethod->invokeArgs($controller, $args);
        if ($result instanceof Response) {
            $result->send();
        }
    }

    private function handleNotFound(): void
    {
        $this->response->setStatusCode(404);
        if ($this->request->isAjax()) {
            Response::json(['error' => 'Resource not found', 'status' => 404], 404);
            return;
        }
        $errorFile = VIEW_PATH . '/errors/404.php';
        if (file_exists($errorFile)) {
            require $errorFile;
        } else {
            echo "<h1>404 Not Found</h1><p>The requested page could not be found.</p>";
        }
    }

    private function handleMethodNotAllowed(array $allowed): void
    {
        $this->response->setStatusCode(405);
        $this->response->header('Allow', implode(', ', $allowed));
        if ($this->request->isAjax()) {
            Response::json(['error' => 'Method Not Allowed', 'allowed' => $allowed], 405);
            return;
        }
        echo "<h1>405 Method Not Allowed</h1>";
    }
}

