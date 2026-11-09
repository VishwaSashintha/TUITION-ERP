<?php

declare(strict_types=1);

/**
 * TuitionPro ERP - One-Click Application Launcher
 *
 * Handles pre-flight verification, environment setup, MySQL auto-start,
 * safe schema migration, dependency installation, and server startup.
 */

$rootDir = dirname(__DIR__);
$logDir  = $rootDir . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs';
$logFile = $logDir . DIRECTORY_SEPARATOR . 'startup.log';

if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

// Initialize clean startup log
file_put_contents($logFile, sprintf(
    "==========================================================\nTuitionPro ERP Startup Log - %s\nRoot: %s\nPHP: %s (%s)\n==========================================================\n",
    date('Y-m-d H:i:s'),
    $rootDir,
    PHP_BINARY,
    PHP_VERSION
));

function appLog(string $level, string $message, ?string $detail = null): void
{
    global $logFile;
    $entry = sprintf("[%s] [%s] %s\n", date('H:i:s'), strtoupper($level), $message);
    if ($detail !== null) {
        // Redact any possible credentials or secrets before logging
        $sanitized = preg_replace('/(password|secret|key|token)[=:]\s*[^\s,]+/i', '$1=********', $detail);
        $entry .= "    Detail: " . trim($sanitized) . "\n";
    }
    @file_put_contents($logFile, $entry, FILE_APPEND);
}

function printStatus(string $tag, string $message, string $color = "\033[0m"): void
{
    echo sprintf("  %-8s %s\n", "[{$tag}]", $message);
}

function printBanner(): void
{
    echo "\n";
    echo "  =========================================================\n";
    echo "   TuitionPro ERP - One-Click Launcher\n";
    echo "  =========================================================\n\n";
}

function fatalError(string $title, array $details, array $solutions): void
{
    global $logFile;
    appLog('FATAL', $title, implode("\n", $details));

    echo "\n";
    echo "  ---------------------------------------------------------\n";
    echo "  [ERROR] " . $title . "\n";
    echo "  ---------------------------------------------------------\n";

    if (!empty($details)) {
        foreach ($details as $d) {
            echo "   * " . $d . "\n";
        }
        echo "\n";
    }

    if (!empty($solutions)) {
        echo "  How to resolve:\n";
        foreach ($solutions as $s) {
            echo "   -> " . $s . "\n";
        }
        echo "\n";
    }

    echo "  Technical log saved to: " . $logFile . "\n";
    echo "  Press Enter to close this window...";
    fgets(STDIN);
    exit(1);
}

printBanner();

// -----------------------------------------------------------------
// STEP 1: Verify PHP Version (>= 8.2)
// -----------------------------------------------------------------
appLog('INFO', 'Verifying PHP version');
if (PHP_VERSION_ID < 80200) {
    fatalError(
        'Incompatible PHP Version',
        ['Detected PHP ' . PHP_VERSION . ', but TuitionPro ERP requires PHP 8.2 or higher.'],
        [
            'Download and install XAMPP with PHP 8.2+ from https://www.apachefriends.org/',
            'Update your Windows PATH environment variable to point to the PHP 8.2 directory.'
        ]
    );
}
printStatus('OK', 'PHP ' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION . ' detected');
appLog('OK', 'PHP version check passed');

// -----------------------------------------------------------------
// STEP 2: Verify Required PHP Extensions
// -----------------------------------------------------------------
appLog('INFO', 'Checking required PHP extensions');
$requiredExtensions = [
    'pdo'        => 'PHP Data Objects core',
    'pdo_mysql'  => 'MySQL PDO driver',
    'mbstring'   => 'Multibyte string support',
    'openssl'    => 'OpenSSL encryption and JWT security',
    'curl'       => 'cURL HTTP client',
    'json'       => 'JSON parser and encoder',
    'ctype'      => 'Character type checking',
    'fileinfo'   => 'MIME type identification for uploads',
];

