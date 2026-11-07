@echo off
setlocal
echo ======================================================
echo TuitionPro ERP - Automated Local Setup Utility
echo ======================================================
echo.

REM Check PHP
where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP was not found in your system PATH.
    echo Please install PHP 8.2+ or add your PHP directory (e.g. C:\xampp\php) to PATH.
    pause
    exit /b 1
)

REM Environment file
if not exist ".env" (
    echo Copying .env.example to .env...
    copy .env.example .env >nul
    echo [OK] .env configuration file created.
    echo Please review your database settings in .env if not using default XAMPP credentials.
    echo.
) else (
    echo [OK] .env configuration file found.
)

REM Database migration and seeding
echo.
echo Initializing database schema and evaluation accounts...
php database\migrate.php --seed
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Database setup encountered an issue.
    echo Please ensure MySQL/MariaDB service is running and credentials in .env are correct.
    pause
    exit /b 1
)

echo.
echo ======================================================
echo Setup completed successfully!
echo Run start.bat or execute: php -S localhost:8080 -t public
echo Then open http://localhost:8080 in your browser.
echo ======================================================
echo.
pause

