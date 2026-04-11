---
name: "FDD Use Spec"
description: "Use when switching the current Flow Driven Development target spec to any document/spec/<Spec> folder by name or path, then continuing work from that spec's history, plan, and check files."
argument-hint: "Provide the spec folder name or path, then the task to continue"
agent: "agent"
---

Switch the current target spec to the user-specified spec under [document/spec](../../document/spec), then continue the user's request under that feature context.

Before doing any work, apply these rules:

1. Treat [document/flow.llm.md](../../document/flow.llm.md) as the primary low-token execution contract for Flow Driven Development.
2. Treat [document/flow.md](../../document/flow.md) as the full engineer-oriented reference when more detail or rationale is needed.
3. Treat [copilot-instructions.md](../copilot-instructions.md) as the workspace-level always-on ruleset.
4. Create or update [document/spec/.current-spec.md](../../document/spec/.current-spec.md) only after confirming that the requested target spec folder actually exists.
5. The user input may be either a spec folder name like `WorkflowEngine` or a path like `document/spec/WorkflowEngine`.
6. If the requested spec folder does not exist, stop immediately and report that exact invalid target instead of guessing or choosing another spec.
7. After resolving a valid target spec, treat its `history.md`, `plan.md`, and `check.md` as the primary continuation set for the task.
8. Do not restart from `idea.md` unless the spec documents show the earlier stages are incomplete or a premise has been invalidated.
9. If validation is needed, prefer the project's existing Docker-based verification routes.

Required execution order:

1. First parse the user's requested spec name or path.
2. Resolve it to a folder under [document/spec](../../document/spec).
3. If the resolved spec folder does not exist, stop immediately and report the invalid target.
4. If it exists, create or update [document/spec/.current-spec.md](../../document/spec/.current-spec.md) so it points to that spec.
5. Confirm which spec folder is now active.
6. Read the resolved spec's `history.md` first.
7. Then read its `plan.md` and `check.md`.
8. Identify the current flow stage, what the last round actually completed, and the smallest valid next step.
9. Execute the user's request within that resolved spec scope.

Response expectations:

- Start by confirming which spec folder was requested and which one was activated.
- If the target is invalid, stop and say so clearly.
- If the target is valid, summarize the current stage and handoff point before doing any broader work.
- Keep the work within the resolved feature scope unless the user explicitly expands it.
- After finishing, state whether the resolved spec's `history.md`, `plan.md`, or `check.md` should be updated.

Spec switch and task:

{{input}}