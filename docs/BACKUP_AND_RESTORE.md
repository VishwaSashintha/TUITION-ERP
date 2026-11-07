# TuitionPro ERP — Backup & Disaster Recovery Guide

**Purpose:** Procedures for automated data backups, integrity verification, and recovery in standalone and multi-tenant environments.

---

## 1. Built-in Backup Utility

TuitionPro ERP provides an administrative backup interface directly within the web application:
- **Route:** `GET /settings/backups`
- **Permissions Required:** `settings.manage` (`institute_owner`, `institute_admin`)
- **Capabilities:**
  1. Trigger immediate snapshot SQL backup (`POST /settings/backups/create`).
  2. Download encrypted/compressed SQL archive (`GET /settings/backups/download?file=...`).
  3. Upload and restore database from a verified snapshot (`POST /settings/backups/restore`).

Backups are saved to `storage/backups/` with a timestamped format:
`tuitionpro_backup_{tenant_code}_{Y-m-d_H-i-s}.sql`

---

## 2. Automated Scheduled Backups via Cron

For production stability, configure automated database backups at the operating system level.

### 2.1 Daily Automated Cron Job (Linux)
Add to `/etc/cron.d/tuitionpro-backup`:
```bash
# Execute daily full backup at 02:00 AM
0 2 * * * root mysqldump --defaults-extra-file=/etc/mysql/backup.cnf --single-transaction --quick --routines --triggers tuitionpro | gzip > /var/backups/tuitionpro/db_$(date +\%Y\%m\%d_\%H\%M\%S).sql.gz

# Prune backups older than 30 days
0 3 * * * root find /var/backups/tuitionpro -type f -name "*.sql.gz" -mtime +30 -delete
```

### 2.2 Dedicated MySQL Backup Authentication
Create `/etc/mysql/backup.cnf`:
```ini
[client]
user=backup_user
password=StrongRandomBackupPassword
```
Permissions: `chmod 600 /etc/mysql/backup.cnf`.

---

## 3. Disaster Recovery & Restoration Procedure

In the event of hardware failure, catastrophic corruption, or migration to a new host:

### Step 1: Provision Clean Database
```bash
mysql -u root -p -e "DROP DATABASE IF EXISTS tuitionpro; CREATE DATABASE tuitionpro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 2: Restore from Latest SQL Dump
```bash
gunzip < /var/backups/tuitionpro/db_latest.sql.gz | mysql -u root -p tuitionpro
```

### Step 3: Run Post-Restore Verification
Execute the automated test suite to ensure referential integrity and user credentials are valid:
```bash
php tests/test_backend_core.php
php tests/test_role_dashboards.php
```

---

## 4. Multi-Tenant Export / Tenant Extraction

To export a single tenant's data from a shared platform deployment:
```sql
-- Extract specific tenant student records
SELECT * INTO OUTFILE '/tmp/tenant_apex_students.csv'
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\n'
FROM `students` WHERE `tenant_id` = 1;
```
Core services also support tenant isolation export scripts in `storage/`.
