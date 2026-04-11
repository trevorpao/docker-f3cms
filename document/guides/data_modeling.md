# F3CMS Data Modeling Guide

## Purpose
- Explain F3CMS data architecture from the perspective of entities.
- Standardize how engineers decide between main tables, lang tables, meta tables, and relation tables.
- Serve as the primary source for data structure design before coding.

## Primary Readers
- SD
- Backend programmers
- SA who need data-aware requirement decomposition
- LLMs generating schemas or recommending module boundaries

## Scope
- Entity identification
- Table decomposition rules
- Field naming rules
- Audit fields
- Relationship modeling
- Multi-language handling

## LLM Reading Contract
- Treat "Entity" as the primary modeling unit.
- Treat one entity as mapping to one module unless explicitly documented otherwise.
- Prefer normalized relational modeling over JSON blobs when the data has query or relationship semantics.
- Use this file as the highest-priority guidance for schema design questions.

## Inputs
- [overall.md](overall.md)
- [create_new_module.md](create_new_module.md)
- [document/sql/init.sql](../sql/init.sql)
- [www/f3cms/libs/Feed.php](../../www/f3cms/libs/Feed.php)

## Key Concepts
- Entity
- Module boundary
- Main table
- Language table
- Meta table
- Relation table
- Audit fields
- Stable field vs localized field vs extensible field

## Core Thesis
- Entity is the core unit of F3CMS.
- One entity normally maps to one module.
- Data architecture should therefore begin with entity identification, not with page layout or API shape.
- Module structure, schema decomposition, and Feed methods are all downstream consequences of the entity model.

## Why Entity-First Modeling Matters

In F3CMS, a page is not the primary source of truth. A route is not the primary source of truth. An API action is not the primary source of truth. The primary source of truth is the business entity.

An entity is the stable unit of business meaning. It usually has:
- a name that remains meaningful even if pages change
- a lifecycle or status model
- a storage boundary
- ownership or permission implications
- relationships to other entities

Examples from the existing system include content entities such as Post, Press, Tag, Author, and system entities such as Staff, Role, Menu, Option, and Contact. Newer business-oriented entities such as Draft and Conversation also fit this pattern because they represent stable business objects rather than temporary UI behavior.

The reason this matters is simple:
- page-first design creates duplicate logic
- API-first design often creates unstable schemas
- UI-first design tends to push short-term fields into the wrong tables

Entity-first design keeps the system stable because the entity usually survives page redesigns, frontend rewrites, and API shape changes.

## Entity to Module Mapping Rules

The default rule in F3CMS is:
- one entity maps to one module

This is not only an organizational preference. It is the mechanism by which the system preserves clear ownership of data access, backend actions, frontend rendering, and validation rules.

Create a new module when most of the following are true:
- the business object has its own main table
- it has an independent lifecycle or status
- it has independent backend actions
- it has its own permission or ownership model
- it is likely to be queried directly, not only through a parent entity

Extend an existing module when most of the following are true:
- the new data is clearly subordinate to an existing entity
- it does not introduce an independent lifecycle
- it is only an attribute, localized field, metadata field, or relation of an existing entity
- it would be unnatural to query or manage it separately

Examples:
- adding SEO fields to Post is an extension of Post, not a new module
- adding Draft as an LLM-generated working artifact is a new module because it has its own state and business lifecycle
- adding Conversation as AI interaction mapping is a new module because it has separate persistence and reuse behavior

## Table Decomposition Model

F3CMS does not assume that one entity equals one table. Instead, it uses a decomposed relational model where each table type serves a specific purpose.

### Main Table

The main table stores stable, non-localized, query-relevant fields for the entity.

Typical contents:
- `id`
- `status`
- `slug`
- `layout`
- `cover`
- `sorter`
- ownership fields such as `owner_id`
- audit fields such as `insert_ts`, `last_ts`, `insert_user`, `last_user`

The main table should remain stable and semantically dense. It should not become a catch-all store for every display-oriented or localized field.

### Language Table

The language table uses the `_lang` suffix and stores fields that vary by language.

Typical contents:
- `title`
- `subtitle`
- `summary`
- `info`
- `content`
- `alias`
- `keyword`

