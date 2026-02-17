# راهنمای نصب و استقرار سامانه آزمون آنلاین روی سرور مجازی اوبونتو

## پیش‌نیازها

- سرور مجازی با سیستم‌عامل Ubuntu 20.04 یا 22.04
- دسترسی root یا sudo
- دامنه یا IP سرور
- Git نصب شده روی سرور

## مرحله ۱: نصب پیش‌نیازهای سیستم

از آنجا که PHP 8.2 در مخازن پیش‌فرض نسخه‌های قدیمی‌تر اوبونتو (مثل 20.04 یا 22.04) موجود نیست، باید از مخزن معتبر ondrej/php استفاده کنیم. همچنین ماژول Redis برای عملکرد صحیح سیستم ضروری است.

```bash
# ۱. نصب ابزار مدیریت مخازن و آپدیت سیستم
sudo apt update && sudo apt upgrade -y
sudo apt install software-properties-common -y

# ۲. اضافه کردن مخزن PHP (Ondrej)
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# ۳. نصب وب‌سرور Nginx
sudo apt install nginx -y

# ۴. نصب PHP 8.2 و تمامی افزونه‌های مورد نیاز (به همراه Redis)
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-zip php8.2-mbstring php8.2-bcmath php8.2-gd php8.2-intl php8.2-tokenizer php8.2-dom php8.2-redis -y

# ۵. بررسی صحت نصب PHP
php -v
# خروجی باید نسخه PHP 8.2.x را نشان دهد

# ۶. بررسی وضعیت سرویس PHP-FPM
sudo systemctl status php8.2-fpm
# وضعیت باید active (running) باشد

# ۷. نصب Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# ۸. نصب Node.js و npm
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y

# ۹. نصب MariaDB
sudo apt install mariadb-server -y

# ۱۰. نصب Redis Server (حیاتی برای کش و صف)
sudo apt install redis-server -y

# ۱۱. نصب Git (اگر نصب نیست)
sudo apt install git -y
```

## مرحله ۲: پیکربندی MariaDB

**مهم**: این مرحله حیاتی است - حتماً کاربر دیتابیس را ایجاد کنید.

```bash
# اجرای اسکریپت امنیتی MariaDB
sudo mysql_secure_installation

# ورود به MariaDB
sudo mysql -u root -p
```

در داخل MariaDB، این دستورات را اجرا کنید:

```sql
-- ایجاد دیتابیس
CREATE DATABASE IF NOT EXISTS onlineazmoon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- حذف کاربر اگر قبلاً وجود داشته (برای اطمینان از تنظیم صحیح رمز عبور)
DROP USER IF EXISTS 'azmoon_user'@'localhost';

-- ایجاد کاربر (رمز عبور قوی انتخاب کنید)
CREATE USER 'azmoon_user'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';

-- دادن دسترسی کامل به دیتابیس
GRANT ALL PRIVILEGES ON onlineazmoon.* TO 'azmoon_user'@'localhost';

-- اعمال تغییرات
FLUSH PRIVILEGES;

-- بررسی کاربر ایجاد شده
SELECT User, Host FROM mysql.user WHERE User = 'azmoon_user';

-- بررسی دسترسی‌ها
SHOW GRANTS FOR 'azmoon_user'@'localhost';

EXIT;
```

تست اتصال:

```bash
# تست اتصال با کاربر جدید
mysql -u azmoon_user -p onlineazmoon
# رمز عبور را وارد کنید
# اگر اتصال موفق بود، EXIT بزنید
```

## مرحله ۳: دریافت کد از GitHub

```bash
# ایجاد دایرکتوری پروژه
sudo mkdir -p /var/www/onlineazmoon
sudo chown $USER:$USER /var/www/onlineazmoon

# کلون کردن پروژه
cd /var/www/onlineazmoon
git clone https://github.com/enigma211/onlineazmoon.git .

# یا اگر از شاخه خاصی استفاده می‌کنید
# git clone -b main https://github.com/YOUR_USERNAME/onlineazmoon.git .
```

## مرحله ۴: نصب پکیج‌های PHP و Node.js

```bash
cd /var/www/onlineazmoon

# نصب پکیج‌های Composer
composer install --optimize-autoloader --no-dev

# نصب پکیج‌های Node.js
npm install

# بیلد assets
npm run build
```

## مرحله ۵: پیکربندی فایل .env

```bash
# کپی فایل .env.example
cp .env.example .env

# ویرایش فایل .env
nano .env
```

محتوای فایل `.env` را به شکل زیر تنظیم کنید:

