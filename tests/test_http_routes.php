<?php

declare(strict_types=1);

$baseUrl = 'http://localhost:8080';
$passed = 0;
$failed = 0;

function runTest(string $name, callable $fn): void {
    global $passed, $failed;
    try {
        $fn();
        echo " [PASS] {$name}\n";
        $passed++;
    } catch (\Throwable $e) {
        echo " [FAIL] {$name}: " . $e->getMessage() . "\n";
        $failed++;
    }
}

class HttpClient {
    private string $cookieFile;

    public function __construct() {
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'tp_cookie_');
    }

    public function __destruct() {
        if (file_exists($this->cookieFile)) {
            @unlink($this->cookieFile);
        }
    }

    public function get(string $url, array $headers = []): array {
        return $this->request('GET', $url, [], $headers);
    }

    public function post(string $url, array $data = [], array $headers = []): array {
        return $this->request('POST', $url, $data, $headers);
    }

    private function request(string $method, string $url, array $data = [], array $headers = []): array {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException("cURL error: {$err}");
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $headerStr = substr($raw, 0, $headerSize);
        $body      = substr($raw, $headerSize);

        return [
            'code'    => $httpCode,
            'headers' => $headerStr,
            'body'    => $body,
        ];
    }

    public function extractCsrfToken(string $html): ?string {
        if (preg_match('/name="_csrf_token"\s+value="([^"]+)"/', $html, $m)) {
            return $m[1];
        }
        return null;
    }
}

echo "======================================================\n";
echo "TuitionPro ERP — Full HTTP Route Smoke Verification\n";
echo "Server Target: {$baseUrl}\n";
echo "======================================================\n\n";
echo "1. Testing Guest Protection & Redirects...\n";

runTest("GET / without auth redirects to /login (302)", function () use ($baseUrl) {
    $client = new HttpClient();
    $res = $client->get("{$baseUrl}/");
    if ($res['code'] !== 302) {
        throw new \RuntimeException("Expected 302, got {$res['code']}");
    }
    if (!str_contains($res['headers'], 'Location:')) {
        throw new \RuntimeException("Missing Location redirect header");
    }
});

runTest("GET /login returns 200 OK", function () use ($baseUrl) {
    $client = new HttpClient();
    $res = $client->get("{$baseUrl}/login");
    if ($res['code'] !== 200) {
        throw new \RuntimeException("Expected 200, got {$res['code']}");
    }
    if (!str_contains($res['body'], 'Sign In')) {
        throw new \RuntimeException("Login form not rendered in response");
    }
});

runTest("GET /dashboard without auth redirects to /login (302)", function () use ($baseUrl) {
    $client = new HttpClient();
    $res = $client->get("{$baseUrl}/dashboard");
    if ($res['code'] !== 302) {
        throw new \RuntimeException("Expected 302, got {$res['code']}");
    }
    if (!str_contains($res['headers'], 'Location:')) {
        throw new \RuntimeException("Missing Location redirect header");
    }
});

runTest("GET /students without auth redirects to /login (302)", function () use ($baseUrl) {
    $client = new HttpClient();
    $res = $client->get("{$baseUrl}/students");
    if ($res['code'] !== 302) {
        throw new \RuntimeException("Expected 302, got {$res['code']}");
    }
});

runTest("GET /platform/dashboard without auth redirects to /login (302)", function () use ($baseUrl) {
    $client = new HttpClient();
    $res = $client->get("{$baseUrl}/platform/dashboard");
    if ($res['code'] !== 302) {
        throw new \RuntimeException("Expected 302, got {$res['code']}");
    }
});
echo "\n2. Testing Developer / Super Admin Workflows...\n";

$superClient = new HttpClient();

runTest("POST /login with Super Admin credentials authenticates successfully", function () use ($baseUrl, $superClient) {
    $loginPage = $superClient->get("{$baseUrl}/login");
    $csrf = $superClient->extractCsrfToken($loginPage['body']);
    if (!$csrf) throw new \RuntimeException("Failed to extract CSRF token from login page");

    $res = $superClient->post("{$baseUrl}/login", [
        '_csrf_token' => $csrf,
        'login'       => 'superadmin@tuitionpro.lk',
        'password'    => 'Password@123',
    ]);

    if ($res['code'] !== 302) {
        throw new \RuntimeException("Expected 302 redirect, got {$res['code']}");
    }
    if (!str_contains($res['headers'], 'tuitionpro_jwt=')) {
        throw new \RuntimeException("Expected tuitionpro_jwt HttpOnly cookie in response");
    }
});