This design allows one entity to support multiple languages without changing the main table schema every time a language is added.

### Meta Table

The meta table uses the `_meta` suffix and stores extensible key-value attributes that are valid but not stable enough for the main table.

Typical use cases:
- SEO fields
- configurable UI support text
- optional feature attributes
- low-frequency extension data

Meta is not a garbage bin. It is an explicit extension layer. If a field is frequently filtered, sorted, constrained, or joined, it usually belongs in the main table instead.

### Relation Table

Relation tables store many-to-many relationships between entities.

Typical examples:
- `tbl_post_tag`
- `tbl_press_author`
- `tbl_category_tag`

If the data expresses a real relationship, it should be modeled relationally instead of being embedded in a JSON string.

### Trace or Extension Table

Some entities need additional non-lang, non-meta secondary tables that capture traces, histories, or specialized sub-structures.

Examples:
- `tbl_press_log`
- raw/import/log-like extensions

Use this pattern when the data is structurally meaningful and grows independently, but is still clearly subordinate to the parent entity.

### Module-owned Workflow Log Pattern

When an entity adopts workflow control, its audit trail should normally be stored in a module-owned log table rather than in a shared workflow runtime table.

Typical rules:
- the log remains subordinate to the entity's module
- the conceptual name may be `press_log` or `order_log`
- the physical table name should still follow F3CMS naming conventions such as `tbl_press_log`
- the table should be designed for auditability, not as a generic replacement for the entity's main table

Recommended minimum fields:
- `parent_id`
- `insert_user`
- `action_code`
- `old_state_code`
- `new_state_code`
- `insert_ts`

Optional fields when the workflow path needs richer traceability:
- `remark`
- `branch_token`
- `parallel_group_code`
- `join_group_code`
- `extra_context_json`

Do not introduce a shared `tbl_workflow_instance` or `tbl_workflow_instance_trace` style schema unless the project explicitly reopens that architecture decision. In current F3CMS practice, workflow runtime context is assembled from module-owned business data plus module-owned logs.

## Field Placement Decision Rules

When adding a field, do not start with "where is it easiest to store". Start with "what kind of data is this".

### Put the field in the Main Table when
- it is not language-specific
- it is central to the entity's identity or lifecycle
- it will be filtered, sorted, or indexed often
- it affects visibility, ownership, or operational behavior
- it needs strong structural stability

Examples:
- `status`
- `slug`
- `layout`
- `mode`
- `online_date`
- `owner_id`

### Put the field in the `_lang` Table when
- the value changes by language
- the field is part of localized content presentation
- translators or content editors will manage language-specific versions independently

Examples:
- `title`
- `content`
- `summary`
- `info`

### Put the field in the `_meta` Table when
- the field is optional or feature-specific
- it is unlikely to be universally required for all rows
- it is semantically attached to the entity but not structurally central
- it is not a first-class filter or sorting column

Examples:
- optional SEO configuration
- CTA labels
- special display settings

### Put the data in a Relation Table when
- the value is actually another entity reference
- multiple rows may be linked on both sides
- relationship order or sorter may matter
- querying the relationship matters

Never use JSON to represent a real relation if the relation needs filtering, reporting, authorization, or structured editing.

## Naming Conventions

F3CMS relies on naming consistency because the codebase is heavily convention-driven.

### Table Naming Rules
- always use the `tbl_` prefix
- use lowercase with underscores
- use singular semantic names where possible
- use `_lang` for localized tables
- use `_meta` for metadata tables
- use `tbl_{entity_a}_{entity_b}` for many-to-many relations

Examples:
- `tbl_post`
- `tbl_post_lang`
- `tbl_post_meta`
- `tbl_post_tag`

### Field Naming Rules
- always use lowercase with underscores
- primary key is always `id`
- foreign keys follow `${target}_id`
- child records of an entity use `parent_id`
- timestamps use `insert_ts` and `last_ts`
- audit users use `insert_user` and `last_user`

### Naming Alignment Rule
- API field names should match database field names when possible
- avoid unnecessary translation layers between payloads and schema

Naming consistency reduces code branching and lowers mental overhead for both humans and LLMs.

## Audit and Lifecycle Fields

