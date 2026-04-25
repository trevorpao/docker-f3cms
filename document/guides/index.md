# F3CMS Guides Index

## Purpose
- Provide a single entry point to all architecture and development guides.
- Help human readers and LLMs find the right source document quickly.
- Formalize reading paths by role, task, and decision stage.
- Preserve older documents while introducing a more structured guide set.

## Primary Readers
- New engineers
- SA / SD
- Backend programmers
- LLM-based assistants used for onboarding, code generation, and review

## Scope
- guide discovery
- reading order
- document precedence
- role-based entry paths
- task-based entry paths
- maintenance rules for this folder

## LLM Reading Contract
- Treat each section titled Purpose, Scope, Inputs, Decision Rules, and Related Documents as canonical metadata.
- Prefer newer guide files in this folder when guidance overlaps with older narrative files.
- Use older files as background context, and newer files as operational guidance.
- When answering implementation questions, trace guidance in this order: data_modeling.md -> module_design.md -> feed_guide.md -> sd_conventions.md.

## Inputs
- [overall.md](overall.md)
- [create_new_module.md](create_new_module.md)
- [data_modeling.md](data_modeling.md)
- [data_architecture_checklist.md](data_architecture_checklist.md)
- [fdd_porting_guide.md](fdd_porting_guide.md)
- [testmode_development_guide.md](testmode_development_guide.md)
- [module_design.md](module_design.md)
- [feed_guide.md](feed_guide.md)
- [idea_md_writing_guide.md](idea_md_writing_guide.md)
- [idea_md_role_examples.md](idea_md_role_examples.md)
- [new_engineer_30min.md](new_engineer_30min.md)
- [pr_review_checklist.md](pr_review_checklist.md)
- [query_and_performance.md](query_and_performance.md)
- [sa_requirement_breakdown.md](sa_requirement_breakdown.md)
- [sd_conventions.md](sd_conventions.md)

## Core Thesis
- This file is not only a directory. It is the routing layer for the documentation system.
- Readers should not start from file names alone. They should start from their role, current task, and decision stage.
- The newer structured guides in this folder define the operational architecture language of F3CMS.
- Older guides remain useful, but they should be read through the precedence rules defined here.

## How to Use This Index

Choose the entry path based on the question you are trying to answer.

If the question is:
- what F3CMS is and how the system is shaped, start from `overall.md`
- how TestMode-based smoke development should work after the migration, start from `testmode_development_guide.md`
- how to write a high-quality FDD `idea.md`, start from `idea_md_writing_guide.md`
- how to transplant FDD into another project, start from `fdd_porting_guide.md`
- how to break requirements into modules, start from `sa_requirement_breakdown.md`
- how to design schema and table structure, start from `data_modeling.md`
- how to act as DBA or prepare schema decisions as an LLM, start from `llm_dba_guide.md`
- how to decide whether a new module is needed, start from `module_design.md`
- how Feed should save, read, or join data, start from `feed_guide.md`
- how to write code in a convention-aligned way, start from `sd_conventions.md`
- how to inspect query cost or avoid slow patterns, start from `query_and_performance.md`
- how to get productive quickly as a new engineer, start from `new_engineer_30min.md`

The intended behavior is:
- narrative understanding first when the reader is unfamiliar with F3CMS
- operational guidance first when the reader is actively designing or coding
- checklists last when the reader is validating or reviewing work

## Document Precedence Rules

When two documents appear to overlap, use the following priority rules.

### Architecture Precedence
- `overall.md` explains the broad architectural story and historical structure.
- New structured guides explain how to make current design and implementation decisions.
- If a newer guide conflicts with older narrative wording, prefer the newer guide for operational decisions.

### Design Precedence
- For early FDD requirement-basis quality, prefer `idea_md_writing_guide.md`.
- For schema decisions, prefer `data_modeling.md`.
- For module boundary decisions, prefer `module_design.md`.
- For SA-side requirement decomposition, prefer `sa_requirement_breakdown.md`.
- For SD-side implementation conventions, prefer `sd_conventions.md`.
- For Feed-layer structure and behavior, prefer `feed_guide.md`.
- For query tuning and access pattern tradeoffs, prefer `query_and_performance.md`.

### Practical Guide Precedence
- `create_new_module.md` remains the practical build sequence reference.
- Use it after the design has already been validated by `data_modeling.md`, `module_design.md`, and `sd_conventions.md`.
- Do not use `create_new_module.md` as the only design source for whether a module should exist.

