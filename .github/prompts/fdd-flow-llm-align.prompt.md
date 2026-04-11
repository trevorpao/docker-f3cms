---
name: "FDD Flow reAlign"
description: "Use when calibrating document/flow.llm.md against document/flow.md, with flow.md as the primary source and flow.llm.md kept minimal, low-token, and execution-oriented."
argument-hint: "Describe how flow.llm.md should be aligned to flow.md"
agent: "agent"
---

Calibrate [document/flow.llm.md](../../document/flow.llm.md) against [document/flow.md](../../document/flow.md), with [document/flow.md](../../document/flow.md) as the primary source.

Before doing any work, apply these rules:

1. Treat [document/flow.md](../../document/flow.md) as the complete, engineer-oriented source of truth for Flow Driven Development.
2. Treat [document/flow.llm.md](../../document/flow.llm.md) as the low-token execution summary for LLM use.
3. Keep the two documents aligned in rules and sequencing, but do not make them the same length or level of explanation.
4. Prefer the smallest edits needed to keep [document/flow.llm.md](../../document/flow.llm.md) accurate, compact, and directly actionable.
5. If a rule belongs only in the full explanation layer, keep it in [document/flow.md](../../document/flow.md) instead of copying it verbatim into [document/flow.llm.md](../../document/flow.llm.md).

Required execution order:

1. First read [document/flow.md](../../document/flow.md).
2. Then read [document/flow.llm.md](../../document/flow.llm.md).
3. Identify missing rules, sequencing drift, wording drift, or over-compression in [document/flow.llm.md](../../document/flow.llm.md).
4. Keep only the minimum rule set needed for LLM execution: stage detection, read order, validation order, optimization entry criteria, minimum outputs, drift handling, and prohibitions.
5. If edits are needed, update [document/flow.llm.md](../../document/flow.llm.md) first and only suggest [document/flow.md](../../document/flow.md) edits when the full source itself is unclear or incomplete.

Response expectations:

- Start by stating whether [document/flow.llm.md](../../document/flow.llm.md) is aligned with [document/flow.md](../../document/flow.md).
- List any missing, distorted, or unnecessary rules.
- Preserve low token cost and scanability.
- Do not expand [document/flow.llm.md](../../document/flow.llm.md) into a second full manual.

User alignment task:

{{input}}