```env
APP_NAME="OnlineAzmoon"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_TIMEZONE=Asia/Tehran
APP_URL=https://azmoonma.ir

APP_LOCALE=fa
APP_FALLBACK_LOCALE=en

# تنظیمات دیتابیس MariaDB
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=onlineazmoon
DB_USERNAME=azmoon_user
DB_PASSWORD=YOUR_STRONG_PASSWORD

# تنظیمات Redis (حیاتی برای عملکرد سیستم)
SESSION_DRIVER=redis
SESSION_LIFETIME=120

CACHE_STORE=redis
QUEUE_CONNECTION=redis

LOG_CHANNEL=stack
LOG_LEVEL=warning

# تنظیمات ایمیل (اختیاری)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"

# تنظیمات پنل پیامک (ملی پیامک)
MELIPAYAMAK_USERNAME=your-username
MELIPAYAMAK_PASSWORD=your-password
```

## مرحله ۶: راه‌اندازی و بررسی Redis

**مهم**: قبل از اجرای دستورات Laravel، باید مطمئن شوید که Redis در حال اجرا است.

```bash
# بررسی وضعیت Redis
sudo systemctl status redis-server

# اگر Redis غیرفعال است، آن را راه‌اندازی کنید
sudo systemctl start redis

# فعال‌سازی Redis برای استارت خودکار
sudo systemctl enable redis-server

# تست اتصال به Redis
redis-cli ping
# باید پاسخ PONG برگردد
```

## مرحله ۷: اجرای دستورات Laravel

```bash
# تولید کلید برنامه
php artisan key:generate

# پاک کردن کش
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# اجرای مایگریشن‌ها
php artisan migrate --force

# لینک کردن storage
php artisan storage:link

# بهینه‌سازی برنامه
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## مرحله ۸: پیکربندی Nginx

```bash
# ایجاد فایل کانفیگ Nginx
sudo nano /etc/nginx/sites-available/onlineazmoon
```

محتوای زیر را در فایل قرار دهید:

```nginx
server {
    listen 80;
    server_name azmoonma.ir www.azmoonma.ir;
    root /var/www/onlineazmoon/public;
    index index.php index.html index.htm;

    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.ht {
        deny all;
    }

    # تنظیمات کش برای assets استاتیک
    location ~* \.(css|js|ico|gif|jpe?g|png)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # امنیت
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    # مهم: برای Livewire/Alpine باید unsafe-eval در script-src مجاز باشد
    # اگر CSP را در خود Laravel (Middleware) تنظیم کرده‌اید، این هدر را در Nginx حذف کنید تا تداخل ایجاد نشود.
    add_header Content-Security-Policy "default-src 'self' https: http: data: blob:; script-src 'self' 'unsafe-inline' 'unsafe-eval' https: http:; style-src 'self' 'unsafe-inline' https: http:; img-src 'self' data: https: http:; font-src 'self' data: https: http:; connect-src 'self' https: http: ws: wss:;" always;
}
```

نکته مهم برای CDN ابرآروان:

- اگر در پنل CDN هدر `Content-Security-Policy` جداگانه ست کرده‌اید، یا آن را حذف کنید یا دقیقاً با سیاست بالا یکسان کنید.
- ست شدن همزمان چند CSP متفاوت (مثلاً یکی از Nginx و یکی از CDN) باعث می‌شود مرورگر سخت‌گیرانه‌ترین حالت را اعمال کند و Livewire روی «در حال پردازش» بماند.

فعال‌سازی سایت و تست کانفیگ:

```bash
# فعال‌سازی سایت
sudo ln -s /etc/nginx/sites-available/onlineazmoon /etc/nginx/sites-enabled/

# تست کانفیگ Nginx
sudo nginx -t

# ری‌استارت Nginx
sudo systemctl restart nginx
```

## مرحله ۹: تنظیمات امنیتی و دسترسی‌ها

```bash
# تنظیم دسترسی‌های فایل‌ها
sudo chown -R www-data:www-data /var/www/onlineazmoon
sudo find /var/www/onlineazmoon -type f -exec chmod 644 {} \;
sudo find /var/www/onlineazmoon -type d -exec chmod 755 {} \;
sudo chmod -R 755 /var/www/onlineazmoon/storage
sudo chmod -R 755 /var/www/onlineazmoon/bootstrap/cache

# فعال‌سازی فایروال
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

## مرحله ۱۰: نصب SSL (Let's Encrypt)

```bash
# نصب Certbot
sudo apt install certbot python3-certbot-nginx -y

# دریافت SSL
sudo certbot --nginx -d azmoonma.ir -d www.azmoonma.ir

# تمدید خودکار SSL
sudo crontab -e
# خط زیر را اضافه کنید:
# 0 12 * * * /usr/bin/certbot renew --quiet
```