runTest("GET /platform/dashboard loads System Administration with 200 OK", function () use ($baseUrl, $superClient) {
    $res = $superClient->get("{$baseUrl}/platform/dashboard");
    if ($res['code'] !== 200) {
        throw new \RuntimeException("Expected 200, got {$res['code']}");
    }
    if (!str_contains($res['body'], 'System Administration & Health Overview')) {
        throw new \RuntimeException("System overview title missing from page");
    }
    if (!str_contains($res['body'], 'Runtime & Server Environment')) {
        throw new \RuntimeException("Runtime server environment section missing");
    }
});

runTest("GET /platform/tenants loads Institute Admin Setup with 200 OK", function () use ($baseUrl, $superClient) {
    $res = $superClient->get("{$baseUrl}/platform/tenants");
    if ($res['code'] !== 200) {
        throw new \RuntimeException("Expected 200, got {$res['code']}");
    }
    if (!str_contains($res['body'], 'Institute Admin & Installation Setup')) {
        throw new \RuntimeException("Institute admin setup title missing");
    }
});
echo "\n3. Testing Institute Admin & Core Tuition Modules...\n";

$adminClient = new HttpClient();

runTest("POST /login with Institute Owner credentials succeeds", function () use ($baseUrl, $adminClient) {
    $loginPage = $adminClient->get("{$baseUrl}/login");
    $csrf = $adminClient->extractCsrfToken($loginPage['body']);

    $res = $adminClient->post("{$baseUrl}/login", [
        '_csrf_token' => $csrf,
        'login'       => 'apex.owner@tuitionpro.lk',
        'password'    => 'Password@123',
    ]);

    if ($res['code'] !== 302) {
        throw new \RuntimeException("Expected 302 redirect, got {$res['code']}");
    }
    if (!str_contains($res['headers'], 'tuitionpro_jwt=')) {
        throw new \RuntimeException("Expected tuitionpro_jwt HttpOnly cookie in response");
    }
});

$instituteRoutes = [
    '/dashboard'                      => 'Executive Dashboard',
    '/students/enquiries'             => 'Student Enquiries',
    '/students/enquiries/create'      => 'Register Student Enquiry',
    '/students'                       => 'Student Directory',
    '/students/create'                => 'New Student Admission',
    '/students/1'                     => 'Kasun Chamara Perera',
    '/students/1/id-card'             => 'Student ID Card',
    '/teachers'                       => 'Teacher Directory',
    '/teachers/create'                => 'Register New Teacher',
    '/teachers/1'                     => 'Prof. Sunimal Fernando',
    '/academic/batches'               => 'Classes & Batches',
    '/academic/batches/create'        => 'Create Class Batch',
    '/academic/batches/1'             => 'Combined Mathematics',
    '/academic/subjects'              => 'Academic Subjects',
    '/academic/materials'             => 'Learning Materials',
    '/academic/materials/create'      => 'Upload Learning Material',
    '/academic/assignments'           => 'Assignments',
    '/academic/assignments/create'    => 'Create New Assignment',
    '/timetable'                      => 'Timetable',
    '/timetable/sessions'             => 'Class Sessions',
    '/attendance'                     => 'Attendance',
    '/attendance/qr-scanner'          => 'QR Attendance Scanner',
    '/attendance/history'             => 'Attendance Records & History',
    '/fees'                           => 'Fee Invoices & Billing',
    '/fees/invoices'                  => 'Fee Invoices & Billing',
    '/fees/create'                    => 'Generate Fee Invoice',
    '/fees/invoices/create'           => 'Generate Fee Invoice',
    '/fees/outstanding'               => 'Outstanding',
    '/teacher-payments'               => 'Teacher Payroll & Compensations',
    '/teacher-payments/calculate'     => 'Calculate Teacher Payroll',
    '/expenses'                       => 'Operational Expenses',
    '/expenses/create'                => 'Record Institute Expense',
    '/expenses/trial-balance'         => 'Trial Balance',
    '/accounting/profit-loss'         => 'Profit & Loss Statement',
    '/accounting/balance-sheet'       => 'Balance Sheet',
    '/accounting/cash-bank'           => 'Cash & Bank',
    '/examinations'                   => 'Examinations & Student Performance',
    '/examinations/marks-entry'       => 'Marks',
    '/examinations/results'           => 'Result',
    '/communication/announcements'    => 'Announcements',
    '/reports'                        => 'Analytics & Reports',
    '/reports/attendance'             => 'Attendance',
    '/reports/fee-collection'         => 'Fee Collection',
    '/reports/outstanding'            => 'Outstanding',
    '/reports/teacher-payments'       => 'Teacher Payment',
    '/reports/expenses'               => 'Expense',
    '/reports/student-performance'    => 'Student Performance',
    '/settings'                       => 'Institute Settings',
    '/settings/branches'              => 'Institute Branches',
    '/settings/users'                 => 'Staff & User Accounts',
    '/settings/backups'               => 'Database Backups',
];