$recommendedExtensions = [
    'gd'         => 'GD Graphics library (required for QR codes and ID cards)',
    'zip'        => 'Zip archive support (required for Excel exports)',
];

$missingRequired = [];
foreach ($requiredExtensions as $ext => $description) {
    if (!extension_loaded($ext)) {
        $missingRequired[$ext] = $description;
    }
}

if (!empty($missingRequired)) {
    $missingList = [];
    $solutions = ['Open your php.ini configuration file: ' . (php_ini_loaded_file() ?: 'C:\xampp\php\php.ini')];
    foreach ($missingRequired as $ext => $desc) {
        $missingList[] = "extension={$ext} ({$desc})";
        $solutions[] = "Remove the semicolon (;) from the line: extension={$ext}";
    }
    $solutions[] = 'Save php.ini and run start.bat again.';

    fatalError('Missing Required PHP Extensions', $missingList, $solutions);
}

$missingRecommended = [];
foreach ($recommendedExtensions as $ext => $desc) {
    if (!extension_loaded($ext)) {
        $missingRecommended[$ext] = $desc;
    }
}
if (!empty($missingRecommended)) {
    foreach ($missingRecommended as $ext => $desc) {
        printStatus('WARN', "Recommended extension '{$ext}' is disabled ({$desc})");
        appLog('WARN', "Recommended extension '{$ext}' is disabled");
    }
} else {
    printStatus('OK', 'All required and recommended PHP extensions are enabled');
}
appLog('OK', 'PHP extension check passed');

// -----------------------------------------------------------------
// STEP 3: Environment Configuration (.env)
// -----------------------------------------------------------------
appLog('INFO', 'Checking environment configuration');
$envFile = $rootDir . DIRECTORY_SEPARATOR . '.env';
$envExample = $rootDir . DIRECTORY_SEPARATOR . '.env.example';

if (!file_exists($envFile)) {
    if (file_exists($envExample)) {
        if (!@copy($envExample, $envFile)) {
            fatalError(
                'Cannot Create .env File',
                ['Unable to copy .env.example to .env due to file permission restrictions.'],
                ['Ensure the application directory has write permissions for your user account.']
            );
        }
        printStatus('OK', 'Created initial .env configuration from template');
        appLog('OK', 'Copied .env.example to .env');
    } else {
        fatalError(
            'Missing Environment Template',
            ['Neither .env nor .env.example was found in the project root.'],
            ['Restore the repository files or pull the latest changes from GitHub.']
        );
    }
} else {
    printStatus('OK', 'Environment configuration file (.env) found');
}

// Parse .env settings
$envConfig = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
        continue;
    }
    [$key, $value] = explode('=', $line, 2);
    $envConfig[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
}

$dbHost = $envConfig['DB_HOST'] ?? '127.0.0.1';
$dbPort = (int)($envConfig['DB_PORT'] ?? 3306);
$dbName = $envConfig['DB_DATABASE'] ?? 'tuitionpro';
$dbUser = $envConfig['DB_USERNAME'] ?? 'root';
$dbPass = $envConfig['DB_PASSWORD'] ?? '';
$appUrl = $envConfig['APP_URL'] ?? 'http://localhost:8080';

$parsedUrl = parse_url($appUrl);
$serverPort = (int)($parsedUrl['port'] ?? 8080);
$serverHost = $parsedUrl['host'] ?? 'localhost';

appLog('INFO', "Target Database: {$dbHost}:{$dbPort}/{$dbName} (User: {$dbUser})");
appLog('INFO', "Target Application URL: {$appUrl} (Port: {$serverPort})");

// -----------------------------------------------------------------
// STEP 4: MySQL Service Detection and Auto-Start
// -----------------------------------------------------------------
appLog('INFO', 'Verifying MySQL service availability');

function isPortOpen(string $host, int $port, int $timeoutSec = 1): bool
{
    $fp = @fsockopen($host, $port, $errno, $errstr, (float)$timeoutSec);
    if ($fp) {
        fclose($fp);
        return true;
    }
    return false;
}

