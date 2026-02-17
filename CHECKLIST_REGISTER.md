# چک‌لیست کامل بررسی مشکل ثبت‌نام

## ✅ موارد بررسی شده (در کد لوکال)

### 1. ساختار دیتابیس
- ✅ جدول `users` دارای ستون `education_field` است
- ✅ جدول `education_fields` ایجاد شده است
- ✅ Migration ها صحیح هستند

### 2. مدل‌ها
- ✅ `User` model دارای `education_field` در `fillable` است
- ✅ `EducationField` model با متد `getActive()` ایجاد شده است
- ✅ Seeder برای `EducationField` آماده است

### 3. فرم ثبت‌نام
- ✅ تمام فیلدها دارای `wire:model` هستند
- ✅ دکمه ثبت‌نام دارای `wire:click="register"` است
- ✅ تابع `register` در کامپوننت Volt تعریف شده است
- ✅ Validation rules صحیح هستند

### 4. Layout و Scripts
- ✅ `@livewireStyles` در head قرار دارد
- ✅ `@livewireScripts` قبل از `</body>` قرار دارد
- ✅ Vite assets لود می‌شوند

### 5. Filament Resource
- ✅ `EducationFieldResource` ایجاد شده است
- ✅ صفحات List, Create, Edit آماده هستند
- ✅ منوی "مدیریت ثبت‌نام" تنظیم شده است

---

## ⚠️ موارد نیازمند بررسی روی سرور

### مرحله 1: بررسی دیتابیس
```bash
# اتصال به دیتابیس
mysql -u root -p

# انتخاب دیتابیس
USE onlineazmoon;

# بررسی جدول users
DESCRIBE users;

# بررسی جدول education_fields
DESCRIBE education_fields;

# بررسی داده‌های education_fields
SELECT * FROM education_fields;

# خروج
EXIT;
```

**انتظار:**
- جدول `users` باید ستون `education_field` داشته باشد
- جدول `education_fields` باید حداقل 5 رکورد داشته باشد (عمران، معماری، ...)

---

### مرحله 2: بررسی Migration ها
```bash
cd /var/www/onlineazmoon

# لیست migration های اجرا شده
php artisan migrate:status

# اگر migration ها اجرا نشده‌اند
php artisan migrate --force
```

**انتظار:**
- تمام migration ها باید وضعیت `Ran` داشته باشند

---

### مرحله 3: بررسی Seeder
```bash
# اجرای seeder برای education fields
php artisan db:seed --class=EducationFieldSeeder --force

# بررسی تعداد رکوردها
php artisan tinker
>>> App\Models\EducationField::count()
>>> App\Models\EducationField::all()
>>> exit
```

**انتظار:**
- باید 5 رشته تحصیلی نمایش داده شود

---

### مرحله 4: بررسی Route ها
```bash
# لیست route های مربوط به register
php artisan route:list | grep register

# پاک کردن کش route
php artisan route:clear
```

**انتظار:**
- باید route با نام `register` وجود داشته باشد

---

### مرحله 5: بررسی Livewire
```bash
# بررسی نسخه Livewire
composer show livewire/livewire
composer show livewire/volt

# پاک کردن کش Livewire
php artisan livewire:delete-stubs
```

**انتظار:**
- Livewire نسخه 3.x باشد
- Volt نسخه 1.x باشد

---

### مرحله 6: بررسی لاگ‌ها
```bash
# مشاهده لاگ‌های لحظه‌ای
tail -f storage/logs/laravel.log

# در ترمینال دیگر، صفحه register را باز کنید و دکمه را بزنید
# آیا خطایی در لاگ نمایش داده می‌شود؟
```

---

### مرحله 7: تست در مرورگر
1. به `https://azmoonma.ir/register` بروید
2. کلید **F12** را بزنید
3. به تب **Console** بروید
4. فرم را پر کنید و دکمه را بزنید

**بررسی Console:**
- آیا پیام `Button clicked` نمایش داده می‌شود؟
  - ✅ بله → JavaScript کار می‌کند
  - ❌ خیر → مشکل در لود شدن JS

**بررسی Network Tab:**
- به تب **Network** بروید
- فیلتر را روی **XHR** بگذارید
- دکمه را دوباره بزنید
- آیا درخواستی به `/livewire/update` ارسال می‌شود؟
  - ✅ بله → Livewire کار می‌کند، مشکل در سمت سرور است
  - ❌ خیر → Livewire لود نشده است

---

## 🔧 راه‌حل‌های احتمالی

### اگر JavaScript کار نمی‌کند:
```bash
# بررسی فایل app.js
cat resources/js/app.js

# بیلد کردن assets
npm install
npm run build

# یا در حالت development
npm run dev
```

### اگر Livewire لود نمی‌شود:
```bash
# پاک کردن تمام کش‌ها
php artisan optimize:clear

# نصب مجدد Livewire
composer require livewire/livewire:^3.6
composer require livewire/volt:^1.7

# پابلیش کردن assets
php artisan livewire:publish --assets
```

### اگر خطای 500 دریافت می‌کنید:
```bash
# فعال کردن debug mode موقتی
# در فایل .env:
APP_DEBUG=true

# مشاهده خطای دقیق در مرورگر
```

### اگر جدول education_fields خالی است:
```bash
php artisan db:seed --class=EducationFieldSeeder --force
```

---

## 📋 دستورات کامل نصب (یکجا)

```bash
#!/bin/bash
cd /var/www/onlineazmoon
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --class=EducationFieldSeeder --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

یا استفاده از اسکریپت آماده:
```bash
chmod +x DEPLOY_REGISTER_FIX.sh
./DEPLOY_REGISTER_FIX.sh
```

---

## 📞 گزارش مشکل

اگر بعد از انجام تمام مراحل بالا همچنان مشکل وجود دارد، لطفاً موارد زیر را گزارش دهید:

1. خروجی دستور: `php artisan route:list | grep register`
2. خروجی دستور: `php artisan tinker --execute="App\Models\EducationField::count()"`
3. محتوای Console مرورگر (F12 → Console)
4. محتوای Network Tab (F12 → Network → XHR)
5. آخرین خطوط فایل `storage/logs/laravel.log`
