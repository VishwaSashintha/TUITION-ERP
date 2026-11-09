<?php

declare(strict_types=1);

define('ROOT_PATH',     dirname(__DIR__, 2));
define('BACKEND_PATH',  ROOT_PATH . '/backend');
define('FRONTEND_PATH', ROOT_PATH . '/frontend');
define('APP_PATH',      BACKEND_PATH . '/app');
define('CONFIG_PATH',   BACKEND_PATH . '/config');
define('PUBLIC_PATH',   __DIR__);
define('STORAGE_PATH',  BACKEND_PATH . '/storage');
define('VIEW_PATH',     FRONTEND_PATH . '/views');
define('ROUTES_PATH',   BACKEND_PATH . '/routes');

// Serve static frontend assets directly when accessed via built-in web server
if (php_sapi_name() !== 'cli') {
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if ($requestUri !== null && str_starts_with($requestUri, '/assets/')) {
        $assetFile = FRONTEND_PATH . $requestUri;
        if (file_exists($assetFile) && !is_dir($assetFile)) {
            $ext = strtolower(pathinfo($assetFile, PATHINFO_EXTENSION));
            $mimes = [
                'css'   => 'text/css; charset=UTF-8',
                'js'    => 'application/javascript; charset=UTF-8',
                'svg'   => 'image/svg+xml',
                'png'   => 'image/png',
                'jpg'   => 'image/jpeg',
                'jpeg'  => 'image/jpeg',
                'gif'   => 'image/gif',
                'ico'   => 'image/x-icon',
                'woff'  => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf'   => 'font/ttf',
                'eot'   => 'application/vnd.ms-fontobject',
                'json'  => 'application/json',
            ];
            header('Content-Type: ' . ($mimes[$ext] ?? 'application/octet-stream'));
            header('Content-Length: ' . (string) filesize($assetFile));
            header('Cache-Control: public, max-age=3600');
            readfile($assetFile);
            exit;
        }
    }
}

$autoloader = ROOT_PATH . '/vendor/autoload.php';
if (!file_exists($autoloader)) {
    $autoloader = BACKEND_PATH . '/vendor/autoload.php';
}

if (!file_exists($autoloader)) {
    //Autoloader
    spl_autoload_register(function (string $class): void {
        $prefix = 'App\\';
        if (str_starts_with($class, $prefix)) {
            $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
            $file = APP_PATH . DIRECTORY_SEPARATOR . $relative . '.php';
            if (file_exists($file)) {
                require $file;
            }
        }
    });
} else {
    require $autoloader;
}

$envFile = ROOT_PATH . '/.env';
if (!file_exists($envFile)) {
    $envFile = BACKEND_PATH . '/.env';
}

if (file_exists($envFile)) {
    if (class_exists(\Dotenv\Dotenv::class)) {
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname($envFile));
        $dotenv->safeLoad();
    } else {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                putenv("$key=$value");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

$helpersFile = APP_PATH . '/Helpers/functions.php';
if (file_exists($helpersFile)) {
    require_once $helpersFile;
}

date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Colombo'));

$app = \App\Core\Application::getInstance();
$app->boot();

if (php_sapi_name() !== 'cli') {
    $router = $app->getRouter();
    $router->dispatch();
}
