# F3CMS Feed Guide

## Purpose
- Document the Feed layer as the primary data access and lifecycle abstraction for entities.
- Teach engineers how to use Feed consistently with Medoo/PDO-backed storage.
- Reduce incorrect placement of data logic in Reaction or Outfit.

## Primary Readers
- Backend programmers
- SD
- LLMs generating data access code

## Scope
- Feed constants
- save lifecycle
- query patterns
- multilingual handling
- metadata and relation handling
- error handling and debugging

## LLM Reading Contract
- Treat Feed as the canonical place for entity data access rules.
- Prefer Feed methods over raw ad hoc query logic unless there is a strong documented reason.
- Read this guide before proposing custom persistence logic.

## Inputs
- [www/f3cms/libs/Feed.php](../../www/f3cms/libs/Feed.php)
- [data_modeling.md](data_modeling.md)
- [query_and_performance.md](query_and_performance.md)

## Core Thesis
- Feed is the primary execution layer of the entity data model.
- If data_modeling.md explains how the schema should be shaped, Feed explains how that schema behaves in code.
- Feed is not only a query helper. It is the canonical implementation layer for entity persistence, retrieval, filtering, metadata handling, language handling, and relation-aware access patterns.

## Role of Feed in F3CMS

Feed should be understood as the data lifecycle layer for an entity.

It does more than read and write rows. It converts the entity model into repeatable code behavior for:
- saving entity fields
- splitting request payloads into main, language, meta, and relation data
- loading one row or many rows consistently
- building default joins and filters
- protecting reserved fields from unsafe writes
- logging and surfacing SQL failures through a unified path

If Module is the architectural base class and module_design.md explains module boundaries, Feed is the layer where the entity model becomes operational.

This is why data logic should usually be added to Feed rather than to Reaction or Outfit:
- Reaction should coordinate requests, not own data structure rules
- Outfit should orchestrate rendering, not define persistence semantics
- Feed is where entity structure, query patterns, and save lifecycle remain stable

## How Feed Reflects the Data Model

The schema patterns described in [data_modeling.md](data_modeling.md) map directly into Feed responsibilities.

### Main Table Mapping
- `MTB` identifies the main table base name
- `fmTbl()` derives physical table names from that base
- `save()`, `one()`, `lots()`, and `limitRows()` treat the main table as the root entity table

### Language Table Mapping
- `MULTILANG` signals whether the entity uses a `_lang` table
- `lotsLang()` and `saveLang()` implement language persistence and loading
- `one()` can merge or attach localized content depending on the requested behavior

### Meta Table Mapping
- `lotsMeta()` and `saveMeta()` implement the `_meta` extension model
- metadata remains attached to the entity without polluting the main table

### Relation Table Mapping
- helper methods such as `lotsTag()` show the preferred relation-table access pattern
- module-specific relation helpers should live in Feed so relation semantics stay close to the entity

This makes Feed the bridge between schema decomposition and runtime behavior.

## Core Constants

Feed constants are not just convenience values. They encode key assumptions about the entity.

### MTB
- Defines the main table base name for the entity.
- `fmTbl()` uses it to build main, lang, meta, and relation table names.

Practical meaning:
- if `MTB = 'post'`, then the main table is `tbl_post`
- the language table becomes `tbl_post_lang`
- the metadata table becomes `tbl_post_meta`

### MULTILANG
- Declares whether the entity supports a `_lang` table.
- Controls how `one()`, `genJoin()`, and localized helper methods behave.

Practical meaning:
- `MULTILANG = 1` means the entity is structurally multilingual
- `MULTILANG = 0` means the entity is single-language or language-neutral

### BE_COLS
- Defines default backend list columns.
- Used by `limitRows()` and related listing flows.

Practical meaning:
- this is part of the admin-facing read model
- a poor `BE_COLS` choice often leads to unnecessary extra queries later

### PK_COL
- Defaults to `id`
- Useful when special primary key logic is needed

### PV_R, PV_U, PV_D
- Express read, update, and delete permission values
- Used by higher layers such as Reaction for access control

### Other Shared Constants
- pagination defaults
- hard delete policy
- multilingual merge/attach modes

When creating a new Feed, these constants should be treated as part of the entity contract.

## Save Lifecycle

The save path in Feed is one of the most important implementation flows in F3CMS.

### Step 1: `save()`

`save()` is the main entry point for insert and update operations.

