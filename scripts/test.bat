@echo off
setlocal enabledelayedexpansion

REM ======================================================
REM TuitionPro ERP - Automated Test Suite Runner
REM ======================================================

echo ======================================================
echo TuitionPro ERP - Automated Test Suite Runner
echo ======================================================
echo.

where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;%PATH%"
    ) else (
        echo [ERROR] PHP was not found in your system PATH.
        echo Please install PHP 8.2+ or add your PHP directory e.g. C:\xampp\php to PATH.
        exit /b 1
    )
)

if exist "..\backend\tests" (
    cd ..
)

REM Check if server is running on port 8080 for HTTP test suites
set "STARTED_SERVER=0"
netstat -an 2>nul | findstr /c:":8080" | findstr "LISTEN" >nul 2>nul
if %errorlevel% neq 0 (
    echo [INFO] HTTP server not detected on port 8080. Starting temporary test server...
    start "TuitionPro Test Server" /min cmd /c "php -S localhost:8080 -t backend\public backend\public\index.php"
    set "STARTED_SERVER=1"
    ping 127.0.0.1 -n 3 >nul
)

echo [1/5] Running Backend Core and Multi-Tenant Tests...
php backend\tests\test_backend_core.php
if %errorlevel% neq 0 goto test_fail

echo.
echo [2/5] Running End-to-End Workflow Tests...
php backend\tests\test_e2e_workflows.php
if %errorlevel% neq 0 goto test_fail

echo.
echo [3/5] Running JWT Authentication and Security Tests...
php backend\tests\test_jwt_auth.php
if %errorlevel% neq 0 goto test_fail

echo.
echo [4/5] Running Role Dashboards and RBAC Tests...
php backend\tests\test_role_dashboards.php
if %errorlevel% neq 0 goto test_fail

echo.
echo [5/5] Running HTTP Route Smoke Tests (63 Routes)...
php backend\tests\test_http_routes.php
if %errorlevel% neq 0 goto test_fail

echo.
echo ======================================================
echo ALL 150 AUTOMATED TESTS PASSED SUCCESSFULLY!
echo ======================================================

if "!STARTED_SERVER!"=="1" (
    call scripts\stop.bat >nul 2>&1
)
exit /b 0

:test_fail
echo.
echo ======================================================
echo [FAIL] One or more automated tests encountered an error.
echo ======================================================
if "!STARTED_SERVER!"=="1" (
    call scripts\stop.bat >nul 2>&1
)
exit /b 1
