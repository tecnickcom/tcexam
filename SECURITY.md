# Security Policy

This document describes the security policy for **TCExam** — a web-based, open-source
Computer-Based Assessment (CBA) application.

---

## Supported Versions

Security fixes are applied only to the **latest stable release** on the `main` branch.

We strongly recommend always running the latest release and keeping the Composer
dependencies up to date.

---

## Reporting a Vulnerability

**Please do not open a public GitHub issue for security vulnerabilities.**

If you discover a security vulnerability — or suspect one — follow responsible disclosure:

1. **Email** the maintainer directly at **[info@tecnick.com](mailto:info@tecnick.com)** with the
   subject line:
   `[SECURITY] TCExam – <brief description>`
2. Include as much detail as possible (see [What to Include](#what-to-include) below).
3. You will receive an acknowledgement as soon as possible.
4. We will work on a fix or mitigation as promptly as the complexity of the issue allows.

If you do not receive a timely response, please follow up by replying to the same email thread.

---

## What to Include

A high-quality report helps us triage and fix issues faster. Please provide:

- **Description** — a clear summary of the vulnerability and its potential impact.
- **Affected component** — which page, function, or feature is involved
  (e.g. `admin/code/tce_edit_user.php`, the OMR import, the PDF result token).
- **Steps to reproduce** — a minimal, self-contained sequence of requests or a script that
  demonstrates the issue.
- **Expected vs. actual behaviour** — what you expected to happen and what actually happened.
- **Environment** — TCExam version (`VERSION` file), PHP version, database engine, web server.
- **CVE / CWE reference** (optional) — if you have already identified a relevant classification.
- **Suggested fix** (optional) — a patch or proposed mitigation if you have one.

---

## Security Best Practices for Administrators

TCExam is a self-hosted application that handles personal data, credentials and exam results.
The deploying administrator is responsible for the security of the installation. We recommend:

- **Complete and lock down the installer.** Run `install/install.php` once, then **delete the
  entire `install/` directory** — it must not remain reachable on a production server.
- **Change the default credentials immediately.** The shipped admin account is
  `admin` / `1234`. Create a new level-10 administrator and remove the default `admin` user
  as soon as possible.
- **Set a unique `K_RANDOM_SECURITY`.** The installer generates a per-install random secret; if
  you are migrating an old configuration, replace any placeholder or historical default with a
  fresh random value, e.g. `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`. The PDF
  result-access token fails closed while this secret is left unconfigured.
- **Restrict file and URL access.** Keep the `K_FILE_ALLOWED_PATHS` / `K_FILE_ALLOWED_HOSTS`
  allow-lists (in `shared/config/tce_paths.php`) as narrow as possible — they constrain the
  `tc-lib-file` safe file/URL access used by OMR import and report delivery.
- **Serve over HTTPS** and keep the secure-cookie and session settings enabled so credentials
  and session tokens are never sent in clear text.
- **Apply the shipped access controls.** Run on Apache + `mod_php` so the bundled `.htaccess`
  rules take effect, or replicate equivalent access restrictions on other web servers
  (especially for `cache/`, `*/config/` and `admin/backup/`).
- **Set least-privilege file permissions** for the web-server user (see
  [doc/UPGRADE.md](doc/UPGRADE.md) for the recommended `chmod`/`chown` commands).
- **Keep dependencies up to date.** Run `composer update` regularly and monitor advisories with
  `composer audit`. Pin versions in production with `composer.lock` and review changes on every
  update.

---

## Contact

| Channel | Details |
|---------|---------|
| Security email | [info@tecnick.com](mailto:info@tecnick.com) |
| Project website | <https://tcexam.org> |
| GitHub repository | <https://github.com/tecnickcom/tcexam> |
