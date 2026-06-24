# ChamaPro Production Deployment Guide

## Server Requirements

- Ubuntu 22.04+ (or similar Linux)
- PHP 8.4+ with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`
- MySQL 8.0+
- Redis (recommended for queues/cache)
- Nginx or Apache
- Node.js 20+ (build assets only)
- Composer 2.x
- Supervisor (queue workers)

## Environment Variables

```env
APP_NAME=ChamaPro
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chamapro
DB_USERNAME=chamapro
DB_PASSWORD=secure-password

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=noreply@your-domain.com

# M-Pesa (Safaricom Daraja)
MPESA_CONSUMER_KEY=
MPESA_CONSUMER_SECRET=
MPESA_SHORTCODE=
MPESA_PASSKEY=
MPESA_CALLBACK_URL=https://your-domain.com/api/mpesa/callback

# SMS Providers (configure per provider in admin panel)
```

## Deployment Steps

1. Clone repository and install dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```

2. Configure environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Run migrations and seed production data:
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=SuperAdminSeeder --force
   php artisan db:seed --class=SubscriptionPlanSeeder --force
   php artisan db:seed --class=RolesAndPermissionsSeeder --force
   ```

4. Optimize Laravel:
   ```bash
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

5. Set permissions:
   ```bash
   chown -R www-data:www-data storage bootstrap/cache
   chmod -R 775 storage bootstrap/cache
   ```

## Queue Worker (Supervisor)

```ini
[program:chamapro-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/chamapro/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/chamapro/storage/logs/worker.log
stopwaitsecs=3600
```

## Scheduler (Cron)

```cron
* * * * * cd /var/www/chamapro && php artisan schedule:run >> /dev/null 2>&1
```

## Nginx Configuration (snippet)

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/chamapro/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## SSL & M-Pesa Webhooks

- Use Let's Encrypt for SSL certificates
- Register callback URL: `https://your-domain.com/api/mpesa/callback`
- Ensure webhook endpoint is publicly accessible (no auth middleware)

## Backups

- Daily MySQL dumps via cron:
  ```bash
  mysqldump -u chamapro -p chamapro > /backups/chamapro-$(date +%F).sql
  ```
- Retain audit logs (`activity_log` table) — do not truncate in production
- Store backups off-site (S3, etc.)

## Laravel Cloud Alternative

1. Connect GitHub repository
2. Set environment variables in dashboard
3. Enable managed MySQL and Redis
4. Deploy — migrations run automatically on deploy hook

## Default Credentials (change immediately)

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@chamapro.com | password |
| Demo Chairperson | chair@demo.com | password |

## Health Check

- Application: `GET /up`
- Queue: monitor Supervisor logs
- Subscriptions: `php artisan subscriptions:check-expiry` (runs daily via scheduler)
