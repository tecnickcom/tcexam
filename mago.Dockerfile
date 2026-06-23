# mago toolchain image for `make dockerlint`.
#
# Provides the mago linter/formatter/analyzer in a container so contributors can run the project's
# QA locally without installing mago on the host (it is installed exactly as `make deps` does it).
# The project source + vendor/ are bind-mounted at run time, so `mago fmt` edits the working tree
# and lint/analyze reflect the current code. See the `dockerlint` Makefile target.
FROM php:8.4-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl git unzip ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Install mago the same way the Makefile `deps` target does (pinned to a stable install dir).
RUN curl --proto '=https' --tlsv1.2 --silent --show-error --fail --location https://carthage.software/mago.sh \
    | bash -s -- --install-dir=/usr/local/bin

WORKDIR /app
ENTRYPOINT ["mago"]
