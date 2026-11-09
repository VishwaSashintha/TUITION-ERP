<?php

declare(strict_types=1);

require_once __DIR__ . '/../public/index.php';

use App\Core\Database;
use App\Services\JwtService;
use App\Services\RateLimiter;
use App\Services\AuthService;

$db     = Database::getInstance();
$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $description): void
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo " [PASS] {$description}\n";
    } else {
        $failed++;
        echo " [FAIL] {$description}\n";
    }
}

echo "======================================================\n";
echo "TUITIONPRO ERP — JWT AUTHENTICATION & SECURITY SUITE\n";
echo "======================================================\n\n";

$jwt = new JwtService();

//Validation
echo "1. Testing JWT Token Issuance & Signature Verification...\n";
$user = [
    'id'           => 1,
    'name'         => 'Test Director',
    'email'        => 'director@iskole.lk',
    'role_name'    => 'institute_admin',
    'role_display' => 'Institute Administrator',
    'tenant_id'    => 1,
    'branch_id'    => 1,
    'is_platform'  => false,
];

$accessToken = $jwt->generateAccessToken($user);
assertTest(!empty($accessToken), "JWT access token successfully issued");

$payload = $jwt->validateToken($accessToken);
assertTest($payload !== null && $payload['sub'] === 1, "JWT access token verified with correct subject claim");
assertTest($payload['role'] === 'institute_admin', "JWT payload carries correct user role");

//Authentication
echo "\n2. Testing Tampered Token Rejection...\n";
$parts = explode('.', $accessToken);
$tamperedPayload = base64_encode(json_encode(['sub' => 999, 'role' => 'super_admin']));
$tamperedToken   = "{$parts[0]}.{$tamperedPayload}.{$parts[2]}";

$tamperedResult = $jwt->validateToken($tamperedToken);
assertTest($tamperedResult === null, "Tampered JWT with modified payload is strictly rejected");

$tamperedSigToken = "{$parts[0]}.{$parts[1]}.invalidsignature123";
assertTest($jwt->validateToken($tamperedSigToken) === null, "JWT with counterfeit signature is strictly rejected");

echo "\n3. Testing Algorithm Manipulation Defense (alg: none)...\n";
$noneHeader = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'none']));
$noneToken  = "{$noneHeader}.{$parts[1]}.";
assertTest($jwt->validateToken($noneToken) === null, "Unsigned token (alg: none) is strictly rejected");

//Authentication
echo "\n4. Testing Refresh Token Rotation & Database Persistence...\n";
$refreshToken = $jwt->generateRefreshToken($user);
assertTest(!empty($refreshToken), "Refresh token successfully generated");

$tokenHash = hash('sha256', $refreshToken);
$record = $db->queryOne("SELECT * FROM `refresh_tokens` WHERE `token_hash` = :thash", ['thash' => $tokenHash]);
assertTest($record !== null && (int)$record['user_id'] === 1, "Refresh token persisted in `refresh_tokens` table");

//Authentication
$rotated = $jwt->rotateRefreshToken($refreshToken);
assertTest($rotated !== null && !empty($rotated['access_token']), "Refresh token rotation succeeded, issued new access token");
assertTest(!empty($rotated['refresh_token']), "New refresh token issued during rotation");

//Authentication
$oldRecord = $db->queryOne("SELECT * FROM `refresh_tokens` WHERE `token_hash` = :thash", ['thash' => $tokenHash]);
assertTest($oldRecord['revoked_at'] !== null, "Previous refresh token marked as revoked in database");

//Authentication
echo "\n5. Testing Replay Attack Detection & Defense...\n";
$replayResult = $jwt->rotateRefreshToken($refreshToken);
assertTest($replayResult === null, "Replaying already-revoked refresh token is strictly rejected");

//Authentication
echo "\n6. Testing Explicit Token Revocation (Logout)...\n";
$newRefreshToken = $rotated['refresh_token'];
$jwt->revokeRefreshToken($newRefreshToken);

$revokedRecord = $db->queryOne(
    "SELECT * FROM `refresh_tokens` WHERE `token_hash` = :thash",
    ['thash' => hash('sha256', $newRefreshToken)]
);
assertTest($revokedRecord['revoked_at'] !== null, "Refresh token successfully revoked upon logout");

//Authentication
echo "\n7. Testing Login Rate Limiting (Brute-Force Protection)...\n";
$limiter = new RateLimiter(5, 300);
$testIp  = '192.168.100.99';
$testUid = 'victim@iskole.lk';

$limiter->clear($testIp, $testUid);
assertTest(!$limiter->isRateLimited($testIp, $testUid), "Initial login attempt is not rate-limited");

for ($i = 1; $i <= 5; $i++) {
    $limiter->recordAttempt($testIp, $testUid, false);
}

assertTest($limiter->isRateLimited($testIp, $testUid), "IP/User locked out after 5 consecutive failed login attempts");

$limiter->clear($testIp, $testUid);
assertTest(!$limiter->isRateLimited($testIp, $testUid), "Rate-limit lockout clears on verified reset");

echo "\n------------------------------------------------------\n";
echo "JWT SECURITY TEST RESULTS:\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "------------------------------------------------------\n";

if ($failed > 0) {
    exit(1);
}

