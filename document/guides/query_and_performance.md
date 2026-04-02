# F3CMS Query and Performance Guide

## Purpose
- Explain how F3CMS uses Medoo and PDO for practical, efficient relational data access.
- Document the performance implications of schema design and query patterns.
- Help engineers avoid slow or structurally expensive implementations.

## Primary Readers
- Backend programmers
- SD
- LLMs recommending query strategies

## Scope
- Medoo positioning
- PDO-backed execution model
- query selection patterns
- join tradeoffs
- indexing and schema implications

## LLM Reading Contract
- Do not treat Medoo as a full ORM.
- Prefer simple relational queries that respect the entity model.
- Always connect performance advice back to schema design, not only to syntax.

## Inputs
- [www/f3cms/libs/Feed.php](../../www/f3cms/libs/Feed.php)
- [document/sql/init.sql](../sql/init.sql)
- [data_modeling.md](data_modeling.md)

## Core Thesis
- F3CMS uses Medoo on top of PDO to achieve a practical balance between control, readability, and performance.
- Query performance in F3CMS is not primarily a syntax problem. It is first a data model problem, then a query design problem.
- The best performance improvements usually come from correct entity decomposition and stable Feed query patterns, not from premature micro-optimizations.

## Why F3CMS Uses Medoo + PDO

F3CMS does not use a heavyweight ORM as its primary data abstraction. Instead, it uses Medoo as a lightweight query builder over PDO.

This choice reflects several design goals:
- preserve direct awareness of relational data structures
- avoid excessive ORM magic that hides joins and query costs
- keep queries readable and compact
- retain enough control to optimize schema-aware access patterns
- support a convention-driven Feed layer without over-abstracting SQL realities

### What This Means in Practice

Medoo is not intended to replace relational thinking. It is a structured way to express relational operations.

PDO remains important because:
- it provides stable database access behavior
- it supports prepared execution and parameter binding
- it gives the project a clear fallback path when raw SQL is necessary

The result is a middle path:
- not raw SQL everywhere
- not heavy ORM indirection everywhere
- but a lightweight data layer that stays close to actual schema behavior

## How to Think About Performance in F3CMS

Performance in F3CMS should be evaluated in this order:

1. Is the entity modeled correctly?
2. Is the data placed in the correct tables?
3. Is the query using the right Feed abstraction?
4. Are joins and filters structurally appropriate?
5. Only then, is the SQL syntax itself worth optimizing further?

This order matters because bad schema design cannot be fixed reliably by clever query code.

For example:
- if many-to-many relations are stored as JSON, no Medoo optimization will restore proper queryability
- if localized fields are stored in the main table, multilingual query behavior becomes structurally messy
- if metadata and core fields are mixed arbitrarily, filtering and indexing become unpredictable

## Query Entry Points in Practice

F3CMS provides several ways to query data. Choosing the right entry point is a design decision.

### Prefer Feed Wrappers First

Use Feed-level methods first when the query is part of the entity's normal lifecycle.

Typical methods include:
- `one()`
- `lots()`
- `limitRows()`
- `paginate()`
- `total()`
- entity-specific helper methods such as relation lookups

Use Feed wrappers when:
- the query belongs to the entity model
- the query should respect multilingual behavior
- the query should reuse standard filters, joins, or sorting
- the query is likely to be reused

### Use `exec()` When the Query Is Truly Custom

`exec()` is the escape hatch for custom SQL that does not fit the standard Feed methods cleanly.

Use `exec()` when:
- the query is structurally unusual
- reporting or aggregation logic is too specific for standard wrappers
- expressing the query in Medoo would be less clear than SQL itself

Do not use `exec()` by default. Use it when its clarity or control is better than forcing the query through generalized helpers.

### Use Medoo Joins for Stable Relational Reads

Use Medoo joins when:
- the relation is part of the entity's regular read model
- multilingual joins are standard for the entity
- list pages need predictable joined columns

This keeps relational behavior explicit and close to the Feed implementation instead of scattering join logic across controllers.

## Performance Starts with Schema

The most important performance decision in F3CMS is still schema design.

### Correct Entity Decomposition

An entity should be decomposed into:
- main table for stable operational fields
- `_lang` table for localized fields
- `_meta` table for extensible key-value attributes
- relation tables for many-to-many associations

This decomposition improves performance because:
- main tables stay compact
- localized content does not bloat non-localized reads
- metadata stays out of hot operational columns
- relationships remain queryable and indexable

### Proper Placement of Localized Data

Putting localized content in `_lang` tables improves performance by keeping common operational queries smaller and more focused. It also allows joins only when localized output is needed.

### Proper Use of Relation Tables

Relation tables are not only a normalization preference. They are also a performance feature because they:
- allow indexed joins
- support efficient many-to-many lookups
- avoid parsing and filtering large blobs or JSON payloads

### Avoiding JSON for Relational Semantics

JSON is a poor substitute for relations when the system needs:
- filtering
- sorting
- reporting
- referential reasoning
- reuse across modules

If the data is relational in meaning, using JSON will eventually make both correctness and performance worse.

## Join Strategy Patterns

Joins are necessary in F3CMS, especially for multilingual and relation-driven entities. The goal is not to avoid joins entirely, but to use them intentionally.

### Single-Entity Reads

For a single entity lookup:
- start with `one()`
- only merge language content if the caller needs localized output
- avoid loading all relations unless the use case actually requires them

### List Pages with Language Joins

For backend or frontend list pages:
- use `limitRows()` or `paginate()` with the entity's `genJoin()` pattern
- keep `BE_COLS` aligned with the list page's true needs
- avoid joining large text-heavy columns unnecessarily

