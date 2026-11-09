# TuitionPro ERP — Automation & Utility Scripts

This directory contains automated batch scripts for operating, configuring, testing, and maintaining TuitionPro ERP in a local Windows development environment.

## Available Scripts

| Script | Purpose | Usage |
|---|---|---|
| `start.bat` | One-click launcher: pre-flight checks, auto-starts MySQL, verifies schema, runs server, opens browser | Double-click root `start.bat` or `scripts\start.bat` |
| `stop.bat` | Gracefully stops the TuitionPro ERP server running on port 8080 | Double-click root `stop.bat` or `scripts\stop.bat` |
| `setup.bat` | Developer utility to apply pending database migrations safely without data loss | `scripts\setup.bat` |
| `test.bat` | Runs all 150 automated verification tests across 5 test suites | `scripts\test.bat` |
| `backup.bat` | Generates a complete SQL database archive in `backend\storage\backups\` | Double-click or `scripts\backup.bat` |
| `launcher.php` | Smart cross-platform launcher engine invoked by `start.bat` | Invoked automatically |
| `open_browser.php` | Background HTTP health checker that opens browser once server is responding | Invoked automatically |

## Notes

- Users only need to double-click `start.bat` in the project root. First-time setup is completely automated and non-destructive.
- All scripts automatically detect PHP if installed in `C:\xampp\php` or available on PATH.
- Startup technical logs are written to `backend\storage\logs\startup.log`.

