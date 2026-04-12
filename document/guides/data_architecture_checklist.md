# F3CMS Data Architecture Checklist

## Purpose
- Provide a practical checklist before changing schemas, Feed logic, or entity structure.
- Turn architectural principles into repeatable day-to-day checks.
- Help new engineers avoid structural mistakes early.

## Primary Readers
- New engineers
- Backend programmers
- Reviewers
- LLMs validating implementation plans

## Scope
- pre-change checks
- schema placement checks
- naming checks
- layer placement checks
- performance sanity checks

## LLM Reading Contract
- Use this file as an actionable checklist, not as a conceptual essay.
- Treat every unchecked item as a potential design risk.
- Prefer explicit yes/no validation when summarizing compliance.

## Inputs
- [data_modeling.md](data_modeling.md)
- [feed_guide.md](feed_guide.md)
- [query_and_performance.md](query_and_performance.md)

## Core Thesis
- This file is the execution layer of data architecture review.
- Use it after a design idea exists but before schema, Feed behavior, or module structure is finalized.
- A design should not be considered ready only because it works conceptually. It should also pass the checklist below with explicit yes or no answers.

## How to Use This Checklist

Use this document in one of three modes:
- design self-check before implementation
- reviewer checklist during PR or design review
- LLM validation template when generating recommendations

The expected behavior is simple:
- answer each item with yes, no, or not applicable
- if the answer is no, record the correction before implementation continues
- do not treat unresolved checklist failures as minor style issues when they affect entity boundaries, table placement, or Feed lifecycle integrity

## SQL Delivery Check

Use this before closing any schema change or DBA handoff.

- [ ] If the change includes schema migration SQL or DBA-executed full-table SQL, has it been added to `document/sql/YYMMDD.sql` for today?
- [ ] If today's SQL file did not already exist, was it created before delivery?
- [ ] Is `document/sql/init.sql` left as baseline bootstrap rather than reused for today's ad hoc change script?

## Review Flow

The recommended order is:
1. identify the entity
2. verify whether a new module is actually needed
3. verify table placement
4. verify naming and field structure
5. verify Feed lifecycle alignment
6. verify query and performance sanity
7. verify that no anti-pattern has been introduced

This order matters because later decisions depend on earlier ones. A query that looks inefficient may actually be a symptom of the wrong entity split or the wrong table placement.

## 1. Entity Identification Checklist

Answer these before discussing schema details.

### Core Entity Questions
- [ ] Can the business object be named independently of any single page, modal, or API action?
- [ ] Does the object still make sense conceptually even if the current UI changes?
- [ ] Does the object have its own lifecycle, status, or ownership model?
- [ ] Can the object be queried or managed directly rather than only through a parent page?

### Existing Entity Reuse Questions
- [ ] Does an existing module already own this business object?
- [ ] Is the new requirement only adding fields, localized content, metadata, or relations to an existing entity?
- [ ] If a new module is proposed, is there a clear reason it cannot remain an extension of an existing module?

### Review Decision
- If two or more answers above are unclear, stop and clarify the entity before designing tables.
- If the requirement is still described mainly in page language, the entity model is probably not ready.

## 2. Module Boundary Checklist

Use this section when deciding whether the design introduces a new module or extends an existing one.

### New Module Signals
- [ ] The object has its own main table or clearly needs one.
- [ ] The object has independent backend actions or list/search behavior.
- [ ] The object has independent permission, audit, or ownership implications.
- [ ] The object is likely to be reused or queried outside one parent entity flow.

### Existing Module Extension Signals
- [ ] The new data is structurally subordinate to an existing entity.
- [ ] The new data does not introduce an independent lifecycle.
- [ ] The new requirement can be modeled as a field, localized field, meta field, or relation.
- [ ] A separate module would create artificial duplication or fragmented ownership.

### Review Decision
- If both groups above are half true, the boundary is ambiguous and should be reviewed before coding.
- Do not create a new module only because the UI has a new screen.

## 3. Table Placement Checklist

