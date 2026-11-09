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

class TestClient {
    private string $cookieFile;

    public function __construct() {
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'tp_role_');
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

    public function login(string $baseUrl, string $login, string $password): void {
        $loginPage = $this->get("{$baseUrl}/login");
        $csrf = $this->extractCsrfToken($loginPage['body']);
        if (!$csrf) throw new \RuntimeException("Failed to extract CSRF token");

        $res = $this->post("{$baseUrl}/login", [
            '_csrf_token' => $csrf,
            'login'       => $login,
            'password'    => $password,
        ]);

        if ($res['code'] !== 302) {
            throw new \RuntimeException("Login failed for {$login}: HTTP {$res['code']}");
        }
    }
}

echo "======================================================\n";
echo "TuitionPro ERP — Role-Based Dashboard & RBAC Security\n";
echo "Target: {$baseUrl}\n";
echo "======================================================\n\n";
echo "1. Testing Platform Super Admin Dashboard...\n";
$superClient = new TestClient();
$superClient->login($baseUrl, 'superadmin@tuitionpro.lk', 'Password@123');

runTest("Super Admin accesses /platform/dashboard with 200 OK", function () use ($baseUrl, $superClient) {
    $res = $superClient->get("{$baseUrl}/platform/dashboard");
    if ($res['code'] !== 200) throw new \RuntimeException("Expected 200, got {$res['code']}");
    if (!str_contains($res['body'], 'System Administration & Health Overview')) {
        throw new \RuntimeException("Missing System Administration title");
    }
    if (!str_contains($res['body'], 'Total Institutes')) {
        throw new \RuntimeException("Missing Total Institutes stat");
    }
    if (!str_contains($res['body'], 'Platform Students')) {
        throw new \RuntimeException("Missing Platform Students stat");
    }
});

runTest("Super Admin accessing /dashboard redirects to /platform/dashboard", function () use ($baseUrl, $superClient) {
    $res = $superClient->get("{$baseUrl}/dashboard");
    if ($res['code'] !== 302 || !str_contains($res['headers'], 'platform/dashboard')) {
        throw new \RuntimeException("Expected 302 redirect to platform/dashboard, got {$res['code']}");
    }
});
echo "\n2. Testing Institute Owner / Executive Dashboard...\n";
$ownerClient = new TestClient();
$ownerClient->login($baseUrl, 'apex.owner@tuitionpro.lk', 'Password@123');

runTest("Institute Owner receives Executive Dashboard with full financials", function () use ($baseUrl, $ownerClient) {
    $res = $ownerClient->get("{$baseUrl}/dashboard");
    if ($res['code'] !== 200) throw new \RuntimeException("Expected 200, got {$res['code']}");
    if (!str_contains($res['body'], 'Executive Dashboard')) {
        throw new \RuntimeException("Executive Dashboard title not found");
    }
    if (!str_contains($res['body'], 'Financial Performance & Cash Flow')) {
        throw new \RuntimeException("Financial Performance section not found");
    }
    if (!str_contains($res['body'], 'Total Collections') || !str_contains($res['body'], 'Outstanding Fees')) {
        throw new \RuntimeException("Financial KPI metrics not found");
    }
    if (!str_contains($res['body'], 'Net Cash Surplus')) {
        throw new \RuntimeException("Net Cash Surplus not found");
    }
});
echo "\n3. Testing Receptionist Dedicated Front-Desk Dashboard...\n";
$receptionClient = new TestClient();
$receptionClient->login($baseUrl, 'apex.reception@tuitionpro.lk', 'Password@123');

runTest("Receptionist receives Front-Desk Reception Dashboard", function () use ($baseUrl, $receptionClient) {
    $res = $receptionClient->get("{$baseUrl}/dashboard");
    if ($res['code'] !== 200) throw new \RuntimeException("Expected 200, got {$res['code']}");
    if (!str_contains($res['body'], 'Front-Desk Reception Dashboard')) {
        throw new \RuntimeException("Front-Desk Reception Dashboard title not found");
    }
    if (!str_contains($res['body'], 'Today\'s Admissions') || !str_contains($res['body'], 'Present Today')) {
        throw new \RuntimeException("Front-Desk KPI cards not found");
    }
    if (!str_contains($res['body'], 'Register Student') || !str_contains($res['body'], 'QR Fast Scanner')) {
        throw new \RuntimeException("Front-Desk quick actions not found");
    }
});

