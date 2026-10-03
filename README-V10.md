# CRM V10

Persian-first, bilingual CRM/Trade/Projects/Service suite for PHP 8.2 + PDO MySQL/MariaDB. V10 keeps compatible historical data, organizes modules under `api/` and `assets/js/`, and enforces capability-based tenant boundaries on modular endpoints.

## Main V10 areas

- CRM: customers, contacts, inquiries/leads, opportunities, follow-ups, full record pages, timeline, related tabs, custom fields, documents.
- Sales: quotations + lines, product/price catalogue, targets/forecast, mPDF quotation output with browser-print fallback.
- Trade: RFQ, RFQ items, supplier comparison, supplier quote flow, payments, LC, shipments, landed cost, FX, document checklists, orders, accounting CSV export.
- Projects: projects, tasks, files, task discussion/time records, and V10 record views.
- Automation: rule builder, run log, date/event triggers, notifications, reminders/snooze/cancel and cron runner.
- Communications: SMTP, SMS provider adapters (Kavenegar, Melipayamak, SMS.ir + generic), IMAP sync, Asterisk/Issabel AMI click-to-call/logging, templates.
- Service: tickets/SLA, replies/canned replies, portal accounts.
- Data/Admin: paged/sortable/filterable core record lists, saved views, bulk assignment/delete/export, import mapping, trash/restore, branding, users/password reset/change password, backups, update check, onboarding/sample data.
- PWA/mobile responsive shell and API v1 for app clients.

## Requirements

- PHP 8.2+ with PDO MySQL; cURL recommended; OpenSSL recommended; IMAP only for mail sync.
- MySQL 8 or MariaDB.
- Composer only if you want mPDF PDF download: `composer install`.
- Scheduled automation: run `php cron-v10.php` every 5 minutes (or another appropriate interval).
- Incoming-call popups: run `php ami-listener.php COMPANY_ID` as a supervised long-running process for each company that has VoIP/AMI enabled. An authenticated `ami-event.php` bridge endpoint is also available for PBX event relays using `CRON_KEY` as `X-AMI-Key`.

## Fresh install

1. Extract into XAMPP `htdocs`, for example `G:\xampp\htdocs\crm-workspace`.
2. Start Apache + MySQL.
3. Open `http://localhost/crm-workspace/install.php`.
4. Enter database, company branding and first administrator. No vendor-specific records are forced into the database. Sample data is optional.
5. After production deployment, keep `APP_ENV=production`, use HTTPS and `SESSION_SECURE=true`.

## Upgrade

See `UPGRADE-TO-V10.md`.

## Local Vazirmatn

V10 CSS is wired for a local Vazirmatn asset. Put your licensed/local font files in `assets/fonts/` following `assets/fonts/README.txt`. Font binaries are intentionally not included in this archive.

## External-service validation

SMTP, SMS, IMAP and Asterisk/Issabel need real credentials/services. Static checks cannot prove delivery, mailbox sync, ETA SMS, or AMI event/origination behavior. Test them from Integrations after supplying real configuration.
