# F3CMS Module Design Guide

## Purpose
- Define how business entities map to modules.
- Clarify responsibility boundaries across Feed, Reaction, Outfit, and Kit.
- Prevent features from being implemented in the wrong layer.

## Primary Readers
- SA
- SD
- Backend programmers
- LLMs proposing new module structures

## Scope
- Module creation criteria
- Responsibility splitting
- Cross-module interaction rules
- Non-content modules such as AI, workflow, and integration modules

## LLM Reading Contract
- Assume module is the code boundary for one entity.
- Prefer extending an existing module unless there is a clear new entity with its own lifecycle.
- Use this guide when deciding file placement and responsibility boundaries.

## Inputs
- [overall.md](overall.md)
- [data_modeling.md](data_modeling.md)
- [create_new_module.md](create_new_module.md)

## Core Thesis
- Module is the code boundary of an entity in F3CMS.
- If entity is the stable business concept, module is the stable implementation unit.
- Therefore, module design should be driven by entity boundaries, not by screens, routes, or one-off APIs.

## What a Module Means in F3CMS

In many projects, folders are organized by feature page, transport type, or framework convention. In F3CMS, the module has a more specific meaning: it is the implementation boundary for one entity and its surrounding behaviors.

A module is not just a place to group files. It is the point where the following concerns are aligned around the same entity:
- data access
- backend interaction
- frontend presentation
- validation and utility support

This means that a module should remain meaningful even if:
- the frontend is redesigned
- new API endpoints are added
- backend workflows change
- additional metadata or localized content is introduced

If a folder only makes sense because one page exists, it is probably not a true module. If it still makes sense when the UI changes because it represents a stable business object, it is probably a valid module.

## How Entities Map to Modules

The default mapping rule is:
- one entity maps to one module

This rule exists because the same entity usually needs consistent handling across multiple layers. For example, an entity may need:
- a Feed for persistence and query behavior
- a Reaction for backend JSON actions
- an Outfit for frontend rendering or page flow
- a Kit for validation rules and module-owned utilities that may also be reused by other modules

If those responsibilities are spread across multiple unrelated folders, the entity boundary becomes unclear and the codebase starts to drift toward duplication and special-case logic.

### Practical Interpretation

When you identify an entity, you should immediately ask:
- what is its module name?
- what is its main table?
- does it need Feed only, or Feed plus Reaction, Outfit, and Kit?

The answer to these questions creates a stable path from requirement to code structure.

## Module Creation Criteria

Create a new module when the business object has enough independence to justify its own boundary.

Strong indicators include:
- it has its own main table
- it has an independent lifecycle or status model
- it needs separate backend actions
- it has ownership, permissions, or audit behavior distinct from another entity
- it is likely to be listed, queried, or managed directly

If the requirement introduces new table-backed business logic, that logic must live under the owning module rather than being placed in `libs`. Shared libraries are for cross-module infrastructure or truly generic helpers, not for owning entity persistence, workflow state, or audit behavior.

更直接的判準是：只有在邏輯不涉及特定實體操作時，才應放進 `libs`。只要已牽涉 entity truth、payload ownership、workflow / duty 判讀、task writeback、audit trail，或其他 module-owned business coordination，就應回到 owning module，而不是以可共用為理由移進 `libs`。

Examples of entities that clearly justify modules:
- Post
- Press
- Staff
- Menu
- Draft
- Conversation

### Signs That a New Module Is Justified

Create a new module if most of these are true:
- the object can be named independently in business language
- the object can exist even if one specific page disappears
- the object has a meaningful row identity of its own
- the object needs its own Feed methods rather than being stored as a field only
- the object needs separate status transitions, list pages, or workflow states

### Signs That a New Module Is Not Yet Justified

Do not create a new module if most of these are true:
- it is only one extra attribute of an existing entity
- it only exists to support one screen variation
- it is just one localized text or one metadata bundle
- it never needs separate querying or lifecycle control

## When to Extend an Existing Module

Many changes in F3CMS should extend an existing module rather than create a new one.

Typical extension cases include:
- adding a stable field to the entity's main table
- adding localized content to the entity's `_lang` table
- adding optional metadata to the entity's `_meta` table
- adding a relation table for a new many-to-many association
- adding a new backend action for the same entity
- adding a new frontend rendering mode for the same entity

### Examples

Extend Post when:
- a new `layout` type is introduced
- a new metadata field supports SEO or CTA configuration
- a new frontend page variant renders the same post entity differently

Extend Press when:
- a new relation to another entity is needed
- a new publish-related status or display option is introduced

Do not create a separate module just because:
- a page has a special editor
- a new API endpoint is added
- one workflow step needs a custom button

