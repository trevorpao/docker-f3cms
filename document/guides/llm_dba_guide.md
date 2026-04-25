# F3CMS LLM DBA Guide

## Purpose
- Provide a single operational reading guide for an LLM acting as DBA or schema reviewer in F3CMS.
- Consolidate entity-first schema rules, naming rules, table decomposition rules, SQL delivery rules, and performance implications into one document.
- Enable precise, reviewable, inference-capable database decisions without requiring the reader to reconstruct the architecture from multiple separate guides.

## Primary Readers
- LLMs acting as DBA
- Senior backend programmers preparing schema changes
- SD reviewers checking database alignment
- Engineers handing off SQL to DBA execution

## Scope
- source-of-truth order for database decisions
- entity-first schema reasoning
- table decomposition rules
- naming and field conventions
- workflow and log table conventions
- migration and delivery conventions
- query and indexing implications
- anti-pattern detection
- expected output format for DBA-quality recommendations

## LLM Reading Contract
- Treat this file as the DBA-oriented routing and consolidation layer, not as a replacement for the underlying guides.
- For schema design questions, read and obey these documents in this order unless a more specific feature document explicitly overrides them:
  1. `document/guides/data_modeling.md`
  2. `document/guides/module_design.md`
  3. `document/guides/sd_conventions.md`
  4. `document/guides/query_and_performance.md`
  5. `document/guides/data_architecture_checklist.md`
  6. `document/sql/init.sql`
- Treat `document/sql/init.sql` as the structural baseline of current table patterns, not as the place to append daily delivery SQL.
- Treat `document/sql/YYMMDD.sql` as the required landing zone for new DBA-executed schema change SQL.
- Do not infer PostgreSQL-style capabilities such as `JSONB`, `GIN`, partial indexes, or ORM-managed migrations unless the repository explicitly reopens those assumptions.
- When uncertain, prefer stable relational modeling and entity boundaries over convenience-oriented schema shortcuts.

## Inputs
- [data_modeling.md](data_modeling.md)
- [module_design.md](module_design.md)
- [sd_conventions.md](sd_conventions.md)
- [query_and_performance.md](query_and_performance.md)
- [data_architecture_checklist.md](data_architecture_checklist.md)
- [create_new_module.md](create_new_module.md)
- [document/sql/init.sql](../sql/init.sql)

## Core Thesis
- In F3CMS, database design starts from the entity, not from the page, route, API payload, or transport format.
- One entity normally maps to one module, and schema decomposition is the database reflection of that entity boundary.
- A correct DBA decision is one that preserves entity ownership, stable naming, predictable table decomposition, Feed lifecycle integrity, and queryable relational structure.
- The DBA role in F3CMS is not only to produce valid SQL. It is to preserve the architectural language of the system.

## Decision Order

When acting as DBA, make decisions in this order:

1. Identify the entity.
2. Decide whether it is a new module or an extension of an existing module.
3. Decide the table decomposition.
4. Decide field placement table by table.
5. Verify naming and audit consistency.
6. Verify Feed alignment and lifecycle impact.
7. Verify query, filtering, join, and indexing implications.
8. Produce delivery SQL in `document/sql/YYMMDD.sql`.

Do not reverse this order. If entity identification is weak, all downstream schema decisions become unstable.

## Source-Of-Truth Hierarchy For DBA Work

### 1. Entity and Schema Shape
- Use [data_modeling.md](data_modeling.md) as the highest-priority schema design source.
- Use [module_design.md](module_design.md) when deciding whether schema belongs to an existing entity or a new module.

### 2. Naming and Delivery
- Use [sd_conventions.md](sd_conventions.md) for table naming, field naming, folder/module alignment, and daily SQL delivery rules.

### 3. Query and Performance
- Use [query_and_performance.md](query_and_performance.md) to validate that schema decisions support intended joins, filters, pagination, and indexing.

### 4. Validation
- Use [data_architecture_checklist.md](data_architecture_checklist.md) as the final design validation layer before treating a schema proposal as ready.

### 5. Existing Baseline Facts
- Use [document/sql/init.sql](../sql/init.sql) to inspect current naming patterns, charset choices, audit field patterns, `_lang` / `_meta` decomposition, and relation table conventions already present in the system.

## Database Environment Assumptions

Unless explicitly reopened by the repository, the LLM DBA should assume:

