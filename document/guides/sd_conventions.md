# F3CMS SD Conventions

## Purpose
- Define implementation conventions for SD across modules, database design, and directory structure.
- Keep new work consistent with existing F3CMS architecture and schema patterns.
- Provide a reviewable baseline for technical design decisions.

## Primary Readers
- SD
- Senior backend programmers
- LLMs drafting implementation plans

## Scope
- naming conventions
- directory conventions
- schema conventions
- responsibility boundaries
- delivery expectations for new modules

## LLM Reading Contract
- Prefer convention-aligned designs over novel folder or schema structures.
- Treat consistency with existing module and table patterns as a priority.
- Use this file when deciding where code and tables should live.

## Inputs
- [data_modeling.md](data_modeling.md)
- [module_design.md](module_design.md)
- [create_new_module.md](create_new_module.md)
- [document/sql/init.sql](../sql/init.sql)

## SQL Delivery Convention

When a change requires database schema updates or full-table SQL that must be executed by a DBA, the SQL must be recorded under `document/sql/` in the file for the current date.

Rules:
- use the filename format `YYMMDD.sql`, for example `260412.sql`
- if the current date's SQL file does not exist yet, create it before adding the new SQL
- append same-day schema and DBA-executed table-wide SQL to that day's file instead of scattering them across ad hoc files
- keep `document/sql/init.sql` as environment baseline bootstrap, not as the place for ad hoc daily delivery SQL

This convention exists so schema delivery and DBA handoff have a predictable daily audit trail.

## Core Thesis
- SD work in F3CMS should optimize for consistency, not novelty.
- A correct solution is one that preserves entity boundaries, naming stability, schema clarity, and layer responsibility.
- The SD role is not only to make the new feature work. It is to ensure the new feature fits the existing architectural language of the system.

## Design Principles for SD in F3CMS

### Consistency Over Novelty

When multiple workable designs exist, choose the one that is most consistent with the current F3CMS patterns unless there is a strong architectural reason to deviate.

This matters because F3CMS is convention-driven:
- autoload behavior depends on naming conventions
- module organization depends on stable folder and class naming
- Feed behavior depends on standardized table patterns
- onboarding and LLM assistance both improve when the architecture is predictable

### Entity-First Implementation

Before deciding directory structure, table layout, or API shape, the SD should identify the entity and its boundaries.

The implementation sequence should be:
- identify entity
- decide whether it is a new module or an extension of an existing module
- design schema
- define layer responsibilities
- then define actions and UI

### Schema and Code Alignment

In F3CMS, table design and code design should mirror each other.

If the schema says:
- main table
- `_lang` table
- `_meta` table
- relation table

then the corresponding Feed should expose that structure in its methods and save flows. If schema and code diverge, the system becomes harder to reason about and harder to maintain.

## Directory and Class Naming Rules

F3CMS uses naming conventions as part of its runtime structure. Naming is therefore architectural, not cosmetic.

### Module Folder Naming
- module folders use PascalCase
- the folder name represents the entity name
- the folder should live under `www/f3cms/modules/`

Examples:
- `Post`
- `Press`
- `Draft`
- `Conversation`

### Standard Files Inside a Module
- `feed.php`
- `reaction.php`
- `outfit.php`
- `kit.php`

Not every module needs all four files immediately, but the naming convention remains fixed.

### Class Naming Rules
- Feed class: `f{Module}`
- Reaction class: `r{Module}`
- Outfit class: `o{Module}`
- Kit class: `k{Module}`

Examples:
- `fPost`
- `rPost`
- `oPost`
- `kPost`

These prefixes are not arbitrary. They are part of the autoload and module resolution model.

### When Not to Invent New Structures
- do not create custom subfolders for one module unless the pattern is intentionally being generalized for the codebase
- do not create alternative layer names for one-off convenience
- do not use page names or transport names as replacements for entity names

## Database Naming Rules

Schema names should be predictable enough that developers can infer the table structure from the module and vice versa.

### Main Tables
- always use `tbl_` prefix
- use lowercase and underscores
- use the entity name as the base

Examples:
- `tbl_post`
- `tbl_press`
- `tbl_draft`

### Language Tables
- use `${base}_lang`

Examples:
- `tbl_post_lang`
- `tbl_press_lang`

### Metadata Tables
- use `${base}_meta`

