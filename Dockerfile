# TCExam runtime image — Apache + PHP (plan Stage 7).
#
# Build:  docker build -t tecnickcom/tcexam .   (or: make docker)
# Run:    docker compose up --build             (or: make up)
#
# The image bundles the application and its Composer dependencies. The bundled-at-install
# artifacts (PDF fonts and translation caches) are generated on first container start by the
# entrypoint and cached in volumes — see docker/entrypoint.sh and docker-compose.yml.

# PHP 8.4 = the newest version in the project's CI matrix (composer requires >=8.2; CI tests
# 8.2/8.3/8.4). PHP 8.5 is intentionally not used yet — it is outside the tested matrix. Apache +
# mod_php is used (not nginx) because the app ships .htaccess access controls (install/,
# admin/backup/) that nginx would silently ignore.
FROM php:8.4-apache

# --- Build/runtime tools ----------------------------------------------------------------------
# make + git/unzip/curl are needed by the entrypoint's `make fonts` / `make lang` steps
# (tc-lib-pdf-font downloads and builds the default font set; Composer prefers dist zips).
RUN apt-get update \
    && apt-get install -y --no-install-recommends make git unzip curl ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# --- PHP extensions ---------------------------------------------------------------------------
# install-php-extensions resolves the system libraries each extension needs (mlocati/php-ext).
# The app's DAL talks to MySQL/MariaDB (mysqli) and PostgreSQL (pgsql) directly; gd/intl/bcmath/
# mbstring/zip back the PDF, i18n, scoring and import/export code. curl/xml/openssl/posix ship
# with the base image. Oracle (oci8) is intentionally omitted — see the docs for enabling it.
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
        mysqli \
        pgsql \
        gd \
        intl \
        bcmath \
        mbstring \
        zip \
        opcache

# Production-leaning php.ini defaults (expose_php Off, error logging, upload/memory headroom).
COPY docker/tcexam-php.ini /usr/local/etc/php/conf.d/tcexam.ini

# --- Apache -----------------------------------------------------------------------------------
RUN a2enmod rewrite headers
COPY docker/tcexam-apache.conf /etc/apache2/conf-available/tcexam.conf
RUN a2enconf tcexam

# --- Composer ---------------------------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first (better layer caching). Skip Composer scripts here: the PDF-font
# generation hook downloads large external sources and is deferred to the entrypoint (volume-cached).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist \
    && composer clear-cache

# --- Application --------------------------------------------------------------------------------
COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

# Entrypoint generates fonts + language caches on first run, fixes runtime permissions, then
# hands off to Apache.
COPY docker/entrypoint.sh /usr/local/bin/tcexam-entrypoint
RUN chmod +x /usr/local/bin/tcexam-entrypoint \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["tcexam-entrypoint"]
CMD ["apache2-foreground"]
