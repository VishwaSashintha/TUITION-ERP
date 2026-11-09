@echo off
setlocal
echo ======================================================
echo TuitionPro ERP - Automated Setup Utility
echo ======================================================
echo.

where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;%PATH%"
    ) else (
        echo [ERROR] PHP was not found in your system PATH.
        echo Please install PHP 8.2+ or add your PHP directory e.g. C:\xampp\php to PATH.
        pause
        exit /b 1
    )
)

if exist "..\database\migrate.php" (
    cd ..
)

if not exist ".env" (
    echo Copying .env.example to .env...
    copy .env.example .env >nul
    echo [OK] .env configuration file created.
    echo Please review your database settings in .env if not using default XAMPP credentials.
    echo.
) else (
    echo [OK] .env configuration file found.
)

echo.
echo Initializing database schema and evaluation accounts...
php database\migrate.php --seed
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Database setup encountered an issue.
    echo Please ensure MySQL or MariaDB service is running and credentials in .env are correct.
    pause
    exit /b 1
)

echo.
echo ======================================================
echo Setup completed successfully!
echo Run start.bat or scripts\start.bat to start the server.
echo Then open http://localhost:8080 in your browser.
echo ======================================================
echo.
pause