### Checklist and Review Precedence
- Use `data_architecture_checklist.md` to validate schema, table placement, and Feed alignment before implementation is considered stable.
- Use `pr_review_checklist.md` to review the final code change after design and implementation choices already exist.
- Use `new_engineer_30min.md` as the entry document for orientation, not as the authority for design arbitration.

## Guide Map

This section describes what each document is for and when it should be opened.

### Foundation and Narrative Documents

#### [overall.md](overall.md)
- Use when the reader needs the full architectural narrative of F3CMS.
- Best for understanding the historical and conceptual model of modules, data, ERD, and system layout.
- Read first when the reader does not yet understand the vocabulary of the system.

#### [setup.md](setup.md)
- Use when the reader needs environment setup and local execution context.
- Best for bootstrapping a machine or verifying prerequisites.

### Core Structured Guides

#### [data_modeling.md](data_modeling.md)
- Primary source for entity-first schema design.
- Use when deciding main tables, `_lang`, `_meta`, relation tables, and field placement.
- Read before writing SQL, Feed constants, or save flows.

#### [llm_dba_guide.md](llm_dba_guide.md)
- DBA-oriented consolidation guide for LLMs.
- Use when the task is to review, design, challenge, or deliver schema changes with enough precision to act as DBA.
- Read before producing DDL, migration rationale, or table-placement arbitration.

#### [module_design.md](module_design.md)
- Primary source for mapping entities into modules and splitting responsibilities.
- Use when deciding whether a requirement is a new module, an extension, or only a relation.
- Read before creating module folders or class skeletons.

#### [feed_guide.md](feed_guide.md)
- Primary source for Feed-layer responsibilities and data lifecycle behavior.
- Use when implementing save, read, relation, multilingual, and query composition behavior.
- Read before adding custom persistence logic.

#### [query_and_performance.md](query_and_performance.md)
- Primary source for query-shape decisions and performance tradeoffs.
- Use when joins, pagination, filtering, indexing, or expensive list pages are involved.
- Read when performance risk appears before or after implementation.

#### [idea_md_writing_guide.md](idea_md_writing_guide.md)
- Primary source for writing a high-quality FDD `idea.md`.
- Use when the feature is still forming and the team needs a stable initial requirement basis before discuss or planning.
- Read before approving an early-stage feature document as "good enough".

#### [idea_md_role_examples.md](idea_md_role_examples.md)
- Practical examples showing how SA, SD, and DBA write the same `idea.md` differently.
- Use after reading `idea_md_writing_guide.md` when the team needs concrete role-oriented samples.
- Best for onboarding, review calibration, and LLM prompting examples.

#### [fdd_porting_guide.md](fdd_porting_guide.md)
- Primary source for moving the FDD workflow into another repository.
- Use when the team wants to preserve LLM behavior, prompt entry points, and spec continuation rules across projects.
- Read before copying prompts or workspace instructions into a new codebase.

#### [testmode_development_guide.md](testmode_development_guide.md)
- Primary source for TestMode-related smoke development after the first migration has stabilized.
- Use when deciding where a smoke suite should live, how it should be named, how it should be validated, and which paths are no longer allowed.
- Read before adding new smoke suites or reshaping existing ones under `www/tests/`.

#### [sa_requirement_breakdown.md](sa_requirement_breakdown.md)
- Primary source for turning business requirements into entities, module boundaries, and handoff structure.
- Use during discovery, scoping, and early solution shaping.
- Read before writing page-first specifications.

#### [sd_conventions.md](sd_conventions.md)
- Primary source for naming, directory structure, schema naming, and implementation boundaries.
- Use when translating approved design into code structure.
- Read before creating files or introducing new conventions.

### Guided Onboarding and Validation Documents

#### [new_engineer_30min.md](new_engineer_30min.md)
- Short entry guide for engineers who need a fast working mental model.
- Use before reading deeper design documents if the reader is entirely new to F3CMS.
- Continue from this document into `data_modeling.md`, `module_design.md`, and `feed_guide.md` once the vocabulary is stable.

#### [data_architecture_checklist.md](data_architecture_checklist.md)
- Validation guide for schema and entity decisions.
- Use after a design is drafted and before implementation is finalized.
- Best paired with `data_modeling.md`, `feed_guide.md`, and `create_new_module.md` when a new module or schema change is being prepared.

#### [pr_review_checklist.md](pr_review_checklist.md)
- Validation guide for code review and design review.
- Use during PR review or self-review before merge.
- Best used after `data_architecture_checklist.md` when the code diff already exists and the reviewer needs a severity-based rubric.

### Existing Practical Guides

