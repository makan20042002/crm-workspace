# Upgrade V9.1 to V10

1. Back up the current database and project folder.
2. Extract V10 to a new folder. Do not overwrite the working copy until testing succeeds.
3. Copy the existing `.env` to the V10 folder and update `APP_URL` if the folder/host changed.
4. Sign in as `super_admin` or `company_admin`.
5. Open `upgrade-v9.1-to-v10.php` and click **Run V10 migration**.
6. The migration executes `migrations/v10.sql`; it creates companion V10 tables and does not remove V9 tables.
7. Open the dashboard, then follow `TEST-CHECKLIST-V10.md`.
8. Configure Task Scheduler/cron for `cron-v10.php`.
9. For PDF download run `composer install`; without mPDF, quotation output falls back to browser printing.
10. Only after the new copy passes your testing should you switch the live subdomain to V10.

The upgrade page requires an authenticated company/super administrator and CSRF validation.
