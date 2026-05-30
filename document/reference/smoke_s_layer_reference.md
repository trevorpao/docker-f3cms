# Smoke S Layer Reference

## Purpose
- Provide a quick technical reference for the FORKS Smoke S Layer entry contract.
- Help engineers quickly verify how `www/tests/index.php` resolves a smoke path, which guards are enforced, and what result vocabulary is expected.
- Prevent implementation and review from guessing path grammar, discovery flow, or failure meanings.

## Primary Readers
- Backend programmers
- Engineers validating or extending module-owned smoke cases
- LLMs doing smoke-path lookup or contract explanation

## Scope
- official smoke entrypoint contract
- path grammar and module discovery
- smoke bootstrap contract
- runtime guard and DB isolation contract
- success and failure response shapes
- common validation commands

## LLM Reading Contract
- Treat this file as a lookup-style technical reference for the current Smoke S Layer contract.
- If the question is whether smoke should exist, where ownership belongs, or how the layers should be split, go back to guides.
- If the question is how a smoke path is parsed, which file is loaded, which guard blocks execution, or which error key is expected, use this file first.

## Core Thesis
- The official Smoke S Layer entry is `www/tests/index.php <path>`.
- `<path>` is a strict `<module>/<surface>/<contract>` contract.
- The entrypoint resolves a module-owned `smoke.php`, enforces runtime guards, and emits a stable JSON envelope.

## Official Entrypoint

```text
php /var/www/tests/index.php <module>/<surface>/<contract>
```

Examples:

- `php /var/www/tests/index.php mobile/request/create_or_ensure`
- `php /var/www/tests/index.php phonebook/owner/create_with_mobiles`
- `php /var/www/tests/index.php campaign/request/create_from_phonebook`

## Path Grammar

```text
<path> := <module>/<surface>/<contract>

<module>   := [a-z][a-z0-9_]*
<surface>  := [a-z][a-z0-9_]*
<contract> := [a-z][a-z0-9_]*
```

Rules:

- The path must contain exactly 3 segments.
- Empty segments, extra segments, and illegal characters are rejected as `invalid_path`.
- Alias paths, fuzzy completion, and query-style routing are not part of the first-pass contract.

## Module Discovery Contract

Resolution flow:

1. Read `module` from path segment 1.
2. Normalize it to the F3CMS module owner name.
3. Resolve the module directory as `www/f3cms/modules/<Module>/`.
4. Resolve the owner smoke file as `www/f3cms/modules/<Module>/smoke.php`.
5. Load `\F3CMS\s<Module>` and execute the shared `run(surface, contract, context)` contract.

Examples:

- `mobile` -> `Mobile` -> `www/f3cms/modules/Mobile/smoke.php` -> `\F3CMS\sMobile`
- `phonebook` -> `Phonebook` -> `www/f3cms/modules/Phonebook/smoke.php` -> `\F3CMS\sPhonebook`
- `campaign` -> `Campaign` -> `www/f3cms/modules/Campaign/smoke.php` -> `\F3CMS\sCampaign`

## Runtime Guard Contract

The entry must reject execution unless all required guard conditions are met.

### Environment Guard

- `APP_ENV` must equal `develop`
- failure key: `smoke_env_blocked`

### Write Guard

- `ALLOW_SMOKE_WRITE` must equal `1`
- failure key: `smoke_write_not_allowed`

### DB Isolation Guard

- `SMOKE_DB_NAME` must be present for DB-backed smoke
- `SMOKE_DB_NAME` may contain only letters, numbers, and underscores
- `SMOKE_DB_NAME` must differ from the primary configured `db_name`
- DB switching happens during `www/tests/adapters/f3cms/bootstrap.php`
- current failure behavior for DB isolation issues is execution blocking during bootstrap

## Smoke Bootstrap Contract

- `www/tests/index.php` loads the resolved module-owned `smoke.php`
- the resolved class must exist as `\F3CMS\s<Module>`
- the class must extend `\F3CMS\Smoke`
- the entrypoint passes runtime context to `run(surface, contract, context)`
- the entrypoint itself must not implement entity business behavior

## Dispatch Contract

- The first-pass implementation uses a unified entrypoint: `run(surface, contract, context)`.
- Surface and contract resolution inside the smoke class must stay explicit.
- Unknown `surface` must produce `surface_not_found`.
- Unknown `contract` must produce `contract_not_found`.
- Arbitrary public-method exposure is not part of the contract.

## Success Envelope

Minimal success shape:

```json
{
  "code": 1,
  "status": "ok",
  "path": "mobile/request/create_or_ensure",
  "module": "Mobile",
  "surface": "request",
  "contract": "create_or_ensure",
  "result": {}
}
```

## Error Envelope

Minimal error shape:

```json
{
  "code": 0,
  "status": "error",
  "error": "module_not_found",
  "message": "Smoke module 'mobile' not found.",
  "path": "mobile/request/create_or_ensure"
}
```

## Error Vocabulary

| Error Key | Common Meaning | Typical Cause |
| --- | --- | --- |
| `smoke_env_blocked` | environment guard failed | `APP_ENV` is not `develop` |
| `smoke_write_not_allowed` | write guard failed | `ALLOW_SMOKE_WRITE` is not `1` |
| `invalid_path` | path shape is invalid | wrong segment count or illegal characters |
| `module_not_found` | module owner directory missing | `www/f3cms/modules/<Module>/` does not exist |
| `smoke_file_not_found` | owner smoke file missing | `smoke.php` does not exist under the module |
| `invalid_smoke_contract` | class bootstrap failed | missing class or class not extending `F3CMS\Smoke` |
| `surface_not_found` | surface mapping missing | module-owned smoke does not support that surface |
| `contract_not_found` | contract mapping missing | module-owned smoke does not support that contract |
| `execution_failed` | runtime execution failed | bootstrap or smoke execution threw an exception |

## Canonical Validation Commands

Successful DB-backed smoke:

```sh
docker compose exec -e APP_ENV=develop -e ALLOW_SMOKE_WRITE=1 -e SMOKE_DB_NAME=target_db_smoke php-fpm php /var/www/tests/index.php campaign/request/create_from_phonebook
```

Expected guard-block example:

```sh
docker compose exec -e APP_ENV=develop -e ALLOW_SMOKE_WRITE=1 -e SMOKE_DB_NAME=target_db php-fpm php /var/www/tests/index.php campaign/request/create_from_phonebook
```

Interpretation:

- the first command should execute against an isolated smoke DB
- the second command should be rejected because `SMOKE_DB_NAME` reuses the primary configured database name

## Common Mainline Paths

| Path | Ownership Shape | Main Contract |
| --- | --- | --- |
| `mobile/request/create_or_ensure` | request-side smoke | mobile normalization and dedupe |
| `phonebook/owner/create_with_mobiles` | owner-side smoke | owner create plus normalized mobile linkage |
| `campaign/request/create_from_phonebook` | request-side smoke | campaign creation and queue expansion |

## Related Documents
- [intro.md](intro.md)
- [../guides/smoke_s_layer_guide.md](../guides/smoke_s_layer_guide.md)
- [../glossary.md](../glossary.md)
- [../spec/SmokeTestOptimization/optimization.md](../spec/SmokeTestOptimization/optimization.md)

## Status
- Draft v1