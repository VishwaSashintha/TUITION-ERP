<?php

declare(strict_types=1);

namespace App\Core;

/**
 * TuitionPro ERP — View Renderer
 *
 * Renders PHP view templates with layout support and variable data passing.
 */
class View
{
    private static ?string $layout = null;
    private static array $sections = [];
    private static ?string $currentSection = null;

    /**
     * Render a view file with optional data and layout.
     *
     * @param string $viewPath Relative to resources/views/, without .php (e.g. 'auth/login')
     * @param array<string,mixed> $data
     */
    public static function render(string $viewPath, array $data = [], ?string $layout = 'layouts/app'): string
    {
        $file = VIEW_PATH . '/' . str_replace('.', '/', $viewPath) . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("View file not found: {$file}");
        }

        self::$layout = $layout;

        // Extract variables into local scope
        extract($data, EXTR_SKIP);

        // Start output buffering for view content
        ob_start();
        require $file;
        $content = ob_get_clean();

        // If a layout is specified, render the layout with $content
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

