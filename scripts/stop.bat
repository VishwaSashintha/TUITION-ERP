@echo off
setlocal enabledelayedexpansion

REM ============================================================
REM TuitionPro ERP - Server Stopper
REM Gracefully stops the development server on port 8080.
REM ============================================================

title TuitionPro ERP - Stop Server

echo ==========================================================
echo  TuitionPro ERP - Stopping Server
echo ==========================================================
echo.

set "STOPPED=0"

for /f "tokens=5" %%a in ('netstat -aon 2^>nul ^| findstr /r /c:":8080 .*LISTENING"') do (
    echo Terminating server process PID %%a on port 8080...
    taskkill /F /PID %%a >nul 2>&1
    if !errorlevel! equ 0 (
        set "STOPPED=1"
        echo [OK] Server process PID %%a terminated.
    )
)

if "!STOPPED!"=="1" (
    echo.
    echo [OK] TuitionPro ERP server has been stopped.
) else (
    echo [INFO] No TuitionPro ERP server was running on port 8080.
)

echo.
exit /b 0
