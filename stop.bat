@echo off
REM ============================================================
REM TuitionPro ERP - Stop Server
REM Double-click this file to stop the local development server.
REM ============================================================
cd /d "%~dp0"
call scripts\stop.bat %*
