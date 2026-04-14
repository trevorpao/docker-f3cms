# FDD idea.md Writing Guide

## Purpose
- Define how to write a high-quality `idea.md` as the first document in Flow Driven Development.
- Help SA, SD, and DBA produce a requirement basis that is strong enough for later discuss, plan, implementation, and review work.
- Reduce downstream drift caused by vague scope, page-first thinking, weak data assumptions, or missing constraints.

## Primary Readers
- SA
- SD
- DBA
- Tech leads reviewing early-stage feature documents
- LLMs drafting or reviewing `idea.md`

## Scope
- what `idea.md` is for
- what quality signals `idea.md` must satisfy before the feature can leave early exploration
- how SA, SD, and DBA should each contribute
- required sections and recommended section order
- weak patterns to avoid
- review checklist and starter template

## LLM Reading Contract
- Treat `idea.md` as the initial source document for a feature, not as a place for implementation progress tracking.
- Prefer business meaning, scope, constraints, data implications, and boundary decisions over page-by-page UI narration.
- If `idea.md` is too weak to support planning, identify the missing parts explicitly instead of filling them with guesses.

## Recommended Background
- basic understanding of Flow Driven Development
- requirement decomposition from business problem to system impact
- entity, module, data lifecycle, and ownership concepts
- basic documentation structure for early-stage feature design

## Core Thesis
- `idea.md` is the starting contract of an FDD feature.
- A weak `idea.md` does not only slow down SA. It causes repeated discuss loops, unstable plans, avoidable schema rework, and implementation drift later.
- Therefore, `idea.md` should be written as a decision-ready requirement basis, not as brainstorming scraps and not as implementation notes.
- `idea.md` should converge by example first. In practice, this means representative examples and scenarios are not decoration; they are one of the primary tools for making the requirement concrete enough to debate.

## What idea.md Is

`idea.md` is the feature's initial problem statement and decision basis.

It should answer:
- what problem exists
- why the problem matters
- what outcome is needed
- what is in scope and out of scope
- what constraints already exist
- what assumptions, dependencies, risks, and unresolved questions must be surfaced early

It should also make those answers testable in business language through concrete examples or scenarios. If readers cannot point to a few representative cases and say "this is the behavior we mean", the document is usually still too abstract.

It should be strong enough that later discuss can focus on real tradeoffs instead of spending time reconstructing basic context.

## What idea.md Is Not

`idea.md` is not:
- a progress log
- a task list
- a commit diary
- a page-by-page UI script without data meaning
- an implementation plan pretending to be a requirement document
- a place to hide unresolved assumptions behind vague wording

Those belong in other artifacts:
- progress and next-step tracking belong in `history.md`
- executable stage splitting belongs in `plan.md`
- completion and risk verification belong in `check.md`

## Why idea.md Quality Matters

A high-quality `idea.md` improves the entire downstream flow.

For SA:
- it improves scope control
- it reduces page-first decomposition
- it creates a better handoff to SD and programmers

For SD:
- it exposes entity boundaries, ownership, and layer mapping early
- it reduces design churn during module and API decisions
- it prevents implementation from being driven by accidental UI wording

For DBA:
- it clarifies data lifecycle, audit needs, relationship shape, and performance-sensitive queries early
- it reduces late-stage schema reversals caused by unclear ownership or missing data requirements

For LLM collaboration:
- it lowers ambiguity in later prompts
- it reduces hallucinated assumptions
- it makes drift detection easier because the original basis is explicit

## idea.md Entry Quality Standard

Before a feature can rely on `idea.md` as a stable starting point, the document should satisfy the following baseline.

### Minimum Standard
- the business problem is stated clearly
- the target outcome is stated clearly
- scope and non-scope are separated
- at least one stable business object or process target is named
- known constraints and dependencies are listed
- major unresolved questions are visible instead of hidden
- at least one concrete example or scenario exists to anchor the requirement in real behavior

