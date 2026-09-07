---
paths:
  - '**'
---

# General

## Follow Spec-Driven Development workflow
Store features in `.specs/NNN-feature-name/` with `spec.md` defining behavior and acceptance criteria (no implementation details), `plan.md` defining the technical approach, and `tasks.md` defining the ordered checklist. Before implementation, read `.specs/product.md`, then the active feature's `spec.md`, `plan.md`, and `tasks.md`, in that order; follow all three feature documents together and never implement from `tasks.md` alone.
Create SDD documents from `.specs/templates/`. Implement only behavior in the active specification; do not add speculative functionality or future abstractions. When expected behavior changes, update `spec.md` first, then update affected `plan.md` and `tasks.md` before implementation.
A bug that violates behavior already specified needs no new specification: fix the implementation and add or update appropriate tests.

## Create incremental Conventional Commits
Commit incrementally throughout implementation using concise English Conventional Commit messages; prefer `feat`, `fix`, `test`, `refactor`, `docs`, and `chore`. Each commit must be a coherent, working unit: do not commit every `tasks.md` checkbox or wait until an entire feature is complete, and keep tests with their implementation when they represent the same behavior.
Before committing, run and pass relevant automated tests and applicable frontend checks, review the diff, and exclude unrelated changes. Never intentionally commit broken code.
Do not rewrite existing Git history, force push, discard unrelated changes, or amend commits not created during the current work.

## Use TDD for behavioral and business logic
For specified behavior, follow TDD when practical: write or update a test from the active specification's behavior and acceptance criteria, confirm it fails for the expected reason, implement the minimum solution, rerun relevant tests, and refactor only while they stay green. Prioritize this cycle for business rules, validation, state transitions, application actions, observable backend behavior, regressions, and bug fixes; reproduce bugs with a failing test before fixing when practical.
Prefer feature tests for observable application behavior and unit tests for domain logic that benefits from isolation. Do not require tests for trivial framework scaffolding, simple migrations, styling-only changes, or implementation details without meaningful behavioral coverage.
A behavioral task is not complete while its relevant tests fail.
