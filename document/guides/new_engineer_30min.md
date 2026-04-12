# F3CMS 30-Minute Onboarding for New Engineers

## Purpose
- Give new engineers a fast and accurate mental model of F3CMS.
- Reduce time spent reading files in the wrong order.
- Provide an onboarding path that works for both humans and LLM assistants.

## Primary Readers
- New engineers
- Engineers switching into the project
- LLMs acting as onboarding copilots

## Scope
- reading order
- core concepts
- first modules to inspect
- first architecture rules to remember

## LLM Reading Contract
- Prioritize speed of orientation over completeness.
- Use this guide to establish vocabulary before deeper technical guides.
- Follow the reading sequence literally unless the task is highly specialized.

## Inputs
- [index.md](index.md)
- [overall.md](overall.md)
- [data_modeling.md](data_modeling.md)
- [feed_guide.md](feed_guide.md)

## Core Thesis
- A new engineer should not start by reading random modules.
- The fastest correct path is: understand the architecture vocabulary, understand the entity-first data model, inspect one representative module end to end, then learn where changes belong.
- This guide optimizes for usable orientation in 30 minutes, not for exhaustive coverage.

## What F3CMS Is in One Minute

F3CMS is not a plain MVC project where most of the understanding comes from controller flow. It is a convention-driven CMS built around entities, module structure, and the Hierarchical FORK model.

The short version is:
- an entity is the stable business object
- one entity usually maps to one module
- a module is usually expressed through Feed, Reaction, Outfit, and Kit
- the data model is intentionally decomposed into main tables, `_lang` tables, `_meta` tables, and relation tables
- code quality depends heavily on putting logic in the correct layer

If you keep those five points in mind, the rest of the codebase becomes much easier to navigate.

## What to Ignore in the First 30 Minutes

Do not begin with:
- every module in `www/f3cms/modules/`
- raw SQL details for every table
- frontend theme files
- every helper in `www/f3cms/libs/`
- one specific feature request or bug ticket

Those are second-round topics. The first onboarding goal is to build a stable map of the system so later details have somewhere to attach.

## The 30-Minute Reading Route

This route is intentionally time-boxed. If you follow it in order, you should reach a usable mental model quickly.

### Minute 0 to 5: Learn the Vocabulary

Read [overall.md](overall.md) first.

Your goal in this phase is not to memorize details. Your goal is to recognize the architectural nouns:
- Feed
- Outfit
- Reaction
- Kit
- Theme
- module
- entity

At the end of this phase, you should be able to say:
- F3CMS splits responsibilities more explicitly than a simple MVC project
- HTML rendering, JSON interaction, and data lifecycle are intentionally separated
- the system is organized around modules, not around one giant controller layer

### Minute 5 to 10: Learn How the System Loads Modules

Open [www/f3cms/libs/Autoload.php](../../www/f3cms/libs/Autoload.php).

What to notice:
- class prefixes map directly to layer types
- `f` means Feed
- `o` means Outfit
- `r` means Reaction
- `k` means Kit
- module naming is part of runtime behavior, not cosmetic style

This is one of the fastest ways to understand why file naming matters in F3CMS. A class name like `fPost` is not just a preference. It is part of how the project locates the correct module file.

Then open [www/f3cms/libs/Module.php](../../www/f3cms/libs/Module.php).

What to notice:
- request extraction and common sanitation live in shared base behavior
- language resolution is framework-level infrastructure, not a one-off module concern
- the project expects common patterns to be centralized rather than reinvented in each module

At the end of this phase, you should understand that conventions are structural in F3CMS.

### Minute 10 to 18: Learn One Module End to End

Use Post as the first representative module.

Read these files in order:
- [www/f3cms/modules/Post/feed.php](../../www/f3cms/modules/Post/feed.php)
- [www/f3cms/modules/Post/reaction.php](../../www/f3cms/modules/Post/reaction.php)
- [www/f3cms/modules/Post/outfit.php](../../www/f3cms/modules/Post/outfit.php)

What to observe in [www/f3cms/modules/Post/feed.php](../../www/f3cms/modules/Post/feed.php):
- the Feed class names the entity through `MTB`
- constants encode permissions and backend list behavior
- Feed is the root of entity data access

What to observe in [www/f3cms/modules/Post/reaction.php](../../www/f3cms/modules/Post/reaction.php):
- Reaction is for request-response style interaction, often JSON-oriented
- it should coordinate the request and call Feed-backed behavior rather than owning schema logic
- it is a thinner interaction layer than many newcomers expect

What to observe in [www/f3cms/modules/Post/outfit.php](../../www/f3cms/modules/Post/outfit.php):
- Outfit is the page-facing orchestration layer
- it loads data, prepares page context, and chooses how to render
- it is not the place to invent new persistence rules

At the end of this phase, you should be able to explain one complete module in one sentence:
- Feed owns the entity data lifecycle
- Reaction owns interactive backend responses
- Outfit owns page-level rendering flow

### Minute 18 to 24: Learn the Data Model Once

Read [data_modeling.md](data_modeling.md).

Your goal here is to stop thinking in page fields and start thinking in entity structure.

You need to leave this phase understanding these table types:
- main table: stable non-localized operational fields
- `_lang`: per-language content
- `_meta`: optional extension attributes
- relation table: structured many-to-many or cross-entity linkage

This is the point where many new engineers either get oriented or get lost.

If you remember only one rule, remember this:
- if a field is stored in the wrong table, the code will usually become harder everywhere else

### Minute 24 to 30: Learn Where Changes Belong

Read [feed_guide.md](feed_guide.md), then skim [sd_conventions.md](sd_conventions.md).

