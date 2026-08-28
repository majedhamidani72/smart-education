# چک‌لیست انتشار درسکا

1. در `.env` مقادیر `APP_ENV=production`، `APP_DEBUG=false`، `APP_URL`، `FRONTEND_URL` و اطلاعات دیتابیس/زیبال/پیامک را تنظیم کنید.
2. اجرا: `composer install --no-dev --optimize-autoloader` و سپس `php artisan migrate --force`.
3. کش: `php artisan optimize` و ساخت لینک فایل‌ها با `php artisan storage:link`.
4. زمان‌بند سرور باید هر دقیقه `php artisan schedule:run` را اجرا کند؛ نسخه پشتیبان روزانه ساعت ۰۲:۳۰ ساخته و ۱۴ نسل نگهداری می‌شود.
5. صف با Supervisor اجرا شود: `php artisan queue:work --tries=3 --timeout=120`.
6. فرانت: `npm ci && npm run build && npm run start` با `NEXT_PUBLIC_API_BASE_URL=https://domain/api/v1`.
7. پیش از انتشار، `/api/v1/health`، ورود، خرید آزمایشی، بازگشت به همان محتوا، دانلود پاورپوینت و یک آزمون کامل بررسی شود.
8. پوشه `storage/app/backups` خارج از همان سرور نیز روزانه کپی و بازیابی آن ماهانه آزمایش شود.
