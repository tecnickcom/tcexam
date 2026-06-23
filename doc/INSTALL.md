# TCExam — Installation

There are two supported ways to run TCExam: a Docker stack (best for evaluation and quick
local runs) and a manual install on your own PHP/web/database stack (for production).

> Already running an older version? See [UPGRADE.md](UPGRADE.md) instead.

## Quick start (Docker)

Requires Docker with the Compose plugin:

```sh
make up            # or: docker compose up --build
```

The stack **installs itself automatically** — the container entrypoint runs the non-interactive
installer (`install/install_cli.php`) using the database settings from `docker-compose.yml`, so
there is no browser install step. When it is up, open <http://localhost:8080/> and log in under
`admin/code/` with the default account `admin` / `1234` (**change it immediately**).

The installer is idempotent and the configuration is kept in a named volume, so the installed
instance survives `docker compose down` / `up`; use `docker compose down -v` to start fresh. The
interactive web installer at <http://localhost:8080/install/> remains available as a fallback.

See the **Quick start** section of [README.md](../README.md) for more details (PostgreSQL, font
generation and persistence notes).

## Manual install

1. **Install the prerequisites:**
   - PHP **>= 8.2** with the extensions: `mysqli` and/or `pgsql`, `gd`, `intl`, `bcmath`,
     `mbstring`, `zip`, `curl`, `xml`, `openssl`, `posix` (Oracle additionally needs `oci8`).
   - [Composer](https://getcomposer.org/).
   - A database server: MySQL/MariaDB, PostgreSQL or Oracle.
   - A web server (Apache + `mod_php` recommended — the app ships `.htaccess` access controls).

2. **Install dependencies and build the bundled assets:**

   ```sh
   composer install     # also generates the PDF fonts via the post-install hook
   make lang            # optional: pre-build the translation caches (built lazily otherwise)
   ```

3. **Set up the filesystem.** Point the web-server document root at the project directory, and
   make `cache/`, `install/`, `admin/backup/` and the `*/config/` parents writable by the web
   user.

4. **Run the installer.** Run `install/install.php` (open `http://your-host/install/` in a
   browser). The installer creates the configuration files and generates a unique random
   `K_RANDOM_SECURITY` for the instance. The full step-by-step manual is
   [install/README.md](../install/README.md).

5. **Secure the installation:**
   - **Delete the `install/` directory** once installation is complete.
   - Log in under `admin/code/` with the default account `admin` / `1234`, create a new level-10
     administrator and **remove the default `admin` user immediately**.

   See [SECURITY.md](../SECURITY.md) for the full hardening checklist.

## Further documentation

- Detailed manual: [install/README.md](../install/README.md)
- Project website: <https://tcexam.org>
- Upgrade notes: [UPGRADE.md](UPGRADE.md)