### Planning-Ready Standard
- SA has already decomposed the requirement beyond page names
- SD can see likely module or layer implications
- DBA can see likely entity, schema, relationship, lifecycle, audit, or performance implications
- the document is detailed enough for discuss to produce real decisions rather than restating the same problem
- the main requirement can be explained through representative scenarios, edge boundaries, or contrasting examples rather than only abstract summary prose

If these conditions are not met, the feature is not ready to move cleanly into later FDD stages.

## Specification by Example Requirement

In this project, `idea.md` should prefer Specification by Example as the default convergence style.

This does not mean the document becomes a test file or acceptance script. It means the requirement should be clarified through representative examples and scenarios before later stages try to split work into implementation steps.

Why this matters:
- examples expose hidden assumptions faster than abstract prose
- scenarios force clearer scope boundaries and actor roles
- example-driven wording helps SA, SD, and DBA debate the same requirement from the same concrete baseline
- LLMs produce less drift when the requirement is grounded in explicit cases

Practical rule:
- do not treat examples as an appendix added at the end only if there is time
- use examples and scenarios as one of the main tools to decide scope, ownership, data meaning, and non-scope

When examples and surrounding prose disagree:
- treat the disagreement as a sign that the requirement is not yet converged
- update the prose, the examples, or both until they express the same feature truth

## Role Responsibilities

`idea.md` does not belong to only one role. It is a shared starting artifact with different responsibilities.

### SA Responsibilities
- define the business problem and target outcome
- separate scope from requests that are merely adjacent
- identify the core business object, process, or lifecycle being changed
- list key actors, permissions, approval roles, or workflow participants when relevant
- expose assumptions and unresolved questions instead of smoothing them over

### SD Responsibilities
- translate the requirement into likely entity, module, layer, and ownership implications
- identify where the requirement may create new boundaries or extend existing ones
- challenge page-first wording that hides real design impact
- surface early architectural constraints, shared-library implications, and integration points

### DBA Responsibilities
- identify likely data ownership, lifecycle, relationship, and retention implications
- flag audit requirements, concurrency risks, cardinality ambiguity, and query-sensitive features
- surface early schema or indexing concerns before planning starts
- challenge requirements that imply unrealistic storage, reporting, or migration assumptions

## Required Sections

The exact wording can vary, but a strong `idea.md` should normally contain the following sections.

### 1. Problem Statement
- describe the real business or system problem
- avoid starting from a page request alone
- explain why the current state is insufficient

### 2. Target Outcome
- describe the expected result in operational terms
- focus on what changes in the system, not only what changes on screen

### 3. Scope
- state what the feature must include in the first version
- keep the scope concrete and bounded

### 4. Non-Scope
- explicitly state what will not be solved in this round
- use this section to prevent later scope creep

### 5. Core Objects or Processes
- identify the entity, process, workflow, record type, or business state being changed
- if the feature affects more than one object, describe their relationship

### 6. Actors and Roles
- list the human roles, system actors, or integrations involved
- clarify who can read, write, approve, trigger, or observe the change

### 7. Data and State Implications
- describe what data must exist, what state changes are expected, and what audit trail or traceability is required
- this is especially important for SD and DBA handoff

### 8. Constraints and Dependencies
- include technical constraints, legal constraints, legacy compatibility constraints, operational dependencies, and rollout assumptions

### 9. Risks and Open Questions
- surface what is still unknown
- separate real decisions from assumptions

### 10. Early Examples or Scenarios
- include a few representative examples that make the problem concrete
- treat this section as a primary convergence tool, not an optional appendix
- prefer scenario-based examples over vague prose
- include at least one mainline scenario and at least one boundary, exception, or counter-example when the feature has meaningful scope edges
- examples should help clarify business truth, data meaning, ownership, and expected system outcome