runTest("Receptionist dashboard strictly conceals private administrative financials", function () use ($baseUrl, $receptionClient) {
    $res = $receptionClient->get("{$baseUrl}/dashboard");
    $body = $res['body'];

    if (str_contains($body, 'Financial Performance & Cash Flow')) {
        throw new \RuntimeException("Data Leak: Receptionist dashboard exposed Financial Performance chart!");
    }
    if (str_contains($body, 'Net Cash Surplus')) {
        throw new \RuntimeException("Data Leak: Receptionist dashboard exposed Net Cash Surplus!");
    }
    if (str_contains($body, 'Bank Balances: Rs.')) {
        throw new \RuntimeException("Data Leak: Receptionist dashboard exposed Bank Balances!");
    }
    if (str_contains($body, 'Teacher Payroll') || str_contains($body, 'Teacher Payments')) {
        throw new \RuntimeException("Data Leak: Receptionist dashboard exposed Teacher Payroll!");
    }
    if (str_contains($body, 'Audited System Ledgers')) {
        throw new \RuntimeException("Data Leak: Receptionist dashboard exposed Audited System Ledgers!");
    }
});
echo "\n4. Testing Receptionist Server-Side Route Restrictions (403 Forbidden)...\n";

$receptionRestricted = [
    '/accounting/profit-loss' => 'Accounting Profit & Loss',
    '/accounting/trial-balance' => 'Trial Balance Ledger',
    '/accounting/cash-bank' => 'Cash & Bank Balances',
    '/teacher-payments' => 'Teacher Payroll',
    '/expenses' => 'Institute Operating Expenses',
    '/settings' => 'Institute Settings',
    '/reports' => 'Institute Analytics & Reports',
];

foreach ($receptionRestricted as $path => $label) {
    runTest("Receptionist direct access to {$path} strictly rejected with HTTP 403", function () use ($baseUrl, $receptionClient, $path, $label) {
        $res = $receptionClient->get("{$baseUrl}{$path}");
        if ($res['code'] !== 403) {
            throw new \RuntimeException("Security Breach: {$label} ({$path}) allowed access! Expected 403, got {$res['code']}");
        }
    });
}
echo "\n5. Testing Teacher Dedicated Academic Dashboard...\n";
$teacherClient = new TestClient();
$teacherClient->login($baseUrl, 'apex.fernando@tuitionpro.lk', 'Password@123');

runTest("Teacher receives Teacher Academic Dashboard scoped to assigned batches", function () use ($baseUrl, $teacherClient) {
    $res = $teacherClient->get("{$baseUrl}/dashboard");
    if ($res['code'] !== 200) throw new \RuntimeException("Expected 200, got {$res['code']}");
    if (!str_contains($res['body'], 'Teacher Academic Dashboard')) {
        throw new \RuntimeException("Teacher Academic Dashboard title not found");
    }
    if (!str_contains($res['body'], 'Assigned Batches') || !str_contains($res['body'], 'My Students')) {
        throw new \RuntimeException("Teacher KPI cards not found");
    }
    if (!str_contains($res['body'], 'My Teaching Schedule Today')) {
        throw new \RuntimeException("Teaching schedule section not found");
    }
});

runTest("Teacher dashboard strictly conceals institute-wide financial data", function () use ($baseUrl, $teacherClient) {
    $res = $teacherClient->get("{$baseUrl}/dashboard");
    $body = $res['body'];

    if (str_contains($body, 'Financial Performance & Cash Flow')) {
        throw new \RuntimeException("Data Leak: Teacher dashboard exposed Financial Performance chart!");
    }
    if (str_contains($body, 'Total Collections') || str_contains($body, 'Outstanding Fees')) {
        throw new \RuntimeException("Data Leak: Teacher dashboard exposed Total Collections or Outstanding Fees!");
    }
    if (str_contains($body, 'Net Cash Surplus')) {
        throw new \RuntimeException("Data Leak: Teacher dashboard exposed Net Cash Surplus!");
    }
});
echo "\n6. Testing Teacher Server-Side Route Restrictions (403 Forbidden)...\n";

$teacherRestricted = [
    '/fees' => 'Fee Management',
    '/accounting/profit-loss' => 'Profit & Loss',
    '/accounting/cash-bank' => 'Cash & Bank',
    '/teacher-payments' => 'Teacher Payroll Module',
    '/expenses' => 'Operating Expenses',
    '/settings' => 'Institute Settings',
];

foreach ($teacherRestricted as $path => $label) {
    runTest("Teacher direct access to {$path} strictly rejected with HTTP 403", function () use ($baseUrl, $teacherClient, $path, $label) {
        $res = $teacherClient->get("{$baseUrl}{$path}");
        if ($res['code'] !== 403) {
            throw new \RuntimeException("Security Breach: {$label} ({$path}) allowed access! Expected 403, got {$res['code']}");
        }
    });
}
echo "\n7. Testing Student Portal Dashboard...\n";
$studentClient = new TestClient();
$studentClient->login($baseUrl, 'apex.student1@tuitionpro.lk', 'Password@123');

