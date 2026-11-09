# TuitionPro ERP — Automation & Utility Scripts

This directory contains automated batch scripts for operating, configuring, testing, and maintaining TuitionPro ERP in a local Windows development environment.

## Available Scripts

| Script | Purpose | Usage |
|---|---|---|
| `start.bat` | Starts the PHP development server on `http://localhost:8080` | Double-click or `scripts\start.bat` |
| `setup.bat` | Creates `.env`, verifies PHP, runs migrations, and seeds demo data | Double-click or `scripts\setup.bat` |
| `test.bat` | Runs all 150 automated verification tests across 5 test suites | `scripts\test.bat` |
| `backup.bat` | Generates a complete SQL database archive in `backend\storage\backups\` | Double-click or `scripts\backup.bat` |

## Notes

- All scripts automatically detect PHP if installed in `C:\xampp\php` without requiring manual environment variable changes.
- Root shortcuts (`start.bat` and `setup.bat`) are provided in the repository root for one-click developer convenience.