For strong example-driven `idea.md` quality, each scenario should usually make visible:
- the triggering condition or starting context
- the actor or system participant
- the action or event
- the expected outcome
- the resulting data, state, log, or workflow consequence when relevant
- any important boundary such as "allowed in this round" versus "explicitly not included"

## Recommended Section Order

For most features, the following order works well:

1. Purpose
2. Background or Problem Statement
3. Target Outcome
4. Scope
5. Non-Scope
6. Core Objects or Processes
7. Actors and Roles
8. Data and State Implications
9. Constraints and Dependencies
10. Risks and Open Questions
11. Example Scenarios

This order moves from business meaning to system consequence, which is usually the safest path for SA, SD, and DBA to collaborate.

If the feature is complex, it is also acceptable to move Example Scenarios slightly earlier, as long as they help the rest of the document converge instead of fragmenting it. The main rule is that examples should drive understanding, not trail behind it.

## Writing Rules

### Use Business-Meaningful Language First
- start from the business object, process, or operational problem
- do not start from route names, button labels, or page blocks unless those are truly the product boundary

### Prefer Example-Driven Convergence
- when a requirement is still abstract, add or refine scenarios before adding more abstract paragraphs
- use examples to clarify what is in scope, what is out of scope, and where ownership or state changes happen
- if a feature has multiple interpretations, write contrasting scenarios until the ambiguity becomes visible
- prefer examples that reveal state transitions, actor boundaries, query implications, or audit consequences

### Use Scenarios To Expose Boundaries, Not Only Happy Paths
- include at least one scenario that shows the intended mainline behavior
- when useful, include a boundary case, exception, or explicitly excluded case
- do not let all examples be idealized UI demos that hide data or lifecycle consequences

### Prefer Stable Terms Over Temporary Labels
- use names that can survive UI changes
- if a UI term is temporary or ambiguous, mark it as such

### State Assumptions Explicitly
- if a statement depends on a legacy table, external API, migration path, or hidden policy, say so directly
- do not present assumptions as settled facts

### Separate Confirmed Facts From Open Questions
- one of the most common causes of weak `idea.md` quality is mixing tentative ideas with confirmed constraints
- if something is undecided, label it as undecided

### Describe Data Meaning, Not Just UI Behavior
- mention what must be stored, traced, linked, or queried
- if a feature implies status changes, logs, or audit trails, surface that in `idea.md`

### Include Enough Detail For Debate, Not Enough For Premature Implementation
- `idea.md` should enable informed discuss
- it should not collapse directly into a line-by-line implementation plan

## Anti-Patterns

### Page-First Specification
Weak pattern:
- "新增一個頁面，有三個欄位、一個按鈕、兩個列表"

Why it is weak:
- it says almost nothing about ownership, lifecycle, persistence, or module boundaries

### Hidden Schema Assumptions
Weak pattern:
- "系統應可記錄歷程"

Why it is weak:
- it does not say what should be recorded, by whom, at what granularity, or how the trace matters to later operations

### Fake Certainty
Weak pattern:
- using decisive wording for facts that have not yet been validated

Why it is weak:
- it creates later rollback cost because discuss starts from false stability

### Overloaded Brain Dump
Weak pattern:
- mixing requirement, design, implementation order, migration checklist, and test diary into one file

Why it is weak:
- the document loses its role and becomes impossible to maintain cleanly across stages

### Non-Scope Omission
Weak pattern:
- describing only desired outcomes and never saying what is intentionally excluded

Why it is weak:
- scope expands silently in later stages

### Example-Last Documentation
Weak pattern:
- writing several pages of abstract requirement prose and adding one shallow scenario at the end

Why it is weak:
- the document sounds complete but still leaves scope, ownership, and state implications open to interpretation

### Happy-Path-Only Examples
Weak pattern:
- only showing the ideal scenario and omitting edge boundaries, exclusions, or failure-relevant cases

Why it is weak:
- discuss and plan later inherit hidden ambiguity even though the document appears concrete

