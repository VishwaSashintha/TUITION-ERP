<?php

declare(strict_types=1);

namespace App\Core;

/**
 * TuitionPro ERP — Application Singleton
 *
 * Bootstraps all core services and acts as the service container.
 */
class Application
{
    private static ?self $instance = null;

    private Router    $router;
    private Database  $database;
    private Session   $session;
    private Logger    $logger;

    private bool $booted = false;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        // Order matters — dependencies must be initialised first.
        $this->initErrorHandling();
        $this->initLogger();
        $this->initDatabase();
        $this->initSession();
        $this->initRouter();

        $this->booted = true;
    }

    // ─── Initialisation Helpers ──────────────────────────────────────────────

    private function initErrorHandling(): void
    {
        $debug = filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);

        if ($debug) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
            ini_set('error_log', STORAGE_PATH . '/logs/php_errors.log');
        }

        set_exception_handler([$this, 'handleException']);
        set_error_handler([$this, 'handleError']);
    }

    private function initLogger(): void
    {
        $this->logger = new Logger(STORAGE_PATH . '/logs');
    }

    private function initDatabase(): void
    {
        $this->database = Database::getInstance();
    }

    private function initSession(): void
    {
        $this->session = new Session();
        $this->session->start();
    }

    private function initRouter(): void
    {
        $this->router = new Router();
        $routeFiles   = glob(ROOT_PATH . '/routes/*.php');
        foreach ($routeFiles as $routeFile) {
            require $routeFile;
        }
    }

    // ─── Error / Exception Handlers ──────────────────────────────────────────

    public function handleException(\Throwable $e): void
    {
        $this->logger?->error('Unhandled exception', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);

        if (!headers_sent()) {
            http_response_code(500);
        }

        $debug = filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);

        if ($debug) {
            echo '<pre style="font-family:monospace;padding:20px;background:#1e1e2e;color:#cdd6f4">';
            echo '<strong style="color:#f38ba8">Unhandled Exception</strong>' . PHP_EOL;
            echo htmlspecialchars($e->getMessage()) . PHP_EOL . PHP_EOL;
            echo htmlspecialchars($e->getTraceAsString());
            echo '</pre>';
        } else {
            require VIEW_PATH . '/errors/500.php';
        }
    }

    /** @param int $errno */
    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    // ─── Accessors ───────────────────────────────────────────────────────────

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function getSession(): Session
    {
        return $this->session;
    }

    public function getLogger(): Logger
    {
        return $this->logger;
    }
}

