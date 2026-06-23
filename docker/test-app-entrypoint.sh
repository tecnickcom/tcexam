#!/bin/sh
#============================================================+
# File name   : test-app-entrypoint.sh
# Begin       : 2026-06-22
#
# Description : Entrypoint for the TCExam app-under-test container used by the
#               HTTP controller integration tests. Generates the per-area
#               configuration non-interactively (what the web installer would
#               write) pointing at the test database, then serves via Apache.
#
# (c) Copyright 2004-2026 Nicola Asuni - Tecnick.com LTD
# License: AGPL-3.0-or-later (see LICENSE).
#============================================================+

set -e

APP=/var/www/html
cd "${APP}"

# 1) Materialise the installed config from the shipped defaults (installer step), once.
for area in shared admin public; do
    if [ ! -d "${APP}/${area}/config" ]; then
        cp -a "${APP}/${area}/config.default" "${APP}/${area}/config"
    fi
done

# 2) Point the configuration at the test database + this container's paths, and set a real
#    per-install secret — mirroring install/tce_functions_install.php F_update_config_files().
APP_HOST="${TCEXAM_APP_HOST:-http://tcexam_app}"
SECRET="$(php -r 'echo bin2hex(random_bytes(32));')"

sed -i \
    -e "s|define('K_DATABASE_TYPE', '')|define('K_DATABASE_TYPE', '${TCEXAM_DB_TYPE}')|" \
    -e "s|define('K_DATABASE_HOST', '')|define('K_DATABASE_HOST', '${TCEXAM_DB_HOST}')|" \
    -e "s|define('K_DATABASE_PORT', '')|define('K_DATABASE_PORT', '${TCEXAM_DB_PORT}')|" \
    -e "s|define('K_DATABASE_NAME', '')|define('K_DATABASE_NAME', '${TCEXAM_DB_NAME}')|" \
    -e "s|define('K_DATABASE_USER_NAME', '')|define('K_DATABASE_USER_NAME', '${TCEXAM_DB_USER}')|" \
    -e "s|define('K_DATABASE_USER_PASSWORD', '')|define('K_DATABASE_USER_PASSWORD', '${TCEXAM_DB_PASSWORD}')|" \
    "${APP}/shared/config/tce_db_config.php"

sed -i \
    -e "s|define('K_PATH_HOST', '')|define('K_PATH_HOST', '${APP_HOST}')|" \
    -e "s|define('K_PATH_TCEXAM', '')|define('K_PATH_TCEXAM', '/')|" \
    -e "s|define('K_PATH_MAIN', '')|define('K_PATH_MAIN', '${APP}/')|" \
    "${APP}/shared/config/tce_paths.php"

sed -i \
    -e "s|define('K_RANDOM_SECURITY', '[^']*')|define('K_RANDOM_SECURITY', '${SECRET}')|" \
    -e "s|define('K_BRUTE_FORCE_DELAY_RATIO', [0-9]*)|define('K_BRUTE_FORCE_DELAY_RATIO', 0)|" \
    "${APP}/shared/config/tce_config.php"

# Disable the registration email-confirmation step so integration tests don't require an SMTP server.
sed -i "s|define('K_USRREG_EMAIL_CONFIRM', true)|define('K_USRREG_EMAIL_CONFIRM', false)|" \
    "${APP}/shared/config/tce_user_registration.php"

# 3) Writable runtime paths (DB-backed sessions also need the cache dir for lazy language caches).
chown -R www-data:www-data "${APP}/cache" "${APP}/shared/config" "${APP}/admin/config" "${APP}/public/config" 2>/dev/null || true

echo "[tcexam-app] config generated for ${TCEXAM_DB_TYPE} at ${TCEXAM_DB_HOST}; serving via Apache."
exec apache2-foreground