## Quality Review Checklist

Use this checklist before treating an `idea.md` as the basis for discuss or plan.

- [ ] The problem statement is clear without needing private meeting context.
- [ ] The target outcome is explicit and operationally meaningful.
- [ ] Scope and non-scope are both present.
- [ ] At least one stable business object, process, or lifecycle target is identified.
- [ ] Actors, permissions, or workflow participants are visible when relevant.
- [ ] Data, state, audit, or relationship implications are named when relevant.
- [ ] Constraints and dependencies are listed explicitly.
- [ ] Risks and open questions are separated from confirmed facts.
- [ ] At least one concrete scenario anchors the requirement in business reality.
- [ ] The examples help clarify scope, ownership, and state implications rather than only retelling UI steps.
- [ ] At least one scenario or counter-example marks an important boundary when the feature has meaningful edge cases.
- [ ] The document does not read like a UI-only script.
- [ ] The document does not read like an implementation plan.
- [ ] SA, SD, and DBA could all identify their next discussion topics from this file.

## SA / SD / DBA Review Lens

### SA Should Ask
- Is the business problem stated clearly enough to survive UI changes?
- Is scope narrow enough for a first version?
- Are there any hidden stakeholders or approval roles missing?

### SD Should Ask
- Can I infer likely entity boundaries, module placement, and layer responsibilities from this file?
- Are there any likely shared-library or architecture impacts already visible?
- Is the file overfitted to one screen rather than the real system behavior?

### DBA Should Ask
- What data will be created, updated, linked, audited, or queried because of this feature?
- Are lifecycle, retention, indexing, or concurrency assumptions still vague?
- Is there any requirement that will probably force schema churn if not clarified now?

## Recommended Starter Template

```md
# <Feature Name> Idea

## Purpose
- 說明這個 feature 要解決的核心問題。

## Background / Problem Statement
- 目前發生了什麼問題。
- 為什麼現況不足。

## Target Outcome
- 第一版完成後，系統應能做到什麼。

## Scope
- 本輪一定要包含的內容。

## Non-Scope
- 本輪明確不處理的內容。

## Core Objects or Processes
- 涉及哪些核心實體、流程、狀態或工作流。

## Actors and Roles
- 誰會發起、審核、操作、查看或整合這個 feature。

## Data and State Implications
- 需要新增、保存、追蹤、查詢或比對哪些資料。
- 是否涉及 state change、audit、history、log、trace。

## Constraints and Dependencies
- 既有系統限制。
- 外部依賴。
- 法規、權限、效能、相容性限制。

## Risks and Open Questions
- 目前仍未定案的點。
- 可能影響 discuss / plan 的主要風險。

## Example Scenarios
- 情境一：主線 scenario
	- 前提 / 觸發：
	- 角色 / 參與者：
	- 行為 / 事件：
	- 預期結果：
	- 對資料 / 狀態 / 紀錄的影響：
- 情境二：邊界或對照 scenario
	- 前提 / 觸發：
	- 角色 / 參與者：
	- 行為 / 事件：
	- 預期結果：
	- 為何屬於本輪非範圍或特殊規則：
```

## Exit Guidance

If `idea.md` is still missing basic business meaning, stable object identification, scope boundaries, or data implications, do not force the feature into planning yet.

The same applies if the document has abstract intent but still lacks strong examples. If the requirement cannot yet be explained through a few stable scenarios, it is usually too early to rely on it as planning input.

Instead:
- keep the feature in early exploration
- use discuss to clarify missing assumptions
- update `idea.md` before expecting `plan.md` to become stable

## Suggested Follow-Up Reading
- requirement breakdown methods for SA
- data modeling guidance for SD and DBA
- module boundary and ownership design guidance
- query and performance review guidance for data-sensitive features
- markdown writing conventions if the team plans to standardize document format

## Status
- Draft v1