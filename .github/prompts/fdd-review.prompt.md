---
name: "FDD Review"
description: "Use when reviewing a Flow Driven Development feature for drift, stage alignment, document consistency, and spec-to-code agreement without restarting the design."
argument-hint: "Describe the feature path or document set to review"
agent: "agent"
---

Review the user's target under Flow Driven Development.

Before doing any work, apply these rules:

1. Treat [document/flow.llm.md](../../document/flow.llm.md) as the primary low-token review contract for Flow Driven Development.
2. Treat [document/flow.md](../../document/flow.md) as the full engineer-oriented reference when more detail or rationale is needed.
3. Treat [copilot-instructions.md](../copilot-instructions.md) as the workspace-level always-on ruleset.
4. Treat [document/spec/.current-spec.md](../../document/spec/.current-spec.md) as the single source of truth for the current target spec.
5. If [document/spec/.current-spec.md](../../document/spec/.current-spec.md) is missing, unreadable, or does not point to a valid spec folder, stop immediately and tell the user to run the appropriate `FDD Focus` command first.
6. If the task involves F3CMS architecture, terminology, process, or feature continuation, treat the files under [document](../../document) as the primary source of truth instead of making generic framework assumptions.
7. After reading [document/spec/.current-spec.md](../../document/spec/.current-spec.md), read the resolved target spec's `history.md` first, then use `plan.md` and `check.md` to determine the current stage and expected next step. Do not restart from `idea.md` unless the documents show the earlier stages are incomplete or a premise has been invalidated.
8. If you inspect runtime validation evidence, prefer the project's existing Docker-based smoke or verification paths over host-only assumptions.

Required review order:

1. First read [document/spec/.current-spec.md](../../document/spec/.current-spec.md).
2. If the pointer file is missing or invalid, stop immediately instead of guessing a spec.
3. State which files you need to read from the resolved target spec.
4. Identify the current flow stage from the resolved `history.md` before assessing drift.
5. Check whether the resolved `history.md`, `plan.md`, `check.md`, and, when needed, `idea.md` are still aligned.
6. Check whether the current code and validation evidence still match the resolved spec documents.
7. Report findings first, ordered by severity, before proposing any edits.
8. If a premise has failed, state the exact rollback or resynchronization scope instead of generically redesigning the feature.

Response expectations:

- Start by confirming which spec folder was resolved from [document/spec/.current-spec.md](../../document/spec/.current-spec.md).
- Then state the current stage and the review scope.
- Findings must be the primary output.
- For each finding, say whether it is document drift, stage drift, validation drift, or code-to-spec mismatch.
- If there are no findings, say that explicitly and mention any remaining documentation gaps or residual risks.
- Do not directly rewrite the spec unless the user explicitly asks for edits after the review.

User review target:

{{input}}