# TuitionPro ERP — Multi-Institute Commercial Platform Deployment Guide

This guide details the deployment of TuitionPro ERP as a **Multi-Institute Commercial Platform** (Mode B), serving multiple education organizations, tuition networks, and branches from a unified cluster.

---

## 1. Production Architecture Topology

```
                              [ Cloudflare / CDN / WAF ]
                                         |
                                         v
                            [ Nginx Reverse Proxy / SSL ]
                             (Certbot Let's Encrypt TLS)
                                         |
                 +-----------------------+-----------------------+
                 |                                               |
                 v                                               v
      [ PHP 8.2-FPM Worker Pool 1 ]                   [ PHP 8.2-FPM Worker Pool 2 ]
                 |                                               |
                 +-----------------------+-----------------------+
                                         |
                                         v
                        [ MySQL 8.0 Primary / MariaDB Cluster ]
                               (InnoDB Replication)
```

---

## 2. Nginx Production Configuration

Create `/etc/nginx/sites-available/tuitionpro.conf`:
```nginx
server {
    listen 80;
    server_name tuitionpro.lk *.tuitionpro.lk;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name tuitionpro.lk *.tuitionpro.lk;

    ssl_certificate /etc/letsencrypt/live/tuitionpro.lk/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/tuitionpro.lk/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    root /var/www/tuitionpro/public;
    index index.php index.html;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files and internal scripts
    location ~ /\. {
        deny all;
    }
    location ~ /(config|database|resources|storage|tests)/ {
        deny all;
    }
}
```

---

## 3. Multi-Tenant Platform Administration

1. **Global Admin Portal:**
   - URL: `https://tuitionpro.lk/platform/dashboard`
   - Access Role: `super_admin`
   - Default Super Admin: `superadmin@tuitionpro.lk` / `SuperAdmin@2026`
2. **Onboarding a New Institute:**
   - Navigate to `/platform/tenants`.
   - Click **Add New Institute**.
   - Specify:
     - Institute Name (e.g. "Wisdom Academy")
     - Tenant Code (e.g. `wisdom`)
     - Subscription Plan (Starter, Professional, Enterprise)
     - Admin Name and Initial Password
   - The platform engine initializes tenant records, default role permissions, and creates the primary owner user transactionally.

---

## 4. Performance Tuning & Production Checklist

1. **PHP OPcache:** Ensure OPcache is enabled in `php.ini`:
   ```ini
   opcache.enable=1
   opcache.memory_consumption=256
   opcache.max_accelerated_files=20000
   opcache.validate_timestamps=0
   ```
2. **MySQL Connection Pool:** Configure `max_connections = 250` and `innodb_buffer_pool_size` to 70% of available server RAM.
3. **Environment Isolation:** Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`.
4. **Log Rotation:** Ensure `storage/logs/app.log` is rotated weekly via `logrotate`.