#### [create_new_module.md](create_new_module.md)
- Existing step-by-step operational guide for creating modules.
- Use after module need, schema shape, and naming decisions are already clear.
- Best treated as execution support rather than as the source of architectural truth.
- Read it after `module_design.md`, `data_modeling.md`, and `sd_conventions.md`, then validate with `data_architecture_checklist.md` and `pr_review_checklist.md`.

#### [coding_style.md](coding_style.md)
- Use for lower-level code style consistency.
- Apply after higher-level design decisions are already made.

#### [markdown_guide.md](markdown_guide.md)
- Use when writing or updating project documentation.
- Helps keep written materials consistent.

## Recommended Reading Paths

The reading paths below are ordered. Read in sequence unless you already know the earlier material.

### For SA
1. [overall.md](overall.md)
2. [idea_md_writing_guide.md](idea_md_writing_guide.md)
3. [idea_md_role_examples.md](idea_md_role_examples.md)
4. [sa_requirement_breakdown.md](sa_requirement_breakdown.md)
5. [data_modeling.md](data_modeling.md)
6. [module_design.md](module_design.md)
7. [data_architecture_checklist.md](data_architecture_checklist.md)

This path is for requirement decomposition, scope control, and handoff quality.

### For SD
1. [overall.md](overall.md)
2. [idea_md_writing_guide.md](idea_md_writing_guide.md)
3. [idea_md_role_examples.md](idea_md_role_examples.md)

### For LLM DBA
1. [data_modeling.md](data_modeling.md)
2. [llm_dba_guide.md](llm_dba_guide.md)
3. [module_design.md](module_design.md)
4. [sd_conventions.md](sd_conventions.md)
5. [query_and_performance.md](query_and_performance.md)
6. [data_architecture_checklist.md](data_architecture_checklist.md)
7. [document/sql/init.sql](../sql/init.sql)

This path is for schema arbitration, DDL preparation, migration review, and database-oriented design challenge.
4. [data_modeling.md](data_modeling.md)
5. [module_design.md](module_design.md)
6. [sd_conventions.md](sd_conventions.md)
7. [testmode_development_guide.md](testmode_development_guide.md) when the task involves smoke structure or validation contracts
8. [feed_guide.md](feed_guide.md)
9. [create_new_module.md](create_new_module.md)
10. [data_architecture_checklist.md](data_architecture_checklist.md)
11. [query_and_performance.md](query_and_performance.md)
12. [pr_review_checklist.md](pr_review_checklist.md)

This path is for turning a validated requirement into a convention-aligned implementation.

### For DBA
1. [overall.md](overall.md)
2. [idea_md_writing_guide.md](idea_md_writing_guide.md)
3. [idea_md_role_examples.md](idea_md_role_examples.md)
4. [data_modeling.md](data_modeling.md)
5. [query_and_performance.md](query_and_performance.md)
6. [data_architecture_checklist.md](data_architecture_checklist.md)

This path is for reviewing whether an early feature idea has enough data, lifecycle, and query clarity before schema or migration work begins.

### For Backend Programmers
1. [new_engineer_30min.md](new_engineer_30min.md)
2. [data_modeling.md](data_modeling.md)
3. [feed_guide.md](feed_guide.md)
4. [query_and_performance.md](query_and_performance.md)
5. [sd_conventions.md](sd_conventions.md)
6. [pr_review_checklist.md](pr_review_checklist.md)

This path is for daily implementation work and defensive self-review.

### For New Engineers
1. [new_engineer_30min.md](new_engineer_30min.md)
2. [overall.md](overall.md)
3. [data_modeling.md](data_modeling.md)
4. [module_design.md](module_design.md)
5. [feed_guide.md](feed_guide.md)
6. [create_new_module.md](create_new_module.md) when the engineer needs to scaffold rather than only understand
7. [data_architecture_checklist.md](data_architecture_checklist.md) before changing schema or Feed

This path is for building a usable system model quickly without reading everything upfront.

### For LLMs
1. [index.md](index.md)
2. [overall.md](overall.md) when broad context is missing
3. [testmode_development_guide.md](testmode_development_guide.md) when the task involves smoke development or validation contracts
4. [data_modeling.md](data_modeling.md)
5. [module_design.md](module_design.md)
5. [feed_guide.md](feed_guide.md)
6. [sd_conventions.md](sd_conventions.md)
7. [query_and_performance.md](query_and_performance.md) when query cost or join shape matters
8. [create_new_module.md](create_new_module.md) only after the design path above is satisfied

This path is intentionally biased toward stable decision documents instead of historical narrative.