runTest("Student receives personal academic portal with enrolled batches", function () use ($baseUrl, $studentClient) {
    $res = $studentClient->get("{$baseUrl}/portal/student");
    if ($res['code'] !== 200) throw new \RuntimeException("Expected 200, got {$res['code']}");
    if (!str_contains($res['body'], 'Student Portal') && !str_contains($res['body'], 'Kasun')) {
        throw new \RuntimeException("Student portal title or student name missing");
    }
    if (!str_contains($res['body'], 'Enrolled Batches') || !str_contains($res['body'], 'Attendance Rate')) {
        throw new \RuntimeException("Student stats missing");
    }
});

runTest("Student accessing /dashboard redirects to /portal/student", function () use ($baseUrl, $studentClient) {
    $res = $studentClient->get("{$baseUrl}/dashboard");
    if ($res['code'] !== 302 || !str_contains($res['headers'], 'portal/student')) {
        throw new \RuntimeException("Expected 302 redirect to portal/student, got {$res['code']}");
    }
});
echo "\n8. Testing Parent Portal Dashboard & Anti-IDOR Data Isolation...\n";
$parentClient = new TestClient();
$parentClient->login($baseUrl, 'apex.parent1@tuitionpro.lk', 'Password@123');

runTest("Parent receives Parent Portal with linked children only", function () use ($baseUrl, $parentClient) {
    $res = $parentClient->get("{$baseUrl}/portal/parent");
    if ($res['code'] !== 200) throw new \RuntimeException("Expected 200, got {$res['code']}");
    if (!str_contains($res['body'], 'Parent & Guardian Portal')) {
        throw new \RuntimeException("Parent portal title missing");
    }
    if (!str_contains($res['body'], 'Linked Children') || !str_contains($res['body'], 'Attendance Rate')) {
        throw new \RuntimeException("Parent stats missing");
    }
});

runTest("Parent accessing /dashboard redirects to /portal/parent", function () use ($baseUrl, $parentClient) {
    $res = $parentClient->get("{$baseUrl}/dashboard");
    if ($res['code'] !== 302 || !str_contains($res['headers'], 'portal/parent')) {
        throw new \RuntimeException("Expected 302 redirect to portal/parent, got {$res['code']}");
    }
});

runTest("Anti-IDOR Defense: Parent cannot manipulate ?child_id to access unrelated student", function () use ($baseUrl, $parentClient) {
    //Students
    $res = $parentClient->get("{$baseUrl}/portal/parent?child_id=15");
    if ($res['code'] !== 200) throw new \RuntimeException("Expected 200, got {$res['code']}");
    if (str_contains($res['body'], 'Sahan Thisara Wickramasinghe')) {
        throw new \RuntimeException("IDOR Vulnerability: Parent accessed unrelated student profile!");
    }
    if (!str_contains($res['body'], 'Kasun Chamara Perera')) {
        throw new \RuntimeException("Expected active child to remain Kasun Chamara Perera");
    }
});
echo "\n9. Testing Design System, 6-Color Palette & Typography...\n";

runTest("Layout loads Google Fonts Roboto and Poppins", function () use ($baseUrl, $ownerClient) {
    $res = $ownerClient->get("{$baseUrl}/dashboard");
    if (!str_contains($res['body'], 'family=Poppins') || !str_contains($res['body'], 'family=Roboto')) {
        throw new \RuntimeException("Google Fonts link for Roboto and Poppins missing");
    }
});

runTest("CSS stylesheet defines the exact 6-color palette variables", function () use ($baseUrl, $ownerClient) {
    $res = $ownerClient->get("{$baseUrl}/assets/css/style.css");
    if ($res['code'] !== 200) throw new \RuntimeException("Failed to load CSS stylesheet");
    $css = $res['body'];

    $expectedColors = [
        '--tp-black: #0C0C0C',
        '--tp-green: #4BA95F',
        '--tp-sky-blue: #77DDFA',
        '--tp-orange: #FEA339',
        '--tp-purple: #9985FB',
        '--tp-coral: #FF908D',
    ];

    foreach ($expectedColors as $colorDef) {
        if (!str_contains($css, $colorDef)) {
            throw new \RuntimeException("Missing color definition: {$colorDef}");
        }
    }

    if (!str_contains($css, "'Roboto'") || !str_contains($css, "'Poppins'")) {
        throw new \RuntimeException("Roboto or Poppins font rule missing in CSS");
    }
});

echo "\n------------------------------------------------------\n";
echo "ROLE-BASED DASHBOARD TEST RESULTS:\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "------------------------------------------------------\n";

if ($failed > 0) {
    exit(1);
}

