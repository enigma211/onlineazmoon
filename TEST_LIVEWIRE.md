# تست عملکرد Livewire در صفحه ثبت‌نام

## مراحل تست:

### 1. باز کردن صفحه ثبت‌نام
- به آدرس `https://azmoonma.ir/register` بروید

### 2. باز کردن Console مرورگر
- کلید F12 را بزنید
- به تب Console بروید

### 3. پر کردن فرم و کلیک روی دکمه
- تمام فیلدها را پر کنید
- روی دکمه "ثبت نام در سامانه" کلیک کنید

### 4. بررسی خروجی Console
باید این پیام‌ها را ببینید:
- `Button clicked` - نشان می‌دهد JavaScript کار می‌کند
- اگر Livewire کار کند، باید درخواست‌های AJAX ببینید

### 5. بررسی Network Tab
- به تب Network بروید
- فیلتر را روی XHR بگذارید
- دوباره دکمه را بزنید
- باید درخواستی به `/livewire/update` ببینید

## خطاهای احتمالی:

### اگر "Button clicked" نمایش داده نشد:
- JavaScript اصلاً لود نشده است
- مشکل در فایل `app.js` یا Vite است

### اگر "Button clicked" نمایش داده شد اما Livewire کار نکرد:
- Livewire scripts لود نشده‌اند
- مشکل در `@livewireScripts` است

### اگر خطای 404 یا 500 دیدید:
- مشکل در route یا controller است
- لاگ‌های Laravel را بررسی کنید: `storage/logs/laravel.log`

## دستورات مفید روی سرور:

```bash
# مشاهده لاگ‌های لحظه‌ای
tail -f storage/logs/laravel.log

# پاک کردن تمام کش‌ها
php artisan optimize:clear

# بررسی route ها
php artisan route:list | grep register
```
