# Contributing to TCExam

Thank you for your interest in contributing to **TCExam**. Contributions of all kinds are
welcome: bug reports, bug fixes, documentation improvements, translations, and new features.

Please take a moment to read this guide before opening an issue or pull request.

> **Pull requests are restricted to project collaborators.** If you are not a collaborator, please
> [open an issue](https://github.com/tecnickcom/tcexam/issues) instead of a pull request,
> describing the bug or feature in detail. A maintainer will review it and take it from there.

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](CODE_OF_CONDUCT.md). By
participating you agree to abide by its terms. Please report unacceptable behaviour to
[info@tecnick.com](mailto:info@tecnick.com).

## Security vulnerabilities

**Do not open a public GitHub issue for security vulnerabilities.** Please follow the
[Security Policy](SECURITY.md) and report them privately.

## Reporting a bug

Before opening an issue:

1. **Check the [Security Policy](SECURITY.md)** — if the bug is a security vulnerability, do not
   file a public issue.
2. **Search [existing issues](https://github.com/tecnickcom/tcexam/issues)** to avoid duplicates.

If no existing issue matches, [open a new one](https://github.com/tecnickcom/tcexam/issues/new)
using the bug-report template and include a **clear title and description**, the **environment**
(TCExam version, PHP version, database engine, web server), **steps to reproduce**, and the
**expected vs. actual behaviour**. A minimal reproduction or failing test case is ideal.

## Submitting a bug fix

> Only project collaborators can open pull requests. If you are not a collaborator, please
> [open an issue](https://github.com/tecnickcom/tcexam/issues/new) describing the bug in detail
> (see [Reporting a bug](#reporting-a-bug)). A maintainer will take it from there.

Collaborators preparing a fix:

1. Create a branch from `main` (e.g. `git checkout -b fix/short-description`).
2. Make your changes, following the existing conventions in the surrounding code.
3. Add or update tests to cover the change.
4. Run the quality-assurance suite locally and ensure it passes (see below).
5. Open a pull request against `main` and fill in the [PR template](.github/pull_request_template.md):
   describe the problem and your solution, and reference the related issue (e.g. `Fixes #123`).

## Proposing a new feature

Before writing any code, **open a Feature Request** on
[GitHub Issues](https://github.com/tecnickcom/tcexam/issues/new) describing the use case and
proposed behaviour, and wait for feedback. This avoids investing time in a direction that may
not be accepted. Once agreed, a collaborator will implement it following the same
branch → code → test → PR workflow as for bug fixes (using a branch named `feature/short-description`).

## Development workflow

The `Makefile` wraps the common development tasks — run `make help` for the full list:

| Command | Description |
|---------|-------------|
| `make deps` | Install Composer and lint dependencies |
| `make lint` | Run mago lint + static analysis |
| `make format` | Auto-format the code with mago |
| `make test` | Run the host unit-test suite (no database needed) |
| `make qa` | Run lint + tests |
| `make dockertest` | Run unit + integration tests against a real database in Docker |
| `make fonts` | Generate the default PDF fonts |
| `make lang` | Build the translation caches |
| `make serve` | Start the built-in PHP development server |
| `make up` | Run the full stack via docker compose |

Before submitting a pull request, please ensure `make qa` passes. For changes that touch the
Database Abstraction Layer or controller logic, also run `make dockertest` (MySQL and, ideally,
`make dockertest DB_TYPE=postgres`).

## Pull request guidelines

> Opening pull requests is restricted to project collaborators. If you are an external
> contributor, please [open an issue](https://github.com/tecnickcom/tcexam/issues/new) describing
> the problem or feature in detail instead.

- **Sign the Contributor License Agreement (CLA).** On your first pull request the CLA Assistant
  bot will comment with a link to sign; the PR cannot be merged until the CLA is signed.
- Target the `main` branch and keep PRs focused — one fix or feature per PR.
- Ensure `make qa` passes locally before opening the PR.
- Cover new code with tests and do not let coverage regress.
- Follow the existing coding conventions; run `make format` then `make lint`.
- Update the relevant documentation when behaviour changes.
- Update the `VERSION` file as described in the PR template (patch / minor / major).
- Be responsive to review feedback.

## Questions?

If you have a question that is not covered here, open a
[GitHub issue](https://github.com/tecnickcom/tcexam/issues) or contact the maintainer at
[info@tecnick.com](mailto:info@tecnick.com).
