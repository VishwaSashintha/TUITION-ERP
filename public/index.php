<?php

declare(strict_types=1);

define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH',ROOT_PATH . '/storage');
define('VIEW_PATH',   ROOT_PATH . '/resources/views');

$autoloader = ROOT_PATH . '/vendor/autoload.php';
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
if (file_exists($envFile)) {
    if (class_exists(\Dotenv\Dotenv::class)) {
        $dotenv = \Dotenv\Dotenv::createImmutable(ROOT_PATH);
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
