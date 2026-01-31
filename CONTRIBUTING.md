# CONTRIBUTING

Thanks for your interest in contributing. This project aims to be practical, approachable, and production-minded - without process bloat.

### What we value
- Small, reviewable pull requests
- Clear documentation alongside code changes
- Tests for behaviour, not implementation detail
- Decisions captured once (so we don’t re-argue them forever)

---

## Quick start (local)
This project is designed to run without Docker.

1) Clone the repo
2) Install dependencies
- `composer install`

3) Configure environment
- `cp .env.example .env`
- Default database should be SQLite for the simplest path

4) Generate key and migrate
- `php artisan key:generate`
- `php artisan migrate`

5) Run locally
- `php artisan serve`

If you hit issues, please open an issue with:
- PHP version
- OS
- exact error output
- steps to reproduce

---

## How we work (PRDs and ADRs)
We keep documentation lightweight but intentional.

### PRDs - Product Requirements Documents
Write a PRD when you introduce or materially change user-facing behaviour.

Location:
- `/docs/prds/`

A PRD should include:
- Problem statement
- Target user and context
- User journey (happy path + key edge cases)
- Success criteria
- Scope (in / out)
- Rollout notes (migrations, flags, backwards compatibility)

### ADRs - Architecture Decision Records
Write an ADR when a decision has long-term architectural impact.

Location:
- `/docs/adrs/`

An ADR should include:
- Context
- Decision
- Options considered
- Consequences

ADRs should be short. Prefer one ADR over repeated debates in PR comments.

---

## Coding standards
- Follow existing conventions in the codebase
- Keep functions small and readable
- Prefer explicitness over cleverness
- Avoid introducing new dependencies unless clearly justified

### Formatting
Use the project formatter before opening a PR. If the repo uses Laravel Pint, run it locally.

---

## Testing
We expect tests for changes that affect behaviour.

Guidelines:
- Unit tests for validation and pure logic (section schemas, permission checks)
- Feature tests for workflows (publish flow, tenant boundaries, exports)
- Keep E2E tests minimal and high-value (smoke flows only)

A PR that changes behaviour without tests should explain why.

---

## Pull request checklist
Before opening a PR, please ensure:
- The change has a clear purpose and a small surface area
- Docs updated if behaviour changes (README, PRD, ADR, or both)
- Tests added or updated
- CI passes

PR description should include:
- What changed
- Why it changed
- How to test

---

## Issues and feature requests
When opening an issue, include:
- What you expected
- What happened
- Steps to reproduce
- Screenshots where relevant

For feature requests:
- Describe the user problem first
- Include examples from real agency workflows if possible
- Be clear about what is in scope and what is not

---

## Security
If you believe you’ve found a security issue:
- Please do not open a public issue with exploit details
- Follow the repo’s security policy (see `SECURITY.md`), or open a private report if available

---

## Licence and contributions
By contributing, you agree that your contributions will be licensed under the project’s licence.

---

## Thank you
Every issue, PR, doc fix, or discussion helps make this useful in the real world.