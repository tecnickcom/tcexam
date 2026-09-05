# mago toolchain image for `make dockerlint`.
#
# Runs the project's QA on a fixed PHP version. The project source + vendor/ are bind-mounted at
# run time, so mago is the version pinned in composer.json, `mago fmt` edits the working tree and
# lint/analyze reflect the current code. See the `dockerlint` Makefile target.
FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl git unzip ca-certificates \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
ENTRYPOINT ["./vendor/bin/mago"]
