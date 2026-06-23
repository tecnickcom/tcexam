# TCExam — Upgrade Guide

> **IMPORTANT — password reset (14.0.0+).** Starting with version 14.0.0 the password
> encryption algorithm changed and **all passwords must be reset**. The default password for
> `admin` is `1234` and its encoding is in `install/db_data.sql`. Create a new administrator
> (level 10) and delete the default `admin` user as soon as possible.

Always [back up your database and files](#general-upgrade-process) before upgrading.

---

## Upgrading to the 17 release

This release modernises TCExam. In addition to the [general process](#general-upgrade-process)
below, note the following one-time changes.

### PHP and dependencies

- **PHP >= 8.2 is now required** (tested on 8.2 / 8.3 / 8.4).
- The previously bundled PHP libraries (TCPDF, PHPMailer, phpCAS, RADIUS) and the 27 MB
  `fonts/` directory have been **removed**. The app now installs them via Composer and generates
  assets on demand:

  ```sh
  composer install     # installs vendor/, generates the PDF fonts (post-install hook)
  make lang            # (optional) pre-builds the translation caches
  ```

  **Without `composer install` the application will not run.**
- Generated artifacts are no longer tracked in git: regenerate the PDF fonts with `make fonts`
  and the language caches with `make lang` (the language caches are also rebuilt lazily on first
  request).
- A `Dockerfile` and `docker-compose.yml` are provided (`make up`). See the **Quick start**
  section of [README.md](../README.md).

### Security-relevant changes

- **`K_RANDOM_SECURITY`.** The shipped value is now a placeholder and the installer generates a
  unique random secret per install. The PDF `?email=` result-access token now **fails closed**
  while the secret is left at the placeholder or the old hardcoded default
  (`'mkTzxf8WwUxwvj6w'`). If `shared/config/tce_general_constants.php` still has the old default,
  set a fresh random value:

  ```sh
  php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
  ```

- **Register-globals emulation removed.** The legacy block in `shared/config/tce_config.php`
  that auto-created `$variables` from posted form fields has been removed; controllers now read
  `$_POST` explicitly. Existing installs keep working unchanged whether or not their live
  `shared/config/tce_config.php` still contains that block. To gain the security benefit (closing
  the variable-injection vector), delete the "parse posted variables" `foreach` loop from your
  `shared/config/tce_config.php`, matching the shipped default.

### New configuration constants

Compare your config files against the shipped `*.default` files and add the new constants:

| File | New constants |
|------|---------------|
| `shared/config/tce_paths.php` | `K_FILE_ALLOWED_PATHS`, `K_FILE_ALLOWED_HOSTS` (allow-lists for `tc-lib-file` safe file/URL access) |
| `shared/config/tce_cas.php` *(CAS only)* | `K_CAS_SERVICE_BASE_URL` |
| `shared/config/tce_pdf.php` | TCPDF-only options removed; `tc-lib-pdf` options added: `K_PDF_UNICODE`, `K_PDF_SUBSET_FONT`, `K_PDF_COMPRESS`, `K_PDF_MODE`, `K_PDF_ALLOWED_PATHS` / `K_PDF_ALLOWED_HOSTS` / `K_PDF_MAX_REMOTE_SIZE` |

The simplest approach is to start from the new `*.default` files and re-apply your previous
customisations.

---

## General upgrade process

This is the general upgrade process for any TCExam version.

1. **Back up** your entire existing database **and** the TCExam folder.

2. **Upgrade the database schema.** If the new main version number (Y) differs from your
   installed main version number (X), execute the matching SQL file for your engine:

   ```
   install/upgrade/mysql/mysql_db_upgrade_XtoY.sql
   install/upgrade/postgresql/postgresql_db_upgrade_XtoY.sql
   install/upgrade/oracle/oracle_db_upgrade_XtoY.sql
   ```

3. **Rename the current folder** (e.g. `/var/www/tcexam` → `/var/www/tcexam.old`).

4. **Extract the new version** into the same location where it was previously installed
   (e.g. `/var/www/tcexam`).

5. **Install the PHP dependencies and generate the assets:**

   ```sh
   composer install
   make lang
   ```

6. **Delete the `tcexam/install` folder.**

7. **Update the configuration files.** Manually edit them to match your previous installation's
   values and add the new constants listed above. The configuration files live in:

   ```
   admin/config/
   public/config/
   shared/config/
   ```

8. **Migrate cached multimedia content** (images, etc.) from your old `cache/` folder to the new
   one.

9. **Set the correct file permissions** for your web server / PHP environment (example for
   Apache running as `www-data`, installation in `/var/www/tcexam`):

   ```sh
   cd /var/www/tcexam
   chown -R www-data:www-data .
   find . -type f -exec chmod 544 {} \;
   find . -type d -exec chmod 755 {} \;
   find cache/ -type d -exec chmod 775 {} \;
   find cache/ -type f -exec chmod 664 {} \;
   ```

10. **Restore custom translations.** If you use custom language files, replace
    `shared/config/lang/language_tmx.xml` and rebuild the caches with `make lang` (or delete the
    `cache/lang/*.php` files).

11. **Verify** that TCExam is working correctly.

12. **Remove the old installation** (`tcexam.old`) after confirming the upgrade succeeded.
