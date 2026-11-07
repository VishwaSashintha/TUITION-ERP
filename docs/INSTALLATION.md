# TuitionPro ERP — Standalone Installation Guide

This guide details the steps required to deploy TuitionPro ERP in **Standalone Installation Mode** for a single tuition institute, school, or learning academy.

---

## 1. System Prerequisites

- **Web Server:** Apache 2.4+ (with `mod_rewrite` enabled) or Nginx 1.20+
- **PHP Version:** PHP 8.2 or higher
  - Required Extensions: `pdo_mysql`, `mbstring`, `openssl`, `json`, `gd`, `fileinfo`, `curl`
- **Database Server:** MySQL 8.0+ or MariaDB 10.4+
- **Disk Space:** 500 MB minimum (plus storage for student files/backups)

---

## 2. Step-by-Step Installation

### Step 2.1: Clone or Extract the Application
Clone the repository or extract the release archive into your web server document root:
```bash
git clone https://github.com/VishwaSashintha/TUITION-ERP.git /var/www/tuitionpro
cd /var/www/tuitionpro
```

### Step 2.2: Configure Environment Variables
Copy the example configuration file:
```bash
cp .env.example .env
```
Edit `.env` to configure your database and institute identity:
```ini
APP_NAME="TuitionPro ERP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-institute-domain.lk
APP_SECRET_KEY=generate-a-secure-random-32-char-string-here

# Single-Institute Profile
INSTITUTE_NAME="Apex Higher Education Institute"
INSTITUTE_CODE=apex
INSTITUTE_EMAIL=info@apexeducation.lk
INSTITUTE_PHONE="+94 11 234 5678"
INSTITUTE_ADDRESS="No. 123, Galle Road, Colombo 03, Sri Lanka"
INSTITUTE_TIMEZONE=Asia/Colombo
INSTITUTE_CURRENCY=LKR
INSTITUTE_CURRENCY_SYMBOL="Rs. "

# Database Connection
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tuitionpro
DB_USERNAME=tuitionpro_user
DB_PASSWORD=YourStrongDatabasePasswordHere

# JWT Security
JWT_SECRET=generate-a-secure-random-64-char-signing-key-here
JWT_ACCESS_TTL=900
JWT_REFRESH_TTL=604800
```

### Step 2.3: Initialize the Database
Create the MySQL database:
```sql
CREATE DATABASE `tuitionpro` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Import the migrations in sequential order:
```bash
mysql -u tuitionpro_user -p tuitionpro < database/migrations/001_initial_schema.sql
mysql -u tuitionpro_user -p tuitionpro < database/migrations/002_jwt_auth_schema.sql
mysql -u tuitionpro_user -p tuitionpro < database/migrations/003_seed_role_permissions.sql
mysql -u tuitionpro_user -p tuitionpro < database/migrations/004_seed_additional_roles.sql
```

### Step 2.4: Set Directory Permissions
Ensure the web server can write to the `storage/` directory:
```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

### Step 2.5: Configure Web Server Document Root
Set your web server's document root strictly to the `public/` directory:
- **Apache VirtualHost:**
```apache
<VirtualHost *:80>
    ServerName your-institute-domain.lk
    DocumentRoot /var/www/tuitionpro/public

    <Directory /var/www/tuitionpro/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## 3. Default Administrative Access

Upon database initialization, the following administrative account is active:
- **URL:** `https://your-institute-domain.lk/login`
- **Institute Code:** `apex`
- **Email:** `owner@apex.lk`
- **Password:** `Password@123`

> [!IMPORTANT]
> Immediately log in and navigate to **Settings -> Users** to change default passwords and update institute contact details before processing live student data.
