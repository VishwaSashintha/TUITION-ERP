@echo off
setlocal
echo ======================================================
echo TuitionPro ERP - Development Server
echo ======================================================
echo.

where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP was not found in your system PATH.
    echo Please install PHP 8.2+ or add your PHP directory (e.g. C:\xampp\php) to PATH.
    pause
    exit /b 1
)

if not exist ".env" (
    echo [WARNING] No .env file detected. Running setup.bat first...
    call setup.bat
)

echo Starting TuitionPro ERP on http://localhost:8080
echo Press Ctrl+C in this window to stop the server.
echo.
echo Evaluation Credentials:
echo   - Platform Super Admin: superadmin@tuitionpro.lk / Password@123
echo   - Institute Owner:      apex.owner@tuitionpro.lk / Password@123
echo   - Receptionist:         apex.reception@tuitionpro.lk / Password@123
echo   - Teacher:              apex.fernando@tuitionpro.lk / Password@123
echo   - Student:              apex.student1@tuitionpro.lk / Password@123
echo   - Parent:               apex.parent1@tuitionpro.lk / Password@123
echo ======================================================
echo.

php -S localhost:8080 -t public

