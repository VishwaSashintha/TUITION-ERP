@echo off
setlocal
echo ======================================================
echo TuitionPro ERP - Database Backup Utility
echo ======================================================
echo.

where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;%PATH%"
    ) else (
        echo [ERROR] PHP was not found in your system PATH.
        pause
        exit /b 1
    )
)

if exist "..\backend\storage" (
    cd ..
)

php -r "require_once 'backend/public/index.php'; \$c = new \App\Controllers\Settings\SettingsController(); \$r = new \App\Core\Request(); \$c->createBackup(\$r);"

echo.
echo Backup operation finished. Check backend\storage\backups for SQL archives.
pause
