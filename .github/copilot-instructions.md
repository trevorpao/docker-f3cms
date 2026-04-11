# Project Guidelines

## Build and Test
- When a task involves validation, smoke scripts, PHP script execution, workflow verification, or post-change runtime checks, prefer the project's existing Docker environment before local PHP.
- Use existing `docker compose` services and container paths as the default execution baseline.
- Only fall back to local PHP when Docker is unavailable or the task explicitly requires the host environment.

## Conventions
- Treat Docker results as the source of truth when host PHP and container PHP behave differently.
- Do not classify a host-only PHP failure as a code regression until the same path fails in Docker.
- When a task involves database verification, use the database connection credentials defined in `.env` as the default source of truth.
- Do not guess, hardcode, or substitute database credentials when `.env` already provides the validation target.
- When understanding F3CMS architecture, terminology, or process, treat the files under `document/` as the primary source of truth instead of making generic framework assumptions.
- For feature work under `document/spec/`, read that feature's `history.md` first, then use `plan.md` and `check.md` to determine the next step; do not restart from `idea.md` unless the documents show the earlier stages are incomplete.