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

### Uploaded files, CSRF and backups

- **Media uploads** (File Manager, media selector, question/answer/subject attachments) reject
  markup and script types (`svgz`, `xml`, `xsl`, `xslt`, `xht`, `xhtml`, `htm`, `html`,
  `shtml`, `mht`, `mhtml`, `js`, `mjs`, `swf`) even when they are listed in
  `K_ALLOWED_UPLOAD_EXTENSIONS`. Server script types (`php*`, `phtml`, `phar`, `cgi`, `pl`, `py`,
  `sh`, ...) are rejected for every upload. File content is checked, inner dots in file names
  become underscores and existing files are never overwritten. File Manager rename follows the
  same rules, including the content check for the new extension; new directory names are
  restricted to `[A-Za-z0-9_-]`. The File Manager still lists existing markup files so that they
  can be deleted.
- **SVG uploads accept only plain, static SVG files.** `'svg'` is in the default
  `K_ALLOWED_UPLOAD_EXTENSIONS`; add it to `admin/config/tce_config.php` to enable SVG uploads on
  an existing install. Each uploaded or renamed SVG file must be well-formed UTF-8 XML with an
  `<svg>` root element and contain only static graphics. The upload is rejected if the file
  contains any of the following:
  - scripts, event handler attributes (`onload`, `onclick`, ...), `<foreignObject>`, links
    (`<a>`), animation elements, or SVG fonts;
  - references to anything other than an element of the same file (`#id`) or an embedded
    PNG, JPEG or GIF image (`data:image/png;base64,...`), including in CSS `url()` and `@import`;
  - entity declarations, an internal DTD subset, or processing instructions such as
    `<?xml-stylesheet?>`;
  - editor-specific elements or attributes (Inkscape, Sodipodi, Adobe Illustrator, Sketch, ...);
  - a declared encoding other than UTF-8, or gzip compression (`.svgz`).

  Save drawings as plain SVG before uploading them:
  - **Inkscape:** *File > Save a Copy...* and choose *Plain SVG* or *Optimized SVG*.
  - **Adobe Illustrator:** *File > Export > Export As...* and choose *SVG*, with *Styling:
    Presentation Attributes* or *Internal CSS*, *Font: Convert to Outlines* and *Images: Embed*.
    Do not use *Save As* with *Preserve Illustrator Editing Capabilities*.
  - **Other editors:** remove editor metadata with an optimizer such as
    [SVGO](https://github.com/svg/svgo) or [Scour](https://github.com/scour-project/scour).

  Existing SVG files are not checked again and are still displayed through `<img>`.
- **Imports** (users, questions, SSL certificates) are read from the PHP temporary upload directory
  and are no longer copied into `cache/`. Keep `'xml'`, `'tsv'`, `'csv'`, `'txt'`, `'pfx'`, `'pem'`
  and `'crt'` in `K_ALLOWED_UPLOAD_EXTENSIONS` as needed by the importers. A custom question
  importer (`admin/code/tce_import_custom.php`) receives the temporary file path, which has no
  file name extension.
- **Previews and `[object]` tags** display images with `<img>`, audio with `<audio>`, video with
  `<video>` and every other type as a download link. Uploaded files are no longer embedded with
  `<object>`.
- **CSRF**: every `POST` request with form data or files to a page that loads the form helpers
  must carry a valid `csrf_token`; otherwise the request stops before the submitted data is
  processed.
- **Backups**: creating, restoring and downloading a backup requires the current user's password.
  After 5 wrong passwords in the same session the backup actions are refused for 15 minutes.
  Accounts that sign in only through an external system (CAS, Shibboleth, HTTP Basic) need a local
  password to use the backup page.
- **`cache/` server configuration.** The shipped `cache/.htaccess` serves the directory as passive
  content: `X-Content-Type-Options: nosniff`, a `Content-Security-Policy` with `sandbox`,
  `Content-Disposition: attachment` for markup types, and no script execution. It requires
  `mod_headers` and `AllowOverride AuthConfig FileInfo` (or `All`). Keep this file when copying an
  old `cache/` folder (step 8 below). For nginx, add the equivalent rules to the server block:

  ```nginx
  location ^~ /cache/ {
      location ~* \.(php[0-9]?|pht|phtml|phar|phps|cgi|pl|py|sh|asp|aspx|jsp)(\.|$) { deny all; }
      location ~* \.(svgz?|xml|xslt?|xht|xhtml|s?html?|mht|mhtml|m?js|swf)$ {
          add_header Content-Disposition "attachment" always;
          add_header X-Content-Type-Options "nosniff" always;
          add_header Content-Security-Policy "default-src 'none'; sandbox" always;
      }
      add_header X-Content-Type-Options "nosniff" always;
      add_header Content-Security-Policy "default-src 'none'; img-src 'self' data:; media-src 'self'; style-src 'unsafe-inline'; sandbox" always;
  }
  ```

  Adjust the `/cache/` prefix if TCExam is installed in a sub-path. `cache/index.php` only
  redirects to the public area, so denying it is harmless.
- **Review existing content.** Files uploaded before this release are not checked again:
  1. List markup and script files in the cache and review or delete them:

     ```sh
     find cache/ -type f \( -iname '*.svg' -o -iname '*.svgz' -o -iname '*.xml' -o -iname '*.xsl*' \
         -o -iname '*.htm*' -o -iname '*.xht*' -o -iname '*.mht*' -o -iname '*.js' -o -iname '*.mjs' \
         -o -iname '*.swf' -o -iname '*.php*' -o -iname '*.phtml' \) -not -path 'cache/lang/*' \
         -not -name index.php
     ```

  2. List file and directory names that contain characters outside `[A-Za-z0-9_.-]`:

     ```sh
     find cache/ -name '*[!A-Za-z0-9_.-]*'
     ```

  3. Delete leftover import files (`*.tsv`, `*.csv`, `*.txt`, `*.xml`) from the `cache/` root.
  4. Search question, answer and subject text for `[object]` tags that reference non-media files:

     ```sql
     SELECT question_id FROM tce_questions
      WHERE question_description LIKE '%[object]%' OR question_explanation LIKE '%[object]%';
     SELECT answer_id FROM tce_answers
      WHERE answer_description LIKE '%[object]%' OR answer_explanation LIKE '%[object]%';
     SELECT subject_id FROM tce_subjects WHERE subject_description LIKE '%[object]%';
     ```

  5. If level 5 accounts could upload `svg` or `xml` files before this release, check
     `admin/backup/` for unexpected backups and consider rotating administrator passwords.

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