Use this section field by field, not only table by table.

### Main Table Placement
- [ ] Every field placed in the main table is stable, non-localized, and central to the entity.
- [ ] Fields in the main table are likely to be filtered, sorted, indexed, or operationally important.
- [ ] The main table is not being used as a catch-all storage area for optional display fields.

Typical main table candidates:
- `status`
- `slug`
- `layout`
- `sorter`
- `owner_id`
- `online_date`

### Language Table Placement
- [ ] Every field placed in `_lang` truly varies by language.
- [ ] Localized content fields are not being duplicated in the main table.
- [ ] Translators or editors would reasonably expect to manage these values per language.

Typical `_lang` candidates:
- `title`
- `summary`
- `info`
- `content`

### Meta Table Placement
- [ ] Every field placed in `_meta` is optional, extensible, or feature-specific.
- [ ] No `_meta` field is expected to become a first-class filter, sort key, or frequent join condition.
- [ ] `_meta` is not being used to avoid making a clear schema decision.

Typical `_meta` candidates:
- SEO values
- optional CTA labels
- low-frequency extension settings

### Relation Table Placement
- [ ] Any value that actually references another entity is modeled as a relation, not as JSON text.
- [ ] Many-to-many relationships are represented with a dedicated relation table.
- [ ] Relation order or sorter is stored explicitly if ordering matters.
- [ ] The design does not hide real relational structure inside a serialized field.

### Secondary or Trace Table Placement
- [ ] Additional secondary tables exist only when the data grows independently but remains subordinate to the parent entity.
- [ ] Trace, history, import, or log-like records are not being mixed into the main entity table.

### Review Decision
- If a field placement cannot be justified in one sentence, it is probably in the wrong table.

## 4. Naming and Field Structure Checklist

Naming is architectural in F3CMS because runtime conventions and maintainability depend on predictability.

### Table Naming
- [ ] Main tables use the `tbl_` prefix.
- [ ] Table names use lowercase with underscores.
- [ ] `_lang` is used only for localized tables.
- [ ] `_meta` is used only for extension metadata tables.
- [ ] Relation tables clearly name the two participating entities.

### Field Naming
- [ ] Primary key naming is consistent with module conventions.
- [ ] Foreign key names are explicit and predictable, usually ending in `_id`.
- [ ] Status, sorter, owner, and audit fields use existing naming conventions rather than new variants.
- [ ] No field name is page-specific, temporary, or tied to one UI label.

### Audit Fields
- [ ] The entity includes `insert_ts` and `last_ts` if it follows standard audit behavior.
- [ ] The entity includes `insert_user` and `last_user` if standard write tracking applies.
- [ ] Audit fields are not renamed for local preference.

### Structural Consistency
- [ ] The schema can be inferred from the module name.
- [ ] The module can be inferred from the schema names.
- [ ] A new engineer can understand the table purpose without reading controller logic.

## 5. Feed Placement Checklist

Use this section to verify that data behavior is implemented in the right architectural layer.

### Feed Responsibility Questions
- [ ] Data normalization belongs in Feed rather than Reaction when it affects persistence semantics.
- [ ] Save lifecycle behavior uses Feed entry points instead of ad hoc direct writes.
- [ ] Relation, metadata, and multilingual behavior are attached to Feed rather than scattered across callers.

### Save Lifecycle Alignment
- [ ] The design respects main table write plus post-save handling for lang, meta, and relation data.
- [ ] Any new field preprocessing is implemented where Feed can apply it consistently.
- [ ] Direct table writes are avoided unless there is a documented reason to bypass the normal lifecycle.

### Read Lifecycle Alignment
- [ ] Single-row reads use the correct Feed path for canonical entity loading.
- [ ] List reads use the correct Feed path for list and pagination semantics.
- [ ] Query-specific joins and filters are added in Feed rather than recreated across multiple callers.

