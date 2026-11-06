<?php

declare(strict_types=1);

namespace App\Core;

/**
 * TuitionPro ERP — HTTP Request Encapsulation
 */
class Request
{
    private string $method;
    private string $uri;
    private string $path;
    private array  $query;
    private array  $post;
    private array  $files;
    private array  $server;
    private array  $headers;
    private ?array $jsonBody = null;

    public function __construct()
    {
        $this->server  = $_SERVER;
        $this->method  = strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
        $this->query   = $_GET;
        $this->post    = $_POST;
        $this->files   = $_FILES;
        $this->headers = $this->extractHeaders();

        // Support _method spoofing for PUT/DELETE via POST form
        if ($this->method === 'POST' && isset($this->post['_method'])) {
            $spoofed = strtoupper((string) $this->post['_method']);
            if (in_array($spoofed, ['PUT', 'PATCH', 'DELETE'], true)) {
                $this->method = $spoofed;
            }
        }

        $rawUri     = $this->server['REQUEST_URI'] ?? '/';
        $parsedUri  = parse_url($rawUri);
        $this->uri  = $rawUri;
        $this->path = $this->normalizePath($parsedUri['path'] ?? '/');

        // Parse JSON body if Content-Type is application/json
        $contentType = $this->header('Content-Type', '');
        if (str_contains($contentType, 'application/json')) {
            $input = file_get_contents('php://input');
            if (!empty($input)) {
                $decoded = json_decode($input, true);
                if (is_array($decoded)) {
                    $this->jsonBody = $decoded;
                }
            }
        }
    }

    private function normalizePath(string $rawPath): string
    {
        // Strip base folder if running under subfolder like /tuitionpro/public/
        $scriptName = $this->server['SCRIPT_NAME'] ?? '';
        $baseDir    = str_replace('\\', '/', dirname($scriptName));

        if ($baseDir !== '/' && str_starts_with($rawPath, $baseDir)) {
            $rawPath = substr($rawPath, strlen($baseDir));
        }

        $path = '/' . trim($rawPath, '/');
        return $path === '' ? '/' : $path;
    }

    private function extractHeaders(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true)) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function isPut(): bool
    {
        return $this->method === 'PUT';
    }

    public function isDelete(): bool
    {
        return $this->method === 'DELETE';
    }

    public function isAjax(): bool
    {
        return ($this->header('X-Requested-With') === 'XMLHttpRequest')
            || str_contains($this->header('Accept', ''), 'application/json');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->input($key, $default);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        if ($this->jsonBody !== null && array_key_exists($key, $this->jsonBody)) {
            return $this->jsonBody[$key];
        }
        if (array_key_exists($key, $this->post)) {
            return $this->post[$key];
        }
        if (array_key_exists($key, $this->query)) {
            return $this->query[$key];
        }
        return $default;
    }

    public function all(): array
    {
        if ($this->jsonBody !== null) {
            return array_merge($this->query, $this->jsonBody);
        }
        return array_merge($this->query, $this->post);
    }

    public function only(array $keys): array
    {
        $all = $this->all();
        $result = [];
        foreach ($keys as $k) {
            if (array_key_exists($k, $all)) {
                $result[$k] = $all[$k];
            }
        }
        return $result;
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    public function post(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->post;
        }
        return $this->post[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function header(string $key, mixed $default = null): mixed
    {
        return $this->headers[$key] ?? $this->headers[strtolower($key)] ?? $default;
    }

    public function ip(): string
    {
        return $this->server['HTTP_X_FORWARDED_FOR']
            ?? $this->server['REMOTE_ADDR']
            ?? '127.0.0.1';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }
}