Examples:
- `tbl_post_meta`
- `tbl_media_meta`

### Relation Tables
- use `tbl_{entity_a}_{entity_b}`
- relation table naming should reflect the real entity relationship, not a UI-specific label

Examples:
- `tbl_post_tag`
- `tbl_press_author`

### Trace or Extension Tables
- use the base entity name plus a descriptive suffix

Examples:
- `tbl_press_log`
- import, log, or raw extensions when structurally justified

### Alignment Rule

The table naming convention must align with the Feed `MTB` constant and module name. If a reviewer cannot infer the table from the module name, the naming is probably drifting.

## Field Naming Rules

Field names should support clear inference and minimal translation.

### Core Rules
- use lowercase and underscores
- primary key is `id`
- foreign keys use `${target}_id`
- subordinate row ownership uses `parent_id`

### Audit Field Rules
- `insert_ts`
- `last_ts`
- `insert_user`
- `last_user`

These should be used consistently across entities that are managed or tracked operationally.

### Status and Sorting Rules
- use `status` for lifecycle state
- use `sorter` when order is part of the model
- use explicit enum values where appropriate

### Avoid
- camelCase in schema
- inconsistent aliases between DB and code without strong reason
- vague field names that only make sense in one screen context

## Responsibility Boundaries

This is the most important implementation rule after naming.

### Feed Responsibilities
- persistence
- retrieval
- query defaults
- metadata and language handling
- relation-aware access patterns
- entity-centric helper methods

Feed should own the entity's data lifecycle.

### Reaction Responsibilities
- request handling
- permission checks
- validation orchestration
- calling Feed
- formatting backend responses

Reaction should not own schema decisions or complex persistence rules.

### Outfit Responsibilities
- frontend route orchestration
- preparing data for rendering
- selecting templates or views
- page-level flow decisions

Outfit should not become the main write path for entity data.

### Kit Responsibilities
- validation rules
- lightweight module-owned utilities
- module-owned logic needed by Reaction or Outfit and, when appropriate, reusable by other modules

Kit should not become a duplicate service layer for the entity.

### When to Use Helpers or `libs`

Move logic to helpers or shared libs when:
- it is reused across multiple modules and is not owned by one module's business rules
- it is not owned by one entity or one module
- it is infrastructural rather than entity-specific

Do not move entity-specific logic to generic helpers just because it is long.
Do not move module-owned rules into `libs` too early just because more than one module needs them. If the logic still belongs to one module's boundary, prefer that module's Kit so `libs` does not become bloated.

## Architectural Intent Comment Contract

Architectural intent comments are a maintenance tool in F3CMS, not decorative PHPDoc.

When a core `libs` class or other high-risk shared boundary is clarified or ported, the code comments should protect:
- the layer role the class or function actually owns
- the red lines it must not cross
- the porting shape future runtimes should preserve

### Required Comment Shape

For core classes, prefer a four-part class comment that covers:
- `Architecture Purpose`
- `Design Intent`
- `Strict Constraints`
- `Porting Guidelines`

For functions inside the same class, comments do not all need the same length, but they should at least state:
- the runtime or owner-boundary role of the method
- the main red line, especially when convenience could blur ownership
- a short porting note when the helper shape matters in .NET or Go

Do not treat old-style `@param` / `@return` text as sufficient architectural coverage for core shared classes.

### FORKS-First Boundary Rule

When entity-first structure, reuse convenience, and owner-boundary clarity pull in different directions, preserve FORKS owner boundary first.

Concretely:
- keep entity truth, duty judgment, payload ownership, writeback, and cross-entity business coordination in the owning module
- keep `www/f3cms/libs/` for shared runtime, parser, evaluator, or transport-level helpers only
- do not create a new libs-level coordinator just because logic is reused by more than one module

### P0 Shared Boundary Summaries

The current P0 reference boundaries are:

- `Module`: shared entry/runtime utility base for escaping, request normalization, language/device lookup, slug formatting, and class-path shifting; not an owner-side business coordinator
- `Feed`: entity-owned persistence base for main / `_lang` / `_meta` / relation lifecycle, query defaults, and entity-centric read/write helpers
- `Reaction`: request/orchestration boundary for parsing, permission, validation orchestration, and backend response shaping; not a persistence owner
- `WorkflowEngine`: shared workflow runtime evaluator for definition validation, projection, transition legality, and runtime state calculation; not a workflow persistence owner
- `Smoke`: shared smoke contract dispatcher for module-owned smoke surfaces; not an owner-side assertion collector, context loader, or fallback router
- `Outfit`: shared frontend route/render boundary for lifecycle hooks, static-page delivery, template adapters, and presentation filters; not a persistence owner or owner-side page coordinator