### Multilingual and Metadata Alignment
- [ ] If the entity is multilingual, the read and save flow respect `_lang` behavior end to end.
- [ ] If metadata exists, Feed exposes a consistent way to save and load it.
- [ ] Side-channel structures such as `meta`, `lang`, and relations are not ignored by shortcut write logic.

### Review Decision
- If the proposed implementation requires repeated special-case data logic in Reaction, the Feed design is probably incomplete.

## 6. Query and Performance Checklist

This section is a sanity check, not a full tuning document. It exists to catch obvious structural mistakes before they become expensive.

### Query Shape Questions
- [ ] The design distinguishes between single-row reads and list reads.
- [ ] Joins are required by the data model rather than added for convenience.
- [ ] The selected query path matches the actual use case instead of overloading one generic method.

### Count and Pagination Questions
- [ ] The design considers the cost of total counts for paginated lists.
- [ ] Expensive list pages are not relying on unnecessary joins just to render one or two columns.
- [ ] Sorting and filtering needs were identified before choosing where fields live.

### Index and Cardinality Questions
- [ ] Frequently filtered or joined fields are eligible for indexing in the schema design.
- [ ] `_meta` is not being used for values that will later need efficient filtering.
- [ ] Relation tables include the key columns needed for expected joins.

### Relation Integrity Questions
- [ ] Relations are enforced structurally rather than reconstructed from JSON strings.
- [ ] The design can answer expected reporting or lookup questions without parsing serialized blobs.

### Review Decision
- If performance depends on application-side filtering of large result sets, the schema or query path is probably wrong.

## 7. Anti-Pattern Checklist

Any yes answer here is a design risk and should trigger review.

### Structural Anti-Patterns
- [ ] Is the schema driven mainly by page layout instead of entity boundaries?
- [ ] Is a new module being created only because a new screen exists?
- [ ] Is optional extension data being pushed into the main table without clear reason?

### Storage Anti-Patterns
- [ ] Is a real entity relationship being stored in JSON or comma-separated text?
- [ ] Is localized content being stored in the main table instead of `_lang`?
- [ ] Is `_meta` being used as a dumping ground for unresolved schema choices?

### Layer Anti-Patterns
- [ ] Is persistence logic being implemented mainly in Reaction instead of Feed?
- [ ] Are direct SQL writes bypassing the normal entity lifecycle without documentation?
- [ ] Are multiple callers expected to rebuild the same data transformation logic separately?

### Performance Anti-Patterns
- [ ] Is the design assuming performance will be fixed later rather than shaped now?
- [ ] Is the list query doing more joins than the screen actually needs?
- [ ] Is a field stored in a way that blocks future indexing or filtering?

## 8. Review Outcomes

Use the result below to decide whether work can proceed.

### Pass
- All core entity and table placement questions are answered clearly.
- No critical anti-pattern is present.
- Remaining issues are minor and do not affect schema or layer boundaries.

### Revise Before Coding
- Entity boundary is still unclear.
- Table placement for important fields is still disputed.
- Feed lifecycle behavior would be bypassed or fragmented.
- Query cost concerns point to a structural problem.

### Escalate for Design Review
- The requirement may change module boundaries across multiple entities.
- The design introduces a new table family without clear precedent.
- The design requires deliberate deviation from existing conventions.

## 9. PR or Design Review Sign-Off Template

Use this block in PR descriptions, design notes, or LLM-generated review summaries.

### Short Checklist
- Entity identified: yes / no
- New module justified: yes / no / not applicable
- Table placement reviewed: yes / no
- Naming and audit fields aligned: yes / no
- Feed lifecycle aligned: yes / no
- Query and pagination sanity checked: yes / no
- Anti-patterns found: yes / no
- Ready to implement or merge: yes / no

### Reviewer Notes Template
- Entity decision:
- Table placement risks:
- Feed lifecycle risks:
- Query or indexing risks:
- Required corrections:

## Related Documents
- [pr_review_checklist.md](pr_review_checklist.md)
- [sd_conventions.md](sd_conventions.md)
- [module_design.md](module_design.md)

## Status
- Draft v1 complete
