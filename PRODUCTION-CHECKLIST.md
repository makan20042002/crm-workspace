# CRM Workspace V10 Production Checklist

- Set `APP_ENV=production`.
- Use HTTPS and `SESSION_SECURE=true`.
- Use a dedicated MySQL/MariaDB user with only the required database privileges; never production `root`.
- Keep `.env`, `storage/`, database backups and logs outside public download access. `.htaccess` blocks common sensitive files on Apache; verify your host configuration.
- Delete or server-restrict `install.php`, `repair.php`, and upgrade scripts after deployment.
- Configure `APP_KEY` and `CRON_KEY`; do not reuse keys between unrelated installations.
- Run `cron-v10.php` from server cron with `CRON_KEY`; do not expose the key in logs or documentation.
- Configure SMTP/SMS credentials in Integrations and test with non-sensitive accounts first.
- Run `composer install --no-dev --optimize-autoloader` if one-click quotation PDF download is required.
- Back up database and private uploads daily; test restores.
- Put the site behind Cloudflare/WAF if appropriate and enable server rate limiting.
- Review permissions/roles before inviting users.
- For Android/Windows API clients, create scoped/revocable API tokens rather than exposing database credentials.
