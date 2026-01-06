# Backend API Optimization & Security Guide

Panduan lengkap optimisasi performa, caching, dan keamanan untuk deployment production.

## 🚀 Quick Optimization Commands

Jalankan perintah ini setelah setiap deployment atau perubahan `.env`:

```bash
# 1. Optimize Laravel Configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 2. Optimized Autoloader
composer install --optimize-autoloader --no-dev
```

---

## ⚡ Caching Layer Implementation

System menggunakan `CacheService` dengan strategi **Tag-based Caching**.

| Content Type | TTL | Cache Tag | Invalidation Trigger |
|--------------|-----|-----------|----------------------|
| **Products** | 30 mins | `products` | Create/Update/Delete Product |
| **News** | 30 mins | `news` | Create/Update/Delete News |
| **Banners** | 5 mins | `banners` | Create/Update/Delete Banner |
| **Brands** | 30 mins | `brands` | Create/Update/Delete Brand |
| **Certifications** | 30 mins | `certifications` | Create/Update/Delete Certification |
| **CSR** | 30 mins | `csr` | Create/Update/Delete CSR |
| **Details** | 1 hour | `products`, `news`, `csr`| Update/Delete Specific Item |

### Cara Kerja Caching
1. **Read Operations (GET)**: System cek cache dulu. Jika ada -> return cache. Jika tidak -> query DB -> simpan cache -> return data.
2. **Write Operations (POST/PUT/DELETE)**: System update database DAN otomatis menghapus (invalidate) cache tag yang relevan.

### Pindah ke Redis (Recommended for Production)
Database cache driver (default) cukup untuk traffic menengah. Untuk high traffic, ganti ke Redis:

1. Install Redis server
2. Update `.env`:
   ```env
   CACHE_STORE=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis
   REDIS_CLIENT=phpredis
   ```

---

## 🛡️ Security Features (Hardening)

### 1. Security Headers
Middleware `SecurityHeaders` melindungi dari:
- **Clickjacking**: `X-Frame-Options: DENY`
- **MIME Sniffing**: `X-Content-Type-Options: nosniff`
- **XSS**: `Content-Security-Policy` & `X-XSS-Protection`

### 2. Request Firewall
Middleware `BlockSuspiciousRequests` memblokir:
- **SQL Injection**: Patterns seperti `UNION SELECT`, `DROP TABLE`, dll.
- **Path Traversal**: Percobaan akses `../../etc/passwd`
- **Exploit Scanners**: User agents seperti `sqlmap`, `nikto`, `vulnscan`
- **Sensitive Paths**: Akses ke `/wp-admin`, `/phpmyadmin`, `.env`, `.git`

### 3. Anti-Abuse & Rate Limiting
- **Login**: Maksimal 5x gagal per menit (Brute force protection)
- **Contact Form**: Maksimal 5 pesan per menit (Spam protection) + **Honeypot**
- **General API**: 60 requests/menit

### 4. Audit Logging
System mencatat aktivitas penting ke tabel `audit_logs`:
- Login success/failed (catat IP & User Agent)
- Data changes (Create/Update/Delete)
- Logout events

---

## 🔧 Production Checklist

- [ ] **Environment**: Set `APP_ENV=production` dan `APP_DEBUG=false`
- [ ] **SSL**: Pastikan HTTPS aktif (HSTS otomatis aktif di production)
- [ ] **PHP Configuration (php.ini)**:
  ```ini
  opcache.enable=1
  opcache.memory_consumption=256
  opcache.max_accelerated_files=20000
  opcache.validate_timestamps=0
  expose_php=Off
  ```
- [ ] **Testing**:
  - Cek security headers: `curl -I https://api.domain.com/api/v1/products`
  - Cek caching: Hit endpoint 2x, response kedua harus lebih cepat (< 100ms)
