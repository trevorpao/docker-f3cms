# Smoke Result Tier Rerun Guide

## Purpose
- Provide a stable shared guide for Smoke S Layer result shape, tier vocabulary, and rerun rules after the first SmokeTestOptimization round.
- Give future engineers and LLMs one operational document for how smoke output should be described, how validation cost should be classified, and how DB-backed suites should remain rerunnable.
- Prevent smoke work from drifting into inconsistent output fields, misleading tier claims, or ad hoc cleanup behavior.

## Primary Readers
- Engineers adding or modifying smoke suites
- Reviewers checking whether a smoke is honest about its coverage and rerun safety
- LLM-based assistants used for smoke implementation, review, and documentation

## Scope
- smoke result vocabulary
- smoke tier classification rules
- DB-backed rerun and cleanup rules
- output honesty rules for human reading and future aggregation
- guide-level defaults for new smoke work

## Recommended Background
- [../glossary.md](../glossary.md)
- [smoke_s_layer_guide.md](smoke_s_layer_guide.md)
- [../reference/smoke_s_layer_reference.md](../reference/smoke_s_layer_reference.md)
- [../spec/SmokeTestOptimization/optimization.md](../spec/SmokeTestOptimization/optimization.md)

## LLM Reading Contract
- Treat this guide as the shared default for how smoke suites should describe result shape, tier, and rerun behavior after the first contract has stabilized.
- When feature-local smoke files omit these rules, prefer this guide rather than inventing ad hoc result fields or cleanup patterns.
- Use the active spec to understand feature-specific assertions; use this guide to decide how to present and classify them.

## Core Thesis
- Smoke is not only about being executable; it must also be honest about what it covers and safe to rerun.
- Tier labels exist to reveal coverage cost and runtime depth, not to make a smoke look stronger than it is.
- DB-backed smoke must be rerunnable by contract, not only by manual cleanup.

## Canonical Tier Rules

### 1. Pure Logic Smoke

- Use this tier when the suite only exercises deterministic logic and does not require fixture files, framework bootstrap side effects, or live DB writes.
- This tier is the cheapest validation shape and should be used when the contract can truly be checked without runtime persistence.
- Do not label a suite `pure_logic` if it depends on live F3 state, DB rows, or route wiring.

### 2. Fixture-driven Smoke

- Use this tier when the suite depends on stable fixture data, fixture files, or pre-shaped input sets, but still does not need a live DB-backed write path as the main contract.
- This tier is appropriate when the contract depends on representative structured input but not on real persistence behavior.
- Do not label a suite `fixture_driven` if its main risk is transaction behavior, owner-side persistence, or runtime DB state transitions.

### 3. DB-backed Smoke

- Use this tier when the contract depends on real DB reads or writes, real owner-side persistence, or runtime behavior that only becomes meaningful with a live smoke database.
- This tier is the most expensive and must use the isolated `SMOKE_DB_NAME` contract.
- If a suite inserts or updates rows to validate a real owner/request contract, it should be explicit that it is `db_backed`.

## Tier Honesty Rules

- Choose the cheapest tier that still tells the truth about what is being validated.
- Do not label a smoke with a cheaper tier to make validation look lighter than it really is.
- Do not imply that a `pure_logic` or `fixture_driven` smoke covers the full runtime contract when the true risk is in DB-backed behavior.
- Tier is descriptive metadata, not a prestige label.

## Result Contract Rules

Every smoke should be able to express, directly or through its surrounding envelope, at least the following context:

- `case`
  - a stable identifier for the scenario or contract being validated
- `domain`
  - the owning module or feature domain
- `tier`
  - one of `pure_logic`, `fixture_driven`, or `db_backed`
- `status`
  - a stable success or failure state
- `message`
  - a short summary that a human can read without reopening the suite

Recommended interpretation:

- `case` should tell the reader which exact contract failed, not only which file ran
- `domain` should follow module or feature ownership, not temporary migration naming
- `tier` should match the tier rules above
- `status` should be stable enough for future aggregation
- `message` should summarize the outcome, not dump an entire stack trace by default

## Result Honesty Rules

- Result output should help a reviewer understand both what was tested and why a failure matters.
- If a smoke only verifies a subset of a broader owner contract, the result message should not overclaim full coverage.
- When a smoke relies on guard behavior, the result should make it clear whether the run exercised a positive contract or a deliberate block case.

## Rerun Contract Rules

### 1. Stable Naming

- DB-backed smoke must use stable naming such as a fixed prefix, slug, or case key for its owned test rows.
- The naming should make it possible to locate and clean prior rows deterministically.
- Do not rely on random data as the only way to avoid collisions.

### 2. Cleanup Ownership

- Cleanup belongs to the suite contract, not to manual operator intervention.
- A DB-backed smoke should either clean previous owned rows before writing, clean after completion, or do both when required by the scenario.
- Cleanup must be limited to the rows owned by that smoke contract.

### 3. Isolation Rules

- Rerun safety assumes the isolated `SMOKE_DB_NAME` contract remains in force.
- Do not normalize rerun behavior around the primary configured database.
- If a suite cannot safely rerun under the isolated smoke DB contract, treat that as a contract defect to fix.

### 4. Failure Cleanup Expectations

- A suite should be written so that a failed run does not force engineers to perform manual DB surgery before rerunning the same case.
- When full cleanup after failure is impractical, the suite must still use stable owned-row naming so that the next run can clean its own prior residue first.

## Review Checklist For Result Tier Rerun Work

Before considering a smoke stable, verify:

1. The suite declares or clearly implies the correct tier.
2. The result output is honest about what contract was actually covered.
3. DB-backed suites use stable owned-row naming.
4. Cleanup behavior is owned by the suite, not by manual operator steps.
5. The suite remains rerunnable under the isolated `SMOKE_DB_NAME` contract.
6. The chosen tier does not understate the real runtime cost or risk.

## File Update Expectations

When a new stable rule about result shape, tiering, or rerun behavior is discovered:

- Update the active feature spec files required by the current FDD stage.
- Update `document/spec/SmokeTestOptimization/optimization.md` when the rule is stable enough to outlive the current task.
- Update this guide when the rule is generic enough to become a shared default for future smoke work.
- Update `glossary.md` only for terms that are likely to be reused across multiple features.

## Related Documents
- [index.md](index.md)
- [smoke_s_layer_guide.md](smoke_s_layer_guide.md)
- [../reference/smoke_s_layer_reference.md](../reference/smoke_s_layer_reference.md)
- [../glossary.md](../glossary.md)
- [../spec/SmokeTestOptimization/optimization.md](../spec/SmokeTestOptimization/optimization.md)

## Status
- Draft v1