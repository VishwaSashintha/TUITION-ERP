<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    private static ?string $layout = null;
    private static array $sections = [];
    private static ?string $currentSection = null;

    public static function render(string $viewPath, array $data = [], ?string $layout = 'layouts/app'): string
    {
        $file = VIEW_PATH . '/' . str_replace('.', '/', $viewPath) . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("View file not found: {$file}");
        }

        self::$layout = $layout;
        extract($data, EXTR_SKIP);

        ob_start();
        require $file;
        $content = ob_get_clean();

        if (self::$layout !== null) {
            $layoutFile = VIEW_PATH . '/' . str_replace('.', '/', self::$layout) . '.php';
            if (file_exists($layoutFile)) {
                ob_start();
                require $layoutFile;
                return ob_get_clean();
            }
        }

        return $content;
    }

    public static function setLayout(?string $layout): void
    {
        self::$layout = $layout;
    }

    public static function section(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }

    public static function endSection(): void
    {
        if (self::$currentSection === null) {
            throw new \RuntimeException('No section started to end.');
        }
        self::$sections[self::$currentSection] = ob_get_clean();
        self::$currentSection = null;
    }

    public static function yield(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }
}

