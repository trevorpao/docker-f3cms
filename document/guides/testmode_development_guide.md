# TestMode Development Guide

## Purpose
- Provide a stable development guide for TestMode after the first migration round has completed.
- Give future engineers and LLMs one operational document for where smoke tests live, how they should be named, and how they should be validated.
- Prevent the test system from drifting back into ad hoc scripts, flat naming, or mixed bootstrap responsibilities.

## Primary Readers
- Engineers adding or modifying smoke suites
- LLM-based assistants used for implementation, refactoring, and validation
- Reviewers checking whether new test-related work follows the post-migration contract

## Scope
- TestMode source-of-truth rules
- smoke suite directory and naming rules
- bootstrap and adapter responsibility boundaries
- Docker-first validation contract
- file update expectations under FDD
- guardrails that prevent regression into legacy `scripts/` patterns

## Recommended Background
- [../flow.md](../flow.md)
- [../flow.llm.md](../flow.llm.md)
- [../glossary.md](../glossary.md)
- [../spec/TestMode/optimization.md](../spec/TestMode/optimization.md)

## LLM Reading Contract
- Treat this guide as the operational source of truth for TestMode-related test development after the first migration has stabilized.
- When guidance here overlaps with older TestMode history or transitional wrapper notes, prefer this guide for current implementation behavior.
- Use `document/spec/TestMode/` to understand why a rule exists; use this guide to decide what to do now.

## Core Thesis
- TestMode is no longer a migration-in-progress design sketch. It is now a stable contract for how smoke suites should be structured in this repository.
- Future work should extend the `www/tests/` system, not reopen `www/f3cms/scripts/` or recreate flat legacy naming.
- The goal is not only to keep tests runnable, but to keep the test system maintainable, discoverable, and predictable for both humans and LLMs.

## Canonical Development Rules

### 1. Source Of Truth

- The only supported smoke source of truth is `www/tests/smoke/<domain>/*.php`.
- Legacy `www/f3cms/scripts/*smoke*.php` wrappers are retired and must not be recreated.
- CLI, Lab, shell scripts, and documentation may consume `www/tests/`, but they must not become alternate homes for smoke logic.

### 2. Responsibility Chain

Keep the following responsibility split stable:

- `www/tests/smoke/`
  - Holds the executable suite body.
  - Expresses the contract or scenario being verified.
- `www/tests/bootstrap/`
  - Holds runner-level behavior such as output shape, fixture loading, and exception wrapping.
  - Must remain framework-agnostic where practical.
- `www/tests/adapters/f3cms/`
  - Holds F3CMS-specific bootstrap and runtime wiring.
  - May contain domain-specific helpers only when they are genuinely shared by multiple suites.

Do not collapse these layers back into a single file.

### 3. Naming Rules

- Use domain folders under `www/tests/smoke/`, not long-term flat file placement.
- Use contract-oriented names, not legacy-history names.
- Preferred examples:
  - `www/tests/smoke/workflow_engine/definition.php`
  - `www/tests/smoke/workflow_engine/instance_api.php`
  - `www/tests/smoke/event_rule_engine/basic_or_rule.php`
- Avoid reintroducing `_smoke.php` suffixes as canonical file names.
- Avoid creating new aliases unless a temporary migration step is explicitly planned and documented.

### 4. Validation Rules

- Docker is the baseline validation environment.
- Preferred command shape:

```sh
docker compose exec -T php-fpm php /var/www/tests/smoke/<path>
```

- Host-only execution must not overrule Docker results.
- If validation behavior differs between host and container, treat Docker as the source of truth.

### 5. Entry Integration Rules

- `www/cli/index.php` is a gateway, not a home for suite logic.
- `www/f3cms/modules/Lab/reaction.php` is a diagnostic entry, not a home for suite logic.
- Future integrations may trigger suites indirectly, but they must point to `www/tests/` contracts rather than copying suite code or bootstrap code into new locations.

### 6. Shared Helper Rules

- Only add a helper to `www/tests/adapters/f3cms/` when the behavior is clearly shared across multiple suites.
- Keep suite-specific logic inside the suite unless reuse is already real and stable.
- Do not create helpers merely to avoid a few lines of duplication if the abstraction would hide the contract being tested.

### 7. Drift Prevention Rules

When working on TestMode-related tests:

- do not reintroduce `www/f3cms/scripts/*smoke*.php`
- do not use flat canonical names when a domain folder already exists
- do not move framework bootstrap back into suites
- do not treat wrapper compatibility as a normal steady-state requirement
- do not let documentation keep calling a retired path the main command after canonical paths have stabilized

## Required Reading Order For Future Work

### For Engineers

1. Read [testmode_development_guide.md](testmode_development_guide.md).
2. Read [../glossary.md](../glossary.md) for terminology if naming or boundaries are unclear.
3. Read the relevant target spec under `document/spec/<feature>/` if the suite belongs to a feature contract.
4. Use [../flow.llm.md](../flow.llm.md) and the feature spec files if the task is part of an FDD stage continuation.

### For LLMs

1. Resolve the current spec from `document/spec/.current-spec.md`.
2. Read that spec's `history.md`, then `plan.md`, then `check.md`.
3. Use this guide when the task involves smoke location, naming, validation, or test-system boundaries.
4. Only reopen TestMode structure design if the spec files show a real prerequisite failure.

## File Update Expectations

When a change touches TestMode-related test structure:

- Update the active feature spec files required by the current FDD stage.
- Update `document/spec/TestMode/history.md` when a new rule, decision, or stable continuation point is discovered.
- Update `document/spec/TestMode/optimization.md` when the rule becomes stable enough to outlive the current task.
- Update this guide only when the rule is generic enough for future engineers and LLMs to follow by default.
- Update shared files such as `glossary.md` only for terminology that is truly reusable outside TestMode itself.

## Review Checklist For TestMode Work

Before considering TestMode-related work complete, verify:

1. The suite lives under `www/tests/smoke/<domain>/`.
2. The file name describes the contract, not the migration history.
3. Shared bootstrap and F3CMS wiring remain in `bootstrap/` and `adapters/f3cms/`.
4. Docker validation uses the canonical `www/tests/smoke/` path.
5. No new wrapper or alternate source-of-truth path was introduced.
6. FDD artifact updates were made in the right spec files.

## Related Documents
- [index.md](index.md)
- [overall.md](overall.md)
- [fdd_porting_guide.md](fdd_porting_guide.md)
- [../glossary.md](../glossary.md)
- [../spec/TestMode/optimization.md](../spec/TestMode/optimization.md)

## Status
- Draft v1