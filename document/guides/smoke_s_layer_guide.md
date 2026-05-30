# Smoke S Layer Guide

## Purpose
- Provide a stable development guide for the FORKS Smoke S Layer after the first SmokeTestOptimization round has completed.
- Give future engineers and LLMs one operational document for where the official smoke entry lives, how module-owned smoke should be structured, and which runtime guards are mandatory.
- Prevent smoke work from drifting back into ad hoc scripts, generic helper overreach, or mixed ownership between `www/tests/` and module code.

## Primary Readers
- Engineers adding or modifying module-owned smoke cases
- LLM-based assistants used for FDD implementation, review, and closeout
- Reviewers checking whether Smoke S Layer work still respects FORKS owner boundaries

## Scope
- Smoke S Layer source-of-truth rules
- responsibility split between `www/tests/index.php`, `www/f3cms/libs/Smoke.php`, and module-owned `smoke.php`
- path grammar and autodiscovery rules
- runtime guard and DB isolation rules
- Docker-first validation contract
- file update expectations under FDD

## Recommended Background
- [../flow.md](../flow.md)
- [../flow.llm.md](../flow.llm.md)
- [../glossary.md](../glossary.md)
- [../spec/SmokeTestOptimization/optimization.md](../spec/SmokeTestOptimization/optimization.md)

## LLM Reading Contract
- Treat this guide as the operational source of truth for FORKS Smoke S Layer work after the first implementation has stabilized.
- When guidance here overlaps with older feature-history wording, prefer this guide for current implementation behavior.
- Use `document/spec/SmokeTestOptimization/` to understand why a rule exists; use this guide to decide what to do now.

## Core Thesis
- Smoke is now a formal FORKS S Layer contract, not an ad hoc script pattern.
- The stable official entry is `www/tests/index.php {path}`, not `www/cli/index.php` and not per-feature temporary scripts.
- Entity test semantics remain module-owned through `www/f3cms/modules/{Entity}/smoke.php`; shared code must stop at runtime and dispatch boundaries.

## Canonical Development Rules

### 1. Source Of Truth

- The only supported Smoke S Layer entry is `www/tests/index.php {path}`.
- The stable path grammar is `<module>/<surface>/<contract>`.
- `www/cli/index.php` may remain a general CLI entry, but it must not become the official smoke dispatcher.
- Do not create alternate source-of-truth smoke entrypoints under `scripts/`, `bin/`, or module-specific ad hoc runners.

### 2. Responsibility Chain

Keep the following split stable:

- `www/tests/index.php`
  - Holds path parsing, module discovery, runtime guard checks, smoke bootstrap, and result emission.
  - Must not hold entity business logic.
- `www/f3cms/libs/Smoke.php`
  - Holds base dispatch behavior and the minimal shared runtime contract.
  - Must not own entity truth, payload semantics, or cross-module business coordination.
- `www/f3cms/modules/{Entity}/smoke.php`
  - Holds module-owned smoke cases, surface / contract mapping, and assertion semantics for that entity.
  - Should remain the owner of smoke-specific business meaning.
- `www/tests/adapters/f3cms/`
  - Holds F3CMS runtime wiring such as bootstrap and smoke DB switching.
  - May carry shared adapter helpers only when reuse is already real.

Do not collapse these layers back into a single file or a generic global helper.

### 3. Path And Discovery Rules

- `path` must contain exactly three segments: `<module>/<surface>/<contract>`.
- `module` is normalized to the F3CMS module owner, for example `mobile -> Mobile` and `phonebook -> Phonebook`.
- The discovered owner file is always `www/f3cms/modules/<Module>/smoke.php`.
- `surface` and `contract` must be resolved by explicit rules inside the module-owned smoke class.
- Do not add fuzzy matching, alias fallback, or cross-module fallback without a new spec decision.

### 4. Runtime Guard Rules

- Smoke execution requires `APP_ENV=develop`.
- Smoke execution requires `ALLOW_SMOKE_WRITE=1`.
- DB-backed smoke execution requires `SMOKE_DB_NAME`.
- `SMOKE_DB_NAME` must differ from the primary configured `db_name`.
- These guards are part of the formal contract, not optional convenience checks.

