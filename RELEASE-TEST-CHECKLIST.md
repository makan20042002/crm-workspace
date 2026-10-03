# Release test checklist

## Clean Windows 10/11 x64

- Verify the installer SHA-256 before launch; install as Administrator.
- Change the default web port and backup directory; test both firewall consent choices.
- Confirm no PHP, MariaDB, Composer, or web server was preinstalled.
- Confirm MariaDB listens only on `127.0.0.1` and the selected non-default port.
- Confirm Caddy listens on the selected LAN port and PHP runs through FastCGI, not PHP's built-in server.
- Confirm first launch skips database fields and opens company/admin setup.
- Complete all core workflows, PDF export, XLSX import/export, local fonts, login/logout, and browser refresh with the service worker disabled/unregistered.
- Reboot; verify the startup task restores all three processes.
- Verify cron runs every five minutes and daily backup retains only 14 files.
- Open Connect devices from a second LAN device and scan the QR code.
- Restore a backup and verify records plus uploads.
- Install a newer build over the existing copy; verify data remains and migrations are recorded once.
- Uninstall once while preserving data, reinstall, and verify recovery. Repeat in a disposable VM choosing permanent data removal.
- Disconnect internet throughout core testing; confirm no asset request leaves the machine and internet-dependent controls are marked.

## cPanel / DirectAdmin

- Verify ZIP SHA-256, extract to a clean HTTPS subdomain, and confirm `.env` is absent.
- Open installer; force one failed requirement and confirm database fields remain inaccessible until all checks pass.
- Install against a least-privilege database user; verify `APP_ENV=production`, `APP_MODE=hosted`, and secure cookies over HTTPS.
- Verify PDF and XLSX work without Composer access on the host.
- Confirm browser developer tools show no CDN/font/icon/script requests and the app works after unregistering service workers.
- Run both the displayed CLI cron command and the CRON_KEY web-cron URL; verify invalid keys return 403.
- Exercise core CRM, RFQ, quotation/order, files, tickets/portal, backup/restore, and audit workflows.
- Serve a test update manifest and ZIP; verify bad SHA-256 is rejected, a valid update creates a backup, preserves `.env`/`storage`, and records migrations once.
- Confirm Offline-only Connect devices navigation is absent and internet-backed integration controls remain available in Hosted mode.

## Not validated by static/build checks

- The compiled installer was not installed end-to-end on a fresh Windows 10/11 virtual machine in this environment.
- The hosting ZIP was not deployed to a real cPanel or DirectAdmin account in this environment.
- Actual SMS, SMTP/IMAP, FX-provider, Asterisk AMI, and public update-server behavior requires real credentials and endpoints.
- LAN discovery/firewall behavior varies by router, Windows network profile, and endpoint-security product.
- cPanel/DirectAdmin permissions and cron syntax vary by hosting provider.
