# CRM Workspace

Source repository: <https://github.com/makan20042002/crm-workspace>

CRM Workspace is a free, self-hosted CRM and operations suite for small and
medium businesses. It is Persian-first, bilingual, and can run either on a
PHP hosting account or as a self-contained Windows installation.

## Included modules

- Customers, contacts, leads, opportunities, pipelines and quotations
- Products, sales targets, orders and quotation PDF output
- RFQs, supplier quotations, comparisons, payments, LCs and shipments
- Projects, tasks, comments, time logs, files, reminders and follow-ups
- Tickets, SLA policies, canned replies and a customer portal
- Reports, import/export, audit logs, API tokens and custom fields
- Offline Windows mode with LAN access, scheduled jobs and daily backups

## Downloads

Official release downloads, SHA-256 checksums and installation instructions
are published on [makanlab.tech](https://makanlab.tech/).

- Hosting: see `INSTALL-HOSTING.md`
- Windows offline: see `INSTALL-OFFLINE.md`
- Release verification: see `RELEASE-TEST-CHECKLIST.md`

## Requirements

- Hosted package: PHP 8.2+, MySQL or MariaDB, and the PHP extensions listed by
  the installer requirement screen
- Offline package: 64-bit Windows 10 or Windows 11

The hosting archive includes production Composer dependencies. The Windows
installer includes PHP, MariaDB and Caddy, so end users do not need to install
those components separately.

## Security

Do not commit `.env`, private uploads, backups, certificates or signing keys.
For production deployments use HTTPS, generate unique `APP_KEY` and
`CRON_KEY` values, and test backups and restores. See `SECURITY.md` for
responsible vulnerability reporting.

## License

Copyright (C) 2026 Makan — <https://makanlab.tech>

CRM Workspace is free software licensed under the GNU Affero General Public
License, version 3 or any later version (`AGPL-3.0-or-later`). You may use,
copy, study, modify and redistribute it under that license. Modified versions
offered to users over a network must provide those users access to the
corresponding source code.

The software is provided without warranty. See `LICENSE`, `NOTICE` and
`THIRD-PARTY-NOTICES.md` for the complete terms and component notices.

This project does not require a paid activation key.
