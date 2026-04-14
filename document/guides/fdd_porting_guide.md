# FDD Porting Guide

## Purpose
- Provide a practical guide for moving the FDD operating model into another project.
- Preserve the parts that make the workflow executable by an LLM, not only understandable by humans.
- Prevent partial migrations where `flow.md` is copied but prompts, spec pointers, or workspace rules are missing.

## Primary Readers
- Tech leads migrating the workflow to a new repository
- Engineers maintaining AI-assisted development standards
- LLM operators who need a stable handoff model in another codebase

## Scope
- portable FDD file set
- required directory structure
- LLM handoff rules
- what can be copied as-is
- what must be rewritten per project
- migration validation checklist

## Recommended Background
- [../flow.md](../flow.md)
- [../flow.llm.md](../flow.llm.md)
- [index.md](index.md)

## Core Thesis
- FDD is not portable if only the stage names are copied.
- To make FDD reusable in another project, the receiving LLM must know:
  - where the canonical process lives
  - how to discover the current target spec
  - which file decides the current stage
  - where validation should run
  - which files must be updated after each round
- The minimum successful migration is therefore a bundle of process files, prompt entry points, and spec-directory conventions.

## What Makes FDD Portable

The portable unit is not one document. It is a contract set.

That contract set has four layers:
- process definition
- workspace-level LLM rules
- prompt entry points
- feature-spec directory contract

If any one of these layers is missing, the next LLM usually falls back to generic implementation behavior.

## Minimum Portable Bundle

Copy these files first when moving FDD into another project.

### A. Process Definition
- [../flow.md](../flow.md)
- [../flow.llm.md](../flow.llm.md)

These two files define the stage model, entry and exit criteria, history-first continuation, drift handling, validation priority, and artifact ownership.

### B. Workspace-Level LLM Rules
- [../../.github/copilot-instructions.md](../../.github/copilot-instructions.md)

This file is what turns FDD from documentation into always-on working behavior. Without it, the LLM may stop reading `history.md` first, stop preferring Docker, or stop updating `plan.md` / `check.md` / `history.md` consistently.

### C. Prompt Entry Points
- [../../.github/prompts/fdd-sprint.prompt.md](../../.github/prompts/fdd-sprint.prompt.md)
- [../../.github/prompts/fdd-review.prompt.md](../../.github/prompts/fdd-review.prompt.md)
- [../../.github/prompts/fdd-retrospective.prompt.md](../../.github/prompts/fdd-retrospective.prompt.md)
- [../../.github/prompts/fdd-focus.prompt.md](../../.github/prompts/fdd-focus.prompt.md)

Recommended to copy as well:
- [../../.github/prompts/fdd-flow-llm-align.prompt.md](../../.github/prompts/fdd-flow-llm-align.prompt.md)

These prompt files expose the workflow as operational entry points instead of leaving it as tribal knowledge.

### D. Spec Directory Contract

Your new project should preserve this structure:

```text
document/
  flow.md
  flow.llm.md
  spec/
    .current-spec.md
    <FeatureA>/
      idea.md
      history.md
      plan.md
      check.md
      optimization.md
```

The `.current-spec.md` pointer is essential. It lets the LLM resolve the current target without guessing which feature the team is discussing.

## What You Can Copy As-Is

These parts are usually portable with no or minimal wording changes:
- stage chain in `flow.md`
- stage判斷順序與 drift handling in `flow.llm.md`
- `history.md` first rule
- `plan.md` / `check.md` / `optimization.md` artifact ownership model
- `.current-spec.md` pointer mechanism
- FDD prompt structure and execution order

Portable means the logic is reusable, not that every noun must remain unchanged.

## What Must Be Rewritten Per Project

Do not blindly copy project-bound assumptions.

### A. Validation Environment Rules

Rewrite all environment-specific rules inside [../../.github/copilot-instructions.md](../../.github/copilot-instructions.md) and the FDD prompts:
- Docker service names
- runtime language assumptions
- smoke script paths
- local vs container precedence
- build or test entry commands

If the new project is not PHP or does not use Docker, this section must be rewritten immediately.

### B. Database Verification Rules

Keep the pattern, rewrite the specifics:
- if the new project uses `.env` as source of truth, keep that rule
- if it uses another secret or config source, replace the wording everywhere consistently

The important part is not `.env` itself. The important part is that the LLM must know there is one canonical source for DB verification.

### C. Project Terminology

Replace repository-specific nouns such as:
- F3CMS
- module / reaction / feed / outfit
- project-specific folder names

The target project should define its own primary architecture vocabulary, and the instructions should point to that vocabulary source.

### D. Document Source-of-Truth Rules

Keep this rule shape, but rewrite the path targets:
- where architecture truth lives
- where feature specs live
- where operational guides live

In this repository, that source is `document/`. In another project, it may be `docs/`, `architecture/`, or `spec/`.

## How To Make The Next LLM Behave Correctly

The next LLM does not need only documents. It needs execution bias.

To preserve that bias, the migrated project must teach the LLM five concrete things.

### 1. Where to Read First

The LLM must be instructed to read in this order:
1. current spec pointer
2. target `history.md`
3. target `plan.md`
4. target `check.md`
5. shared process or architecture documents
6. `idea.md` only if earlier documents are incomplete or invalidated

