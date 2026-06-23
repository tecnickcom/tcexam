# Makefile
#
# @since       2004-05-26
# @category    Application
# @package     TCExam
# @author      Nicola Asuni <info@tecnick.com>
# @copyright   2004-2026 Nicola Asuni - Tecnick.com LTD
# @license     https://www.gnu.org/licenses/agpl-3.0.html GNU-AGPL v3 (see LICENSE)
# @link        https://github.com/tecnickcom/tcexam
#
# This file is part of TCExam software.
# ----------------------------------------------------------------------------------------------------------------------

SHELL=/bin/bash
.SHELLFLAGS=-o pipefail -c

# Project owner
OWNER=tecnickcom

# Project vendor
VENDOR=${OWNER}

# Project name
PROJECT=tcexam

# Project version
VERSION=$(shell cat VERSION)

# Project release number (packaging build number)
RELEASE=$(shell cat RELEASE)

# Current directory
CURRENTDIR=$(dir $(realpath $(firstword $(MAKEFILE_LIST))))

# Target directory for build/test/report artifacts
TARGETDIR=$(CURRENTDIR)target

# Application source directories that we own (excludes bundled third-party libs).
SRCDIRS=admin public shared index.php

# Default port for the development web server
PORT?=8080

# Docker image tag
DOCKERTAG=$(OWNER)/$(PROJECT):$(VERSION)

# Database engine used by the integration tests: "mysql" (MariaDB) or "postgres".
DB_TYPE?=mysql

# Host uid/gid handed to the test container so the copied-back reports stay user-owned.
HOST_UID=$(shell id -u)
HOST_GID=$(shell id -g)

# docker compose invocation for the integration test stack (base file + per-database override),
# isolated under its own compose project so it never clashes with the dev `make up` stack.
DOCKERCOMPOSETEST=COMPOSE_PROJECT_NAME=$(PROJECT)_test HOST_UID=$(HOST_UID) HOST_GID=$(HOST_GID) \
	docker compose -f docker-compose-test.yml -f docker-compose-test.$(DB_TYPE).yml

# Set default sed inline-replacement option (compatible with both GNU and BSD sed).
SEDINPLACE=-i
ifeq ($(shell uname -s),Darwin)
	SEDINPLACE=-i ''
endif

# PHP binary
PHP=$(shell which php)

# Composer executable (disable APC as a work-around of a known bug)
COMPOSER=$(PHP) -d "apc.enable_cli=0" $(shell which composer)

# phpDocumentor executable file
PHPDOC=$(shell which phpDocumentor)

# --- MAKE TARGETS ---

# Display general help about this command
.PHONY: help
help:
	@echo ""
	@echo "$(PROJECT) Makefile."
	@echo "The following commands are available:"
	@echo ""
	@awk '/^## /{desc=substr($$0,4)} /^\.PHONY:/{if(NF>1) {target=$$2; if(desc) printf "  make %-13s: %s\n",target,desc; desc=""}}' Makefile
	@echo ""

# alias for help target
.PHONY: all
all: help