It does the following:
- resolves the called class
- delegates request normalization to `_handleColumn()`
- decides whether the operation is insert or update
- writes the main table row
- triggers `_afterSave()` for follow-up persistence

This means `save()` is not just an insert/update wrapper. It is the orchestration point for the full entity write lifecycle.

### Step 2: `_handleColumn()`

`_handleColumn()` separates incoming request data into two groups:
- `data`: fields that belong directly to the main table
- `other`: structured side-channel data such as `meta`, `tags`, and `lang`

It also applies special handling to important fields:
- `slug` is normalized
- `pwd` is hashed
- `online_date` is normalized to datetime
- arrays may be encoded or restructured depending on the field
- `id` is skipped from direct writes

This method is where entity-aware request normalization happens. If a field needs custom preprocessing before storage, Feed is usually the right place to add it.

### Step 3: Main Table Write

After `_handleColumn()` returns, `save()` writes the main row:
- insert if there is no `id`
- update if `id` exists

Audit fields are assigned automatically:
- `insert_ts`, `insert_user` on insert
- `last_ts`, `last_user` on every write via `_handleColumn()`

### Step 4: `_afterSave()`

`_afterSave()` persists deferred parts of the entity model.

Out of the box, it handles:
- metadata via `saveMeta()`
- tags via `saveMany()`
- language rows via `saveLang()`

This reflects the decomposed schema design of F3CMS. Main table data is not the whole entity; `_afterSave()` finishes the entity write.

### Design Implication

If you bypass `save()` and write directly to tables in ad hoc ways, you are usually bypassing the entity lifecycle and risking partial saves.

## Read Lifecycle

Feed provides several read patterns, each aligned to a different usage scenario.

### `one()`
- Reads a single entity row from the main table.
- Can optionally merge or attach language data depending on the multilingual mode.

Use `one()` when:
- you need a canonical single-entity lookup
- you want Feed to respect the entity's multilingual behavior

### `lots()`
- Reads a collection of rows with optional joins and limits.
- Useful for direct condition-based reads where full pagination is unnecessary.

Use `lots()` when:
- you need a bounded batch query
- you already know the selection conditions

### `limitRows()`
- Builds a paginated backend-oriented list using `BE_COLS`, `genFilter()`, and `genJoin()`.

Use `limitRows()` when:
- you are building admin list pages
- you want to reuse module-specific filters and joins

### `paginate()`
- General pagination engine used by higher-level list methods.
- Handles total counts, limits, ordering, result slices, and debug SQL output.

### `total()` and `_total()`
- Count-based helpers for reporting or pagination support.

### Design Implication

Choosing the right read method matters because each one encodes assumptions about entity behavior, joins, and result shape.

## Language Handling

Language handling is where Feed most clearly embodies the schema model.

### `lotsLang()`
- Reads all language rows or one language row for a parent entity
- strips structural columns so callers get usable localized data

### `saveLang()`
- upserts language rows for a given parent entity
- adds audit fields consistently
- updates existing language rows or inserts new ones as needed

### `MULTILANG` in `one()`

`one()` supports multiple multilingual behaviors:
- attach all language rows separately
- merge the current language into the main row
- skip language enrichment

This is important because different use cases need different read shapes:
- backend editors may need all languages
- frontend display usually needs the current language only
- internal logic may only need the main row

### Practical Rule

If a field is localizable, the Feed should not invent one-off custom loading logic outside the `_lang` pattern. Use the existing language helpers instead.

## Metadata and Relation Handling

### `lotsMeta()`
- Loads metadata rows and folds them into a key-value map

### `saveMeta()`
- Persists metadata as structured key-value rows
- supports replace behavior to overwrite known keys safely

### Relation Patterns

Feed already shows relation handling through helpers like `lotsTag()` and relation-aware query helpers. This pattern should be extended for other relations such as authors, books, or custom entity links.

Good relation helper methods in Feed usually:
- start from the current entity
- express relation semantics clearly
- use explicit relation tables
- return a shape useful to higher layers without hiding the relationship model

### Practical Rule

If another entity is part of the current entity's read model, relation helper methods belong in Feed, not in controllers or views.

## Query Construction Patterns

Feed supports a reusable query model so each module does not need to reinvent list filtering rules.

### `genFilter()`
- Converts query input into Medoo-compatible filter arrays
- centralizes filtering logic per entity

