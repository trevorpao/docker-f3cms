---
name: "FDD Refactor"
description: "Use when a Flow Driven Development feature has confirmed convention drift that requires a bounded refactor without reopening the feature design or expanding implementation scope."
argument-hint: "Describe the feature path and the specific drift or refactor target"
agent: "agent"
---

Execute the user's request as Flow Driven Development convention-refactor work.

Before doing any work, apply these rules:

1. Treat [document/flow.llm.md](../../document/flow.llm.md) as the primary low-token refactor contract for Flow Driven Development.
2. Treat [document/flow.md](../../document/flow.md) as the full engineer-oriented reference when more detail or rationale is needed.
3. Treat [copilot-instructions.md](../copilot-instructions.md) as the workspace-level always-on ruleset.
4. Treat [document/spec/.current-spec.md](../../document/spec/.current-spec.md) as the single source of truth for the current target spec.
5. If [document/spec/.current-spec.md](../../document/spec/.current-spec.md) is missing, unreadable, or does not point to a valid spec folder, stop immediately and tell the user to run the appropriate `FDD Focus` command first.
6. If the task involves F3CMS architecture, terminology, module boundaries, or feature continuation, treat the files under [document](../../document) as the primary source of truth instead of making generic framework assumptions.
7. After reading [document/spec/.current-spec.md](../../document/spec/.current-spec.md), read the resolved target spec's `history.md` first, then use `plan.md` and `check.md` to determine the current stage, current next step, and whether the refactor is actually the right move.
8. Treat convention-refactor as valid only when there is confirmed convention drift such as wrong-layer logic, cross-layer coupling, unstable helper boundaries, or owner-boundary leakage that already affects implementation, review, validation, or the next step.
9. If the feature still has an open runtime gap, acceptance gap, or mainline implementation gap, do not expand into refactor unless the convention drift is actively blocking the current stage.
10. Prefer the smallest bounded refactor that restores owner boundaries and stable interfaces; do not use refactor as a reason to reopen feature design, broaden scope, or perform aesthetic cleanup.
11. If validation is needed, prefer the project's existing Docker-based smoke or verification paths over host-only assumptions.
12. If the task involves database verification, use the database connection credentials defined in `.env` as the default source of truth. Do not guess, hardcode, or substitute credentials when `.env` already provides the validation target.

Required execution order:

1. First read [document/spec/.current-spec.md](../../document/spec/.current-spec.md).
2. If the pointer file is missing or invalid, stop immediately instead of guessing a spec.
3. State which files you need to read from the resolved target spec and which validation environment you will use.
4. Identify the current flow stage from the resolved `history.md` before proposing or making refactor changes.
5. Name the exact drift you are evaluating and classify it as one of: boundary drift, convention drift, code-to-spec mismatch, validation drift, or stage-blocking refactor need.
6. Decide explicitly whether the result is `must refactor now` or `can defer refactor`; do not silently proceed.
7. If the result is `can defer refactor`, stop the refactor path and explain the smaller correct next step instead.
8. If the result is `must refactor now`, define the smallest safe refactor boundary: affected files, caller boundary, expected unchanged behavior, and what is explicitly out of scope.
9. Before editing, state which artifact files should record the refactor reason, scope, and validation plan, typically `history.md` and when needed `plan.md` or `check.md`.
10. Execute the bounded refactor without reopening the feature design.
11. Validate with the narrowest existing executable check, preferring Docker compose services, container paths, smoke scripts, or existing verification routes.
12. After finishing, state whether the resolved target spec's `history.md`, `plan.md`, or `check.md` should be updated further.

Response expectations:

- Start by confirming which spec folder was resolved from [document/spec/.current-spec.md](../../document/spec/.current-spec.md).
- Then state the current stage and the exact drift under review.
- Before editing, explicitly say whether this is `must refactor now` or `can defer refactor`.
- If refactor proceeds, state the minimal refactor boundary and the invariant behavior that must remain unchanged.
- If relevant, mention the Docker-based validation path you will use.
- If relevant, mention that database verification is based on `.env`.
- Do not expand into generic cleanup, opportunistic renaming, or feature redesign.
- Do not skip the artifact update decision; refactor rationale and outcome must not live only in chat.

User refactor task:

{{input}}