@echo off
:: ============================================================
:: TuitionPro ERP - Database Migration & Setup Utility
:: ============================================================
:: NOTE: Double-clicking start.bat now automatically handles all
:: first-time setup and service configuration.
::
:: This script is retained as a developer utility to apply pending
:: migrations explicitly without resetting or destroying existing data.
cd /d "%~dp0"

echo ==========================================================
echo  TuitionPro ERP - Setup & Migration Utility
echo ==========================================================
echo.
echo  NOTE: start.bat already handles automatic safe setup.
echo.
echo  This utility will check and apply pending migrations only.
echo  Existing database records will NOT be deleted or reset.
echo.
pause

set "PHP_EXE="
if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
where php >nul 2>nul
if %errorlevel% equ 0 if "%PHP_EXE%"=="" for /f "delims=" %%i in ('where php 2^>nul') do set "PHP_EXE=%%i"

if "%PHP_EXE%"=="" (
    echo [ERROR] PHP was not found. Please install XAMPP or add PHP to PATH.
    pause
    exit /b 1
)

"%PHP_EXE%" database\migrate.php
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Migration encountered an error.
    pause
    exit /b 1
)

echo.
echo [OK] Migration process completed successfully.
echo You can now run start.bat to launch TuitionPro ERP.
echo.
pause
