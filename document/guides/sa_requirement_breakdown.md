# F3CMS Requirement Breakdown for SA

## Purpose
- Provide SA with a module-first method to decompose requirements.
- Align requirement analysis with entity boundaries, data modeling, and implementation layers.
- Reduce downstream rework caused by page-first or UI-first specifications.

## Primary Readers
- SA
- PM
- SD who collaborate on early solution design
- LLMs helping transform requirements into technical structure

## Scope
- requirement decomposition
- entity identification
- module split decisions
- data model implications
- handoff structure to SD and programmers

## LLM Reading Contract
- Convert requirements into entities before converting them into pages or APIs.
- Prefer describing data shape, lifecycle, and ownership before interface details.
- Use this guide as the primary structure for requirement breakdown.

## Inputs
- [overall.md](overall.md)
- [data_modeling.md](data_modeling.md)
- [module_design.md](module_design.md)

## Core Thesis
- In F3CMS, a good requirement is not one that lists pages first. It is one that identifies the right entity first.
- The SA role is to translate business intent into stable entities, data shape, and module boundaries before implementation details spread into pages, actions, and UI fragments.
- The quality of downstream design depends heavily on whether requirement decomposition is entity-first or page-first.

## Requirement Analysis Philosophy in F3CMS

F3CMS is built around entities and modules, not around pages or controllers. Because of this, requirement analysis should begin with the business object that needs to exist in the system.

Why this matters:
- pages can change without changing the business object
- API formats can change without changing the business object
- workflow steps can change without changing the business object

If the requirement is decomposed around pages first, the usual result is:
- duplicated behavior across modules
- unstable schema decisions
- confusion between frontend needs and data model needs
- weak handoff quality to SD and programmers

If the requirement is decomposed around entities first, the result is usually:
- stable module boundaries
- cleaner schema design
- clearer responsibility splitting
- better reuse of Feed, Reaction, Outfit, and Kit

The working principle for SA is therefore:
- identify the stable business object first
- define how it behaves over time
- then define how it is edited, queried, and displayed

## Identifying the Core Entity

The first job in requirement breakdown is to identify the entity.

An entity is a stable business object that can usually be described independently of any single screen.

### Questions to Identify the Core Entity

Ask:
- what is the thing we are managing?
- can it be named independently in business language?
- does it have its own lifecycle or status?
- can it be queried or managed as a standalone object?
- does it have its own owner, permissions, or audit behavior?

If the answer is yes to most of these, you probably have an entity.

### Examples

Likely entities:
- Post
- Press
- Tag
- Staff
- Draft
- Conversation

Likely non-entities:
- one specific editor page
- one report widget
- one modal dialog
- one page section label

### Entity Attributes SA Must Capture Early

For each candidate entity, the SA should try to capture:
- business name
- purpose
- ownership
- lifecycle or status values
- key relationships
- whether it is internal-only or also visible in frontend/public flows

Without this information, the SD will be forced to infer structure from UI behavior, which usually produces weaker architecture.

## New Module vs Existing Module Decision Rules

One of the most important SA responsibilities is deciding whether the requirement introduces:
- a new entity and therefore likely a new module
- or only an extension of an existing entity and therefore an extension of an existing module

### Strong Signals for a New Module

Expect a new module when:
- the object has its own persistent identity
- it has a separate main table
- it has its own status model or workflow states
- it has separate list, save, or search behavior
- it needs separate permissions or backend management

### Strong Signals for Extending an Existing Module

Expect an existing module extension when:
- the new requirement only adds fields to an existing entity
- the new requirement adds localized content for an existing entity
- the new requirement adds metadata or settings to an existing entity
- the new requirement adds a relation between existing entities
- the new requirement only adds one more action or rendering mode for the same entity

### Practical Rule

If removing one page would make the "new thing" disappear conceptually, it is probably not a new module.

If the object would still make sense as a business concept even after multiple UI changes, it is probably a valid entity and possibly a module.

## Requirement Decomposition Order

In F3CMS, the recommended breakdown order is:

1. Business intent
2. Entity
3. Data structure
4. Module boundary
5. Interaction flows
6. Page and API surfaces

### 1. Business Intent

Start from the business need:
- what problem is being solved?
- what operation does the organization want to support?
- what object or event is central to that operation?

### 2. Entity

Turn the business intent into one or more entities.

### 3. Data Structure

Define what data belongs to the entity:
- stable fields
- localized fields
- metadata
- relationships
- lifecycle fields

### 4. Module Boundary

Decide whether the entity maps to a new module or an existing module.

### 5. Interaction Flows

Only after the module boundary is clear should the SA define:
- backend actions
- status transitions
- validation checkpoints
- admin or public flows

### 6. Page and API Surfaces

Pages and APIs are the final expression of the requirement, not the starting point.

This order ensures the data architecture drives implementation rather than the other way around.

## Data-Oriented Specification Template

Every requirement should contain a data-oriented section before detailed UI or API descriptions.

