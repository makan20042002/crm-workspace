# Manual test checklist — V10

Use a fresh test company and also a `viewer` account. Each line gives the exact navigation/click path.

- Login: open `login.php` → enter admin credentials → **ورود به سیستم / Sign in** → dashboard opens without PHP/JS errors.
- Language: top language control → switch **FA/EN** → direction changes RTL/LTR and labels change.
- Dark mode: top theme control → toggle dark mode → sidebar/cards/forms remain readable.
- Dashboard: **Dashboard** → verify My tasks today, overdue follow-ups, pipeline, target, due payments, in-transit shipments and legacy charts; click each widget to open its filtered destination.
- Onboarding: first login → complete/add customer, opportunity, user → checklist updates; click **Load sample data** only on a disposable database.
- Customers list: **Sales → Customers** → filter/sort/page/choose columns/save view → inline owner/status → select rows → bulk assign/export/delete → Trash → restore.
- Customer record: Customers → click a customer name → edit details → Add note/task/reminder/file → Call/Email/SMS → timeline shows newest event → related tabs open.
- Contacts: **Sales → Contacts** → create → open full record → edit → delete → Trash restore.
- Leads/Inquiries: **Sales → New inquiries** → create with probability/value fields as applicable → edit stage → delete/restore.
- Opportunities: **Sales → Opportunities** → create probability `0` → edit without changing stage → confirm probability remains `0` and stage remains unchanged → delete/restore.
- Quotations: **Sales → Quotations** → create → open → add/edit/delete line → product picker → totals recalculate → edit quotation → PDF/print → delete/restore.
- Products: **Sales → Products & Pricing** → create/edit/delete product → use it in a quotation line.
- Targets/forecast: **Sales → Sales Targets** → create target → dashboard/report forecast reflects data → edit/delete.
- Projects: **Projects → Projects** → create → full record → add task/file → edit status/owner → delete/restore.
- Tasks: **Projects → Tasks** → create → inline status/owner → open task → add comment/time entry/file → edit → delete/restore.
- Follow-ups: **Projects/CRM → Follow-ups** → create due follow-up → edit/complete/delete → run cron and verify overdue notification.
- Suppliers: **Trade → Suppliers** → create → full record → edit/delete/restore → related supplier quotes visible.
- RFQ: **Trade → RFQs** → create → open full record → add/edit/delete RFQ item → compare supplier quotes → convert RFQ to quotation.
- Payments: **Trade → Payments** → create pending → edit unrelated field and confirm `paid_at` stays empty → change to paid and confirm it is set once → edit again and confirm timestamp is not overwritten → edit/delete.
- LC: **Trade → Letters of Credit** → create with expiry → edit/delete → run cron near expiry and verify notification rule/automatic alert behavior.
- Shipments: **Trade → Shipments** → create ETA/status → edit/delete → run cron with due ETA and verify alert path.
- Trade tools: **Trade → Trade Tools** → FX create/edit/delete → landed cost calculate/edit/delete → document checklist create/edit/delete → convert quote to order → accounting export Sepidar/Hesabfa CSV.
- Automation: **Admin/Automation → Automation** → create rule (stage changed → create task/notify) → modify a matching record → check **Workflow runs** → edit/disable/delete rule.
- Reminders: **Automation → Reminders** → create linked reminder → edit → snooze → cancel → run `cron-v10.php` and verify in-app notification.
- Notifications: bell/**Notifications** → mark one read → unread count decreases.
- Communications: **Admin/Communications → Communications** → create/edit/delete email/SMS templates → send with configured provider → failure/success appears in UI and log.
- Integrations: **Admin → Integrations** → create/edit/delete SMTP/SMS/VoIP provider → test button → masked credentials remain unchanged after editing other fields.
- IMAP: Integrations configured → **Communications → Sync IMAP** → matching contact/customer email appears in communication/timeline.
- VoIP: configure AMI → customer record → **Call** → click-to-call/log result appears; verify fallback `tel:` on mobile.
- Web form: **Admin → Web Forms** → create/edit/delete → copy embed code → open generated `web-lead.php?k=...` → submit → new inquiry appears.
- Tickets: **Service → Support Tickets** → create/edit/delete ticket → open → add/delete reply → canned reply create/edit/delete → portal user create/edit/deactivate.
- Reports: **Reports** → open classic team/pipeline/vertical charts → Report Builder → select module/fields/filter/group → run → save → reload saved report → export CSV.
- Import: **Admin/Data → Import / Export** → upload CSV → map columns → choose duplicate strategy → preview/commit → imported rows visible in list.
- Global search: press **Ctrl+K** → search customer/project/RFQ → click result → correct full record page opens.
- Users: **Admin → Users** → create user with phone → edit super admin and verify role cannot be demoted → deactivate normal user → Change my password → sign out/in with new password → Send password reset with SMTP configured.
- Permissions: sign in as `viewer` → verify write buttons/menu items are hidden → directly request write/export/import/send endpoints and confirm HTTP 403.
- Branding: **Admin → Settings/Branding** → edit name/logo URL/primary/accent/base currency → refresh and verify branding.
- Trash: **Admin → Trash** → restore deleted core records and confirm list visibility returns.
- Backups: **Admin → Backups** → Create backup → modify disposable data → Restore backup → verify data reverts → delete backup.
- Updates: **Admin → Backups/Updates** → Check for updates with manifest configured → status displayed; do not apply untrusted manifests.
- Mobile: resize browser to 380px → open dashboard, core lists and record page → cards/bottom navigation/full-screen forms remain usable.
- PWA: browser install/Add to Home Screen → launch standalone → navigation works after login.

## Not testable in this build environment

- Real MySQL/MariaDB execution of every migration/query and transaction (no access to your XAMPP database).
- SMTP delivery/reply synchronization without a real SMTP/IMAP account.
- Kavenegar, Melipayamak and SMS.ir delivery without provider credentials and credit.
- Asterisk/Issabel AMI call origination/incoming-call behavior without your PBX.
- mPDF rendered Persian PDF without running Composer/vendor in your environment.
- Windows Task Scheduler execution; only the PHP cron script can be syntax-checked here.
- Sepidar/Hesabfa semantic import acceptance; V10 exports CSV structures but your accounting-version field mapping must be verified against the target product/version.
- Safe remote update application is intentionally not automatic in this build; update check reads a configured manifest, while deployment should still be staged/backed up before replacing code.
