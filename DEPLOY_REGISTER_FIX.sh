#!/bin/bash

echo "=========================================="
echo "اسکریپت نصب و راه‌اندازی سیستم ثبت‌نام"
echo "=========================================="
echo ""

# رنگ‌ها برای خروجی
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# تابع برای نمایش پیام موفقیت
success() {
    echo -e "${GREEN}✓ $1${NC}"
}

# تابع برای نمایش پیام خطا
error() {
    echo -e "${RED}✗ $1${NC}"
}

# تابع برای نمایش پیام هشدار
warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

echo "مرحله 1: دریافت آخرین تغییرات از Git"
echo "----------------------------------------"
git pull origin main
if [ $? -eq 0 ]; then
    success "کدها با موفقیت دریافت شدند"
else
    error "خطا در دریافت کدها از Git"
    exit 1
fi
echo ""

echo "مرحله 2: نصب وابستگی‌های Composer"
echo "----------------------------------------"
composer install --no-dev --optimize-autoloader
if [ $? -eq 0 ]; then
    success "وابستگی‌های Composer نصب شدند"
else
    warning "خطا در نصب وابستگی‌ها - ادامه می‌دهیم..."
fi
echo ""

echo "مرحله 3: اجرای Migration ها"
echo "----------------------------------------"
php artisan migrate --force
if [ $? -eq 0 ]; then
    success "Migration ها با موفقیت اجرا شدند"
else
    error "خطا در اجرای Migration ها"
    echo "لطفاً به صورت دستی بررسی کنید: php artisan migrate"
fi
echo ""

echo "مرحله 4: اجرای Seeder برای رشته‌های تحصیلی"
echo "----------------------------------------"
php artisan db:seed --class=EducationFieldSeeder --force
if [ $? -eq 0 ]; then
    success "داده‌های اولیه رشته‌های تحصیلی ایجاد شدند"
else
    warning "خطا در اجرای Seeder - ممکن است قبلاً اجرا شده باشد"
fi
echo ""

echo "مرحله 5: پاک کردن تمام کش‌ها"
echo "----------------------------------------"
php artisan optimize:clear
success "تمام کش‌ها پاک شدند"
echo ""

echo "مرحله 6: پاک کردن کش‌های جداگانه"
echo "----------------------------------------"
php artisan view:clear
success "کش View پاک شد"

php artisan route:clear
success "کش Route پاک شد"

php artisan config:clear
success "کش Config پاک شد"

php artisan cache:clear
success "کش Application پاک شد"
echo ""

echo "مرحله 7: بهینه‌سازی برای Production"
echo "----------------------------------------"
php artisan config:cache
success "Config کش شد"

php artisan route:cache
success "Route کش شد"

php artisan view:cache
success "View کش شد"
echo ""

echo "مرحله 8: تنظیم دسترسی‌ها"
echo "----------------------------------------"
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
success "دسترسی‌ها تنظیم شدند"
echo ""

echo "=========================================="
echo "بررسی نهایی"
echo "=========================================="
echo ""

echo "بررسی جدول education_fields:"
php artisan tinker --execute="echo 'تعداد رشته‌های تحصیلی: ' . App\Models\EducationField::count() . PHP_EOL;"
echo ""

echo "بررسی Route های ثبت‌نام:"
php artisan route:list | grep register
echo ""

echo "=========================================="
success "نصب با موفقیت انجام شد!"
echo "=========================================="
echo ""
echo "مراحل بعدی:"
echo "1. به آدرس https://azmoonma.ir/register بروید"
echo "2. کلید F12 را بزنید و Console را باز کنید"
echo "3. فرم را پر کنید و دکمه ثبت‌نام را بزنید"
echo "4. در Console باید پیام 'Button clicked' را ببینید"
echo "5. در تب Network باید درخواست به /livewire/update ببینید"
echo ""
echo "اگر مشکلی وجود دارد، لاگ‌ها را بررسی کنید:"
echo "tail -f storage/logs/laravel.log"
echo ""