### For FDD Migration
1. [fdd_porting_guide.md](fdd_porting_guide.md)
2. [idea_md_writing_guide.md](idea_md_writing_guide.md)
3. [idea_md_role_examples.md](idea_md_role_examples.md)

This path is for moving the workflow into another project without losing the behavior contract that guides an LLM.

## Task-Oriented Entry Paths

Use this section when the reader has a concrete task rather than a role identity.

### Task: Break Down a New Requirement
1. [overall.md](overall.md)
2. [idea_md_writing_guide.md](idea_md_writing_guide.md)
3. [sa_requirement_breakdown.md](sa_requirement_breakdown.md)
4. [data_modeling.md](data_modeling.md)
5. [module_design.md](module_design.md)

### Task: Review an Early idea.md
1. [idea_md_writing_guide.md](idea_md_writing_guide.md)
2. [idea_md_role_examples.md](idea_md_role_examples.md)
3. [sa_requirement_breakdown.md](sa_requirement_breakdown.md)
4. [data_modeling.md](data_modeling.md)
5. [module_design.md](module_design.md)
6. [query_and_performance.md](query_and_performance.md)

### Task: Decide Whether to Create a New Module
1. [module_design.md](module_design.md)
2. [data_modeling.md](data_modeling.md)
3. [sd_conventions.md](sd_conventions.md)
4. [create_new_module.md](create_new_module.md)
5. [data_architecture_checklist.md](data_architecture_checklist.md)

### Task: Scaffold and Implement a New Module
1. [module_design.md](module_design.md)
2. [data_modeling.md](data_modeling.md)
3. [sd_conventions.md](sd_conventions.md)
4. [create_new_module.md](create_new_module.md)
5. [feed_guide.md](feed_guide.md)
6. [data_architecture_checklist.md](data_architecture_checklist.md)
7. [pr_review_checklist.md](pr_review_checklist.md)

### Task: Design a New Table or Extend an Existing Schema
1. [data_modeling.md](data_modeling.md)
2. [data_architecture_checklist.md](data_architecture_checklist.md)
3. [feed_guide.md](feed_guide.md)

### Task: Implement Feed Logic
1. [feed_guide.md](feed_guide.md)
2. [data_modeling.md](data_modeling.md)
3. [query_and_performance.md](query_and_performance.md)

### Task: Review a Pull Request
1. [pr_review_checklist.md](pr_review_checklist.md)
2. [sd_conventions.md](sd_conventions.md)
3. [data_architecture_checklist.md](data_architecture_checklist.md)
4. [query_and_performance.md](query_and_performance.md)

### Task: Onboard a New Engineer
1. [new_engineer_30min.md](new_engineer_30min.md)
2. [overall.md](overall.md)
3. [data_modeling.md](data_modeling.md)
4. [module_design.md](module_design.md)
5. [feed_guide.md](feed_guide.md)

## Fast Route by Question Type

If the reader only has one urgent question, use this short routing table.

If asking:
- where should this field live, read `data_modeling.md`
- should this be a new module, read `module_design.md`
- how do I actually scaffold the module after the design is clear, read `create_new_module.md`
- how should Feed save or query this, read `feed_guide.md`
- why is this query likely to become slow, read `query_and_performance.md`
- how should SA package this requirement for SD, read `sa_requirement_breakdown.md`
- how should SD name and place the files, read `sd_conventions.md`
- how do I validate schema and layer placement before coding or before merge, read `data_architecture_checklist.md`
- how should I review a PR in a severity-based way, read `pr_review_checklist.md`
- what should I read first as a newcomer, read `new_engineer_30min.md`

## Document Lifecycle Rules

The guide system should stay stable enough that humans and LLMs can build habits around it.

### When to Create a New Guide
- create a new guide only when the topic introduces a stable decision domain
- do not create a new guide for one-off feature notes
- prefer extending an existing guide when the new topic is already governed by an existing decision category

### When to Update This Index
- update this file whenever a new guide is added
- update this file whenever document precedence changes
- update this file whenever a reading path becomes misleading or incomplete

### Naming and Section Stability
- keep major section names stable so links and LLM routing remain predictable
- prefer Purpose, Scope, Inputs, Core Thesis, and Related Documents as recurring metadata anchors
- avoid renaming documents without also updating all references in this folder

## Reading Strategy for Humans and LLMs

The expected reading behavior is:
- use `overall.md` to learn the language of the system
- use the structured guides to make actual decisions
- use checklist documents to validate completed design or code
- use older practical guides to execute, not to arbitrate architecture

This order matters because many implementation mistakes in F3CMS come from jumping directly into file creation before the entity model and module boundary are stable.

## Status
- Draft v1 complete