Your goal is to answer one practical question:
- when I receive a change request, where in the architecture should I place the change?

Use the following quick map:
- change the schema when the business entity structure is wrong or incomplete
- change Feed when persistence, query shape, language handling, metadata handling, or relation logic changes
- change Reaction when request/response interaction changes
- change Outfit when page rendering flow or page composition changes
- change Kit when module-owned rule or helper behavior should be encapsulated for reuse, including cross-module callers

If the behavior still belongs to one module's business boundary, prefer that module's Kit even when other modules call it. Only move it to helpers or `libs` when it becomes infrastructural or no longer belongs to one module.

If you cannot decide where a change belongs, the problem is usually one of two things:
- the entity boundary is still unclear
- the module responsibilities have not been respected

## The Mental Model You Should Keep After 30 Minutes

If the onboarding worked, the new engineer should now hold the following model:

### 1. F3CMS Is Entity-First
- Pages are not the primary modeling unit.
- APIs are not the primary modeling unit.
- Entities are the primary modeling unit.

### 2. A Module Is a Business Boundary
- A module is usually the code boundary around one entity.
- Module shape is not arbitrary. It reflects how data, rendering, and interaction are separated.

### 3. Feed Is the Most Important Layer to Understand Early
- Feed is where the entity data model becomes operational.
- If you do not understand Feed, later changes will often be placed in the wrong layer.

### 4. Conventions Are Runtime Structure
- Naming conventions are not only style.
- Autoload behavior, file discovery, and architectural predictability all depend on them.

### 5. Table Design Drives Code Design
- Main, `_lang`, `_meta`, and relation tables are not storage trivia.
- They shape how Feed reads, saves, joins, and exposes the entity.

## How to Decide Where a Change Belongs

When a ticket arrives, do not ask only "which file should I edit". Ask "what kind of architectural change is this".

### If the ticket changes business data shape
- read [data_modeling.md](data_modeling.md)
- inspect SQL and Feed together

### If the ticket changes save or read behavior
- read [feed_guide.md](feed_guide.md)
- inspect the module Feed before touching Reaction or Outfit

### If the ticket changes interaction flow or JSON output
- inspect Reaction first
- verify that no data-lifecycle logic is leaking out of Feed

### If the ticket changes page rendering or template selection
- inspect Outfit first
- verify that rendering changes are not hiding a data-model problem

### If the ticket introduces reuse across modules
- inspect whether the behavior belongs in Kit or a shared library

## Common Mistakes in the First Week

These are the most common early mistakes and how to avoid them.

### Mistake 1: Looking for Controllers First
New engineers often expect one central controller layer to explain the system. In F3CMS, that instinct is misleading.

Correct approach:
- start from the module and its layer split
- trace Feed, Reaction, and Outfit separately

### Mistake 2: Putting Data Logic in Reaction
Because Reaction handles requests, it is tempting to add normalization, relation handling, or schema decisions there.

Correct approach:
- keep persistence semantics in Feed
- keep Reaction focused on coordinating request and response flow

### Mistake 3: Adding Fields to the Wrong Table
This usually happens when a field is placed based on UI convenience instead of data semantics.

Correct approach:
- use [data_modeling.md](data_modeling.md)
- decide whether the field is stable, localized, extensible, or relational before touching SQL

### Mistake 4: Treating a New Screen as a New Module
A new page does not always imply a new entity.

Correct approach:
- ask whether the business object has independent lifecycle, ownership, and query behavior
- use [module_design.md](module_design.md) before creating new module folders

### Mistake 5: Reading Too Broadly Too Early
Trying to understand every module at once usually slows onboarding.

Correct approach:
- learn one representative module well
- then compare the second and third modules later

## First Files to Keep Open While Working

These are the anchor files that reduce confusion during the first few days.

- [www/f3cms/libs/Autoload.php](../../www/f3cms/libs/Autoload.php)
- [www/f3cms/libs/Module.php](../../www/f3cms/libs/Module.php)
- [www/f3cms/libs/Feed.php](../../www/f3cms/libs/Feed.php)
- [www/f3cms/modules/Post/feed.php](../../www/f3cms/modules/Post/feed.php)
- [www/f3cms/modules/Post/reaction.php](../../www/f3cms/modules/Post/reaction.php)
- [www/f3cms/modules/Post/outfit.php](../../www/f3cms/modules/Post/outfit.php)

These files give a newcomer the quickest stable reference for naming, layer split, and a representative module pattern.

## Recommended Second-Round Reading

After the first 30 minutes, continue in this order.

1. [index.md](index.md)
2. [data_modeling.md](data_modeling.md)
3. [module_design.md](module_design.md)
4. [feed_guide.md](feed_guide.md)
5. [sd_conventions.md](sd_conventions.md)
6. [data_architecture_checklist.md](data_architecture_checklist.md)
7. [create_new_module.md](create_new_module.md)

This second round turns orientation into working judgment.

## What a New Engineer Should Be Able to Say After Reading

After finishing this guide, a new engineer should be able to explain:
- what F3CMS is trying to optimize for
- why it is not best understood as plain MVC
- what Feed, Reaction, Outfit, and Kit each do
- why entities and table decomposition come before page-first implementation
- which file family to inspect first when a new ticket arrives

If those five answers are still unclear, the engineer should repeat the route above rather than jumping into implementation.

## Related Documents
- [data_architecture_checklist.md](data_architecture_checklist.md)
- [sd_conventions.md](sd_conventions.md)
- [create_new_module.md](create_new_module.md)
- [module_design.md](module_design.md)

## Status
- Draft v1 complete
