# Hosting installation / نصب روی هاست

AI is optional and off by default. Configure a local OpenAI-compatible endpoint or cloud-compatible API in **Settings > AI**. Cloud keys are encrypted with `APP_KEY`; keep that key private and backed up. The hosted CRM works fully if outbound internet access is unavailable.

هوش مصنوعی اختیاری و پیش‌فرض خاموش است. مدل محلی سازگار یا API ابری از مسیر **تنظیمات > هوش مصنوعی** تنظیم می‌شود. کلید ابری با `APP_KEY` رمزگذاری می‌شود و CRM بدون دسترسی اینترنت نیز کامل کار می‌کند.

## English

1. Upload and extract `crm-workspace-hosted-10.2.1.zip` into the site document root.
2. Create an empty MySQL/MariaDB database and user in cPanel or DirectAdmin.
3. Open `/install.php`. The first page checks PHP 8.2+, PDO MySQL, OpenSSL, Fileinfo, ZIP, GD, mbstring, and writable folders. Resolve every FAIL before continuing.
4. Enter the database credentials, company, and administrator. HTTPS installs automatically use `APP_ENV=production` and secure session cookies.
5. In **Settings**, copy either the exact CLI cron command or the CRON_KEY-protected web-cron URL. Run it every five minutes.
6. Keep `.env` and `storage/` outside public backups. Never replace them during an update.
7. The hosted updater downloads the manifest ZIP, verifies SHA-256, creates a pre-update backup, preserves `.env` and `storage/`, replaces application files, and runs pending migrations.

### Safe upgrade of crm.nadtrading.com

1. In Hostinger, confirm the subdomain document root is `/home/u409141504/domains/nadtrading.com/public_html/crm`. Do not work in the parent `public_html` directory.
2. Download both the current files backup and the CRM database backup before changing files.
3. Preserve the existing `.env`, `storage/`, and `uploads/` paths. Never overwrite or delete them.
4. Extract the hosted ZIP into a temporary sibling directory. Copy the application files into the `crm` document root, excluding `.env`, `storage/`, and `uploads/`.
5. Open `https://crm.nadtrading.com/upgrade-v9.1-to-v10.php` while signed in as an administrator. The shared migration runner applies only pending migrations, including the counterparty fields in version 10.2.1.
6. Verify customer and supplier records, then remove the uploaded ZIP and temporary extraction directory. The main `nadtrading.com` document root and database are not part of this procedure.

## فارسی

۱. فایل `crm-workspace-hosted-10.2.1.zip` را در ریشهٔ دامنه آپلود و Extract کنید.
۲. در cPanel یا DirectAdmin یک دیتابیس و کاربر MySQL/MariaDB بسازید.
۳. آدرس `/install.php` را باز کنید. صفحهٔ اول نسخهٔ PHP، افزونه‌ها و دسترسی نوشتن پوشه‌ها را بررسی می‌کند. پیش از ادامه همهٔ موارد باید PASS باشند.
۴. اطلاعات دیتابیس، شرکت و مدیر را وارد کنید. روی HTTPS، حالت production و کوکی امن به‌طور خودکار فعال می‌شود.
۵. در **Settings** فرمان دقیق Cron یا آدرس Web-Cron دارای `CRON_KEY` را کپی کنید و هر پنج دقیقه اجرا نمایید.
۶. هنگام ارتقا فایل `.env` و پوشهٔ `storage/` را جایگزین نکنید.
۷. به‌روزرسان داخلی SHA-256 را کنترل می‌کند، نسخهٔ پشتیبان می‌سازد، فایل‌های داده را نگه می‌دارد و Migrationها را اجرا می‌کند.

### ارتقای امن crm.nadtrading.com

۱. در Hostinger بررسی کنید Document Root زیردامنه دقیقاً `/home/u409141504/domains/nadtrading.com/public_html/crm` باشد. داخل پوشهٔ مادر `public_html` کاری انجام ندهید.
۲. قبل از هر تغییر، فایل‌های فعلی و دیتابیس CRM را جداگانه Backup و Download کنید.
۳. فایل `.env` و پوشه‌های `storage/` و `uploads/` را نگه دارید و هرگز جایگزین یا حذف نکنید.
۴. ZIP میزبانی را ابتدا در یک پوشهٔ موقت هم‌سطح Extract کنید. سپس فایل‌های برنامه را، به‌جز `.env`، `storage/` و `uploads/`، داخل پوشهٔ `crm` کپی کنید.
۵. با حساب مدیر آدرس `https://crm.nadtrading.com/upgrade-v9.1-to-v10.php` را باز کنید. Migration Runner مشترک فقط Migrationهای اجرا‌نشده، از جمله تغییرات طرف‌حساب نسخهٔ 10.2.1، را اعمال می‌کند.
۶. رکوردهای مشتری و تأمین‌کننده را بررسی کنید و سپس ZIP و پوشهٔ موقت را حذف کنید. این مراحل هیچ تغییری در Document Root یا دیتابیس سایت اصلی `nadtrading.com` ایجاد نمی‌کند.