## Clean the vendor directory and download all dependencies (Composer + mago linter)
.PHONY: deps
deps: ensuretarget
	rm -rf ./vendor/*
	($(COMPOSER) install --no-interaction)
	curl --proto '=https' --tlsv1.2 --silent --show-error --fail --location https://carthage.software/mago.sh | bash -s -- --install-dir=./vendor/bin

## Generate the default PDF fonts into the vendored tc-lib-pdf-font/target/fonts (as in tc-lib-pdf)
.PHONY: fonts
fonts:
	cd vendor/tecnickcom/tc-lib-pdf-font/ && make deps fonts

## Pre-generate and validate the per-language translation caches from the TMX source (plan Stage 6)
.PHONY: lang
lang:
	$(PHP) tools/build_lang_cache.php

## Create missing target directories for test and build artifacts
.PHONY: ensuretarget
ensuretarget:
	@mkdir -p $(TARGETDIR)/test
	@mkdir -p $(TARGETDIR)/report
	@mkdir -p $(TARGETDIR)/doc
	@mkdir -p $(TARGETDIR)/coverage
	@mkdir -p $(TARGETDIR)/logs

## Format the source code with mago
.PHONY: format
format:
	./vendor/bin/mago fmt $(SRCDIRS)

## Statically analyze and lint the source code with mago
# Baselines freeze pre-existing legacy debt so only NEW issues fail the build.
# Regenerate after fixing real issues: add --generate-baseline (or
# --remove-outdated-baseline-entries to prune stale entries). See PLAN_LINT.md.
.PHONY: lint
lint:
	./vendor/bin/mago --config ./mago.src.toml lint --baseline ./mago.lint.baseline.toml
	./vendor/bin/mago --config ./mago.src.toml analyze --baseline ./mago.analyze.baseline.toml
	@if [ -d test ] && ls test/*.php >/dev/null 2>&1; then \
		./vendor/bin/mago --config ./mago.test.toml lint --baseline ./mago.test.lint.baseline.toml; \
		./vendor/bin/mago --config ./mago.test.toml analyze --baseline ./mago.test.analyze.baseline.toml; \
	else \
		echo "Skipping test lint (no test/*.php yet — see plan Stage 5)"; \
	fi

## Run the unit test suite on the host (DB-free; integration tests run via `make dockertest`)
.PHONY: test
test: ensuretarget
	@if [ -f phpunit.xml ] || [ -f phpunit.xml.dist ]; then \
		[ -f phpunit.xml ] || cp phpunit.xml.dist phpunit.xml; \
		./vendor/bin/phpunit --stderr --no-coverage --testsuite unit; \
	else \
		echo "Skipping tests (no phpunit.xml(.dist) yet — see plan Stage 5)"; \
	fi

## Run linting and tests (quality assurance)
.PHONY: qa
qa: lint test

## Generate the source code API documentation with phpDocumentor
.PHONY: doc
doc: ensuretarget
	rm -rf $(TARGETDIR)/doc
	$(PHPDOC) -d ./admin/code,./public/code,./shared/code -t $(TARGETDIR)/doc/

## Start a local PHP development web server on PORT (default 8080)
.PHONY: serve
serve:
	$(PHP) -S localhost:$(PORT) -t .

## Build the Docker image
.PHONY: docker
docker:
	docker build -t $(DOCKERTAG) .

## Start the full stack via docker compose (TCExam app + MariaDB)
.PHONY: up
up:
	docker compose up --build

## Stop the docker compose stack
.PHONY: down
down:
	docker compose down

## Run the full suite (unit + integration) against a real DB in Docker, then clean up (DB_TYPE=mysql|postgres)
.PHONY: dockertest
dockertest: dockertestup dockertestdown

## Build and start the test environment, run the suite, copy reports back, capture the exit code
.PHONY: dockertestup
dockertestup: ensuretarget
	@echo 0 > $(TARGETDIR)/make.exit
	$(DOCKERCOMPOSETEST) down --volumes --remove-orphans || true
	$(DOCKERCOMPOSETEST) build
	$(DOCKERCOMPOSETEST) run --rm tcexam_integration || echo $${?} > $(TARGETDIR)/make.exit
	$(DOCKERCOMPOSETEST) logs --no-color > $(TARGETDIR)/report/docker-compose.log 2>&1 || true

## Tear down the test environment and propagate the captured test exit code
.PHONY: dockertestdown
dockertestdown:
	$(DOCKERCOMPOSETEST) down --rmi local --volumes --remove-orphans || true
	@exit `cat $(TARGETDIR)/make.exit`

## Run mago lint + static analysis in a container (no host mago install required)
.PHONY: dockerlint
dockerlint:
	docker build -f mago.Dockerfile -t $(OWNER)/$(PROJECT)-mago:local .
	docker run --rm -v "$(CURRENTDIR):/app" -w /app --entrypoint sh $(OWNER)/$(PROJECT)-mago:local -c '\
		mago --config mago.src.toml lint; mago --config mago.src.toml analyze; \
		mago --config mago.test.toml lint; mago --config mago.test.toml analyze'

## Delete the vendor and target directories (keeps the app cache/ data)
.PHONY: clean
clean:
	rm -rf ./vendor $(TARGETDIR)

## Increase the version patch number in the VERSION file
.PHONY: versionup
versionup:
	echo ${VERSION} | gawk -F. '{printf("%d.%d.%d\n",$$1,$$2,(($$3+1)));}' > VERSION

## Tag the current git HEAD with the VERSION value
.PHONY: tag
tag:
	git tag -a $(VERSION) -m "Release $(VERSION)" && git push origin --tags