### `_handleQuery()` and `adjustFilter()`
- parse compact query string syntax
- normalize operators and special fields
- add ordering defaults
- resolve relation-aware filters such as tags

### `genJoin()`
- Defines the default joins for the entity
- especially important for multilingual entities

### `genOrder()`
- Provides a default ordering policy for entity lists

### Design Implication

These methods allow each Feed to define a consistent read model. If engineers build list filters outside these hooks, backend behavior becomes inconsistent across modules.

## Column Filtering and Safety Rules

Feed includes built-in protection against unsafe or structurally incorrect writes.

### `default_filtered_column()`
- protects reserved fields such as `id`, audit fields, and timestamps from arbitrary mutation

### `filtered_column()`
- allows module-specific extension of the protected column list

### `filterColumn()`
- determines whether a request field may be written to the main table

### `saveCol()`
- updates one column only if it passes safety checks

### Why This Matters

These methods enforce schema discipline. They reduce accidental corruption of structural fields and keep module-specific protected columns centralized.

## Error Handling and Observability

Feed centralizes SQL failure behavior through `chkErr()`.

### `chkErr()`
- inspects Medoo error state
- logs SQL errors and the last query
- returns null in non-debug mode
- prints detailed failure context in debug mode

### Why This Matters

Without a shared error path, data layer failures become inconsistent and hard to trace. `chkErr()` ensures that Feed methods either succeed predictably or fail through a common observability path.

### Debug Signals to Use
- SQL error logs
- last query inspection
- paginated result debug SQL when debug mode is enabled

## Extension Patterns for Custom Feeds

The base Feed is designed to be extended, not copied.

### Override `filtered_column()` when
- certain entity fields must be protected from direct assignment

### Override `genJoin()` when
- the entity has a standard list view that needs relation or language joins beyond the default behavior

### Override `genOrder()` when
- the entity's natural ordering differs from the default insert timestamp descending order

### Add Entity-Specific Helper Methods when
- the relation is part of the entity's normal read or write model
- the helper expresses real domain semantics

Examples include:
- option lists
- relation lookups
- domain-specific totals or filtered subsets

### Keep the Extension Rule Simple

Extend Feed to make entity behavior clearer, not to hide unrelated business workflows.

## How Data Modeling Becomes Feed Behavior

This is the key bridge between the higher-level documents and actual implementation.

### If a field belongs in the main table
- it should normally flow through `_handleColumn()` into `data`

### If a field belongs in `_lang`
- it should be carried through `other['lang']` and finalized in `saveLang()`

### If a field belongs in `_meta`
- it should be carried through `other['meta']` and finalized in `saveMeta()`

### If a field belongs to a many-to-many relation
- it should be normalized into relation IDs and saved through relation-aware helpers in `_afterSave()` or module-specific save flows

This is why Feed is the execution model of the schema. It turns conceptual decomposition into a real save and query lifecycle.

## Anti-Patterns

Avoid the following patterns:

### Placing Write Orchestration in Reaction
If Reaction starts deciding how data splits across main, lang, meta, and relations, the entity lifecycle is no longer centralized.

### Bypassing Standard Save Flows
Direct ad hoc writes often skip metadata, language rows, or audit fields.

### Mixing Presentation Mapping into Feed
Feed should support the read model, but it should not become a page-HTML preparation layer.

### Writing Raw SQL by Default
Raw SQL is acceptable when necessary, but it should not replace stable Feed abstractions without reason.

### Repeating Query Semantics Across Modules
If filtering, joins, and sort behavior are repeatedly reimplemented outside Feed, module consistency degrades.

## Practical Checklist

Before adding or changing Feed logic, verify the following:

1. Which entity does this Feed represent?
2. Does the change belong to main, lang, meta, or relation behavior?
3. Should the logic modify `_handleColumn()`, `_afterSave()`, or a helper method instead of Reaction?
4. Should default joins or ordering be updated?
5. Is the field safe for direct assignment?
6. Does the new query respect multilingual and relation patterns?
7. Would the resulting behavior still make sense if the frontend changed?

If the answer to question 7 is no, the logic may be too UI-driven to belong in Feed.

## Related Documents
- [data_modeling.md](data_modeling.md)
- [query_and_performance.md](query_and_performance.md)
- [sd_conventions.md](sd_conventions.md)

## Status
- Draft v1 complete