### Minimum Data Specification Fields

For each entity, document:
- entity name
- business purpose
- main fields
- localized fields
- metadata fields
- related entities
- status values
- ownership fields
- audit expectations
- visibility or publish behavior

### Required Fields

These are structurally important fields that define the entity in operations.

Examples:
- `status`
- `slug`
- `owner_id`
- `online_date`
- `sorter`

### Localized Fields

Identify which fields are language-dependent.

Examples:
- `title`
- `summary`
- `content`
- `info`

### Metadata Fields

Identify optional or extensible fields that should likely be stored in `_meta` rather than the main table.

Examples:
- SEO-specific values
- CTA labels
- optional display configuration

### Relationships

Document relationships explicitly:
- one-to-many
- many-to-many
- self-referential
- hierarchical through `parent_id`

Do not leave relationships implied in UI text only.

### Status Definitions

The SA should provide clear lifecycle states and meanings.

Examples:
- New
- Waiting
- Done
- Invalid
- Published
- Archived

Each status should have a business meaning, not just a display label.

## Layer Mapping in Specifications

SA does not need to write code-level design, but the requirement should still anticipate which layer owns which type of behavior.

### Feed-Oriented Specification Items
- persistence rules
- retrieval rules
- relationships
- status changes
- localization behavior

### Reaction-Oriented Specification Items
- backend actions
- interactive operations
- JSON-oriented workflows
- admin save/list/load/delete flows

### Outfit-Oriented Specification Items
- frontend pages
- display routes
- render variants
- SEO or content delivery behavior

### Kit-Oriented Specification Items
- validation rules
- input constraints
- reusable rule sets

This mapping helps SD avoid guessing where the requirement belongs in code.

## Typical Requirement Patterns

### Content Entity Pattern

Use this when the entity represents content managed in backend and displayed in frontend.

Typical examples:
- Post
- Press
- Author

Common characteristics:
- localized content
- status and publish behavior
- tags or relations
- list and detail views

### Workflow Entity Pattern

Use this when the entity represents a process artifact with states.

Typical examples:
- Draft
- review queue style entities
- approval or trace records

Common characteristics:
- clear status transitions
- owner or operator fields
- backend-focused actions

### Backend-Only Entity Pattern

Use this when the entity exists operationally but not as public-facing content.

Typical examples:
- Contact handling records
- internal mapping tables with real lifecycle
- admin support entities

### AI or Integration Entity Pattern

Use this when the requirement introduces a stable business object created or maintained through external AI or integration behavior.

Typical examples:
- Conversation
- Draft generated by LLM workflows

The important point is that AI-related does not mean "special case outside the architecture". If the object has stable identity and persistence, it should still follow the entity/module model.

## Common SA Mistakes

### Naming by Page

If a requirement is named after a screen instead of a business object, the design is already at risk.

### Under-Specifying Relationships

If related entities are mentioned in UI notes but not specified in the data model, SD will have to guess relation structure later.

### Missing Lifecycle Fields

If the object clearly has states or publish timing but the requirement omits them, implementation tends to become inconsistent.

### Ignoring Multilingual Implications

If a content-bearing entity is introduced without identifying which fields are localized, the schema often ends up wrong on the first attempt.

### Treating Metadata as an Afterthought

If the requirement has optional extensible attributes but does not distinguish them from stable fields, the main table tends to be overgrown.

### Starting from API Endpoints

If the requirement begins with endpoint definitions and never stabilizes the entity model, the resulting design tends to be transport-driven instead of domain-driven.

## Handoff Checklist to SD

Before handing off a requirement for technical design, the SA should ensure the following items are explicit.

### Entity Clarity
- entity name is defined
- business purpose is clear
- new module vs existing module intent is stated

### Data Clarity
- stable fields are listed
- localized fields are listed
- metadata fields are listed if applicable
- relationships are explicitly named
- lifecycle states are defined

### Operational Clarity
- admin actions are identified
- frontend visibility is identified
- ownership or permissions are identified
- audit or trace requirements are identified

### Boundary Clarity
- it is clear what belongs to the same entity versus another entity
- it is clear whether the requirement introduces a new independent object or only extends an existing one

### Handoff Rule

If the SD would need to guess the entity, guess the lifecycle, or guess whether the data is localized, the requirement is not ready.

## Suggested Requirement Template

Use the following compact structure in requirement documents:

1. Business goal
2. Core entity or entities
3. New module or existing module extension
4. Main fields
5. Localized fields
6. Metadata fields
7. Relationships
8. Status model
9. Backend actions
10. Frontend pages or public behavior
11. Validation constraints
12. Permissions and ownership

This structure maps naturally to the F3CMS architecture and reduces ambiguity during design.

## Related Documents
- [module_design.md](module_design.md)
- [sd_conventions.md](sd_conventions.md)
- [create_new_module.md](create_new_module.md)

## Status
- Draft v1 complete
