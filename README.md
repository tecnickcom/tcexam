# TCExam

> **Computer-Based Assessment (CBA) software** — create, distribute and manage exams, tests,
> surveys and quizzes, on screen or on paper.

[![Build](https://github.com/tecnickcom/tcexam/actions/workflows/check.yml/badge.svg)](https://github.com/tecnickcom/tcexam/actions/workflows/check.yml)
[![License: AGPL v3](https://img.shields.io/badge/license-AGPL--3.0--or--later-blue.svg)](LICENSE)
[![Sponsor on GitHub](https://img.shields.io/badge/sponsor-github-EA4AAA.svg?logo=githubsponsors&logoColor=white)](https://github.com/sponsors/tecnickcom)

- **Website:** <https://tcexam.org>
- **Source code:** <https://github.com/tecnickcom/tcexam>
- **Support / bug reports:** [GitHub Issues](https://github.com/tecnickcom/tcexam/issues)
- **Security reports:** see [SECURITY.md](SECURITY.md) (do **not** open a public issue)
- **License:** Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD — GNU AGPL-3.0-or-later
  (see [LICENSE](LICENSE))

If this project is useful to you, please consider
[supporting development via GitHub Sponsors](https://github.com/sponsors/tecnickcom).

---

## Contents

- [Description](#description)
- [Key features](#key-features)
- [Quick start](#quick-start)
- [Requirements](#requirements)
- [Dependencies](#dependencies)
- [Build & development](#build--development)
- [Documentation](#documentation)
- [Contributing](#contributing)
- [License](#license)

---

## Description

TCExam is a software solution (CBA - Computer-Based Assessment) to
drastically simplify the evaluation process for schools, universities,
recruiting firms as well as private and public companies, allowing
professors, teachers and examiners to create, distribute and manage exams,
tests, surveys and quizzes.

Compared to the traditional Pen-and-Paper Testing (PPT), TCExam simplifies
the whole process of evaluation reducing costs and improving quality and
reliability of the examinations.

TCExam comes in a variety of languages and is already used all over the
world by top universities, schools, private and public institutions,
independent educators and research centers.

## Key features

* **Flexibility and Configurability**: The system has been designed to offer a
high degree of adaptability to cover a great variety of usage scenarios.
Numerous configuration features allow customizing TCExam to fit all possible
requirements.

* **Free Open Source Software (FOSS)**: TCExam software is distributed with
GNU-AGPLv3 license. Open Source promotes Software reliability and quality by
supporting independent peer review and rapid evolution of the source code.

* **Web-based Architecture**: TCExam is a server-side PHP application that runs on a
standard LAMP-style stack (Linux, Apache, MySQL/MariaDB or PostgreSQL, PHP 8.2+). Once it is
installed on a server, any device on the network can use it through a normal web browser —
independently of the client's operating system and with no additional software or plug-in to
install on the clients.

* **Internationalization (I18N)**: TCExam is language-independent through the
adoption of the UTF-8, Unicode and TMX standards. It supports the
Right-To-Left mode and currently includes translations in 26 different
languages.

* **Accessibility and Usability**: The TCExam web interface uses semantic HTML5 and follows
the Web Content Accessibility Guidelines (WCAG 2.1 level AA) — landmarks, skip links, ARIA
roles, labelled forms and screen-reader cues — to provide equal access and equal opportunity to
people with disabilities, including blindness.

* **Results and Statistics**: TCExam outputs a variety of result pages, enabling
various selection filters and providing numerous statistical indexes.
Results and statistics can then be exported into various formats for filing
or reworking. The test-takers can immediately be informed of the result of
their test, or have it delivered via email.

* **Data Import and Export**: TCExam uses Open Standard protocols for data
filing and interchange: TSV, XML and PDF. Everything is fully documented to
be easily extended or used by external applications. Custom filters can be
added to import data from other systems. Include Optical Mark Recognition
(OMR) system to import users' answers from paper sheets. TCExam supports
several types of remote authentication and single-sign-on protocols: LDAP,
RADIUS, CAS.

* **Multimedia Content**: TCExam uses a common mark-up language to add text
formatting, images, multimedia objects (audio and video) and mathematical
formulas (supports LaTeX and MathML).

* **Unique Tests**: TCExam can simultaneously generate unique tests for
different users by randomly selecting and sorting questions and alternative
answers. This feature drastically reduces or eliminates the chances of
cheating on the test.

* **Paper Testing with Optical Character Recognition (OMR)**: TCExam can
generate printable PDF documents for pen-and-paper testing. The OMR answer
sheet can be scanned and uploaded to TCExam for automatic test importing,
scoring and reporting.

## Quick start

### Run with Docker (recommended for evaluation)

Requires Docker with the Compose plugin.

```sh
make up            # or: docker compose up --build
```

This starts TCExam (Apache + PHP 8.4) on <http://localhost:8080/> together with a MariaDB
database **and installs it automatically** — the container entrypoint runs the non-interactive
installer (`install/install_cli.php`) using the database settings from `docker-compose.yml`, so
there is **no browser install step**. (On first start it also generates the PDF fonts and
translation caches in the background; PDF export becomes available once font generation finishes.)

When the stack is up, open <http://localhost:8080/> and log in under `admin/code/` (default user
`admin` / password `1234` — **change it immediately**).

> The database, PDF fonts, cache and the installed configuration (including the per-instance
> random `K_RANDOM_SECURITY`) are kept in named volumes, so the installed instance survives
> `docker compose down` / `up`. Run `docker compose down -v` to discard everything and start
> fresh. For PostgreSQL, swap the `db` service for a `postgres` image and set
> `TCEXAM_DB_TYPE=POSTGRESQL` / `TCEXAM_DB_PORT=5432` on the `app` service. The interactive web
> installer at <http://localhost:8080/install/> remains available as a fallback.

### Manual install

1. Install PHP **>= 8.2** with the extensions listed under *Requirements*, plus a web server
   (Apache + mod_php recommended — the app ships `.htaccess` access controls) and a database.
2. Install dependencies and build the bundled assets:

   ```sh
   composer install   # also generates the PDF fonts via the post-install hook
   make lang          # pre-build the translation caches (optional; built lazily otherwise)
   ```

3. Point the web server document root at the project directory; make `cache/`, `install/`,
   `admin/backup/` and the `*/config/` parents writable by the web user.
4. Run the installer, then **delete the `install/` folder.** Either open
   <http://your-host/install/> in a browser (the web installer), or run the non-interactive
   command-line installer for scripted/headless setups — set the `TCEXAM_DB_*` / `TCEXAM_PATH_*`
   environment variables and run `php install/install_cli.php` (see the file header for the full
   list). Both generate a unique random `K_RANDOM_SECURITY` for the instance.

Full instructions: [install/README.md](install/README.md) and <https://tcexam.org>.

## Requirements

* PHP **>= 8.2** (tested on 8.2 / 8.3 / 8.4) with: `mysqli` and/or `pgsql`, `gd`, `intl`,
  `bcmath`, `mbstring`, `zip`, `curl`, `xml`, `openssl`, `posix` (Oracle additionally needs `oci8`).
* [Composer](https://getcomposer.org/).
* A database server: MySQL/MariaDB, PostgreSQL or Oracle.
* A web server (Apache + mod_php recommended).

## Dependencies

PHP dependencies are managed with Composer (`composer.json`) and are **no longer bundled** in the
source tree:

| Component | Package | Purpose |
|-----------|---------|---------|
| PDF engine | `tecnickcom/tc-lib-pdf` | results / report / OMR answer-sheet PDFs |
| Barcodes / QR | `tecnickcom/tc-lib-barcode` (via tc-lib-pdf) | OTP QR codes, OMR barcodes |
| PDF fonts | `tecnickcom/tc-lib-pdf-font` | default fonts, generated at install (`make fonts`) |
| Safe file/URL I/O | `tecnickcom/tc-lib-file` (via tc-lib-pdf) | path/host-allow-listed file & URL access |
| Email | `phpmailer/phpmailer` | outgoing mail / report delivery |
| CAS SSO | `apereo/phpcas` | CAS single sign-on |
| RADIUS | `dapphp/radius` | RADIUS authentication |
| Tests | `phpunit/phpunit` *(dev)* | unit test suite |

Front-end JavaScript components remain bundled under `shared/jscripts/`.

### PDF fonts

The previously bundled 27 MB of fonts have been removed. The default fonts are generated from
`tecnickcom/tc-lib-pdf-font` into `vendor/tecnickcom/tc-lib-pdf-font/target/fonts` by `make fonts`
(run by the Composer post-install hook and by the Docker entrypoint).

### Translations (i18n)

26 languages are maintained in a single TMX file
(`shared/config.default/lang/language_tmx.xml`, copied to `shared/config/lang/` on install) and
compiled to per-language PHP caches in `cache/lang/` by `make lang` (or lazily on first request).
See [doc/TRANSLATORS.md](doc/TRANSLATORS.md) for the maintenance workflow and how to contribute a
translation.

## Build & development

A `Makefile` wraps the common tasks — run `make help` for the full list:

```
make deps        # install Composer + lint (mago) dependencies
make lint        # mago lint + static analysis
make test        # run the host unit-test suite (no database needed)
make qa          # lint + test
make fonts       # generate the default PDF fonts
make lang        # build the translation caches
make serve       # local PHP dev server (http://localhost:8080)
make docker      # build the Docker image
make up          # run the full stack via docker compose
make dockertest  # run unit + integration tests against a real DB in Docker
```

### Integration tests against a real database

`make test` runs only the pure-logic **unit** suite on the host. The **integration** suite
(`test/integration/`) exercises the Database Abstraction Layer against a live database — and drives
real controllers over HTTP against an app-under-test container (config generated against the seeded
DB) — inside a disposable Docker Compose environment:

```
make dockertest                  # MySQL/MariaDB (default)
make dockertest DB_TYPE=postgres # PostgreSQL
```

This builds the test runner image, starts a throwaway database seeded from `install/*.sql`, runs
the full suite (unit + integration), copies the reports back to `target/` (`coverage/`, `logs/`,
`report/`), then tears the environment down and exits with the test result. Requires Docker with
the Compose plugin. The integration tests self-skip when run without the Docker environment, so a
plain `phpunit` invocation on the host stays green.

## Documentation

| Document | Purpose |
|----------|---------|
| [doc/INSTALL.md](doc/INSTALL.md) | Installation (Docker and manual) |
| [doc/UPGRADE.md](doc/UPGRADE.md) | Upgrade process and per-version notes |
| [install/README.md](install/README.md) | Full installation manual |
| [doc/LATEX.md](doc/LATEX.md) | Enabling LaTeX formula rendering |
| [doc/TRANSLATORS.md](doc/TRANSLATORS.md) | How to contribute a translation |
| [CONTRIBUTING.md](CONTRIBUTING.md) | How to contribute code |
| [SECURITY.md](SECURITY.md) | Security policy and vulnerability reporting |
| [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) | Community Code of Conduct |

## Contributing

Contributions are welcome — bug reports, fixes, documentation, translations and features.
Please read [CONTRIBUTING.md](CONTRIBUTING.md) and the [Code of Conduct](CODE_OF_CONDUCT.md)
before opening an issue or pull request. For security issues, follow [SECURITY.md](SECURITY.md)
instead of filing a public issue.

## License

TCExam is free software distributed under the **GNU Affero General Public License v3.0 or later
(AGPL-3.0-or-later)**.

Copyright (C) 2004-2026 Nicola Asuni - Tecnick.com LTD. See the [LICENSE](LICENSE) file for the
full text.