$mysqlRunning = isPortOpen($dbHost, $dbPort, 1);

if (!$mysqlRunning) {
    printStatus('INFO', 'MySQL service is not running. Attempting to start MySQL automatically...');
    appLog('WARN', "MySQL not detected on {$dbHost}:{$dbPort}. Searching for XAMPP MySQL...");

    $started = false;

    // Search for XAMPP MySQL startup scripts/executables
    $mysqlLocations = [
        'C:\xampp\mysql\bin\mysqld.exe',
        'C:\xampp\mysql_start.bat',
        'D:\xampp\mysql\bin\mysqld.exe',
        'D:\xampp\mysql_start.bat',
    ];

    foreach ($mysqlLocations as $loc) {
        if (file_exists($loc)) {
            if (str_ends_with($loc, 'mysqld.exe')) {
                $cmd = sprintf('start "XAMPP MySQL" /min "%s" --defaults-file="%s" --standalone', $loc, dirname($loc) . '\my.ini');
            } else {
                $cmd = sprintf('start "XAMPP MySQL" /min cmd /c "%s"', $loc);
            }
            appLog('INFO', "Executing MySQL startup: {$cmd}");
            pclose(popen($cmd, 'r'));
            $started = true;
            break;
        }
    }

    if (!$started) {
        // Try Windows Service net start mysql
        exec('net start mysql 2>&1', $netOutput, $netCode);
        if ($netCode === 0) {
            $started = true;
            appLog('INFO', 'Started MySQL via Windows service');
        }
    }

    if ($started) {
        // Wait up to 25 seconds for MySQL to respond
        $waitAttempts = 0;
        $maxWait = 25;
        while ($waitAttempts < $maxWait) {
            sleep(1);
            $waitAttempts++;
            if (isPortOpen($dbHost, $dbPort, 1)) {
                $mysqlRunning = true;
                break;
            }
        }
    }

    if (!$mysqlRunning) {
        fatalError(
            'Cannot Connect to MySQL Server',
            [
                "Could not reach MySQL on {$dbHost}:{$dbPort}.",
                "Automated MySQL startup did not respond within {$maxWait} seconds."
            ],
            [
                'Open XAMPP Control Panel and click "Start" next to MySQL.',
                'Ensure no firewall or antivirus is blocking port ' . $dbPort . '.',
                'Verify DB_HOST and DB_PORT in .env match your MySQL server settings.'
            ]
        );
    }

    printStatus('OK', 'MySQL service started and listening on port ' . $dbPort);
    appLog('OK', 'MySQL service started successfully');
} else {
    printStatus('OK', "MySQL is running on port {$dbPort}");
    appLog('OK', "MySQL port {$dbPort} open");
}

// -----------------------------------------------------------------
// STEP 5: Database Connection & Schema Verification
// -----------------------------------------------------------------
appLog('INFO', 'Verifying MySQL credentials and database');

try {
    $dsnServer = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $dbHost, $dbPort);
    $pdoServer = new PDO($dsnServer, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 3,
    ]);
} catch (PDOException $e) {
    fatalError(
        'MySQL Authentication Failed',
        ['Could not authenticate with MySQL server: ' . $e->getMessage()],
        [
            'Check DB_USERNAME and DB_PASSWORD in .env file.',
            'For default XAMPP setups, DB_USERNAME is "root" and DB_PASSWORD is empty.'
        ]
    );
}

// Check / create database
try {
    $pdoServer->exec(sprintf(
        'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
        $dbName
    ));
    appLog('OK', "Database '{$dbName}' verified/created");
} catch (PDOException $e) {
    fatalError(
        'Cannot Access or Create Database',
        ["Failed creating/accessing database '{$dbName}': " . $e->getMessage()],
        ['Ensure the MySQL user has CREATE and SELECT privileges.']
    );
}