foreach ($instituteRoutes as $route => $expectedSubstring) {
    runTest("GET {$route} returns 200 OK and valid view", function () use ($baseUrl, $adminClient, $route, $expectedSubstring) {
        $res = $adminClient->get("{$baseUrl}{$route}");
        if ($res['code'] !== 200) {
            throw new \RuntimeException("Expected HTTP 200, got {$res['code']}");
        }
        if (!str_contains($res['body'], $expectedSubstring)) {
            throw new \RuntimeException("Response does not contain expected substring '{$expectedSubstring}'");
        }
    });
}
echo "\n4. Testing Student & Parent Portals...\n";

$studentClient = new HttpClient();
runTest("Student login and portal access", function () use ($baseUrl, $studentClient) {
    $loginPage = $studentClient->get("{$baseUrl}/login");
    $csrf = $studentClient->extractCsrfToken($loginPage['body']);

    $res = $studentClient->post("{$baseUrl}/login", [
        '_csrf_token' => $csrf,
        'login'       => 'apex.student1@tuitionpro.lk',
        'password'    => 'Password@123',
    ]);
    if ($res['code'] !== 302) throw new \RuntimeException("Student login failed: HTTP {$res['code']}");

    $portal = $studentClient->get("{$baseUrl}/portal/student");
    if ($portal['code'] !== 200) throw new \RuntimeException("Student portal returned {$portal['code']}");
    if (!str_contains($portal['body'], 'Student Portal') && !str_contains($portal['body'], 'Kasun')) {
        throw new \RuntimeException("Student portal view content not found");
    }
});

$parentClient = new HttpClient();
runTest("Parent login and portal access", function () use ($baseUrl, $parentClient) {
    $loginPage = $parentClient->get("{$baseUrl}/login");
    $csrf = $parentClient->extractCsrfToken($loginPage['body']);

    $res = $parentClient->post("{$baseUrl}/login", [
        '_csrf_token' => $csrf,
        'login'       => 'apex.parent1@tuitionpro.lk',
        'password'    => 'Password@123',
    ]);
    if ($res['code'] !== 302) throw new \RuntimeException("Parent login failed: HTTP {$res['code']}");

    $portal = $parentClient->get("{$baseUrl}/portal/parent");
    if ($portal['code'] !== 200) throw new \RuntimeException("Parent portal returned {$portal['code']}");
    if (!str_contains($portal['body'], 'Parent Portal') && !str_contains($portal['body'], 'Children')) {
        throw new \RuntimeException("Parent portal view content not found");
    }
});
echo "\n5. Testing Secure Logout & Session Clearance...\n";

runTest("POST /logout terminates session and clears cookies", function () use ($baseUrl, $adminClient) {
    $dashboard = $adminClient->get("{$baseUrl}/dashboard");
    $csrf = $adminClient->extractCsrfToken($dashboard['body']);

    $res = $adminClient->post("{$baseUrl}/logout", [
        '_csrf_token' => $csrf,
    ]);

    if ($res['code'] !== 302) {
        throw new \RuntimeException("Expected 302 redirect after logout, got {$res['code']}");
    }

    //Authentication
    $res2 = $adminClient->get("{$baseUrl}/dashboard");
    if ($res2['code'] !== 302) {
        throw new \RuntimeException("Expected 302 to login after logout, got {$res2['code']}");
    }
});

echo "\n------------------------------------------------------\n";
echo "HTTP ROUTE TEST RESULTS:\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "------------------------------------------------------\n";

if ($failed > 0) {
    exit(1);
}
