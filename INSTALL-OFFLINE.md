# Windows Offline installation / نصب آفلاین ویندوز

## English

1. Run `crm-workspace-offline-10.1.3-setup.exe` as administrator on Windows 10/11 x64.
2. Choose the web port and daily-backup folder. Keep the optional private-network firewall task selected to connect phones and other PCs.
3. Setup installs its own PHP 8.2 runtime, MariaDB on `127.0.0.1:3307`, and Caddy. It generates random database and application secrets; database fields are never shown.
4. The browser opens at the company/admin step. Complete it, then sign in.
5. Windows startup and scheduled tasks start MariaDB/PHP FastCGI/Caddy, run cron every five minutes, and create a daily backup while retaining the latest 14.
6. Open **Connect devices** to see the LAN URL and QR code. Devices must be on the same private network.
7. Restore a backup under **Admin → Backups**. Type its exact filename to confirm.
8. Run a newer installer over the existing installation to upgrade. It keeps `.env`, database, uploads, and backups and runs pending migrations.
9. Uninstall keeps all data by default. Choose **No** when asked about keeping data only if permanent deletion is intended.

## فارسی

۱. فایل `crm-workspace-offline-10.1.3-setup.exe` را در Windows 10/11 نسخهٔ ۶۴بیتی با دسترسی Administrator اجرا کنید.
۲. پورت وب و پوشهٔ پشتیبان روزانه را انتخاب کنید. برای اتصال موبایل و رایانه‌های شبکه، گزینهٔ Firewall شبکهٔ Private را فعال نگه دارید.
۳. نصب‌کننده PHP، MariaDB روی `127.0.0.1:3307` و وب‌سرور Caddy را نصب می‌کند و رمزهای تصادفی می‌سازد؛ کاربر هیچ فیلد دیتابیسی نمی‌بیند.
۴. مرورگر مستقیماً مرحلهٔ مشخصات شرکت و مدیر را باز می‌کند.
۵. Taskهای ویندوز سرویس‌ها را هنگام روشن‌شدن اجرا می‌کنند، Cron را هر پنج دقیقه و پشتیبان را روزانه اجرا می‌کنند. فقط ۱۴ نسخهٔ آخر نگهداری می‌شود.
۶. در صفحهٔ **اتصال دستگاه‌ها** آدرس شبکه و QR را مشاهده کنید.
۷. بازیابی یک‌کلیکی از **مدیریت ← پشتیبان‌ها** انجام می‌شود و برای تأیید باید نام فایل را وارد کنید.
۸. نصب‌کنندهٔ نسخهٔ جدید را روی نسخهٔ موجود اجرا کنید؛ داده‌ها حفظ و Migrationها اجرا می‌شوند.
۹. حذف برنامه به‌طور پیش‌فرض داده‌ها را نگه می‌دارد. فقط برای حذف دائمی، در پرسش پایانی گزینهٔ عدم نگهداری را انتخاب کنید.