### Shared Global Runtime Surface Rule

Some high-risk shared runtime surfaces in F3CMS are not classes.

The current reference example is:

- `www/f3cms/libs/Utils.php`: a shared global runtime surface that exposes bootstrap and service-locator style helpers such as `f3()`, `mh()`, and `rc()`

When a file like this is clarified or ported:
- do not treat it as a generic utility dump just because it is function-based
- protect the file-level role first, then document each shared function's boundary and porting shape
- keep entity truth, module-owned rules, and business coordination out of these global helpers
- prefer narrowing future dependencies on global helpers rather than expanding them for convenience

If a proposed design contradicts these summaries for convenience, prefer the owner boundary and revise the design rather than widening the shared layer.

## Rights, Roles, and Menu Integration

A valid module design in F3CMS often includes operational integration, not only code and tables.

### Permission Constants

Each Feed can define:
- `PV_R`
- `PV_U`
- `PV_D`

These permission constants should reflect how the entity is managed in backend flows.

### Menu Integration Expectations

If the entity is intended for backend management, SD should assess whether backend menu entries are needed.

This may require:
- menu structure updates
- menu language rows
- role visibility checks

### Admin Usability Considerations

Module design should not stop at table creation. Ask:
- will admins need a list view?
- will they need a save flow?
- will they need option mappings to related entities?
- does the entity need sorting, status toggles, or display labels in the backend?

If yes, the SD design should reflect that from the beginning.

## New Module Delivery Checklist

When designing a new module, SD should assume that the deliverable is not complete unless the following concerns have been reviewed.

### Schema
- main table defined
- lang/meta/relation tables defined where needed
- audit fields included where appropriate
- indexes and uniqueness constraints reviewed

### Feed
- `MTB`, `MULTILANG`, and key constants defined
- standard save/read lifecycle reviewed
- default list behavior considered

### Reaction
- backend operations identified
- permission and validation integration considered

### Outfit
- included only if the entity has frontend page or rendering needs
- route and render implications reviewed

### Validation
- if input constraints matter, Kit rules or equivalent validation path should be planned

### Menu and Role Implications
- admin visibility needs reviewed
- role permissions reviewed
- operational discoverability considered

## Recommended SD Design Sequence

Use this sequence for new work:

1. Identify the entity.
2. Decide new module vs existing module extension.
3. Design the schema using the data modeling rules.
4. Map schema to Feed behavior.
5. Define Reaction and Outfit responsibilities.
6. Define permissions, menus, and validation needs.
7. Review for naming and layer consistency.

If implementation starts before step 3 is stable, architectural drift is likely.

## Common Anti-Patterns

### Data Logic in Reaction
If Reaction starts handling data decomposition, relation semantics, or write orchestration in detail, Feed is being bypassed.

### Write Logic in Outfit
If Outfit becomes a mutation layer, page flow and data lifecycle become entangled.

### Schema Drift
Adding fields or tables without respecting existing naming and decomposition rules creates long-term inconsistency even if the feature works now.

### Inconsistent Naming
Different table, field, and class names for the same concept increase onboarding cost and reduce LLM reliability.

### Page-Driven Module Design
If a proposed module only makes sense because one screen exists, it probably should not be a module.

### Helper-Driven Architecture Drift
If entity logic is repeatedly extracted into random helpers instead of staying in the module's Feed, the module boundary becomes weak.

## Decision Questions for SD

Before finalizing a design, answer these questions:

1. What is the entity?
2. Why is this a new module, or why is it not?
3. What is the main table and what are the extension tables?
4. Which layer owns each part of the behavior?
5. Are naming conventions fully aligned from folder to table to class?
6. Does the design support permissions, backend visibility, and maintenance?
7. Would the same design still make sense if the UI changed?

If question 7 cannot be answered confidently, the design is likely too page-driven.

## Related Documents
- [create_new_module.md](create_new_module.md)
- [feed_guide.md](feed_guide.md)
- [pr_review_checklist.md](pr_review_checklist.md)

## Status
- Draft v1 complete
