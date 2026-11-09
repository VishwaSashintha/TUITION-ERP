# TuitionPro ERP — Database & Migrations

TuitionPro ERP uses MySQL 8.0+ (or MariaDB 10.4+) with the InnoDB storage engine and `utf8mb4_unicode_ci` collation across 61 relational tables.

## Directory Structure

```
database/
├── migrations/
│   ├── 001_initial_schema.sql            # Core 61 tables, foreign keys, and indexes
│   ├── 002_jwt_auth_schema.sql           # Refresh token table and JWT claims
│   ├── 003_seed_role_permissions.sql     # Baseline role definitions and RBAC grants
│   ├── 004_seed_additional_roles.sql     # Additional institutional staff roles
│   └── 005_student_enquiries_schema.sql  # Lead tracking and enquiry lifecycle schema
├── seeds/
│   └── 001_seed_demo_data.php            # Comprehensive demo dataset with accounts
├── migrate.php                           # Migration execution and seeding utility
└── README.md                             # Database documentation
```

## Running Migrations

To apply pending migrations:
```cmd
php database\migrate.php
```

To run a fresh migration and seed demo accounts:
```cmd
php database\migrate.php --seed
```

To drop all existing tables and re-run migrations from scratch:
```cmd
php database\migrate.php --fresh --seed
```

## Database Safety & Backups

- Production database archives are stored in `backend/storage/backups/`.
- All `.sql` backup files are excluded from Git version control via `.gitignore`.
- Backups can be triggered through the Settings UI (`/settings/backups`) or via `scripts\backup.bat`.

