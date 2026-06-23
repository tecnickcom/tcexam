# TCExam — Installation manual

This folder contains the TCExam installation files. To install, run `install.php` (the web
installer) as described below.

> **IMPORTANT:** delete this entire `install/` directory once installation is complete — it must
> not remain reachable on a production server.

This is the full installation manual. For a condensed version and a ready-to-run Docker stack, see
the project [README](../README.md) and [doc/INSTALL.md](../doc/INSTALL.md). Upgrading an existing
installation? Read [doc/UPGRADE.md](../doc/UPGRADE.md) first.

## Contents

- [System requirements](#system-requirements)
- [Installation](#installation)
- [Manual installation](#manual-installation)
- [Docker (alternative)](#docker-alternative)
- [Upgrading](#upgrading)
- [Configuration reference](#configuration-reference)

## System requirements

The instructions below assume a working web server. These are the minimum requirements:

- **PHP 8.2 or newer** (tested on 8.2, 8.3 and 8.4 — [php.net](https://www.php.net/)), with the
  following extensions enabled: `mysqli` and/or `pgsql`, `gd`, `intl`, `bcmath`, `mbstring`,
  `zip`, `curl`, `xml`, `openssl` and `posix` (Oracle additionally needs `oci8`).
- **Composer** ([getcomposer.org](https://getcomposer.org/)) to install the PHP dependencies — the
  bundled libraries (PDF engine, mailer, CAS, RADIUS) are **no longer shipped** in the source tree
  and must be installed with `composer install`.
- A **DBMS**: MySQL/MariaDB ([mariadb.org](https://mariadb.org/)),
  PostgreSQL ([postgresql.org](https://www.postgresql.org/)) or Oracle.
- A **web server**. Apache with `mod_php` is recommended because TCExam ships `.htaccess`
  access-control rules; on other web servers you must replicate those restrictions manually
  (see [Access and security](#5-access-and-security-post-installation)).

### Optional feature requirements

- **LaTeX formula rendering** requires **LaTeX** ([latex-project.org](https://www.latex-project.org/);
  on Windows, [MiKTeX](https://miktex.org/)) and **ImageMagick** ([imagemagick.org](https://imagemagick.org/)).
  See [doc/LATEX.md](../doc/LATEX.md) for configuration.
- **Optical Mark Recognition (OMR)** — importing answers from scanned paper sheets — requires the
  `zbarimg` tool from [ZBar](https://github.com/mchehab/zbar).

On Debian/Ubuntu, the PHP runtime and optional tooling can be installed with (adjust the PHP
version to the one your distribution provides):

```sh
sudo apt-get install apache2 libapache2-mod-php \
  php-cli php-mysql php-pgsql php-gd php-intl php-bcmath php-mbstring php-zip php-curl php-xml \
  composer \
  imagemagick texlive-latex-extra zbar-tools
```

### Preparing the database

The web installer can create the database and its tables for you, provided the database user has
the necessary privileges. On hosted environments where the database must be created in advance,
note the following settings before you start:

- The **database name** (some hosts pre-assign it).
- The **database host** (usually `localhost` on a single server; check with your provider on
  shared hosting).
- A database **username** and **password**. TCExam requires a non-blank password.

## Installation

Installing TCExam for the first time involves getting the files, installing the PHP dependencies,
setting filesystem permissions, and running the web installer.

### 1. Get the files

Download the latest stable release from [GitHub](https://github.com/tecnickcom/tcexam) and extract
it into a directory under your web-server document root (for example `/var/www/tcexam`).

### 2. Install dependencies and build assets

From the project directory, install the PHP dependencies with Composer:

```sh
composer install
```

The Composer post-install hook generates the default PDF fonts into
`vendor/tecnickcom/tc-lib-pdf-font/target/fonts/` (you can regenerate them later with
`make fonts`). Optionally, pre-build the translation caches — otherwise they are built lazily on
first use:

```sh
make lang
```

**TCExam will not run without `composer install`.**

### 3. Set filesystem permissions

Make the runtime directories writable by the web-server user. The web installer also needs to
write the configuration files, so the `*/config/` parent directories must be writable during
installation. The writable locations are: `cache/`, `admin/backup/`, and the `admin/config/`,
`public/config/` and `shared/config/` directories. Final, locked-down permissions are applied
after installation (see [Access and security](#5-access-and-security-post-installation)).

### 4. Run the web installer

Point your browser at the installation script, e.g.
`https://www.yoursite.com/install/install.php` (or
`https://www.yoursite.com/tcexam_folder/install/install.php`). Fill in the form and press
**INSTALL**.

> **Warning:** the installer creates the database tables and the default data, and will overwrite
> the data of any previous installation using the same database and table prefix. Back up first if
> you are reinstalling.

The form fields are:

| Field | Description |
|-------|-------------|
| **db type** | the DBMS type (default *MySQL*) |
| **db host** | the database host (usually *localhost*) |
| **db port** | the database port (usually *3306* for MySQL, *5432* for PostgreSQL) |
| **db user** | the database user (often *root* for MySQL, *postgres* for PostgreSQL) |
| **db password** | that user's password |
| **db name** | the database name (usually *tcexam*); change it only if other copies of TCExam share the same server |
| **tables prefix** | prefix added to the table names (usually *tce_*) |
| **host URL** | the domain of your site (e.g. *https://www.yoursite.com*) |
| **relative URL** | the path from the web-server root to the TCExam files (usually */* or */tcexam_folder/*) |
| **TCExam path** | the full server path to the installation folder (e.g. */var/www/tcexam/*) |
| **TCExam port** | the connection port (usually *80* for HTTP or *443* for HTTPS) |

The installer writes the configuration files and generates a unique, random `K_RANDOM_SECURITY`
value for this instance. If the installation succeeds, continue to
[Access and security](#5-access-and-security-post-installation). If it fails, use the
[manual installation](#manual-installation) procedure instead.

> The installer never drops an existing database automatically (tick **Drop Existing Database?**
> only for a deliberate clean reinstall). Leaving **Create New Database?** ticked is safe: if the
> database already exists or the database user lacks the `CREATE` privilege — as on many managed
> and Docker setups — the installer falls back to the existing database.

### 4b. Alternative: non-interactive command-line installer

For scripted, headless or container installs you can skip the web form and run the command-line
installer instead. It reads its settings from environment variables, is idempotent (it preserves
an existing configuration and never overwrites existing data), and is what the Docker entrypoint
runs automatically:

```sh
TCEXAM_DB_TYPE=MYSQL \
TCEXAM_DB_HOST=localhost TCEXAM_DB_PORT=3306 \
TCEXAM_DB_NAME=tcexam TCEXAM_DB_USER=tcexam TCEXAM_DB_PASSWORD=secret \
TCEXAM_PATH_HOST=https://www.yoursite.com TCEXAM_PATH_TCEXAM=/ \
TCEXAM_PATH_MAIN=/var/www/tcexam/ TCEXAM_STANDARD_PORT=443 \
php install/install_cli.php
```

Set `TCEXAM_DB_CREATE=1` to have it attempt to create the database first, and pass `--reconfig`
to rewrite the configuration files when they already exist. See the header comment of
`install/install_cli.php` for the complete list of variables and defaults.

### 5. Access and security (post-installation)

Once installed, you can reach the administration area at
`https://www.yoursite.com/tcexam_folder/admin/code/` using the default account:

- name: **admin**
- password: **1234**

Immediately complete the following hardening steps (see [SECURITY.md](../SECURITY.md) for the full
checklist):

- **Delete the entire `install/` directory** — it must not remain reachable on a production server
  (e.g. `rm -fR /var/www/tcexam/install`).
- **Change the default credentials.** Create a new level-10 administrator and remove the default
  `admin` user as soon as possible.
- **Confirm `K_RANDOM_SECURITY` is set** to a unique random value in
  `shared/config/tce_general_constants.php` (the installer does this automatically; the PDF
  result-access token fails closed while it is left at the placeholder).
- **Serve over HTTPS** so credentials and session tokens are never sent in clear text.
- **Apply least-privilege file permissions.** On a POSIX system (example for Apache running as
  `www-data`, installed in `/var/www/tcexam`):

  ```sh
  cd /var/www/tcexam
  chown -R www-data:www-data .
  find . -type f -exec chmod 544 {} \;
  find . -type d -exec chmod 755 {} \;
  find cache/ -type d -exec chmod 775 {} \;
  find cache/ -type f -exec chmod 664 {} \;
  find admin/backup/ -type d -exec chmod 775 {} \;
  ```

- **Protect the sensitive directories.** The shipped `.htaccess` rules deny web access to
  `*/config/`, `cache/` and `admin/backup/` on Apache; replicate equivalent restrictions on other
  web servers. You may move the backup folder out of the document root and point the
  `K_PATH_BACKUP` constant in `shared/config/tce_paths.php` at the new location.

## Manual installation

If the web installer cannot be used, you can configure TCExam by hand: edit the configuration
files and load the database schema manually.

### Configuration files

First, copy each `config.default` directory to `config` inside `admin`, `public` and `shared`,
then edit the essential parameters:

- **shared/config/tce_db_config.php**
  - `K_DATABASE_TYPE` — database type (e.g. *MYSQL*, *POSTGRESQL* or *ORACLE*)
  - `K_DATABASE_HOST` — database host (usually *localhost*)
  - `K_DATABASE_PORT` — database port
  - `K_DATABASE_NAME` — database name
  - `K_DATABASE_USER_NAME` — database user
  - `K_DATABASE_USER_PASSWORD` — database password
  - `K_TABLE_PREFIX` — table-name prefix (usually *tce_*)
- **shared/config/tce_paths.php**
  - `K_PATH_HOST` — the domain of your site (e.g. *https://www.yoursite.com*)
  - `K_PATH_TCEXAM` — relative path from the web-server root (usually */* or */tcexam_folder/*)
  - `K_PATH_MAIN` — full server path to the installation folder (e.g. */var/www/tcexam/*)
  - `K_STANDARD_PORT` — HTTP/HTTPS port (usually 80 or 443)

You must also set a unique `K_RANDOM_SECURITY` in `shared/config/tce_general_constants.php`.
Generate one with:

```sh
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

### Database installation

The `install/` folder contains the SQL files with the database structure and data:

- **mysql_db_structure.sql** — MySQL/MariaDB database structure.
- **postgresql_db_structure.sql** — PostgreSQL database structure.
- **oracle_db_structure.sql** — Oracle database structure.
- **db_data.sql** — default database data.

To use a different table prefix, search-and-replace `tce_` in these files: replace
`CREATE TABLE tce_` with `CREATE TABLE yourprefix` in the structure file, and `INSERT INTO tce_`
with `INSERT INTO yourprefix` in `db_data.sql`.

Load the files with your DBMS client. For MySQL/MariaDB:

```sh
mysql -u root -p
mysql> CREATE DATABASE tcexam;
mysql> quit
shell> mysql -u root -p tcexam < install/mysql_db_structure.sql
shell> mysql -u root -p tcexam < install/db_data.sql
```

Alternatively, use a database management tool (phpMyAdmin, phpPgAdmin, pgAdmin, etc.) to create the
database and run the SQL files. Then complete the
[post-installation security steps](#5-access-and-security-post-installation) above.

## Docker (alternative)

For evaluation or a quick local run, a Docker stack is provided. From the project directory:

```sh
make up            # or: docker compose up --build
```

This starts TCExam together with a MariaDB database and **installs it automatically**: the
container entrypoint runs the non-interactive installer (`install_cli.php`) using the database
settings from `docker-compose.yml`, so no browser install step is required. When the stack is up,
open `http://localhost:8080/` and sign in under `admin/code/` (`admin` / `1234`). The installed
configuration is kept in a named volume and survives `docker compose down` / `up`. See the
[README](../README.md) for details.

## Upgrading

The upgrade process can change between releases. Always back up your database and files first, then
follow [doc/UPGRADE.md](../doc/UPGRADE.md), which includes the per-version notes and the
schema-migration SQL files under `install/upgrade/`.

## Configuration reference

After installation, TCExam runs in a basic configuration. Additional features (email, CAS, LDAP,
RADIUS, Shibboleth, LaTeX, PDF and SSL options) are enabled by editing the configuration files. The
files are self-explanatory and documented inline:

- **shared/config/** — main configuration:
  - **lang/language_tmx.xml** — TMX file with all translations
  - **tce_config.php** — general system configuration
  - **tce_db_config.php** — database configuration
  - **tce_paths.php** — file and folder paths
  - **tce_general_constants.php** — general constants (including `K_RANDOM_SECURITY`)
  - **tce_email_config.php** — outgoing email configuration
  - **tce_pdf.php** — PDF document format and options
  - **tce_latex.php** — LaTeX rendering configuration
  - **tce_mime.php** — MIME associations for file extensions
  - **tce_cas.php**, **tce_ldap.php**, **tce_radius.php**, **tce_shibboleth.php**,
    **tce_httpbasic.php** — authentication / single-sign-on backends
  - **tce_ssl.php** — SSL client-certificate options
  - **tce_user_registration.php** — self-registration configuration
- **admin/config/** — administration area:
  - **tce_auth.php** — access levels for the administration modules
  - **tce_config.php** — administration panel configuration
- **public/config/** — public area:
  - **tce_auth.php** — access levels for the public modules
  - **tce_config.php** — public area configuration

For support, visit the project website at [tcexam.org](https://tcexam.org).
