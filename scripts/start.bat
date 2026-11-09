@echo off
setlocal
echo ======================================================
echo TuitionPro ERP - Development Server
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

if exist "..\backend\public\index.php" (
    cd ..
)

if not exist ".env" (
    if not exist "backend\.env" (
        echo [WARNING] No .env file detected. Running setup first...
        call scripts\setup.bat
    )
)

echo Starting TuitionPro ERP on http://localhost:8080
echo Document Root:   backend\public
echo Frontend Views:  frontend\views
echo Frontend Assets: frontend\assets
echo.
echo Evaluation Credentials:
echo   - Platform Super Admin: superadmin@tuitionpro.lk / Password@123
echo   - Institute Owner:      apex.owner@tuitionpro.lk / Password@123
echo   - Receptionist:         apex.reception@tuitionpro.lk / Password@123
echo   - Teacher:              apex.fernando@tuitionpro.lk / Password@123
echo   - Student:              apex.student1@tuitionpro.lk / Password@123
echo   - Parent:               apex.parent1@tuitionpro.lk / Password@123
echo ======================================================
echo Press Ctrl+C in this window to stop the server.
echo.

php -S localhost:8080 -t backend\public backend\public\index.php