// Connect directly to the application database
try {
    $dsnDb = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $dbHost, $dbPort, $dbName);
    $pdoDb = new PDO($dsnDb, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    fatalError('Database Access Failed', [$e->getMessage()], ['Check database configuration in .env.']);
}

// Check if tables already exist (SAFE: Never overwrite existing data)
$tablesStmt = $pdoDb->query("SHOW TABLES LIKE 'users'");
$hasTables = ($tablesStmt && $tablesStmt->rowCount() > 0);

if (!$hasTables) {
    printStatus('INFO', "Database '{$dbName}' is empty. Initializing schema safely...");
    appLog('INFO', 'Running initial migrations (non-destructive, schema only)');

    $migrateScript = $rootDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrate.php';
    if (file_exists($migrateScript)) {
        // Execute without --seed and without --fresh to preserve safety
        $phpExe = PHP_BINARY;
        exec(sprintf('"%s" "%s"', $phpExe, $migrateScript), $migOutput, $migCode);
        if ($migCode !== 0) {
            fatalError(
                'Initial Database Migration Failed',
                $migOutput,
                ['Check database/migrations directory and verify MySQL permissions.']
            );
        }
        printStatus('OK', 'Initial database schema created successfully');
        appLog('OK', 'Initial migration succeeded');
    }
} else {
    printStatus('OK', "Database '{$dbName}' is ready (existing data preserved)");
    appLog('OK', "Existing tables detected in '{$dbName}', preserving existing data");
}

// -----------------------------------------------------------------
// STEP 6: Verify Composer Dependencies
// -----------------------------------------------------------------
appLog('INFO', 'Checking Composer autoloader and vendor packages');
$autoloadFile = $rootDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!file_exists($autoloadFile)) {
    printStatus('WARN', 'Composer vendor packages not detected. Installing dependencies...');
    appLog('WARN', 'vendor/autoload.php not found. Attempting composer install');

    $composerPhar = $rootDir . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'composer.phar';
    if (!file_exists($composerPhar)) {
        // Attempt download if curl available
        $downloadUrl = 'https://getcomposer.org/download/latest-stable/composer.phar';
        $toolsDir = dirname($composerPhar);
        if (!is_dir($toolsDir)) {
            @mkdir($toolsDir, 0755, true);
        }
        $pharContent = @file_get_contents($downloadUrl);
        if ($pharContent !== false && strlen($pharContent) > 100000) {
            file_put_contents($composerPhar, $pharContent);
            appLog('OK', 'Downloaded Composer PHAR to tools/composer.phar');
        }
    }

    if (file_exists($composerPhar)) {
        $composerCmd = sprintf(
            '"%s" "%s" install --no-interaction --no-dev --optimize-autoloader --working-dir="%s"',
            PHP_BINARY,
            $composerPhar,
            $rootDir
        );
        appLog('INFO', 'Running: ' . $composerCmd);
        exec($composerCmd . ' 2>&1', $compOutput, $compCode);
        if (file_exists($autoloadFile)) {
            printStatus('OK', 'Composer dependencies installed successfully');
            appLog('OK', 'Composer installation completed');
        } else {
            printStatus('WARN', 'Composer install returned warning. Built-in autoloader fallback will be used.');
            appLog('WARN', 'Composer returned non-zero. Output: ' . implode("\n", $compOutput));
        }
    } else {
        printStatus('INFO', 'Composer PHAR not found. Using built-in PSR-4 autoloader.');
        appLog('INFO', 'Composer PHAR unavailable. Core classes autoload via spl_autoload_register');
    }
} else {
    printStatus('OK', 'Composer dependencies verified');
    appLog('OK', 'vendor/autoload.php found');
}

// -----------------------------------------------------------------
// STEP 7: Check Port & Running Application (Avoid Duplicate Servers)
// -----------------------------------------------------------------
appLog('INFO', "Checking status of port {$serverPort}");

