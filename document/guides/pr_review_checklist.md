# F3CMS PR Review Checklist

## Purpose
- Standardize architecture-aware code review across schema, module, and data access changes.
- Help reviewers check structural quality, not only syntax correctness.
- Provide a review format that can also be applied by LLM reviewers.

## Primary Readers
- Reviewers
- Senior engineers
- Tech leads
- LLMs performing review assistance

## Scope
- module integrity
- schema integrity
- layer integrity
- data access quality
- maintainability and risk review

## LLM Reading Contract
- Prioritize structural defects over stylistic suggestions.
- When reviewing, report issues by severity and by violated architectural rule.
- Use this checklist as a review rubric.

## Inputs
- [data_modeling.md](data_modeling.md)
- [module_design.md](module_design.md)
- [feed_guide.md](feed_guide.md)
- [sd_conventions.md](sd_conventions.md)

## Core Thesis
- A good review in F3CMS does not start with formatting or personal style.
- It starts by checking whether the change preserves entity boundaries, schema integrity, layer responsibility, and operational safety.
- Review findings should therefore be written in order of architectural risk, not in order of file traversal.

## How to Use This Checklist

Use this document in three contexts:
- reviewer checklist during PR review
- author self-review before requesting review
- LLM-assisted review when summarizing risks and open questions

The expected behavior is:
- report findings first
- order findings by severity
- tie findings to violated architectural rules when possible
- treat unresolved structural issues as more important than naming or formatting nits

## Review Order

The recommended review order is:
1. identify what entity or module boundary the PR touches
2. verify that schema and module ownership are correct
3. verify that each changed layer keeps the right responsibility
4. verify query, save, and lifecycle safety
5. verify permissions, migration, and operational impact
6. list high-risk anti-patterns and residual risks

This order matters because many code-level symptoms are downstream of a wrong architecture decision.

## What Counts as a High-Value Finding

High-value findings usually identify one of the following:
- wrong entity or wrong module boundary
- wrong table placement or schema drift
- data logic implemented outside Feed
- duplicated business rules across multiple layers
- query patterns that create obvious performance or correctness risk
- operational regressions such as missing permissions, missing migration support, or backward compatibility breakage

Low-value review comments usually focus on:
- superficial naming without architectural consequence
- personal style preference without consistency impact
- tiny refactor suggestions that do not affect risk, correctness, or maintainability

The checklist below is designed to bias review effort toward the high-value category.

## 1. Entity and Module Integrity

Use this section first, especially when the PR creates files, tables, or new actions.

### Entity Boundary Questions
- [ ] Is the change clearly attached to a recognizable business entity?
- [ ] Does the PR modify the correct existing module rather than introducing logic into a neighboring module for convenience?
- [ ] If a new module is introduced, is there clear evidence that the business object has independent lifecycle, ownership, or query behavior?
- [ ] If the change extends an existing module, does it remain subordinate rather than behaving like a hidden new entity?

### Module Structure Questions
- [ ] Do new files follow the expected module structure under `www/f3cms/modules/`?
- [ ] Are Feed, Reaction, Outfit, and Kit names aligned with the module name and autoload conventions?
- [ ] Does the folder and class naming preserve the standard architectural language of the project?

### Reviewer Decision
- If the reviewer cannot explain which entity owns the change, the PR is not ready.
- If a new screen appears to be driving a new module without a stable entity, request redesign before merge.

## 2. Schema Integrity

Use this section whenever the PR adds or changes database structure, save payloads, or data fields.

### Table Placement Questions
- [ ] Are stable operational fields stored in the main table?
- [ ] Are localized fields stored in `_lang` rather than the main table?
- [ ] Are optional extension fields placed in `_meta` only when they are not first-class query fields?
- [ ] Are real cross-entity relationships modeled with relation tables rather than serialized blobs?

### Naming Questions
- [ ] Do table names use `tbl_` plus predictable lowercase underscore naming?
- [ ] Do `_lang`, `_meta`, and relation tables follow existing naming conventions?
- [ ] Are field names consistent with existing audit, owner, status, and foreign key naming rules?

### Audit and Lifecycle Questions
- [ ] Are standard audit fields present when the entity should be tracked operationally?
- [ ] Does the schema support the actual lifecycle described by the feature rather than only the current UI needs?
- [ ] Would a new engineer understand the data model from schema names alone?

### Reviewer Decision
- If a field is stored in the wrong table, treat that as a structural defect even if the feature appears to work.

## 3. Layer Integrity

This is one of the most important review sections in F3CMS.

### Feed Questions
- [ ] Does Feed remain the owner of entity persistence, retrieval, query defaults, multilingual behavior, metadata handling, and relation-aware logic?
- [ ] Are save and read lifecycle rules implemented in Feed rather than scattered through callers?
- [ ] If custom query or normalization logic was added, is Feed the right home for it?

### Reaction Questions
- [ ] Does Reaction focus on request handling, permission orchestration, validation orchestration, and response formatting?
- [ ] Has the PR avoided pushing schema decisions or persistence semantics into Reaction?
- [ ] Are backend responses consistent with existing patterns?

### Outfit Questions
- [ ] Does Outfit remain focused on page flow, rendering preparation, and template selection?
- [ ] Has the PR avoided turning Outfit into an alternative data write path?
- [ ] Are page changes separated from data lifecycle changes?

### Kit and Shared Utility Questions
- [ ] Is shared reusable logic placed in Kit or a shared library only when reuse across modules is real?
- [ ] If the logic still belongs to one module's business boundary, does the PR keep it in that module's Kit even when other modules reuse it, instead of pushing it into `libs` too early?
- [ ] Has the PR avoided creating one-off helpers that only hide poor layer placement?