If this order is not preserved, the LLM tends to restart design from `idea.md` and loses continuity.

### 2. How to Determine Stage

The migrated rules must state explicitly:
- `history.md` is the stage handoff file
- `plan.md` and `check.md` are support files for execution and acceptance
- `idea.md` is not the default continuation entry point

### 3. What Counts as the Smallest Valid Next Step

The LLM must be told not to expand scope by default.

Required behavior:
- identify current stage first
- detect drift before changing direction
- only do the smallest valid next step unless the user broadens scope

### 4. Which Environment Wins During Validation

The LLM must know:
- whether Docker is the baseline
- whether host execution is allowed
- where smoke tests live
- how to interpret mismatched results

Without this, the same task may produce different conclusions between sessions.

### 5. Which Files Must Be Updated After Each Round

The migrated rules should force explicit artifact updates.

Typical mapping:
- `idea` round: update `idea.md`
- `(discuss)` round: update `history.md`
- `plan` round: update `plan.md` and often `check.md`
- `(done)` round: update implementation plus `history.md`
- `check` round: update `check.md` and `history.md`
- `(Optimization)` round: update shared docs and `optimization.md`

If this is not written down, progress drifts back into chat-only state.

## Recommended Migration Steps

### Step 1: Copy the Core Files

Start with:
- `flow.md`
- `flow.llm.md`
- `.github/copilot-instructions.md`
- `.github/prompts/fdd-*.prompt.md`

If you are porting from this repository, you can also use the helper script directly:

```sh
./bin/port_fdd.sh /path/to/new-project
```

This script copies the current FDD bundle, creates `document/spec/`, and initializes a placeholder `document/spec/.current-spec.md` so the receiving project has the minimum handoff structure before you rewrite project-bound rules.

### Step 2: Rewrite Project-Bound Rules

Before using FDD in the new project, replace:
- project name
- doc root path
- build and validation paths
- database verification source
- architecture terms

### Step 3: Create the Spec Skeleton

Establish:
- `document/spec/.current-spec.md`
- at least one real feature folder
- the five base spec files in that feature folder

Do not wait until later to add `history.md`. The workflow breaks quickly without it.

### Step 4: Validate the Prompt Contract

Test these prompt behaviors in the new project:
- `FDD Focus`
- `FDD Sprint`
- `FDD Review`
- `FDD Retrospective`

The test should confirm that the LLM:
- resolves `.current-spec.md`
- reads `history.md` first
- states current stage correctly
- chooses the smallest next step
- mentions the correct validation environment

### Step 5: Run a Pilot Feature

Do not declare the migration complete after copying files.

Use one real feature and verify that the LLM can:
- initialize the spec chain
- continue from `history.md`
- detect drift
- update the correct artifacts
- avoid skipping directly to implementation

## Migration Validation Checklist

Use this checklist before declaring the migration stable.

- [ ] The new project has a canonical process file equivalent to `flow.md`
- [ ] The new project has a low-token execution file equivalent to `flow.llm.md`
- [ ] Workspace instructions tell the LLM where architecture truth lives
- [ ] Workspace instructions tell the LLM how validation should run
- [ ] Workspace instructions tell the LLM how DB verification should resolve source of truth
- [ ] Prompt entry points exist for focus, sprint, review, and retrospective
- [ ] The spec root contains `.current-spec.md`
- [ ] Feature folders use `idea.md`, `history.md`, `plan.md`, `check.md`, `optimization.md`
- [ ] The LLM reads `history.md` before `plan.md` and `check.md`
- [ ] The LLM does not restart from `idea.md` unless required
- [ ] The LLM reports drift before changing direction
- [ ] The LLM updates the expected artifact files after each round

## Common Failure Modes

### Only copying `flow.md`

This preserves concepts but not behavior. The LLM still acts generically because no prompt or workspace rule enforces the workflow.

### Copying prompts without `.current-spec.md`

This removes target resolution. The LLM can no longer reliably know which feature to continue.

### Keeping old project nouns

This creates false certainty. The LLM will use stale folder names, stale validation paths, or stale architecture terms.

### Keeping Docker-first wording in a non-Docker project

This produces wrong validation behavior and wrong regression judgments.

### Treating `idea.md` as the universal entry point

This destroys continuity and reopens already-settled decisions.

## Suggested First Prompt In A New Project

After migration, use one calibration task before normal work begins.

Suggested prompt:

```text
請依 FDD 承接目前 feature。
先讀 current spec pointer，再讀 history.md、plan.md、check.md。
先回答目前 stage、上一輪完成項與最小下一步。
若發現 drift，先指出 drift，不要直接重做設計。
若需要驗證，請使用本專案規定的驗證環境。
```

If the LLM answers in the correct order and references the correct files, the migration is usually operational.

## Suggested Follow-Up Reading
- [../flow.md](../flow.md)
- [../flow.llm.md](../flow.llm.md)
- [../../.github/prompts/fdd-sprint.prompt.md](../../.github/prompts/fdd-sprint.prompt.md)
- [../../.github/prompts/fdd-focus.prompt.md](../../.github/prompts/fdd-focus.prompt.md)
- [../spec/prompts.md](../spec/prompts.md)