### 5. DB Isolation Rules

- DB-backed smoke must run against a dedicated smoke database, not the primary configured database.
- DB switching must happen during bootstrap before the first real DB initialization.
- If a smoke needs write access, it must rely on the isolated smoke DB contract rather than temporary table cleanup in the primary DB.

### 6. Validation Rules

- Docker is the baseline validation environment.
- Preferred command shape:

```sh
docker compose exec -e APP_ENV=develop -e ALLOW_SMOKE_WRITE=1 -e SMOKE_DB_NAME=<isolated_db> php-fpm php /var/www/tests/index.php <module>/<surface>/<contract>
```

- Guard-case validation should also use Docker and the same official entrypoint.
- Host-only execution must not overrule Docker results.

### 7. Owner-Boundary Rules

- Keep entity-specific smoke logic inside the owning module.
- Do not move entity assertions or scenario setup into `libs/Smoke.php` just to reduce duplication.
- Only add adapter helpers when more than one smoke suite clearly shares the same F3CMS runtime wiring.
- Do not introduce project-specific shortcut entrypoints when the existing owner-side path already works.

### 8. Drift Prevention Rules

When working on Smoke S Layer code:

- do not reintroduce official smoke execution through `www/cli/index.php`
- do not move module-owned smoke semantics into `libs/Smoke.php`
- do not bypass `APP_ENV=develop`, `ALLOW_SMOKE_WRITE=1`, or `SMOKE_DB_NAME`
- do not reuse primary `db_name` for DB-backed smoke
- do not let feature specs become the only place where stable smoke rules are documented

## Required Reading Order For Future Work

### For Engineers

1. Read [smoke_s_layer_guide.md](smoke_s_layer_guide.md).
2. Read [../glossary.md](../glossary.md) if ownership or terminology is unclear.
3. Read the active feature spec under `document/spec/<feature>/` if the work belongs to a specific smoke contract.
4. Use [../flow.llm.md](../flow.llm.md) and the current feature spec files if the task is part of an FDD continuation.

### For LLMs

1. Resolve the current spec from `document/spec/.current-spec.md`.
2. Read that spec's `history.md`, then `plan.md`, then `check.md`, then `optimization.md` when present.
3. Use this guide when the task involves smoke ownership, entrypoint rules, path grammar, or runtime guards.
4. Only reopen Smoke S Layer design if the current spec files show a real prerequisite failure.

## File Update Expectations

When a change touches the Smoke S Layer structure:

- Update the active feature spec files required by the current FDD stage.
- Update `document/spec/SmokeTestOptimization/history.md` when a new stable continuation point or rule is discovered.
- Update `document/spec/SmokeTestOptimization/optimization.md` when a rule becomes stable enough to outlive the current task.
- Update this guide only when the rule is generic enough for future engineers and LLMs to follow by default.
- Update shared files such as `glossary.md` and reference docs only when the rule is reusable outside the current feature narrative.

## Review Checklist For Smoke S Layer Work

Before considering Smoke S Layer work complete, verify:

1. The official execution path is still `www/tests/index.php <module>/<surface>/<contract>`.
2. Module-owned smoke still lives under `www/f3cms/modules/{Entity}/smoke.php`.
3. `www/f3cms/libs/Smoke.php` still stops at base runtime and dispatch behavior.
4. Docker validation uses the official entrypoint and an isolated `SMOKE_DB_NAME`.
5. No new alternate source-of-truth smoke entry was introduced.
6. FDD artifact updates were made in the right spec files.

## Related Documents
- [index.md](index.md)
- [../glossary.md](../glossary.md)
- [smoke_result_tier_rerun_guide.md](smoke_result_tier_rerun_guide.md)
- [../reference/smoke_s_layer_reference.md](../reference/smoke_s_layer_reference.md)
- [../spec/SmokeTestOptimization/optimization.md](../spec/SmokeTestOptimization/optimization.md)

## Status
- Draft v1