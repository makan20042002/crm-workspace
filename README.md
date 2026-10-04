# CRM Workspace 10.2.0

Free, self-hosted CRM and operations software for small and medium businesses.
CRM Workspace is Persian-first, fully bilingual, and ships from one codebase as
a hosting package and a self-contained Windows installer.

**نرم‌افزار رایگان مدیریت ارتباط با مشتری، فروش، بازرگانی و پروژه‌ها؛ با رابط کامل فارسی و انگلیسی.**

[Website](https://makanlab.tech/) ·
[Hosted download](https://makanlab.tech/downloads/crm-workspace-hosted-10.2.0.zip) ·
[Windows download](https://makanlab.tech/downloads/crm-workspace-offline-10.2.0-setup.exe) ·
[Update manifest](https://makanlab.tech/downloads/update-manifest.json)

## Highlights

- Customers, contacts, leads, opportunities and configurable pipeline boards
- Products, quotations, branded PDFs, sales targets and orders
- RFQs, supplier quotes, side-by-side comparison, payments, LCs and shipments
- Projects, tasks, comments, time logs, reminders, follow-ups and record files
- Tickets, SLA policies, canned replies and customer portal
- Reports, CSV import/export, audit logs, API tokens and custom fields
- Persian/Jalali dates and a bilingual responsive interface
- Offline Windows mode with LAN access, scheduled jobs and 14-day backups
- Built-in bilingual Help centre rendered locally without a CDN

## Optional AI assistant

AI is **off by default** and the complete CRM works without it. Administrators
can choose a local Ollama model or any cloud provider offering an
OpenAI-compatible chat-completions API.

The assistant has no free-chat box. Its focused actions can:

- summarise a customer, opportunity or project;
- suggest the next step for an inactive opportunity;
- draft an editable email or SMS reply;
- translate Persian and English notes or messages;
- extract a supplier quotation from text or PDF into a review form.

AI never saves, sends or deletes. Results remain drafts until a person accepts
them. Calls respect record permissions, daily limits and timeouts, and are
logged without storing prompt text. See the built-in guides:

- [English AI guide](docs/AI-GUIDE.en.md)
- [راهنمای فارسی هوش مصنوعی](docs/AI-GUIDE.fa.md)
- [AI test checklist](AI-TEST-CHECKLIST.md)

## Choose a package

| Package | Best for | Included |
|---|---|---|
| Hosting ZIP | cPanel, DirectAdmin and PHP hosting | Application and production Composer dependencies |
| Windows Offline | Windows 10/11 office server or workstation | PHP, MariaDB, Caddy, services, scheduler and backup tools |

Both packages contain the same application code; only package configuration is
different. They work without a service worker and make no required requests to
CDNs, online fonts, icons or scripts.

## Installation

### Hosting

1. Download and extract the [hosting ZIP](https://makanlab.tech/downloads/crm-workspace-hosted-10.2.0.zip).
2. Upload its contents to the domain or subdomain document root.
3. Open the site and pass the requirement check.
4. Enter the database, company and administrator details.
5. Configure the displayed cron command or protected web-cron URL.

Full instructions: [INSTALL-HOSTING.md](INSTALL-HOSTING.md)

### Windows offline

1. Download and run the [Windows installer](https://makanlab.tech/downloads/crm-workspace-offline-10.2.0-setup.exe).
2. Select the web port, backup folder and optional LAN firewall rule.
3. Optionally install Ollama and download a model based on the PC memory.
4. The browser opens at company and administrator setup; database details are
   created automatically and are never shown to the user.

Full instructions: [INSTALL-OFFLINE.md](INSTALL-OFFLINE.md)

## Updates and verification

The hosted updater downloads the release from the update manifest, verifies
SHA-256, creates a backup, preserves `.env` and `storage`, replaces application
files and runs ordered database migrations. The Windows upgrade installer
preserves application data and runs the same migration runner.

Always compare downloads with their adjacent `.sha256` files. The canonical
manifest is:

```text
https://makanlab.tech/downloads/update-manifest.json
```

## Requirements

- Hosting: PHP 8.2+, MySQL or MariaDB, HTTPS recommended
- Windows: 64-bit Windows 10 or Windows 11
- Local AI: Ollama and a separately downloaded model; no model is bundled
- Cloud AI: internet access and an API key from a compatible provider

## Development

The application is framework-light PHP and JavaScript. Database changes belong
in ordered migrations and are applied through the shared `schema_versions`
runner used by clean installation and upgrades.

Useful project documents:

- [Contributing](CONTRIBUTING.md)
- [Security policy](SECURITY.md)
- [Release test checklist](RELEASE-TEST-CHECKLIST.md)
- [Hosting guide](INSTALL-HOSTING.md)
- [Offline guide](INSTALL-OFFLINE.md)

Never commit `.env`, uploads, backups, private keys or signing certificates.

## License and credit

Copyright © 2026 [Makan](https://makanlab.tech/).

CRM Workspace is free software licensed under
[GNU AGPL-3.0-or-later](LICENSE). Modified versions offered over a network must
make their corresponding source available under the same license. The visible
“Powered by Makan — https://makanlab.tech” attribution must be preserved as
described in [NOTICE](NOTICE).

The software is provided without warranty and does not require a paid
activation key. Third-party components keep their respective licenses; see
[THIRD-PARTY-NOTICES.md](THIRD-PARTY-NOTICES.md).