### Relation Lookups

For related data such as tags or authors:
- prefer dedicated Feed helper methods
- keep relation joins explicit
- avoid repeated row-by-row relation reads when a bulk relation query can be used

### Count Queries

Counts matter for pagination and dashboards. They should be treated differently from full row queries.

F3CMS already separates count logic through `_total()` and `total()`. This is good because count queries often need leaner columns and different join costs than full data retrieval.

## Pagination and List Queries

List performance is one of the most visible performance areas in a CMS-like system.

### `limitRows()`

`limitRows()` provides a module-aware list path that combines:
- `BE_COLS`
- `genFilter()`
- `genJoin()`
- pagination logic

Use it for standard admin and index views where the entity defines a stable list shape.

### `paginate()`

`paginate()` does the heavy lifting for:
- total count
- page bounds
- limit slicing
- result retrieval
- debug SQL visibility

### Count Costs

Every paginated query usually implies an additional total-count query. That means:
- overly complex filters raise the cost of both the count query and the result query
- unnecessary joins can double the pain because they affect both phases

### Sorting Implications

Sorting should be designed, not improvised:
- use indexed or operationally meaningful fields when possible
- define stable default ordering in `genOrder()`
- avoid sorting by large text fields unless absolutely necessary

## Index and Cardinality Considerations

F3CMS relies on relational integrity and predictable keys. The schema should support this with practical indexing.

### Identifiers
- primary keys should be simple and stable
- main table `id` fields should remain the standard primary key unless there is a strong reason otherwise

### Slug and Unique Keys
- unique or frequently searched slugs should be indexed
- if the entity depends on slug-based lookup, that path should be treated as first-class in schema design

### Relation Table Composite Keys
- many-to-many tables should usually use composite keys over the linked ids
- this reduces duplicates and improves relation lookups

### `_lang` Table Uniqueness
- language tables should normally enforce unique `(lang, parent_id)` combinations
- this supports efficient lookups and prevents duplicate localized rows for the same entity-language pair

### Cardinality Awareness

Think about how many rows a relation can produce:
- tag relations may multiply rows quickly in joins
- large metadata sets can cause repeated secondary lookups
- trace or history tables may grow faster than content tables

High-cardinality relations need deliberate query patterns, not just correct syntax.

## Common Slow Patterns

### N+1 Relation Reads

Loading one row, then repeatedly loading related tags/authors/metadata in a loop is a classic source of avoidable cost.

Prefer:
- bulk relation reads
- dedicated relation helper methods
- list-aware read patterns

### Repeated Metadata Queries

Calling metadata lookups row by row for large lists can become expensive. If metadata is part of a list view, consider whether the list shape is over-demanding or whether a more targeted read model is needed.

### Overuse of Raw Ad Hoc Queries

Raw SQL is not inherently bad. But repeated one-off queries spread across Reaction or Outfit often indicate missing Feed abstractions, inconsistent joins, and harder optimization paths later.

### Incorrect Relation Storage

Storing relation semantics as JSON creates slow filtering, poor reuse, and weak indexing. This is one of the highest-cost mistakes because it affects both correctness and performance.

### Overfetching Large Text Fields

Selecting long `content` or `summary` fields in list pages when only titles and statuses are needed increases payload, memory, and rendering cost. Keep list queries narrow.

## Debugging and Measuring

Performance work should be observable.

### Last Query Inspection

Feed pagination helpers can expose the generated SQL in debug mode. This is useful when:
- a list query seems too slow
- joins look suspicious
- unexpected filters are being applied

### SQL Error Logs

`chkErr()` writes SQL failures and the last query to logs. While intended for correctness, this also helps identify broken query patterns and misaligned schema assumptions.

### Debug Mode Usage

Use debug mode to:
- inspect generated SQL
- verify joins
- validate ordering and filters

Do not confuse debug visibility with actual performance measurement. It helps explain the query, but schema reasoning still matters more than raw query text alone.

## Practical Rules of Thumb

### Prefer Feed-Level Reuse
- if a query belongs to the entity, add or refine a Feed method

### Keep Main Tables Lean
- do not move localized or highly optional data into the main table just because it feels convenient

### Query Only What the Use Case Needs
- list pages need list columns
- detail pages can afford richer payloads

### Use Relation Tables as First-Class Structures
- never downgrade real relationships into strings or arrays if they need structured access

### Denormalize Carefully

Denormalization may be acceptable when:
- the read path is dominant and proven hot
- the duplicated field is operationally stable
- the duplication does not destroy the entity model

Do not denormalize just to avoid writing a proper relation or language table.

### Add Feed Helper Methods When Semantics Repeat

If the same relation-aware or filtered query appears multiple times, the correct performance move is often not "write faster SQL in every caller" but "add one correct Feed helper and reuse it".

## Decision Flow for Engineers

When a query feels slow or awkward, ask these questions in order:

1. Is the entity modeled correctly?
2. Is the data in the correct table type?
3. Am I using the right Feed entry point?
4. Is this a single-row, list, relation, or count query?
5. Are my joins necessary and appropriately scoped?
6. Am I fetching too much data for this use case?
7. Would a Feed helper reduce duplication and improve consistency?

This sequence usually leads to better outcomes than starting with low-level SQL tuning.

## Related Documents
- [feed_guide.md](feed_guide.md)
- [data_modeling.md](data_modeling.md)
- [data_architecture_checklist.md](data_architecture_checklist.md)

## Status
- Draft v1 complete
