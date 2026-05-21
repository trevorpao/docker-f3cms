# API Testing Guide

## Purpose
- Provide one operational guide for testing F3CMS API routes from the local machine and from Docker-based smoke suites.
- Give engineers and LLMs a stable answer for API base URL, route shape, request format, and validation choices.
- Prevent API verification from drifting into ad hoc guesses about host names, ports, or route entrypoints.

## Primary Readers
- Backend programmers testing Reaction routes
- Engineers validating new module request surfaces
- LLM-based assistants performing route-level verification

## Scope
- local-machine API entrypoint
- route shape for Reaction APIs
- request and response expectations
- curl-based verification examples
- when to use host-side API calls versus Docker smoke suites

## Inputs
- [setup.md](setup.md)
- [testmode_development_guide.md](testmode_development_guide.md)
- [../reference/reaction_reference.md](../reference/reaction_reference.md)
- [../../www/f3cms/routes.ini](../../www/f3cms/routes.ini)

## Core Thesis
- For local machine API testing, the canonical base URL is `https://loc.f3cms.com:4433/api/`.
- For formal repository validation, Docker remains the source of truth; host-side calls are the fastest external verification layer, while smoke suites remain the stable executable contract.
- Route behavior should be tested through the real HTTPS entrypoint when the goal is to validate Nginx + F3 routing, and through `www/tests/smoke/` when the goal is stable regression coverage.

## Canonical Local API Entry

### Local Machine Base URL

- Use `https://loc.f3cms.com:4433/api/` as the default local API base URL.
- Do not default to `https://localhost:4433/api/` for formal project documentation.
- This host matches the local nginx `server_name` and local SSL setup used by the repository.

### Local Prerequisites

Before calling the API from the host machine:

1. Ensure `loc.f3cms.com` resolves locally, for example through `/etc/hosts`.
2. Ensure local SSL for `loc.f3cms.com` is available.
3. Ensure Docker services are running and nginx is exposed on port `4433`.

If the machine does not yet trust the local certificate, temporary diagnostics may use `curl -k`, but trusted local SSL is the intended steady state.

## Route Shape

The generic Reaction API entry is defined by the route:

```ini
GET|HEAD|POST /api/@module/@method
```

This means the canonical local pattern is:

```text
https://loc.f3cms.com:4433/api/<module>/<method>
```

Examples:

- `https://loc.f3cms.com:4433/api/phonebook/create_with_phones`
- `https://loc.f3cms.com:4433/api/campaign/create_from_phonebook`
- `https://loc.f3cms.com:4433/api/mobile/create_or_ensure`

## Request Rules

### Preferred Method

- Prefer `POST` for API verification unless the target route is explicitly read-only.
- Even though the generic route allows `GET|HEAD|POST`, create and mutation flows should be tested as `POST`.

### Request Body Format

- Reaction request parsing accepts normal form posts and may also consume raw body content depending on runtime context.
- For routine route testing, prefer `application/x-www-form-urlencoded` requests because they match the existing `Reaction::_getReq()` fallback path well.

### Response Envelope

Reaction responses return a JSON envelope shaped like:

```json
{
  "code": 1,
  "data": {},
  "csrf": "..."
}
```

Practical interpretation:

- `code = 1` means success.
- `data` carries the route-specific payload.
- `csrf` is refreshed by `_return()` and is part of the normal response shape.

## Host-Side curl Examples

### 1. Health-Style Route Probe

Use this first when you only need to confirm the HTTPS route is reachable:

```sh
curl https://loc.f3cms.com:4433/api/contact/add_new
```

If local SSL trust is not ready yet:

```sh
curl -k https://loc.f3cms.com:4433/api/contact/add_new
```

### 2. Phonebook Creation Example

```sh
curl https://loc.f3cms.com:4433/api/phonebook/create_with_phones \
  --data-urlencode 'member_id=123' \
  --data-urlencode 'title=API Test Phonebook' \
  --data-urlencode 'phones[]=0912345678' \
  --data-urlencode 'phones[]=+14155550123' \
  --data-urlencode 'remark=host api test' \
  --data-urlencode 'insert_user=1'
```

### 3. Campaign Creation Example

```sh
curl https://loc.f3cms.com:4433/api/campaign/create_from_phonebook \
  --data-urlencode 'member_id=123' \
  --data-urlencode 'phonebook_id=456' \
  --data-urlencode 'content=API route test content' \
  --data-urlencode 'scheduled_ts=2026-05-21 10:00:00' \
  --data-urlencode 'insert_user=1'
```

### 4. Mobile Create Or Ensure Example

```sh
curl https://loc.f3cms.com:4433/api/mobile/create_or_ensure \
  --data-urlencode 'phone_number=0912345678' \
  --data-urlencode 'insert_user=1'
```

## Docker-Side Route Testing

When a smoke suite must hit the real HTTPS route from inside the Docker network:

- call `https://web-server/api/<module>/<method>`
- send `Host: loc.f3cms.com`
- disable SSL peer verification when the suite is using the container-internal hostname rather than the certificate hostname

This pattern is for executable smoke validation inside the repository. It is not the canonical host-machine URL.

## When To Use Which Validation Layer

### Use Host-Side API Calls When

- you want a fast manual check against the real nginx HTTPS entrypoint
- you want to confirm route reachability, request shape, or visible JSON behavior
- you are debugging whether the route works from the local machine as a client

### Use Docker Smoke Suites When

- you need repeatable repository validation
- you need DB setup and cleanup to stay deterministic
- you need CI-like proof for an FDD slice
- you need to verify external route flow and resulting database state together

Canonical smoke command shape:

```sh
docker compose exec -T php-fpm php /var/www/tests/smoke/<path>
```

## Recommended Testing Sequence

1. Probe the route quickly from the host machine with `curl`.
2. If the route is part of a feature contract, add or update the corresponding smoke under `www/tests/smoke/<domain>/`.
3. Run the narrowest Docker smoke that proves the changed route behavior.
4. Update the active feature spec files when the validation slice becomes part of the stable contract.

## Common Mistakes To Avoid

- Do not document `localhost` as the canonical API host when the repository expects `loc.f3cms.com`.
- Do not test only helper methods when the requirement is specifically about external route behavior.
- Do not replace Docker smoke validation with curl-only validation for formal completion.
- Do not create route-test logic under legacy `www/f3cms/scripts/` paths.
- Do not guess the response shape; Reaction routes return the standard `code / data / csrf` envelope.

## Related Documents
- [index.md](index.md)
- [testmode_development_guide.md](testmode_development_guide.md)
- [../reference/reaction_reference.md](../reference/reaction_reference.md)
- [setup.md](setup.md)

## Status
- Draft v1