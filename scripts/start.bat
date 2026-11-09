@echo off
setlocal

REM ============================================================
REM TuitionPro ERP - One-Click Launcher (scripts\ version)
REM ============================================================

title TuitionPro ERP Launcher

REM Ensure working directory is always the project root
cd /d "%~dp0.."

REM Locate PHP
set "PHP_EXE="

REM 1. Check XAMPP standard path
if exist "C:\xampp\php\php.exe" (
    set "PHP_EXE=C:\xampp\php\php.exe"
    goto :php_ready
)

REM 2. Check system PATH
where php >nul 2>nul
if %errorlevel% equ 0 (
    for /f "delims=" %%i in ('where php 2^>nul') do (
        set "PHP_EXE=%%i"
        goto :php_ready
    )
)

REM 3. Check common secondary locations
if exist "D:\xampp\php\php.exe"       set "PHP_EXE=D:\xampp\php\php.exe" & goto :php_ready
if exist "C:\xampp8\php\php.exe"      set "PHP_EXE=C:\xampp8\php\php.exe" & goto :php_ready
if exist "C:\laragon\bin\php\php.exe" set "PHP_EXE=C:\laragon\bin\php\php.exe" & goto :php_ready

REM PHP not found
echo.
echo ==========================================================
echo  [ERROR] PHP was not found on this computer.
echo ==========================================================
echo.
echo  TuitionPro ERP requires PHP 8.2 or higher.
echo.
echo  To fix this:
echo   1. Install XAMPP with PHP 8.2+ from:
echo      https://www.apachefriends.org/
echo.
echo   2. Or add your PHP installation directory (e.g. C:\xampp\php)
echo      to your Windows PATH environment variable.
echo.
echo ==========================================================
echo.
pause
exit /b 1

:php_ready

REM Run the smart PHP launcher engine
"%PHP_EXE%" "%~dp0launcher.php" %*
if %errorlevel% neq 0 (
    echo.
    pause
    exit /b %errorlevel%
)
exit /b 0