## مرحله ۱۱: راه‌اندازی Cron Job برای Laravel

```bash
sudo crontab -e
```

خط زیر را اضافه کنید:

```cron
* * * * * cd /var/www/onlineazmoon && php artisan schedule:run >> /dev/null 2>&1
```

## مرحله ۱۲: راه‌اندازی Queue Worker (اختیاری)

```bash
# ایجاد سرویس systemd برای queue worker
sudo nano /etc/systemd/system/laravel-queue.service
```

محتوای زیر را قرار دهید:

```ini
[Unit]
Description=Laravel Queue Worker

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/onlineazmoon/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

فعال‌سازی و راه‌اندازی سرویس:

```bash
sudo systemctl daemon-reload
sudo systemctl enable laravel-queue
sudo systemctl start laravel-queue
```

## مرحله ۱۳: مانیتورینگ و لاگ‌ها

```bash
# مشاهده لاگ‌های Laravel
tail -f /var/www/onlineazmoon/storage/logs/laravel.log

# مشاهده لاگ‌های Nginx
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log

# مشاهده لاگ‌های PHP-FPM
sudo tail -f /var/log/php8.2-fpm.log
```

## مرحله ۱۴: تست نهایی

- باز کردن سایت در مرورگر: `https://azmoonma.ir`
- تست صفحه ورود و ثبت‌نام
- تست ایجاد آزمون و سوالات
- بررسی عملکرد سیستم

## نکات مهم

1. **پشتیبان‌گیری**: تنظیم پشتیبان‌گیری روزانه از دیتابیس:
   ```bash
   mysqldump -u azmoon_user -p onlineazmoon > backup_$(date +%Y%m%d).sql
   ```

2. **به‌روزرسانی**: برای به‌روزرسانی پروژه:
   ```bash
   cd /var/www/onlineazmoon
   git pull origin main
   composer install --optimize-autoloader --no-dev
   npm install && npm run build
   php artisan migrate --force
   php artisan cache:clear
   php artisan config:clear
   sudo systemctl restart nginx
   ```

3. **مونیتورینگ**: استفاده از ابزارهایی مانند `htop` برای مانیتورینگ منابع سرور

4. **امنیت**:定期 به‌روزرسانی سیستم و پکیج‌ها:
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

## عیب‌یابی مشکلات رایج

### مشکل: خطای "Connection refused" هنگام اجرای دستورات Laravel
این خطا معمولاً به دلیل عدم اجرای سرویس Redis است.

```bash
# بررسی وضعیت Redis
sudo systemctl status redis-server

# راه‌اندازی Redis
sudo systemctl start redis
sudo systemctl enable redis-server

# تست اتصال
redis-cli ping
# باید PONG برگرداند

# اگر مشکل ادامه داشت، بررسی کنید که Redis روی پورت صحیح در حال اجرا است
sudo netstat -tulpn | grep redis
```

### مشکل: صفحه سفید یا خطای 500
```bash
# بررسی لاگ‌های Laravel
tail -f /var/www/onlineazmoon/storage/logs/laravel.log

# بررسی دسترسی‌ها
sudo chown -R www-data:www-data /var/www/onlineazmoon
```

### مشکل: خطای دیتابیس (Access denied)
این خطا معمولاً به دلیل عدم وجود کاربر دیتابیس یا رمز عبور اشتباه است.

```bash
# ۱. ورود به MariaDB به عنوان root
sudo mysql -u root -p

# ۲. در داخل MariaDB، بررسی کاربران موجود
SELECT User, Host FROM mysql.user;

# ۳. اگر کاربر وجود دارد اما رمز عبور کار نمی‌کند، آن را حذف و دوباره بسازید
DROP USER IF EXISTS 'azmoon_user'@'localhost';
CREATE USER 'azmoon_user'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON onlineazmoon.* TO 'azmoon_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# ۴. بررسی تنظیمات .env
cat /var/www/onlineazmoon/.env | grep DB_

# ۵. تست اتصال به دیتابیس با کاربر جدید
mysql -u azmoon_user -p onlineazmoon
# رمز عبور را وارد کنید

# ۶. اگر اتصال موفق بود، دوباره مایگریشن را اجرا کنید
php artisan migrate --force
```

### مشکل: کار نکردن CSS/JS
```bash
# بیلد مجدد assets
cd /var/www/onlineazmoon
npm run build

# لینک کردن storage
php artisan storage:link
```

## تمام!

سامانه آزمون آنلاین شما با موفقیت روی سرور اوبونتو نصب و راه‌اندازی شد.
## تمام!

سامانه آزمون آنلاین شما با موفقیت روی سرور اوبونتو نصب و راه‌اندازی شد.