function checkTuitionProRunning(string $url): bool
{
    $loginUrl = rtrim($url, '/') . '/login';
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'timeout' => 2,
            'ignore_errors' => true,
            'header'  => "User-Agent: TuitionPro-Launcher/1.0\r\n",
        ],
    ]);

    $res = @file_get_contents($loginUrl, false, $ctx);
    if ($res !== false && isset($http_response_header)) {
        $statusLine = $http_response_header[0] ?? '';
        if (preg_match('/HTTP\/\d\.\d\s+(200|302)/', $statusLine)) {
            return true;
        }
    }
    return false;
}

if (isPortOpen($serverHost, $serverPort, 1)) {
    if (checkTuitionProRunning($appUrl)) {
        printStatus('OK', "TuitionPro ERP is already running at {$appUrl}");
        appLog('OK', "Existing TuitionPro instance detected at {$appUrl}. Opening browser.");

        echo "\n";
        echo "  =========================================================\n";
        echo "   TuitionPro ERP is already active!\n\n";
        echo "   Local URL : {$appUrl}\n\n";
        echo "   Opening application in your default web browser...\n";
        echo "  =========================================================\n\n";

        pclose(popen('start "" ' . escapeshellarg($appUrl), 'r'));
        sleep(2);
        exit(0);
    } else {
        fatalError(
            'Port Conflict Detected',
            [
                "Port {$serverPort} is already occupied by another application on your system.",
                "The application occupying port {$serverPort} is not TuitionPro ERP."
            ],
            [
                'Open Task Manager and check for other web servers or conflicting programs.',
                "Or run 'scripts\\stop.bat' to terminate any lingering processes.",
                'Or change APP_URL port in .env (e.g. http://localhost:8081).'
            ]
        );
    }
}

// -----------------------------------------------------------------
// STEP 8: Start Development Server & Open Browser
// -----------------------------------------------------------------
$backendPublic = $rootDir . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'public';
$routerScript  = $backendPublic . DIRECTORY_SEPARATOR . 'index.php';

if (!file_exists($routerScript)) {
    fatalError(
        'Missing Entry Point',
        ['Could not locate ' . $routerScript],
        ['Verify repository structure has backend/public/index.php.']
    );
}

// Launch background health-check & browser opener
$browserScript = $rootDir . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'open_browser.php';
if (file_exists($browserScript)) {
    $browserCmd = sprintf('start /min "" "%s" "%s" "%s"', PHP_BINARY, $browserScript, $appUrl);
    pclose(popen($browserCmd, 'r'));
    appLog('INFO', "Started background browser opener for {$appUrl}");
}

// Output ready banner
echo "\n";
echo "  =========================================================\n";
echo "   TuitionPro ERP is starting!\n\n";
echo "   Local URL    : {$appUrl}\n";
echo "   Entry Point  : backend\\public\\index.php\n";
echo "   Database     : {$dbName} ({$dbHost}:{$dbPort})\n\n";
echo "   Demo User Accounts:\n";
echo "     * Super Admin      : superadmin@tuitionpro.lk / Password@123\n";
echo "     * Institute Owner  : apex.owner@tuitionpro.lk  / Password@123\n";
echo "     * Receptionist     : apex.reception@tuitionpro.lk / Password@123\n";
echo "     * Teacher          : apex.fernando@tuitionpro.lk / Password@123\n";
echo "     * Student          : apex.student1@tuitionpro.lk / Password@123\n";
echo "     * Parent           : apex.parent1@tuitionpro.lk  / Password@123\n\n";
echo "   The browser will open automatically once the server is ready.\n";
echo "   Press Ctrl+C in this window at any time to stop the server.\n";
echo "  =========================================================\n\n";

appLog('OK', "Launching PHP built-in server on {$serverHost}:{$serverPort}");

// Run PHP built-in server in foreground
$serverCmd = sprintf(
    '"%s" -S %s:%d -t "%s" "%s"',
    PHP_BINARY,
    $serverHost,
    $serverPort,
    $backendPublic,
    $routerScript
);

passthru($serverCmd);