Those are often interaction-layer changes, not new entity boundaries.

## Layer Responsibilities

F3CMS module design depends on clear layer responsibilities.

### Feed

Feed is the entity's data access and data lifecycle layer.

Feed is responsible for:
- persistence
- retrieval
- list queries
- metadata and language save behavior
- relation save behavior
- query defaults and filters
- direct `mh()` access and transaction control for module-owned writes

Feed should not become:
- a page renderer
- a transport handler
- a catch-all business workflow engine unrelated to data lifecycle

### Reaction

Reaction is the backend interaction layer for JSON-oriented operations.

Reaction is responsible for:
- reading request data
- permission checks
- validation orchestration through Kit
- calling Feed methods
- returning backend responses

Reaction should not become:
- the primary home of data logic
- a place where raw SQL is duplicated across actions
- the long-term storage model definition
- the place that directly calls `mh()` or owns transaction begin / commit / rollback

### Outfit

Outfit is the frontend presentation and route orchestration layer.

Outfit is responsible for:
- page-level request flow
- render orchestration
- selecting templates or theme output
- preparing view-facing data

Outfit should not become:
- the main write path for entity data
- a place where business persistence rules are invented separately from Feed

### Kit

Kit is the module-owned rule and utility layer.

Kit is responsible for:
- validation rules
- lightweight helper logic that belongs to the module
- reusable utility behavior that encapsulates the module's rules and may be called by that module's Reaction or Outfit or by other modules

Kit should not become:
- a second Feed
- an arbitrary dumping ground for unrelated functions
- a replacement for `libs` infrastructure

Practical rule:
- if logic is owned by one module's business rules, keep it in that module's Kit even when other modules need to call it
- if logic is infrastructural, generic, or not owned by one module, move it to helpers or `libs`

## FORK 分工優先級

在 F3CMS 中，不同重構徵兆的優先級不同。高優先級規則一旦違反，必須先修正，不能用較低優先級的改善理由覆蓋。

### 第一級：必須符合
- `mh()` 的呼叫只能放在 Feed
- transaction begin / commit / rollback 只能由 Feed 持有
- table-backed persistence 與 module-owned log write 必須落在 Feed

### 第二級：應優先收斂
- 不要新增只服務單一 caller、且沒有穩定語意邊界的函式
- 若某函式只是把單一路徑包一層名字，應優先內聚回主流程或提升為真正的 module-owned helper

### 第三級：結構偏好
- 跨 module 呼叫時，優先只依賴對方的 Kit 或 Feed
- 避免跨調另一個 module 的 Reaction / Outfit 或把它們當 reusable API

判斷順序：
- 先看第一級是否違反
- 第一級未違反，再看第二級是否值得收斂
- 前兩級都穩定後，再處理第三級的結構優化

Presentation transform rule:
- if multiple modules need the same row-decoration or response-shaping logic for one entity, do not cross-call another module's Reaction hook such as `handleIteratee()` or `handleRow()`
- instead, move the stable transform into that entity's module-owned helper or presenter, for example `kPress::decorateListRow()` / `decorateDetailRow()`, and let each Reaction delegate to it
- this keeps Reaction as the transport-facing layer while preserving one clear owner for the response shape of that entity

## WorkflowEngine Integration Pattern

When a module needs workflow judgment, WorkflowEngine should be treated as a shared library under `libs/`, not as a new module or a shared persistence owner.

### Stable Rules

- `libs` 只承接 shared runtime / parser / evaluator / generic infrastructure，不承接特定實體操作
- module owns the business entity, workflow definition source, and workflow audit persistence
- WorkflowEngine owns workflow rule evaluation only
- Feed persists business rows and module-owned log rows, but does not become the place where workflow rules are invented
- Reaction handles action requests and delegates transaction-backed writes to Feed
- Outfit may ask WorkflowEngine for display-facing projection such as current stage or available actions
- Kit may wrap module-owned workflow helpers for reuse by other modules, but should not replace WorkflowEngine itself
- if workflow coordination starts to own entity state, duty judgment, task writeback, payload source, or audit persistence, keep that part in the owning module instead of expanding `libs`

### What Not To Do

- do not create a generic workflow module just to hold shared instance tables
- do not move entity ownership away from the module because workflow exists
- do not let Feed absorb generic workflow branching logic that belongs in WorkflowEngine
- do not treat retired workflow instance persistence APIs as valid integration entry points

### Theme

Theme is not part of the module folder by default, but it is still part of the overall design system.

Theme is responsible for:
- HTML output
- reusable presentation templates
- keeping presentation code separate from module logic

## Cross-Layer Communication Patterns

To keep modules coherent, each layer should communicate in predictable ways.

