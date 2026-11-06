<?php

declare(strict_types=1);

namespace App\Core;

/**
 * TuitionPro ERP — HTTP Response Encapsulation
 */
class Response
{
    private int   $statusCode = 200;
    private array $headers    = [];
    private string $content   = '';

    public function setStatusCode(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function send(): void
    {
        if (!headers_sent()) {
            http_response_code($this->statusCode);
            foreach ($this->headers as $name => $value) {
                header("{$name}: {$value}");
            }
        }
        echo $this->content;
    }

    public static function json(mixed $data, int $statusCode = 200): void
    {
        $resp = new self();
        $resp->setStatusCode($statusCode)
             ->header('Content-Type', 'application/json; charset=utf-8')
             ->setContent(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE))
             ->send();
        exit;
    }

    public static function redirect(string $url, int $statusCode = 302): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header("Location: {$url}");
        } else {
            echo "<script>window.location.href=" . json_encode($url) . ";</script>";
        }
        exit;
    }
}

