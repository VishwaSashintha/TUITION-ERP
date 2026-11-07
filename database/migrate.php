<?php

declare(strict_types=1);

//Migration

require_once dirname(__DIR__) . '/public/index.php';

use App\Core\Database;

$isFresh = in_array('--fresh', $argv, true);
$shouldSeed = in_array('--seed', $argv, true);

echo "TuitionPro ERP — Database Migration Utility\n";
echo "============================================\n";

$host = env('DB_HOST', '127.0.0.1');
$port = env('DB_PORT', '3306');
$dbName = env('DB_DATABASE', 'tuitionpro');
$user = env('DB_USERNAME', 'root');
$pass = env('DB_PASSWORD', '');

//Database
try {
    $dsnRoot = "mysql:host={$host};port={$port};charset=utf8mb4";
    $pdoRoot = new PDO($dsnRoot, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    if ($isFresh) {
        echo "Dropping existing database '{$dbName}' (--fresh)...\n";
        $pdoRoot->exec("DROP DATABASE IF EXISTS `{$dbName}`");
    }

    $pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database '{$dbName}' is ready.\n";
} catch (PDOException $e) {
    echo "Error connecting to MySQL: " . $e->getMessage() . "\n";
    exit(1);
}

//Connection
$dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
]);

$migrationsDir = __DIR__ . '/migrations';
$files = glob($migrationsDir . '/*.sql');
sort($files);

if (empty($files)) {
    echo "No migration files found in {$migrationsDir}.\n";
    exit(1);
}

echo "Applying " . count($files) . " migration file(s)...\n\n";

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

foreach ($files as $file) {
    $basename = basename($file);
    echo " - Applying: {$basename}... ";
    
    $sql = file_get_contents($file);
    if ($sql === false || trim($sql) === '') {
        echo "[SKIP] Empty\n";
        continue;
    }

    try {
        $pdo->exec($sql);
        echo "[DONE]\n";
    } catch (PDOException $e) {
        echo "[ERROR]\n";
        echo "   " . $e->getMessage() . "\n";
        exit(1);
    }
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\nAll migrations applied successfully!\n";

if ($shouldSeed) {
    echo "\nRunning seed script...\n";
    require_once __DIR__ . '/seeds/001_seed_demo_data.php';
}