### Reaction to Feed
- Reaction receives the request.
- Reaction resolves validation and permissions.
- Reaction delegates persistence and retrieval to Feed.

### Reaction to WorkflowEngine
- Reaction loads the target workflow JSON from the module's chosen source.
- Reaction assembles runtime context from the entity's current state and operator context.
- Reaction asks WorkflowEngine whether the requested action is allowed before writing business data.
- Reaction delegates module-owned workflow log writes and business-row updates to Feed methods that keep them inside the same transaction when audit consistency matters.

### Outfit to Feed
- Outfit resolves route or page context.
- Outfit reads entity data through Feed.
- Outfit prepares variables for theme rendering.

### Outfit to WorkflowEngine
- Outfit may use WorkflowEngine projection to render state labels, available actions, or next-step hints.
- Outfit should remain display-oriented and should not invent write-side workflow persistence rules.

### Reaction to Kit
- Reaction uses Kit to obtain rules or module-owned helpers.
- Kit supports Reaction, but does not replace Feed.

### Feed to Other Entities
- Feed may use relation patterns or helper methods to interact with other entities.
- Cross-entity access should remain explicit and justified.

The key principle is:
- data lifecycle belongs in Feed
- transport and route behavior belong in Reaction or Outfit

## Cross-Module Interaction Patterns

Modules do not live in isolation. Entities relate to each other. However, cross-module interaction must remain structurally clear.

### Preferred Cross-Module Patterns
- use relation tables for many-to-many associations
- use `oneOpt()`-style lookup methods for option mapping
- use Feed helper methods for reading related entities
- use common helpers only when the logic is not owned by a single entity

### Use Direct Module Calls Carefully

Calling another module's Feed method is acceptable when:
- the other entity is genuinely part of the current entity's read or write model
- the relationship is structurally meaningful
- duplication would be worse than the dependency

Do not create hidden coupling where one module silently owns another module's rules.

### Good Pattern
- Press uses Author through relation tables and explicit Feed helpers.

### Bad Pattern
- an unrelated module writes directly into another module's main table because a shortcut seemed convenient.

## Example Module Shapes

Not every module has the same visible surface, but all valid modules are still centered on one entity.

### Content Module
- has Feed, Reaction, Outfit, maybe Kit
- examples: Post, Press

### Backend-Oriented Module
- has Feed and Reaction, minimal or no Outfit
- examples: administrative or workflow modules

### Taxonomy or Hierarchy Module
- strong use of `parent_id` and relation helpers
- examples: Tag, Menu, Category

### Integration or AI-Support Module
- may not be classic CMS content
- still qualifies as a module if it has its own entity model, lifecycle, and storage
- examples: Draft, Conversation

### Feed-Only or Minimal Module
- acceptable if the entity exists mainly for persistence and lookup support
- still must have a clear entity identity

## Common Module Design Mistakes

### Page-First Module Creation
Creating a module because a new screen exists is a mistake unless a new entity also exists.

### Too Much Logic in Reaction
If Reaction methods start deciding how data is structured, persisted, normalized, and related, the entity boundary is already being eroded.

### Using Outfit for Writes
If Outfit starts becoming the main place for data mutation, the page layer and data layer are being mixed.

### Treating Kit as a Dump Area
If module rules, generic helpers, and random convenience methods are all dropped into Kit, the code loses discoverability.

### Duplicating Entity Logic Across Modules
If two modules each partially own the same entity rules, the system becomes difficult to reason about and difficult for LLMs to model correctly.

## Decision Matrix

Use the following decision logic when deciding where a change belongs.

### Create a New Module when
- there is a new entity
- the entity has a separate lifecycle
- separate management or querying is expected

### Extend an Existing Module when
- the change is an attribute, language field, metadata field, or relation of an existing entity
- the behavior still belongs to the same business object

### Add a Helper or Shared Utility when
- the logic is not owned by one entity
- the logic is reused across modules
- the logic does not redefine the data boundary of an entity

### Add a New Page or API Action without New Module when
- the entity remains the same
- only the interaction surface changes

## Design Questions to Ask Before Implementing

1. What is the entity behind this requirement?
2. Does that entity already have a module?
3. Is this a new entity or only a new interaction surface?
4. Which layer should own the behavior?
5. If a new module is proposed, what is its main table and lifecycle?
6. Would the module still make sense if the current page design changed?

If the final answer depends entirely on a single page layout, the proposed module boundary is probably wrong.

## Related Documents
- [data_modeling.md](data_modeling.md)
- [sa_requirement_breakdown.md](sa_requirement_breakdown.md)
- [sd_conventions.md](sd_conventions.md)

## Status
- Draft v1 complete
