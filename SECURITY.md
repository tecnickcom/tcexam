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
  fresh random value, e.g. `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"`. The secret is
  used to generate session IDs, client fingerprints, CSRF tokens and SSO passwords.
- **Restrict file and URL access.** Keep the `K_FILE_ALLOWED_PATHS` / `K_FILE_ALLOWED_HOSTS`
  allow-lists (in `shared/config/tce_paths.php`) as narrow as possible; they constrain the
  `tc-lib-file` safe file/URL access used by the OMR bulk import.
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

## Isolating the administration area

The `/admin/` path manages users, questions, tests and results. Treat it as a separate, internal
service: put server-side controls in front of it so that TCExam's own login is not the only
barrier.

- **Separate entry point.** Serve `/admin/` from a dedicated virtual host or hostname (e.g.
  `admin.exam.example.org`) and deny `/admin/` on the public host. Set `K_PATH_URL_ADMIN` (in
  `shared/config/tce_paths.php`) to the base URL of the admin host so links and PDF back-links
  point to it. Leave `K_COOKIE_DOMAIN` empty: the session cookie is then host-only, and a session
  opened on the public host is not sent to the admin host.
- **Network restriction.** Make the admin host reachable only through a VPN or an
  identity-aware access gateway, and drop all other traffic with a firewall (see below).
- **Second, independent authentication layer.** In front of `/admin/`, require one of the
  following, with credentials separate from TCExam accounts:
  - TLS client certificates (`SSLVerifyClient require` / `ssl_verify_client on`), optionally
    combined with `K_AUTH_SSL_LEVEL` and `K_AUTH_SSLIDS` (`admin/config/tce_auth.php`);
  - a reverse-proxy SSO with MFA (e.g. an OIDC authentication proxy);
  - HTTP Basic authentication over HTTPS, as a minimum.
- **Serve only what is needed.** Under `/admin/`, serve only `admin/code/*.php` and
  `admin/styles/`. Deny `admin/backup/`, `admin/log/`, `admin/doc/`, `admin/config*/` and
  `public/log/`. Prefer a `K_PATH_BACKUP` outside the web root.
- **Rate limiting.** Rate-limit `/admin/` requests and login `POST`s at the web server or proxy
  (`mod_evasive`, `mod_security`, nginx `limit_req`) and feed failed logins to `fail2ban`.
- **Response headers.** On `/admin/`, send `X-Frame-Options: DENY` (or
  `Content-Security-Policy: frame-ancestors 'none'`), `Referrer-Policy: no-referrer`,
  `Strict-Transport-Security`, and `Cache-Control: no-store` on generated PDF, XML, TSV and
  backup downloads.
- **Separate logging.** Write a dedicated access log for the admin host and alert on unexpected
  source addresses and on bursts of `200` responses to `admin/code/*.php`.
- **Process isolation (optional).** Run `/admin/` in its own PHP-FPM pool with its own
  `open_basedir`, `disable_functions` and resource limits. It needs write access to `cache/` and
  `K_PATH_BACKUP`.
- **Split deployment (optional).** On exam-delivery nodes that only serve `public/`, do not
  install `admin/` at all; run administration from an internal node against the same database.

### Restricting network access to `/admin/`

A network firewall (host firewall, cloud security group, perimeter firewall) filters by IP
address and port; it cannot see the URL path. It can only block `/admin/` once the admin area has
its own listener: a separate hostname bound to its own IP address, a separate port, or a
separate node. Path-only rules on a shared hostname need a layer 7 component (reverse proxy,
gateway, WAF or the web server).

**Recommended:** bind the admin listener to a private address or a separate port, reach it only
through a **VPN** or an **identity-aware access gateway** with MFA, and use a **firewall** to drop
everything else.

| Option | How it restricts `/admin/` | Fits | Notes |
|--------|----------------------------|------|-------|
| Split deployment | `admin/` is not installed on Internet-facing nodes | Several nodes, high-stakes exams | Strongest; nothing to misconfigure on the public side |
| VPN (WireGuard, OpenVPN, IPsec, mesh VPN) + firewall | Admin listener on a private or VPN address; the firewall allows the admin port only from the VPN subnet | Most institutions | Simple and robust; revoke access by removing VPN keys |
| Identity-aware access gateway + firewall | The gateway authenticates every request against the organisation's identity provider with MFA; the firewall allows the backend only from the gateway | Remote operators without VPN; existing SSO | Per-user, per-request audit; the backend must accept traffic only from the gateway |
| Campus or office network + perimeter firewall | The firewall allows the admin port only from internal ranges | All operators on site | Exposed to compromised internal hosts; add a second authentication layer |
| Reverse proxy / WAF path rule on a shared hostname | The proxy denies `/admin/` unless the source address is allowed or the request is authenticated | Only when a separate listener is not possible | Normalise paths before matching (case, `//`, `%2e`, `%2f`, `/public/../admin/`); repeat the rule on the backend; the backend must not be reachable directly |
| Web server `Require ip` / nginx `allow` | Per-directory IP allow-list | Baseline, defence in depth | Same limits as a path rule |

Host firewall examples, with the admin listener on port `8443` and the VPN subnet `10.8.0.0/24`:

```sh
# nftables
nft add rule inet filter input tcp dport 8443 ip saddr 10.8.0.0/24 accept
nft add rule inet filter input tcp dport 8443 drop

# ufw
ufw allow from 10.8.0.0/24 to any port 8443 proto tcp
ufw deny 8443/tcp
```

For a cloud security group, allow inbound `8443/tcp` only from the VPN or gateway security group.

### Web server examples

Apache 2.4, admin virtual host (adjust paths, addresses and the authentication method):

```apache
<VirtualHost *:8443>
    ServerName admin.exam.example.org
    DocumentRoot /var/www/tcexam
    SSLEngine on
    # SSLCertificateFile / SSLCertificateKeyFile ...
    SSLCACertificateFile /etc/ssl/tcexam-admin-ca.pem
    SSLVerifyClient require

    <Directory /var/www/tcexam>
        Require all denied
    </Directory>
    <Directory /var/www/tcexam/admin>
        Require ip 10.8.0.0/24
    </Directory>
    <DirectoryMatch "/var/www/tcexam/(admin/backup|admin/log|admin/doc|admin/config|public/log)">
        Require all denied
    </DirectoryMatch>
    <Directory /var/www/tcexam/shared/jscripts>
        Require ip 10.8.0.0/24
    </Directory>
    <Directory /var/www/tcexam/cache>
        Require ip 10.8.0.0/24
    </Directory>

    Header always set X-Frame-Options "DENY"
    Header always set Referrer-Policy "no-referrer"
    Header always set Strict-Transport-Security "max-age=31536000"
</VirtualHost>
```

Apache 2.4, public virtual host:

```apache
<Location "/admin/">
    Require all denied
</Location>
```

nginx:

```nginx
# admin server block
location ^~ /admin/ {
    allow 10.8.0.0/24;
    deny all;
    location ~ ^/admin/(backup|log|doc|config)/ { deny all; }
    # PHP handling for admin/code/*.php ...
}
location ^~ /public/log/ { deny all; }
add_header X-Frame-Options "DENY" always;
add_header Referrer-Policy "no-referrer" always;

# public server block
location ^~ /admin/ { deny all; }
```

The admin pages also load files from `shared/jscripts/`, `shared/` styles and `cache/` (media);
allow these on the admin host.

---

## Contact

| Channel | Details |
|---------|---------|
| Security email | [info@tecnick.com](mailto:info@tecnick.com) |
| Project website | <https://tcexam.org> |
| GitHub repository | <https://github.com/tecnickcom/tcexam> |