- Database family: MariaDB / MySQL-compatible
- Existing baseline version evidence: MariaDB 10.4.6-compatible patterns are present in [document/sql/init.sql](../sql/init.sql#L1)
- Typical storage engine: `InnoDB`
- Typical charset target for content tables: `utf8mb4`
- Baseline schema style: convention-driven relational decomposition with `_lang`, `_meta`, relation tables, and module-owned logs

Do not assume:

- PostgreSQL-specific features
- ORM-generated migration history
- automatic schema diff tooling as the source of truth
- JSON fields are preferred over relational tables when the data has relation semantics

## Entity-First DBA Rules

### What Counts As an Entity
An entity in F3CMS is a stable business object that usually has:

- its own business name
- its own persistence boundary
- its own lifecycle or status model
- its own ownership, permission, or audit implications
- relationships to other entities

### Default Mapping Rule
- One entity normally maps to one module.

### New Module Signals
Treat the requirement as a likely new module when most of these are true:

- it needs its own main table
- it has an independent lifecycle or status
- it needs separate backend actions
- it has independent audit or ownership meaning
- it is likely to be queried directly

### Existing Module Extension Signals
Treat the requirement as an extension of an existing entity when most of these are true:

- it is subordinate to an existing entity
- it does not create an independent lifecycle
- it is a stable field, localized field, metadata field, relation, or trace of an existing entity
- separate querying would be unnatural

### DBA Stop Rule
If the requirement is still described mainly in page language, screen language, or transport language, stop and ask for the entity boundary first.

## Table Decomposition Rules

F3CMS does not default to one table per entity. It uses decomposed relational structure.

### Main Table
Use the main table for stable, non-localized, operationally important fields.

Typical fields:
- `id`
- `status`
- `slug`
- `layout`
- `sorter`
- ownership fields such as `${owner}_id`
- `insert_ts`
- `last_ts`
- `insert_user`
- `last_user`

Main table fields should be:
- frequently filtered or sorted
- structurally central
- not language-specific
- not optional display-only noise

### Language Table
Use `${base}_lang` for language-variant content.

Typical fields:
- `title`
- `subtitle`
- `summary`
- `info`
- `content`
- `alias`
- `keyword`

Do not place language-varying content in the main table.

### Meta Table
Use `${base}_meta` for optional or extensible attributes that are real but not central enough for the main table.

Typical candidates:
- SEO values
- optional CTA labels
- low-frequency extension settings

Do not use `_meta` as a garbage bin. If the field will be filtered, sorted, joined, or indexed frequently, it likely belongs in the main table.

### Relation Table
Use a dedicated relation table when the value is actually another entity reference.

Typical form:
- `tbl_{entity_a}_{entity_b}`

Use relation tables when:
- the relationship is many-to-many
- filtering or reporting on the relation matters
- the relationship is not just decorative
- relation order matters and may need `sorter`

Never hide a real relation in JSON or comma-separated text if the relation needs structured editing or querying.

### Trace / Extension / Log Table
Use a secondary subordinate table when the data grows independently but remains owned by the parent entity.

Examples:
- `tbl_press_log`
- import-like or raw extension tables

### Module-Owned Workflow Log Pattern
When an entity has workflow control, keep audit history in a module-owned log table rather than inventing a shared workflow runtime table.

Recommended minimum fields:
- `parent_id`
- `insert_user`
- `action_code`
- `old_state_code`
- `new_state_code`
- `insert_ts`

Optional richer trace fields:
- `remark`
- `branch_token`
- `parallel_group_code`
- `join_group_code`
- `extra_context_json`

Current F3CMS practice explicitly avoids introducing shared workflow runtime tables such as:
- `tbl_workflow_instance`
- `tbl_workflow_instance_trace`

unless the repository explicitly reopens that architecture decision.

## Field Placement Rules

When deciding a field, ask what kind of data it is before asking where it is easiest to store.

### Put It In the Main Table When
- it is not language-specific
- it is central to the entity identity or lifecycle
- it is frequently filtered, sorted, or indexed
- it affects ownership, visibility, or operation
- it needs strong structural stability

### Put It In `_lang` When
- it varies by language
- it is localized presentation content
- editors or translators need per-language control

### Put It In `_meta` When
- it is optional or feature-specific
- it is low-frequency extension data
- it is semantically attached but not structurally central
- it is not expected to be a hot filter or join key

### Put It In a Relation Table When
- it references another entity
- many-to-many modeling is required
- the relation needs filtering, sorting, reporting, or structured editing

### Put It In a Trace Table When
- it is history, audit, import, or subordinate trace data
- it grows independently from the parent row count
- it should not pollute the main table

## Naming Conventions For DBA Work

### Table Naming
- always use `tbl_` prefix for business tables
- use lowercase with underscores
- use the entity name as the base
- use `${base}_lang` for localized tables
- use `${base}_meta` for metadata tables
- use `tbl_{entity_a}_{entity_b}` for relation tables
- use `${base}_{suffix}` for justified trace or extension tables

Examples:
- `tbl_post`
- `tbl_post_lang`
- `tbl_post_meta`
- `tbl_post_tag`
- `tbl_press_log`

### Field Naming
- primary key: `id`
- foreign keys: `${target}_id`
- subordinate ownership: `parent_id`
- audit timestamps: `insert_ts`, `last_ts`
- audit actors: `insert_user`, `last_user`
- lifecycle state: `status`
- ordering field: `sorter`

Avoid:
- camelCase in schema
- screen-specific field names
- synonyms for standard audit fields
- vague field names that only make sense in one UI

## Responsibility Boundaries That Affect DBA Decisions

### Feed Owns Data Lifecycle
Schema should support the entity's Feed as the primary owner of:
- persistence
- retrieval
- language handling
- metadata handling
- relation save behavior
- transaction ownership

### Reaction Should Not Force Schema Drift
Do not design tables around one Reaction action or transport detail. Reaction is not the long-term owner of schema decisions.

### Kit Is Not a Database Dumping Ground
Do not create schema structures just because logic is long. Entity-specific logic stays module-owned; only true infrastructure belongs in helpers or `libs`.

### `libs` Is Not a Persistence Owner
If logic involves entity truth, payload ownership, workflow or duty judgment, task writeback, or audit persistence, it belongs to the owning module, not to `libs`.

## Workflow / Rule Engine Related DBA Rules

### WorkflowEngine Pattern
- `WorkflowEngine` is shared runtime under `libs`
- module owns workflow definition source
- module owns workflow audit persistence
- Feed owns writes and transactions
- do not create shared workflow persistence tables unless the project explicitly reopens that decision

### EventRuleEngine Pattern
- `EventRuleEngine` is shared pure engine under `libs`
- owning modules provide payload, context preload, truth write, and writeback coordination
- do not collapse `duty`, `task`, `member`, `press`, or `manaccount` persistence into an EventRuleEngine-owned schema

### DBA Inference Rule
If a proposal says an engine should own tables, logs, or runtime state merely because the engine is shared, reject that assumption unless the architecture has been explicitly reopened.

## Query and Indexing Rules

Schema design in F3CMS must support realistic Feed query patterns.

### Prefer Queryable Structure Over JSON Convenience
If the data has relation, filter, reporting, or authorization semantics, model it relationally.

### Index Candidate Heuristics
Fields are likely index candidates when they are:
- frequently filtered
- frequently joined
- used for direct lookups
- used for ordering on large lists
- used as uniqueness guards

Typical candidates include:
- `slug`
- `status`
- `${target}_id`
- relation table foreign keys
- unique language keys such as `(lang, parent_id)`

### Do Not Hide Hot Fields In `_meta`
If a future query will need efficient filtering or joining, the field should not be pushed into `_meta` merely to avoid a schema decision.

### Count and Pagination Awareness
Paginated lists usually require both result queries and count queries. Avoid schema choices that make both expensive unnecessarily.

## SQL Delivery Convention

When the DBA-oriented output requires executable SQL:

- create or append to `document/sql/YYMMDD.sql`
- do not append ad hoc change SQL to `document/sql/init.sql`
- keep `init.sql` as baseline bootstrap schema

The DBA output should state:
- why the change is needed
- which entity owns the change
- which tables are new or altered
- whether data backfill is required
- whether indexes or uniqueness constraints are required
- whether rollback or data safety concerns exist

## What an LLM DBA Should Produce

When proposing or reviewing a schema change, the LLM DBA should produce all of the following where applicable:

1. Entity identification.
2. New module vs existing module decision.
3. Table decomposition decision.
4. Field placement rationale.
5. Naming alignment check.
6. Feed and lifecycle alignment implications.
7. Query and indexing implications.
8. Anti-pattern warnings.
9. SQL delivery destination.
10. Concrete DDL or migration outline if the change is approved.

## Recommended DBA Output Template

Use this structure when answering as DBA:

1. Entity
   - What entity is being changed?
   - Is this a new module or an extension of an existing one?

2. Schema Decision
   - Which tables should be added or changed?
   - Why does each field belong in main / `_lang` / `_meta` / relation / trace?

3. Naming and Ownership
   - Do table and field names follow F3CMS conventions?
   - Which module owns the tables and write lifecycle?

4. Query and Index Impact
   - What joins, filters, counts, or list behaviors does this schema need to support?
   - Which fields likely need indexes or uniqueness constraints?

5. Delivery
   - Which file under `document/sql/` should receive the SQL?
   - Is backfill, migration ordering, or rollback planning required?

## Anti-Patterns the LLM DBA Must Reject

Reject or challenge proposals that do any of the following unless the repository explicitly reopens the architecture:

- create schema from page layout instead of entity boundary
- create a new module only because a new screen exists
- store real relations in JSON or comma-separated text
- put language-varying content in the main table
- use `_meta` to avoid making a structural schema decision
- bypass Feed lifecycle with undocumented direct writes
- invent shared workflow runtime tables by default
- move entity-specific persistence responsibility into `libs`
- create naming that cannot be inferred from the module or entity

## Quick DBA Reading Path

If the LLM DBA has limited context, read in this order:

1. [data_modeling.md](data_modeling.md)
2. [module_design.md](module_design.md)
3. [sd_conventions.md](sd_conventions.md)
4. [query_and_performance.md](query_and_performance.md)
5. [data_architecture_checklist.md](data_architecture_checklist.md)
6. [document/sql/init.sql](../sql/init.sql)

This document exists so that, in most cases, the LLM DBA can start here and then dive into the exact underlying guide only when a decision needs more detail.