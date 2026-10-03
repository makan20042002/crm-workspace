# Security policy

## Supported releases

Security fixes are applied to the latest published release of CRM Workspace.
Administrators should keep their installation current and retain tested
backups before applying an update.

## Reporting a vulnerability

Please do not publish an exploitable vulnerability, private customer data,
credentials or access tokens in a public issue. Contact Makan privately using
the contact method published at <https://makanlab.tech/> and include:

- the affected version and package mode;
- clear reproduction steps;
- the expected and observed result;
- the potential impact;
- a minimal proof of concept with secrets removed.

Please allow reasonable time for investigation and a coordinated fix before
public disclosure.

## Deployment responsibilities

Operators are responsible for HTTPS, database permissions, firewall rules,
credential storage, backups, access control and the security of their server
or Windows host. Never publish `.env`, backup archives, private uploads or
code-signing keys.
