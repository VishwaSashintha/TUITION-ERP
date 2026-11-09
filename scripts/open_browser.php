<?php

declare(strict_types=1);

/**
 * TuitionPro ERP - Background Health Check & Browser Opener
 *
 * Polls the application login endpoint until HTTP 200 is returned,
 * then opens the application in the default web browser.
 */

$url = $argv[1] ?? 'http://localhost:8080';
$checkUrl = rtrim($url, '/') . '/login';
$maxSeconds = 25;
$startTime = time();

while ((time() - $startTime) < $maxSeconds) {
    usleep(250000); // 250ms interval

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 1.5,
            'ignore_errors' => true,
            'header' => "User-Agent: TuitionPro-HealthCheck/1.0\r\n",
        ],
    ]);

    $content = @file_get_contents($checkUrl, false, $context);
    if ($content !== false && isset($http_response_header)) {
        $statusLine = $http_response_header[0] ?? '';
        if (preg_match('/HTTP\/\d\.\d\s+(200|302)/', $statusLine)) {
            // Server is ready! Open default browser
            pclose(popen('start "" ' . escapeshellarg($url), 'r'));
            exit(0);
        }
    }
}

// Timed out
exit(1);

