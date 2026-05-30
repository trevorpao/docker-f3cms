---
name: "FDD Commit"
description: "Use when preparing a commit for the current Flow Driven Development spec by reading the active current spec, deriving a commit message from the current spec and changed files, asking for explicit user confirmation, then performing a non-interactive git commit."
argument-hint: "Describe any commit emphasis, exclusions, or commit-scope notes"
agent: "agent"
---

Prepare and execute a git commit for the current Flow Driven Development spec.

Before doing any work, apply these rules:

1. Treat [document/flow.llm.md](../../document/flow.llm.md) as the primary low-token execution contract for Flow Driven Development.
2. Treat [document/flow.md](../../document/flow.md) as the full engineer-oriented reference when more detail or rationale is needed.
3. Treat [copilot-instructions.md](../copilot-instructions.md) as the workspace-level always-on ruleset.
4. Treat [document/spec/.current-spec.md](../../document/spec/.current-spec.md) as the single source of truth for the current target spec.
5. If [document/spec/.current-spec.md](../../document/spec/.current-spec.md) is missing, unreadable, or does not point to a valid spec folder, stop immediately and tell the user to run the appropriate `FDD Focus` command first.
6. After reading [document/spec/.current-spec.md](../../document/spec/.current-spec.md), read the resolved target spec's `history.md` first, then use `plan.md` and `check.md` to understand the current stage, latest completed slice, and intended commit scope.
7. Inspect the current git working tree before proposing a commit message. Use the changed files, the resolved current spec, and the latest documented completion state together; do not invent a commit message from the spec alone.
8. Treat commit scope as "current spec changes only". If the working tree contains unrelated changes, do not silently include them. Call out the unrelated files and ask whether to exclude them or intentionally include them.
9. Do not amend an existing commit unless the user explicitly asks for amend behavior.
10. Use non-interactive git commands only. Do not use the interactive git console.
11. When committing, prefer staging only the files that belong to the confirmed commit scope. Do not use a blanket destructive cleanup command.

Required execution order:

1. First read [document/spec/.current-spec.md](../../document/spec/.current-spec.md).
2. If the pointer file is missing or invalid, stop immediately instead of guessing a spec.
3. Confirm which spec folder was resolved.
4. Read the resolved target spec's `history.md`, `plan.md`, and `check.md`.
5. Inspect the git working tree, including changed and untracked files, to identify which files belong to the current spec commit candidate.
6. If there are unrelated dirty files, stop and ask the user whether to exclude them or include them in this commit.
7. Propose a commit message grounded in the resolved current spec, the latest documented completion state, and the actual file changes.
8. Show the proposed commit scope and proposed commit message, then ask for explicit confirmation.
9. After the user confirms, stage only the confirmed files and run a non-interactive `git commit`.
10. Report the resulting commit hash and final subject line.

Response expectations:

- Start by confirming which spec folder was resolved from [document/spec/.current-spec.md](../../document/spec/.current-spec.md).
- Then summarize the current stage and the latest completed slice from `history.md`, `plan.md`, and `check.md`.
- Clearly separate `in-scope changed files` from `unrelated dirty files`, if any.
- Propose one primary commit message, and when helpful one shorter alternative.
- Do not run `git commit` until the user explicitly confirms.
- After confirmation, commit directly instead of restating the plan.

User commit task:

{{input}}