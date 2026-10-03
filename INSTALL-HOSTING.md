# Hosting installation / نصب روی هاست

## English

1. Upload and extract `crm-workspace-hosted-10.1.3.zip` into the site document root.
2. Create an empty MySQL/MariaDB database and user in cPanel or DirectAdmin.
3. Open `/install.php`. The first page checks PHP 8.2+, PDO MySQL, OpenSSL, Fileinfo, ZIP, GD, mbstring, and writable folders. Resolve every FAIL before continuing.
4. Enter the database credentials, company, and administrator. HTTPS installs automatically use `APP_ENV=production` and secure session cookies.
5. In **Settings**, copy either the exact CLI cron command or the CRON_KEY-protected web-cron URL. Run it every five minutes.
6. Keep `.env` and `storage/` outside public backups. Never replace them during an update.
7. The hosted updater downloads the manifest ZIP, verifies SHA-256, creates a pre-update backup, preserves `.env` and `storage/`, replaces application files, and runs pending migrations.

## فارسی

۱. فایل `crm-workspace-hosted-10.1.3.zip` را در ریشهٔ دامنه آپلود و Extract کنید.
۲. در cPanel یا DirectAdmin یک دیتابیس و کاربر MySQL/MariaDB بسازید.
۳. آدرس `/install.php` را باز کنید. صفحهٔ اول نسخهٔ PHP، افزونه‌ها و دسترسی نوشتن پوشه‌ها را بررسی می‌کند. پیش از ادامه همهٔ موارد باید PASS باشند.
۴. اطلاعات دیتابیس، شرکت و مدیر را وارد کنید. روی HTTPS، حالت production و کوکی امن به‌طور خودکار فعال می‌شود.
۵. در **Settings** فرمان دقیق Cron یا آدرس Web-Cron دارای `CRON_KEY` را کپی کنید و هر پنج دقیقه اجرا نمایید.
۶. هنگام ارتقا فایل `.env` و پوشهٔ `storage/` را جایگزین نکنید.
۷. به‌روزرسان داخلی SHA-256 را کنترل می‌کند، نسخهٔ پشتیبان می‌سازد، فایل‌های داده را نگه می‌دارد و Migrationها را اجرا می‌کند.