Most F3CMS entities should support basic auditability and lifecycle management.

### Required or Strongly Recommended Audit Fields
- `insert_ts`
- `last_ts`
- `insert_user`
- `last_user`

### Lifecycle Fields
- `status`
- `online_date` or equivalent when content has publish timing
- `sorter` when ordering matters in backend or frontend behavior

### Why These Fields Matter
- they preserve operational accountability
- they simplify list sorting and admin workflows
- they support debugging and traceability
- they keep behavior consistent across modules

If a new entity is meant to be managed, reviewed, published, retried, archived, or audited, these fields should be part of the design from the beginning.

## Relationship Modeling Patterns

F3CMS uses relational modeling to make entity connections explicit.

### Tag Relations
- use a relation table such as `tbl_post_tag`
- keep relation semantics queryable
- add sorter if order matters

### Author Relations
- use relation tables such as `tbl_press_author`
- do not duplicate author labels inside content rows if author is its own entity

### Self-Referential Relations
- use a dedicated relation table when an entity refers to peers
- examples include related content such as `tbl_press_related`

### Hierarchical Relations
- use `parent_id` when an entity has a tree-like or nested structure
- examples include Menu and Tag

### Do Not Model These as JSON
- tag lists
- author lists
- related articles
- nested menu relationships

If the data is relational in meaning, store it relationally.

## Multi-language Design Patterns

Multi-language support in F3CMS is structural, not cosmetic.

This means:
- multilingual support starts in the schema
- Feed behavior reflects this through `MULTILANG`
- query and save paths are aware of `_lang` tables

### Translatable vs Non-Translatable Rule

Ask these questions for each field:
- will the value differ by language?
- is the field intended for display to end users?
- will translators need to manage it independently?

If the answer is yes, it probably belongs in `_lang`.

### Common Mistakes
- putting `title` in the main table and `content` in `_lang`
- mixing localized and non-localized copies of the same field
- introducing one-off translated fields that bypass the `_lang` model

### Code Impact

Because Feed methods such as `one()`, `lotsLang()`, and `saveLang()` assume the `_lang` pattern, schema decisions directly affect the code path. Incorrect placement increases special-case logic and reduces reuse.

## Examples from Existing Modules

### Post
- main table stores status, slug, cover, layout
- lang table stores title and content
- meta table stores optional extra attributes
- relation table stores tags

### Press
- main table stores publication and display control fields
- lang table stores title, keyword, info, content
- relation tables connect authors, books, tags, related items, and terms
- trace table stores follow-up or tracking information

### Menu
- main table stores hierarchy and routing fields
- lang table stores title, badge, and display info
- relation table may link tags

### Tag
- main table stores taxonomy behavior and hierarchy
- lang table stores translated presentation labels
- related table supports cross-tag relationships

### Conversation and Draft
- these show that F3CMS is not limited to classic CMS content
- they still follow the entity-first model because they have state, storage, and operational meaning

## Anti-Patterns

Avoid the following patterns unless there is a very strong, explicitly documented reason:

- storing relation semantics in JSON fields
- storing localized fields directly in main tables
- creating schemas from a single screen's needs
- naming fields differently across API, DB, and module logic without a strong reason
- turning meta tables into a dump area for structurally important fields
- creating new modules for what is only a child attribute of an existing entity

These anti-patterns usually produce short-term speed at the cost of long-term inconsistency.

## Design Checklist

Before creating or changing a table, answer these questions:

1. What is the entity?
2. Does this entity already exist as a module?
3. Is this data stable, localized, extensible, or relational?
4. Should the field live in main, `_lang`, `_meta`, or a relation table?
5. Does the entity require status, audit, and sorting fields?
6. Will the data be filtered, sorted, indexed, or joined often?
7. Does this design preserve consistency with existing F3CMS modules?
8. Would the same schema still make sense if the page layout changed?

If the answer to question 8 is no, the model is probably too page-driven.

## Expected Outputs
- A repeatable schema decision process.
- Shared language for SA, SD, and programmers.

## Related Documents
- [module_design.md](module_design.md)
- [feed_guide.md](feed_guide.md)
- [data_architecture_checklist.md](data_architecture_checklist.md)

## Status
- Draft v1 complete