### Reviewer Decision
- If the same business rule now exists in two layers, request consolidation before merge.
- If data logic is being introduced mainly in Reaction or Outfit, treat that as a strong review finding.

## 4. Query and Performance Integrity

Use this section for both obvious performance risk and correctness risk caused by the wrong query shape.

### Read Path Questions
- [ ] Does the PR use the correct Feed read path for single-row, list, or paginated access?
- [ ] Are list and detail reads treated differently when they have different cost or data-shape requirements?
- [ ] Are query joins justified by the data model rather than by convenience?

### Query Construction Questions
- [ ] Has the PR avoided unnecessary raw SQL when Feed or shared helpers already express the pattern?
- [ ] If raw SQL is used, is the reason clear and defensible?
- [ ] Are repeated query fragments centralized instead of duplicated in multiple callers?

### Performance Sanity Questions
- [ ] Does the list path avoid unnecessary joins or heavy columns?
- [ ] Are count and pagination implications considered when list endpoints are involved?
- [ ] Are fields that need filtering or sorting stored in a way that can be indexed reasonably?

### Reviewer Decision
- If the implementation relies on application-side filtering of large result sets, treat it as a likely structural issue, not a later optimization task.

## 5. Operational Integrity

This section checks whether the code can operate safely after merge.

### Permission and Access Questions
- [ ] Are read, update, delete, or backend action permissions aligned with existing module patterns?
- [ ] Does the PR avoid accidentally exposing backend-only behavior through frontend or public flows?
- [ ] If a new capability is introduced, is its access model explicit?

### Admin and Delivery Questions
- [ ] If the feature changes backend usage, are menu, admin visibility, or management flows considered?
- [ ] If the PR introduces a new module, does it account for the minimum operational wiring expected by the system?

### Migration and Compatibility Questions
- [ ] Are schema changes accompanied by the required SQL or migration updates?
- [ ] Does the change preserve compatibility for existing data where necessary?
- [ ] Could the PR silently break existing read paths, save flows, or existing records?

### Reviewer Decision
- If the code cannot be safely deployed without undocumented manual steps, the PR is not operationally complete.

## 6. Testability and Maintainability Integrity

This section checks whether the change will remain understandable after the original author moves on.

### Maintainability Questions
- [ ] Can another engineer infer the architecture from the final code without reading the PR discussion?
- [ ] Does the code follow project conventions closely enough that future changes will feel predictable?
- [ ] Has the PR avoided introducing clever but non-standard structure for a one-off problem?

### Testability and Verification Questions
- [ ] Is there enough verification evidence to trust the change?
- [ ] If automated tests are absent, does the PR at least make manual verification explicit?
- [ ] Are risky branches, failure cases, or migration-sensitive paths acknowledged in the review notes?

### Reviewer Decision
- If the only reason the change seems acceptable is that the author understands it today, the maintainability bar is too low.

## 7. High-Risk Anti-Pattern Checklist

Any yes answer here should produce an explicit review finding.

### Structural Anti-Patterns
- [ ] Is the PR creating a new module mainly because of a new page or endpoint?
- [ ] Is the PR mixing multiple entities into one module without a clear ownership model?
- [ ] Is naming drifting away from standard module or schema conventions?

### Data Anti-Patterns
- [ ] Is a real relation stored as JSON, comma-separated text, or another unstructured field?
- [ ] Is localized content being stored in the main table?
- [ ] Is `_meta` being used to avoid making a real schema decision?

### Layer Anti-Patterns
- [ ] Is direct write logic bypassing Feed without clear justification?
- [ ] Is Reaction or Outfit taking ownership of persistence semantics?
- [ ] Is the same business rule duplicated across Feed, Reaction, Outfit, or frontend code?

### Operational Anti-Patterns
- [ ] Does the PR require hidden manual deployment or data-fix steps?
- [ ] Does the PR change behavior without documenting migration or compatibility expectations?
- [ ] Does the PR introduce performance risk while assuming it will be addressed later?

## 8. Severity Model for Review Findings

Use this model so reviews stay consistent.

### High Severity
- likely correctness bug
- schema or entity boundary defect
- permission or security exposure
- strong likelihood of data corruption or lifecycle inconsistency
- clear regression risk in existing module behavior

### Medium Severity
- maintainability problem with likely future breakage
- incorrect layer placement that works now but will spread duplication
- query or pagination pattern with visible scale risk
- convention drift that makes future work harder

### Low Severity
- naming cleanup with modest readability impact
- minor consistency issue with low architectural risk
- small simplification suggestion that does not block merge

## 9. Review Output Template

Use this format when writing the final review.

### Findings First
- List defects before summaries.
- Order by severity.
- For each finding, state what is wrong, why it matters, and which architectural rule is being violated.

### Open Questions
- Record any missing assumptions that affect correctness, migration, or ownership.
- Use questions only when the issue cannot be proven from the diff alone.

### Residual Risks
- State what was not validated.
- Mention testing gaps, deployment assumptions, or scale concerns.

### Suggested Short Review Template
- Findings:
- Open questions:
- Residual risks:
- Merge recommendation: approve / revise / redesign

## 10. Merge Decision Guidance

### Approve
- No high-severity structural findings remain.
- Layer placement, schema decisions, and operational behavior are coherent.

### Request Revision
- The PR is directionally correct but contains fixable structural, lifecycle, or maintainability issues.

### Request Redesign
- The PR is built on the wrong entity split, wrong module boundary, wrong table placement, or wrong layer ownership.
- Small patching would only hide the real design problem.

## Related Documents
- [data_architecture_checklist.md](data_architecture_checklist.md)
- [sd_conventions.md](sd_conventions.md)
- [query_and_performance.md](query_and_performance.md)

## Status
- Draft v1 complete
