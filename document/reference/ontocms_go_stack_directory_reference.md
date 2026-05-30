# OntoCms_go Stack And Directory Reference

## Purpose
- Provide a stable reference for the default Go stack and directory structure of `OntoCms_go`.
- Keep Go repo bootstrap aligned with the current OntoCMS architectural language instead of falling back to generic Go project templates.

## Default Stack
- Database: PostgreSQL
- HTTP router: `chi`
- SSR template: Go standard library `html/template`
- Session: `alexedwards/scs/v2`
- Database driver and pool: `pgx/v5`, `pgxpool`
- Migration: `goose` or `atlas`
- Logging: Go standard library `log/slog`
- Query strategy: raw SQL first, optionally `sqlc` where strongly typed bindings improve maintainability

## Directory Thesis
- Preserve OntoCMS responsibility language: thin public entry, repo-wide conventions, module-owned app code, technical infra, theme, and tests.
- Do not replace OntoCMS layer naming with generic Go template folders such as `cmd/`, `internal/`, or `pkg/` unless the repo explicitly decides to abandon the current architecture language.
- Keep entity-owned logic inside the owning module under `src/app/{Entity}`.
- Keep only pure runtime, transport, adapter, and infrastructure concerns in `src/infra`.
- Keep repo-wide conventions that are not owned by a single entity in `src/conventions`.

## Suggested Structure
```text
OntoCms_go/
├── README.md
├── go.mod
├── go.sum
├── docker-compose.yml
├── .env
├── bin/
│   ├── build.sh
│   ├── up.sh
│   ├── down.sh
│   └── clear.sh
├── conf/
│   ├── nginx/
│   ├── go/
│   └── postgresql/
├── database/
├── document/
│   ├── flow.md
│   ├── flow.llm.md
│   ├── glossary.md
│   ├── guides/
│   ├── reference/
│   ├── spec/
│   └── sql/
├── log/
├── src/
│   ├── public/
│   │   ├── main.go
│   │   ├── bootstrap.go
│   │   ├── routes.go
│   │   ├── server.go
│   │   └── wwwroot/
│   ├── conventions/
│   │   ├── authorization/
│   │   ├── hmvc/
│   │   ├── responses/
│   │   ├── routing/
│   │   ├── validation/
│   │   └── contracts/
│   ├── app/
│   │   ├── Menu/
│   │   ├── Option/
│   │   ├── Staff/
│   │   ├── Role/
│   │   └── Post/
│   ├── infra/
│   │   ├── auth/
│   │   ├── cache/
│   │   ├── data/
│   │   ├── http/
│   │   ├── sql/
│   │   ├── session/
│   │   ├── template/
│   │   └── payments/
│   ├── theme/
│   │   └── default/
│   │       ├── layouts/
│   │       ├── partials/
│   │       ├── pages/
│   │       └── assets/
│   └── tests/
│       ├── integration/
│       ├── smoke/
│       └── fixtures/
└── tmp/
```

## Layer Responsibilities
- `src/public`: thin executable entry only; wire server, routes, middleware, static files, and bootstrap.
- `src/conventions`: repo-wide conventions, contracts, routing rules, response shapes, validation rules, and cross-repo architectural defaults.
- `src/app`: module-owned business code; each entity keeps its own `feed.go`, `reaction.go`, `outfit.go`, and `kit.go`.
- `src/infra`: technical adapters and runtime implementations such as DB, cache, session, SQL execution, transport clients, and payment gateways.
- `src/theme`: SSR theme assets and templates; equivalent responsibility to F3CMS theme.
- `src/tests`: smoke, integration, and fixture-backed tests.

## Entity Module Rule
- The minimum entity unit remains one folder per entity under `src/app/{Entity}`.
- The minimum conventional files are `feed.go`, `reaction.go`, `outfit.go`, and `kit.go`.
- If workflow or DTO files are entity-owned, keep them in the same module folder rather than pushing them into global helper folders.

## Boundary Rules
- If logic contains entity truth, writeback ownership, payload loading, or business coordination, keep it in `src/app/{Entity}`.
- If logic is pure runtime or a reusable adapter without owner-specific decisions, place it in `src/infra`.
- If logic defines repo-wide conventions rather than technical adapters, place it in `src/conventions`.

## Mapping From F3CMS
- `www/f3cms/modules` -> `src/app`
- `www/f3cms/theme` -> `src/theme`
- `www/f3cms/libs` -> split between `src/conventions` and `src/infra`
- web entry such as `index.php` -> `src/public/main.go`