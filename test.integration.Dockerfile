# Integration-test runner image for `make dockertest`.
#
# A slim PHP CLI image with the database drivers and the dev dependencies (PHPUnit). It runs the
# full test suite — including the DAL integration tests — against the database service defined in
# the accompanying docker-compose-test.*.yml override, writing reports to the bind-mounted
# target/ directory so they are copied back to the host. See docker/test-entrypoint.sh.
#
# PHP 8.4 matches the top of the project's CI matrix (composer requires >=8.2).
FROM php:8.4-cli

# git/unzip let Composer fetch and extract packages; everything else is provided by extensions.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

# Database drivers (mysqli + pgsql) and the extensions the shared code loads at include time;
# pcov provides fast line coverage (no Xdebug needed in the container).
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
        mysqli \
        pgsql \
        gd \
        intl \
        bcmath \
        mbstring \
        zip \
        pcov

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /workspace

# Install dependencies first for better layer caching. Dev requires (PHPUnit) are kept; skip the
# Composer scripts — the PDF-font build hook is irrelevant to these tests and needs network access.
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts \
    && composer clear-cache

# Application + test sources (honouring .dockerignore: vendor/, target/ and config are excluded).
COPY . .
RUN composer dump-autoload --optimize

COPY docker/test-entrypoint.sh /usr/local/bin/tcexam-test-entrypoint
RUN chmod +x /usr/local/bin/tcexam-test-entrypoint

ENTRYPOINT ["tcexam-test-entrypoint"]
